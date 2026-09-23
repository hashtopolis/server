<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\dba\models\AccessGroupAgent;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\AgentError;
use Hashtopolis\dba\models\AgentStat;
use Hashtopolis\dba\models\AgentZap;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\dba\models\NotificationSetting;
use Hashtopolis\dba\models\Speed;
use Hashtopolis\dba\models\Zap;
use Hashtopolis\dba\models\User;
use Hashtopolis\TestBase;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DNotificationType;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\SConfig;

require_once(dirname(__FILE__) . '/../../TestBase.php');

final class AgentUtilsTest extends TestBase {
  
  private const INACTIVE_COLOR = "#CCCCCC";
  private const ACTIVE_COLOR = "#42d4f4";
  private const GREEN_COLOR = "#009933";
  private const YELLOW_COLOR = "#ff9900";
  private const RED_COLOR = "#FF0000";
  private const ERROR_COLOR = "#800000";

  protected function setUp(): void {
    parent::setUp();
  }
  
  function testGetStatusColorInactiveMissingDeviceUtil(): void {
    $agent = $this->createAgent("agentutiltest");
    $agent->setIsActive(0);

    $this->assertEquals(self::INACTIVE_COLOR, AgentUtils::getDeviceUtilStatusColor(null, $agent));
  }

  function testGetStatusColorActiveButTimedOut(): void {
    $agent = $this->createAgent("agentutiltest");
    $agent->setIsActive(1);
    $now = 1700000000;
     
    \hashtopolis_set_test_mock('Hashtopolis\\inc\\utils\\time', static function () use ($now): int {
      return $now;
    });

    $agentTimeout = (int) SConfig::getInstance()->getVal(DConfig::AGENT_TIMEOUT);
    $agent->setLastTime($now - $agentTimeout - 1);

    $this->assertEquals(self::INACTIVE_COLOR, AgentUtils::getDeviceUtilStatusColor(null, $agent));
  }

  function testGetStatusColorActiveWithinTimeout(): void {
    $agent = $this->createAgent("agentutiltest");
    $agent->setIsActive(1);
    $now = 1700000000;
    
    \hashtopolis_set_test_mock('Hashtopolis\\inc\\utils\\time', static function () use ($now): int {
      return $now;
    });

    $agentTimeout = (int) SConfig::getInstance()->getVal(DConfig::AGENT_TIMEOUT);
    $agent->setLastTime($now - $agentTimeout + 1);

    $this->assertEquals(self::ACTIVE_COLOR, AgentUtils::getDeviceUtilStatusColor(null, $agent));
  }

  function testGetDeviceUtilStatusColorZeroSumReturnsRedColor(): void {
    $agent = $this->createAgent("agentutiltest");
    $deviceUtil = new AgentStat(null, $agent->getId(), 2, 1700000000, '0,0,0');

    $this->assertEquals(self::RED_COLOR, AgentUtils::getDeviceUtilStatusColor($deviceUtil, $agent));
  }

  function testGetDeviceUtilStatusColorHighUtilReturnsGreenColor(): void {
    $agent = $this->createAgent("agentutiltest");
    $threshold1 = (int) SConfig::getInstance()->getVal(DConfig::AGENT_UTIL_THRESHOLD_1);
    $deviceUtil = new AgentStat(null, $agent->getId(), 2, 1700000000, (string) ($threshold1 + 1));

    $this->assertEquals(self::GREEN_COLOR, AgentUtils::getDeviceUtilStatusColor($deviceUtil, $agent));
  }

  function testGetDeviceUtilStatusColorMediumUtilReturnsYellowColor(): void {
    $agent = $this->createAgent("agentutiltest");
    $threshold1 = (int) SConfig::getInstance()->getVal(DConfig::AGENT_UTIL_THRESHOLD_1);
    $threshold2 = (int) SConfig::getInstance()->getVal(DConfig::AGENT_UTIL_THRESHOLD_2);
    $deviceUtil = new AgentStat(null, $agent->getId(), 2, 1700000000, (string) ($threshold2 + 1));

    $this->assertGreaterThan($threshold2, $threshold1);
    $this->assertEquals(self::YELLOW_COLOR, AgentUtils::getDeviceUtilStatusColor($deviceUtil, $agent));
  }

