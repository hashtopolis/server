<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\HTException;

class AssignmentUtils {
  
  /**
   * @param string $benchmark
   * @param User $user
   * @throws HTException
   */
  public static function setBenchmark($assignmentId, $benchmark, $user) {
    // adjust agent benchmark
    $assignment = Factory::getAssignmentFactory()->get($assignmentId);
    $agent = Factory::getAgentFactory()->get($assignment->getAgentId());
    if ($assignment == null) {
      throw new HTException("No assignment found with this id to change benchmark of");
    }
    else if (!AccessUtils::userCanAccessAgent($agent, $user)) {
      throw new HTException("No access to this agent!");
    }
    // TODO: check benchmark validity
    Factory::getAssignmentFactory()->set($assignment, Assignment::BENCHMARK, $benchmark);
  }
}