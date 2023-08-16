from hashtopolis import Agent

from utils import BaseTest
from utils import do_create_agent


class AgentTest(BaseTest):
    model_class = Agent

    def create_test_object(self, delete=True):
        _, model_obj = do_create_agent()
        if delete:
            self.delete_after_test(model_obj)
        return model_obj

    def test_create_agent(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch_agent(self):
        model_obj = self.create_test_object()
        attr = 'agentName'
        self._test_patch(model_obj, attr)

    def test_expandables_agent(self):
        model_obj = self.create_test_object()
        expandables = ['agentstats']
        self._test_expandables(model_obj, expandables)
