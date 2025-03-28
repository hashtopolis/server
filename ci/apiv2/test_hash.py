from hashtopolis import Hash, HashtopolisResponseError, HashtopolisError
from utils import BaseTest


class HashTest(BaseTest):
    model_class = Hash

    def create_test_object(self, *nargs, **kwargs):
        hashlist = self.create_hashlist(*nargs, **kwargs)
        hash = Hash.objects.filter(hashlistId=hashlist.id)[0]
        self.assertIsNotNone(hash)
        return hash

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        # Patching Hashes is not possible via API
        model_obj = self.create_test_object()
        with self.assertRaises(HashtopolisResponseError) as e:
            self._test_patch(model_obj, 'isCracked', True)
        self.assertEqual(e.exception.status_code, 405)

    def test_delete(self):
        # Deleting Hashes is not possible via API
        model_obj = self.create_test_object()
        with self.assertRaises(HashtopolisResponseError) as e:
            self._test_delete(model_obj)
        self.assertEqual(e.exception.status_code, 405)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['hashlist', 'chunk']
        self._test_expandables(model_obj, expandables)
