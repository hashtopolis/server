<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\AccessGroupAgent;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\models\AgentError;
use Hashtopolis\dba\models\BrokenTask;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\DataSet;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\SConfig;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../../TestBase.php');

final class AgentErrorUtilsTest extends TestBase {

  // Override only the broken-handling thresholds in the SConfig singleton so the
  // tests control them without touching the Config table. Start from the real
  // config so every OTHER lookup keeps working: markTaskBroken logs through
  // Util::createLogEntry, whose rotation reads numLogEntries, and wiping it would
  // make that read null and delete unrelated log entries (breaking test_logentry).
  private function mockConfig(int $taskThreshold, int $agentThreshold, int $window): void {
    $values = SConfig::getInstance()->getAllValues();
    $values[DConfig::BROKEN_TASK_THRESHOLD]  = (string)$taskThreshold;
    $values[DConfig::BROKEN_AGENT_THRESHOLD] = (string)$agentThreshold;
    $values[DConfig::BROKEN_ERROR_WINDOW]    = (string)$window;
    $p = (new \ReflectionClass(SConfig::class))->getProperty('instance');
    $p->setValue(null, new DataSet($values));
  }

  protected function tearDown(): void {
    $p = (new \ReflectionClass(SConfig::class))->getProperty('instance');
    $p->setValue(null, null);
    parent::tearDown();
  }

  // handleClientError creates AgentError (and possibly BrokenTask) rows that the
  // fixture helpers do not track. Register them so tearDown removes them before
  // the task and agents they reference (databaseObjects are freed in reverse).
  private function registerErrorArtifacts(Task $task): void {
    $errors = Factory::getAgentErrorFactory()->filter([Factory::FILTER => new QueryFilter(AgentError::TASK_ID, $task->getId(), '=')]);
    $this->registerDatabaseObjects(Factory::getAgentErrorFactory(), $errors);
    $broken = Factory::getBrokenTaskFactory()->filter([Factory::FILTER => new QueryFilter(BrokenTask::TASK_ID, $task->getId(), '=')]);
    $this->registerDatabaseObjects(Factory::getBrokenTaskFactory(), $broken);
  }

  // Put the agent in the task's access group so it counts as eligible for the
  // task: countEligibleAgents (and so the effective threshold cap) only counts
  // agents that could actually be assigned the task.
  private function makeEligible(AccessGroup $group, Agent $agent): void {
    $this->createDatabaseObject(Factory::getAccessGroupAgentFactory(), new AccessGroupAgent(null, $group->getId(), $agent->getId()));
  }

  // Two distinct agents failing on one task marks the task broken and keeps both
  // agents active.
  public function testTwoDistinctAgentsFailBreaksTaskNotAgents(): void {
    $this->mockConfig(2, 3, 0);
    $helper = $this->createTaskHelper();
    $task = $helper["task"];
    $group = $helper["accessGroup"];
    $agent1 = $this->createAgent("phpunit");
    $agent2 = $this->createAgent("phpunit");
    $this->makeEligible($group, $agent1);
    $this->makeEligible($group, $agent2);
    $this->createDatabaseObject(Factory::getAssignmentFactory(), new Assignment(null, $task->getId(), $agent1->getId(), '0'));
    $this->createDatabaseObject(Factory::getAssignmentFactory(), new Assignment(null, $task->getId(), $agent2->getId(), '0'));

    AgentErrorUtils::handleClientError($agent1, $task, null, 'boom');
    $this->assertFalse(AgentErrorUtils::isTaskBroken($task->getId()), 'one agent should not fault the task');

    AgentErrorUtils::handleClientError($agent2, $task, null, 'boom');
    $this->registerErrorArtifacts($task);

    $this->assertTrue(AgentErrorUtils::isTaskBroken($task->getId()), 'two distinct agents should fault the task');
    $this->assertEquals(1, Factory::getAgentFactory()->get($agent1->getId())->getIsActive());
    $this->assertEquals(1, Factory::getAgentFactory()->get($agent2->getId())->getIsActive());
  }

  // With at least as many active agents as the threshold, one agent failing does
  // not fault the task and does not deactivate the agent: an untrusted node
  // failing alone is treated as a node problem. The agent is only unassigned.
  public function testSingleAgentFailureKeepsTaskAndAgent(): void {
    $this->mockConfig(2, 3, 0);
    $helper = $this->createTaskHelper();
    $task = $helper["task"];
    $group = $helper["accessGroup"];
    $agent = $this->createAgent("phpunit");
    $agent2 = $this->createAgent("phpunit"); // a second eligible agent, so the effective threshold stays 2
    $this->makeEligible($group, $agent);
    $this->makeEligible($group, $agent2);
    $this->createDatabaseObject(Factory::getAssignmentFactory(), new Assignment(null, $task->getId(), $agent->getId(), '0'));

    AgentErrorUtils::handleClientError($agent, $task, null, 'boom');
    $this->registerErrorArtifacts($task);

    $this->assertFalse(AgentErrorUtils::isTaskBroken($task->getId()));
    $this->assertEquals(1, Factory::getAgentFactory()->get($agent->getId())->getIsActive());
    $remaining = Factory::getAssignmentFactory()->filter([Factory::FILTER => [
      new QueryFilter(Assignment::AGENT_ID, $agent->getId(), '='),
      new QueryFilter(Assignment::TASK_ID, $task->getId(), '='),
    ]]);
    $this->assertCount(0, $remaining, 'the agent should be unassigned from the failing task');
  }

