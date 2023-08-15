import datetime

import hashtopolis
from hashtopolis import Hashlist
from utils import BaseTest
from utils import do_create_hashlist


class HashlistTest(BaseTest):
    def test_create_hashlist(self):
        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        objs = Hashlist.objects.filter(hashlistId=hashlist.id)
        self.assertEqual(len(objs), 1)

    def test_patch_hashlist(self):
        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        stamp = datetime.datetime.now().isoformat()
        new_name = f'Dummy Hashlist - {stamp}'
        hashlist.name = new_name
        hashlist.save()

        obj = Hashlist.objects.get(hashlistId=hashlist.id)
        self.assertEqual(obj.name, new_name)

    def test_delete_hashlist(self):
        hashlist = do_create_hashlist()
        hashlist.delete()

        objs = Hashlist.objects.filter(hashlistId=id)
        self.assertEqual(objs, [])

    def test_exception_hashlist(self):
        with self.assertRaises(hashtopolis.HashtopolisError):
            _ = do_create_hashlist(file_id='002')

        objs = Hashlist.objects.filter(name='Hashlist-md5sum-002')
        self.assertEqual(objs, [])

    def test_filter_archived(self):
        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        obj = Hashlist.objects.get(hashlistId=hashlist.id, isArchived=False)
        self.assertEqual(obj.id, hashlist.id)

    def test_expand_hashlist(self):
        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        obj = Hashlist.objects.get(hashlistId=hashlist.id, expand='hashes')
        self.assertEqual(len(obj.hashes_set), 1)

    def test_create_hashtype(self):
        hashlist = do_create_hashlist('003')
        self.delete_after_test(hashlist)

        obj = Hashlist.objects.get(pk=hashlist.id)
        self.assertEqual(obj.hashTypeId, 1000)
