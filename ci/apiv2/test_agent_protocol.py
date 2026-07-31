"""
Contract tests for the Hashtopolis agent API (POST /api/server.php).

These tests pin the wire-level contract of the agent API as it exists today
(single-endpoint, action-string dispatch, `{action, response, ...}` JSON
envelope, token-in-body auth). They are intentionally written to pass against
the current `src/api/server.php` implementation AND to keep passing after the
planned Slim 4 / PSR-7 refactor, so they act as the executable specification
of the agent protocol during the rewrite (see doc/agent_api_rewrite.md).

The tests drive the live server through `DummyAgent` (success flows that mirror
what real agents do) and through a small raw request helper (envelope / error
paths, where `DummyAgent._do_request` would raise on non-SUCCESS responses).
"""

import json
import re
import unittest

import requests

from hashtopolis import Agent, Config, HealthCheck, Voucher
from hashtopolis_agent import DummyAgent
from utils import BaseTest, do_create_agentassignent, do_create_dummy_agent, do_create_voucher


AGENT_ENDPOINT = '/api/server.php'


def _uri():
    """Return the configured server base URI (resolved the same way as BaseTest)."""
    from pathlib import Path
    import confidence
    load_order = (str(Path(__file__).parent.joinpath('{name}-defaults.{extension}')),) \
                 + confidence.DEFAULT_LOAD_ORDER
    cfg = confidence.load_name('hashtopolis-test', load_order=load_order)
    return cfg['hashtopolis_uri']


def agent_request(payload):
    """POST a raw JSON payload to the agent API and return (status_code, body_text).

    Unlike `DummyAgent._do_request`, this does NOT raise or log on non-SUCCESS
    responses, which is required to assert error envelopes.
    """
    r = requests.post(_uri() + AGENT_ENDPOINT, data=json.dumps(payload))
    return r.status_code, r.text


def parse_envelope(body):
    """Parse an agent API JSON response body.

    The current legacy `server.php` emits PHP warnings *before* the JSON when the
    request body is empty / non-JSON (because it reads `$QUERY['action']` on a
    null array). Those warnings are a bug that the planned refactor will silently
    fix; they are NOT part of the wire contract. To keep these tests passing both
    before and after the refactor, we tolerate leading non-JSON noise by
    extracting the trailing JSON object.
    """
    try:
        return json.loads(body)
    except json.JSONDecodeError:
        match = re.search(r'\{.*}', body, re.DOTALL)
        if match is None:
            raise AssertionError(f"No JSON object found in response body: {body!r}")
        return json.loads(match.group(0))


def assert_error_envelope(test, body, action):
    """Assert `body` is the standard error envelope for the given action string."""
    resp = parse_envelope(body)
    test.assertEqual(resp['action'], action)
    test.assertEqual(resp['response'], 'ERROR')
    test.assertIn('message', resp)
    test.assertIsInstance(resp['message'], str)


class AgentProtocolBase(BaseTest):
    """Base for agent-protocol tests; ensures every dummy agent is cleaned up.

    `do_create_dummy_agent()` registers an Agent in the DB but does NOT place it
    on the teardown heap; callers must do that themselves. Forgetting it leaks
    agents into the shared test DB. `_dummy()` does the registration AND queues
    the agent for teardown, so subclasses just use `self._dummy()`.

    `tearDown` is overridden to tolerate 404s when the server already deleted
    an object (e.g. an Assignment is deleted server-side when all hashes in a
    hashlist are cracked).
    """

    def _dummy(self):
        """Register a dummy agent and queue it for teardown; return the DummyAgent."""
        dummy, agent = do_create_dummy_agent()
        self.delete_after_test(agent)
        return dummy

    def tearDown(self):
        """Like BaseTest.tearDown but tolerates 404/already-deleted objects.

        This happens when the server-side logic deletes assignments/chunks
        (e.g. when all hashes are cracked → unassignAllAgents).
        """
        while len(self.obj_heap) > 0:
            obj = self.obj_heap.pop()
            try:
                obj.delete()
            except Exception:
                pass

    def _dummy_with_agent(self):
        """Register a dummy agent and queue it for teardown; return (DummyAgent, Agent)."""
        dummy, agent = do_create_dummy_agent()
        self.delete_after_test(agent)
        return dummy, agent

    def _setup_assigned_agent(self, hashlist=None, task_extra=None):
        """Create agent + hashlist + task + assignment; return (dummy, agent, task, hashlist).

        The task uses ``staticChunks=0`` (NORMAL) by default so the benchmark
        step is exercised. Pass ``task_extra`` to override task fields.
        """
        dummy, agent = self._dummy_with_agent()
        if hashlist is None:
            hashlist = self.create_hashlist()
        extra = {'staticChunks': 0}
        if task_extra:
            extra.update(task_extra)
        task = self.create_task(hashlist, extra_payload=extra)
        assignment = do_create_agentassignent(agent, task)
        self.delete_after_test(assignment)
        return dummy, agent, task, hashlist

    def _drive_agent_to_chunk(self, dummy, task_id, keyspace=56800):
        """Drive an agent through the lifecycle until it gets a chunk (status=OK).

        Handles both fresh tasks (keyspace_required → benchmark → OK) and tasks
        where the keyspace was already set by another agent (benchmark → OK).
        The assignment must already exist (created via ``do_create_agentassignent``).
        Returns the parsed OK chunk response dict (with chunkId, skip, length).
        """
        _, body = agent_request({"action": "getChunk", "token": dummy.token, "taskId": task_id})
        resp = parse_envelope(body)
        self.assertEqual(resp['response'], "SUCCESS", f"getChunk failed: {resp}")

        if resp['status'] == "keyspace_required":
            agent_request({
                "action": "sendKeyspace", "token": dummy.token,
                "taskId": task_id, "keyspace": keyspace,
            })
            _, body = agent_request({"action": "getChunk", "token": dummy.token, "taskId": task_id})
            resp = parse_envelope(body)

        if resp['status'] == "benchmark":
            agent_request({
                "action": "sendBenchmark", "token": dummy.token,
                "taskId": task_id, "type": "run", "result": 674,
            })
            _, body = agent_request({"action": "getChunk", "token": dummy.token, "taskId": task_id})
            resp = parse_envelope(body)

        self.assertEqual(resp['status'], "OK",
                         f"Expected OK after lifecycle, got {resp.get('status')}: {resp}")
        return resp


# ---------------------------------------------------------------------------
# Envelope / dispatch contract
# ---------------------------------------------------------------------------

class TestEnvelope(unittest.TestCase):
    """Contract-level properties of the endpoint that do not require fixtures."""

    def test_content_type_is_json(self):
        """Response Content-Type header is always application/json."""
        r = requests.post(_uri() + AGENT_ENDPOINT, data=json.dumps({"action": "testConnection"}))
        self.assertEqual(r.status_code, 200)
        self.assertEqual(r.headers.get('Content-Type'), 'application/json')

    def test_test_connection_success_envelope(self):
        """testConnection returns the minimal SUCCESS envelope with no extra fields."""
        code, body = agent_request({"action": "testConnection"})
        self.assertEqual(code, 200)
        resp = parse_envelope(body)
        self.assertEqual(resp, {"action": "testConnection", "response": "SUCCESS"})

    def test_unknown_action_returns_inv_envelope(self):
        """An unknown action string returns the INV error envelope."""
        code, body = agent_request({"action": "doesNotExist"})
        self.assertEqual(code, 200)
        self.assertEqual(parse_envelope(body), {
            "action": "INV",
            "response": "ERROR",
            "message": "Invalid query!",
        })

    def test_missing_action_returns_inv_envelope(self):
        """A request body without an action field returns the INV error envelope."""
        code, body = agent_request({"some": "thing"})
        self.assertEqual(code, 200)
        self.assertEqual(parse_envelope(body), {
            "action": "INV",
            "response": "ERROR",
            "message": "Invalid query!",
        })

    def test_empty_body_returns_inv_envelope(self):
        """An empty JSON object body returns the INV error envelope.

        The current server emits PHP warnings before the JSON here (the bug the
        refactor will silently fix); we only assert the trailing JSON envelope
        so the test is stable across the refactor.
        """
        code, body = agent_request({})
        self.assertEqual(code, 200)
        self.assertEqual(parse_envelope(body), {
            "action": "INV",
            "response": "ERROR",
            "message": "Invalid query!",
        })

    def test_non_json_body_returns_inv_envelope(self):
        """A non-JSON request body returns the INV error envelope."""
        r = requests.post(_uri() + AGENT_ENDPOINT, data="this is not json")
        self.assertEqual(r.status_code, 200)
        self.assertEqual(parse_envelope(r.text), {
            "action": "INV",
            "response": "ERROR",
            "message": "Invalid query!",
        })

    def test_error_envelope_shape(self):
        """A representative error path (login with bogus token) produces the canonical 3-key error envelope."""
        code, body = agent_request({"action": "login", "token": "bogus", "clientSignature": "x"})
        self.assertEqual(code, 200)
        assert_error_envelope(self, body, "login")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")


