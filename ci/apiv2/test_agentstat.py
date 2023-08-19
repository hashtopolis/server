from hashtopolis import AgentStat

from utils import BaseTest


class AgentStatTest(BaseTest):
    model_class = AgentStat

    def create_test_object(self, *nargs, **kwargs):
        # TODO: Objects has to be created via Server API
        return self.create_agent(*nargs, **kwargs)

    # def test_create(self):
    #     model_obj = self.create_test_object()
    #     self._test_create(model_obj)
