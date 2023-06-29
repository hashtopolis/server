#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# PoC testing/development framework for APIv2
# Written in python to work on creation of hashtopolis APIv2 python binding.
#
import datetime
import time

from hashtopolis_agent import DummyAgent
from hashtopolis import Agent, Voucher

from utils import BaseTest

from test_hashlists import do_create_hashlist
from test_tasks import do_create_task


def do_create_agent():
    stamp = int(time.time() * 1000)
    voucher = Voucher(voucher=f'dummy-test-{stamp}').save()

    dummy_agent = DummyAgent()
    dummy_agent.register(voucher=voucher.voucher, name=f'test-agent-{stamp}')
    dummy_agent.login()

    # Validate automatically deleted when an test-agent claims the voucher
    assert(Voucher.objects.filter(_id=voucher.id) == [])

    agent = Agent.objects.get(agentName=dummy_agent.name)
    return (dummy_agent, agent)


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
        dummy_agent, agent = do_create_agent()
        self.delete_after_test(agent)

        new_name = f'agent-patch-{datetime.datetime.now().isoformat()}'
        agent.agentName = new_name
        agent.save()

        obj = Agent.objects.get(pk=agent.id)
        assert obj.agentName == new_name
