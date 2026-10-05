from hashtopolis import Supertask, HashtopolisError, Helper, Task, Cracker
from utils import BaseTest, get_bearer_token, get_hashtopolis_uri
import requests
import time

APIV2 = get_hashtopolis_uri() + '/api/v2'


class SupertaskTest(BaseTest):
    model_class = Supertask

    def create_test_object(self, *nargs, **kwargs):
        pretasks = [self.create_pretask() for i in range(2)]
        return self.create_supertask(pretasks=pretasks, *nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_create_pretask_cracker_binary_type_mismatch(self):
        pretask = self.create_pretask()
        crackertype = self.create_crackertype()
        pretask_other_type = self.create_pretask(
            extra_payload={'crackerBinaryTypeId': crackertype.id})
        with self.assertRaises(HashtopolisError) as e:
            self.create_supertask(
                pretasks=[pretask, pretask_other_type],
                extra_payload={'crackerBinaryTypeId': 1},
                delete=False)
        self.assertEqual(e.exception.status_code, 400)
        self.assertIn('cannot be mixed', e.exception.title)

    def test_create_invalid_cracker_binary_type(self):
        pretask = self.create_pretask()
        with self.assertRaises(HashtopolisError) as e:
            self.create_supertask(
                pretasks=[pretask],
                extra_payload={'crackerBinaryTypeId': 999999},
                delete=False)
        self.assertEqual(e.exception.status_code, 400)
        self.assertIn('Invalid cracker binary type ID', e.exception.title)

    def test_run_supertask_cracker_binary_type_mismatch(self):
        supertask = self.create_test_object()
        hashlist = self.create_hashlist()
        crackertype = self.create_crackertype()
        binary_other_type = self.create_cracker(
            extra_payload={'crackerBinaryTypeId': crackertype.id})
        with self.assertRaises(HashtopolisError) as e:
            Helper().create_supertask(supertask, hashlist, binary_other_type)
        self.assertEqual(e.exception.status_code, 400)
        self.assertIn('cannot be mixed', e.exception.title)

    def test_run_supertask_creates_consistent_tasks(self):
        supertask = self.create_test_object()
        hashlist = self.create_hashlist()
        cracker = self.create_cracker()
        task_wrapper = Helper().create_supertask(supertask, hashlist, cracker)
        self.delete_after_test(task_wrapper)

        tasks = list(Task.objects.filter(taskWrapperId=task_wrapper.id))
        self.assertEqual(2, len(tasks))
        for task in tasks:
            self.assertEqual(cracker.id, task.crackerBinaryId)
            self.assertEqual(cracker.crackerBinaryTypeId, task.crackerBinaryTypeId)

    def test_run_supertask_cracker_hashtype_mismatch(self):
        crackertype = self.create_crackertype()
        pretasks = [self.create_pretask(extra_payload={'crackerBinaryTypeId': crackertype.id}) for _ in range(2)]
        supertask = self.create_supertask(pretasks=pretasks, extra_payload={'crackerBinaryTypeId': crackertype.id})
        cracker = self.create_cracker(extra_payload={'crackerBinaryTypeId': crackertype.id})
        hashlist = self.create_hashlist()
        # the binary only supports an unrelated hashtype, not the hashlist's one
        stamp = int(time.time() * 1000)
        hashtype = self.create_hashtype(extra_payload={'hashTypeId': 100001 + stamp % 99999,
                                                       'description': f'supertask-hashtype-{stamp}'})
        work_obj = Cracker.objects.prefetch_related('hashtypes').get(pk=cracker.id)
        work_obj.hashtypes_set = [hashtype]
        work_obj.save()

        with self.assertRaises(HashtopolisError) as e:
            Helper().create_supertask(supertask, hashlist, cracker)
        self.assertEqual(e.exception.status_code, 400)
        self.assertIn('does not support the hash type', e.exception.title)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'supertaskName')

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['pretasks']
        self._test_expandables(model_obj, expandables)

    def test_new_pretasks(self):
        model_obj = self.create_test_object()

        # Quirk for expanding object to allow update to take place
        work_obj = Supertask.objects.prefetch_related('pretasks').get(pk=model_obj.id)
        new_pretasks = [self.create_pretask(file_id="002") for i in range(2)]
        selected_pretasks = [work_obj.pretasks_set[0], new_pretasks[1]]
        work_obj.pretasks_set = selected_pretasks
        work_obj.save()

        obj = Supertask.objects.prefetch_related('pretasks').get(pk=model_obj.id)
        self.assertListEqual(selected_pretasks, obj.pretasks_set)

    def test_patch_pretask_cracker_binary_type_mismatch(self):
        supertask = self.create_test_object()
        crackertype = self.create_crackertype()
        pretask_other_type = self.create_pretask(
            extra_payload={'crackerBinaryTypeId': crackertype.id})
        work_obj = Supertask.objects.prefetch_related('pretasks').get(pk=supertask.id)
        with self.assertRaises(HashtopolisError) as e:
            work_obj.pretasks_set = [work_obj.pretasks_set[0], pretask_other_type]
            work_obj.save()
        self.assertEqual(e.exception.status_code, 400)
        self.assertIn('cannot be mixed', e.exception.title)

    def test_patch_pretasks_to_empty(self):
        supertask = self.create_test_object()
        work_obj = Supertask.objects.prefetch_related('pretasks').get(pk=supertask.id)
        with self.assertRaises(HashtopolisError) as e:
            work_obj.pretasks_set = []
            work_obj.save()
        self.assertEqual(e.exception.status_code, 400)
        self.assertIn('at least one pretask', e.exception.title)

    def test_patch_replace_pretasks_with_other_binary_type(self):
        supertask = self.create_test_object()
        crackertype = self.create_crackertype()
        pretask_other_type = self.create_pretask(
            extra_payload={'crackerBinaryTypeId': crackertype.id})
        work_obj = Supertask.objects.prefetch_related('pretasks').get(pk=supertask.id)
        work_obj.pretasks_set = [pretask_other_type]
        work_obj.save()

        obj = Supertask.objects.prefetch_related('pretasks').get(pk=supertask.id)
        self.assertListEqual([pretask_other_type.id],
                              [pretask.id for pretask in obj.pretasks_set])

    def test_post_relationship_pretask_cracker_binary_type_mismatch(self):
        supertask = self.create_test_object()
        crackertype = self.create_crackertype()
        pretask_other_type = self.create_pretask(
            extra_payload={'crackerBinaryTypeId': crackertype.id})
        pretask_same_type = self.create_pretask()
        headers = {'Authorization': f'Bearer {get_bearer_token()}',
                   'Content-Type': 'application/json'}
        url = f'{APIV2}/ui/supertasks/{supertask.id}/relationships/pretasks'

        response = requests.post(url, headers=headers,
                                 json={'data': [{'type': 'pretask', 'id': pretask_other_type.id}]})
        self.assertEqual(400, response.status_code, response.text)
        self.assertIn('cannot be mixed', response.text)

        # adding a pretask of the same type is allowed
        response = requests.post(url, headers=headers,
                                 json={'data': [{'type': 'pretask', 'id': pretask_same_type.id}]})
        self.assertEqual(201, response.status_code, response.text)
        obj = Supertask.objects.prefetch_related('pretasks').get(pk=supertask.id)
        self.assertIn(pretask_same_type.id, [pretask.id for pretask in obj.pretasks_set])

    def test_delete_all_relationship_pretasks(self):
        supertask = self.create_test_object()
        pretask_ids = [pretask.id for pretask in
                       Supertask.objects.prefetch_related('pretasks').get(pk=supertask.id).pretasks_set]
        headers = {'Authorization': f'Bearer {get_bearer_token()}',
                   'Content-Type': 'application/json'}
        url = f'{APIV2}/ui/supertasks/{supertask.id}/relationships/pretasks'

        # deleting all pretasks at once is rejected
        response = requests.delete(url, headers=headers,
                                    json={'data': [{'type': 'pretask', 'id': pretask_id}
                                                    for pretask_id in pretask_ids]})
        self.assertEqual(400, response.status_code, response.text)
        self.assertIn('at least one pretask', response.text)

        # deleting only some of them is allowed
        response = requests.delete(url, headers=headers,
                                    json={'data': [{'type': 'pretask', 'id': pretask_ids[0]}]})
        self.assertIn(response.status_code, [201, 204], response.text)
        obj = Supertask.objects.prefetch_related('pretasks').get(pk=supertask.id)
        self.assertListEqual(sorted(pretask_ids[1:]),
                              sorted(pretask.id for pretask in obj.pretasks_set))
