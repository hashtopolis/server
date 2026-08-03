import base64
import json
import time

from hashtopolis import Hash, HashType

from utils import BaseTest, do_create_agentassignent, do_create_dummy_agent, request_with_api_token


def _resource_payload(resource_type, attributes, resource_id=None):
    data = {
        'type': resource_type,
        'attributes': attributes,
    }
    if resource_id is not None:
        data['id'] = resource_id
    return {'data': data}


def _hashtype_attributes(hash_type_id, description='Permission Test HashType'):
    return {
        'hashTypeId': hash_type_id,
        'description': description,
        'isSalted': False,
        'isSlowHash': False,
    }


def _decode_jwt_scope(token):
    payload_b64 = token.split('.')[1]
    payload_b64 += '=' * (-len(payload_b64) % 4)
    payload = json.loads(base64.urlsafe_b64decode(payload_b64))
    return json.loads(payload['scope'])


def _all_scopes_except(test, excluded):
    scope_template = _decode_jwt_scope(test.create_apitoken(extra_payload={'scopes': []}).token)
    return [permission for permission in scope_template if permission not in excluded]


AGENT_INCLUDE_PERMISSIONS = {
    'accessGroups': 'permAccessGroupRead',
    'tasks': 'permTaskRead',
    'assignments': 'permAgentAssignmentRead',
    'user': 'permUserRead',
    'agentStats': 'permAgentStatRead',
}


ASSIGNMENT_AGGREGATES = 'crackingTime,currentChunkId,searched,currentSpeed,cracked'

TASK_WRAPPER_DISPLAY_AGGREGATES = 'totalAssignedAgents,searched,dispatched,status,currentSpeed'

HELPER_PERMISSION_CASES = [
    {
        'name': 'createSupertask',
        'path': '/helper/createSupertask',
        'payload': {'supertaskTemplateId': 1, 'hashlistId': 1, 'crackerVersionId': 1},
        'permissions': [
            'permTaskWrapperCreate',
            'permTaskCreate',
            'permSupertaskRead',
            'permHashlistRead',
            'permCrackerBinaryRead',
        ],
    },
    {
        'name': 'createSuperHashlist',
        'path': '/helper/createSuperHashlist',
        'payload': {'hashlistIds': [1], 'name': 'Permission Test Superhashlist'},
        'permissions': ['permHashlistCreate', 'permHashlistRead', 'permHashlistHashlistCreate'],
    },
    {
        'name': 'exportCrackedHashes',
        'path': '/helper/exportCrackedHashes',
        'payload': {'hashlistId': 1},
        'permissions': ['permHashlistRead', 'permHashRead', 'permFileCreate'],
    },
    {
        'name': 'exportLeftHashes',
        'path': '/helper/exportLeftHashes',
        'payload': {'hashlistId': 1},
        'permissions': ['permHashlistRead', 'permHashRead', 'permFileCreate'],
    },
    {
        'name': 'exportWordlist',
        'path': '/helper/exportWordlist',
        'payload': {'hashlistId': 1},
        'permissions': ['permHashlistRead', 'permHashRead', 'permFileCreate'],
    },
    {
        'name': 'assignAgent',
        'path': '/helper/assignAgent',
        'payload': {'agentId': 1, 'taskId': 1},
        'permissions': ['permAgentUpdate', 'permTaskUpdate'],
    },
    {
        'name': 'abortChunk',
        'path': '/helper/abortChunk',
        'payload': {'chunkId': 1},
        'permissions': ['permChunkUpdate'],
    },
    {
        'name': 'getTaskProgressImage',
        'path': '/helper/getTaskProgressImage?task=1',
        'method': 'GET',
        'payload': None,
        'permissions': ['permTaskRead', 'permTaskWrapperRead'],
    },
]