# ---------------------------------------------------------------------------
# testConnection
# ---------------------------------------------------------------------------

class TestTestConnection(unittest.TestCase):
    """Tests for the testConnection action — a no-auth liveness probe."""

    def test_success(self):
        """testConnection returns SUCCESS via DummyAgent."""
        ok = DummyAgent().test_connection()
        self.assertTrue(ok)


# ---------------------------------------------------------------------------
# register
# ---------------------------------------------------------------------------

class TestRegister(AgentProtocolBase):
    """Tests for the register action — voucher-based agent registration."""

    def test_register_success(self):
        """Registering with a valid voucher returns a non-empty token and consumes the voucher."""
        voucher = do_create_voucher()
        self.delete_after_test(voucher)
        agent = DummyAgent()
        agent.register(voucher=voucher.voucher, name='protocol-test-register')
        self.assertEqual(agent.token.__class__, str)
        self.assertGreaterEqual(len(agent.token), 1)
        self.assertEqual(list(Voucher.objects.filter(id=voucher.id)), [])
        self.delete_after_test(Agent.objects.get(agentName='protocol-test-register'))

    def test_register_missing_fields(self):
        """Registering without voucher or name returns 'Invalid registering query!'."""
        code, body = agent_request({"action": "register"})
        assert_error_envelope(self, body, "register")
        self.assertEqual(parse_envelope(body)['message'], "Invalid registering query!")

    def test_register_unknown_voucher(self):
        """Registering with a non-existent voucher returns 'Provided voucher does not exist.'"""
        code, body = agent_request({
            "action": "register",
            "voucher": "definitely-not-a-real-voucher-xyz",
            "name": "protocol-test-bad-voucher",
        })
        assert_error_envelope(self, body, "register")
        self.assertEqual(parse_envelope(body)['message'], "Provided voucher does not exist.")

    def test_register_voucher_not_consumed_when_multi_use_enabled(self):
        """When voucherDeletion is enabled (0=disabled, 1=enabled), the voucher is NOT consumed on registration.

        The voucherDeletion config is a TICKBOX where '1' means "vouchers can be
        used multiple times and will not be deleted automatically". The config is
        toggled on for the duration of the test, then restored.
        """
        config = Config.objects.get(item='voucherDeletion')
        original = config.value
        config.value = "1"
        config.save()
        try:
            voucher = do_create_voucher()
            self.delete_after_test(voucher)
            agent = DummyAgent()
            agent.register(voucher=voucher.voucher, name='protocol-test-multi-voucher')
            self.assertGreaterEqual(len(agent.token), 1)
            self.delete_after_test(Agent.objects.get(agentName='protocol-test-multi-voucher'))
            # Voucher should still exist after registration
            self.assertEqual(len(list(Voucher.objects.filter(id=voucher.id))), 1)
        finally:
            config.value = original
            config.save()


# ---------------------------------------------------------------------------
# login
# ---------------------------------------------------------------------------

class TestLogin(AgentProtocolBase):
    """Tests for the login action — token validation and server config retrieval."""

    def test_login_success(self):
        """Login with a valid token returns multicast, timeout, and server-version fields."""
        dummy = self._dummy()
        code, body = agent_request({
            "action": "login",
            "token": dummy.token,
            "clientSignature": "protocol-test-sig",
        })
        self.assertEqual(code, 200)
        resp = parse_envelope(body)
        self.assertEqual(resp['action'], "login")
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertIsInstance(resp['multicastEnabled'], bool)
        self.assertIsInstance(resp['timeout'], int)
        self.assertIsInstance(resp['server-version'], str)
        self.assertIn('(', resp['server-version'])

    def test_login_invalid_token(self):
        """Login with a bogus token returns 'Invalid token!'."""
        code, body = agent_request({"action": "login", "token": "bad", "clientSignature": "x"})
        assert_error_envelope(self, body, "login")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")

    def test_login_missing_fields(self):
        """Login without any fields (no token, no clientSignature) returns 'Invalid token!' (middleware handles missing token for PSR-7 controllers)."""
        code, body = agent_request({"action": "login"})
        assert_error_envelope(self, body, "login")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")

    def test_login_invalid_token_takes_priority_over_missing_fields(self):
        """When the token is present but invalid, 'Invalid token!' takes priority over missing-field errors.

        The TokenAuthMiddleware checks the token before the controller validates
        other fields, so an invalid token with a missing clientSignature returns
        'Invalid token!' rather than 'Invalid login query!'.
        """
        code, body = agent_request({"action": "login", "token": "x"})
        assert_error_envelope(self, body, "login")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")


# ---------------------------------------------------------------------------
# updateInformation
# ---------------------------------------------------------------------------

class TestUpdateInformation(AgentProtocolBase):
    """Tests for the updateInformation action — agent hardware specs upload."""

    def test_update_information_success(self):
        """Sending valid hardware info (uid, os, devices) returns SUCCESS."""
        dummy = self._dummy()
        code, body = agent_request({
            "action": "updateInformation",
            "token": dummy.token,
            "uid": "230-34-345-345",
            "os": 0,
            "devices": ["ATI HD7970", "ATI HD7970"],
        })
        self.assertEqual(code, 200)
        self.assertEqual(parse_envelope(body), {
            "action": "updateInformation",
            "response": "SUCCESS",
        })

    def test_update_information_missing_fields(self):
        """Sending updateInformation without required fields returns 'Invalid update query!'."""
        dummy = self._dummy()
        code, body = agent_request({"action": "updateInformation", "token": dummy.token})
        assert_error_envelope(self, body, "updateInformation")
        self.assertEqual(parse_envelope(body)['message'], "Invalid update query!")

    def test_update_information_invalid_token(self):
        """Sending updateInformation with a bogus token returns 'Invalid token!'."""
        code, body = agent_request({
            "action": "updateInformation",
            "token": "bad",
            "uid": "x",
            "os": 0,
            "devices": ["cpu"],
        })
        assert_error_envelope(self, body, "updateInformation")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")


# ---------------------------------------------------------------------------
# checkClientVersion
# ---------------------------------------------------------------------------

class TestCheckClientVersion(AgentProtocolBase):
    """Tests for the checkClientVersion action — client binary update checking."""

    def test_check_up_to_date(self):
        """Sending a version newer than the seeded binary returns version='OK'."""
        dummy = self._dummy()
        code, body = agent_request({
            "action": "checkClientVersion",
            "token": dummy.token,
            "version": "999.999.999",
            "type": "python",
        })
        self.assertEqual(code, 200)
        resp = parse_envelope(body)
        self.assertEqual(resp['action'], "checkClientVersion")
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertEqual(resp['version'], "OK")

    def test_check_new_version_available(self):
        """Sending a version older than the seeded binary returns version='NEW' with a url."""
        dummy = self._dummy()
        code, body = agent_request({
            "action": "checkClientVersion",
            "token": dummy.token,
            "version": "0.0.1",
            "type": "python",
        })
        self.assertEqual(code, 200)
        resp = parse_envelope(body)
        self.assertEqual(resp['action'], "checkClientVersion")
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertEqual(resp['version'], "NEW")
        self.assertIsInstance(resp['url'], str)

    def test_check_unknown_type(self):
        """Sending an unknown binary type returns 'Type not found!'."""
        dummy = self._dummy()
        code, body = agent_request({
            "action": "checkClientVersion",
            "token": dummy.token,
            "version": "0.0.1",
            "type": "no-such-type",
        })
        assert_error_envelope(self, body, "checkClientVersion")
        self.assertEqual(parse_envelope(body)['message'], "Type not found!")

    def test_check_missing_fields(self):
        """Sending checkClientVersion without version or type returns 'Invalid version check query!'."""
        dummy = self._dummy()
        code, body = agent_request({"action": "checkClientVersion", "token": dummy.token})
        assert_error_envelope(self, body, "checkClientVersion")
        self.assertEqual(parse_envelope(body)['message'], "Invalid version check query!")

    def test_check_invalid_token(self):
        """Sending checkClientVersion with a bogus token returns 'Invalid token!'."""
        code, body = agent_request({
            "action": "checkClientVersion",
            "token": "bad",
            "version": "1.0",
            "type": "python",
        })
        assert_error_envelope(self, body, "checkClientVersion")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")


