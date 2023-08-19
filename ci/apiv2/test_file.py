from hashtopolis import File
from utils import BaseTest


class FileTest(BaseTest):
    model_class = File

    def create_test_object(self, *nargs, **kwargs):
        return self.create_file(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'isSecret', True)

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['accessGroup']
        self._test_expandables(model_obj, expandables)

    def test_create_binary(self):
        model_obj = self.create_test_object(compress=True)
        self._test_create(model_obj)
