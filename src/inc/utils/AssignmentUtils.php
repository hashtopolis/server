<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\HTException;

class AssignmentUtils {
  
  /**
   * @param int $assignmentId
   * @param string $benchmark
   * @param User $user
   * @throws HTException
   * @throws \Exception
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
    Factory::getAssignmentFactory()->set($assignment, Assignment::BENCHMARK, $benchmark);
  }
}