  function testGetDeviceUtilStatusColorLowUtilReturnsRedColor(): void {
    $agent = $this->createAgent("agentutiltest");
    $threshold2 = (int) SConfig::getInstance()->getVal(DConfig::AGENT_UTIL_THRESHOLD_2);
    $deviceUtil = new AgentStat(null, $agent->getId(), 2, 1700000000, (string) $threshold2);

    $this->assertEquals(self::ERROR_COLOR, AgentUtils::getDeviceUtilStatusColor($deviceUtil, $agent));
  }

  function testGetCpuUtilStatusColorZeroSumReturnsRedColor(): void {
    $agent = $this->createAgent("agentutiltest");
    $cpuUtil = new AgentStat(null, $agent->getId(), 3, 1700000000, '0,0,0');

    $this->assertEquals(self::RED_COLOR, AgentUtils::getCpuUtilStatusColor($cpuUtil, $agent));
  }

  function testGetCpuUtilStatusColorHighUtilReturnsGreenColor(): void {
    $agent = $this->createAgent("agentutiltest");
    $threshold1 = (int) SConfig::getInstance()->getVal(DConfig::AGENT_UTIL_THRESHOLD_1);
    $cpuUtil = new AgentStat(null, $agent->getId(), 3, 1700000000, (string) ($threshold1 + 1));

    $this->assertEquals(self::GREEN_COLOR, AgentUtils::getCpuUtilStatusColor($cpuUtil, $agent));
  }

  function testGetCpuUtilStatusColorMediumUtilReturnsYellowColor(): void {
    $agent = $this->createAgent("agentutiltest");
    $threshold2 = (int) SConfig::getInstance()->getVal(DConfig::AGENT_UTIL_THRESHOLD_2);
    $cpuUtil = new AgentStat(null, $agent->getId(), 3, 1700000000, (string) ($threshold2 + 1));

    $this->assertEquals(self::YELLOW_COLOR, AgentUtils::getCpuUtilStatusColor($cpuUtil, $agent));
  }

  function testGetCpuUtilStatusColorLowUtilReturnsRedColor(): void {
    $agent = $this->createAgent("agentutiltest");
    $threshold2 = (int) SConfig::getInstance()->getVal(DConfig::AGENT_UTIL_THRESHOLD_2);
    $cpuUtil = new AgentStat(null, $agent->getId(), 3, 1700000000, (string) $threshold2);

    $this->assertEquals(self::ERROR_COLOR, AgentUtils::getCpuUtilStatusColor($cpuUtil, $agent));
  }

  function testGetDeviceTempStatusColorZeroSumReturnsRedColor(): void {
    $agent = $this->createAgent("agentutiltest");
    $deviceTemp = new AgentStat(null, $agent->getId(), 1, 1700000000, '0,0,0');

    $this->assertEquals(self::RED_COLOR, AgentUtils::getDeviceTempStatusColor($deviceTemp, $agent));
  }

  function testGetDeviceTempStatusColorLowTempReturnsGreenColor(): void {
    $agent = $this->createAgent("agentutiltest");
    $threshold1 = (int) SConfig::getInstance()->getVal(DConfig::AGENT_TEMP_THRESHOLD_1);
    $deviceTemp = new AgentStat(null, $agent->getId(), 1, 1700000000, (string) $threshold1);

    $this->assertEquals(self::GREEN_COLOR, AgentUtils::getDeviceTempStatusColor($deviceTemp, $agent));
  }

