from hashtopolis import User
from utils import BaseTest
from utils import do_create_user
from utils import do_create_globalpermissiongroup


class UserTest(BaseTest):
    def test_create_user(self):
        user = do_create_user()
        self.delete_after_test(user)

        obj = User.objects.get(id=user.id)
        self.assertEqual(obj.name, user.name)

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
        user = do_create_user()
        user.delete()

        objs = User.objects.filter(id=user.id)
        self.assertEqual(len(objs), 0)

    def test_expand_user(self):
        gp_group = do_create_globalpermissiongroup()
        user = do_create_user(gp_group.id)

        self.delete_after_test(gp_group)
        self.delete_after_test(user)

        obj = User.objects.get(id=user.id, expand='globalPermissionGroup')

        self.assertEqual(obj.globalPermissionGroup_set.name, gp_group.name)