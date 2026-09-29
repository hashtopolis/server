<?php

declare(strict_types=1);

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Benchmark;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\OrderFilter;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\agent\PValuesBenchmarkType;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\SConfig;

/**
 * Benchmark cache (issue #879).
 *
 * An agent normally re-runs a benchmark on every task pickup, which wastes GPU
 * time. This cache stores the benchmark result an agent reported and hands the
 * same value to any agent whose run would produce the same speed, until the
 * entry expires.
 *
 * The cache key is the set of factors that determine the measured speed. If any
 * one of them were left out, a cache hit could hand an agent a benchmark from a
 * different-speed run and the server would then size its chunks wrong. The key
 * is therefore built in exactly one place, here, and consists of:
 *
 *   - crackerBinaryId: the cracker binary the task runs with. Different binaries
 *     (and versions) have different speeds for the same attack.
 *   - hashTypeId: the hashcat hash-type (the '-m' mode). Speed varies by orders
 *     of magnitude between modes.
 *   - runSignature (stored in the attackParameters column): a SHA-256 of the
 *     whole normalized attack command, the preprocessor settings (id and
 *     command), the forced-pipe flag, and the hashlist's salt count. Measurement
 *     shows the specific mask, wordlist and rule set each move the benchmark by
 *     several orders of magnitude (a candidate-starved run is far slower than a
 *     saturating one), so the whole command has to be in the key, not just the
 *     attack mode. The salt count is added separately because it changes the
 *     speed (roughly linearly) but is not part of the command: two hashlists of
 *     the same mode running the same attack, one with many salts, benchmark very
 *     differently. The salt count is the hashlist's hashCount for a salted mode
 *     and 1 for an unsalted one, so unsalted modes still share one benchmark
 *     across their hashlists.
 *   - deviceSignature: a SHA-256 of the agent's reported device string, its
 *     cpuOnly flag and its per-agent command parameters (cmdPars, e.g. -O / -w
 *     tuning). This stands in for "identical hardware, identically configured".
 *   - benchmarkType: 'speed' or 'run'. The two methods produce values in
 *     different formats and units, so a value from one must never be reused for
 *     the other.
 *
 * A TTL of 0 (or less) disables the cache entirely.
 */
class BenchmarkUtils {
  /**
   * Configured cache TTL in seconds. 0 or less disables caching.
   */
  public static function getTtl(): int {
    return intval(SConfig::getInstance()->getVal(DConfig::BENCHMARK_CACHE_TTL));
  }

  /**
   * The benchmark method the given task uses, matching what GetTaskAction sends
   * to the agent as the benchmark type.
   */
  public static function benchmarkTypeForTask(Task $task): string {
    return ($task->getUseNewBench() == 1) ? PValuesBenchmarkType::SPEED_TEST : PValuesBenchmarkType::RUN_TIME;
  }