  function testGetDeviceTempStatusColorMediumTempReturnsYellowColor(): void {
    $agent = $this->createAgent("agentutiltest");
    $threshold1 = (int) SConfig::getInstance()->getVal(DConfig::AGENT_TEMP_THRESHOLD_1);
    $deviceTemp = new AgentStat(null, $agent->getId(), 1, 1700000000, (string) ($threshold1 + 1));

    $this->assertEquals(self::YELLOW_COLOR, AgentUtils::getDeviceTempStatusColor($deviceTemp, $agent));
  }

  function testGetDeviceTempStatusColorHighTempReturnsRedColor(): void {
    $agent = $this->createAgent("agentutiltest");
    $threshold2 = (int) SConfig::getInstance()->getVal(DConfig::AGENT_TEMP_THRESHOLD_2);
    $deviceTemp = new AgentStat(null, $agent->getId(), 1, 1700000000, (string) ($threshold2 + 1));

    $this->assertEquals(self::ERROR_COLOR, AgentUtils::getDeviceTempStatusColor($deviceTemp, $agent));
  }

  function testGetDeviceUtilStatusValueReturnsNoDataForNull(): void {
    $this->assertSame("No data", AgentUtils::getDeviceUtilStatusValue(null));
  }

  function testGetDeviceUtilStatusValueReturnsNoValidDataForNullValue(): void {
    $agent = $this->createAgent("agentutiltest");
    $deviceUtil = new AgentStat(null, $agent->getId(), 2, 1700000000, null);

    $this->assertSame("No valid data", AgentUtils::getDeviceUtilStatusValue($deviceUtil));
  }

  function testGetDeviceUtilStatusValueReturnsAveragePercentage(): void {
    $agent = $this->createAgent("agentutiltest");
    $deviceUtil = new AgentStat(null, $agent->getId(), 2, 1700000000, '10,21');

    $this->assertSame("15.5%", AgentUtils::getDeviceUtilStatusValue($deviceUtil));
  }

  function testGetDeviceTempStatusValueReturnsNoDataForNull(): void {
    $this->assertSame("No data", AgentUtils::getDeviceTempStatusValue(null));
  }

  function testGetDeviceTempStatusValueReturnsNoValidDataForNullValue(): void {
    $agent = $this->createAgent("agentutiltest");
    $deviceTemp = new AgentStat(null, $agent->getId(), 1, 1700000000, null);

    $this->assertSame("No valid data", AgentUtils::getDeviceTempStatusValue($deviceTemp));
  }

  function testGetDeviceTempStatusValueReturnsMaxTemperature(): void {
    $agent = $this->createAgent("agentutiltest");
    $deviceTemp = new AgentStat(null, $agent->getId(), 1, 1700000000, '10,21');

    $this->assertSame("21°", AgentUtils::getDeviceTempStatusValue($deviceTemp));
  }

  function testGetCpuUtilStatusValueReturnsNoDataForNull(): void {
    $this->assertSame("No data", AgentUtils::getCpuUtilStatusValue(null));
  }

  function testGetCpuUtilStatusValueReturnsNoValidDataForNullValue(): void {
    $agent = $this->createAgent("agentutiltest");
    $cpuUtil = new AgentStat(null, $agent->getId(), 3, 1700000000, null);

    $this->assertSame("No valid data", AgentUtils::getCpuUtilStatusValue($cpuUtil));
  }

  function testGetCpuUtilStatusValueReturnsAveragePercentage(): void {
    $agent = $this->createAgent("agentutiltest");
    $cpuUtil = new AgentStat(null, $agent->getId(), 3, 1700000000, '10,21');

    $this->assertSame("15.5%", AgentUtils::getCpuUtilStatusValue($cpuUtil));
  }

  function testSetAgentCpuEnablesCpuOnly(): void {
    $user = $this->createUser("agentutiltest");
    $agent = $this->createUserAgent($user);
    
    AgentUtils::setAgentCpu($agent->getId(), true, $user);

    $updatedAgent = Factory::getAgentFactory()->get($agent->getId());
    $this->assertSame(1, $updatedAgent->getCpuOnly());
  }

