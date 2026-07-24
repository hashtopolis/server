from utils import BaseTest, do_create_dummy_agent, do_create_agentassignent

from hashtopolis import Helper
from hashtopolis import Task


KEYSPACE = 56800
# Large enough that the server dispatches the whole keyspace as a single chunk.
CHUNK_SIZE = 1_000_000


def _complete_task(base_test, task):
    """Drive a fresh dummy agent through the protocol so that the given task
    reaches the completed condition checked by GetCompletedCountHelperAPI, i.e.
    ``checkpointSum - skipSum == keyspace``.

    A dedicated agent is created and assigned only to this task, then walked
    through the agent protocol. The flow is adaptive: getChunk may report
    ``keyspace_required`` (first call), ``benchmark`` (when useNewBench is set)
    or ``OK`` (a real chunk). Each intermediate status is satisfied before
    requesting the next chunk. With progress=100 the server stores
    ``checkpoint = skip + length`` on the chunk, so for a single chunk covering
    the whole keyspace the completion condition is satisfied.
    """
    dummy_agent, agent = do_create_dummy_agent()
    base_test.delete_after_test(agent)

    do_create_agentassignent(agent, task)

    dummy_agent.get_task()
    dummy_agent.get_hashlist()

    dummy_agent.get_chunk()
    # Loop until a real chunk is dispatched, satisfying intermediate requests.
    while dummy_agent.chunk['status'] != 'OK':
        status = dummy_agent.chunk['status']
        if status == 'keyspace_required':
            dummy_agent.send_keyspace(keyspace=KEYSPACE)
        elif status == 'benchmark':
            dummy_agent.send_benchmark()
        else:
            raise AssertionError(f"Unexpected chunk status: {status}")
        dummy_agent.get_chunk()

    # progress=100 -> keyspaceProgress = skip + length -> checkpoint = skip + length
    dummy_agent.send_process(progress=100)


def _create_simple_task(base_test, **extra_payload):
    """Create a hashlist + task with deterministic chunking parameters so a single
    agent chunk covers the whole keyspace."""
    hashlist = base_test.create_hashlist()
    overrides = {
        'chunkSize': CHUNK_SIZE,
        'staticChunks': 0,
        'skipKeyspace': 0,
        'isCpuTask': True,
        'preprocessorId': 0,
        'preprocessorCommand': '',
        'useNewBench': False,
    }
    overrides.update(extra_payload)
    task = base_test.create_task(hashlist, extra_payload=overrides, file_id='004')
    return task


def _create_completed_task(base_test, **extra_payload):
    task = _create_simple_task(base_test, **extra_payload)
    _complete_task(base_test, task)
    return task


class CompletedCountTest(BaseTest):
    def _get_counts(self):
        result = Helper().get_completed_count()
        self.assertIsInstance(result, dict)
        self.assertIn('completedTasks', result)
        self.assertIn('completedSupertasks', result)
        return result['completedTasks'], result['completedSupertasks']

    def test_returns_dict_with_keys(self):
        completed_tasks, completed_supertasks = self._get_counts()
        self.assertIsInstance(completed_tasks, int)
        self.assertIsInstance(completed_supertasks, int)

    def test_completed_task_increments_count(self):
        before, _ = self._get_counts()
        _create_completed_task(self)
        after, _ = self._get_counts()
        self.assertEqual(after, before + 1)

    def test_incomplete_task_not_counted(self):
        before, _ = self._get_counts()
        task = _create_simple_task(self)

        dummy_agent, agent = do_create_dummy_agent()
        self.delete_after_test(agent)
        do_create_agentassignent(agent, task)
        dummy_agent.get_task()
        dummy_agent.get_hashlist()
        dummy_agent.get_chunk()
        while dummy_agent.chunk['status'] != 'OK':
            status = dummy_agent.chunk['status']
            if status == 'keyspace_required':
                dummy_agent.send_keyspace(keyspace=KEYSPACE)
            elif status == 'benchmark':
                dummy_agent.send_benchmark()
            dummy_agent.get_chunk()
        # Only 50% progress -> checkpoint < skip + length -> not completed
        dummy_agent.send_process(progress=50)

        after, _ = self._get_counts()
        self.assertEqual(after, before)

    def test_archived_completed_task_not_counted(self):
        before, _ = self._get_counts()
        task = _create_completed_task(self)

        task.isArchived = True
        task.save()

        after, _ = self._get_counts()
        self.assertEqual(after, before)

    def test_completed_supertask_increments_count(self):
        _, before = self._get_counts()

        pretasks = [self.create_pretask() for _ in range(2)]
        supertask = self.create_supertask(pretasks=pretasks)
        cracker = self.create_cracker()
        hashlist = self.create_hashlist()

        task_wrapper = Helper().create_supertask(supertask, hashlist, cracker)
        self.delete_after_test(task_wrapper)

        sub_tasks = list(Task.objects.filter(taskWrapperId=task_wrapper.id))
        self.assertEqual(len(sub_tasks), 2)
        for sub_task in sub_tasks:
            _complete_task(self, sub_task)

        _, after = self._get_counts()
        self.assertEqual(after, before + 1)

    def test_supertask_with_one_incomplete_subtask_not_counted(self):
        _, before = self._get_counts()

        pretasks = [self.create_pretask() for _ in range(2)]
        supertask = self.create_supertask(pretasks=pretasks)
        cracker = self.create_cracker()
        hashlist = self.create_hashlist()

        task_wrapper = Helper().create_supertask(supertask, hashlist, cracker)
        self.delete_after_test(task_wrapper)

        sub_tasks = list(Task.objects.filter(taskWrapperId=task_wrapper.id))
        self.assertEqual(len(sub_tasks), 2)
        # Complete only the first sub-task, leave the second incomplete.
        _complete_task(self, sub_tasks[0])

        _, after = self._get_counts()
        self.assertEqual(after, before)

    def test_counts_are_consistent_across_calls(self):
        result1 = Helper().get_completed_count()
        result2 = Helper().get_completed_count()
        self.assertEqual(result1, result2)
