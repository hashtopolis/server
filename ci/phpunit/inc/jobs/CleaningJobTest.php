<?php

namespace Hashtopolis\inc\jobs;

use Exception;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\AgentStat;
use Hashtopolis\dba\models\AgentZap;
use Hashtopolis\dba\models\BackgroundJob;
use Hashtopolis\dba\models\Speed;
use Hashtopolis\dba\models\StoredValue;
use Hashtopolis\dba\models\Zap;
use Hashtopolis\inc\defines\DAgentStatsType;
use Hashtopolis\inc\defines\DBackgroundJobStatus;
use Hashtopolis\inc\defines\DBackgroundJobType;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DDirectories;
use Hashtopolis\inc\jobs\handlers\CleaningJob;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\Util;
use Hashtopolis\TestBase;
use Override;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class CleaningJobTest extends TestBase {
  /** lifetime the agent data cleanup uses in these tests, in seconds */
  private const AGENT_DATA_LIFETIME = 3600;
  /** retention of the zap queue, hardcoded in Util::zapCleaning() */
  private const ZAP_LIFETIME = 600;
  /** retention of the background job history, hardcoded in CleaningJob */
  private const JOB_HISTORY_LIFETIME = 2592000;
  
  private string $tusDirectory;
  private ?string $savedTusDirectory = null;
  private mixed $savedAgentDataLifetime = null;
  
  /**
   * @throws Exception
   */
  #[Override]
  protected function setUp(): void {
    parent::setUp();
    
    // point the TUS directory at a temporary one, so neither this test nor the full
    // execute() run touches the uploads of the instance the tests run against
    $this->tusDirectory = sys_get_temp_dir() . '/hashtopolis-tus-' . uniqid();
    mkdir($this->tusDirectory);
    mkdir($this->tusDirectory . '/meta');
    mkdir($this->tusDirectory . '/uploads');
    
    $factory = Factory::getStoredValueFactory();
    $entry = $factory->get(DDirectories::TUS);
    $this->savedTusDirectory = ($entry === null) ? null : $entry->getVal();
    if ($entry === null) {
      $factory->save(new StoredValue(DDirectories::TUS, $this->tusDirectory));
    }
    else {
      $factory->set($entry, StoredValue::VAL, $this->tusDirectory);
    }
    
    $this->savedAgentDataLifetime = SConfig::getInstance()->getVal(DConfig::AGENT_DATA_LIFETIME);
    SConfig::getInstance()->addValue(DConfig::AGENT_DATA_LIFETIME, strval(self::AGENT_DATA_LIFETIME));
  }
  
  /**
   * @throws Exception
   */
  #[Override]
  protected function tearDown(): void {
    SConfig::getInstance()->addValue(DConfig::AGENT_DATA_LIFETIME, $this->savedAgentDataLifetime);
    
    $factory = Factory::getStoredValueFactory();
    $entry = $factory->get(DDirectories::TUS);
    if ($this->savedTusDirectory === null) {
      if ($entry !== null) {
        $factory->delete($entry);
      }
    }
    else if ($entry !== null) {
      $factory->set($entry, StoredValue::VAL, $this->savedTusDirectory);
    }
    
    foreach (['meta', 'uploads'] as $subDirectory) {
      foreach (glob($this->tusDirectory . '/' . $subDirectory . '/*') ?: [] as $path) {
        unlink($path);
      }
      rmdir($this->tusDirectory . '/' . $subDirectory);
    }
    rmdir($this->tusDirectory);
    
    parent::tearDown();
  }
  
  /**
   * Writes the meta and part file of an upload expiring at the given time.
   */
  private function writeUpload(string $id, int $uploadExpires): void {
    file_put_contents($this->tusDirectory . '/meta/' . $id . '.meta', json_encode(['upload_expires' => $uploadExpires]));
    file_put_contents($this->tusDirectory . '/uploads/' . $id . '.part', 'content');
  }
  
  private function uploadExists(string $id): bool {
    return file_exists($this->tusDirectory . '/meta/' . $id . '.meta')
      || file_exists($this->tusDirectory . '/uploads/' . $id . '.part');
  }
  
  /**
   * Regression test for the expiration check, which used to compare against an hour in the
   * future instead of the present. That never matched a stale upload and, when it did
   * match, deleted an upload which was still being written.
   *
   * @throws Exception
   */
  public function testTusCleaningRemovesExpiredUploadsAndKeepsLiveOnes(): void {
    $this->writeUpload('expired', time() - Util::TUS_EXPIRY_GRACE - 100);
    $this->writeUpload('justExpired', time() - 1);
    $this->writeUpload('live', time() + 3600);
    
    // the meta and the part file of the expired upload
    $this->assertSame(2, Util::tusFileCleaning());
    
    $this->assertFalse($this->uploadExists('expired'));
    // still within the grace period, a request could still be writing to it
    $this->assertTrue($this->uploadExists('justExpired'));
    $this->assertTrue($this->uploadExists('live'));
  }
  
  /**
   * @throws Exception
   */
  public function testTusCleaningIgnoresMalformedMeta(): void {
    file_put_contents($this->tusDirectory . '/meta/truncated.meta', '{"upload_expires":');
    file_put_contents($this->tusDirectory . '/meta/incomplete.meta', json_encode(['size' => 10]));
    file_put_contents($this->tusDirectory . '/uploads/truncated.part', 'content');
    file_put_contents($this->tusDirectory . '/uploads/incomplete.part', 'content');
    
    $this->assertSame(0, Util::tusFileCleaning());
    
    $this->assertTrue($this->uploadExists('truncated'));
    $this->assertTrue($this->uploadExists('incomplete'));
  }
  
  /**
   * @throws Exception
   */
  public function testAgentStatCleaningRemovesDataOlderThanTheLifetime(): void {
    $helper = $this->createTaskHelper();
    $agent = $this->createAgent('phpunitCleaning');
    $statFactory = Factory::getAgentStatFactory();
    $speedFactory = Factory::getSpeedFactory();
    
    $oldStat = $this->createDatabaseObject($statFactory, new AgentStat(null, $agent->getId(), DAgentStatsType::GPU_TEMP, time() - self::AGENT_DATA_LIFETIME - 100, '[50]'));
    $newStat = $this->createDatabaseObject($statFactory, new AgentStat(null, $agent->getId(), DAgentStatsType::GPU_TEMP, time(), '[50]'));
    $oldSpeed = $this->createDatabaseObject($speedFactory, new Speed(null, $agent->getId(), $helper['task']->getId(), 1000, time() - self::AGENT_DATA_LIFETIME - 100));
    $newSpeed = $this->createDatabaseObject($speedFactory, new Speed(null, $agent->getId(), $helper['task']->getId(), 1000, time()));
    
    // the cleanup is global, so it can also remove leftovers of other runs
    $this->assertGreaterThanOrEqual(2, Util::agentStatCleaning());
    
    $this->assertNull($statFactory->get($oldStat->getId()));
    $this->assertNotNull($statFactory->get($newStat->getId()));
    $this->assertNull($speedFactory->get($oldSpeed->getId()));
    $this->assertNotNull($speedFactory->get($newSpeed->getId()));
  }
  
  /**
   * @throws Exception
   */
  public function testZapCleaningClearsAgentReferencesBeforeDeleting(): void {
    $helper = $this->createTaskHelper();
    $agent = $this->createAgent('phpunitCleaningZap');
    
    // the zap has to be registered before the agent zap referencing it, as the test base
    // deletes the objects in reverse order of their registration
    $zap = $this->createDatabaseObject(
      Factory::getZapFactory(),
      new Zap(null, 'hash', time() - self::ZAP_LIFETIME - 100, $agent->getId(), $helper['hashlist']->getId())
    );
    $agentZap = $this->createDatabaseObject(
      Factory::getAgentZapFactory(),
      new AgentZap(null, $agent->getId(), $zap->getId())
    );
    
    $this->assertGreaterThanOrEqual(1, Util::zapCleaning());
    
    $this->assertNull(Factory::getZapFactory()->get($zap->getId()));
    $this->assertNull(Factory::getAgentZapFactory()->get($agentZap->getId())->getLastZapId());
  }
  
  /**
   * @throws Exception
   */
  public function testExecutePrunesTerminalJobsOnlyAndReportsEveryStep(): void {
    $factory = Factory::getBackgroundJobFactory();
    $old = time() - self::JOB_HISTORY_LIFETIME - 100;
    
    $oldDone = $this->createDatabaseObject($factory, new BackgroundJob(null, DBackgroundJobType::CLEANING, "[]", DBackgroundJobStatus::DONE, null, $old, $old, $old, 0, 'done'));
    $oldFailed = $this->createDatabaseObject($factory, new BackgroundJob(null, DBackgroundJobType::CLEANING, "[]", DBackgroundJobStatus::FAILED, null, $old, $old, $old, -1, 'failed'));
    $oldPending = $this->createDatabaseObject($factory, new BackgroundJob(null, DBackgroundJobType::CLEANING, "[]", DBackgroundJobStatus::PENDING, null, $old, null, null, null, null));
    $recentDone = $this->createDatabaseObject($factory, new BackgroundJob(null, DBackgroundJobType::CLEANING, "[]", DBackgroundJobStatus::DONE, null, time(), time(), time(), 0, 'done'));
    
    $job = new BackgroundJob(null, DBackgroundJobType::CLEANING, "[]", DBackgroundJobStatus::RUNNING, null, time(), time(), null, null, null);
    $result = (new CleaningJob())->execute($job, []);
    
    $this->assertSame(0, $result->getExitCode());
    $message = $result->getMessage();
    $this->assertLessThanOrEqual(1024, strlen($message));
    foreach (['agent statistics', 'zap queue', 'stale uploads', 'expired refresh tokens', 'job history'] as $step) {
      $this->assertStringContainsString($step, $message);
    }
    
    $this->assertNull($factory->get($oldDone->getId()));
    $this->assertNull($factory->get($oldFailed->getId()));
    // a queued job is never dropped, no matter how long it has been waiting
    $this->assertNotNull($factory->get($oldPending->getId()));
    $this->assertNotNull($factory->get($recentDone->getId()));
  }
}