# ---------------------------------------------------------------------------
# downloadBinary
# ---------------------------------------------------------------------------

class TestDownloadBinary(AgentProtocolBase):
    """Tests for the downloadBinary action — downloading 7zr, uftpd, cracker, or preprocessor binaries."""

    def test_download_extractor_7zr(self):
        """Downloading the 7zr extractor binary returns SUCCESS with an executable field."""
        dummy = self._dummy()
        code, body = agent_request({
            "action": "downloadBinary",
            "token": dummy.token,
            "type": "7zr",
        })
        self.assertEqual(code, 200)
        resp = parse_envelope(body)
        self.assertEqual(resp['action'], "downloadBinary")
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertIn("executable", resp)
        self.assertTrue(resp["executable"].endswith("7zr") or "7zr" in resp["executable"])

    def test_download_uftpd(self):
        """Downloading the uftpd binary returns SUCCESS with an executable field."""
        dummy = self._dummy()
        code, body = agent_request({
            "action": "downloadBinary",
            "token": dummy.token,
            "type": "uftpd",
        })
        resp = parse_envelope(body)
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertIn("executable", resp)

    def test_download_unknown_type(self):
        """Downloading with an unknown binary type returns 'Unknown download type!'."""
        dummy = self._dummy()
        code, body = agent_request({
            "action": "downloadBinary",
            "token": dummy.token,
            "type": "nothing-real",
        })
        assert_error_envelope(self, body, "downloadBinary")
        self.assertEqual(parse_envelope(body)['message'], "Unknown download type!")

    def test_download_missing_fields(self):
        """Sending downloadBinary without the type field returns 'Invalid download query!'."""
        dummy = self._dummy()
        code, body = agent_request({"action": "downloadBinary", "token": dummy.token})
        assert_error_envelope(self, body, "downloadBinary")
        self.assertEqual(parse_envelope(body)['message'], "Invalid download query!")

    def test_download_invalid_token(self):
        """Sending downloadBinary with a bogus token returns 'Invalid token!'."""
        code, body = agent_request({"action": "downloadBinary", "token": "bad", "type": "7zr"})
        assert_error_envelope(self, body, "downloadBinary")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")

    def test_download_cracker_invalid_binary_version_id(self):
        """Downloading a cracker with a non-existent binaryVersionId returns 'Invalid cracker binary type id!'."""
        dummy = self._dummy()
        code, body = agent_request({
            "action": "downloadBinary",
            "token": dummy.token,
            "type": "cracker",
            "binaryVersionId": 99999999,
        })
        assert_error_envelope(self, body, "downloadBinary")
        self.assertEqual(parse_envelope(body)['message'], "Invalid cracker binary type id!")


# ---------------------------------------------------------------------------
# clientError
# ---------------------------------------------------------------------------

class TestClientError(AgentProtocolBase):
    """Tests for the clientError action — agent reports a hashcat error for a task."""

    def test_client_error_success(self):
        """Reporting an error for an assigned task returns SUCCESS."""
        retval = self.create_agent_with_task()
        dummy = retval['dummy_agent']
        task = retval['task']
        code, body = agent_request({
            "action": "clientError",
            "token": dummy.token,
            "taskId": task.id,
            "message": "some hashcat error line",
        })
        self.assertEqual(code, 200)
        self.assertEqual(parse_envelope(body), {
            "action": "clientError",
            "response": "SUCCESS",
        })

    def test_client_error_invalid_task(self):
        """Reporting an error for a non-existent task returns 'Invalid task!'."""
        dummy = self._dummy()
        code, body = agent_request({
            "action": "clientError",
            "token": dummy.token,
            "taskId": 99999999,
            "message": "x",
        })
        assert_error_envelope(self, body, "clientError")
        self.assertEqual(parse_envelope(body)['message'], "Invalid task!")

    def test_client_error_not_assigned(self):
        """Reporting an error for a valid task the agent is not assigned to returns 'Agent is not assigned to this task!'."""
        dummy, agent, task, _ = self._setup_assigned_agent()
        dummy_b, agent_b = self._dummy_with_agent()
        code, body = agent_request({
            "action": "clientError",
            "token": dummy_b.token,
            "taskId": task.id,
            "message": "x",
        })
        assert_error_envelope(self, body, "clientError")
        self.assertEqual(parse_envelope(body)['message'], "Agent is not assigned to this task!")

    def test_client_error_invalid_token(self):
        """Reporting an error with a bogus token returns 'Invalid token!'."""
        code, body = agent_request({
            "action": "clientError",
            "token": "bad",
            "taskId": 1,
            "message": "x",
        })
        assert_error_envelope(self, body, "clientError")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")

    def test_client_error_missing_fields(self):
        """Sending clientError without required fields returns 'Invalid error query!'."""
        dummy = self._dummy()
        code, body = agent_request({"action": "clientError", "token": dummy.token})
        assert_error_envelope(self, body, "clientError")
        self.assertEqual(parse_envelope(body)['message'], "Invalid error query!")


# ---------------------------------------------------------------------------
# getFileStatus
# ---------------------------------------------------------------------------

class TestGetFileStatus(AgentProtocolBase):
    """Tests for the getFileStatus action — retrieve list of server-deleted files for cleanup."""

    def test_get_file_status_success_shape(self):
        """getFileStatus with a valid token returns SUCCESS with a filenames list."""
        dummy = self._dummy()
        code, body = agent_request({"action": "getFileStatus", "token": dummy.token})
        self.assertEqual(code, 200)
        resp = parse_envelope(body)
        self.assertEqual(resp['action'], "getFileStatus")
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertIsInstance(resp['filenames'], list)

    def test_get_file_status_no_token_required(self):
        """getFileStatus with a bogus token returns 'Invalid token!' (token is now validated)."""
        code, body = agent_request({"action": "getFileStatus", "token": "bogus-token"})
        assert_error_envelope(self, body, "getFileStatus")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")


# ---------------------------------------------------------------------------
# getTask
# ---------------------------------------------------------------------------

class TestGetTask(AgentProtocolBase):
    """Tests for the getTask action — retrieve the task an agent should work on."""

    def test_get_task_inactive_agent(self):
        """An inactive agent gets the no-task envelope (taskId=null, reason='Agent is inactive!')."""
        dummy, agent = do_create_dummy_agent()
        self.delete_after_test(agent)
        agent.isActive = False
        agent.save()
        code, body = agent_request({"action": "getTask", "token": dummy.token})
        self.assertEqual(code, 200)
        resp = parse_envelope(body)
        self.assertEqual(resp['action'], "getTask")
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertIsNone(resp['taskId'])
        self.assertIsInstance(resp['reason'], str)

    def test_get_task_assigned(self):
        """An agent assigned to a task gets the full task envelope with attackcmd, files, crackerId, etc."""
        retval = self.create_agent_with_task()
        dummy = retval['dummy_agent']
        code, body = agent_request({"action": "getTask", "token": dummy.token})
        resp = parse_envelope(body)
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertIsInstance(resp['taskId'], int)
        self.assertGreater(resp['taskId'], 0)
        self.assertEqual(resp['hashlistId'], retval['hashlist'].id)
        self.assertIsInstance(resp['attackcmd'], str)
        self.assertIsInstance(resp['cmdpars'], str)
        self.assertIsInstance(resp['files'], list)
        self.assertIsInstance(resp['crackerId'], int)
        self.assertIn(resp['benchType'], ("speed", "run"))

    def test_get_task_missing_fields(self):
        """Sending getTask without a token returns 'Invalid task query!'."""
        code, body = agent_request({"action": "getTask"})
        assert_error_envelope(self, body, "getTask")
        self.assertEqual(parse_envelope(body)['message'], "Invalid task query!")

    def test_get_task_invalid_token(self):
        """Sending getTask with a bogus token returns 'Invalid token!'."""
        code, body = agent_request({"action": "getTask", "token": "bad"})
        assert_error_envelope(self, body, "getTask")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")

    def test_get_task_no_task_for_active_agent(self):
        """An active agent with no assignment gets either a task (taskId:int) or no-task (taskId:null+reason).

        Whether a task is available depends on the shared DB state, so we assert
        the envelope shape for both possible responses.
        """
        dummy = self._dummy()
        code, body = agent_request({"action": "getTask", "token": dummy.token})
        resp = parse_envelope(body)
        self.assertEqual(resp['action'], "getTask")
        self.assertEqual(resp['response'], "SUCCESS")
        if resp['taskId'] is not None:
            self.assertIsInstance(resp['taskId'], int)
            self.assertGreater(resp['taskId'], 0)
        else:
            self.assertIsNone(resp['taskId'])
            self.assertIsInstance(resp['reason'], str)


