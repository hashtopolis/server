from hashtopolis import AccessGroup, HashtopolisError
from utils import BaseTest


class AccessGroupTest(BaseTest):
    model_class = AccessGroup

    def create_test_object(self, *nargs, **kwargs):
        return self.create_accessgroup(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'groupName')

    def test_patch_empty_name(self):
        model_obj = self.create_test_object()
        with self.assertRaises(HashtopolisError) as e:
            self._test_patch(model_obj, 'groupName', '')
        self.assertEqual(e.exception.status_code, 500)

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_exception(self):
        self._test_exception(self.create_test_object, file_id='002', delete=False)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['userMembers', 'agentMembers']
        self._test_expandables(model_obj, expandables)
