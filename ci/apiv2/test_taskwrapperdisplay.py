from hashtopolis import TaskWrapperDisplay
from utils import BaseTest
class TaskWrapperDisplayTest(BaseTest):
    model_class = TaskWrapperDisplay
    
    def test_task_wrapper_display_should_return_color_field(self):
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist)
        expected_color_value = str(task.color)
        task_wrapper_Display_object = TaskWrapperDisplay.objects.get(pk=task.taskWrapperId)
        self.assertIsNotNone(task_wrapper_Display_object.color)
        self.assertEqual(task_wrapper_Display_object.color, expected_color_value)
        self.assertNotEqual("FFFFFF", task_wrapper_Display_object.color)