from hashtopolis import TaskWrapperDisplay, TaskWrapperDisplayHelper
from utils import BaseTest


class TaskWrapperDisplayTest(BaseTest):
    model_class = TaskWrapperDisplay
    def test_toast(self):
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist)
        expected_color_value = str(task.color)
        display = TaskWrapperDisplay.objects.get(id=task.taskWrapperId)
        self.assertEqual(display.color, expected_color_value)

    def test_taskwrapperdisplays_returns_color_field(self):
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist)
        values = TaskWrapperDisplayHelper().get_task_wrapper_display()
        data_items = values.get('data') or []
        expected_id = str(task.taskWrapperId)
        color_value = None
        expected_color_value = str(task.color)
        for item in data_items:
            if str(item.get('id')) == expected_id:
                color_value = item.get('attributes', {}).get('color')
                break
        self.assertIsNotNone(color_value)
        self.assertEqual(expected_color_value, color_value)
        self.assertNotEqual("ff0000", color_value)
