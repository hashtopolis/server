import datetime

from hashtopolis import HashType
from utils import BaseTest
from utils import do_create_hashtype


class HashtypeTest(BaseTest):
    def test_create_hashtype(self):
        hashtype = do_create_hashtype()
        self.delete_after_test(hashtype)

        objs = HashType.objects.filter(hashTypeId=hashtype.id)
        self.assertEqual(len(objs), 1)

    def test_get_one_hashtype(self):
        hashtype = do_create_hashtype()
        self.delete_after_test(hashtype)

        obj = HashType.objects.get(pk=hashtype.id)
        self.assertIsNotNone(obj)

    def test_patch_hashtype(self):
        hashtype = do_create_hashtype()
        self.delete_after_test(hashtype)

        # TODO: Boring to only request the first one
        stamp = datetime.datetime.now().isoformat()
        new_description = f'MD5 - {stamp}'

        hashtype.description = new_description
        hashtype.save()

        obj = HashType.objects.get(pk=hashtype.id)
        self.assertEqual(obj.description, new_description)
