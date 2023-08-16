from hashtopolis import Hashlist
from utils import BaseTest
from utils import do_create_hashlist


class HashlistTest(BaseTest):
    model_class = Hashlist

    def create_test_object(self, delete=True):
        model_obj = do_create_hashlist()
        if delete:
            self.delete_after_test(model_obj)
        return model_obj

    def test_create_hashlist(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch_hashlist(self):
        model_obj = self.create_test_object()
        attr = 'name'
        self._test_patch(model_obj, attr)

    def test_delete_hashlist(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_exception_hashlist(self):
        self._test_exception(do_create_hashlist, '002')

    def test_filter_archived(self):
        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        obj = Hashlist.objects.get(hashlistId=hashlist.id, isArchived=False)
        self.assertEqual(obj.id, hashlist.id)

    def test_create_hashtype(self):
        hashlist = do_create_hashlist('003')
        self.delete_after_test(hashlist)

        obj = Hashlist.objects.get(pk=hashlist.id)
        self.assertEqual(obj.hashTypeId, 1000)

    def test_expandables_hashlist(self):
        model_obj = self.create_test_object()
        expandables = ['accessGroup', 'hashType', 'hashes', 'tasks']
        self._test_expandables(model_obj, expandables)
