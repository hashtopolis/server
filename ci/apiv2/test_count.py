from hashtopolis import Hashlist
from hashtopolis import HashType
from hashtopolis import HashtopolisError
from utils import BaseTest, do_create_hashlist


class CountTest(BaseTest):
    model_class = HashType

    def create_test_objects(self, **kwargs):
        objs = []
        for i in range(90000, 90100, 10):
            obj = HashType(hashTypeId=i,
                           description=f"Dummy HashType {i}",
                           isSalted=(i < 90050),
                           isSlowHash=False).save()
            objs.append(obj)
            self.delete_after_test(obj)
        return objs

    def test_count(self):
        model_objs = self.create_test_objects()
        model_count = len(model_objs)
        api_count = HashType.objects.count(hashTypeId__gte=90000, hashTypeId__lte=91000)['count']
        self.assertEqual(model_count, api_count)

    def test_count_by_id(self):
        """The generic 'id' key must address the primary key, exactly like it does when listing."""
        model_obj = self.create_test_objects()[0]
        self.assertEqual(HashType.objects.count(id=model_obj.id)['count'], 1)

    def test_count_by_aliased_field(self):
        """Filtering has to use the alias of a field, not the name of its database column."""
        wanted = do_create_hashlist(extra_payload={'name': 'Hashlist-count-alias-wanted'})
        self.delete_after_test(wanted)
        other = do_create_hashlist(extra_payload={'name': 'Hashlist-count-alias-other'})
        self.delete_after_test(other)

        counted = Hashlist.objects.count(name=wanted.name)['count']
        listed = len(list(Hashlist.objects.filter(name=wanted.name)))
        self.assertEqual(counted, listed, "count must agree with the list endpoint")
        # both hashlists exist, so an applied filter can never count all of them
        self.assertLess(counted, Hashlist.objects.count()['count'])

        # 'hashlistName' is the column behind the 'name' alias and must not be accepted
        with self.assertRaises(HashtopolisError):
            Hashlist.objects.count(hashlistName=wanted.name)

    def test_count_rejects_unknown_filter(self):
        """An unusable filter must fail loudly, silently ignoring it reports an unfiltered count."""
        self.create_test_objects()
        with self.assertRaises(HashtopolisError):
            HashType.objects.count(thisFieldDoesNotExist=1)
