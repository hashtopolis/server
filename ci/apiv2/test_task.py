from hashtopolis import Task
from utils import BaseTest
from utils import do_create_hashlist, do_create_task


class TaskTest(BaseTest):
    model_class = Task

    def create_test_object(self, file_id='001', delete=True):
        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        model_obj = do_create_task(hashlist, file_id)
        if delete:
            self.delete_after_test(model_obj)
        return model_obj

    def test_create_task(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_expand_task(self):
        model_obj = self.create_test_object()
        expandables = ['hashlist']
        self._test_expandables(model_obj, expandables)

    def test_patch_task(self):
        model_obj = self.create_test_object()
        attr = 'taskName'
        self._test_patch(model_obj, attr)

    def test_patch_color_null(self):
        task = self.create_test_object()

        task.color = None
        task.save()

        obj = Task.objects.get(taskId=task.id)
        self.assertEqual(obj.color, '')

    def test_runtime(self):
        task = self.create_test_object('002')

        obj = Task.objects.get(taskId=task.id)
        self.assertEqual(obj.useNewBench, 0)

    def test_speed(self):
        task = self.create_test_object()

        obj = Task.objects.get(taskId=task.id)
        self.assertEqual(obj.useNewBench, 1)

    def test_preprocessor(self):
        task = self.create_test_object()

        obj = Task.objects.get(taskId=task.id)
        self.assertEqual(obj.preprocessorCommand, "this-is-prepressor")
        self.assertEqual(obj.preprocessorId, 1)

    def test_archive_filter(self):
        task = self.create_test_object()

        task.isArchived = True
        task.save()

        # Use filter to get archived tasks
        test_obj = Task.objects.filter(taskId=task.id, isArchived=True)

        self.assertEqual(len(test_obj), 1)
        self.assertTrue(test_obj[0].isArchived)
