<?php

declare(strict_types=1);

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Benchmark;
use Hashtopolis\dba\models\Task;
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
 *   - hashMode: the hashcat hash-type (the '-m' mode). Speed varies by orders of
 *     magnitude between modes.
 *   - attackParameters: a SHA-256 of the normalized attack command together with
 *     the task settings that change how the run executes (preprocessor use and
 *     command, forced pipe). The attack command already contains the wordlist,
 *     rule and mask references, which are what actually drive the speed.
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
   * Signature of the attack. The benchmark measures raw candidate speed, which
   * depends on the attack mode (the '-a' value: straight, combinator, mask, ...)
   * and on whether a rule file is applied ('-r'), but not on which wordlist,
   * mask or rule file is used. So the key is just those two factors, and every
   * mask of a given mode shares one benchmark instead of being measured
   * separately. Hashed so it fits a fixed column and never leaks command
   * contents into the cache table.
   */
  public static function computeAttackSignature(Task $task): string {
    $cmd = (string)$task->getAttackCmd();
    $attackMode = preg_match('/(?:^|\s)-a\s*(\d+)/', $cmd, $m) ? $m[1] : '';
    $hasRules = preg_match('/(?:^|\s)(?:-r|--rules-file)(?:=|\s)/', $cmd) ? 1 : 0;
    $parts = [$attackMode, (string)$hasRules];
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
  private static function keyFilters(Task $task, int $hashMode, Agent $agent, string $benchmarkType): array {
    return [
      new QueryFilter(Benchmark::CRACKER_BINARY_ID, $task->getCrackerBinaryId(), '='),
      new QueryFilter(Benchmark::HASH_MODE, $hashMode, '='),
      new QueryFilter(Benchmark::ATTACK_PARAMETERS, self::computeAttackSignature($task), '='),
      new QueryFilter(Benchmark::DEVICE_SIGNATURE, self::computeDeviceSignature($agent), '='),
      new QueryFilter(Benchmark::BENCHMARK_TYPE, $benchmarkType, '='),
    ];
  }

  /**
   * Return a cached benchmark value for this task/agent combination, or null if
   * caching is disabled or there is no unexpired entry.
   */
  public static function lookup(Task $task, int $hashMode, Agent $agent): ?string {
    if (self::getTtl() <= 0) {
      return null;
    }

    $filters = self::keyFilters($task, $hashMode, $agent, self::benchmarkTypeForTask($task));
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
  public static function store(Task $task, int $hashMode, Agent $agent, string $benchmarkValue): void {
    $ttl = self::getTtl();
    if ($ttl <= 0) {
      return;
    }

    self::prune();

    $benchmarkType = self::benchmarkTypeForTask($task);
    $filters = self::keyFilters($task, $hashMode, $agent, $benchmarkType);
    Factory::getBenchmarkFactory()->massDeletion([Factory::FILTER => $filters]);

    $now = time();
    $benchmark = new Benchmark(
      null,
      $task->getCrackerBinaryId(),
      $hashMode,
      self::computeAttackSignature($task),
      self::computeDeviceSignature($agent),
      $benchmarkType,
      $benchmarkValue,
      $now,
      $now + $ttl
    );
    Factory::getBenchmarkFactory()->save($benchmark);
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