  function testSetAgentCpuDisablesCpuOnly(): void {
    $user = $this->createUser("agentutiltest");
    $agent = $this->createUserAgent($user);

    Factory::getAgentFactory()->set($agent, Agent::CPU_ONLY, 1);

    AgentUtils::setAgentCpu($agent->getId(), false, $user);

    $updatedAgent = Factory::getAgentFactory()->get($agent->getId());
    $this->assertSame(0, $updatedAgent->getCpuOnly());
  }

  function testSetAgentCpuThrowsWhenAgentDoesNotExist(): void {
    $this->expectException(HTException::class);
    $this->expectExceptionMessage("Invalid agent!");

    AgentUtils::setAgentCpu(999999, true, $this->adminUser);
  }

  function testClearErrorsDeletesOnlySelectedAgentErrors(): void {
    $user = $this->createUser("agentutiltest");
    $task = $this->createTaskHelper()["task"];
    $agent1 = $this->createUserAgent($user);
    $agent2 = $this->createUserAgent($user);

    $this->createDatabaseObject(
      Factory::getAgentErrorFactory(),
      new AgentError(null, $agent1->getId(), $task->getId(), null, 1700000000, 'agent1-error-1')
    );
    $this->createDatabaseObject(
      Factory::getAgentErrorFactory(),
      new AgentError(null, $agent1->getId(), $task->getId(), null, 1700000001, 'agent1-error-2')
    );
    $this->createDatabaseObject(
      Factory::getAgentErrorFactory(),
      new AgentError(null, $agent2->getId(), $task->getId(), null, 1700000002, 'agent2-error')
    );

    AgentUtils::clearErrors($agent1->getId(), $user);

    $agent1Errors = Factory::getAgentErrorFactory()->filter([Factory::FILTER => new \Hashtopolis\dba\QueryFilter(AgentError::AGENT_ID, $agent1->getId(), "=")]);
    $agent2Errors = Factory::getAgentErrorFactory()->filter([Factory::FILTER => new \Hashtopolis\dba\QueryFilter(AgentError::AGENT_ID, $agent2->getId(), "=")]);

    $this->assertCount(0, $agent1Errors);
    $this->assertCount(1, $agent2Errors);
  }

  function testRenameAgentUpdatesName(): void {
    $user = $this->createUser("agentutiltest");
    $agent = $this->createUserAgent($user);

    AgentUtils::rename($agent->getId(), 'renamed-agent', $user);

    $updatedAgent = Factory::getAgentFactory()->get($agent->getId());
    $this->assertSame('renamed-agent', $updatedAgent->getAgentName());
  }

  function testRenameAgentThrowsWhenNameIsEmpty(): void {
    $user = $this->createUser("agentutiltest");
    $agent = $this->createUserAgent($user);

    $this->expectException(HTException::class);
    $this->expectExceptionMessage("Agent name cannot be empty!");

    AgentUtils::rename($agent->getId(), '', $user);
  }

  function testRenameAgentThrowsWhenAgentDoesNotExist(): void {
    $this->expectException(HTException::class);
    $this->expectExceptionMessage("Invalid agent!");

    AgentUtils::rename(999999, 'renamed-agent', $this->adminUser);
  }

  function testDeleteRemovesAgentAndDependencies(): void {
    $fixture = $this->createAgentDeleteFixture();
    $agentId = $fixture['agent']->getId();

    AgentUtils::delete($agentId, $this->adminUser);

    $this->assertDeletedAgentState($agentId, $fixture);
  }

   function testAssignThrowsWhenTaskDoesNotExist(): void {
    $user = $this->createUser("agentutiltest");
    $agent = $this->createUserAgent($user);

    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("Invalid task!");

    AgentUtils::assign($agent->getId(), 999999, $user);
  }

  

