from hashtopolis import User, Helper, HashtopolisError
from utils import BaseTest


class UserTest(BaseTest):
    model_class = User

    def create_test_object(self, *nargs, **kwargs):
        return self.create_user(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        gp_group = self.create_globalpermissiongroup()
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'globalPermissionGroupId', gp_group.id)

    def test_patch_email(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'email', "some.valid@email.org")

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expand(self):
        model_obj = self.create_test_object()
        expandables = ['accessGroups', 'globalPermissionGroup']
        self._test_expandables(model_obj, expandables)

    def test_disable_enable_user(self):
        user = self.create_test_object()

        # set a password so we can afterwards test with
        password = "testing123"
        helper = Helper()
        helper.set_user_password(user, password)

        # Disable User
        user.isValid = False
        user.save()

        obj = User.objects.get(id=user.id)
        self.assertFalse(obj.isValid)

        # check that the user is not able to log in, even with the correct password
        helper = Helper()
        with self.assertRaises(HashtopolisError) as e:
            helper._test_authentication(user.name, password)
        self.assertEqual(e.exception.status_code, 401)
        self.assertEqual(e.exception.title, f"Authentication failed")

        #  Enable user
        user.isValid = True
        user.save()

        obj = User.objects.get(id=user.id)
        self.assertTrue(obj.isValid)

    def test_disable_own_user(self):
        # we assume on test setups, there is always user with id 1 existing which was initially created and used for the test run
        user = User.objects.get(id=1)
        user.isValid = False
        with self.assertRaises(HashtopolisError) as e:
            user.save()
        self.assertEqual(e.exception.status_code, 500)
        self.assertEqual(e.exception.title, f"You cannot disable yourself!")
        user = User.objects.get(id=1)
        self.assertTrue(user.isValid)

    def test_helper_set_user_password(self):
        user = self.create_test_object()
        newPassword = "testing123"
        helper = Helper()
        helper.set_user_password(user, newPassword)
        helper._test_authentication(user.name, newPassword)

    def test_helper_set_empty_user_password(self):
        user = self.create_test_object()
        helper = Helper()
        with self.assertRaises(HashtopolisError) as e:
            helper.set_user_password(user, "")
        self.assertEqual(e.exception.status_code, 500)
        self.assertEqual(e.exception.title, f"Password cannot be of zero length!")

    def test_bulk_deactivate(self):
        users = [self.create_test_object() for i in range(5)]
        active_attributes = [False for i in range(5)]
        User.objects.patch_many(users, active_attributes, "isValid")

    def test_patch_invalid_email(self):
        model_obj = self.create_test_object()
        model_obj.email = "this-is-no-email"
        with self.assertRaises(HashtopolisError) as e:
            model_obj.save()
        self.assertEqual(e.exception.status_code, 500)
        self.assertEqual(e.exception.title, f"Invalid email address!")

    def test_patch_empty_email(self):
        model_obj = self.create_test_object()
        model_obj.email = ""
        with self.assertRaises(HashtopolisError) as e:
            model_obj.save()
        self.assertEqual(e.exception.status_code, 500)
        self.assertEqual(e.exception.title, f"Invalid email address!")

    def test_patch_invalid_session_lifetime_zero(self):
        model_obj = self.create_test_object()
        model_obj.sessionLifetime = 0
        with self.assertRaises(HashtopolisError) as e:
            model_obj.save()
        self.assertEqual(e.exception.status_code, 500)
        self.assertEqual(e.exception.title, f"Lifetime must be larger than 1 minute and smaller than 48 hours!")

    def test_patch_invalid_session_lifetime_negative(self):
        model_obj = self.create_test_object()
        model_obj.sessionLifetime = -5000
        with self.assertRaises(HashtopolisError) as e:
            model_obj.save()
        self.assertEqual(e.exception.status_code, 500)
        self.assertEqual(e.exception.title, f"Lifetime must be larger than 1 minute and smaller than 48 hours!")

    def test_patch_invalid_session_lifetime_large(self):
        model_obj = self.create_test_object()
        model_obj.sessionLifetime = 500000
        with self.assertRaises(HashtopolisError) as e:
            model_obj.save()
        self.assertEqual(e.exception.status_code, 500)
        self.assertEqual(e.exception.title, f"Lifetime must be larger than 1 minute and smaller than 48 hours!")

    def test_patch_registeredSince(self):
        model_obj = self.create_test_object()
        model_obj.registeredSince = 123456
        with self.assertRaises(HashtopolisError) as e:
            model_obj.save()
        self.assertEqual(e.exception.status_code, 403)
        self.assertEqual(e.exception.title, f"Key 'registeredSince' is immutable")

    def test_patch_lastLoginDate(self):
        model_obj = self.create_test_object()
        model_obj.lastLoginDate = 99999999
        with self.assertRaises(HashtopolisError) as e:
            model_obj.save()
        self.assertEqual(e.exception.status_code, 403)
        self.assertEqual(e.exception.title, f"Key 'lastLoginDate' is immutable")

    def test_patch_username(self):
        model_obj = self.create_test_object()
        model_obj.name = "fancy-username"
        with self.assertRaises(HashtopolisError) as e:
            model_obj.save()
        self.assertEqual(e.exception.status_code, 403)
        self.assertEqual(e.exception.title, f"Key 'name' is immutable")