# ---------------------------------------------------------------------------
# getHashlist / getFound
# ---------------------------------------------------------------------------

class TestGetHashlistAndFound(AgentProtocolBase):
    """Tests for the getHashlist and getFound actions — retrieve download URLs for hashlist/found files."""

    def test_get_hashlist_success(self):
        """getHashlist for the assigned task's hashlist returns SUCCESS with a URL containing 'getHashlist.php'."""
        retval = self.create_agent_with_task()
        dummy = retval['dummy_agent']
        hashlist = retval['hashlist']
        code, body = agent_request({
            "action": "getHashlist",
            "token": dummy.token,
            "hashlistId": hashlist.id,
        })
        resp = parse_envelope(body)
        self.assertEqual(resp['action'], "getHashlist")
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertIsInstance(resp['url'], str)
        self.assertIn('getHashlist.php', resp['url'])

    def test_get_hashlist_invalid_id(self):
        """getHashlist for a non-existent hashlist returns 'Invalid hashlist!'."""
        dummy = self._dummy()
        code, body = agent_request({
            "action": "getHashlist",
            "token": dummy.token,
            "hashlistId": 99999999,
        })
        assert_error_envelope(self, body, "getHashlist")
        self.assertEqual(parse_envelope(body)['message'], "Invalid hashlist!")

    def test_get_hashlist_missing_fields(self):
        """Sending getHashlist without hashlistId returns 'Invalid hashlist query!'."""
        dummy = self._dummy()
        code, body = agent_request({"action": "getHashlist", "token": dummy.token})
        assert_error_envelope(self, body, "getHashlist")
        self.assertEqual(parse_envelope(body)['message'], "Invalid hashlist query!")

    def test_get_hashlist_invalid_token(self):
        """Sending getHashlist with a bogus token returns 'Invalid token!'."""
        code, body = agent_request({"action": "getHashlist", "token": "bad", "hashlistId": 1})
        assert_error_envelope(self, body, "getHashlist")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")

    def test_get_hashlist_not_assigned(self):
        """getHashlist when the agent has no task assignment returns 'Agent is not assigned to a task!'."""
        dummy = self._dummy()
        hashlist = self.create_hashlist()
        code, body = agent_request({
            "action": "getHashlist",
            "token": dummy.token,
            "hashlistId": hashlist.id,
        })
        assert_error_envelope(self, body, "getHashlist")
        self.assertEqual(parse_envelope(body)['message'], "Agent is not assigned to a task!")

    def test_get_hashlist_wrong_hashlist_for_task(self):
        """getHashlist for a hashlist that doesn't match the assigned task returns 'This hashlist is not used for the assigned task!'."""
        dummy, agent, task, _ = self._setup_assigned_agent()
        other_hashlist = self.create_hashlist()
        code, body = agent_request({
            "action": "getHashlist",
            "token": dummy.token,
            "hashlistId": other_hashlist.id,
        })
        assert_error_envelope(self, body, "getHashlist")
        self.assertEqual(parse_envelope(body)['message'],
                         "This hashlist is not used for the assigned task!")

    def test_get_found_success(self):
        """getFound for the assigned task's hashlist returns SUCCESS with a URL containing 'getFound.php'."""
        retval = self.create_agent_with_task()
        dummy = retval['dummy_agent']
        hashlist = retval['hashlist']
        code, body = agent_request({
            "action": "getFound",
            "token": dummy.token,
            "hashlistId": hashlist.id,
        })
        resp = parse_envelope(body)
        self.assertEqual(resp['action'], "getFound")
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertIsInstance(resp['url'], str)
        self.assertIn('getFound.php', resp['url'])

    def test_get_found_invalid_id(self):
        """getFound for a non-existent hashlist returns 'Invalid hashlist!'."""
        dummy = self._dummy()
        code, body = agent_request({
            "action": "getFound",
            "token": dummy.token,
            "hashlistId": 99999999,
        })
        assert_error_envelope(self, body, "getFound")
        self.assertEqual(parse_envelope(body)['message'], "Invalid hashlist!")

    def test_get_found_missing_fields(self):
        """Sending getFound without hashlistId returns 'Invalid found query!'."""
        dummy = self._dummy()
        code, body = agent_request({"action": "getFound", "token": dummy.token})
        assert_error_envelope(self, body, "getFound")
        self.assertEqual(parse_envelope(body)['message'], "Invalid found query!")

    def test_get_found_invalid_token(self):
        """getFound with a bogus token returns 'Invalid token!' with the correct action string 'getFound'."""
        code, body = agent_request({"action": "getFound", "token": "bad", "hashlistId": 1})
        assert_error_envelope(self, body, "getFound")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")

    def test_get_found_not_assigned(self):
        """getFound when the agent has no task assignment returns 'Agent is not assigned to a task!'."""
        dummy = self._dummy()
        hashlist = self.create_hashlist()
        code, body = agent_request({
            "action": "getFound",
            "token": dummy.token,
            "hashlistId": hashlist.id,
        })
        assert_error_envelope(self, body, "getFound")
        self.assertEqual(parse_envelope(body)['message'], "Agent is not assigned to a task!")


# ---------------------------------------------------------------------------
# getFile
# ---------------------------------------------------------------------------

class TestGetFile(AgentProtocolBase):
    """Tests for the getFile action — retrieve a task file's download URL and metadata."""

    def test_get_file_success(self):
        """getFile for a file attached to the agent's task returns SUCCESS with filename, extension, url, filesize."""
        file_obj = self.create_file()
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist, extra_payload={
            'staticChunks': 0, 'files': [file_obj.id],
        })
        dummy, agent = self._dummy_with_agent()
        assignment = do_create_agentassignent(agent, task)
        self.delete_after_test(assignment)
        code, body = agent_request({
            "action": "getFile",
            "token": dummy.token,
            "taskId": task.id,
            "file": file_obj.filename,
        })
        self.assertEqual(code, 200)
        resp = parse_envelope(body)
        self.assertEqual(resp['action'], "getFile")
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertEqual(resp['filename'], file_obj.filename)
        self.assertIsInstance(resp['extension'], str)
        self.assertIsInstance(resp['url'], str)
        self.assertIn('getFile.php', resp['url'])
        self.assertIsInstance(resp['filesize'], int)

    def test_get_file_invalid_task(self):
        """getFile for a non-existent task returns 'Invalid task!'."""
        dummy = self._dummy()
        code, body = agent_request({
            "action": "getFile",
            "token": dummy.token,
            "taskId": 99999999,
            "file": "whatever.txt",
        })
        assert_error_envelope(self, body, "getFile")
        self.assertEqual(parse_envelope(body)['message'], "Invalid task!")

    def test_get_file_not_assigned(self):
        """getFile for a valid task the agent is not assigned to returns 'Client is not assigned to this task!'."""
        file_obj = self.create_file()
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist, extra_payload={
            'staticChunks': 0, 'files': [file_obj.id],
        })
        dummy = self._dummy()
        code, body = agent_request({
            "action": "getFile",
            "token": dummy.token,
            "taskId": task.id,
            "file": file_obj.filename,
        })
        assert_error_envelope(self, body, "getFile")
        self.assertEqual(parse_envelope(body)['message'], "Client is not assigned to this task!")

    def test_get_file_invalid_file(self):
        """getFile for a file that doesn't exist in the DB returns 'Invalid file!'."""
        dummy, agent, task, _ = self._setup_assigned_agent()
        code, body = agent_request({
            "action": "getFile",
            "token": dummy.token,
            "taskId": task.id,
            "file": "nonexistent-file.txt",
        })
        assert_error_envelope(self, body, "getFile")
        self.assertEqual(parse_envelope(body)['message'], "Invalid file!")

    def test_get_file_missing_fields(self):
        """Sending getFile without required fields returns 'Invalid file query!'."""
        dummy = self._dummy()
        code, body = agent_request({"action": "getFile", "token": dummy.token})
        assert_error_envelope(self, body, "getFile")
        self.assertEqual(parse_envelope(body)['message'], "Invalid file query!")

    def test_get_file_invalid_token(self):
        """Sending getFile with a bogus token returns 'Invalid token!'."""
        code, body = agent_request({
            "action": "getFile",
            "token": "bad",
            "taskId": 1,
            "file": "x.txt",
        })
        assert_error_envelope(self, body, "getFile")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")


