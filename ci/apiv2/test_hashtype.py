import json
import datetime
from pathlib import Path

from hashtopolis import HashType
from utils import BaseTest


def do_create_hashtype(file_id='001'):
    p = Path(__file__).parent.joinpath(f'create_hashtype_{file_id}.json')
    payload = json.loads(p.read_text('UTF-8'))
    hashlist = HashType(**payload)
    obj = hashlist.save()
    return obj


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
