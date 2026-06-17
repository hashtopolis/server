<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\HealthCheck;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\defines\DHealthCheckAgentStatus;
use Hashtopolis\inc\defines\DHealthCheckMode;
use Hashtopolis\inc\defines\DHealthCheckStatus;
use Hashtopolis\inc\defines\DHealthCheckType;
use Hashtopolis\inc\HTException;
use Hashtopolis\TestBase;
use Override;
use ReflectionClass;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class HealthUtilsTest extends TestBase {
  private HealthCheck $healthCheck;
  private HealthCheckAgent $healthCheckAgent;
  private HealthCheckAgent $completedAgent;
  private Agent $agent;
  private Agent $otherAgent;
  private CrackerBinary $crackerBinary;

  #[Override]
  protected function setUp(): void {
    parent::setUp();

    $crackerBinaryType = $this->createCrackerBinaryType();
    $this->crackerBinary = $this->createCrackerBinary($crackerBinaryType);
    $this->agent = $this->createAgent('hc_agent');
    $this->otherAgent = $this->createAgent('hc_other');

    $this->healthCheck = $this->createHealthCheck($this->crackerBinary, DHealthCheckStatus::PENDING, DHealthCheckType::BRUTE_FORCE, DHealthCheckMode::MD5, 50, '-a 3 -1 ?l?u?d ?1?1?1?1?1');

    $this->healthCheckAgent = $this->createHealthCheckAgent($this->healthCheck, $this->agent);

    $this->completedAgent = $this->createHealthCheckAgent($this->healthCheck, $this->otherAgent, DHealthCheckAgentStatus::COMPLETED, 10, 2, 100, 200);
  }

  #[Override]
  protected function tearDown(): void {
    $tmpFile = '/tmp/health-check-' . ($this->healthCheck->getId() ?? 0) . '.txt';
    if (file_exists($tmpFile)) {
      unlink($tmpFile);
    }
    parent::tearDown();
  }

  public function testGenerateHashMd5(): void {
    $plain = 'testplain';
    $hash = HealthUtils::generateHash(DHealthCheckMode::MD5, $plain);
    $this->assertSame(md5($plain), $hash);
  }

  public function testGenerateHashBcrypt(): void {
    $plain = 'abc';
    $hash = HealthUtils::generateHash(DHealthCheckMode::BCRYPT, $plain);
    $this->assertNotFalse(password_verify($plain, $hash));
  }

  public function testGenerateHashThrowsForUnknownHashtype(): void {
    $this->expectException(HTException::class);
    HealthUtils::generateHash(999999, 'plain');
  }

  public function testGetAttackModeBruteForce(): void {
    $mode = $this->callPrivateMethod('getAttackMode', DHealthCheckType::BRUTE_FORCE);
    $this->assertSame(' -a 3', $mode);
  }

  public function testGetAttackInputMd5BruteForce(): void {
    $input = $this->callPrivateMethod('getAttackInput', DHealthCheckMode::MD5, DHealthCheckType::BRUTE_FORCE);
    $this->assertSame(' -1 ?l?u?d ?1?1?1?1?1', $input);
  }

  public function testGetAttackInputBcryptBruteForce(): void {
    $input = $this->callPrivateMethod('getAttackInput', DHealthCheckMode::BCRYPT, DHealthCheckType::BRUTE_FORCE);
    $this->assertSame(' ?l?l?l', $input);
  }

  public function testGetAttackNumHashesMd5(): void {
    $num = $this->callPrivateMethod('getAttackNumHashes', DHealthCheckMode::MD5);
    $this->assertSame(100, $num);
  }

  public function testGetAttackNumHashesBcrypt(): void {
    $num = $this->callPrivateMethod('getAttackNumHashes', DHealthCheckMode::BCRYPT);
    $this->assertSame(10, $num);
  }

  public function testGetAttackNumHashesUnknown(): void {
    $num = $this->callPrivateMethod('getAttackNumHashes', 999);
    $this->assertSame(100, $num);
  }

  public function testCheckNeededReturnsPendingAgentCheck(): void {
    $result = HealthUtils::checkNeeded($this->agent);
    $this->assertInstanceOf(HealthCheckAgent::class, $result);
    $this->assertSame($this->healthCheckAgent->getId(), $result->getId());
  }

  public function testCheckNeededReturnsFalseWhenAgentHasNoPending(): void {
    $freshAgent = $this->createAgent('hc_fresh');
    $result = HealthUtils::checkNeeded($freshAgent);
    $this->assertFalse($result);
  }

  public function testCheckNeededReturnsFalseWhenHealthCheckIsAborted(): void {
    $abortedCheck = $this->createHealthCheck($this->crackerBinary, DHealthCheckStatus::ABORTED);
    $isolatedAgent = $this->createAgent('hc_isolated');
    $pendingAgent = $this->createHealthCheckAgent($abortedCheck, $isolatedAgent);

    $result = HealthUtils::checkNeeded($isolatedAgent);
    $this->assertFalse($result);
  }

  public function testCheckCompletionMarksCompleteWhenAllAgentsDone(): void {
    $allDoneCheck = $this->createHealthCheck($this->crackerBinary);
    $this->createHealthCheckAgent($allDoneCheck, $this->agent, DHealthCheckAgentStatus::COMPLETED, 5, 1, 0, 10);
    $this->createHealthCheckAgent($allDoneCheck, $this->otherAgent, DHealthCheckAgentStatus::FAILED, 0, 0, 0, 0, 'error');

    HealthUtils::checkCompletion($allDoneCheck);

    $updated = Factory::getHealthCheckFactory()->get($allDoneCheck->getId());
    $this->assertSame(DHealthCheckStatus::COMPLETED, $updated->getStatus());
  }

  public function testCheckCompletionDoesNotCompleteWhenAgentPending(): void {
    HealthUtils::checkCompletion($this->healthCheck);

    $updated = Factory::getHealthCheckFactory()->get($this->healthCheck->getId());
    $this->assertSame(DHealthCheckStatus::PENDING, $updated->getStatus());
  }

  public function testResetAgentCheckResetsPendingAgent(): void {
    HealthUtils::resetAgentCheck($this->healthCheckAgent->getId());

    $updated = Factory::getHealthCheckAgentFactory()->get($this->healthCheckAgent->getId());
    $this->assertSame(DHealthCheckAgentStatus::PENDING, $updated->getStatus());
    $this->assertSame(0, $updated->getStart());
    $this->assertSame(0, $updated->getEnd());
    $this->assertSame('', $updated->getErrors());
    $this->assertSame(0, $updated->getCracked());
    $this->assertSame(0, $updated->getNumGpus());
  }

  public function testResetAgentCheckReopensCompletedHealthCheck(): void {
    $completedCheck = $this->createHealthCheck($this->crackerBinary, DHealthCheckStatus::COMPLETED);
    $agentCheck = $this->createHealthCheckAgent($completedCheck, $this->agent, DHealthCheckAgentStatus::COMPLETED, 5, 1, 0, 10);

    HealthUtils::resetAgentCheck($agentCheck->getId());

    $updatedCheck = Factory::getHealthCheckFactory()->get($completedCheck->getId());
    $this->assertSame(DHealthCheckStatus::PENDING, $updatedCheck->getStatus());
  }

  public function testResetAgentCheckThrowsForAbortedHealthCheck(): void {
    $abortedCheck = $this->createHealthCheck($this->crackerBinary, DHealthCheckStatus::ABORTED);
    $agentCheck = $this->createHealthCheckAgent($abortedCheck, $this->agent, DHealthCheckAgentStatus::FAILED, 5, 1, 0, 10);

    $this->expectException(HTException::class);
    HealthUtils::resetAgentCheck($agentCheck->getId());
  }

  public function testResetAgentCheckThrowsForInvalidId(): void {
    $this->expectException(HTException::class);
    HealthUtils::resetAgentCheck(-1);
  }

  public function testDeleteHealthCheckRemovesCheckAndAgents(): void {
    HealthUtils::deleteHealthCheck($this->healthCheck->getId());

    $this->assertNull(Factory::getHealthCheckFactory()->get($this->healthCheck->getId()));

    $qF = new QueryFilter(HealthCheckAgent::HEALTH_CHECK_ID, $this->healthCheck->getId(), '=');
    $remaining = Factory::getHealthCheckAgentFactory()->filter([Factory::FILTER => $qF]);
    $this->assertSame([], $remaining);
  }

  public function testDeleteHealthCheckThrowsForInvalidId(): void {
    $this->expectException(HTException::class);
    HealthUtils::deleteHealthCheck(-1);
  }

  private function callPrivateMethod(string $name, ...$args): mixed {
    $ref = new ReflectionClass(HealthUtils::class);
    $method = $ref->getMethod($name);
    return $method->invoke(null, ...$args);
  }
}
