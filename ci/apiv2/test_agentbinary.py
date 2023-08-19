from hashtopolis import AgentBinary
from utils import BaseTest


class AgentBinaryTest(BaseTest):
    model_class = AgentBinary

    # def create_test_object(self, *nargs, **kwargs):
    #     # TODO: Cannot test create agent binaries with custom filename
    #     # since we cannot upload files to 'bin' directory
    #     # file = self.create_file()
    #     # kwargs['extra_payload'] = dict(filename=file.filename)
    #     return self.create_agentbinary(*nargs, **kwargs)

    # def test_create(self):
    #     model_obj = self.create_test_object()
    #     self._test_create(model_obj)

    # def test_patch(self):
    #     model_obj = self.create_test_object()
    #     attr = 'version'
    #     new_attr_value = '1.2.3'
    #     self._test_patch(model_obj, attr, new_attr_value)

    # def test_delete(self):
    #     model_obj = self.create_test_object(delete=False)
    #     self._test_delete(model_obj)
