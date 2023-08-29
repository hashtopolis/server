from hashtopolis import HashType
from utils import BaseTest
import pytest


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
        self.assertEqual(
            [x.id for x in model_objs],
            [x.id for x in objs])

    @pytest.mark.skip(reason="Broken due to bug https://github.com/hashtopolis/server/issues/968")
    def test_filter__contains(self):
        search_token = "SHA"
        objs = HashType.objects.filter(description__contains=search_token)
        all_objs = HashType.objects.all()
        self.assertEqual(
            [x.id for x in all_objs if search_token in x.description],
            [x.id for x in objs])

    @pytest.mark.skip(reason="Broken due to bug https://github.com/hashtopolis/server/issues/968")
    def test_filter__endswith(self):
        search_token = 'sha512'
        objs = HashType.objects.filter(description__endswith=search_token)
        all_objs = HashType.objects.all()
        self.assertEqual(
            [x.id for x in all_objs if x.description.endswith(search_token)],
            [x.id for x in objs])

    def test_filter__eq(self):
        objs = HashType.objects.filter(hashTypeId__eq=100)
        all_objs = HashType.objects.all()
        self.assertEqual(
            [x.id for x in all_objs if x.hashTypeId == 100],
            [x.id for x in objs])

    def test_filter__gt(self):
        objs = HashType.objects.filter(hashTypeId__gt=8000)
        all_objs = HashType.objects.all()
        self.assertEqual(
            [x.id for x in all_objs if x.hashTypeId > 8000],
            [x.id for x in objs])

    def test_filter__gte(self):
        objs = HashType.objects.filter(hashTypeId__gte=8000)
        all_objs = HashType.objects.all()
        self.assertEqual(
            [x.id for x in all_objs if x.hashTypeId >= 8000],
            [x.id for x in objs])

    def test_filter__icontains(self):
        search_token = 'ShA'
        objs = HashType.objects.filter(description__icontains=search_token)
        all_objs = HashType.objects.all()
        self.assertEqual(
            [x.id for x in all_objs if search_token.lower() in x.description.lower()],
            [x.id for x in objs])

    def test_filter__iendswith(self):
        search_token = 'sHa512'
        objs = HashType.objects.filter(description__iendswith=search_token)
        all_objs = HashType.objects.all()
        self.assertEqual(
            [x.id for x in all_objs if x.description.lower().endswith(search_token.lower())],
            [x.id for x in objs])

    def test_filter__istartswith(self):
        search_token = 'NeTnTLM'
        objs = HashType.objects.filter(description__istartswith=search_token)
        all_objs = HashType.objects.all()
        self.assertEqual(
            [x.id for x in all_objs if x.description.lower().startswith(search_token.lower())],
            [x.id for x in objs])

    def test_filter__lt(self):
        objs = HashType.objects.filter(hashTypeId__lt=100)
        all_objs = HashType.objects.all()
        self.assertEqual(
            [x.id for x in all_objs if x.hashTypeId < 100],
            [x.id for x in objs])

    def test_filter__lte(self):
        objs = HashType.objects.filter(hashTypeId__lte=100)
        all_objs = HashType.objects.all()
        self.assertEqual(
            [x.id for x in all_objs if x.hashTypeId <= 100],
            [x.id for x in objs])

    def test_filter__ne(self):
        objs = HashType.objects.filter(hashTypeId__ne=100)
        all_objs = HashType.objects.all()
        self.assertEqual(
            [x.id for x in all_objs if x.hashTypeId != 100],
            [x.id for x in objs])

    @pytest.mark.skip(reason="Broken due to bug https://github.com/hashtopolis/server/issues/968")
    def test_filter__startswith(self):
        objs = HashType.objects.filter(description__startswith="net")
        all_objs = HashType.objects.all()
        self.assertEqual(
            [x.id for x in all_objs if x.description.startswith('net')],
            [x.id for x in objs])

    def test_ordering(self):
        model_objs = self.create_test_objects()
        objs = HashType.objects.filter(hashTypeId__gte=90000, hashTypeId__lte=91000,
                                       ordering=['-hashTypeId'])
        sorted_model_objs = sorted(model_objs, key=lambda x: x.hashTypeId, reverse=True)
        self.assertEqual(
            [x.id for x in sorted_model_objs],
            [x.id for x in objs])

    def test_ordering_twice(self):
        model_objs = self.create_test_objects()
        objs = HashType.objects.filter(hashTypeId__gte=90000, hashTypeId__lte=91000,
                                       ordering=['-isSalted', '-hashTypeId'])
        sorted_model_objs = sorted(model_objs, key=lambda x: (x.isSalted, x.hashTypeId), reverse=True)
        self.assertEqual(
            [x.id for x in sorted_model_objs],
            [x.id for x in objs])
