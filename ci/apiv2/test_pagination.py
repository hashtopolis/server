from hashtopolis import HashType
from utils import BaseTest
import logging


class PaginationTest(BaseTest):
    model_class = HashType

    def pagination_test_helper(self, after, size):
        # logger = logging.getLogger(__name__)
        objs = HashType.objects.paginate(size=size, after=after).get_pagination()
        all_objs = list(HashType.objects.all())
        index = None
        for idx, obj in enumerate(all_objs):
            if obj.id > after:
                index = idx
                break
        
        self.assertIsNotNone(index)
        self.assertEqual(objs, all_objs[index:index+size])
        pass

    def test_get_page(self):
        # TODO test can be randomised to get more coverage
        self.pagination_test_helper(1200, 25)
        self.pagination_test_helper(2500, 50)
        self.pagination_test_helper(20, 10)