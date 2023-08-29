from hashtopolis import Notification
from utils import BaseTest


class NotificationTest(BaseTest):
    model_class = Notification

    def create_test_object(self, *nargs, **kwargs):
        return self.create_notification(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'receiver', 'dummy-user@example.com')

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['user']
        self._test_expandables(model_obj, expandables)
