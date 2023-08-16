from hashtopolis import GlobalPermissionGroup
from utils import BaseTest
from utils import do_create_globalpermissiongroup


class GlobalPermissionGroupTest(BaseTest):
    model_class = GlobalPermissionGroup

    def create_test_object(self, delete=True):
        model_obj = do_create_globalpermissiongroup()
        if delete:
            self.delete_after_test(model_obj)
        return model_obj

    def test_create_globalpermissiongroup(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

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
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_exception_globalpermissiongroup(self):
        self._test_exception(do_create_globalpermissiongroup, '002')

    def test_expand_globalpermissiongroup(self):
        model_obj = self.create_test_object()
        expandables = ['user']
        self._test_expandables(model_obj, expandables)
