from hashtopolis import Helper, HashtopolisError, TaskWrapper
from utils import BaseTest


class TaskWrapperTest(BaseTest):
    model_class = TaskWrapper

    def create_test_object(self, *nargs, **kwargs):
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist)
        return TaskWrapper.objects.get(pk=task.taskWrapperId)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch_invalid_key(self):
        model_obj = self.create_test_object()
        # Internal error, since field is not defined
        with self.assertRaises(AttributeError):
            self._test_patch(model_obj, 'invalidKey', 2)

    def test_patch_immutable(self):
        model_obj = self.create_test_object()
        with self.assertRaises(HashtopolisError) as e:
            self._test_patch(model_obj, 'taskType', 2)
        self.assertEqual(e.exception.status_code, 500)

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expand(self):
        model_obj = self.create_test_object()
        expandables = ['accessGroup', 'tasks']
        self._test_expandables(model_obj, expandables)

    def test_patch_priority(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'priority', 100)

    def test_helper_create_supertask(self):
        pretasks = [self.create_pretask() for i in range(2)]
        supertask = self.create_supertask(pretasks=pretasks)
        cracker = self.create_cracker()
        hashlist = self.create_hashlist()

        helper = Helper()
        helper.create_supertask(supertask, hashlist, cracker)
        self.assertEqual(len(TaskWrapper.objects.filter(hashlistId=hashlist.id)), 1)
