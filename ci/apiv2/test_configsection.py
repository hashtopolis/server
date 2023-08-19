from hashtopolis import ConfigSection
from utils import BaseTest


class ConfigSectionTest(BaseTest):
    model_class = ConfigSection

    def test_get_one(self):
        obj = ConfigSection.objects.get(pk=1)
        self.assertIsNotNone(obj)
