from hashtopolis import AccessGroup, Hashlist, Task
from utils import BaseTest


class ExpandTest(BaseTest):
    def test_accessgroups_usermembers_m2m(self):
        # Many-to-many casting
        objs = AccessGroup.objects.all(expand='userMembers')

        # Check the default account
        self.assertEqual(objs[0].userMembers_set[0].name, 'admin')

    def test_crackerbinary_o2o(self):
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist)

        objs = Task.objects.filter(taskId=task.id, expand='crackerBinary')
        self.assertEqual(objs[0].crackerBinary_set.binaryName, 'hashcat')

    def test_individual_object_expanding(self):
        hashlist = self.create_hashlist()

        obj = Hashlist.objects.get(pk=hashlist.id, expand='hashes')
        self.assertEqual('cc03e747a6afbbcbf8be7668acfebee5', obj.hashes_set[0].hash)
