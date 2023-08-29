from hashtopolis import Supertask
from utils import BaseTest


class SupertaskTest(BaseTest):
    model_class = Supertask

    def create_test_object(self, *nargs, **kwargs):
        pretasks = [self.create_pretask() for i in range(2)]
        return self.create_supertask(pretasks=pretasks, *nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'supertaskName')

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['pretasks']
        self._test_expandables(model_obj, expandables)

    def test_new_pretasks(self):
        model_obj = self.create_test_object()

        # Quirk for expanding object to allow update to take place
        work_obj = Supertask.objects.get(pk=model_obj.id, expand='pretasks')
        new_pretasks = [self.create_pretask() for i in range(2)]
        selected_pretasks = [work_obj.pretasks_set[0], new_pretasks[1]]
        work_obj.pretasks_set = selected_pretasks
        work_obj.save()

        obj = Supertask.objects.get(pk=model_obj.id, expand='pretasks')
        self.assertListEqual(selected_pretasks, obj.pretasks_set)