  // An agent failing across brokenAgentThreshold distinct tasks is the problem
  // itself and gets deactivated. Marking tasks broken is disabled here to isolate the
  // agent path.
  public function testAgentFailingAcrossManyTasksIsDeactivated(): void {
    $this->mockConfig(0, 3, 0);
    $agent = $this->createAgent("phpunit");
    for ($i = 0; $i < 3; $i++) {
      $task = $this->createTaskHelper()["task"];
      $this->createDatabaseObject(Factory::getAssignmentFactory(), new Assignment(null, $task->getId(), $agent->getId(), '0'));
      AgentErrorUtils::handleClientError($agent, $task, null, 'driver crash');
      $this->registerErrorArtifacts($task);
    }
    $this->assertEquals(0, Factory::getAgentFactory()->get($agent->getId())->getIsActive(), 'agent failing on three distinct tasks should be deactivated');
  }

  // Two agents failing reaches the threshold, but a third agent is making
  // progress on the same task (a chunk with non-zero progress). A working node
  // proves the command runs, so the task must not be marked broken: the failures
  // are agent faults, not a task fault.
  public function testProgressingTaskIsNotMarkedBroken(): void {
    $this->mockConfig(2, 3, 0);
    $helper = $this->createTaskHelper();
    $task = $helper["task"];
    $group = $helper["accessGroup"];
    $agent1 = $this->createAgent("phpunit");
    $agent2 = $this->createAgent("phpunit");
    $agent3 = $this->createAgent("phpunit");
    $this->makeEligible($group, $agent1);
    $this->makeEligible($group, $agent2);
    $this->createDatabaseObject(Factory::getAssignmentFactory(), new Assignment(null, $task->getId(), $agent1->getId(), '0'));
    $this->createDatabaseObject(Factory::getAssignmentFactory(), new Assignment(null, $task->getId(), $agent2->getId(), '0'));

    // The healthy third node is cracking: a dispatched chunk with real progress.
    $this->createDatabaseObject(Factory::getChunkFactory(), new Chunk(null, $task->getId(), 0, 1000, $agent3->getId(), time(), 0, 0, 5000, 0, 0, 0));

    AgentErrorUtils::handleClientError($agent1, $task, null, 'boom');
    AgentErrorUtils::handleClientError($agent2, $task, null, 'boom');
    $this->registerErrorArtifacts($task);

    $this->assertFalse(AgentErrorUtils::isTaskBroken($task->getId()), 'a task some agent is progressing must not be marked broken');
  }

  // When only one agent is eligible for the task, the threshold is capped at 1,
  // so that single agent failing marks the task broken rather than looping on it
  // forever. It cannot get corroboration from other agents because none can run
  // the task.
  public function testSingleEligibleAgentFaultsTask(): void {
    $this->mockConfig(2, 3, 0);
    $helper = $this->createTaskHelper();
    $task = $helper["task"];
    $group = $helper["accessGroup"];
    $agent = $this->createAgent("phpunit"); // the only eligible agent
    $this->makeEligible($group, $agent);
    $this->createDatabaseObject(Factory::getAssignmentFactory(), new Assignment(null, $task->getId(), $agent->getId(), '0'));

    AgentErrorUtils::handleClientError($agent, $task, null, 'boom');
    $this->registerErrorArtifacts($task);

    $this->assertTrue(AgentErrorUtils::isTaskBroken($task->getId()), 'a single eligible agent should fault the task it cannot get corroboration for');
    $this->assertEquals(1, Factory::getAgentFactory()->get($agent->getId())->getIsActive());
  }

  // The cap counts agents ELIGIBLE for the task, not the whole active fleet. Here
  // three agents are active but only one is in the task's access group, so the
  // effective threshold is capped at 1 and that single eligible agent's failure
  // faults the task, even though the configured threshold is 2 and two more
  // active agents exist. Without eligibility scoping the task would loop forever.
  public function testThresholdCappedToEligibleNotActiveAgents(): void {
    $this->mockConfig(2, 3, 0);
    $helper = $this->createTaskHelper();
    $task = $helper["task"];
    $group = $helper["accessGroup"];
    $eligible = $this->createAgent("phpunit");
    $this->makeEligible($group, $eligible);
    // Two active agents outside the task's access group: they can never run it.
    $this->createAgent("phpunit");
    $this->createAgent("phpunit");
    $this->createDatabaseObject(Factory::getAssignmentFactory(), new Assignment(null, $task->getId(), $eligible->getId(), '0'));

    AgentErrorUtils::handleClientError($eligible, $task, null, 'boom');
    $this->registerErrorArtifacts($task);

    $this->assertTrue(AgentErrorUtils::isTaskBroken($task->getId()), 'threshold must cap at eligible agents, not the whole active fleet');
  }
}