# ---------------------------------------------------------------------------
# getChunk
# ---------------------------------------------------------------------------

class TestGetChunk(AgentProtocolBase):
    """Tests for the getChunk action — retrieve a chunk to work on for an assigned task."""

    def test_get_chunk_keyspace_required(self):
        """A new task (keyspace=0) returns status='keyspace_required'."""
        dummy, agent, task, _ = self._setup_assigned_agent()
        code, body = agent_request({
            "action": "getChunk",
            "token": dummy.token,
            "taskId": task.id,
        })
        self.assertEqual(code, 200)
        resp = parse_envelope(body)
        self.assertEqual(resp['action'], "getChunk")
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertEqual(resp['status'], "keyspace_required")

    def test_get_chunk_benchmark_required(self):
        """After keyspace is set but before benchmark returns status='benchmark'."""
        dummy, agent, task, _ = self._setup_assigned_agent()
        agent_request({
            "action": "sendKeyspace",
            "token": dummy.token,
            "taskId": task.id,
            "keyspace": 56800,
        })
        code, body = agent_request({
            "action": "getChunk",
            "token": dummy.token,
            "taskId": task.id,
        })
        self.assertEqual(code, 200)
        resp = parse_envelope(body)
        self.assertEqual(resp['action'], "getChunk")
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertEqual(resp['status'], "benchmark")

    def test_get_chunk_ok(self):
        """After keyspace + benchmark returns status='OK' with chunkId, skip, length."""
        dummy, agent, task, _ = self._setup_assigned_agent()
        agent_request({
            "action": "sendKeyspace",
            "token": dummy.token,
            "taskId": task.id,
            "keyspace": 56800,
        })
        agent_request({
            "action": "sendBenchmark",
            "token": dummy.token,
            "taskId": task.id,
            "type": "run",
            "result": 674,
        })
        code, body = agent_request({
            "action": "getChunk",
            "token": dummy.token,
            "taskId": task.id,
        })
        self.assertEqual(code, 200)
        resp = parse_envelope(body)
        self.assertEqual(resp['action'], "getChunk")
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertEqual(resp['status'], "OK")
        self.assertIsInstance(resp['chunkId'], int)
        self.assertIsInstance(resp['skip'], int)
        self.assertIsInstance(resp['length'], int)

    def test_get_chunk_fully_dispatched(self):
        """When all chunks of a task are complete returns status='fully_dispatched'.

        Uses a small keyspace so a single chunk exhausts it; completing that
        chunk and requesting another yields fully_dispatched.
        """
        dummy, agent, task, _ = self._setup_assigned_agent(
            task_extra={'chunkSize': 600, 'skipKeyspace': 0}
        )
        agent_request({
            "action": "sendKeyspace",
            "token": dummy.token,
            "taskId": task.id,
            "keyspace": 100,
        })
        agent_request({
            "action": "sendBenchmark",
            "token": dummy.token,
            "taskId": task.id,
            "type": "run",
            "result": 674,
        })
        _, body = agent_request({
            "action": "getChunk",
            "token": dummy.token,
            "taskId": task.id,
        })
        resp = parse_envelope(body)
        self.assertEqual(resp['status'], "OK")
        chunk_id = resp['chunkId']
        agent_request({
            "action": "sendProgress",
            "token": dummy.token,
            "chunkId": chunk_id,
            "keyspaceProgress": resp['skip'] + resp['length'],
            "relativeProgress": 10000,
            "speed": 1000,
            "state": 4,
            "cracks": [],
        })
        code, body = agent_request({
            "action": "getChunk",
            "token": dummy.token,
            "taskId": task.id,
        })
        self.assertEqual(code, 200)
        resp = parse_envelope(body)
        self.assertEqual(resp['action'], "getChunk")
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertEqual(resp['status'], "fully_dispatched")

    def test_get_chunk_invalid_task(self):
        """getChunk for a non-existent task returns 'Invalid task ID!'."""
        dummy = self._dummy()
        code, body = agent_request({
            "action": "getChunk",
            "token": dummy.token,
            "taskId": 99999999,
        })
        assert_error_envelope(self, body, "getChunk")
        self.assertEqual(parse_envelope(body)['message'], "Invalid task ID!")

    def test_get_chunk_not_assigned(self):
        """getChunk for a valid task the agent is not assigned to returns 'You are not assigned to this task!'."""
        dummy, agent, task, _ = self._setup_assigned_agent()
        dummy_b, agent_b = self._dummy_with_agent()
        code, body = agent_request({
            "action": "getChunk",
            "token": dummy_b.token,
            "taskId": task.id,
        })
        assert_error_envelope(self, body, "getChunk")
        self.assertEqual(parse_envelope(body)['message'], "You are not assigned to this task!")

    def test_get_chunk_invalid_token(self):
        """Sending getChunk with a bogus token returns 'Invalid token!'."""
        code, body = agent_request({"action": "getChunk", "token": "bad", "taskId": 1})
        assert_error_envelope(self, body, "getChunk")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")

    def test_get_chunk_missing_fields(self):
        """Sending getChunk without required fields returns 'Invalid chunk query!'."""
        dummy = self._dummy()
        code, body = agent_request({"action": "getChunk", "token": dummy.token})
        assert_error_envelope(self, body, "getChunk")
        self.assertEqual(parse_envelope(body)['message'], "Invalid chunk query!")


# ---------------------------------------------------------------------------
# sendKeyspace / sendBenchmark
# ---------------------------------------------------------------------------

