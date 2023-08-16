from hashtopolis import Pretask
from utils import BaseTest
from utils import do_create_pretask


class PretaskTest(BaseTest):
    model_class = Pretask

    def create_test_object(self, delete=True):
        model_obj = do_create_pretask()
        if delete:
            self.delete_after_test(model_obj)
        return model_obj

    def test_create_pretask(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch_pretask(self):
        model_obj = self.create_test_object()
        attr = 'taskName'
        self._test_patch(model_obj, attr)

    def test_delete_pretask(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expandables_hashlist(self):
        model_obj = self.create_test_object()
        expandables = ['pretaskFiles']
        self._test_expandables(model_obj, expandables)