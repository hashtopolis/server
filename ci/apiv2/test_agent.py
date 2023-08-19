from hashtopolis import Agent

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

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['agentstats']
        self._test_expandables(model_obj, expandables)