class TestSendKeyspaceAndBenchmark(AgentProtocolBase):
    """Tests for the sendKeyspace and sendBenchmark actions — agent reports keyspace and benchmark results."""

    def test_send_keyspace_success(self):
        """Sending a valid keyspace for an assigned task returns SUCCESS with keyspace='OK'."""
        retval = self.create_agent_with_task()
        dummy = retval['dummy_agent']
        task = retval['task']
        code, body = agent_request({
            "action": "sendKeyspace",
            "token": dummy.token,
            "taskId": task.id,
            "keyspace": 56800,
        })
        self.assertEqual(code, 200)
        self.assertEqual(parse_envelope(body), {
            "action": "sendKeyspace",
            "response": "SUCCESS",
            "keyspace": "OK",
        })

    def test_send_keyspace_invalid_task(self):
        """Sending keyspace for a non-existent task returns 'Invalid task ID!'."""
        dummy = self._dummy()
        code, body = agent_request({
            "action": "sendKeyspace",
            "token": dummy.token,
            "taskId": 99999999,
            "keyspace": 100,
        })
        assert_error_envelope(self, body, "sendKeyspace")
        self.assertEqual(parse_envelope(body)['message'], "Invalid task ID!")

    def test_send_keyspace_not_assigned(self):
        """Sending keyspace for a task the agent is not assigned to returns 'You are not assigned to this task!'."""
        dummy, agent, task, _ = self._setup_assigned_agent()
        dummy_b, agent_b = self._dummy_with_agent()
        code, body = agent_request({
            "action": "sendKeyspace",
            "token": dummy_b.token,
            "taskId": task.id,
            "keyspace": 100,
        })
        assert_error_envelope(self, body, "sendKeyspace")
        self.assertEqual(parse_envelope(body)['message'], "You are not assigned to this task!")

    def test_send_keyspace_invalid_token(self):
        """Sending keyspace with a bogus token returns 'Invalid token!'."""
        code, body = agent_request({
            "action": "sendKeyspace",
            "token": "bad",
            "taskId": 1,
            "keyspace": 100,
        })
        assert_error_envelope(self, body, "sendKeyspace")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")

    def test_send_keyspace_missing_fields(self):
        """Sending sendKeyspace without required fields returns 'Invalid keyspace query!'."""
        dummy = self._dummy()
        code, body = agent_request({"action": "sendKeyspace", "token": dummy.token})
        assert_error_envelope(self, body, "sendKeyspace")
        self.assertEqual(parse_envelope(body)['message'], "Invalid keyspace query!")

    def test_send_benchmark_success(self):
        """Sending a run-type benchmark with a valid result returns SUCCESS with benchmark='OK'."""
        retval = self.create_agent_with_task()
        dummy = retval['dummy_agent']
        task = retval['task']
        code, body = agent_request({
            "action": "sendBenchmark",
            "token": dummy.token,
            "taskId": task.id,
            "type": "run",
            "result": 674,
        })
        self.assertEqual(code, 200)
        self.assertEqual(parse_envelope(body), {
            "action": "sendBenchmark",
            "response": "SUCCESS",
            "benchmark": "OK",
        })

    def test_send_benchmark_speed_type_success(self):
        """Sending a speed-type benchmark with the int:float format (e.g. '2345:323.000') returns SUCCESS."""
        retval = self.create_agent_with_task()
        dummy = retval['dummy_agent']
        task = retval['task']
        code, body = agent_request({
            "action": "sendBenchmark",
            "token": dummy.token,
            "taskId": task.id,
            "type": "speed",
            "result": "2345:323.000",
        })
        self.assertEqual(code, 200)
        self.assertEqual(parse_envelope(body), {
            "action": "sendBenchmark",
            "response": "SUCCESS",
            "benchmark": "OK",
        })

    def test_send_benchmark_speed_type_invalid_format(self):
        """Sending a speed-type benchmark with a malformed result returns 'Invalid benchmark result!'."""
        retval = self.create_agent_with_task()
        dummy = retval['dummy_agent']
        task = retval['task']
        code, body = agent_request({
            "action": "sendBenchmark",
            "token": dummy.token,
            "taskId": task.id,
            "type": "speed",
            "result": "not-a-valid-format",
        })
        assert_error_envelope(self, body, "sendBenchmark")
        self.assertEqual(parse_envelope(body)['message'], "Invalid benchmark result!")

    def test_send_benchmark_invalid_type(self):
        """Sending a benchmark with an unknown type returns 'Invalid benchmark type!'."""
        retval = self.create_agent_with_task()
        dummy = retval['dummy_agent']
        task = retval['task']
        code, body = agent_request({
            "action": "sendBenchmark",
            "token": dummy.token,
            "taskId": task.id,
            "type": "invalid-type",
            "result": 1,
        })
        assert_error_envelope(self, body, "sendBenchmark")
        self.assertEqual(parse_envelope(body)['message'], "Invalid benchmark type!")

    def test_send_benchmark_invalid_task(self):
        """Sending a benchmark for a non-existent task returns 'Invalid task ID!'."""
        dummy = self._dummy()
        code, body = agent_request({
            "action": "sendBenchmark",
            "token": dummy.token,
            "taskId": 99999999,
            "type": "run",
            "result": 674,
        })
        assert_error_envelope(self, body, "sendBenchmark")
        self.assertEqual(parse_envelope(body)['message'], "Invalid task ID!")

    def test_send_benchmark_not_assigned(self):
        """Sending a benchmark for a task the agent is not assigned to returns 'You are not assigned to this task!'."""
        dummy, agent, task, _ = self._setup_assigned_agent()
        dummy_b, agent_b = self._dummy_with_agent()
        code, body = agent_request({
            "action": "sendBenchmark",
            "token": dummy_b.token,
            "taskId": task.id,
            "type": "run",
            "result": 674,
        })
        assert_error_envelope(self, body, "sendBenchmark")
        self.assertEqual(parse_envelope(body)['message'], "You are not assigned to this task!")

    def test_send_benchmark_invalid_token(self):
        """Sending a benchmark with a bogus token returns 'Invalid token!'."""
        code, body = agent_request({
            "action": "sendBenchmark",
            "token": "bad",
            "taskId": 1,
            "type": "run",
            "result": 1,
        })
        assert_error_envelope(self, body, "sendBenchmark")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")

    def test_send_benchmark_run_type_invalid_result(self):
        """Sending a run-type benchmark with a non-positive result (0) returns 'Invalid benchmark result!' (also deactivates agent)."""
        retval = self.create_agent_with_task()
        dummy = retval['dummy_agent']
        task = retval['task']
        code, body = agent_request({
            "action": "sendBenchmark",
            "token": dummy.token,
            "taskId": task.id,
            "type": "run",
            "result": 0,
        })
        assert_error_envelope(self, body, "sendBenchmark")
        self.assertEqual(parse_envelope(body)['message'], "Invalid benchmark result!")

    def test_send_benchmark_missing_fields(self):
        """Sending sendBenchmark without required fields returns 'Invalid benchmark query!'."""
        dummy = self._dummy()
        code, body = agent_request({"action": "sendBenchmark", "token": dummy.token})
        assert_error_envelope(self, body, "sendBenchmark")
        self.assertEqual(parse_envelope(body)['message'], "Invalid benchmark query!")


# ---------------------------------------------------------------------------
# sendProgress
# ---------------------------------------------------------------------------