  /**
   * Test cracking time aggregation for an agent on a task.
   *
   * @return void
   * @throws Exception
   */
  public function testCrackingTimeAggregation(): void {
    $task = $this->createTaskHelper()["task"];
    $agent1 = $this->createAgent("test");
    $agent2 = $this->createAgent("test");
    $timeSpans = (array) [
      [1000, 2000, $agent1],
      [3000, 5000, $agent2],
      [3000, 8000, $agent1],
      [8000, 10000, $agent1],
      [15000, 20000, $agent1],
    ];
    
    foreach ($timeSpans as [$start, $end, $agent]) {
      $chunk = $this->createChunk($task, $agent, 4);
      $chunk->setDispatchTime($start);
      $chunk->setSolveTime($end);
      Factory::getChunkFactory()->update($chunk);
    }
    
    // Calculate reference value via an interval merge algorithm
    $totalEnd = $referenceSum = 0;
    usort($timeSpans, fn($a, $b) => $a[0] <=> $b[0]); // Make sure time spans are sorted
    foreach ($timeSpans as [$currentStart, $currentEnd, $agent]) { // Expects list to be sorted by time
      if ($agent == $agent1) {
        $referenceSum = $referenceSum + ($currentEnd - $currentStart) // Add current time span to running total
          - max(0, $totalEnd - $currentStart) // Correct for potential overlapping when current start before previous end
          + max(0, $totalEnd - $currentEnd); // Correct for potential overcorrection when current end before previous end
        $totalEnd = max($totalEnd, $currentEnd); // Extend window if current end exceeds previous end
      }
    }
    
    // Calculate aggregate cracking time via AgentUtils
    $crackingTime = AgentUtils::getAggregateCrackingTime($agent1->getId(), $task->getId());
    
    $this->assertEquals($referenceSum, $crackingTime);
  }

  private function createUserAgent(User $user): Agent {
    $agent = $this->createAgent("agentutiltest");
    $accessGroup = $this->createAccessGroup("agentutiltest");
    $this->createAccessGroupUser($user, $accessGroup);
    $this->createAccessGroupAgent($agent, $accessGroup);

    return $agent;
  }

  private function createAgentDeleteFixture(): array {
    $taskData = $this->createTaskHelper();
    $this->createAccessGroupUser($this->adminUser, $taskData['accessGroup']);
    $agent = $this->createAgent("agentdelete");

    $assignment = $this->createDatabaseObject(
      Factory::getAssignmentFactory(),
      new Assignment(null, $taskData['task']->getId(), $agent->getId(), 'benchmark')
    );
    $agentError = $this->createDatabaseObject(
      Factory::getAgentErrorFactory(),
      new AgentError(null, $agent->getId(), $taskData['task']->getId(), null, 1700000000, 'agent-delete-error')
    );
    $agentStat = $this->createDatabaseObject(
      Factory::getAgentStatFactory(),
      new AgentStat(null, $agent->getId(), 2, 1700000000, '10,20')
    );
    $agentZap = $this->createDatabaseObject(
      Factory::getAgentZapFactory(),
      new AgentZap(null, $agent->getId(), null)
    );
    $healthCheck = $this->createHealthCheck($taskData['crackerBinary']);
    $healthCheckAgent = $this->createHealthCheckAgent($healthCheck, $agent);
    $speed = $this->createDatabaseObject(
      Factory::getSpeedFactory(),
      new Speed(null, $agent->getId(), $taskData['task']->getId(), 1234, 1700000000)
    );
    $accessGroupAgent = $this->createDatabaseObject(
      Factory::getAccessGroupAgentFactory(),
      new AccessGroupAgent(null, $taskData['accessGroup']->getId(), $agent->getId())
    );
    $chunk = $this->createChunk($taskData['task'], $agent, 2);
    $zap = $this->createDatabaseObject(
      Factory::getZapFactory(),
      new Zap(null, 'hash-delete-agent', 1700000000, $agent->getId(), $taskData['hashlist']->getId())
    );
    $deleteNotification = $this->createDatabaseObject(
      Factory::getNotificationSettingFactory(),
      new NotificationSetting(
        null,
        DNotificationType::DELETE_AGENT,
        $agent->getId(),
        'delete-agent-notification',
        $this->adminUser->getId(),
        'admin@example.com',
        0
      )
    );
    $keepNotification = $this->createDatabaseObject(
      Factory::getNotificationSettingFactory(),
      new NotificationSetting(
        null,
        DNotificationType::NEW_TASK,
        $taskData['task']->getId(),
        'keep-task-notification',
        $this->adminUser->getId(),
        'admin@example.com',
        0
      )
    );

    return [
      'agent' => $agent,
      'task' => $taskData['task'],
      'accessGroup' => $taskData['accessGroup'],
      'assignment' => $assignment,
      'agentError' => $agentError,
      'agentStat' => $agentStat,
      'agentZap' => $agentZap,
      'healthCheck' => $healthCheck,
      'healthCheckAgent' => $healthCheckAgent,
      'speed' => $speed,
      'accessGroupAgent' => $accessGroupAgent,
      'chunk' => $chunk,
      'zap' => $zap,
      'deleteNotification' => $deleteNotification,
      'keepNotification' => $keepNotification,
    ];
  }

