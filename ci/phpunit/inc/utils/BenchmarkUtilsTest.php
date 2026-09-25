<?php

namespace Tests\Utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Benchmark;
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
 * change when any of their inputs change, store and lookup agree on the key for
 * identical inputs, changing any single factor (cracker binary, hash mode,
 * attack parameters, device signature, benchmark type) produces a miss, and an
 * expired entry is not returned.
 */
final class BenchmarkUtilsTest extends TestBase {
  private ?Task $task = null;
  private ?Agent $agent = null;
  private int $hashMode = 1000;

  /**
   * @throws \Exception
   */
  #[Override]
  protected function setUp(): void {
    parent::setUp();
    $fixtures = $this->createTaskHelper();
    $this->task = $fixtures['task'];
    $this->agent = $this->createAgent('benchmarkutils');
  }

  /**
   * @throws \Exception
   */
  #[Override]
  protected function tearDown(): void {
    // Cache rows are not registered for cleanup and hold a foreign key to the
    // cracker binary, so clear them before TestBase deletes the fixtures.
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
    $property = (new ReflectionClass(SConfig::class))->getProperty('instance');
    $property->setValue(null, new DataSet([DConfig::BENCHMARK_CACHE_TTL => (string)$ttl]));
  }

  public function testAttackSignatureIsDeterministic(): void {
    $this->assertSame(
      BenchmarkUtils::computeAttackSignature($this->task),
      BenchmarkUtils::computeAttackSignature($this->task)
    );
  }

  public function testAttackSignatureDependsOnModeAndRulesOnly(): void {
    // Same attack mode, different mask: one shared signature. This is the point
    // of keying on the mode rather than the whole command.
    $maskA = clone $this->task; $maskA->setAttackCmd('#HL# -a 3 ?d?d?d?d');
    $maskB = clone $this->task; $maskB->setAttackCmd('#HL# -a 3 ?l?l?l?l?l');
    $this->assertSame(
      BenchmarkUtils::computeAttackSignature($maskA),
      BenchmarkUtils::computeAttackSignature($maskB),
      'two masks of the same attack mode must share a signature'
    );

    // A different attack mode ('-a') changes the signature.
    $straight = clone $this->task; $straight->setAttackCmd('#HL# -a 0 wordlist.txt');
    $this->assertNotSame(
      BenchmarkUtils::computeAttackSignature($maskA),
      BenchmarkUtils::computeAttackSignature($straight),
      'a different attack mode must change the signature'
    );

    // Applying a rule file ('-r') changes the signature.
    $withRules = clone $this->task; $withRules->setAttackCmd('#HL# -a 0 wordlist.txt -r best64.rule');
    $this->assertNotSame(
      BenchmarkUtils::computeAttackSignature($straight),
      BenchmarkUtils::computeAttackSignature($withRules),
      'applying a rule file must change the signature'
    );

    // A different rule file, still rules: same signature.
    $withRules2 = clone $this->task; $withRules2->setAttackCmd('#HL# -a 0 other.txt -r dive.rule');
    $this->assertSame(
      BenchmarkUtils::computeAttackSignature($withRules),
      BenchmarkUtils::computeAttackSignature($withRules2),
      'the specific rule file must not change the signature'
    );

    // An unrelated option ('-O') must not change the signature.
    $optimized = clone $this->task; $optimized->setAttackCmd('#HL# -a 3 ?d?d?d?d -O');
    $this->assertSame(
      BenchmarkUtils::computeAttackSignature($maskA),
      BenchmarkUtils::computeAttackSignature($optimized),
      'an option that does not affect the mode or rule use must not change the signature'
    );
  }

