from hashtopolis import Hashlist, Helper, File
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

    def test_export_cracked_hashes(self):
        model_obj = self.create_test_object(file_id='001')

        helper = Helper()
        file = helper.export_cracked_hashes(model_obj)

        obj = File.objects.get(fileId=file.id)
        self.assertEqual(int(file.id), obj.id)
        self.assertIn('Pre-cracked_', obj.filename)

    def test_export_left_hashes(self):
        model_obj = self.create_test_object(file_id='001')

        helper = Helper()
        file = helper.export_left_hashes(model_obj)

        obj = File.objects.get(fileId=file.id)
        self.assertEqual(int(file.id), obj.id)
        self.assertIn('Leftlist_', obj.filename)

    def test_export_wordlist(self):
        model_obj = self.create_test_object(file_id='001')

        cracked = "cc03e747a6afbbcbf8be7668acfebee5:test123"

        helper = Helper()
        helper.import_cracked_hashes(model_obj, cracked, ':')

        file = helper.export_wordlist(model_obj)

        obj = File.objects.get(fileId=file.id)
        self.assertEqual(int(file.id), obj.id)
        self.assertIn('Wordlist_', obj.filename)

    def test_import_cracked_hashes(self):
        model_obj = self.create_test_object(file_id='001')

        cracked = "cc03e747a6afbbcbf8be7668acfebee5:test123"

        helper = Helper()
        result = helper.import_cracked_hashes(model_obj, cracked, ':')

        self.assertEqual(result['totalLines'], 1)
        self.assertEqual(result['newCracked'], 1)

        obj = Hashlist.objects.get(hashlistId=model_obj.id)
        self.assertEqual(obj.cracked, 1)

    def test_import_cracked_hashes_invalid(self):
        model_obj = self.create_test_object(file_id='001')

        cracked = "cc03e747a6afbbcbf8be7668acfebee5__test123"

        helper = Helper()
        result = helper.import_cracked_hashes(model_obj, cracked, ':')

        self.assertEqual(result['totalLines'], 1)
        self.assertEqual(result['invalid'], 1)

        obj = Hashlist.objects.get(hashlistId=model_obj.id)
        self.assertEqual(obj.cracked, 0)

    def test_import_cracked_hashes_notfound(self):
        model_obj = self.create_test_object(file_id='001')

        cracked = "ffffffffffffffffffffffffffffffff:test123"

        helper = Helper()
        result = helper.import_cracked_hashes(model_obj, cracked, ':')

        self.assertEqual(result['totalLines'], 1)
        self.assertEqual(result['notFound'], 1)

        obj = Hashlist.objects.get(hashlistId=model_obj.id)
        self.assertEqual(obj.cracked, 0)

    def test_import_cracked_hashes_already_cracked(self):
        model_obj = self.create_test_object(file_id='001')

        cracked = "cc03e747a6afbbcbf8be7668acfebee5:test123"

        helper = Helper()
        helper.import_cracked_hashes(model_obj, cracked, ':')

        result = helper.import_cracked_hashes(model_obj, cracked, ':')

        self.assertEqual(result['totalLines'], 1)
        self.assertEqual(result['alreadyCracked'], 1)

        obj = Hashlist.objects.get(hashlistId=model_obj.id)
        self.assertEqual(obj.cracked, 1)

    def test_helper_create_superhashlist(self):
        hashlists = [self.create_test_object() for _ in range(2)]

        helper = Helper()
        hashlist = helper.create_superhashlist(name="Testing 123", hashlists=hashlists)
        self.delete_after_test(hashlist)

        # Ensure is created as superhashlist
        self.assertEqual(hashlist.format, 3)

        # Validate if created with provided hashlists
        obj = Hashlist.objects.get(pk=hashlist.id, expand='hashlists')
        self.assertListEqual(hashlists, obj.hashlists_set)