class PermissionsTest(BaseTest):
    def test_api_token_agent_read_scope(self):
        """API tokens must need permAgentRead to list agents.

        The allowed branch proves a token with only the direct agent read scope can read
        the collection. The denied branch proves an unrelated read scope does not grant
        access and that the error names the missing agent permission.
        """
        allowed_token = self.create_apitoken(extra_payload={'scopes': ['permAgentRead']})
        allowed_response = request_with_api_token(
            allowed_token.token,
            '/ui/agents?page[size]=1',
        )
        self.assertEqual(allowed_response.status_code, 200, allowed_response.text)
        self.assertIn('data', allowed_response.json())

        denied_token = self.create_apitoken(extra_payload={'scopes': ['permHashlistRead']})
        denied_response = request_with_api_token(
            denied_token.token,
            '/ui/agents?page[size]=1',
        )
        self.assertEqual(denied_response.status_code, 403, denied_response.text)
        self.assertIn('permAgentRead', denied_response.text)

    def test_api_token_agent_includes_report_missing_read_scopes(self):
        """Agent list includes validate each related model's read permission.

        The request mirrors the frontend's broad agents table include list. Each subtest
        removes one include permission from an otherwise fully-scoped token and checks
        that the API reports that missing permission while still returning the base agent
        collection. The user include is the intentional exception: users have public
        attributes, so missing permUserRead is reduced to public-only output instead of
        an include error.
        """
        include_path = '/ui/agents?include=accessGroups,tasks,assignments,user,agentStats&page[size]=1'

        for relationship, permission in AGENT_INCLUDE_PERMISSIONS.items():
            with self.subTest(relationship=relationship):
                token = self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, [permission])})

                response = request_with_api_token(token.token, include_path)
                self.assertEqual(response.status_code, 200, response.text)
                body = response.json()
                self.assertIn('data', body)
                if relationship == 'user':
                    self.assertNotIn(permission, json.dumps(body['meta']))
                else:
                    self.assertIn(permission, json.dumps(body['meta']))

        allowed_token = self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, [])})
        allowed_response = request_with_api_token(allowed_token.token, include_path)
        self.assertEqual(allowed_response.status_code, 200, allowed_response.text)
        self.assertEqual(allowed_response.json()['meta']['Include errors'], [])

    def test_api_token_agent_user_include_filters_private_attributes(self):
        """Agent include=user falls back to public user attributes without permUserRead.

        Existing agent fixtures register without a user, so the test assigns a created
        user to the agent using the same patch pattern as the agent tests. It then proves
        that include=user still appears with only User.public attributes when permUserRead
        is absent, and includes private/read attributes once permUserRead is present.
        """
        agent = self.create_agent()
        user = self.create_user()
        agent.userId = user.id
        agent.save()

        public_only_token = self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, ['permUserRead'])})
        public_only_response = request_with_api_token(
            public_only_token.token,
            f'/ui/agents/{agent.id}?include=user',
        )
        self.assertEqual(public_only_response.status_code, 200, public_only_response.text)
        public_only_body = public_only_response.json()
        self.assertEqual(public_only_body['included'][0]['type'], 'user')
        self.assertEqual(set(public_only_body['included'][0]['attributes']), {'name'})

        allowed_token = self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, [])})
        allowed_response = request_with_api_token(
            allowed_token.token,
            f'/ui/agents/{agent.id}?include=user',
        )
        self.assertEqual(allowed_response.status_code, 200, allowed_response.text)
        allowed_attributes = allowed_response.json()['included'][0]['attributes']
        self.assertIn('name', allowed_attributes)
        self.assertIn('email', allowed_attributes)
        self.assertIn('isValid', allowed_attributes)

    def _assignment_query_path(self, assignment):
        return (
            f'/ui/agentassignments?filter[agentId]={assignment.agentId}'
            f'&filter[taskId]={assignment.taskId}'
            f'&include=agent,task&aggregate[assignment]={ASSIGNMENT_AGGREGATES}&page[size]=1'
        )

    def test_api_token_assignment_include_aggregate_request_allowed(self):
        """Assignment list requests can combine filters, includes, and aggregates.

        This is the fully-authorized happy path for the frontend-style assignment query:
        filter by the created assignment's agent/task, include both related resources,
        and request all assignment aggregate fields used by the UI. It verifies the
        filtered row is returned, aggregate attributes are present, and both includes are
        materialized without include errors.
        """
        assignment = self.create_agentassignment()
        token = self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, [])})

        response = request_with_api_token(token.token, self._assignment_query_path(assignment))
        self.assertEqual(response.status_code, 200, response.text)
        body = response.json()
        self.assertEqual(body['meta']['page']['total_elements'], 1)
        self.assertEqual(body['meta']['Include errors'], [])
        attributes = body['data'][0]['attributes']
        self.assertEqual(attributes['agentId'], assignment.agentId)
        self.assertEqual(attributes['taskId'], assignment.taskId)
        self.assertIn('crackingTime', attributes)
        self.assertIn('searched', attributes)
        self.assertIn('currentSpeed', attributes)
        self.assertIn('cracked', attributes)
        self.assertEqual({item['type'] for item in body['included']}, {'agent', 'task'})

    def test_api_token_assignment_include_aggregate_request_requires_base_read(self):
        """Assignment queries still require the base Assignment read permission.

        Even though the request also asks for agent/task includes and aggregate fields,
        omitting permAgentAssignmentRead must deny the whole request with 403. This locks
        down that include or aggregate permissions cannot bypass base model read access.
        """
        assignment = self.create_agentassignment()
        token = self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, ['permAgentAssignmentRead'])})

        response = request_with_api_token(token.token, self._assignment_query_path(assignment))
        self.assertEqual(response.status_code, 403, response.text)
        self.assertIn('permAgentAssignmentRead', response.text)

    def test_api_token_assignment_include_aggregate_request_reports_missing_include_scopes(self):
        """Assignment includes are independently omitted when their read scope is absent.

        The base assignment read permission remains available, so the list request and
        aggregate data still succeed. Each subtest removes either permAgentRead or
        permTaskRead and verifies the corresponding include is omitted and reported in
        response metadata.
        """
        assignment = self.create_agentassignment()
        include_permissions = {
            'agent': 'permAgentRead',
            'task': 'permTaskRead',
        }

        for relationship, permission in include_permissions.items():
            with self.subTest(relationship=relationship):
                token = self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, [permission])})

                response = request_with_api_token(token.token, self._assignment_query_path(assignment))
                self.assertEqual(response.status_code, 200, response.text)
                body = response.json()
                self.assertEqual(body['meta']['page']['total_elements'], 1)
                self.assertIn(permission, json.dumps(body['meta']))
                included_types = {item['type'] for item in body.get('included', [])}
                self.assertNotIn(relationship, included_types)

    def _task_wrapper_display_query_path(self, task):
        return (
            f'/ui/taskwrapperdisplays?filter[taskWrapperId__eq]={task.taskWrapperId}'
            f'&filter[taskWrapperIsArchived__eq]=false'
            f'&aggregate[taskwrapperdisplay]={TASK_WRAPPER_DISPLAY_AGGREGATES}&page[size]=1'
        )

    def test_api_token_task_wrapper_display_aggregate_request_allowed(self):
        """TaskWrapperDisplay aggregate requests use custom endpoint permissions.

        TaskWrapperDisplayAPI overrides its GET permission to require permTaskRead and
        permTaskWrapperRead instead of the model's own permTaskWrapperDisplayRead. This
        fully-authorized request mirrors the dashboard query, filters to a created normal
        task wrapper, and verifies that the expected aggregate dashboard fields are
        returned.
        """
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist)
        token = self.create_apitoken(extra_payload={'scopes': ['permTaskRead', 'permTaskWrapperRead']})

        response = request_with_api_token(token.token, self._task_wrapper_display_query_path(task))
        self.assertEqual(response.status_code, 200, response.text)
        body = response.json()
        self.assertEqual(body['meta']['page']['total_elements'], 1)
        attributes = body['data'][0]['attributes']
        self.assertEqual(body['data'][0]['id'], task.taskWrapperId)
        self.assertEqual(attributes['taskWrapperIsArchived'], False)
        self.assertIn('totalAssignedAgents', attributes)
        self.assertIn('searched', attributes)
        self.assertIn('dispatched', attributes)
        self.assertIn('status', attributes)
        self.assertIn('currentSpeed', attributes)

    def test_api_token_task_wrapper_display_aggregate_request_requires_task_and_wrapper_read(self):
        """Both custom TaskWrapperDisplay GET permissions are required.

        Each subtest removes one of the overridden required permissions from an otherwise
        fully-scoped token. Missing either permTaskRead or permTaskWrapperRead must deny
        the full dashboard aggregate request with 403, proving the endpoint treats these
        as base permissions and not optional aggregate permissions.
        """
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist)

        for permission in ['permTaskRead', 'permTaskWrapperRead']:
            with self.subTest(permission=permission):
                token = self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, [permission])})

                response = request_with_api_token(token.token, self._task_wrapper_display_query_path(task))
                self.assertEqual(response.status_code, 403, response.text)
                self.assertIn(permission, response.text)

    def test_api_token_task_wrapper_display_model_read_scope_is_not_sufficient(self):
        """The model read scope does not bypass TaskWrapperDisplayAPI's override.

        Because the endpoint has custom required permissions, a token with only
        permTaskWrapperDisplayRead should still be denied. This protects against future
        regressions where generic model CRUD permissions might accidentally replace the
        endpoint-specific Task and TaskWrapper read requirements.
        """
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist)
        token = self.create_apitoken(extra_payload={'scopes': ['permTaskWrapperDisplayRead']})

        response = request_with_api_token(token.token, self._task_wrapper_display_query_path(task))
        self.assertEqual(response.status_code, 403, response.text)
        self.assertIn('permTaskRead', response.text)
        self.assertIn('permTaskWrapperRead', response.text)

    def _hashlist_include_query_path(self, hashlist):
        return (
            f'/ui/hashlists?filter[hashlistId__eq]={hashlist.id}'
            f'&filter[isArchived__eq]=false&include=hashType,accessGroup&page[size]=1'
        )

    def _hash_include_query_path(self, hashlist):
        return f'/ui/hashes?filter[hashlistId__eq]={hashlist.id}&include=hashlist,chunk&page[size]=1'

    def _hash_include_query_path_by_hash(self, hash_obj):
        return f'/ui/hashes?filter[hashId__eq]={hash_obj.id}&include=hashlist,chunk&page[size]=1'

    def _create_cracked_hash_with_chunk(self):
        source_data = base64.b64encode(
            b'7fde65673fd28736423f23423786f\n7fde65673f28987f7423f2342378f\n'
        ).decode()
        hashlist = self.create_hashlist(extra_payload={'sourceData': source_data})
        task = self.create_task(hashlist=hashlist, file_id='004')
        dummy_agent, agent = do_create_dummy_agent()
        self.delete_after_test(agent)
        do_create_agentassignent(agent, task)

        dummy_agent.get_task()
        dummy_agent.get_hashlist()
        dummy_agent.get_chunk()
        while dummy_agent.chunk['status'] != 'OK':
            status = dummy_agent.chunk['status']
            if status == 'keyspace_required':
                dummy_agent.send_keyspace(keyspace=56800)
            elif status == 'benchmark':
                dummy_agent.send_benchmark()
            else:
                raise AssertionError(f'Unexpected chunk status: {status}')
            dummy_agent.get_chunk()

        dummy_agent.send_process(progress=50)
        hash_obj = Hash.objects.filter(hashlistId=hashlist.id)[0]
        self.assertIsNotNone(hash_obj.chunkId)
        return hash_obj

    def test_api_token_hashlist_include_request_allowed(self):
        """Hashlist table requests can include hashType and accessGroup.

        This mirrors the common frontend hashlist table flow with filters for a concrete
        non-archived hashlist and includes for both related display columns. With all
        required read permissions present, the hashlist row is returned and both related
        resources are materialized without include errors.
        """
        hashlist = self.create_hashlist()
        token = self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, [])})

        response = request_with_api_token(token.token, self._hashlist_include_query_path(hashlist))
        self.assertEqual(response.status_code, 200, response.text)
        body = response.json()
        self.assertEqual(body['meta']['page']['total_elements'], 1)
        self.assertEqual(body['meta']['Include errors'], [])
        self.assertEqual(body['data'][0]['id'], hashlist.id)
        self.assertEqual(body['data'][0]['attributes']['isArchived'], False)
        self.assertEqual({item['type'] for item in body['included']}, {'hashType', 'accessGroup'})

    def test_api_token_hashlist_include_request_requires_base_read(self):
        """Hashlist include requests still require permHashlistRead on the base model.

        Even when the token has every include permission, omitting permHashlistRead must
        deny the entire hashlist table request with 403. This ensures include permissions
        cannot be used to bypass the hashlist model read permission.
        """
        hashlist = self.create_hashlist()
        token = self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, ['permHashlistRead'])})

        response = request_with_api_token(token.token, self._hashlist_include_query_path(hashlist))
        self.assertEqual(response.status_code, 403, response.text)
        self.assertIn('permHashlistRead', response.text)

    def test_api_token_hashlist_include_request_reports_missing_include_scopes(self):
        """Hashlist includes are independently omitted when their read scope is absent.

        The base hashlist read permission remains present, so the table request succeeds.
        Each subtest removes one include permission and verifies the API reports the
        missing related-model scope and omits only that relationship's included resource.
        """
        hashlist = self.create_hashlist()
        include_permissions = {
            'hashType': 'permHashTypeRead',
            'accessGroup': 'permAccessGroupRead',
        }

        for relationship, permission in include_permissions.items():
            with self.subTest(relationship=relationship):
                token = self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, [permission])})

                response = request_with_api_token(token.token, self._hashlist_include_query_path(hashlist))
                self.assertEqual(response.status_code, 200, response.text)
                body = response.json()
                self.assertEqual(body['meta']['page']['total_elements'], 1)
                self.assertIn(permission, json.dumps(body['meta']))
                included_types = {item['type'] for item in body.get('included', [])}
                self.assertNotIn(relationship, included_types)

    def test_api_token_hash_include_request_allowed(self):
        """Hashes table requests can include the parent hashlist and optional chunk.

        A newly-created hashlist creates hash rows with no chunk yet, matching hashes that
        have not been assigned to cracking work. With full permissions, the request returns
        the hash row, includes its parent hashlist, and reports no include error even though
        the chunk relationship is null and therefore not materialized.
        """
        hashlist = self.create_hashlist()
        hash_obj = Hash.objects.filter(hashlistId=hashlist.id)[0]
        token = self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, [])})

        response = request_with_api_token(token.token, self._hash_include_query_path(hashlist))
        self.assertEqual(response.status_code, 200, response.text)
        body = response.json()
        self.assertEqual(body['meta']['page']['total_elements'], 1)
        self.assertEqual(body['meta']['Include errors'], [])
        self.assertEqual(body['data'][0]['id'], hash_obj.id)
        self.assertEqual(body['data'][0]['relationships']['chunk']['data'], None)
        self.assertEqual({item['type'] for item in body.get('included', [])}, {'hashlist'})

    def test_api_token_hash_include_request_requires_base_read(self):
        """Hashes include requests still require permHashRead on the base model.

        The hashlist and chunk include permissions do not grant access to the hash rows
        themselves. Removing permHashRead must deny the complete hashes table request with
        403 before include materialization matters.
        """
        hashlist = self.create_hashlist()
        token = self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, ['permHashRead'])})

        response = request_with_api_token(token.token, self._hash_include_query_path(hashlist))
        self.assertEqual(response.status_code, 403, response.text)
        self.assertIn('permHashRead', response.text)

    def test_api_token_hash_include_request_reports_missing_include_scopes(self):
        """Hash includes are independently permission-checked for hashlist and chunk.

        The base hash read permission remains present. Removing permHashlistRead omits the
        parent hashlist include, while removing permChunkRead reports the missing chunk
        permission even though the fixture hash currently has a null chunk relationship.
        """
        hashlist = self.create_hashlist()
        include_permissions = {
            'hashlist': 'permHashlistRead',
            'chunk': 'permChunkRead',
        }

        for relationship, permission in include_permissions.items():
            with self.subTest(relationship=relationship):
                token = self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, [permission])})

                response = request_with_api_token(token.token, self._hash_include_query_path(hashlist))
                self.assertEqual(response.status_code, 200, response.text)
                body = response.json()
                self.assertEqual(body['meta']['page']['total_elements'], 1)
                self.assertIn(permission, json.dumps(body['meta']))
                included_types = {item['type'] for item in body.get('included', [])}
                self.assertNotIn(relationship, included_types)

    def test_api_token_hash_chunk_include_materializes_only_with_chunk_read_scope(self):
        """Hash include=chunk materializes a real chunk only with permChunkRead.

        Most freshly-created hashes have chunk=null, so they only prove include permission
        validation. This test drives the existing dummy-agent protocol through chunk
        dispatch and matching crack submission, which updates the cracked hash with a real
        chunkId. The allowed request must include both hashlist and chunk resources; the
        denied request must still return the hash row but omit chunk and report
        permChunkRead in metadata.
        """
        hash_obj = self._create_cracked_hash_with_chunk()

        allowed_token = self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, [])})
        allowed_response = request_with_api_token(allowed_token.token, self._hash_include_query_path_by_hash(hash_obj))
        self.assertEqual(allowed_response.status_code, 200, allowed_response.text)
        allowed_body = allowed_response.json()
        self.assertEqual(allowed_body['meta']['Include errors'], [])
        self.assertEqual(allowed_body['data'][0]['id'], hash_obj.id)
        self.assertEqual(allowed_body['data'][0]['attributes']['chunkId'], hash_obj.chunkId)
        self.assertEqual({item['type'] for item in allowed_body['included']}, {'hashlist', 'chunk'})

        denied_token = self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, ['permChunkRead'])})
        denied_response = request_with_api_token(denied_token.token, self._hash_include_query_path_by_hash(hash_obj))
        self.assertEqual(denied_response.status_code, 200, denied_response.text)
        denied_body = denied_response.json()
        self.assertIn('permChunkRead', json.dumps(denied_body['meta']))
        self.assertEqual(denied_body['data'][0]['id'], hash_obj.id)
        self.assertEqual(denied_body['data'][0]['attributes']['chunkId'], hash_obj.chunkId)
        self.assertNotIn('chunk', {item['type'] for item in denied_body.get('included', [])})

    def test_api_token_high_value_helpers_report_each_missing_required_scope(self):
        """High-value helper endpoints enforce every declared required permission.

        Helper permission checks run before payload validation or action execution, so the
        test can safely use minimal dummy payloads without creating side effects. Each
        subtest removes exactly one required scope from an otherwise fully-scoped token and
        verifies the helper returns 403 naming that missing permission. This covers the
        multi-permission helpers from the priority list: createSupertask, hash exports,
        assignAgent, and abortChunk.
        """
        for helper in HELPER_PERMISSION_CASES:
            for permission in helper['permissions']:
                with self.subTest(helper=helper['name'], permission=permission):
                    token = self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, [permission])})

                    response = request_with_api_token(
                        token.token,
                        helper['path'],
                        method=helper.get('method', 'POST'),
                        payload=helper['payload'],
                    )
                    self.assertEqual(response.status_code, 403, response.text)
                    self.assertIn(permission, response.text)

    def test_api_token_assign_agent_helper_allowed_with_update_scopes(self):
        """assignAgent helper succeeds with both Agent and Task update scopes.

        The missing-permission matrix proves the helper denies absent scopes before action
        execution. This end-to-end branch creates real agent/task fixtures and verifies a
        token with exactly permAgentUpdate and permTaskUpdate can execute the helper and
        receives the expected success metadata.
        """
        agent = self.create_agent()
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist)
        token = self.create_apitoken(extra_payload={'scopes': ['permAgentUpdate', 'permTaskUpdate']})

        response = request_with_api_token(
            token.token,
            '/helper/assignAgent',
            method='POST',
            payload={'agentId': agent.id, 'taskId': task.id},
        )
        self.assertEqual(response.status_code, 200, response.text)
        self.assertEqual(response.json()['meta']['Assign'], 'Success')

    def _relationship_payload(self, resource_type, resource_id):
        return {'data': [{'type': resource_type, 'id': resource_id}]}

    def _create_unique_accessgroup(self):
        return self.create_accessgroup(extra_payload={'groupName': f'Permission Group {time.time_ns()}'})

    def test_api_token_access_group_user_member_relationship_add_remove_allowed(self):
        """Access group user membership can be added and removed through relationship links.

        This covers the non-CRUD JSON:API relationship mutation path for userMembers.
        The current generic relationship route maps POST to the parent model create scope
        and DELETE to the parent model delete scope, so the test uses those exact scopes
        and verifies the membership appears after POST and disappears after DELETE.
        """
        group = self._create_unique_accessgroup()
        user = self.create_user()
        payload = self._relationship_payload('user', user.id)

        add_response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': ['permAccessGroupCreate']}).token,
            f'/ui/accessgroups/{group.id}/relationships/userMembers',
            method='POST',
            payload=payload,
        )
        self.assertEqual(add_response.status_code, 201, add_response.text)

        get_response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': ['permAccessGroupRead', 'permUserRead']}).token,
            f'/ui/accessgroups/{group.id}/relationships/userMembers',
        )
        self.assertEqual(get_response.status_code, 200, get_response.text)
        self.assertIn(user.id, [item['id'] for item in get_response.json()['data']])

        delete_response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': ['permAccessGroupDelete']}).token,
            f'/ui/accessgroups/{group.id}/relationships/userMembers',
            method='DELETE',
            payload=payload,
        )
        self.assertEqual(delete_response.status_code, 201, delete_response.text)

        get_after_delete_response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': ['permAccessGroupRead', 'permUserRead']}).token,
            f'/ui/accessgroups/{group.id}/relationships/userMembers',
        )
        self.assertEqual(get_after_delete_response.status_code, 200, get_after_delete_response.text)
        self.assertNotIn(user.id, [item['id'] for item in get_after_delete_response.json()['data']])

    def test_api_token_access_group_agent_member_relationship_add_remove_allowed(self):
        """Access group agent membership can be added and removed through relationship links.

        This mirrors the userMembers relationship test for agentMembers and uses a real
        dummy-agent fixture. It confirms POST creates the junction-table membership and
        DELETE removes it through the generic relationship endpoints.
        """
        group = self._create_unique_accessgroup()
        agent = self.create_agent()
        payload = self._relationship_payload('agent', agent.id)

        add_response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': ['permAccessGroupCreate']}).token,
            f'/ui/accessgroups/{group.id}/relationships/agentMembers',
            method='POST',
            payload=payload,
        )
        self.assertEqual(add_response.status_code, 201, add_response.text)

        get_response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': ['permAccessGroupRead', 'permAgentRead']}).token,
            f'/ui/accessgroups/{group.id}/relationships/agentMembers',
        )
        self.assertEqual(get_response.status_code, 200, get_response.text)
        self.assertIn(agent.id, [item['id'] for item in get_response.json()['data']])

        delete_response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': ['permAccessGroupDelete']}).token,
            f'/ui/accessgroups/{group.id}/relationships/agentMembers',
            method='DELETE',
            payload=payload,
        )
        self.assertEqual(delete_response.status_code, 201, delete_response.text)

        get_after_delete_response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': ['permAccessGroupRead', 'permAgentRead']}).token,
            f'/ui/accessgroups/{group.id}/relationships/agentMembers',
        )
        self.assertEqual(get_after_delete_response.status_code, 200, get_after_delete_response.text)
        self.assertNotIn(agent.id, [item['id'] for item in get_after_delete_response.json()['data']])

    def test_api_token_access_group_member_relationship_post_requires_create_scope(self):
        """POST relationship mutations currently require permAccessGroupCreate.

        The generic relationship route validates permissions before resource lookup or
        payload mutation. Each subtest removes the create scope and verifies adding either
        userMembers or agentMembers is denied with 403 and names permAccessGroupCreate.
        """
        group = self._create_unique_accessgroup()
        user = self.create_user()
        agent = self.create_agent()
        cases = {
            'userMembers': self._relationship_payload('user', user.id),
            'agentMembers': self._relationship_payload('agent', agent.id),
        }

        for relationship, payload in cases.items():
            with self.subTest(relationship=relationship):
                response = request_with_api_token(
                    self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, ['permAccessGroupCreate'])}).token,
                    f'/ui/accessgroups/{group.id}/relationships/{relationship}',
                    method='POST',
                    payload=payload,
                )
                self.assertEqual(response.status_code, 403, response.text)
                self.assertIn('permAccessGroupCreate', response.text)

    def test_api_token_access_group_member_relationship_delete_requires_delete_scope(self):
        """DELETE relationship mutations currently require permAccessGroupDelete.

        The relationships are first seeded with full permissions. Each subtest then tries
        to remove a membership without the delete scope and verifies the relationship route
        denies the mutation before deleting the junction-table row.
        """
        group = self._create_unique_accessgroup()
        user = self.create_user()
        agent = self.create_agent()
        cases = {
            'userMembers': self._relationship_payload('user', user.id),
            'agentMembers': self._relationship_payload('agent', agent.id),
        }
        seed_token = self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, [])})
        for relationship, payload in cases.items():
            seed_response = request_with_api_token(
                seed_token.token,
                f'/ui/accessgroups/{group.id}/relationships/{relationship}',
                method='POST',
                payload=payload,
            )
            self.assertEqual(seed_response.status_code, 201, seed_response.text)

        for relationship, payload in cases.items():
            with self.subTest(relationship=relationship):
                response = request_with_api_token(
                    self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, ['permAccessGroupDelete'])}).token,
                    f'/ui/accessgroups/{group.id}/relationships/{relationship}',
                    method='DELETE',
                    payload=payload,
                )
                self.assertEqual(response.status_code, 403, response.text)
                self.assertIn('permAccessGroupDelete', response.text)

    def test_api_token_access_group_member_relationship_patch_requires_update_scope(self):
        """PATCH relationship mutations currently require permAccessGroupUpdate.

        The generic relationship route maps PATCH to the parent model update permission.
        These access group relationships use junction tables, so full replacement is not
        the useful mutation path here, but the permission gate should still reject missing
        update scope before it reaches relationship update logic.
        """
        group = self._create_unique_accessgroup()
        user = self.create_user()
        agent = self.create_agent()
        cases = {
            'userMembers': self._relationship_payload('user', user.id),
            'agentMembers': self._relationship_payload('agent', agent.id),
        }

        for relationship, payload in cases.items():
            with self.subTest(relationship=relationship):
                response = request_with_api_token(
                    self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, ['permAccessGroupUpdate'])}).token,
                    f'/ui/accessgroups/{group.id}/relationships/{relationship}',
                    method='PATCH',
                    payload=payload,
                )
                self.assertEqual(response.status_code, 403, response.text)
                self.assertIn('permAccessGroupUpdate', response.text)

    def test_api_token_access_group_member_relationship_patch_junction_replacement_is_not_supported(self):
        """PATCH replacement is not supported for access group junction relationships.

        POST and DELETE are the supported mutation paths for userMembers and agentMembers.
        With the required update scope present, PATCH reaches the generic replacement
        implementation, which cannot mutate these immutable junction-table foreign keys and
        returns a failure instead of replacing the membership set.
        """
        group = self._create_unique_accessgroup()
        user = self.create_user()
        agent = self.create_agent()
        cases = {
            'userMembers': self._relationship_payload('user', user.id),
            'agentMembers': self._relationship_payload('agent', agent.id),
        }

        for relationship, payload in cases.items():
            with self.subTest(relationship=relationship):
                response = request_with_api_token(
                    self.create_apitoken(extra_payload={'scopes': ['permAccessGroupUpdate']}).token,
                    f'/ui/accessgroups/{group.id}/relationships/{relationship}',
                    method='PATCH',
                    payload=payload,
                )
                self.assertEqual(response.status_code, 403, response.text)
                self.assertIn('immutable', response.text)

    def test_api_token_readonly_taskwrapperdisplay_tasks_relationship_post_patch_denied(self):
        """Readonly taskwrapperdisplay tasks relationship rejects POST and PATCH.

        TaskWrapperDisplayAPI marks the tasks relationship as readonly. With sufficient
        parent-model permissions, POST and PATCH both reach the readonly guard and return a
        400 error instead of creating or replacing task relationships.
        """
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist)
        payload = self._relationship_payload('task', task.id)

        for method in ['POST', 'PATCH']:
            with self.subTest(method=method):
                response = request_with_api_token(
                    self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, [])}).token,
                    f'/ui/taskwrapperdisplays/{task.taskWrapperId}/relationships/tasks',
                    method=method,
                    payload=payload,
                )
                self.assertEqual(response.status_code, 400, response.text)
                self.assertIn('readonly', response.text)

    def test_api_token_readonly_taskwrapperdisplay_tasks_relationship_delete_denied(self):
        """Readonly taskwrapperdisplay tasks relationship rejects DELETE mutations.

        DELETE should use the same readonly guard as POST and PATCH, so all write methods
        consistently reject attempts to mutate TaskWrapperDisplayAPI's readonly tasks
        relationship before touching task foreign keys.
        """
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist)

        response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': _all_scopes_except(self, [])}).token,
            f'/ui/taskwrapperdisplays/{task.taskWrapperId}/relationships/tasks',
            method='DELETE',
            payload=self._relationship_payload('task', task.id),
        )
        self.assertEqual(response.status_code, 400, response.text)
        self.assertIn('readonly', response.text)

    def test_api_token_user_read_scope_public_attributes(self):
        """User reads degrade to public attributes when permUserRead is missing.

        A token with permUserRead receives normal user attributes such as email. A token
        with only an unrelated scope still receives a 200 response, but only User.public
        attributes are serialized. This documents the read-permission reduction behavior
        for models with public fields.
        """
        allowed_token = self.create_apitoken(extra_payload={'scopes': ['permUserRead']})
        allowed_response = request_with_api_token(
            allowed_token.token,
            '/ui/users?page[size]=1',
        )
        self.assertEqual(allowed_response.status_code, 200, allowed_response.text)
        allowed_attributes = allowed_response.json()['data'][0]['attributes']
        self.assertIn('name', allowed_attributes)
        self.assertIn('email', allowed_attributes)

        public_only_token = self.create_apitoken(extra_payload={'scopes': ['permHashlistRead']})
        public_only_response = request_with_api_token(
            public_only_token.token,
            '/ui/users?page[size]=1',
        )
        self.assertEqual(public_only_response.status_code, 200, public_only_response.text)
        public_only_attributes = public_only_response.json()['data'][0]['attributes']
        self.assertEqual(set(public_only_attributes), {'name'})

    def test_api_token_user_public_attributes_with_denied_global_permission_group_include(self):
        """Users plus globalPermissionGroup include with neither relevant read scope.

        Without permUserRead the base user row is reduced to public attributes only.
        Without permRightGroupRead the globalPermissionGroup include is omitted and the
        missing include permission is reported in metadata rather than failing the whole
        list request.
        """
        scopes = _all_scopes_except(self, ['permUserRead', 'permRightGroupRead'])
        token = self.create_apitoken(extra_payload={'scopes': scopes})

        response = request_with_api_token(
            token.token,
            '/ui/users?include=globalPermissionGroup&page[size]=1',
        )
        self.assertEqual(response.status_code, 200, response.text)
        body = response.json()
        user_attributes = body['data'][0]['attributes']
        self.assertEqual(set(user_attributes), {'name'})
        self.assertNotIn('included', body)
        self.assertIn('permRightGroupRead', json.dumps(body['meta']))

    def test_api_token_user_read_attributes_with_denied_global_permission_group_include(self):
        """Full user data is returned while a denied globalPermissionGroup include is omitted.

        This isolates the include permission from the base model permission. With
        permUserRead present, the user attributes are complete; with permRightGroupRead
        absent, the requested globalPermissionGroup relationship is not included and the
        missing permission is reported in metadata.
        """
        scopes = _all_scopes_except(self, ['permRightGroupRead'])
        token = self.create_apitoken(extra_payload={'scopes': scopes})

        response = request_with_api_token(
            token.token,
            '/ui/users?include=globalPermissionGroup&page[size]=1',
        )
        self.assertEqual(response.status_code, 200, response.text)
        body = response.json()
        user_attributes = body['data'][0]['attributes']
        self.assertIn('name', user_attributes)
        self.assertIn('email', user_attributes)
        self.assertIn('isValid', user_attributes)
        self.assertIn('globalPermissionGroupId', user_attributes)
        self.assertNotIn('included', body)
        self.assertIn('permRightGroupRead', json.dumps(body['meta']))

    def test_api_token_user_public_attributes_with_allowed_global_permission_group_include(self):
        """A denied base user read does not block an allowed globalPermissionGroup include.

        This is the inverse mixed-permission case: permUserRead is absent so users are
        public-only, but permRightGroupRead is present so the globalPermissionGroup include
        is still returned with its readable attributes. It ensures public fallback on the
        base resource does not suppress allowed includes.
        """
        scopes = _all_scopes_except(self, ['permUserRead'])
        token = self.create_apitoken(extra_payload={'scopes': scopes})

        response = request_with_api_token(
            token.token,
            '/ui/users?include=globalPermissionGroup&page[size]=1',
        )
        self.assertEqual(response.status_code, 200, response.text)
        body = response.json()
        user_attributes = body['data'][0]['attributes']
        self.assertEqual(set(user_attributes), {'name'})
        self.assertIn('included', body)
        group_attributes = body['included'][0]['attributes']
        self.assertIn('name', group_attributes)
        self.assertIn('permissions', group_attributes)

    def test_api_token_user_read_attributes_with_allowed_global_permission_group_include(self):
        """Users plus globalPermissionGroup include with all relevant read scopes.

        This is the fully-authorized case for the users include matrix. It verifies full
        user attributes and full global permission group attributes are returned when both
        permUserRead and permRightGroupRead are available.
        """
        scopes = _all_scopes_except(self, [])
        token = self.create_apitoken(extra_payload={'scopes': scopes})

        response = request_with_api_token(
            token.token,
            '/ui/users?include=globalPermissionGroup&page[size]=1',
        )
        self.assertEqual(response.status_code, 200, response.text)
        body = response.json()
        user_attributes = body['data'][0]['attributes']
        self.assertIn('name', user_attributes)
        self.assertIn('email', user_attributes)
        self.assertIn('isValid', user_attributes)
        self.assertIn('globalPermissionGroupId', user_attributes)
        self.assertIn('included', body)
        group_attributes = body['included'][0]['attributes']
        self.assertIn('name', group_attributes)
        self.assertIn('permissions', group_attributes)

    def test_api_token_hashtype_create_scope(self):
        """HashType creation is controlled by permHashTypeCreate.

        A token with the create scope can POST a new HashType and receives 201. A token
        with only read access cannot create a HashType and receives 403 mentioning the
        missing create permission.
        """
        hash_type_id = 90000 + int(time.time() * 1000) % 900
        payload = _resource_payload('HashType', _hashtype_attributes(hash_type_id))

        allowed_token = self.create_apitoken(extra_payload={'scopes': ['permHashTypeCreate']})
        allowed_response = request_with_api_token(
            allowed_token.token,
            '/ui/hashtypes',
            method='POST',
            payload=payload,
        )
        self.assertEqual(allowed_response.status_code, 201, allowed_response.text)
        self.delete_after_test(HashType.objects.get(pk=hash_type_id))

        denied_response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': ['permHashTypeRead']}).token,
            '/ui/hashtypes',
            method='POST',
            payload=_resource_payload('HashType', _hashtype_attributes(hash_type_id + 1)),
        )
        self.assertEqual(denied_response.status_code, 403, denied_response.text)
        self.assertIn('permHashTypeCreate', denied_response.text)

    def test_api_token_hashtype_update_scope(self):
        """HashType updates are controlled by permHashTypeUpdate.

        The allowed branch proves a PATCH with the update scope changes the description.
        The denied branch proves read access alone is insufficient for PATCH and reports
        permHashTypeUpdate as the missing permission.
        """
        hashtype = self.create_hashtype()
        payload = _resource_payload(
            'HashType',
            {'description': 'Permission Test HashType Updated'},
            resource_id=hashtype.id,
        )

        allowed_response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': ['permHashTypeUpdate']}).token,
            f'/ui/hashtypes/{hashtype.id}',
            method='PATCH',
            payload=payload,
        )
        self.assertEqual(allowed_response.status_code, 200, allowed_response.text)
        self.assertEqual(allowed_response.json()['data']['attributes']['description'], 'Permission Test HashType Updated')

        denied_response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': ['permHashTypeRead']}).token,
            f'/ui/hashtypes/{hashtype.id}',
            method='PATCH',
            payload=payload,
        )
        self.assertEqual(denied_response.status_code, 403, denied_response.text)
        self.assertIn('permHashTypeUpdate', denied_response.text)

    def test_api_token_hashtype_delete_scope(self):
        """HashType deletion is controlled by permHashTypeDelete.

        A token with delete access can delete a HashType and receives 204. A token with
        only read access cannot delete and receives 403 naming the delete permission.
        """
        allowed_hashtype = self.create_hashtype(delete=False)
        allowed_response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': ['permHashTypeDelete']}).token,
            f'/ui/hashtypes/{allowed_hashtype.id}',
            method='DELETE',
        )
        self.assertEqual(allowed_response.status_code, 204, allowed_response.text)

        denied_hashtype = self.create_hashtype()
        denied_response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': ['permHashTypeRead']}).token,
            f'/ui/hashtypes/{denied_hashtype.id}',
            method='DELETE',
        )
        self.assertEqual(denied_response.status_code, 403, denied_response.text)
        self.assertIn('permHashTypeDelete', denied_response.text)
