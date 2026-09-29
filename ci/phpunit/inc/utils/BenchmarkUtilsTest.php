<?php

namespace Tests\Utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\Task;
use Hashtopolis\inc\DataSet;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\utils\BenchmarkUtils;
use Hashtopolis\TestBase;
use Override;
use ReflectionClass;
use Throwable;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

/**
 * Unit tests for BenchmarkUtils, the benchmark cache (#879).
 *
 * The correctness of the cache rests on its key covering every speed-affecting
 * factor. These tests pin that: the signature builders are deterministic and
 * change when any of their inputs change (the whole attack command, the
 * preprocessor and pipe settings, the salt count, and every device factor),
 * store and lookup agree on the key for identical inputs, changing any single
 * factor produces a miss, and an expired entry is not returned.
 */
final class BenchmarkUtilsTest extends TestBase {
  private ?Task $task = null;
  private ?Hashlist $hashlist = null;
  private ?Agent $agent = null;

  /**
   * @throws \Exception
   */
  #[Override]
  protected function setUp(): void {
    parent::setUp();
    $fixtures = $this->createTaskHelper();
    $this->task = $fixtures['task'];
    $this->hashlist = $fixtures['hashlist'];
    $this->agent = $this->createAgent('benchmarkutils');
  }

  /**
   * @throws \Exception
   */
  #[Override]
  protected function tearDown(): void {
    // Cache rows are not registered for cleanup and hold foreign keys to the
    // cracker binary and hash type, so clear them before TestBase deletes the
    // fixtures.
    try {
      Factory::getBenchmarkFactory()->massDeletion([]);
    }
    catch (Throwable $e) {
      // ignore, table may not exist in a partial environment
    }
    SConfig::reload();
    parent::tearDown();
  }

  private function setTtl(int $ttl): void {
    // Start from the real config and override only the TTL, so every other lookup
    // still works: store() logs through Util::createLogEntry, whose rotation reads
    // numLogEntries, and wiping it would make that read null and delete unrelated
    // log entries (breaking test_logentry later in the same run).
    $values = SConfig::getInstance()->getAllValues();
    $values[DConfig::BENCHMARK_CACHE_TTL] = (string)$ttl;
    $property = (new ReflectionClass(SConfig::class))->getProperty('instance');
    $property->setValue(null, new DataSet($values));
  }

  public function testRunSignatureIsDeterministic(): void {
    $this->assertSame(
      BenchmarkUtils::computeRunSignature($this->task),
      BenchmarkUtils::computeRunSignature($this->task)
    );
  }

  public function testRunSignatureDependsOnWholeCommand(): void {
    // Two masks of the same attack mode measure at very different speeds (a small
    // or literal-prefixed mask is candidate starved), so they must NOT share a
    // signature. This is the whole point of keying on the command, not the mode.
    $maskA = clone $this->task; $maskA->setAttackCmd('#HL# -a 3 ?d?d?d?d');
    $maskB = clone $this->task; $maskB->setAttackCmd('#HL# -a 3 ?l?l?l?l?l');
    $this->assertNotSame(
      BenchmarkUtils::computeRunSignature($maskA),
      BenchmarkUtils::computeRunSignature($maskB),
      'two different masks must produce different signatures'
    );

    // A different attack mode changes the signature.
    $straight = clone $this->task; $straight->setAttackCmd('#HL# -a 0 wordlist.txt');
    $this->assertNotSame(
      BenchmarkUtils::computeRunSignature($maskA),
      BenchmarkUtils::computeRunSignature($straight),
      'a different attack mode must change the signature'
    );

    // Applying a rule file changes the signature.
    $withRules = clone $this->task; $withRules->setAttackCmd('#HL# -a 0 wordlist.txt -r best64.rule');
    $this->assertNotSame(
      BenchmarkUtils::computeRunSignature($straight),
      BenchmarkUtils::computeRunSignature($withRules),
      'applying a rule file must change the signature'
    );

    // A different rule set measures at a different speed, so it must change the
    // signature too.
    $withRules2 = clone $this->task; $withRules2->setAttackCmd('#HL# -a 0 wordlist.txt -r dive.rule');
    $this->assertNotSame(
      BenchmarkUtils::computeRunSignature($withRules),
      BenchmarkUtils::computeRunSignature($withRules2),
      'a different rule file must change the signature'
    );

    // An optimized-kernel flag changes the speed, so it must change the signature.
    $optimized = clone $this->task; $optimized->setAttackCmd('#HL# -a 3 ?d?d?d?d -O');
    $this->assertNotSame(
      BenchmarkUtils::computeRunSignature($maskA),
      BenchmarkUtils::computeRunSignature($optimized),
      'a flag that changes the run (like -O) must change the signature'
    );
  }

