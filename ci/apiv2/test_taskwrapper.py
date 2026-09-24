from hashtopolis import Helper, HashtopolisError, TaskWrapper
from hashtopolis import Cracker
from utils import (BaseTest, create_restricted_user, get_bearer_token, get_cracker_archive_url,
                   get_hashtopolis_uri)

import requests


APIV2 = get_hashtopolis_uri() + '/api/v2'


class TaskWrapperTest(BaseTest):
    model_class = TaskWrapper

    def create_test_object(self, *nargs, delete=True, **kwargs):
        # Always cleanup hashlist when done, this is potentially confusing, 
        # since it will also remove the related task
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist, delete=delete)
        return TaskWrapper.objects.get(pk=task.taskWrapperId)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch_invalid_key(self):
        model_obj = self.create_test_object()
        # Internal error, since field is not defined
        with self.assertRaises(AttributeError):
            self._test_patch(model_obj, 'invalidKey', 2)

    def test_patch_immutable(self):
        model_obj = self.create_test_object()
        with self.assertRaises(HashtopolisError) as e:
            self._test_patch(model_obj, 'taskType', 2)
        self.assertEqual(e.exception.status_code, 403)

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expand(self):
        model_obj = self.create_test_object()
        expandables = ['accessGroup', 'tasks']
        self._test_expandables(model_obj, expandables)

    def test_patch_priority(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'priority', 100)

    def test_helper_create_supertask(self):
        pretasks = [self.create_pretask() for i in range(2)]
        supertask = self.create_supertask(pretasks=pretasks)
        cracker = self.create_cracker()
        hashlist = self.create_hashlist()

        helper = Helper()
        helper.create_supertask(supertask, hashlist, cracker)
        self.assertEqual(len(TaskWrapper.objects.filter(hashlistId=hashlist.id)), 1)

    def test_helper_create_supertask_generic_cracker(self):
        pretasks = [self.create_pretask() for i in range(2)]
        supertask = self.create_supertask(pretasks=pretasks)
        crackertype = self.create_crackertype()
        cracker = Cracker(
            crackerBinaryTypeId=crackertype.id,
            version='1.2.3',
            downloadUrl=get_cracker_archive_url(),
            binaryName='generic-x64',
            accessGroupId=1)
        cracker.save()
        self.delete_after_test(cracker)
        hashlist = self.create_hashlist()

        helper = Helper()
        taskwrapper = helper.create_supertask(supertask, hashlist, cracker)       
        objs = TaskWrapper.objects.filter(hashlistId=hashlist.id)
        self.assertEqual(len(objs), 1, "Should only create 1 TaskWrapper")
        self.assertEqual(taskwrapper, objs[0],
                         "Returned create_supertask object != object found by filter")

    def test_acl(self):
        model_obj = self.create_test_object()
        self._test_acl_list(model_obj, {'permTaskWrapperRead': True})
        self._test_acl_count(model_obj, {'permTaskWrapperRead': True})


class TestTaskWrapperToOneRelations(BaseTest):
    """The to-one relations of task wrappers.

    The hashType relation goes through the hashlist as intermediate table,
    the hashlist relation is a plain foreign key. Both are resolved from the
    wrapper and enforce the access group of the wrapper.
    """

    def test_related_resources_resolve_and_enforce_access_group(self):
        """The to-one routes resolve the related resources from the wrapper.

        Users without access to the wrapper's access group must not read the
        related resources or the expansions of the wrapper, even when they
        have the global read permissions.
        """
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist)
        wrapper = TaskWrapper.objects.get(pk=task.taskWrapperId)

        admin_headers = {'Authorization': f'Bearer {get_bearer_token()}'}

        # the hashType is resolved through the hashlist
        r = requests.get(f'{APIV2}/ui/taskwrappers/{wrapper.id}/hashType', headers=admin_headers)
        self.assertEqual(200, r.status_code, f'Related hashType should be readable: {r.text}')
        self.assertEqual(hashlist.hashTypeId, r.json()['data']['id'])

        # the hashlist is a plain foreign key relation
        r = requests.get(f'{APIV2}/ui/taskwrappers/{wrapper.id}/hashlist', headers=admin_headers)
        self.assertEqual(200, r.status_code, f'Related hashlist should be readable: {r.text}')
        self.assertEqual(hashlist.id, r.json()['data']['id'])

        # the hashType include is resolved through the hashlist as well
        wrapper_obj = TaskWrapper.objects.prefetch_related('hashType').get(pk=wrapper.id)
        self.assertEqual(hashlist.hashTypeId, wrapper_obj.hashType.id)

        # a user without access to the wrapper's access group is denied everywhere
        username, password = create_restricted_user(self, {
            'permTaskWrapperRead': True,
            'permHashlistRead': True,
            'permHashTypeRead': True,
        })
        r = requests.post(f'{APIV2}/auth/token', auth=(username, password))
        self.assertEqual(201, r.status_code, f'Could not get a token: {r.text}')
        headers = {'Authorization': f"Bearer {r.json()['token']}"}

        r = requests.get(f'{APIV2}/ui/taskwrappers/{wrapper.id}/hashType', headers=headers)
        self.assertEqual(403, r.status_code, f'Related hashType should be denied: {r.text}')
        r = requests.get(f'{APIV2}/ui/taskwrappers/{wrapper.id}/hashlist', headers=headers)
        self.assertEqual(403, r.status_code, f'Related hashlist should be denied: {r.text}')
        r = requests.get(f'{APIV2}/ui/taskwrappers/{wrapper.id}/relationships/hashlist', headers=headers)
        self.assertEqual(403, r.status_code, f'Relationship link should be denied: {r.text}')
        # the wrapper is not visible to the user at all, so there is no expansion
        with self.assertRaises(TaskWrapper.DoesNotExist):
            TaskWrapper.objects.prefetch_related('hashType') \
                .authenticate((username, password)).get(pk=wrapper.id)
