from hashtopolis import User, Helper
from utils import BaseTest


class UserTest(BaseTest):
    model_class = User

    def create_test_object(self, *nargs, **kwargs):
        return self.create_user(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        gp_group = self.create_globalpermissiongroup()
        user = self.create_user()

        user.globalPermissionGroupId = gp_group.id
        user.save()

        obj = user.objects.get(id=user.id)
        self.assertEqual(obj.globalPermissionGroupId, gp_group.id)

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expand(self):
        model_obj = self.create_test_object()
        expandables = ['accessGroups', 'globalPermissionGroup']
        self._test_expandables(model_obj, expandables)

    def test_disable_enable_user(self):
        user = self.create_test_object()

        # Disable User
        user.isValid = False
        user.save()

        obj = User.objects.get(id=user.id)
        self.assertFalse(obj.isValid)

        #  Enable user
        user.isValid = True
        user.save()

        obj = User.objects.get(id=user.id)
        self.assertTrue(obj.isValid)

    def test_helper_setUserPassword(self):
        user = self.create_test_object()
        newPassword = "testing123"
        helper = Helper()
        helper.setUserPassword(user, newPassword)
        helper._test_authentication(user.name, newPassword)
