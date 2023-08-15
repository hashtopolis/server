from hashtopolis import GlobalPermissionGroup
from hashtopolis import HashtopolisError
from utils import BaseTest
from utils import do_create_globalpermissiongroup, do_create_user


class GlobalPermissionGroupTest(BaseTest):
    def test_create_globalpermissiongroup(self):
        gp_group = do_create_globalpermissiongroup()
        self.delete_after_test(gp_group)

        obj = GlobalPermissionGroup.objects.get(pk=gp_group.id)
        self.assertEqual(gp_group.name, obj.name)

    def test_patch_globalpermissiongroup(self):
        gp_group = do_create_globalpermissiongroup()
        self.delete_after_test(gp_group)

        obj = GlobalPermissionGroup.objects.get(pk=gp_group.id)
        self.assertFalse(obj.permissions['permRightGroupCreate'])

        gp_group.permissions['permRightGroupCreate'] = True
        gp_group.save()

        obj = GlobalPermissionGroup.objects.get(pk=gp_group.id)
        self.assertFalse(obj.permissions['permRightGroupCreate'])

    def test_delete_globalpermissiongroup(self):
        gp_group = do_create_globalpermissiongroup()
        gp_group.delete()

        objs = GlobalPermissionGroup.objects.filter(id=id)
        self.assertEqual(objs, [])

    def test_exception_globalpermissiongroup(self):
        with self.assertRaises(HashtopolisError) as e:
            _ = do_create_globalpermissiongroup(permissions='test')

        self.assertEqual(e.exception.args[1], 'Creation of object failed')
        self.assertIn('is not of type dict', e.exception.args[4])

    def test_expand_globalpermissiongroup(self):
        gp_group = do_create_globalpermissiongroup()
        self.delete_after_test(gp_group)
        user = do_create_user(global_permission_group_id=gp_group.id)
        self.delete_after_test(user)

        obj = GlobalPermissionGroup.objects.get(id=gp_group.id, expand='user')
        self.assertGreaterEqual(len(obj.user_set), 1)
        self.assertEqual(obj.user_set[0].name, user.name)
