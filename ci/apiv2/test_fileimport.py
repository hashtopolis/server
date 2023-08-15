from hashtopolis import File
from utils import BaseTest
from utils import do_create_file


class FileImportTest(BaseTest):
    def test_do_upload(self):
        file = do_create_file()
        self.delete_after_test(file)

        self.assertEqual(len(File.objects.filter(filename=file.filename)), 1)