  private function assertDeletedAgentState(int $agentId, array $fixture): void {
    $this->assertNull(Factory::getAgentFactory()->get($agentId));
    $this->assertNotNull(Factory::getTaskFactory()->get($fixture['task']->getId()));
    $this->assertNotNull(Factory::getAccessGroupFactory()->get($fixture['accessGroup']->getId()));

    $this->assertCount(0, Factory::getAssignmentFactory()->filter([
      Factory::FILTER => new \Hashtopolis\dba\QueryFilter(Assignment::AGENT_ID, $agentId, '=')
    ]));
    $this->assertCount(0, Factory::getAgentErrorFactory()->filter([
      Factory::FILTER => new \Hashtopolis\dba\QueryFilter(AgentError::AGENT_ID, $agentId, '=')
    ]));
    $this->assertCount(0, Factory::getAgentStatFactory()->filter([
      Factory::FILTER => new \Hashtopolis\dba\QueryFilter(AgentStat::AGENT_ID, $agentId, '=')
    ]));
    $this->assertCount(0, Factory::getAgentZapFactory()->filter([
      Factory::FILTER => new \Hashtopolis\dba\QueryFilter(AgentZap::AGENT_ID, $agentId, '=')
    ]));
    $this->assertCount(0, Factory::getHealthCheckAgentFactory()->filter([
      Factory::FILTER => new \Hashtopolis\dba\QueryFilter(HealthCheckAgent::AGENT_ID, $agentId, '=')
    ]));
    $this->assertCount(0, Factory::getSpeedFactory()->filter([
      Factory::FILTER => new \Hashtopolis\dba\QueryFilter(Speed::AGENT_ID, $agentId, '=')
    ]));
    $this->assertCount(0, Factory::getAccessGroupAgentFactory()->filter([
      Factory::FILTER => new \Hashtopolis\dba\QueryFilter(AccessGroupAgent::AGENT_ID, $agentId, '=')
    ]));

    $deleteNotification = Factory::getNotificationSettingFactory()->get($fixture['deleteNotification']->getId());
    $keepNotification = Factory::getNotificationSettingFactory()->get($fixture['keepNotification']->getId());
    $this->assertNull($deleteNotification);
    $this->assertNotNull($keepNotification);

    $chunk = Factory::getChunkFactory()->get($fixture['chunk']->getId());
    $this->assertNotNull($chunk);
    $this->assertNull($chunk->getAgentId());

    $zap = Factory::getZapFactory()->get($fixture['zap']->getId());
    $this->assertNotNull($zap);
    $this->assertNull($zap->getAgentId());
  }
}
