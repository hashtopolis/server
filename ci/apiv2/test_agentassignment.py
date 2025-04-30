from hashtopolis import AgentAssignment

from utils import BaseTest


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
