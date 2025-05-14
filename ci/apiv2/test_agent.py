from test_task import TaskTest
from hashtopolis import Agent, Helper
from hashtopolis import HashtopolisError

from utils import BaseTest


class AgentTest(BaseTest):
    model_class = Agent

    def create_test_object(self, *nargs, **kwargs):
        return self.create_agent(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'agentName')

    def test_patch_field_ignorerrors_invalid_choice(self):
        model_obj = self.create_test_object()
        with self.assertRaises(HashtopolisError) as e:
            self._test_patch(model_obj, 'ignoreErrors', 5)
        self.assertEqual(e.exception.status_code, 400)

    def test_name_too_long(self):
        model_obj = self.create_test_object()
        too_long_name = "a" * 101
        with self.assertRaises(HashtopolisError) as e:
            self._test_patch(model_obj, 'agentName', too_long_name)  # name exceeds max size of 100
        self.assertEqual(e.exception.status_code, 400)
        self.assertEqual(e.exception.exception_details[0]["message"],
                         f"The string value: '{too_long_name}' is too long. The max size is '100'")

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['accessGroups', 'agentStats']
        self._test_expandables(model_obj, expandables)

    def test_assign_unassign_agent(self):
        agent_obj = self.create_test_object()

        task_test = TaskTest()
        task_obj = task_test.create_test_object(delete=True)

        helper = Helper()

        result = helper.assign_agent(agent=agent_obj, task=task_obj)

        self.assertEqual(result['Assign'], 'Success')

        result = helper.unassign_agent(agent=agent_obj)

        self.assertEqual(result['Unassign'], 'Success')

        task_test.tearDown()

    def test_bulk_activate(self):
        agents = [self.create_agent() for i in range(5)]
        active_attributes = [True for i in range(5)]
        Agent.objects.patch_many(agents, active_attributes, "isActive")
