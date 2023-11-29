from hashtopolis import Agent
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
        self.assertEqual(e.exception.status_code, 500)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['accessGroups', 'agentStats']
        self._test_expandables(model_obj, expandables)