class TestSendProgress(AgentProtocolBase):
    """Tests for the sendProgress action — agent reports chunk progress, cracked hashes, and receives zaps."""

    CRACK_HASH = "cc03e747a6afbbcbf8be7668acfebee5"
    CRACK_PLAIN = "test123"
    CRACK_HEX = "74657374313233"
    FAKE_HASH = "ffffffffffffffffffffffffffffffff"
    CRACK_HASH_2 = "098f6bcd4621d373cade4e832627b4f6"
    TWO_HASH_SOURCE_DATA = (
        "Y2MwM2U3NDdhNmFmYmJjYmY4YmU3NjY4YWNmZWJlZTUK"
        "MDk4ZjZiY2Q0NjIxZDM3M2NhZGU0ZTgzMjYyN2I0ZjYK"
    )

    def _make_chunk(self, dummy, task_id):
        """Drive an agent through the lifecycle and return the OK chunk response."""
        return self._drive_agent_to_chunk(dummy, task_id)

    @staticmethod
    def _send_progress(token, chunk, state=2, cracks=None, progress=100):
        """Send a progress update and return the parsed response."""
        _, body = agent_request({
            "action": "sendProgress",
            "token": token,
            "chunkId": chunk['chunkId'],
            "keyspaceProgress": chunk['skip'] + int(chunk['length'] / 100 * progress),
            "relativeProgress": progress * 100,
            "speed": 5700,
            "state": state,
            "cracks": cracks or [],
            "gpuTemp": [30, 70],
            "gpuUtil": [60, 90],
            "cpuUtil": [5, 15],
        })
        return parse_envelope(body)

    def _setup_with_two_hashes(self):
        """Set up an agent + task on a 2-hash hashlist so cracking 1 hash is safe."""
        hashlist = self.create_hashlist(extra_payload={'sourceData': self.TWO_HASH_SOURCE_DATA})
        return self._setup_assigned_agent(hashlist=hashlist)

    def test_send_progress_no_cracks(self):
        """Progress with no cracks returns cracked=0, skipped=0, zaps=[]."""
        dummy, agent, task, _ = self._setup_with_two_hashes()
        chunk = self._make_chunk(dummy, task.id)
        resp = self._send_progress(dummy.token, chunk)
        self.assertEqual(resp['action'], "sendProgress")
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertEqual(resp['cracked'], 0)
        self.assertEqual(resp['skipped'], 0)
        self.assertEqual(resp['zaps'], [])

    def test_send_progress_with_counted_crack(self):
        """Progress with a crack for a real hash in the hashlist returns cracked=1."""
        dummy, agent, task, _ = self._setup_with_two_hashes()
        chunk = self._make_chunk(dummy, task.id)
        resp = self._send_progress(dummy.token, chunk, cracks=[
            [self.CRACK_HASH, self.CRACK_PLAIN, self.CRACK_HEX, "1"],
        ])
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertEqual(resp['cracked'], 1)
        self.assertEqual(resp['skipped'], 0)

    def test_send_progress_with_multiple_counted_cracks(self):
        """Progress with multiple real cracks returns cracked count matching the number of cracks."""
        dummy, agent, task, _ = self._setup_with_two_hashes()
        chunk = self._make_chunk(dummy, task.id)
        resp = self._send_progress(dummy.token, chunk, cracks=[
            [self.CRACK_HASH, self.CRACK_PLAIN, self.CRACK_HEX, "1"],
            [self.CRACK_HASH_2, "test", "74657374", "2"],
        ])
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertEqual(resp['cracked'], 2)

    def test_send_progress_with_skipped_crack(self):
        """Progress with a crack for a non-existent hash returns skipped=1, cracked=0."""
        dummy, agent, task, _ = self._setup_with_two_hashes()
        chunk = self._make_chunk(dummy, task.id)
        resp = self._send_progress(dummy.token, chunk, cracks=[
            [self.FAKE_HASH, "fake", "66616b65", "1"],
        ])
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertEqual(resp['cracked'], 0)
        self.assertEqual(resp['skipped'], 1)

    def test_send_progress_mixed_cracks(self):
        """Progress with a mix of real and non-existent cracks returns cracked=1, skipped=1."""
        dummy, agent, task, _ = self._setup_with_two_hashes()
        chunk = self._make_chunk(dummy, task.id)
        resp = self._send_progress(dummy.token, chunk, cracks=[
            [self.CRACK_HASH, self.CRACK_PLAIN, self.CRACK_HEX, "1"],
            [self.FAKE_HASH, "fake", "66616b65", "2"],
        ])
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertEqual(resp['cracked'], 1)
        self.assertEqual(resp['skipped'], 1)

    def test_send_progress_receives_zaps(self):
        """When agent A cracks a hash, agent B on the same task receives a zap containing the cracked hash.

        Uses staticChunks=1 (CHUNK_SIZE) with chunkSize=1000 so each chunk is
        a fixed 1000-keyspace slice — this ensures agent A's chunk doesn't cover
        the entire keyspace, leaving room for agent B to get one.
        """
        hashlist = self.create_hashlist(extra_payload={'sourceData': self.TWO_HASH_SOURCE_DATA})
        task = self.create_task(hashlist, extra_payload={'staticChunks': 1, 'chunkSize': 1000})
        self.delete_after_test(task)

        dummy_a, agent_a = self._dummy_with_agent()
        assignment_a = do_create_agentassignent(agent_a, task)
        self.delete_after_test(assignment_a)
        chunk_a = self._make_chunk(dummy_a, task.id)
        self._send_progress(dummy_a.token, chunk_a, progress=50, cracks=[
            [self.CRACK_HASH, self.CRACK_PLAIN, self.CRACK_HEX, "1"],
        ])

        dummy_b, agent_b = self._dummy_with_agent()
        assignment_b = do_create_agentassignent(agent_b, task)
        self.delete_after_test(assignment_b)
        chunk_b = self._make_chunk(dummy_b, task.id)

        resp = self._send_progress(dummy_b.token, chunk_b)
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertIn(self.CRACK_HASH, resp['zaps'])

    def test_send_progress_no_zaps_without_other_cracks(self):
        """A single agent cracking a hash does NOT receive its own zap."""
        dummy, agent, task, _ = self._setup_with_two_hashes()
        chunk = self._make_chunk(dummy, task.id)
        resp = self._send_progress(dummy.token, chunk, cracks=[
            [self.CRACK_HASH, self.CRACK_PLAIN, self.CRACK_HEX, "1"],
        ])
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertNotIn(self.CRACK_HASH, resp['zaps'])

    def test_send_progress_state_aborted(self):
        """Sending progress with state=ABORTED (6) returns error 'Chunk was aborted!'."""
        dummy, agent, task, _ = self._setup_with_two_hashes()
        chunk = self._make_chunk(dummy, task.id)
        code, body = agent_request({
            "action": "sendProgress",
            "token": dummy.token,
            "chunkId": chunk['chunkId'],
            "keyspaceProgress": chunk['skip'] + 1,
            "relativeProgress": 100,
            "speed": 5700,
            "state": 6,
            "cracks": [],
        })
        assert_error_envelope(self, body, "sendProgress")
        self.assertEqual(parse_envelope(body)['message'], "Chunk was aborted!")

    def test_send_progress_state_quit(self):
        """Sending progress with state=QUIT (7) returns error 'Chunk was aborted!'."""
        dummy, agent, task, _ = self._setup_with_two_hashes()
        chunk = self._make_chunk(dummy, task.id)
        code, body = agent_request({
            "action": "sendProgress",
            "token": dummy.token,
            "chunkId": chunk['chunkId'],
            "keyspaceProgress": chunk['skip'] + 1,
            "relativeProgress": 100,
            "speed": 5700,
            "state": 7,
            "cracks": [],
        })
        assert_error_envelope(self, body, "sendProgress")
        self.assertEqual(parse_envelope(body)['message'], "Chunk was aborted!")

    def test_send_progress_on_already_aborted_chunk(self):
        """Sending progress on an already-aborted chunk returns 'Chunk was aborted!' via the aborting path."""
        dummy, agent, task, _ = self._setup_with_two_hashes()
        chunk = self._make_chunk(dummy, task.id)
        agent_request({
            "action": "sendProgress",
            "token": dummy.token,
            "chunkId": chunk['chunkId'],
            "keyspaceProgress": chunk['skip'] + 1,
            "relativeProgress": 100,
            "speed": 5700,
            "state": 6,
            "cracks": [],
        })
        code, body = agent_request({
            "action": "sendProgress",
            "token": dummy.token,
            "chunkId": chunk['chunkId'],
            "keyspaceProgress": chunk['skip'] + 2,
            "relativeProgress": 200,
            "speed": 5700,
            "state": 2,
            "cracks": [],
        })
        assert_error_envelope(self, body, "sendProgress")
        self.assertEqual(parse_envelope(body)['message'], "Chunk was aborted!")

    def test_send_progress_state_status_aborted_runtime(self):
        """Sending progress with state=STATUS_ABORTED_RUNTIME (10) returns 'Chunk was manually interrupted.'."""
        dummy, agent, task, _ = self._setup_with_two_hashes()
        chunk = self._make_chunk(dummy, task.id)
        code, body = agent_request({
            "action": "sendProgress",
            "token": dummy.token,
            "chunkId": chunk['chunkId'],
            "keyspaceProgress": chunk['skip'] + 1,
            "relativeProgress": 100,
            "speed": 5700,
            "state": 10,
            "cracks": [],
        })
        assert_error_envelope(self, body, "sendProgress")
        self.assertEqual(parse_envelope(body)['message'], "Chunk was manually interrupted.")

    def test_send_progress_state_exhausted(self):
        """Sending progress with state=EXHAUSTED (4) returns SUCCESS with cracked/skipped fields."""
        dummy, agent, task, _ = self._setup_with_two_hashes()
        chunk = self._make_chunk(dummy, task.id)
        resp = self._send_progress(dummy.token, chunk, state=4, progress=100)
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertIsInstance(resp['cracked'], int)
        self.assertIsInstance(resp['skipped'], int)

    def test_send_progress_agent_stop_on_all_cracked(self):
        """When all hashes in the hashlist are cracked the response includes agent='stop'.

        Uses a single-hash hashlist; cracking the only hash triggers the
        'all hashes cracked' path, which deprioritizes the task, unassigns all
        agents, and tells the agent to stop.
        """
        dummy, agent, task, _ = self._setup_assigned_agent()
        chunk = self._make_chunk(dummy, task.id)
        resp = self._send_progress(dummy.token, chunk, state=2, cracks=[
            [self.CRACK_HASH, self.CRACK_PLAIN, self.CRACK_HEX, "1"],
        ])
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertEqual(resp['agent'], "stop")

    def test_send_progress_invalid_chunk(self):
        """Sending progress for a non-existent chunk returns 'Invalid chunk id {id}'."""
        dummy = self._dummy()
        code, body = agent_request({
            "action": "sendProgress",
            "token": dummy.token,
            "chunkId": 99999999,
            "keyspaceProgress": 1,
            "relativeProgress": 1,
            "speed": 1,
            "state": 2,
            "cracks": [],
        })
        assert_error_envelope(self, body, "sendProgress")
        self.assertEqual(parse_envelope(body)['message'], "Invalid chunk id 99999999")

    def test_send_progress_not_assigned_to_chunk(self):
        """Sending progress for a chunk belonging to a different agent returns 'You are not assigned to this chunk'."""
        dummy_a, agent_a, task, _ = self._setup_with_two_hashes()
        chunk = self._make_chunk(dummy_a, task.id)
        dummy_b, agent_b = self._dummy_with_agent()
        code, body = agent_request({
            "action": "sendProgress",
            "token": dummy_b.token,
            "chunkId": chunk['chunkId'],
            "keyspaceProgress": chunk['skip'] + 1,
            "relativeProgress": 100,
            "speed": 5700,
            "state": 2,
            "cracks": [],
        })
        assert_error_envelope(self, body, "sendProgress")
        self.assertEqual(parse_envelope(body)['message'], "You are not assigned to this chunk")

    def test_send_progress_inactive_agent(self):
        """Sending progress from an inactive agent returns 'Agent is marked inactive!'."""
        dummy, agent, task, _ = self._setup_with_two_hashes()
        chunk = self._make_chunk(dummy, task.id)
        agent.isActive = False
        agent.save()
        code, body = agent_request({
            "action": "sendProgress",
            "token": dummy.token,
            "chunkId": chunk['chunkId'],
            "keyspaceProgress": chunk['skip'] + 1,
            "relativeProgress": 100,
            "speed": 5700,
            "state": 2,
            "cracks": [],
        })
        assert_error_envelope(self, body, "sendProgress")
        self.assertEqual(parse_envelope(body)['message'], "Agent is marked inactive!")

    def test_send_progress_invalid_token(self):
        """Sending progress with a bogus token returns 'Invalid token!'."""
        code, body = agent_request({
            "action": "sendProgress",
            "token": "bad",
            "chunkId": 1,
            "keyspaceProgress": 1,
            "relativeProgress": 1,
            "speed": 1,
            "state": 2,
            "cracks": [],
        })
        assert_error_envelope(self, body, "sendProgress")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")

    def test_send_progress_state_cracked(self):
        """Sending progress with state=CRACKED (5) returns SUCCESS (deprioritizes all tasks and unassigns all agents).

        Uses a single-hash hashlist so cracking it triggers the CRACKED state.
        """
        dummy, agent, task, _ = self._setup_assigned_agent()
        chunk = self._make_chunk(dummy, task.id)
        resp = self._send_progress(dummy.token, chunk, state=5, cracks=[
            [self.CRACK_HASH, self.CRACK_PLAIN, self.CRACK_HEX, "1"],
        ])
        self.assertEqual(resp['response'], "SUCCESS")
        self.assertIsInstance(resp['cracked'], int)
        self.assertIsInstance(resp['skipped'], int)

    def test_send_progress_missing_fields(self):
        """Sending sendProgress without required fields returns 'Invalid progress query!'."""
        dummy = self._dummy()
        code, body = agent_request({"action": "sendProgress", "token": dummy.token})
        assert_error_envelope(self, body, "sendProgress")
        self.assertEqual(parse_envelope(body)['message'], "Invalid progress query!")


