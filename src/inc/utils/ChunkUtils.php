<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DHashcatStatus;
use Hashtopolis\inc\defines\DLogEntry;
use Hashtopolis\inc\defines\DPrince;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\defines\DTaskStaticChunking;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\Util;

class ChunkUtils {
  // chunkSpeed is stored scaled by SPEED_SCALE (milli-base-words/second) so the EWMA accumulator keeps
  // sub-unit precision; without scaling an integer accumulator cannot climb when a step is < 0.5/s
  // (the observed "integer dead-zone" that froze slow-hash sizing at the seed).
  const SPEED_SCALE = 1000;
  const RECONCILE_ALPHA = 0.6;          // weight on the newest completed-chunk measurement
  const RECONCILE_MAX_UP_RATIO = 2.0;   // bound a single upward jump against a freak fast chunk (1.6x after blending)
  const RECONCILE_MIN_DURATION = 5;     // seconds; ignore chunks too short to measure a stable rate

  /**
   * Observed BASE-WORD processing rate of a COMPLETED chunk, scaled by SPEED_SCALE.
   *
   * Rate = chunk length (base words) / wall-clock duration (solveTime - dispatchTime). This is the
   * unit-correct, multiplier-agnostic sizing signal (chunk length and task keyspace are both in base
   * words, unlike the raw hashcat hash-rate which is base-words/s x rules x salts), and it is measured
   * over the WHOLE chunk rather than a single inter-report delta, so it reflects the stable sustained
   * rate instead of the per-report jitter that made the old per-report reconcile oscillate.
   *
   * Returns null when there is nothing usable to measure (chunk too short, or no forward length).
   */
  public static function completedChunkSpeed(int $chunkLength, int $dispatchTime, int $solveTime): ?int {
    if ($chunkLength <= 0) { return null; }
    $duration = $solveTime - $dispatchTime;
    if ($duration < self::RECONCILE_MIN_DURATION) { return null; }
    return intval($chunkLength * self::SPEED_SCALE / $duration);
  }

  /**
   * Reconcile the stored (scaled) chunkSpeed toward a new observed (scaled) rate. Climb-only: the speed
   * never decreases. That respects the long-standing maintainer rule against automatic chunk shrinking
   * (hashtopolis/server#729) and, combined with the stable whole-chunk signal, removes the oscillation:
   * a single upward jump is bounded to RECONCILE_MAX_UP_RATIO and the EWMA damps anomalous chunks.
   */
  public static function reconcileSpeed(int $old, int $observed): int {
    if ($observed <= 0) { return $old; }
    if ($old <= 0) { return $observed; }      // first real measurement seeds directly
    if ($observed <= $old) { return $old; }   // climb-only: never shrink
    $upper = (int) floor($old * self::RECONCILE_MAX_UP_RATIO);
    $target = ($observed > $upper) ? $upper : $observed;
    $blended = (1.0 - self::RECONCILE_ALPHA) * $old + self::RECONCILE_ALPHA * $target;
    return (int) max(1, round($blended));
  }

  // Convert a stored benchmark value to a canonical chunkSpeed (scaled by SPEED_SCALE). Used to seed
  // chunkSpeed from a benchmark submission and from manual benchmark overrides. The scale is applied
  // before flooring so a sub-unit seed (e.g. ~4/s on a slow hash) is not truncated away. Null on
  // unparseable input.
  public static function benchmarkToChunkSpeed($benchmark, $keyspace): ?int {
    if ($benchmark === null || $benchmark === "") { return null; }
    if (strpos((string)$benchmark, ":") !== false) {
      $split = explode(":", (string)$benchmark);
      if (sizeof($split) != 2 || !is_numeric($split[0]) || !is_numeric($split[1]) || $split[0] <= 0 || $split[1] <= 0) { return null; }
      // keyspace-per-ms -> base words/s, scaled: (split0 / split1) * 1000 * SPEED_SCALE
      return (int) floor($split[0] * 1000 * self::SPEED_SCALE / $split[1]);
    }
    if (!is_numeric($benchmark) || $benchmark <= 0) { return null; }
    if ($keyspace === null || $keyspace <= 0) { return null; }
    return (int) floor($benchmark * $keyspace * self::SPEED_SCALE / 100);
  }

