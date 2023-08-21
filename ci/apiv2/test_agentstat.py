from hashtopolis import AgentStat

from utils import BaseTest


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
        self.assertEqual(len(objs), 1)
        self.assertListEqual(objs[0].value, gpu_temperatures)

    def test_gpu_utilisation(self):
        gpu_utilisations = [40, 50]
        retval = self.create_agent_with_task(gpu_utilisations=gpu_utilisations)
        agent = retval['agent']
        objs = AgentStat.objects.filter(agentId=agent.id, statType=2)
        self.assertEqual(len(objs), 1)
        self.assertListEqual(objs[0].value, gpu_utilisations)

    def test_cpu_utilisation(self):
        cpu_utilisations = [60, 70]
        retval = self.create_agent_with_task(cpu_utilisations=cpu_utilisations)
        agent = retval['agent']
        objs = AgentStat.objects.filter(agentId=agent.id, statType=3)
        self.assertEqual(len(objs), 1)
        self.assertListEqual(objs[0].value, cpu_utilisations)
