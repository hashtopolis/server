from hashtopolis import Config
from utils import BaseTest
from hashtopolis.hashtopolis import HashtopolisResponseError

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
        configs = Config.objects.filter(configId__lte='9')
        attributes_to_change = ["10", "40", "1200", "20", "|"]
        Config.objects.patch_many(configs, attributes_to_change, "value")

        newConfigs = Config.objects.filter(configId__lte='9')
        for new_config, new_attribute in zip(newConfigs, attributes_to_change):
            self.assertEqual(new_config.value, new_attribute) 

    def test_expandables(self):
        model_obj = Config.objects.get(pk=1)
        expandables = ['configSection']
        self._test_expandables(model_obj, expandables)

    def test_blacklist_chars(self):
        config = Config.objects.get(item='blacklistChars')
        tmp_value = config.value
        config.value = config.value + "<\öäüß🙂"
        config.save()

        obj = Config.objects.get(item='blacklistChars')
        self.assertEqual(obj.value, tmp_value + "<\öäüß🙂")

        config.value = tmp_value
        config.save()

        obj = Config.objects.get(item='blacklistChars')
        self.assertEqual(obj.value, tmp_value)