  /**
   * Resolve the hashlist a task runs against with a single Task -> TaskWrapper ->
   * Hashlist join, instead of fetching the wrapper and then the hashlist in two
   * separate queries.
   */
  public static function hashlistForTask(Task $task): ?Hashlist {
    $qF = new QueryFilter(Task::TASK_ID, $task->getId(), '=', Factory::getTaskFactory());
    $jF1 = new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID, Factory::getTaskFactory());
    $jF2 = new JoinFilter(Factory::getHashlistFactory(), TaskWrapper::HASHLIST_ID, Hashlist::HASHLIST_ID, Factory::getTaskWrapperFactory());
    $joined = Factory::getTaskFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => [$jF1, $jF2]]);
    /** @var Hashlist[] $hashlists */
    $hashlists = $joined[Factory::getHashlistFactory()->getModelName()];
    return (count($hashlists) > 0) ? $hashlists[0] : null;
  }

  /**
   * Number of salts the run has to test each candidate against, which scales the
   * time and so the measured speed. A salted mode tests every candidate against
   * every salt, so its hashCount is the salt count; an unsalted mode always
   * counts as one.
   */
  private static function saltCount(Hashlist $hashlist): int {
    return $hashlist->getIsSalted() ? max(1, (int)$hashlist->getHashCount()) : 1;
  }

  /**
   * Signature of everything about the run that changes the measured speed and is
   * not the hardware: the whole attack command (mask, wordlist, rule set, pipe),
   * the preprocessor settings, the forced-pipe flag and the salt count. Hashed so
   * it fits a fixed column and never leaks command contents into the cache table.
   */
  public static function computeRunSignature(Task $task, int $saltCount): string {
    $cmd = preg_replace('/\s+/', ' ', trim((string)$task->getAttackCmd()));
    $parts = [
      $cmd,
      (int)$task->getUsePreprocessor(),
      trim((string)$task->getPreprocessorCommand()),
      (int)$task->getForcePipe(),
      $saltCount,
    ];
    return hash('sha256', implode("\x1f", $parts));
  }

  /**
   * Signature of the agent's hardware and per-agent run configuration.
   */
  public static function computeDeviceSignature(Agent $agent): string {
    $normalizedDevices = preg_replace('/\s+/', ' ', trim((string)$agent->getDevices()));
    $parts = [
      $normalizedDevices,
      (int)$agent->getCpuOnly(),
      trim((string)$agent->getCmdPars()),
    ];
    return hash('sha256', implode("\x1f", $parts));
  }

  /**
   * The QueryFilters that identify one cache key. Shared by lookup and store so
   * they can never drift apart.
   *
   * @return QueryFilter[]
   */
  private static function keyFilters(Task $task, Hashlist $hashlist, Agent $agent, string $benchmarkType): array {
    return [
      new QueryFilter(Benchmark::CRACKER_BINARY_ID, $task->getCrackerBinaryId(), '='),
      new QueryFilter(Benchmark::HASH_TYPE_ID, (int)$hashlist->getHashTypeId(), '='),
      new QueryFilter(Benchmark::ATTACK_PARAMETERS, self::computeRunSignature($task, self::saltCount($hashlist)), '='),
      new QueryFilter(Benchmark::DEVICE_SIGNATURE, self::computeDeviceSignature($agent), '='),
      new QueryFilter(Benchmark::BENCHMARK_TYPE, $benchmarkType, '='),
    ];
  }

  /**
   * Return a cached benchmark value for this task/agent combination, or null if
   * caching is disabled or there is no unexpired entry.
   */
  public static function lookup(Task $task, Hashlist $hashlist, Agent $agent): ?string {
    if (self::getTtl() <= 0) {
      return null;
    }

    $filters = self::keyFilters($task, $hashlist, $agent, self::benchmarkTypeForTask($task));
    $filters[] = new QueryFilter(Benchmark::EXPIRE_TIME, time(), '>');
    $oF = new OrderFilter(Benchmark::CREATE_TIME, 'DESC');
    /** @var Benchmark[] $entries */
    $entries = Factory::getBenchmarkFactory()->filter([Factory::FILTER => $filters, Factory::ORDER => $oF]);
    if (count($entries) == 0) {
      return null;
    }
    DServerLog::log(DServerLog::DEBUG, 'Benchmark cache hit', [$agent, $task, $entries[0]->getBenchmarkValue()]);
    return $entries[0]->getBenchmarkValue();
  }

  /**
   * Store a benchmark result for reuse. Replaces any existing entry with the
   * same key so the table keeps one row per key. Does nothing when caching is
   * disabled.
   */
  public static function store(Task $task, Hashlist $hashlist, Agent $agent, string $benchmarkValue): void {
    $ttl = self::getTtl();
    if ($ttl <= 0) {
      return;
    }

    self::prune();

    $benchmarkType = self::benchmarkTypeForTask($task);
    $filters = self::keyFilters($task, $hashlist, $agent, $benchmarkType);
    Factory::getBenchmarkFactory()->massDeletion([Factory::FILTER => $filters]);

    $now = time();
    $benchmark = new Benchmark(
      null,
      $task->getCrackerBinaryId(),
      (int)$hashlist->getHashTypeId(),
      self::computeRunSignature($task, self::saltCount($hashlist)),
      self::computeDeviceSignature($agent),
      $benchmarkType,
      $benchmarkValue,
      $now,
      $now + $ttl
    );
    // The massDeletion above clears the key first, so only a concurrent store
    // for the same key can collide with the unique index; treat that as a no-op.
    try {
      Factory::getBenchmarkFactory()->save($benchmark);
    }
    catch (\PDOException $e) {
      DServerLog::log(DServerLog::DEBUG, 'Benchmark already stored concurrently', [$agent, $task]);
      return;
    }
    DServerLog::log(DServerLog::DEBUG, 'Stored benchmark in cache', [$agent, $task, $benchmarkValue]);
  }

  /**
   * Remove expired cache entries.
   */
  public static function prune(): void {
    Factory::getBenchmarkFactory()->massDeletion([Factory::FILTER => [
      new QueryFilter(Benchmark::EXPIRE_TIME, time(), '<='),
    ]]);
  }
}
