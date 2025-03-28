from hashtopolis import HashType
from utils import BaseTest


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
