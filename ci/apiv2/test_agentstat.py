from hashtopolis import AgentStat

from utils import BaseTest
from utils import do_create_dummy_agent, do_create_hashlist, do_create_task


class AgentStatTest(BaseTest):
    model_class = AgentStat

    def create_test_objects(self, *nargs, **kwargs):
        retval = self.create_agent_with_task(*nargs, **kwargs)
        return AgentStat.objects.filter(agentId=retval['agent'].id)

    def test_gpu_temperature(self):
        gpu_temperatures = [20, 30]
        retval = self.create_agent_with_task(gpu_temperatures=gpu_temperatures)
        agent = retval['agent']
        objs = AgentStat.objects.filter(agentId=agent.id, statType=1)
        self.assertGreaterEqual(len(objs), 1)

    def test_gpu_utilisation(self):
        gpu_utilisations = [40, 50]
        retval = self.create_agent_with_task(gpu_utilisations=gpu_utilisations)
        agent = retval['agent']
        objs = AgentStat.objects.filter(agentId=agent.id, statType=2)
        self.assertGreaterEqual(len(objs), 1)

    def test_cpu_utilisation(self):
        cpu_utilisations = [60, 70]
        retval = self.create_agent_with_task(cpu_utilisations=cpu_utilisations)
        agent = retval['agent']
        objs = AgentStat.objects.filter(agentId=agent.id, statType=3)
        self.assertGreaterEqual(len(objs), 1)
