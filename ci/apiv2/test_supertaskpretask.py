from hashtopolis import HashtopolisError, Helper, Model, Pretask, Supertask, Task

from utils import BaseTest, get_bearer_token, get_hashtopolis_uri
import requests

APIV2 = get_hashtopolis_uri() + '/api/v2'


class SupertaskPretask(Model, uri="/ui/supertaskpretasks"):
    pass  # associations are created and removed through the pretasks relationship of the supertask


class SupertaskPretaskTest(BaseTest):
    model_class = SupertaskPretask

    def _associations_of(self, supertask):
        return list(SupertaskPretask.objects.filter(supertaskId=supertask.id))

    def _association_of(self, supertask, pretask):
        associations = [association for association in self._associations_of(supertask)
                        if association.pretaskId == pretask.id]
        self.assertEqual(1, len(associations))
        return associations[0]

    def create_test_object(self, *nargs, **kwargs):
        pretasks = [self.create_pretask() for _ in range(2)]
        supertask = self.create_supertask(pretasks=pretasks, *nargs, **kwargs)
        return self._association_of(supertask, pretasks[0])

    def test_create_takes_the_priority_of_the_pretask(self):
        pretask = self.create_pretask(extra_payload={'priority': 17})
        supertask = self.create_supertask(pretasks=[pretask])
        self.assertEqual(17, self._association_of(supertask, pretask).priority)

    def test_patch_priority(self):
        pretask = self.create_pretask(extra_payload={'priority': 17})
        supertask = self.create_supertask(pretasks=[pretask])
        association = self._association_of(supertask, pretask)

        association.priority = 42
        association.save()

        self.assertEqual(42, SupertaskPretask.objects.get(pk=association.id).priority)
        # the pretask keeps the priority it was created with
        self.assertEqual(17, Pretask.objects.get(pk=pretask.id).priority)

    def test_patch_priority_is_per_supertask(self):
        """The bug this column exists for: two supertasks sharing one pretask."""
        pretask = self.create_pretask(extra_payload={'priority': 17})
        supertask_a = self.create_supertask(pretasks=[pretask])
        supertask_b = self.create_supertask(pretasks=[pretask])

        association_a = self._association_of(supertask_a, pretask)
        association_a.priority = 42
        association_a.save()

        self.assertEqual(42, self._association_of(supertask_a, pretask).priority)
        self.assertEqual(17, self._association_of(supertask_b, pretask).priority)
        self.assertEqual(17, Pretask.objects.get(pk=pretask.id).priority)

    def test_patch_priority_of_pretask_leaves_associations_alone(self):
        pretask = self.create_pretask(extra_payload={'priority': 17})
        supertask = self.create_supertask(pretasks=[pretask])

        work_obj = Pretask.objects.get(pk=pretask.id)
        work_obj.priority = 3
        work_obj.save()

        self.assertEqual(17, self._association_of(supertask, pretask).priority)

    def test_filter_by_supertask(self):
        pretasks = [self.create_pretask(extra_payload={'priority': priority}) for priority in (5, 9)]
        supertask = self.create_supertask(pretasks=pretasks)
        associations = self._associations_of(supertask)
        self.assertEqual(sorted([(pretask.id, pretask.priority) for pretask in pretasks]),
                         sorted([(association.pretaskId, association.priority)
                                 for association in associations]))

    def test_add_through_relationship_takes_the_priority_of_the_pretask(self):
        supertask = self.create_supertask(pretasks=[self.create_pretask()])
        added = self.create_pretask(extra_payload={'priority': 23})
        headers = {'Authorization': f'Bearer {get_bearer_token()}',
                   'Content-Type': 'application/json'}

        response = requests.post(f'{APIV2}/ui/supertasks/{supertask.id}/relationships/pretasks',
                                 headers=headers,
                                 json={'data': [{'type': 'pretask', 'id': added.id}]})
        self.assertEqual(201, response.status_code, response.text)
        self.assertEqual(23, self._association_of(supertask, added).priority)

    def test_patch_relationship_keeps_the_priority_of_a_kept_pretask(self):
        kept = self.create_pretask(extra_payload={'priority': 17})
        supertask = self.create_supertask(pretasks=[kept, self.create_pretask()])
        association = self._association_of(supertask, kept)
        association.priority = 42
        association.save()

        work_obj = Supertask.objects.prefetch_related('pretasks').get(pk=supertask.id)
        added = self.create_pretask(extra_payload={'priority': 8})
        work_obj.pretasks_set = [pretask for pretask in work_obj.pretasks_set
                                 if pretask.id == kept.id] + [added]
        work_obj.save()

        self.assertEqual(42, self._association_of(supertask, kept).priority)
        self.assertEqual(8, self._association_of(supertask, added).priority)

    def test_post_and_delete_are_rejected(self):
        pretask = self.create_pretask()
        supertask = self.create_supertask(pretasks=[pretask])
        association = self._association_of(supertask, pretask)
        headers = {'Authorization': f'Bearer {get_bearer_token()}',
                   'Content-Type': 'application/json'}

        response = requests.post(f'{APIV2}/ui/supertaskpretasks', headers=headers,
                                 json={'data': {'type': 'supertaskPretask',
                                                'attributes': {'supertaskId': supertask.id,
                                                               'pretaskId': pretask.id,
                                                               'priority': 1}}})
        self.assertEqual(405, response.status_code, response.text)

        response = requests.delete(f'{APIV2}/ui/supertaskpretasks/{association.id}', headers=headers)
        self.assertEqual(405, response.status_code, response.text)

    def test_run_supertask_uses_the_association_priority(self):
        pretasks = [self.create_pretask(extra_payload={'priority': 17}) for _ in range(2)]
        supertask = self.create_supertask(pretasks=pretasks)
        for priority, pretask in enumerate(pretasks, start=1):
            association = self._association_of(supertask, pretask)
            association.priority = priority
            association.save()

        hashlist = self.create_hashlist()
        cracker = self.create_cracker()
        task_wrapper = Helper().create_supertask(supertask, hashlist, cracker)
        self.delete_after_test(task_wrapper)

        tasks = list(Task.objects.filter(taskWrapperId=task_wrapper.id))
        self.assertEqual({1, 2}, {task.priority for task in tasks})
        # the wrapper takes the lowest priority of the subtasks
        self.assertEqual(1, task_wrapper.priority)
