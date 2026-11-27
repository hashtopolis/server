import json
from pathlib import Path

from hashtopolis import HashtopolisError, Notification
from utils import BaseTest, create_restricted_user


def _notification_payload(**overrides):
    payload = json.loads(Path(__file__).parent.joinpath('testfiles/notification/create_notification_001.json').read_text('UTF-8'))
    payload.update(overrides)
    return payload


class NotificationTest(BaseTest):
    model_class = Notification

    def create_test_object(self, *nargs, **kwargs):
        return self.create_notification(*nargs, **kwargs)

    @property
    def conn(self):
        return Notification.objects.get_conn()

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_create_is_active_is_honored(self):
        model_obj = Notification(**_notification_payload(isActive=False))
        model_obj.save()
        self.delete_after_test(model_obj)

        obj = Notification.objects.get(pk=model_obj.id)
        self.assertFalse(obj.isActive, "isActive=false must be honored on creation")

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'receiver', 'dummy-user@example.com')

    def test_patch_action(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'action', 'logError')

    def test_patch_action_same_object_type_keeps_object_id(self):
        """A notification tied to an object can only change to actions of the same object type."""
        model_obj = Notification(**_notification_payload(action='userLoginFailed', actionFilter='1'))
        model_obj.save()
        self.delete_after_test(model_obj)

        # userDeleted is a user action type as well, so the object binding is kept
        model_obj.action = 'userDeleted'
        model_obj.save()
        obj = Notification.objects.get(pk=model_obj.id)
        self.assertEqual(obj.objectId, 1)

        # newAgent is no user action type, so the object binding cannot be kept
        model_obj.action = 'newAgent'
        with self.assertRaises(HashtopolisError) as e:
            model_obj.save()
        self.assertIn(e.exception.status_code, [400, 403])

    def test_patch_notification_channel(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'notification', 'Email')

    def test_patch_invalid_notification_channel(self):
        model_obj = self.create_test_object()
        model_obj.notification = 'DoesNotExist'
        with self.assertRaises(HashtopolisError) as e:
            model_obj.save()
        self.assertIn(e.exception.status_code, [400, 403])

    def test_patch_empty_receiver_rejected(self):
        model_obj = self.create_test_object()
        model_obj.receiver = '   '
        with self.assertRaises(HashtopolisError) as e:
            model_obj.save()
        self.assertIn(e.exception.status_code, [400, 403])

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['user']
        self._test_expandables(model_obj, expandables)

    def test_acl(self):
        model_obj = self.create_test_object()
        self._test_acl_list(model_obj, {'permNotificationSettingRead': True})
        self._test_acl_count(model_obj, {'permNotificationSettingRead': True})

    def test_acl_get_single(self):
        """A restricted user must not be able to read a notification setting of another user."""
        model_obj = self.create_test_object()
        auth = create_restricted_user(self, {'permNotificationSettingRead': True})
        with self.assertRaises(HashtopolisError) as e:
            self.conn.get_one(model_obj.id, None, auth=auth)
        self.assertIn(e.exception.status_code, [400, 403, 404])

    def test_acl_patch(self):
        """A restricted user must not be able to update a notification setting of another user."""
        model_obj = self.create_test_object()
        auth = create_restricted_user(self, {'permNotificationSettingRead': True})
        model_obj.receiver = 'restricted-user@example.com'
        with self.assertRaises(HashtopolisError) as e:
            self.conn.patch_one(model_obj, auth=auth)
        self.assertIn(e.exception.status_code, [400, 403])

        # the change must not have been applied
        obj = Notification.objects.get(pk=model_obj.id)
        self.assertNotEqual(obj.receiver, 'restricted-user@example.com')

    def test_acl_delete(self):
        """A restricted user must not be able to delete a notification setting of another user."""
        model_obj = self.create_test_object()
        auth = create_restricted_user(self, {'permNotificationSettingRead': True})
        with self.assertRaises(HashtopolisError) as e:
            self.conn.delete(model_obj, auth=auth)
        self.assertIn(e.exception.status_code, [400, 403])

        # the notification still exists, teardown (running as admin) removes it
        obj = Notification.objects.get(pk=model_obj.id)
        self.assertIsNotNone(obj)

    def test_rejected_patch_is_not_partially_applied(self):
        """A PATCH with a valid and an invalid attribute must not apply the valid one."""
        model_obj = self.create_test_object()
        original_action = model_obj.action
        original_notification = model_obj.notification

        model_obj.action = 'logError'  # valid
        model_obj.notification = 'DoesNotExist'  # invalid
        with self.assertRaises(HashtopolisError) as e:
            model_obj.save()
        self.assertIn(e.exception.status_code, [400, 403])

        obj = Notification.objects.get(pk=model_obj.id)
        self.assertEqual(obj.action, original_action, "action must be unchanged by the rejected PATCH")
        self.assertEqual(obj.notification, original_notification)

    def test_admin_manages_all_users(self):
        """An administrator can update and delete the notification settings of other users."""
        auth = create_restricted_user(self, {'permNotificationSettingRead': True, 'permAgentRead': True})
        model_obj = Notification(**_notification_payload())
        self.conn.create(model_obj, auth=auth)

        # an administrator sees the notification settings of all users
        objs = list(Notification.objects.filter(id=model_obj.id))
        self.assertGreater(len(objs), 0, "Admin should be able to list the notification settings of all users")

        # an administrator can update the settings of another user
        model_obj.receiver = 'admin@example.com'
        model_obj.save()
        obj = Notification.objects.get(pk=model_obj.id)
        self.assertEqual(obj.receiver, 'admin@example.com')

        # and delete them
        model_obj.delete()
        with self.assertRaises(Notification.DoesNotExist):
            Notification.objects.get(pk=model_obj.id)