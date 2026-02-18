from hashtopolis import AgentAssignment

from hashtopolis_agent import DummyAgent
from utils import BaseTest, do_create_dummy_agent


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
