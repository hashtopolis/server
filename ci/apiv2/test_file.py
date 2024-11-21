from hashtopolis import File, Helper
from utils import BaseTest


class FileTest(BaseTest):
    model_class = File

    def create_test_object(self, *nargs, **kwargs):
        return self.create_file(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'isSecret', True)

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['accessGroup']
        self._test_expandables(model_obj, expandables)

    def test_create_binary(self):
        model_obj = self.create_test_object(compress=True)
        self._test_create(model_obj)

    def test_recount_wordlist(self):
        # Note: After the object creation, the line count is already updated, but afterward it is immutable on the API.
        #       There the test just check that the API function is callable and returns the file, but the count is
        #       already the same beforehand.
        model_obj = self.create_test_object()

        helper = Helper()
        file = helper.recount_file_lines(file=model_obj)

        self.assertEqual(file.lineCount, 3)

    def test_helper_get_file(self):
        model_obj = self.create_test_object()

        helper = Helper()
        file_data = helper.get_file(file=model_obj)
        self.assertEqual(file_data, "12345678\n123456\nprincess\n")
    
    def test_range_request_get_file(self):
        model_obj = self.create_test_object()

        helper = Helper()
        file_data = helper.get_file(file=model_obj, range="bytes=9-15")
        self.assertEqual(file_data, "123456\n")
