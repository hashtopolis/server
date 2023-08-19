from hashtopolis import Chunk

from utils import BaseTest


class ChunkTest(BaseTest):
    model_class = Chunk

    def create_test_object(self, *nargs, **kwargs):
        # TODO: Objects has to be created via Server API
        return self.create_agent(*nargs, **kwargs)

    # def test_create(self):
    #     model_obj = self.create_test_object()
    #     self._test_create(model_obj)

    # def test_expandables(self):
    #     model_obj = self.create_test_object()
    #     expandables = ['task']
    #     self._test_expandables(model_obj, expandables)