  public function testSaltNormalizationReusesAcrossSaltCounts(): void {
    // The salt count is NOT in the key; a benchmark stored for one salt count is
    // reused for another with the value rescaled. The benchmark scales inversely
    // with the salt count, so more salts means a proportionally larger time.
    $this->setTtl(3600);

    // Store a speed benchmark ("keyspaceCount:timeMs") measured on 1000 salts.
    $salted1000 = clone $this->hashlist;
    $salted1000->setIsSalted(1);
    $salted1000->setHashCount(1000);
    BenchmarkUtils::store($this->task, $salted1000, $this->agent, '857375:286927');

    // An unsalted hashlist (salt count 1) hits the same entry; the stored value is
    // normalized to a single salt, so its time is 1000x smaller.
    $unsalted = clone $this->hashlist;
    $unsalted->setIsSalted(0);
    $got1 = BenchmarkUtils::lookup($this->task, $unsalted, $this->agent);
    $this->assertNotNull($got1, 'a benchmark from another salt count must still hit');
    [$count1, $time1] = explode(':', $got1);
    $this->assertSame('857375', $count1, 'rescaling leaves the keyspace count unchanged');
    $this->assertEqualsWithDelta(286.927, (float)$time1, 0.001, 'the time is rescaled to a single salt');

    // A 500-salt hashlist gets half the 1000-salt time.
    $salted500 = clone $this->hashlist;
    $salted500->setIsSalted(1);
    $salted500->setHashCount(500);
    $got500 = BenchmarkUtils::lookup($this->task, $salted500, $this->agent);
    [, $time500] = explode(':', $got500);
    $this->assertEqualsWithDelta(143463.5, (float)$time500, 0.01, 'the time scales with the salt count');
  }

  public function testRunSignatureDependsOnPreprocessorAndPipe(): void {
    $base = BenchmarkUtils::computeRunSignature($this->task);

    $pre = clone $this->task;
    $pre->setUsePreprocessor((int)$this->task->getUsePreprocessor() + 1);
    $this->assertNotSame($base, BenchmarkUtils::computeRunSignature($pre), 'the preprocessor id must be part of the signature');

    $preCmd = clone $this->task;
    $preCmd->setPreprocessorCommand('--pw-min=8');
    $this->assertNotSame($base, BenchmarkUtils::computeRunSignature($preCmd), 'the preprocessor command must be part of the signature');

    $pipe = clone $this->task;
    $pipe->setForcePipe($this->task->getForcePipe() == 1 ? 0 : 1);
    $this->assertNotSame($base, BenchmarkUtils::computeRunSignature($pipe), 'the forced-pipe flag must be part of the signature');
  }

  public function testRunSignatureIgnoresWhitespaceOnly(): void {
    $t = clone $this->task;
    $t->setAttackCmd('  ' . preg_replace('/ /', '  ', (string)$this->task->getAttackCmd()) . '  ');
    $this->assertSame(
      BenchmarkUtils::computeRunSignature($this->task),
      BenchmarkUtils::computeRunSignature($t),
      'differences in surrounding/redundant whitespace must not change the signature'
    );
  }

  public function testDeviceSignatureIsDeterministic(): void {
    $this->assertSame(
      BenchmarkUtils::computeDeviceSignature($this->agent),
      BenchmarkUtils::computeDeviceSignature($this->agent)
    );
  }

  public function testDeviceSignatureChangesWithEveryDeviceFactor(): void {
    $base = BenchmarkUtils::computeDeviceSignature($this->agent);

    $a = clone $this->agent;
    $a->setDevices("GeForce RTX 4090\nGeForce RTX 4090");
    $this->assertNotSame($base, BenchmarkUtils::computeDeviceSignature($a), 'devices must affect the signature');

    $a = clone $this->agent;
    $a->setCpuOnly($this->agent->getCpuOnly() == 1 ? 0 : 1);
    $this->assertNotSame($base, BenchmarkUtils::computeDeviceSignature($a), 'cpuOnly must affect the signature');

    $a = clone $this->agent;
    $a->setCmdPars('--force -O');
    $this->assertNotSame($base, BenchmarkUtils::computeDeviceSignature($a), 'cmdPars must affect the signature');
  }

  public function testBenchmarkTypeForTask(): void {
    $t = clone $this->task;
    $t->setUseNewBench(1);
    $this->assertSame('speed', BenchmarkUtils::benchmarkTypeForTask($t));
    $t->setUseNewBench(0);
    $this->assertSame('run', BenchmarkUtils::benchmarkTypeForTask($t));
  }

  public function testHashlistForTaskResolvesViaJoin(): void {
    $resolved = BenchmarkUtils::hashlistForTask($this->task);
    $this->assertNotNull($resolved, 'the task must resolve to its hashlist through the join');
    $this->assertSame($this->hashlist->getId(), $resolved->getId(), 'the join must return the task\'s own hashlist');
  }

