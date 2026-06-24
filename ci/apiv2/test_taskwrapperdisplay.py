from hashtopolis import TaskWrapperDisplay, Helper
import re

from utils import BaseTest
class TaskWrapperDisplayTest(BaseTest):
    model_class = TaskWrapperDisplay

    def create_test_object(self, *nargs, delete=True, **kwargs):
        # Always cleanup hashlist when done, this is potentially confusing,
        # since it will also remove the related task
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist, delete=delete)
        return TaskWrapperDisplay.objects.get(pk=task.taskWrapperId)

    def test_task_wrapper_display_should_return_color_field(self):
        task_wrapper_display_object = self.create_test_object()
        expected_color_value = str(task_wrapper_display_object.color)
        self.assertIsNotNone(task_wrapper_display_object.color)
        self.assertEqual(task_wrapper_display_object.color, expected_color_value)
        self.assertNotEqual("FFFFFF", task_wrapper_display_object.color)

    def test_aggregate_data_on_supertask(self):
        pretasks = [self.create_pretask() for _ in range(2)]
        supertask = self.create_supertask(pretasks=pretasks)
        cracker = self.create_cracker()
        hashlist = self.create_hashlist()

        helper = Helper()
        task_wrapper = helper.create_supertask(supertask, hashlist, cracker)
        aggregate_attrs = ['timeSpent', 'searched', 'dispatched', 'currentSpeed', 'cprogress']
        task_wrapper_display = TaskWrapperDisplay.objects.params(**{"aggregate[taskwrapperdisplay]": ','.join(aggregate_attrs)}).get(taskWrapperId=task_wrapper.id)
        self.assertFalse(any(hasattr(task_wrapper_display, attr) for attr in aggregate_attrs), f"Aggregate attributes should not be set: {', '.join(aggregate_attrs)}")

    def test_aggregate_data_on_normal_task(self):
        task_wrapper_display_object = self.create_test_object()
        aggregate_attrs = ['totalAssignedAgents', 'searched', 'dispatched', 'status', 'currentSpeed']
        task_wrapper_display = TaskWrapperDisplay.objects.params(**{"aggregate[taskwrapperdisplay]": ','.join(aggregate_attrs)}).get(taskWrapperId=task_wrapper_display_object.id)
        self.assertTrue(all(hasattr(task_wrapper_display, a) for a in aggregate_attrs), f"Aggregate attributes should be set: {', '.join(aggregate_attrs)}")
        self.assertIsNotNone(re.fullmatch(r"\d+", str(task_wrapper_display.totalAssignedAgents)), "Attribute 'totalAssignedAgents' should be numeric")
        self.assertIsNotNone(re.fullmatch(r"\d?\d?\d\.\d{2}", str(task_wrapper_display.searched)), "Attribute 'searched' should be a decimal string")
        self.assertIsNotNone(re.fullmatch(r"\d?\d?\d\.\d{2}", str(task_wrapper_display.dispatched)), "Attribute 'dispatched' should be a decimal string")
        self.assertIsInstance(task_wrapper_display.status, int, "Attribute 'status' should be of type int")
        self.assertIsInstance(task_wrapper_display.currentSpeed, int, "Attribute 'currentSpeed' should be of type int")