  public function testAttackSignatureIgnoresWhitespaceOnly(): void {
    $t = clone $this->task;
    $t->setAttackCmd('  ' . preg_replace('/ /', '  ', (string)$this->task->getAttackCmd()) . '  ');
    $this->assertSame(
      BenchmarkUtils::computeAttackSignature($this->task),
      BenchmarkUtils::computeAttackSignature($t),
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

  public function testStoreThenLookupReturnsValueForSameKey(): void {
    $this->setTtl(3600);
    BenchmarkUtils::store($this->task, $this->hashMode, $this->agent, '2345:323.000');
    $this->assertSame('2345:323.000', BenchmarkUtils::lookup($this->task, $this->hashMode, $this->agent));
  }

  public function testLookupDisabledWhenTtlZero(): void {
    $this->setTtl(3600);
    BenchmarkUtils::store($this->task, $this->hashMode, $this->agent, '2345:323.000');
    $this->setTtl(0);
    $this->assertNull(BenchmarkUtils::lookup($this->task, $this->hashMode, $this->agent), 'lookup must be disabled when TTL is 0');
  }

  public function testStoreDoesNothingWhenTtlZero(): void {
    $this->setTtl(0);
    BenchmarkUtils::store($this->task, $this->hashMode, $this->agent, '2345:323.000');
    $this->setTtl(3600);
    $this->assertNull(BenchmarkUtils::lookup($this->task, $this->hashMode, $this->agent), 'store must be disabled when TTL is 0');
  }

  public function testLookupMissWhenAnyKeyFactorDiffers(): void {
    $this->setTtl(3600);
    // Pin a known attack (mask, no rules) so the "different attack" case below is
    // guaranteed to differ in mode and rule use.
    $this->task->setAttackCmd('#HL# -a 3 ?d?d?d?d');
    BenchmarkUtils::store($this->task, $this->hashMode, $this->agent, '2345:323.000');

    // Control: identical inputs hit.
    $this->assertSame('2345:323.000', BenchmarkUtils::lookup($this->task, $this->hashMode, $this->agent));

    // Different cracker binary.
    $t = clone $this->task;
    $t->setCrackerBinaryId($this->task->getCrackerBinaryId() + 99999);
    $this->assertNull(BenchmarkUtils::lookup($t, $this->hashMode, $this->agent), 'cracker binary must be part of the key');

    // Different hash mode.
    $this->assertNull(BenchmarkUtils::lookup($this->task, $this->hashMode + 1, $this->agent), 'hash mode must be part of the key');

    // Different attack: the signature keys on the '-a' mode and rule use, so a
    // straight attack with rules differs from the stored mask attack.
    $t = clone $this->task;
    $t->setAttackCmd('#HL# -a 0 wordlist.txt -r best64.rule');
    $this->assertNull(BenchmarkUtils::lookup($t, $this->hashMode, $this->agent), 'attack mode and rule use must be part of the key');

    // Different device signature.
    $a = clone $this->agent;
    $a->setDevices("GeForce RTX 4090");
    $this->assertNull(BenchmarkUtils::lookup($this->task, $this->hashMode, $a), 'device signature must be part of the key');

    // Different benchmark method (useNewBench toggles speed/run).
    $t = clone $this->task;
    $t->setUseNewBench($this->task->getUseNewBench() == 1 ? 0 : 1);
    $this->assertNull(BenchmarkUtils::lookup($t, $this->hashMode, $this->agent), 'benchmark type must be part of the key');

    // Control again: the original still hits, proving the misses were not side effects.
    $this->assertSame('2345:323.000', BenchmarkUtils::lookup($this->task, $this->hashMode, $this->agent));
  }

  public function testExpiredEntryIsNotReturned(): void {
    $this->setTtl(3600);
    $now = time();
    $benchmark = new Benchmark(
      null,
      $this->task->getCrackerBinaryId(),
      $this->hashMode,
      BenchmarkUtils::computeAttackSignature($this->task),
      BenchmarkUtils::computeDeviceSignature($this->agent),
      BenchmarkUtils::benchmarkTypeForTask($this->task),
      '999:9.9',
      $now - 100,
      $now - 50
    );
    $benchmark = Factory::getBenchmarkFactory()->save($benchmark);

    // The entry exists but is expired, so lookup must not return it.
    $this->assertNull(BenchmarkUtils::lookup($this->task, $this->hashMode, $this->agent), 'an expired entry must not be returned');

    // Extend its expiry into the future and it becomes visible again.
    $benchmark->setExpireTime($now + 3600);
    Factory::getBenchmarkFactory()->update($benchmark);
    $this->assertSame('999:9.9', BenchmarkUtils::lookup($this->task, $this->hashMode, $this->agent), 'an unexpired entry must be returned');
  }
}
