from hashtopolis import HashType
from utils import BaseTest, create_restricted_user


class HashTypeTest(BaseTest):
    model_class = HashType

    def create_test_object(self, *nargs, **kwargs):
        return self.create_hashtype(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'description')

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['crackerBinaries']
        self._test_expandables(model_obj, expandables)

    def test_visible_without_access_groups(self):
        """Hashtypes are a global catalog, not isolated by the access groups of cracker binaries.

        A user without any access group sees a hashtype which is not associated
        with any binary, both in the list and by id.
        """
        model_obj = self.create_test_object()
        auth = create_restricted_user(self, {'permHashTypeRead': True})
        objs = list(HashType.objects.filter(id=model_obj.id).authenticate(auth))
        self.assertEqual([model_obj.id], [obj.id for obj in objs])
        response = HashType.objects.get_conn().get_one(model_obj.id, None, auth=auth)
        self.assertEqual(model_obj.id, int(response['data']['id']))
