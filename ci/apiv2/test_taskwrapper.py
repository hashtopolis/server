from hashtopolis import HashtopolisResponseError, TaskWrapper
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

    def test_patch(self):
        model_obj = self.create_test_object()
        with self.assertRaises(HashtopolisResponseError) as e:
            self._test_patch(model_obj, 'taskWrapperName')
        self.assertEqual(e.exception.status_code, 500)

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        with self.assertRaises(HashtopolisResponseError) as e:
            self._test_delete(model_obj)
        self.assertEqual(e.exception.status_code, 500)

    def test_expand(self):
        model_obj = self.create_test_object()
        expandables = ['accessGroup', 'tasks']
        self._test_expandables(model_obj, expandables)