  /**
   * @param $chunk Chunk
   * @param $task Task
   * @param $assignment Assignment
   * @return Chunk|null
   * @throws HTException
   * @throws Exception
   */
  public static function handleExistingChunk(Chunk $chunk, Task $task, Assignment $assignment): ?Chunk {
    $disptolerance = 1 + SConfig::getInstance()->getVal(DConfig::DISP_TOLERANCE) / 100;
    
    DServerLog::log(DServerLog::TRACE, "Handling existing chunk...", [$task, $chunk, $assignment]);
    $initialProgress = ($task->getUsePreprocessor() || $task->getForcePipe()) ? null : 0;
    
    $agentChunkSize = ChunkUtils::calculateChunkSize($task->getKeyspace(), intval($assignment->getChunkSpeed()), $task->getChunkTime(), 1, $task->getStaticChunks(), $task->getChunkSize(), $assignment->getAgentId());
    $agentChunkSizeMax = ChunkUtils::calculateChunkSize($task->getKeyspace(), intval($assignment->getChunkSpeed()), $task->getChunkTime(), $disptolerance, $task->getStaticChunks(), $task->getChunkSize(), $assignment->getAgentId());
    if (($chunk->getCheckpoint() == $chunk->getSkip() || SConfig::getInstance()->getVal(DConfig::DISABLE_TRIMMING)) && $agentChunkSizeMax >= $chunk->getLength()) {
      //chunk has not started yet
      DServerLog::log(DServerLog::TRACE, "Chunk did not start yet and is small enough to give it to agent", [$task, $chunk, $assignment]);
      return Factory::getChunkFactory()->mset($chunk, [
          Chunk::PROGRESS => $initialProgress,
          Chunk::DISPATCH_TIME => time(),
          Chunk::SOLVE_TIME => 0,
          Chunk::STATE => DHashcatStatus::INIT,
          Chunk::AGENT_ID => $assignment->getAgentId(),
          Chunk::SPEED => 0
        ]
      );
    }
    else if ($chunk->getCheckpoint() == $chunk->getSkip() || SConfig::getInstance()->getVal(DConfig::DISABLE_TRIMMING)) {
      //split chunk into two parts
      DServerLog::log(DServerLog::TRACE, "Chunk has not started, but needs to be split", [$task, $chunk, $assignment]);
      $originalLength = $chunk->getLength();
      $firstPart = $chunk;
      $firstPart = Factory::getChunkFactory()->mset($firstPart, [
          Chunk::LENGTH => $agentChunkSize,
          Chunk::AGENT_ID => $assignment->getAgentId(),
          Chunk::DISPATCH_TIME => time(),
          Chunk::SOLVE_TIME => 0,
          Chunk::STATE => DHashcatStatus::INIT,
          Chunk::PROGRESS => $initialProgress,
          Chunk::SPEED => 0
        ]
      );
      $secondPart = new Chunk(null, $task->getId(), $firstPart->getSkip() + $firstPart->getLength(), $originalLength - $firstPart->getLength(), null, 0, 0, $firstPart->getSkip() + $firstPart->getLength(), $initialProgress, DHashcatStatus::INIT, 0, 0);
      $secondPart = Factory::getChunkFactory()->save($secondPart);
      DServerLog::log(DServerLog::TRACE, "Splitting done, resulting in two chunks", [$task, $assignment, $firstPart, $secondPart]);
      return $firstPart;
    }
    else {
      DServerLog::log(DServerLog::TRACE, "Chunk was started and reached a checkpoint", [$task, $chunk, $assignment]);
      if ($chunk->getLength() + $chunk->getSkip() - $chunk->getCheckpoint() == 0) {
        // special case when remaining chunk length gets 0
        $chunk = Factory::getChunkFactory()->mset($chunk, [
            Chunk::PROGRESS => 10000,
            Chunk::STATE => DHashcatStatus::ABORTED_CHECKPOINT,
            Chunk::SPEED => 0
          ]
        );
        DServerLog::log(DServerLog::TRACE, "Remaining part is 0 for some reason, finished chunk", [$task, $chunk]);
        return ChunkUtils::createNewChunk($task, $assignment);
      }
      $newChunk = new Chunk(
        null,
        $task->getId(),
        $chunk->getCheckpoint(),
        $chunk->getLength() + $chunk->getSkip() - $chunk->getCheckpoint(),
        $assignment->getAgentId(),
        time(),
        0,
        $chunk->getCheckpoint(),
        $initialProgress,
        DHashcatStatus::INIT,
        0,
        0
      );
      $newChunk = Factory::getChunkFactory()->save($newChunk);
      $chunk = Factory::getChunkFactory()->mset($chunk, [
          Chunk::LENGTH => $chunk->getCheckpoint() - $chunk->getSkip(),
          Chunk::PROGRESS => 10000,
          Chunk::STATE => DHashcatStatus::ABORTED_CHECKPOINT,
          Chunk::SPEED => 0
        ]
      );
      DServerLog::log(DServerLog::TRACE, "Trimmed chunk and created new one of the remaining part", [$task, $chunk, $newChunk, $assignment]);
      return $newChunk;
    }
  }
  