  public function testStoreThenLookupReturnsValueForSameKey(): void {
    $this->setTtl(3600);
    BenchmarkUtils::store($this->task, $this->hashlist, $this->agent, '2345:323.000');
    $this->assertSame('2345:323.000', BenchmarkUtils::lookup($this->task, $this->hashlist, $this->agent));
  }

  public function testLookupDisabledWhenTtlZero(): void {
    $this->setTtl(3600);
    BenchmarkUtils::store($this->task, $this->hashlist, $this->agent, '2345:323.000');
    $this->setTtl(0);
    $this->assertNull(BenchmarkUtils::lookup($this->task, $this->hashlist, $this->agent), 'lookup must be disabled when TTL is 0');
  }

  public function testStoreDoesNothingWhenTtlZero(): void {
    $this->setTtl(0);
    BenchmarkUtils::store($this->task, $this->hashlist, $this->agent, '2345:323.000');
    $this->setTtl(3600);
    $this->assertNull(BenchmarkUtils::lookup($this->task, $this->hashlist, $this->agent), 'store must be disabled when TTL is 0');
  }

  public function testLookupMissWhenAnyKeyFactorDiffers(): void {
    $this->setTtl(3600);
    $this->task->setAttackCmd('#HL# -a 3 ?d?d?d?d');
    BenchmarkUtils::store($this->task, $this->hashlist, $this->agent, '2345:323.000');

    // Control: identical inputs hit.
    $this->assertSame('2345:323.000', BenchmarkUtils::lookup($this->task, $this->hashlist, $this->agent));

    // Different cracker binary.
    $t = clone $this->task;
    $t->setCrackerBinaryId($this->task->getCrackerBinaryId() + 99999);
    $this->assertNull(BenchmarkUtils::lookup($t, $this->hashlist, $this->agent), 'cracker binary must be part of the key');

    // Different hash type.
    $hl = clone $this->hashlist;
    $hl->setHashTypeId((int)$this->hashlist->getHashTypeId() + 1);
    $this->assertNull(BenchmarkUtils::lookup($this->task, $hl, $this->agent), 'hash type must be part of the key');

    // Different attack command (a different mask of the same mode).
    $t = clone $this->task;
    $t->setAttackCmd('#HL# -a 3 ?l?l?l?l?l');
    $this->assertNull(BenchmarkUtils::lookup($t, $this->hashlist, $this->agent), 'the whole attack command must be part of the key');

    // A different salt count is NOT a key factor: it still hits, with the value
    // rescaled (see testSaltNormalizationReusesAcrossSaltCounts).
    $hl = clone $this->hashlist;
    $hl->setIsSalted(1);
    $hl->setHashCount((int)$this->hashlist->getHashCount() + 987654);
    $this->assertNotNull(BenchmarkUtils::lookup($this->task, $hl, $this->agent), 'a different salt count must still hit, rescaled');

    // Different device signature.
    $a = clone $this->agent;
    $a->setDevices("GeForce RTX 4090");
    $this->assertNull(BenchmarkUtils::lookup($this->task, $this->hashlist, $a), 'device signature must be part of the key');

    // Different benchmark method (useNewBench toggles speed/run).
    $t = clone $this->task;
    $t->setUseNewBench($this->task->getUseNewBench() == 1 ? 0 : 1);
    $this->assertNull(BenchmarkUtils::lookup($t, $this->hashlist, $this->agent), 'benchmark type must be part of the key');

    // Control again: the original still hits, proving the misses were not side effects.
    $this->assertSame('2345:323.000', BenchmarkUtils::lookup($this->task, $this->hashlist, $this->agent));
  }

  public function testExpiredEntryIsNotReturned(): void {
    $this->setTtl(3600);
    BenchmarkUtils::store($this->task, $this->hashlist, $this->agent, '999:9.9');
    $rows = Factory::getBenchmarkFactory()->filter([]);
    $this->assertCount(1, $rows, 'the store should have written exactly one row');
    $benchmark = $rows[0];
    $now = time();

    // Push its expiry into the past, so lookup must not return it.
    $benchmark->setExpireTime($now - 50);
    Factory::getBenchmarkFactory()->update($benchmark);
    $this->assertNull(BenchmarkUtils::lookup($this->task, $this->hashlist, $this->agent), 'an expired entry must not be returned');

    // Extend its expiry into the future and it becomes visible again.
    $benchmark->setExpireTime($now + 3600);
    Factory::getBenchmarkFactory()->update($benchmark);
    $this->assertSame('999:9.9', BenchmarkUtils::lookup($this->task, $this->hashlist, $this->agent), 'an unexpired entry must be returned');
  }
}
