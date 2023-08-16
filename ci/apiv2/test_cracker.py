from hashtopolis import Cracker
from utils import BaseTest
from utils import do_create_cracker


class CrackerTest(BaseTest):
    model_class = Cracker

    def create_test_object(self, delete=True):
        model_obj = do_create_cracker()
        if delete:
            self.delete_after_test(model_obj)
        return model_obj

    def test_create_cracker(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch_cracker(self):
        model_obj = self.create_test_object()
        attr = 'binaryName'
        self._test_patch(model_obj, attr)

    def test_delete_cracker(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_exception_cracker(self):
        self._test_exception(do_create_cracker, '002')

    def test_expandables_cracker(self):
        model_obj = self.create_test_object()
        expandables = ['crackerBinaryType']
        self._test_expandables(model_obj, expandables)
