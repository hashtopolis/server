from hashtopolis import HashType
from utils import BaseTest


class FilterTest(BaseTest):
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

    def test_filter(self):
        model_objs = self.create_test_objects()
        objs = HashType.objects.filter(hashTypeId__gte=90000, hashTypeId__lte=91000)
        self.assertEqual([x.id for x in model_objs], [x.id for x in objs])

    def test_ordering(self):
        model_objs = self.create_test_objects()
        objs = HashType.objects.filter(hashTypeId__gte=90000, hashTypeId__lte=91000,
                                       ordering=['-hashTypeId'])  
        sorted_model_objs = sorted(model_objs, key=lambda x: x.hashTypeId, reverse=True)
        self.assertEqual([x.id for x in sorted_model_objs], [x.id for x in objs])

    def test_ordering_twice(self):
        model_objs = self.create_test_objects()
        objs = HashType.objects.filter(hashTypeId__gte=90000, hashTypeId__lte=91000,
                                       ordering=['-isSalted', '-hashTypeId'])
        sorted_model_objs = sorted(model_objs, key=lambda x: (x.isSalted, x.hashTypeId), reverse=True)
        self.assertEqual([x.id for x in sorted_model_objs], [x.id for x in objs])
