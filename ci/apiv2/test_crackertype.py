from hashtopolis import CrackerType
from utils import BaseTest
from utils import do_create_crackertype


class CrackerTypeTest(BaseTest):
    model_class = CrackerType

    def create_test_object(self, delete=True):
        model_obj = do_create_crackertype()
        if delete:
            self.delete_after_test(model_obj)
        return model_obj

    def test_create_crackertype(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch_crackertype(self):
        model_obj = self.create_test_object()
        attr = 'typeName'
        new_attr_value = 'Generic - edited'
        self._test_patch(model_obj, attr, new_attr_value)

    def test_delete_crackertype(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_exception_crackertype(self):
        self._test_exception(do_create_crackertype, '002')

    def test_expandables_crackertype(self):
        model_obj = self.create_test_object()
        expandables = ['crackerVersions']
        self._test_expandables(model_obj, expandables)
