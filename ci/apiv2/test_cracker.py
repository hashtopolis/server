from hashtopolis import Cracker
from utils import BaseTest


class CrackerTest(BaseTest):
    model_class = Cracker

    def create_test_object(self, *nargs, **kwargs):
        return self.create_cracker(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'binaryName')

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_exception(self):
        self._test_exception(self.create_test_object, file_id='002', delete=False)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['crackerBinaryType']
        self._test_expandables(model_obj, expandables)
