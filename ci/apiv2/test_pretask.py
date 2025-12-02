from hashtopolis import Pretask, HashtopolisError
from utils import BaseTest,do_create_pretask


class PretaskTest(BaseTest):
    model_class = Pretask

    def create_test_object(self, *nargs, **kwargs):
        return self.create_pretask(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'taskName')

    def test_patch_missing_alias(self):
        model_obj = self.create_test_object()
        model_obj.attackCmd = "-a 3 ?l?l?l"
        with self.assertRaises(HashtopolisError) as e:
            model_obj.save()
        self.assertEqual(e.exception.status_code, 500)
        self.assertEqual(e.exception.title, f"The attack command does not contain the hashlist alias!")

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['pretaskFiles']
        self._test_expandables(model_obj, expandables)

    def test_create_alt(self):
        model_obj = self.create_test_object(file_id='002')
        self._test_create(model_obj)

    def test_create_missing_alias(self):
        with self.assertRaises(HashtopolisError) as e:
            model_obj = self.create_test_object(file_id='inv_attackcmd')
        self.assertEqual(e.exception.status_code, 400)
        self.assertEqual(e.exception.title, "The attack command does not contain the hashlist alias!")

    def test_create_empty_name(self):
        with self.assertRaises(HashtopolisError) as e:
            model_obj = self.create_test_object(file_id='inv_name')
        self.assertEqual(e.exception.status_code, 400)
        self.assertEqual(e.exception.title, "Name cannot be empty!")

    def test_create_chunktime_zero(self):
        model_obj = self.create_test_object(file_id='chunk_zero')
        self._test_create(model_obj)
        self.assertGreater(model_obj.chunkTime, 0)

    def test_create_chunktime_negative(self):
        model_obj = self.create_test_object(file_id='chunk_negative')
        self._test_create(model_obj)
        self.assertGreater(model_obj.chunkTime, 0)
