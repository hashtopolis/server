<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\HTException;

class AssignmentUtils {
  
  /**
   * @param int $assignmentId
   * @param string $benchmark
   * @param User $user
   * @throws HTException
   * @throws Exception
   */
  public static function setBenchmark(int $assignmentId, string $benchmark, User $user): void {
    // adjust agent benchmark
    $assignment = Factory::getAssignmentFactory()->get($assignmentId);
    if ($assignment == null) {
      throw new HTException("No assignment found with this id to change benchmark of");
    }
    $agent = Factory::getAgentFactory()->get($assignment->getAgentId());
    if (!AccessUtils::userCanAccessAgent($agent, $user)) {
      throw new HTException("No access to this agent!");
    }
    // TODO: check benchmark validity
    // Keep the raw benchmark as a diagnostic, and also derive the canonical chunkSpeed (H/s) that
    // adaptive sizing consumes, so a manual override still steers chunk size.
    $task = Factory::getTaskFactory()->get($assignment->getTaskId());
    $keyspace = ($task != null) ? $task->getKeyspace() : null;
    $chunkSpeed = ChunkUtils::benchmarkToChunkSpeed($benchmark, $keyspace);
    if ($chunkSpeed === null) {
      DServerLog::log(DServerLog::WARNING, "Manual benchmark override could not be converted to a chunk speed; keeping previous chunkSpeed", [$assignment, $benchmark]);
      Factory::getAssignmentFactory()->set($assignment, Assignment::BENCHMARK, $benchmark);
    } else {
      Factory::getAssignmentFactory()->mset($assignment, [Assignment::BENCHMARK => $benchmark, Assignment::CHUNK_SPEED => $chunkSpeed]);
    }
  }
}