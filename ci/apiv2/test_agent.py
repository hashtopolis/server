import test_task
import test_user
from hashtopolis import Agent, Config, Helper
from hashtopolis import HashtopolisError

from utils import BaseTest, error_title, patch_many


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

    def test_patch_field_name_empty(self):
        model_obj = self.create_test_object()
        with self.assertRaises(HashtopolisError) as e:
            self._test_patch(model_obj, 'agentName', '')
        self.assertEqual(e.exception.status_code, 500)

    def test_patch_field_token(self):
        model_obj = self.create_test_object()
        with self.assertRaises(HashtopolisError) as e:
            self._test_patch(model_obj, 'token', 'whatever')
        self.assertEqual(e.exception.status_code, 403)

    def test_patch_field_user(self):
        user_test = test_user.UserTest()
        user_test.setUp()

        user_obj = user_test.create_test_object()
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'userId', user_obj.id)

        user_test.tearDown()

    def test_name_too_long(self):
        model_obj = self.create_test_object()
        too_long_name = "a" * 101
        with self.assertRaises(HashtopolisError) as e:
            self._test_patch(model_obj, 'agentName', too_long_name)  # name exceeds max size of 100
        self.assertEqual(e.exception.status_code, 400)
        self.assertEqual(error_title(e.exception), f"The string value: '{too_long_name}' is too long. The max size is '100'")

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['accessGroups', 'agentStats']
        self._test_expandables(model_obj, expandables)

    def test_assign_unassign_agent(self):
        agent_obj = self.create_test_object()

        task_test = test_task.TaskTest()
        task_test.setUp()
        task_obj = task_test.create_test_object()

        helper = Helper()

        result = helper.assign_agent(agent=agent_obj, task=task_obj)

        self.assertEqual(result['Assign'], 'Success')

        result = helper.unassign_agent(agent=agent_obj)

        self.assertEqual(result['Unassign'], 'Success')

        task_test.tearDown()

    def test_bulk_activate(self):
        agents = [self.create_agent() for i in range(5)]
        active_attributes = [True for i in range(5)]
        patch_many(Agent, agents, active_attributes, "isActive")

    def test_hide_ip_info(self):
        agent_obj = self.create_test_object()
        config = Config.objects.get(item='hideIpInfo')
        original_value = config.value

        try:
            config.value = "0"
            config.save()

            visible_agent = Agent.objects.get(pk=agent_obj.id)
            self.assertIsNotNone(visible_agent.lastIp)
            self.assertNotEqual(visible_agent.lastIp, "Hidden")

            config.value = "1"
            config.save()

            hidden_agent = Agent.objects.get(pk=agent_obj.id)
            self.assertEqual(hidden_agent.lastIp, "Hidden")
        finally:
            config.value = original_value
            config.save()

    def test_acl(self):
        model_obj = self.create_test_object()
        self._test_acl_list(model_obj, {'permAgentRead': True})
