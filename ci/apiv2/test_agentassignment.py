import time, random
from hashtopolis import AgentAssignment, Chunk

from utils import BaseTest, do_create_dummy_agent, do_create_agentassignent


class AgentStatTest(BaseTest):
    model_class = AgentAssignment

    def create_test_object(self, *nargs, **kwargs):
        return self.create_agentassignment(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'benchmark', "1234")

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['task', 'agent']
        self._test_expandables(model_obj, expandables)

    def test_agent_assign_task(self):
        (dummy, agent) = do_create_dummy_agent()
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist)

        # no assignment should exist yet
        check = AgentAssignment.objects.filter(taskId=task.id)

        self.assertEqual(len(check), 0)

        taskId = dummy.get_task()

        self.assertEqual(taskId, task.id)

        # after the agent asked for a task, there should be an assignment
        check = AgentAssignment.objects.filter(taskId=task.id)

        self.assertEqual(len(check), 1)
        self.assertEqual(check[0].agentId, agent.id)
        self.assertEqual(check[0].taskId, task.id)

    def test_acl(self):
        model_obj = self.create_test_object()
        self._test_acl_list(model_obj, {'permAgentAssignmentRead': True})

    def test_cracking_time_aggregation(self):
        dummy_agent, agent, _, task = self.create_agent_with_task().values()
        time.sleep(1)  # Simulate cracking time
        dummy_agent.send_process(progress=100)
        dummy_agent.get_chunk()
        time.sleep(1)  # Simulate cracking time
        dummy_agent.send_process(progress=100)
        dummy_agent.get_chunk()
        time.sleep(1)  # Simulate cracking time
        dummy_agent.send_process(progress=100)
        dummy_agent.get_chunk()  # Leave the last chunk unfinished for a more meaningful test

        # Calculate a reference value for the cracking time by applying an interval merge algorithm
        chunks = Chunk.objects.filter(agentId=agent.id, taskId=task.id)
        totalEnd = totalSum = 0
        for chunk in sorted(chunks, key=lambda c: c.dispatchTime): # Expects list to be sorted by time
            if chunk.dispatchTime > 0 and chunk.solveTime > 0:
                totalSum = (
                    totalSum + (chunk.solveTime - chunk.dispatchTime)
                    - max(0, totalEnd - chunk.dispatchTime)
                    + max(0, totalEnd - chunk.solveTime)
                )
                totalEnd = max(totalEnd, chunk.solveTime)

        # Get the aggregate cracking time from the application
        aggregate_attrs = ['crackingTime']
        assignment = AgentAssignment.objects.params(**{"aggregate[assignment]": ','.join(aggregate_attrs)}).get(agentId=agent.id, taskId=task.id)

        self.assertEqual(totalSum, assignment.crackingTime)
