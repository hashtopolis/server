from hashtopolis import HashtopolisResponseError, Speed
from utils import BaseTest


class SpeedTest(BaseTest):
    model_class = Speed

    def create_test_object(self, *nargs, **kwargs):
        retval = self.create_agent_with_task(*nargs, **kwargs)
        return Speed.objects.filter(taskId=retval['task'].id)[0]

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        # Patching should not be possible via API
        model_obj = self.create_test_object()
        with self.assertRaises(HashtopolisResponseError) as e:
            self._test_patch(model_obj, 'speed', 1234)
        self.assertEqual(e.exception.status_code, 405)

    def test_delete(self):
        # Delete should not be possible via API
        model_obj = self.create_test_object()
        with self.assertRaises(HashtopolisResponseError) as e:
            self._test_delete(model_obj)
        self.assertEqual(e.exception.status_code, 405)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['agent', 'task']
        self._test_expandables(model_obj, expandables)
