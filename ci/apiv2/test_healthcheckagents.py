from hashtopolis import HealthCheckAgent
from utils import BaseTest


class HealthCheckAgentTest(BaseTest):
    model_class = HealthCheckAgent

    # def create_test_object(self, *nargs, **kwargs):
    #     # TODO: Create healthcheckagent via Dummy Agent
    #     self.assertIsNotNone(hash)
    #     return

    # def test_create(self):
    #     model_obj = self.create_test_object()
    #     self._test_create(model_obj)

    # def test_patch(self):
    #     # Patching HealthCheckAgents is not possible via API
    #     model_obj = self.create_test_object()
    #     with self.assertRaises(HashtopolisResponseError) as e:
    #         attr = 'isCracked'
    #         new_attr_value = True
    #         self._test_patch(model_obj, attr, new_attr_value)
    #     self.assertEqual(e.exception.status_code, 500)

    # def test_delete(self):
    #     # Deleting HealthCheckAgents is not possible via API
    #     model_obj = self.create_test_object(delete=False)
    #     with self.assertRaises(HashtopolisResponseError) as e:
    #         self._test_delete(model_obj)
    #     self.assertEqual(e.exception.status_code, 500)

    # def test_expandables(self):
    #     model_obj = self.create_test_object()
    #     expandables = ['agent', 'healthcheck']
    #     self._test_expandables(model_obj, expandables)
