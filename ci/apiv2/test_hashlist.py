from hashtopolis import Hashlist, Helper
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
        expandables = ['accessGroup', 'hashType', 'hashes', 'hashlists', 'tasks']
        self._test_expandables(model_obj, expandables)

    def test_filter_archived(self):
        model_obj = self.create_test_object()

        obj = Hashlist.objects.get(hashlistId=model_obj.id, isArchived=False)
        self.assertEqual(obj.id, model_obj.id)

    def test_create_alternative_hashtype(self):
        model_obj = self.create_test_object(file_id='003')
        self._test_create(model_obj)

    def test_helper_create_superhashlist(self):
        hashlists = [self.create_test_object() for _ in range(2)]

        helper = Helper()
        hashlist = helper.create_superhashlist(name="Testing 123", hashlists=hashlists)
        self.delete_after_test(hashlist)

        # Ensure is created as superhashlist
        self.assertEqual(hashlist.format, 3)

        # Validate if created with provided hashlists
        obj = Hashlist.objects.prefetch_related('hashlists').get(pk=hashlist.id)
        self.assertListEqual(hashlists, obj.hashlists_set)
