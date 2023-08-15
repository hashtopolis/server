from hashtopolis import File
from utils import BaseTest
from utils import do_create_file


class FileTest(BaseTest):
    def test_create_file(self):
        file = do_create_file()
        self.delete_after_test(file)
      
        objs = File.objects.filter(filename=file.filename)
        self.assertEqual(len(objs), 1)

    def test_patch_file(self):
        file = do_create_file()
        self.delete_after_test(file)

        file.isSecret = True
        file.save()

        obj = File.objects.get(fileId=file.id)
        self.assertTrue(obj.isSecret)

    def test_delete_file(self):
        file = do_create_file()
        file.delete()

        objs = File.objects.filter(fileId=file.id)
        self.assertEqual(objs, [])

    def test_expand_file(self):
        file = do_create_file()
        self.delete_after_test(file)

        # One-to-one casting
        objects = File.objects.filter(fileId=file.id, expand='accessGroup')
        self.assertEqual(objects[0].accessGroup_set.groupName, 'Default Group')