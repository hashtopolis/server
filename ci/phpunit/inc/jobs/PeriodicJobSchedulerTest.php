<?php

namespace Hashtopolis\inc\jobs;

use Exception;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\BackgroundJob;
use Hashtopolis\dba\models\StoredValue;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\defines\DBackgroundJobStatus;
use Hashtopolis\inc\defines\DBackgroundJobType;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\SConfig;
use Hashtopolis\TestBase;
use Override;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class PeriodicJobSchedulerTest extends TestBase {
  private const ANCHOR_KEY = 'periodic_cleaning';
  /** interval the cleaning job runs on in these tests, in seconds */
  private const INTERVAL = 600;
  
  private ?string $savedAnchor = null;
  private mixed $savedInterval = null;
  
  #[Override]
  protected function setUp(): void {
    parent::setUp();
    $entry = Factory::getStoredValueFactory()->get(self::ANCHOR_KEY);
    $this->savedAnchor = ($entry === null) ? null : $entry->getVal();
    $this->savedInterval = SConfig::getInstance()->getVal(DConfig::CLEANING_INTERVAL);
    // pin the interval so the tests do not depend on the configured value
    SConfig::getInstance()->addValue(DConfig::CLEANING_INTERVAL, strval(self::INTERVAL / 60));
    // the scheduler refuses to queue a job while a previous one is unfinished, so make
    // that precondition deterministic
    $this->deleteUnfinishedCleaningJobs();
  }
  
  /**
   * @throws Exception
   */
  #[Override]
  protected function tearDown(): void {
    SConfig::getInstance()->addValue(DConfig::CLEANING_INTERVAL, $this->savedInterval);
    $factory = Factory::getStoredValueFactory();
    $entry = $factory->get(self::ANCHOR_KEY);
    if ($this->savedAnchor === null) {
      if ($entry !== null) {
        $factory->delete($entry);
      }
    }
    else if ($entry === null) {
      $factory->save(new StoredValue(self::ANCHOR_KEY, $this->savedAnchor));
    }
    else {
      $factory->set($entry, StoredValue::VAL, $this->savedAnchor);
    }
    parent::tearDown();
  }
  
  /**
   * @throws Exception
   */
  private function deleteUnfinishedCleaningJobs(): void {
    Factory::getBackgroundJobFactory()->massDeletion([
      Factory::FILTER => [
        new QueryFilter(BackgroundJob::JOB_TYPE, DBackgroundJobType::CLEANING, "="),
        new ContainFilter(BackgroundJob::STATUS, [DBackgroundJobStatus::PENDING, DBackgroundJobStatus::RUNNING]),
      ],
    ]);
  }
  
  /**
   * @return BackgroundJob[]
   * @throws Exception
   */
  private function getCleaningJobs(): array {
    $qF = new QueryFilter(BackgroundJob::JOB_TYPE, DBackgroundJobType::CLEANING, "=");
    return Factory::getBackgroundJobFactory()->filter([Factory::FILTER => $qF]);
  }
  
  /**
   * Runs the scheduler and returns only the jobs it created, registered for cleanup. Other
   * cleaning jobs can already be present in the database, so they are filtered out.
   *
   * @return BackgroundJob[]
   * @throws Exception
   */
  private function scheduleAndCollectCreated(): array {
    $before = [];
    foreach ($this->getCleaningJobs() as $job) {
      $before[$job->getId()] = true;
    }
    PeriodicJobScheduler::scheduleDue();
    $created = [];
    foreach ($this->getCleaningJobs() as $job) {
      if (!isset($before[$job->getId()])) {
        $created[] = $job;
      }
    }
    $this->registerDatabaseObjects(Factory::getBackgroundJobFactory(), $created);
    return $created;
  }
  
  /**
   * @throws Exception
   */
  private function setAnchor(int $value): void {
    $factory = Factory::getStoredValueFactory();
    $entry = $factory->get(self::ANCHOR_KEY);
    if ($entry === null) {
      $factory->save(new StoredValue(self::ANCHOR_KEY, strval($value)));
      return;
    }
    $factory->set($entry, StoredValue::VAL, strval($value));
  }
  
  /**
   * @throws Exception
   */
  private function getAnchor(): ?string {
    $entry = Factory::getStoredValueFactory()->get(self::ANCHOR_KEY);
    return ($entry === null) ? null : $entry->getVal();
  }
  
  /**
   * @throws Exception
   */
  public function testScheduleDueEnqueuesWhenNeverRunBefore(): void {
    $this->setAnchor(0);
    $before = time();
    
    $created = $this->scheduleAndCollectCreated();
    
    $this->assertCount(1, $created);
    $this->assertSame(DBackgroundJobStatus::PENDING, $created[0]->getStatus());
    $this->assertSame("[]", $created[0]->getPayload());
    // a periodic job is triggered by the server, not by a user. The integration test
    // cleanup relies on this to tell test data apart from system jobs.
    $this->assertNull($created[0]->getUserId());
    $this->assertGreaterThanOrEqual($before, intval($this->getAnchor()));
  }
  
  /**
   * @throws Exception
   */
  public function testScheduleDueIsThrottledWithinTheInterval(): void {
    $this->setAnchor(time());
    
    $this->assertCount(0, $this->scheduleAndCollectCreated());
  }
  
  /**
   * @throws Exception
   */
  public function testScheduleDueEnqueuesAgainAfterTheIntervalElapsed(): void {
    $this->setAnchor(time() - self::INTERVAL - 1);
    
    $this->assertCount(1, $this->scheduleAndCollectCreated());
  }
  
  /**
   * @throws Exception
   */
  public function testScheduleDueSkipsWhileAPreviousRunIsUnfinished(): void {
    $anchor = time() - self::INTERVAL - 1;
    $this->setAnchor($anchor);
    $this->createDatabaseObject(
      Factory::getBackgroundJobFactory(),
      new BackgroundJob(null, DBackgroundJobType::CLEANING, "[]", DBackgroundJobStatus::RUNNING, null, time(), time(), null, null, null)
    );
    
    $this->assertCount(0, $this->scheduleAndCollectCreated());
    // the anchor is only stamped once a job is actually queued, otherwise the interval
    // would restart without anything having run
    $this->assertSame($anchor, intval($this->getAnchor()));
  }
  
  /**
   * @throws Exception
   */
  public function testScheduleDueDoesNotExecuteTheJob(): void {
    $this->setAnchor(0);
    
    $created = $this->scheduleAndCollectCreated();
    
    $this->assertCount(1, $created);
    $job = Factory::getBackgroundJobFactory()->get($created[0]->getId());
    $this->assertSame(DBackgroundJobStatus::PENDING, $job->getStatus());
    $this->assertNull($job->getStartedAt());
    $this->assertNull($job->getFinishedAt());
  }
  
  public function testGetLastRunKeyPrefixesTheJobType(): void {
    $this->assertSame('periodic_cleaning', PeriodicJobScheduler::getLastRunKey(DBackgroundJobType::CLEANING));
  }
  
  /**
   * The key is stored in a varchar(50) on MySQL while it is a text on PostgreSQL, so
   * without this check an overly long job type would only fail on one of the two.
   */
  public function testGetLastRunKeyRejectsOverlongJobTypes(): void {
    $this->expectException(HTException::class);
    PeriodicJobScheduler::getLastRunKey(str_repeat('a', 42));
  }
}
