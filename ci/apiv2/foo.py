import hashtopolis
from hashtopolis import Agent

import utils

#r = utils.do_create_agent_with_task()

a = Agent.objects.all()[0]
a.ignoreErrors = 4
a.save()
print(a)
