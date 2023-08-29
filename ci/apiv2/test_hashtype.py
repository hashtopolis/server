from hashtopolis import HashType
from utils import BaseTest


class HashTypeTest(BaseTest):
    model_class = HashType

    def create_test_object(self, *nargs, **kwargs):
        return self.create_hashtype(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'description')

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)
