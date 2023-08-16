from hashtopolis import User
from utils import BaseTest
from utils import do_create_user
from utils import do_create_globalpermissiongroup


class UserTest(BaseTest):
    model_class = User

    def create_test_object(self, delete=True):
        model_obj = do_create_user()
        if delete:
            self.delete_after_test(model_obj)
        return model_obj

    def test_create_user(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch_user(self):
        user = do_create_user()

        gp_group = do_create_globalpermissiongroup()
        self.delete_after_test(gp_group)
        self.delete_after_test(user)

        user.globalPermissionGroupId = gp_group.id
        user.save()

        obj = user.objects.get(id=user.id)
        self.assertEqual(obj.globalPermissionGroupId, gp_group.id)

    def test_disable_enable_user(self):
        user = do_create_user()
        self.delete_after_test(user)

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

    def test_delete_user(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expand_user(self):
        model_obj = self.create_test_object()
        expandables = ['globalPermissionGroup']
        self._test_expandables(model_obj, expandables)