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
use PDOException;

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
 *     command) and the forced-pipe flag. Measurement shows the specific mask,
 *     wordlist and rule set each move the benchmark by orders of magnitude (the
 *     attack decides how much of each keyspace step runs in-kernel, so the
 *     keyspace-rate the server sizes chunks with is not comparable between
 *     commands), so the whole command has to be in the key, not just the attack
 *     mode.
 *   - deviceSignature: a SHA-256 of the agent's reported device string, its
 *     cpuOnly flag and its per-agent command parameters (cmdPars, e.g. -O / -w
 *     tuning). This stands in for "identical hardware, identically configured".
 *   - benchmarkType: 'speed' or 'run'. The two methods produce values in
 *     different formats and units, so a value from one must never be reused for
 *     the other.
 *
 * The salt count also changes the speed (every candidate is tested against every
 * salt), but it is a property of the hashlist, not the command, so it is NOT in
 * the key. Keying on it would store one entry per salt count; instead the value is
 * normalized to a single salt on store and rescaled to the hashlist's salt count
 * on lookup, so one entry serves any salt count of the same command and hardware.
 * See toSingleSalt and fromSingleSalt.
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
   * carried by the attack command: the whole command (mask, wordlist, rule set,
   * pipe), the preprocessor settings and the forced-pipe flag. Hashed so it fits a
   * fixed column and never leaks command contents into the cache table.
   *
   * The salt count is deliberately NOT part of the signature. It changes the speed
   * too but is a property of the hashlist, not the command, so instead of keying
   * on it (which would store one entry per salt count) the value is normalized to a
   * single salt on store and rescaled to the hashlist's salt count on lookup, so
   * one entry serves any salt count. See toSingleSalt and fromSingleSalt.
   */
  public static function computeRunSignature(Task $task): string {
    $cmd = preg_replace('/\s+/', ' ', trim((string)$task->getAttackCmd()));
    $parts = [
      $cmd,
      (int)$task->getUsePreprocessor(),
      trim((string)$task->getPreprocessorCommand()),
      (int)$task->getForcePipe(),
    ];
    return hash('sha256', implode("\x1f", $parts));
  }

  /**
   * Normalize a benchmark value to its single-salt equivalent for storage, so one
   * cache entry can serve a hashlist of any salt count. The benchmark scales
   * inversely with the salt count (every candidate is tested against every salt),
   * so the single-salt value is the salt-independent one to store.
   *
   * The speed value is "keyspaceCount:timeMs" and its rate is count/time, so the
   * single-salt time is time/saltCount. The run value is a scalar proportional to
   * progress, i.e. to 1/saltCount, so the single-salt value is value*saltCount.
   */
  private static function toSingleSalt(string $value, int $saltCount): string {
    if ($saltCount <= 1) {
      return $value;
    }
    if (str_contains($value, ':')) {
      [$count, $timeMs] = explode(':', $value, 2);
      return $count . ':' . self::formatNumber((float)$timeMs / $saltCount);
    }
    return self::formatNumber((float)$value * $saltCount);
  }

  /**
   * Expand a stored single-salt benchmark back to the given salt count, the
   * inverse of toSingleSalt.
   */
  private static function fromSingleSalt(string $value, int $saltCount): string {
    if ($saltCount <= 1) {
      return $value;
    }
    if (str_contains($value, ':')) {
      [$count, $timeMs] = explode(':', $value, 2);
      return $count . ':' . self::formatNumber((float)$timeMs * $saltCount);
    }
    return self::formatNumber((float)$value / $saltCount);
  }

  /**
   * Whether a benchmark value can size a chunk: both parts of a speed value
   * "count:time" strictly positive, or a run scalar strictly positive.
   * Normalizing across a very large salt count can round a time down to zero,
   * which the chunker rejects as a zero-sized chunk, so such a value has to be
   * treated as a cache miss rather than stored or returned.
   */
  private static function isUsableValue(string $value): bool {
    if (str_contains($value, ':')) {
      $parts = explode(':', $value);
      return count($parts) == 2 && is_numeric($parts[0]) && is_numeric($parts[1])
        && (float)$parts[0] > 0 && (float)$parts[1] > 0;
    }
    return is_numeric($value) && (float)$value > 0;
  }

  /**
   * Format a rescaled benchmark number as a plain decimal that fits the value
   * column, with no scientific notation or trailing zeros. A small positive value
   * keeps more decimals so it does not collapse to zero.
   */
  private static function formatNumber(float $value): string {
    $decimals = ($value != 0.0 && abs($value) < 1.0) ? 15 : 6;
    $s = rtrim(rtrim(sprintf('%.' . $decimals . 'f', $value), '0'), '.');
    return ($s === '' || $s === '-0') ? '0' : $s;
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
      new QueryFilter(Benchmark::ATTACK_PARAMETERS, self::computeRunSignature($task), '='),
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
    // The stored value is normalized to a single salt; rescale it to this
    // hashlist's salt count before handing it back.
    $value = self::fromSingleSalt($entries[0]->getBenchmarkValue(), self::saltCount($hashlist));
    if (!self::isUsableValue($value)) {
      // Rescaling collapsed the value (e.g. a tiny time under a huge salt count),
      // so do not hand back a benchmark that would size a zero chunk; benchmark.
      return null;
    }
    DServerLog::log(DServerLog::DEBUG, 'Benchmark cache hit', [$agent, $task, $value]);
    return $value;
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

    $storedValue = self::toSingleSalt($benchmarkValue, self::saltCount($hashlist));
    if (!self::isUsableValue($storedValue)) {
      // Normalization collapsed the value (e.g. a tiny time under a huge salt
      // count); skip caching rather than store one that rescales to a zero chunk.
      return;
    }

    self::prune();

    // Key the entry on the reported value's own format, not the task's configured
    // mode: the speed value is "count:time" and the run value is a bare scalar. If
    // an agent reports a value in the wrong format for the task, it is stored under
    // its own type, so a lookup for the task's type never serves a wrong-format
    // value to a later agent (which would size its chunks wrong).
    $benchmarkType = str_contains($storedValue, ':') ? PValuesBenchmarkType::SPEED_TEST : PValuesBenchmarkType::RUN_TIME;
    $filters = self::keyFilters($task, $hashlist, $agent, $benchmarkType);
    Factory::getBenchmarkFactory()->massDeletion([Factory::FILTER => $filters]);

    $now = time();
    $benchmark = new Benchmark(
      null,
      $task->getCrackerBinaryId(),
      (int)$hashlist->getHashTypeId(),
      self::computeRunSignature($task),
      self::computeDeviceSignature($agent),
      $benchmarkType,
      $storedValue,
      $now,
      $now + $ttl
    );
    // The massDeletion above clears the key first, so only a duplicate on the
    // unique lookup key is the expected concurrent-store race (SQLSTATE 23000 on
    // MySQL, 23505 on PostgreSQL); surface any other database error.
    try {
      Factory::getBenchmarkFactory()->save($benchmark);
    }
    catch (PDOException $e) {
      if (in_array($e->getCode(), ['23000', '23505'], true)) {
        DServerLog::log(DServerLog::DEBUG, 'Benchmark already stored concurrently', [$agent, $task]);
        return;
      }
      throw $e;
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
