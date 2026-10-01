from hashtopolis import Supertask, HashtopolisError, Helper, Task, Cracker
from utils import BaseTest
import time


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
