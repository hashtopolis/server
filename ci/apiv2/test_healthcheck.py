from hashtopolis import HealthCheck, HealthCheckAgent, HashType, Cracker
from hashtopolis_agent import DummyAgent
from utils import BaseTest, do_create_dummy_agent


class HealthCheckAgentTest(BaseTest):
    model_class = HealthCheck

    def create_test_object(self, *nargs, **kwargs):
        return self.create_healthcheck(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'crackerBinaryId', 1)

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['crackerBinary', 'hashType', 'healthCheckAgents']
        self._test_expandables(model_obj, expandables)

    def test_healthcheck_agents(self):
        (dummy_1, agent_1) = do_create_dummy_agent()
        model_obj = self.create_test_object()
        
        check = HealthCheck.objects.filter(healthCheckId=model_obj.id)
        self.assertEqual(len(check), 1)

        check = HealthCheckAgent.objects.filter(healthCheckId=model_obj.id)
        self.assertEqual(len(check), 2)

    def test_to_one_relationships(self):
        model_obj = self.create_test_object()
        self.assertEqual(len(HashType.objects.filter(hashTypeId=getattr(model_obj, 'hashtypeId'))), 1)
        self.assertEqual(len(Cracker.objects.filter(crackerBinaryId=getattr(model_obj, 'crackerBinaryId'))), 1)