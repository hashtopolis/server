from hashtopolis import Task
from utils import BaseTest


class TaskTest(BaseTest):
    model_class = Task

    def create_test_object(self, **kwargs):
        hashlist_kwargs = kwargs.copy()
        hashlist_kwargs['file_id'] = kwargs.get('hashlist_file_id', '001')

        hashlist = self.create_hashlist(**hashlist_kwargs)
        task = self.create_task(hashlist=hashlist, **kwargs)
        return task

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_expand(self):
        model_obj = self.create_test_object()
        expandables = ['assignedAgents', 'crackerBinary', 'crackerBinaryType', 'hashlist', 'files', 'speeds']
        self._test_expandables(model_obj, expandables)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'taskName')

    def test_patch_color_null(self):
        task = self.create_test_object()

        task.color = None
        task.save()

        obj = Task.objects.get(taskId=task.id)
        self.assertIsNone(obj.color)

    def test_runtime(self):
        task = self.create_test_object(file_id='002')

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

    def test_task_with_file(self):
        hashlist = self.create_hashlist()
        files = [self.create_file()]

        #  Not part of default model fields, how-ever expanded field
        extra_payload = dict(files=[x.id for x in files])
        task = self.create_task(hashlist, extra_payload=extra_payload)
        obj = Task.objects.get(pk=task.id, expand='files')
        self.assertListEqual([x.id for x in files], [x.id for x in obj.files_set])
