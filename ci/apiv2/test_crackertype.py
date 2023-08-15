import json
import datetime
from pathlib import Path

from hashtopolis import CrackerType
from hashtopolis import HashtopolisError
from utils import BaseTest


class CrackerTypeTest(BaseTest):
    def test_create_crackertype(self):
        p = Path(__file__).parent.joinpath('create_crackertype_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        crackertype = CrackerType(**payload)
        crackertype.save()

        obj = CrackerType.objects.get(crackerBinaryTypeId=crackertype.id)
        self.assertEqual(obj.typeName, payload.get('typeName'))

        crackertype.delete()

    def test_patch_crackertype(self):
        p = Path(__file__).parent.joinpath('create_crackertype_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        crackertype = CrackerType(**payload)
        crackertype.save()

        stamp = datetime.datetime.now().day
        obj_name = f'hashcat{stamp}'
        crackertype.typeName = obj_name
        crackertype.save()

        obj = CrackerType.objects.get(crackerBinaryTypeId=crackertype.id)
        self.assertEqual(obj.typeName, obj_name)

        crackertype.delete()

    def test_delete_crackertype(self):
        p = Path(__file__).parent.joinpath('create_crackertype_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        crackertype = CrackerType(**payload)
        crackertype.save()

        id = crackertype.id

        crackertype.delete()

        objs = CrackerType.objects.filter(crackerBinaryTypeId=id)

        self.assertEqual(objs, [])

    def test_exception_crackertype(self):
        p = Path(__file__).parent.joinpath('create_crackertype_002.json')
        payload = json.loads(p.read_text('UTF-8'))
        crackertype = CrackerType(**payload)

        with self.assertRaises(HashtopolisError) as e:
            crackertype.save()
        self.assertEqual(e.exception.args[1],  'Creation of object failed')
        self.assertIn('is not of type string', e.exception.args[4])