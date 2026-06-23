from hashtopolis import TaskWrapperDisplay, Helper, TaskWrapper
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
        task_wrapper_display = TaskWrapperDisplay.objects.params(**{"aggregate[taskwrapperdisplay]": "timeSpent,dispatched,searched,currentSpeed,cprogress"}).get(taskWrapperId=task_wrapper.id)
        assert not any([hasattr(task_wrapper_display, attr) for attr in ['timeSpent' ,'searched', 'dispatched', 'currentSpeed', 'cprogress']]), "Attribute 'timeSpent' should not be set"

    def test_aggregate_data_on_normal_task(self):
        task_wrapper_display_object = self.create_test_object()
        task_wrapper_display = TaskWrapperDisplay.objects.params(**{"aggregate[taskwrapperdisplay]": "totalAssignedAgents,searched,dispatched,status,currentSpeed"}).get(taskWrapperId=task_wrapper_display_object.id)
        assert all([hasattr(task_wrapper_display, attr) for attr in ['totalAssignedAgents' ,'searched', 'dispatched', 'status', 'currentSpeed']]), "Attributes 'totalAssignedAgents,searched,dispatched,status,currentSpeed' should be set"
        assert str(task_wrapper_display.totalAssignedAgents).isnumeric(), "Attribute 'totalAssignedAgents' should be numeric"
        assert re.match(r'\d?\d?\d\.\d\d', task_wrapper_display.searched), "Attribute 'searched' should be a decimal string"
        assert re.match(r'\d?\d?\d\.\d\d', task_wrapper_display.dispatched), "Attribute 'dispatched' should be decimal string"
        self.assertIsInstance(task_wrapper_display.status, int)
        self.assertIsInstance(task_wrapper_display.currentSpeed, int)