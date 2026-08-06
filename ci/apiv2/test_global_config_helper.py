from hashtopolis import Config, Helper
from utils import BaseTest


class GlobalConfigHelperTest(BaseTest):
    def test_returns_list(self):
        configs = Helper().get_global_config()
        self.assertIsInstance(configs, list)
        self.assertGreater(len(configs), 0)
        for config in configs:
            self.assertIsInstance(config, Config)

    def test_configs_have_item_and_value(self):
        configs = Helper().get_global_config()
        for config in configs:
            self.assertTrue(hasattr(config, 'item'))
            self.assertTrue(hasattr(config, 'value'))
            self.assertIsInstance(config.item, str)
            self.assertIsInstance(config.value, str)

    def test_contains_known_config(self):
        configs = Helper().get_global_config()
        items = [config.item for config in configs]
        self.assertIn('hashcatBrainEnable', items)

    def test_reflects_config_change(self):
        config = Config.objects.get(item='hashcatBrainEnable')
        original_value = config.value

        try:
            new_value = '1' if original_value != '1' else '0'
            config.value = new_value
            config.save()

            configs = Helper().get_global_config()
            for c in configs:
                if c.item == 'hashcatBrainEnable':
                    self.assertEqual(c.value, new_value)
                    return
            self.fail('hashcatBrainEnable not found in helper response')
        finally:
            config.value = original_value
            config.save()

    def test_consistent_across_calls(self):
        result1 = Helper().get_global_config()
        result2 = Helper().get_global_config()
        self.assertEqual(result1, result2)

    def test_bounds_are_exposed(self):
        configs = Helper().get_global_config()

        port_config = next(c for c in configs if c.item == 'hashcatBrainPort')
        self.assertEqual(port_config.min, 1)
        self.assertEqual(port_config.max, 65535)

        field_separator = next(c for c in configs if c.item == 'fieldseparator')
        self.assertEqual(field_separator.maxLength, 1)

        tickbox_config = next(c for c in configs if c.item == 'multicastTransferRateEnable')
        self.assertEqual(tickbox_config.binaryValues, ['0', '1'])