  /**
   * @param Task $task
   * @param Assignment $assignment
   * @return Chunk|null
   * @throws HTException
   * @throws Exception
   */
  public static function createNewChunk(Task $task, Assignment $assignment): ?Chunk {
    $disptolerance = 1 + SConfig::getInstance()->getVal(DConfig::DISP_TOLERANCE) / 100;
    
    // if we have set a skip keyspace we set the the current progress to the skip which was set initially
    if ($task->getSkipKeyspace() > $task->getKeyspaceProgress()) {
      $task = Factory::getTaskFactory()->set($task, Task::KEYSPACE_PROGRESS, $task->getSkipKeyspace());
    }
    
    $remaining = $task->getKeyspace() - $task->getKeyspaceProgress();
    if ($remaining == 0 && $task->getKeyspace() != DPrince::PRINCE_KEYSPACE) {
      return null;
    }
    $agentChunkSize = ChunkUtils::calculateChunkSize($task->getKeyspace(), intval($assignment->getChunkSpeed()), $task->getChunkTime(), 1, $task->getStaticChunks(), $task->getChunkSize(), $assignment->getAgentId());
    $start = $task->getKeyspaceProgress();
    $length = $agentChunkSize;
    if ($remaining / $length <= $disptolerance && $task->getKeyspace() != DPrince::PRINCE_KEYSPACE) {
      $length = $remaining;
    }
    Factory::getTaskFactory()->inc($task, Task::KEYSPACE_PROGRESS, $length);
    $initialProgress = ($task->getUsePreprocessor() || $task->getForcePipe()) ? null : 0;
    $chunk = new Chunk(null, $task->getId(), $start, $length, $assignment->getAgentId(), time(), 0, $start, $initialProgress, DHashcatStatus::INIT, 0, 0);
    $chunk = Factory::getChunkFactory()->save($chunk);
    DServerLog::log(DServerLog::TRACE, "Created new chunk for task", [$task, $chunk, $assignment]);
    return $chunk;
  }
  
  /**
   * @param int $keyspace
   * @param int $chunkSpeed
   * @param int $chunkTime
   * @param float $tolerance
   * @param int $staticChunking
   * @param int $chunkSize
   * @param int|null $agentId
   * @return int
   * @throws HTException
   * @throws Exception
   */
  public static function calculateChunkSize(int $keyspace, int $chunkSpeed, int $chunkTime, float $tolerance = 1.0, int $staticChunking = DTaskStaticChunking::NORMAL, int $chunkSize = 0, ?int $agentId = 0): int {
    if ($chunkTime <= 0) {
      $chunkTime = SConfig::getInstance()->getVal(DConfig::CHUNK_DURATION);
    }
    else if ($staticChunking > DTaskStaticChunking::NORMAL) {   // KEEP this else-if gate EXACTLY as-is
      switch ($staticChunking) {
        case DTaskStaticChunking::CHUNK_SIZE:
          if ($chunkSize == 0) { throw new HTException("Invalid chunk size for static chunk size set!"); }
          return $chunkSize;
        case DTaskStaticChunking::NUM_CHUNKS:
          if ($chunkSize == 0) {
            throw new HTException("Invalid number of static chunks set!");
          }
          else if ($chunkSize > 10000) { // just protection to avoid millions or whatever chunk number
            throw new HTException("Too large number of static chunks, most likely because of misconfiguration!");
          }
          return intval(ceil($keyspace / $chunkSize));
        default:
          throw new HTException("Unknown static chunking method!");
      }
    }
    // No usable observed speed yet (fresh assignment / small task / bootstrap) -> one chunk of whole keyspace.
    // Subsumes the legacy "benchmark == 0 => return keyspace" case. PRINCE guard: PRINCE_KEYSPACE is a
    // negative sentinel; never return it as a size.
    if ($chunkSpeed <= 0) {
      return ($keyspace > 0) ? $keyspace : 1;
    }
    // chunkSpeed is scaled by SPEED_SCALE (milli-base-words/s); divide it back out here.
    $size = floor($chunkSpeed * $chunkTime / self::SPEED_SCALE);
    $chunkSize = $size * $tolerance;
    if ($chunkSize <= 0) {
      $chunkSize = 1;
      DServerLog::log(DServerLog::WARNING, "Chunk size 0!", [$keyspace, $chunkSpeed, $chunkTime]);
      Util::createLogEntry("API", $agentId, DLogEntry::WARN, "Calculated chunk size was 0 on chunkSpeed $chunkSpeed!");
    }
    return intval($chunkSize);
  }
}