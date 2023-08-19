from hashtopolis import Hashlist
from utils import BaseTest


class HashlistTest(BaseTest):
    model_class = Hashlist

    def create_test_object(self, *nargs, **kwargs):
        return self.create_hashlist(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'name')

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_exception(self):
        self._test_exception(self.create_test_object, file_id='002', delete=False)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['accessGroup', 'hashType', 'hashes', 'tasks']
        self._test_expandables(model_obj, expandables)

    def test_filter_archived(self):
        model_obj = self.create_test_object()

        obj = Hashlist.objects.get(hashlistId=model_obj.id, isArchived=False)
        self.assertEqual(obj.id, model_obj.id)

    def test_create_alternative_hashtype(self):
        model_obj = self.create_test_object(file_id='003')
        self._test_create(model_obj)
