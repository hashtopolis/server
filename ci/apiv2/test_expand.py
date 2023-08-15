from hashtopolis import AccessGroup, Hashlist, Task
from utils import BaseTest
from utils import do_create_hashlist, do_create_task


class ExpandTest(BaseTest):
    def test_task_crackerbinary_o2o(self):
        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        task = do_create_task(hashlist)
        self.delete_after_test(task)

        # One-to-one casting
        objs = Task.objects.filter(taskId=task.id, expand='crackerBinary')
        self.assertEqual(objs[0].crackerBinary_set.binaryName, 'hashcat')

    def test_accessgroups_usermembers_m2m(self):
        # Many-to-many casting
        objs = AccessGroup.objects.all(expand='userMembers')

        # Check the default account
        self.assertEqual(objs[0].userMembers_set[0].name, 'admin')

    def test_individual_object_expanding(self):
        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        task = do_create_task(hashlist)
        self.delete_after_test(task)

        obj = Hashlist.objects.get(pk=hashlist.id, expand='hashes')
        self.assertEqual('cc03e747a6afbbcbf8be7668acfebee5', obj.hashes_set[0].hash)
