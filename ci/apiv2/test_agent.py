import datetime

from hashtopolis import Agent

from utils import BaseTest
from utils import do_create_agent, do_create_hashlist, do_create_task


class AgentTest(BaseTest):
    def test_new_agent(self):
        dummy_agent, agent = do_create_agent()
        self.delete_after_test(agent)

        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        task = do_create_task(hashlist)
        self.delete_after_test(task)

        dummy_agent.get_task()
        dummy_agent.get_hashlist()
        dummy_agent.get_chunk()

    def test_patch_agent(self):
        _, agent = do_create_agent()
        self.delete_after_test(agent)

        new_name = f'agent-patch-{datetime.datetime.now().isoformat()}'
        agent.agentName = new_name
        agent.save()

        obj = Agent.objects.get(pk=agent.id)
        self.assertEqual(obj.agentName, new_name)
