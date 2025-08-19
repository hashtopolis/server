from hashtopolis import GlobalPermissionGroup
from utils import BaseTest


class GlobalPermissionGroupTest(BaseTest):
    model_class = GlobalPermissionGroup

    def create_test_object(self, *nargs, **kwargs):
        return self.create_globalpermissiongroup(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()

        # with how the current testing framework, works, multiple permissions have to be set, otherwise conflicting
        # permissions, will be set to false by default
        attributes = ["permUserDelete", "permUserRead", "permUserUpdate", "permUserCreate", "permRightGroupCreate",
                      "permRightGroupDelete", "permRightGroupRead", "permRightGroupUpdate"]
        for attr in attributes:
            model_obj.permissions[attr] = True
        model_obj.save()

        # Request object from backend and validate PATCHed permission
        attr = 'permRightGroupCreate'
        obj = self.model_class.objects.get(pk=model_obj.id)
        self.assertTrue(obj.permissions[attr])

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expand(self):
        model_obj = self.create_test_object()
        expandables = ['userMembers']
        self._test_expandables(model_obj, expandables)
