<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
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

  // Inject the broken-handling thresholds into the SConfig singleton so the
  // tests control them without touching the Config table.
  private function mockConfig(int $taskThreshold, int $agentThreshold, int $window): void {
    $p = (new \ReflectionClass(SConfig::class))->getProperty('instance');
    $p->setValue(null, new DataSet([
      DConfig::BROKEN_TASK_THRESHOLD  => (string)$taskThreshold,
      DConfig::BROKEN_AGENT_THRESHOLD => (string)$agentThreshold,
      DConfig::BROKEN_ERROR_WINDOW    => (string)$window,
    ]));
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

  // Two distinct agents failing on one task marks the task broken and keeps both
  // agents active.
  public function testTwoDistinctAgentsFailBreaksTaskNotAgents(): void {
    $this->mockConfig(2, 3, 0);
    $task = $this->createTaskHelper()["task"];
    $agent1 = $this->createAgent("phpunit");
    $agent2 = $this->createAgent("phpunit");
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
    $task = $this->createTaskHelper()["task"];
    $agent = $this->createAgent("phpunit");
    $this->createAgent("phpunit"); // a second active agent, so the effective threshold stays 2
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
    $task = $this->createTaskHelper()["task"];
    $agent1 = $this->createAgent("phpunit");
    $agent2 = $this->createAgent("phpunit");
    $agent3 = $this->createAgent("phpunit");
    $this->createDatabaseObject(Factory::getAssignmentFactory(), new Assignment(null, $task->getId(), $agent1->getId(), '0'));
    $this->createDatabaseObject(Factory::getAssignmentFactory(), new Assignment(null, $task->getId(), $agent2->getId(), '0'));

    // The healthy third node is cracking: a dispatched chunk with real progress.
    $this->createDatabaseObject(Factory::getChunkFactory(), new Chunk(null, $task->getId(), 0, 1000, $agent3->getId(), time(), 0, 0, 5000, 0, 0, 0));

    AgentErrorUtils::handleClientError($agent1, $task, null, 'boom');
    AgentErrorUtils::handleClientError($agent2, $task, null, 'boom');
    $this->registerErrorArtifacts($task);

    $this->assertFalse(AgentErrorUtils::isTaskBroken($task->getId()), 'a task some agent is progressing must not be marked broken');
  }

  // When only one agent is active, the threshold is capped at 1, so that single
  // agent failing marks the task broken rather than looping on it forever. It
  // cannot get corroboration from other agents because there are none.
  public function testSingleActiveAgentFaultsTask(): void {
    $this->mockConfig(2, 3, 0);
    $task = $this->createTaskHelper()["task"];
    $agent = $this->createAgent("phpunit"); // the only active agent
    $this->createDatabaseObject(Factory::getAssignmentFactory(), new Assignment(null, $task->getId(), $agent->getId(), '0'));

    AgentErrorUtils::handleClientError($agent, $task, null, 'boom');
    $this->registerErrorArtifacts($task);

    $this->assertTrue(AgentErrorUtils::isTaskBroken($task->getId()), 'a single active agent should fault the task it cannot get corroboration for');
    $this->assertEquals(1, Factory::getAgentFactory()->get($agent->getId())->getIsActive());
  }
}
