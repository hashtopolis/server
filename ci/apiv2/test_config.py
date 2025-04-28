from hashtopolis import Config
from utils import BaseTest


class ConfigTest(BaseTest):
    model_class = Config

    def test_patch_config(self):
        config = Config.objects.get(item='hashcatBrainEnable')
        config.value = "0"
        config.save()

        obj = Config.objects.get(item='hashcatBrainEnable')
        self.assertEqual(obj.value, "0")

        config.value = "1"
        config.save()

        obj = Config.objects.get(item='hashcatBrainEnable')
        self.assertEqual(obj.value, "1")
    
    def test_patch_many(self):
        configs = Config.objects.get(configsectionId='1')
        attributes_to_change = [10, 40, 1200, 20, "|"]
        Config.objects.patch_many(configs, attributes_to_change, "value")

        newConfigs = Config.objects.get(configsectionId='1')
        for idx, i in enumerate(newConfigs):
            self.assertEqual(i.value, attributes_to_change[idx]) 

    def test_expandables(self):
        model_obj = Config.objects.get(pk=1)
        expandables = ['configSection']
        self._test_expandables(model_obj, expandables)
