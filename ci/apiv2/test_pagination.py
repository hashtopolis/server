from hashtopolis import Hashlist, HashType
from utils import BaseTest
import json
from base64 import b64encode


class PaginationTest(BaseTest):
    model_class = HashType

    def pagination_test_helper(self, after, size):
        after_dict = {"primary": {"hashTypeId": after}}
        after_param = b64encode(json.dumps(after_dict).encode('utf-8')).decode('utf-8')
        objs = HashType.objects.paginate(size=size, after=after_param).get_pagination()
        all_objs = list(HashType.objects.all())
        index = None
        for idx, obj in enumerate(all_objs):
            if obj.id > after:
                index = idx
                break

        self.assertIsNotNone(index)
        self.assertEqual(objs, all_objs[index:index+size])
        pass

    def pagination_with_ordering_helper(self):
        hashlist1 = self.create_hashlist()
        hashlist2 = self.create_hashlist()

        after_dict = {"primary": {"cracked": 0}, "secondary": {"hashlistId": hashlist1.id}}
        after_param = b64encode(json.dumps(after_dict).encode('utf-8')).decode('utf-8')
        
        objs = Hashlist.objects.paginate(size=1, after=after_param).filter(format__nin=3).order_by('cracked').get_pagination()
        self.assertEqual(objs[0].id, hashlist2.id)

        pass

    def test_get_page(self):
        # TODO test can be randomised to get more coverage
        self.pagination_test_helper(1200, 25)
        self.pagination_test_helper(2500, 50)
        self.pagination_test_helper(20, 10)

        self.pagination_with_ordering_helper()