# ---------------------------------------------------------------------------
# getHealthCheck / sendHealthCheck
# ---------------------------------------------------------------------------

class TestHealthCheck(AgentProtocolBase):
    """Tests for the getHealthCheck and sendHealthCheck actions — health check retrieval and results reporting."""

    def test_get_health_check_none_available(self):
        """getHealthCheck when no health check is scheduled returns 'No health check available for this agent!'."""
        dummy = self._dummy()
        code, body = agent_request({"action": "getHealthCheck", "token": dummy.token})
        assert_error_envelope(self, body, "getHealthCheck")
        self.assertEqual(parse_envelope(body)['message'], "No health check available for this agent!")

    def test_get_health_check_missing_fields(self):
        """Sending getHealthCheck without a token returns 'Invalid token!' (middleware handles missing token for PSR-7 controllers)."""
        code, body = agent_request({"action": "getHealthCheck"})
        assert_error_envelope(self, body, "getHealthCheck")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")

    def test_get_health_check_invalid_token(self):
        """Sending getHealthCheck with a bogus token returns 'Invalid token!'."""
        code, body = agent_request({"action": "getHealthCheck", "token": "bad"})
        assert_error_envelope(self, body, "getHealthCheck")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")

    def test_send_health_check_invalid_id(self):
        """Sending sendHealthCheck with a non-existent checkId returns 'Invalid health check id!'."""
        dummy = self._dummy()
        code, body = agent_request({
            "action": "sendHealthCheck",
            "token": dummy.token,
            "checkId": 99999999,
            "numCracked": 0,
            "numGpus": 1,
            "errors": [],
            "start": 1,
            "end": 2,
        })
        assert_error_envelope(self, body, "sendHealthCheck")
        self.assertEqual(parse_envelope(body)['message'], "Invalid health check id!")

    def test_send_health_check_invalid_token(self):
        """Sending sendHealthCheck with a bogus token returns 'Invalid token!'."""
        code, body = agent_request({
            "action": "sendHealthCheck",
            "token": "bad",
            "checkId": 1,
            "numCracked": 0,
            "numGpus": 1,
            "errors": [],
            "start": 1,
            "end": 2,
        })
        assert_error_envelope(self, body, "sendHealthCheck")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")

    def test_send_health_check_missing_fields(self):
        """Sending sendHealthCheck without required fields returns 'Invalid send health check query!'."""
        dummy = self._dummy()
        code, body = agent_request({"action": "sendHealthCheck", "token": dummy.token})
        assert_error_envelope(self, body, "sendHealthCheck")
        self.assertEqual(parse_envelope(body)['message'], "Invalid send health check query!")

    def test_send_health_check_success(self):
        """sendHealthCheck success path returns response='SUCCESS'.

        Creating a HealthCheck via the v2 API auto-creates HealthCheckAgent
        rows for all agents (HealthUtils::createHealthCheck), so the agent
        already has a pending HCA. Deleting the HealthCheck via the v2 API
        cascades to its HCA rows (HealthUtils::deleteHealthCheck).
        """
        dummy, agent = self._dummy_with_agent()
        hc = HealthCheck(checkType=0, crackerBinaryId=1, hashtypeId=0)
        hc.save()
        self.delete_after_test(hc)

        code, body = agent_request({
            "action": "sendHealthCheck",
            "token": dummy.token,
            "checkId": hc.id,
            "numCracked": 0,
            "numGpus": 1,
            "errors": [],
            "start": 1,
            "end": 2,
        })
        self.assertEqual(code, 200)
        resp = parse_envelope(body)
        self.assertEqual(resp['action'], "sendHealthCheck")
        self.assertEqual(resp['response'], "SUCCESS")

    def test_send_health_check_invalid_health_check_agent_id(self):
        """HealthCheck exists but no HealthCheckAgent row for this agent returns 'Invalid health check agent id!'.

        We create the HealthCheck FIRST (which auto-creates HCA rows for all
        existing agents), then register the dummy agent AFTER. Since the agent
        didn't exist when the HC was created, there is no HCA row for it.
        """
        hc = HealthCheck(checkType=0, crackerBinaryId=1, hashtypeId=0)
        hc.save()
        self.delete_after_test(hc)
        voucher = do_create_voucher()
        self.delete_after_test(voucher)
        dummy = DummyAgent()
        dummy.register(voucher=voucher.voucher, name='hca-invalid-test')
        self.delete_after_test(Agent.objects.get(agentName='hca-invalid-test'))
        code, body = agent_request({
            "action": "sendHealthCheck",
            "token": dummy.token,
            "checkId": hc.id,
            "numCracked": 0,
            "numGpus": 1,
            "errors": [],
            "start": 1,
            "end": 2,
        })
        assert_error_envelope(self, body, "sendHealthCheck")
        self.assertEqual(parse_envelope(body)['message'], "Invalid health check agent id!")


# ---------------------------------------------------------------------------
# deregister
# ---------------------------------------------------------------------------

class TestDeregister(AgentProtocolBase):
    """Tests for the deregister action — agent self-deletion (gated by allowDeregister config)."""

    def test_deregister_success(self):
        """Deregistering with allowDeregister enabled returns SUCCESS.

        The config is toggled on for the duration of the test, then restored.
        """
        config = Config.objects.get(item='allowDeregister')
        original = config.value
        config.value = "1"
        config.save()
        try:
            voucher = do_create_voucher()
            self.delete_after_test(voucher)
            dummy = DummyAgent()
            dummy.register(voucher=voucher.voucher, name='protocol-test-deregister')
            code, body = agent_request({"action": "deregister", "token": dummy.token})
            self.assertEqual(code, 200)
            self.assertEqual(parse_envelope(body), {
                "action": "deregister",
                "response": "SUCCESS",
            })
        finally:
            config.value = original
            config.save()

    def test_deregister_not_allowed(self):
        """Deregistering with allowDeregister disabled returns 'De-registration is not allowed on this server!'."""
        config = Config.objects.get(item='allowDeregister')
        original = config.value
        config.value = "0"
        config.save()
        try:
            dummy = self._dummy()
            code, body = agent_request({"action": "deregister", "token": dummy.token})
            assert_error_envelope(self, body, "deregister")
            self.assertEqual(parse_envelope(body)['message'],
                             "De-registration is not allowed on this server!")
        finally:
            config.value = original
            config.save()

    def test_deregister_invalid_token(self):
        """Deregistering with a bogus token returns 'Invalid token!'."""
        code, body = agent_request({"action": "deregister", "token": "bad"})
        assert_error_envelope(self, body, "deregister")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")

    def test_deregister_missing_fields(self):
        """Sending deregister without a token returns 'Invalid token!' (middleware handles missing token for PSR-7 controllers)."""
        code, body = agent_request({"action": "deregister"})
        assert_error_envelope(self, body, "deregister")
        self.assertEqual(parse_envelope(body)['message'], "Invalid token!")
