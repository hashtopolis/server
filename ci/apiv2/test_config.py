from hashtopolis import Config
from hashtopolis import HashtopolisError
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
        config.value = config.value + "<\\öäüß🙂"
        config.save()

        obj = Config.objects.get(item='blacklistChars')
        self.assertEqual(obj.value, tmp_value + "<\\öäüß🙂")

        config.value = tmp_value
        config.save()

        obj = Config.objects.get(item='blacklistChars')
        self.assertEqual(obj.value, tmp_value)

    def test_bounds_are_exposed_via_aggregate(self):
        default_config = Config.objects.get(item='hashcatBrainPort')
        self.assertFalse(hasattr(default_config, 'valueBoundaries'))

        numeric_config = Config.objects.params(**{"aggregate[config]": "valueBoundaries"}).get(item='hashcatBrainPort')
        self.assertTrue(hasattr(numeric_config, 'valueBoundaries'))
        self.assertEqual(numeric_config.valueBoundaries['min'], 1)
        self.assertEqual(numeric_config.valueBoundaries['max'], 65535)

        field_separator = Config.objects.params(**{"aggregate[config]": "valueBoundaries"}).get(item='fieldseparator')
        self.assertEqual(field_separator.valueBoundaries['maxLength'], 1)

        tickbox_config = Config.objects.params(
            **{"aggregate[config]": "valueBoundaries"}
        ).get(item='multicastTransferRateEnable')
        self.assertEqual(tickbox_config.valueBoundaries['binaryValues'], ['0', '1'])

    def test_numeric_bounds_are_validated(self):
        config = Config.objects.get(item='hashcatBrainPort')
        original_value = config.value

        try:
            config.value = '70000'
            with self.assertRaises(HashtopolisError) as e:
                config.save()
            self.assertIn('at most 65535', e.exception.title)
        finally:
            config.value = original_value
            config.save()

    def test_field_separator_max_length_is_validated(self):
        config = Config.objects.get(item='fieldseparator')
        original_value = config.value

        try:
            config.value = '::'
            with self.assertRaises(HashtopolisError) as e:
                config.save()
            self.assertIn('at most 1', e.exception.title)
        finally:
            config.value = original_value
            config.save()
