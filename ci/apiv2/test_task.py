from hashtopolis import Task, TaskWrapper
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
        obj = Task.objects.prefetch_related('files').get(pk=task.id)
        self.assertListEqual([x.id for x in files], [x.id for x in obj.files_set])

    def test_task_update_priority(self):
        task = self.create_test_object()
        obj = TaskWrapper.objects.get(pk=task.taskWrapperId)
        self.assertEqual(task.priority, obj.priority)

        new_priority = task.priority + 1234
        task.priority = new_priority
        task.save()

        obj = TaskWrapper.objects.get(pk=task.taskWrapperId)
        self.assertEqual(new_priority, obj.priority)
  
    def test_task_update_maxagent(self):
        task = self.create_test_object()
        obj = TaskWrapper.objects.get(pk=task.taskWrapperId)
        self.assertEqual(task.maxAgents, obj.maxAgents)

        new_maxagent = task.maxAgents + 1234
        task.maxAgents = new_maxagent
        task.save()

        obj = TaskWrapper.objects.get(pk=task.taskWrapperId)
        self.assertEqual(new_maxagent, obj.maxAgents)

    def test_bulk_archive(self):
        tasks = [self.create_test_object() for i in range(5)]
        active_attributes = [True for i in range(5)]
        Task.objects.patch_many(tasks, active_attributes, "isArchived")

    def test_toggle_archive_task(self):
        # Test normal task archiving
        task = self.create_test_object()

        # Initially task should not be archived
        self.assertFalse(task.isArchived)

        # Archive the task
        task.isArchived = True
        task.save()

        # Verify task is archived
        obj = Task.objects.get(taskId=task.id)
        self.assertTrue(obj.isArchived)

        # Verify taskwrapper is also archived for normal tasks
        wrapper = TaskWrapper.objects.get(pk=task.taskWrapperId)
        self.assertTrue(wrapper.isArchived)

        # Unarchive the task
        task.isArchived = False
        task.save()

        # Verify task is unarchived
        obj = Task.objects.get(taskId=task.id)
        self.assertFalse(obj.isArchived)

        # Verify taskwrapper is also unarchived
        wrapper = TaskWrapper.objects.get(pk=task.taskWrapperId)
        self.assertFalse(wrapper.isArchived)
