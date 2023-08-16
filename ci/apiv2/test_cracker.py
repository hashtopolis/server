import datetime

from hashtopolis import Cracker
from hashtopolis import HashtopolisError
from utils import BaseTest
from utils import do_create_cracker


class CrackerTest(BaseTest):
    def test_create_cracker(self):
        cracker = do_create_cracker()
        self.delete_after_test(cracker)

        obj = Cracker.objects.get(crackerBinaryId=cracker.id)
        self.assertEqual(obj.binaryName, cracker.binaryName)

    def test_patch_cracker(self):
        cracker = do_create_cracker()
        self.delete_after_test(cracker)

        stamp = datetime.datetime.now().isoformat()
        obj_name = f'Dummy Cracker - {stamp}'
        cracker.binaryName = obj_name
        cracker.save()

        obj = cracker.objects.get(crackerBinaryId=cracker.id)
        self.assertEqual(obj.binaryName, obj_name)

    def test_delete_cracker(self):
        cracker = do_create_cracker()

        obj = cracker.objects.get(crackerBinaryId=cracker.id)
        self.assertIsNotNone(obj)

        cracker.delete()

        objs = Cracker.objects.filter(crackerBinaryId=cracker.id)
        self.assertEqual(objs, [])

    def test_exception_cracker(self):
        with self.assertRaises(HashtopolisError) as e:
            _ = do_create_cracker('002')
        self.assertEqual(e.exception.args[1], 'Creation of object failed')
        self.assertIn('Required parameter', e.exception.args[4])

