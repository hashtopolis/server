<?php

declare(strict_types=1);

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\AgentError;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\models\BrokenTask;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\DataSet;
use Hashtopolis\inc\defines\DAgentIgnoreErrors;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DNotificationType;
use Hashtopolis\inc\defines\DPayloadKeys;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\handlers\NotificationHandler;
use Hashtopolis\inc\SConfig;

/**
 * Agent error handling (issue #884).
 *
 * A single broken task (a bad hashcat command, a syntax error) used to
 * deactivate every agent that picked it up, one by one, until the whole fleet
 * was offline. This util separates the two failure modes:
 *
 *   - the TASK is broken: several DISTINCT agents fail on the same task, so the
 *     task is marked broken (see BrokenTask) and the reporting agents stay active;
 *   - the AGENT is broken: one agent fails across several distinct tasks, so the
 *     agent itself is deactivated (a locked file, a broken GPU, ...).
 *
 * Hashtopolis is built for the untrusted-node case (a cracking contest), so a
 * single node failing is treated as a driver or hardware problem on that node,
 * not as proof the task is bad. Only once brokenTaskThreshold DISTINCT agents
 * have failed on one task is the task considered broken. That count is
 * configurable.
 *
 * All three thresholds are configurable. Setting brokenTaskThreshold to 0 and
 * brokenAgentThreshold to 1 reproduces the historical "deactivate the agent on
 * its first error" behaviour.
 */
class AgentErrorUtils {
  /**
   * Record a client error and decide what, if anything, to deactivate: the task
   * (marked broken) or the agent, keeping the fleet online where possible.
   */
  public static function handleClientError(Agent $agent, Task $task, ?int $chunkId, string $message): void {
    // Persist the error unless the agent is explicitly configured to not store.
    if ($agent->getIgnoreErrors() <= DAgentIgnoreErrors::IGNORE_SAVE) {
      $error = new AgentError(null, $agent->getId(), $task->getId(), $chunkId, time(), $message);
      Factory::getAgentErrorFactory()->save($error);

      $payload = new DataSet([DPayloadKeys::AGENT => $agent, DPayloadKeys::AGENT_ERROR => $message]);
      NotificationHandler::checkNotifications(DNotificationType::AGENT_ERROR, $payload);
      NotificationHandler::checkNotifications(DNotificationType::OWN_AGENT_ERROR, $payload);
    }

    // Agents explicitly told to ignore errors are never deactivated and never
    // mark a task broken: keep the historical behaviour for that override.
    if ($agent->getIgnoreErrors() != DAgentIgnoreErrors::NO) {
      return;
    }

    $window = intval(SConfig::getInstance()->getVal(DConfig::BROKEN_ERROR_WINDOW));
    $since = ($window > 0) ? time() - $window : 0;

    // Task fault: several DISTINCT agents failing on one task points at the task
    // rather than the hardware, since independent untrusted nodes are unlikely
    // to hit the same driver fault. Mark it broken and keep the agent active.
    // A task some agent has made progress on is excluded: a node that is working
    // it proves the command runs, so the failures are agent faults, not a task
    // fault (two nodes sharing a driver bug must not condemn a working task).
    // Note the flip side: once any chunk has progress the task can no longer be
    // auto-faulted, only the per-agent path still fires. That is deliberate, a
    // task that ran once is presumed valid.
    //
    // The threshold is capped at the number of agents ELIGIBLE for this task, not
    // the whole fleet: a task reachable by only a subset of agents (access group,
    // trust, a secret hashlist or file) could otherwise never reach a threshold
    // larger than that subset, and the eligible agents would loop on it forever.
    // Capping at the eligible count means a task only its single agent can run
    // faults on that agent's first distinct failure. The cap is recomputed per
    // error, so the configured value applies again once enough eligible agents
    // are active.
    $taskThreshold = intval(SConfig::getInstance()->getVal(DConfig::BROKEN_TASK_THRESHOLD));
    if ($taskThreshold > 0) {
      $effectiveThreshold = min($taskThreshold, self::countEligibleAgents($task));
      if (self::countDistinctAgentsForTask($task->getId(), $since) >= $effectiveThreshold
          && !self::taskHasProgress($task->getId())) {
        self::markTaskBroken($task, 'Marked broken automatically after ' . $effectiveThreshold . ' or more distinct agents failed');
        self::unassignAgentFromTask($agent, $task);
        return;
      }
    }

    // Agent fault: the agent fails across several distinct tasks, so the agent
    // itself is the problem and gets deactivated.
    $agentThreshold = intval(SConfig::getInstance()->getVal(DConfig::BROKEN_AGENT_THRESHOLD));
    if ($agentThreshold > 0 && self::countDistinctTasksForAgent($agent->getId(), $since) >= $agentThreshold) {
      Factory::getAgentFactory()->set($agent, Agent::IS_ACTIVE, 0);
      return;
    }

    // Neither threshold reached yet: drop this agent's assignment to the task so
    // it moves on to other work, but keep it active. This replaces the old
    // behaviour of deactivating the agent on its first error.
    self::unassignAgentFromTask($agent, $task);
  }

  /**
   * Mark a task as broken so it is no longer handed out until an admin clears
   * it. A task can carry at most one BrokenTask entry.
   */
  public static function markTaskBroken(Task $task, string $reason): void {
    if (self::isTaskBroken($task->getId())) {
      return;
    }
    $broken = new BrokenTask(null, $task->getId(), time(), $reason);
    try {
      Factory::getBrokenTaskFactory()->save($broken);
    } catch (\Exception $e) {
      // A concurrent error on the same task can insert the row between the check
      // above and this save; the UNIQUE(taskId) constraint then rejects the
      // duplicate. The task is broken either way, so swallow the race instead of
      // surfacing a 500 to the reporting agent.
      if (self::isTaskBroken($task->getId())) {
        return;
      }
      throw $e;
    }
    DServerLog::log(DServerLog::WARNING, 'Task ' . $task->getId() . ' marked broken: ' . $reason, [$task]);
  }

  /**
   * Whether the task is currently flagged broken (and therefore not assignable).
   * Reads the database, so it is impure: a concurrent request can insert or
   * remove the BrokenTask row between two calls (see the race guard in
   * markTaskBroken).
   *
   * @phpstan-impure
   */
  public static function isTaskBroken(int $taskId): bool {
    $qF = new QueryFilter(BrokenTask::TASK_ID, $taskId, '=');
    return count(Factory::getBrokenTaskFactory()->filter([Factory::FILTER => $qF])) > 0;
  }

  /**
   * Clear the broken flag of a task so it becomes assignable again.
   */
  public static function clearBrokenTask(int $taskId): void {
    $qF = new QueryFilter(BrokenTask::TASK_ID, $taskId, '=');
    Factory::getBrokenTaskFactory()->massDeletion([Factory::FILTER => $qF]);
  }

  /**
   * Number of distinct agents that have reported errors on a task, optionally
   * only those newer than the given unix time (0 counts all). Distinct agents,
   * not raw error count, because one untrusted node failing repeatedly is a
   * node problem, not proof the task is broken.
   */
  private static function countDistinctAgentsForTask(int $taskId, int $since): int {
    $filters = [new QueryFilter(AgentError::TASK_ID, $taskId, '=')];
    if ($since > 0) {
      $filters[] = new QueryFilter(AgentError::TIME, $since, '>=');
    }
    $errors = Factory::getAgentErrorFactory()->filter([Factory::FILTER => $filters]);
    $agents = [];
    foreach ($errors as $error) {
      $agents[$error->getAgentId()] = true;
    }
    return count($agents);
  }

  /**
   * Whether any agent has made measurable progress on the task. A chunk with a
   * non-zero progress means the command ran and produced work, so the reported
   * errors are agent faults rather than a broken task.
   */
  private static function taskHasProgress(int $taskId): bool {
    $qF1 = new QueryFilter(Chunk::TASK_ID, $taskId, '=');
    $qF2 = new QueryFilter(Chunk::PROGRESS, 0, '>');
    return Factory::getChunkFactory()->countFilter([Factory::FILTER => [$qF1, $qF2]]) > 0;
  }

  /**
   * How many active agents are eligible to run this task, that is could actually
   * be assigned it (same access group, trusted enough for any secret hashlist or
   * file). Used to cap the broken-task threshold so a task is never held to a
   * threshold higher than the number of agents that can ever work it, which would
   * otherwise let the eligible agents loop on it forever.
   */
  private static function countEligibleAgents(Task $task): int {
    $qF = new QueryFilter(Agent::IS_ACTIVE, 1, '=');
    $agents = Factory::getAgentFactory()->filter([Factory::FILTER => $qF]);
    $count = 0;
    foreach ($agents as $agent) {
      if (AccessUtils::agentCanAccessTask($agent, $task)) {
        $count++;
      }
    }
    return $count;
  }

  /**
   * Number of distinct tasks an agent has reported errors on, optionally only
   * those newer than the given unix time (0 counts all).
   */
  private static function countDistinctTasksForAgent(int $agentId, int $since): int {
    $filters = [new QueryFilter(AgentError::AGENT_ID, $agentId, '=')];
    if ($since > 0) {
      $filters[] = new QueryFilter(AgentError::TIME, $since, '>=');
    }
    $errors = Factory::getAgentErrorFactory()->filter([Factory::FILTER => $filters]);
    $tasks = [];
    foreach ($errors as $error) {
      $tasks[$error->getTaskId()] = true;
    }
    return count($tasks);
  }

  /**
   * Remove the agent's assignment to the task so its next request picks up
   * different work, without deactivating the agent.
   */
  private static function unassignAgentFromTask(Agent $agent, Task $task): void {
    $qF1 = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), '=');
    $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), '=');
    Factory::getAssignmentFactory()->massDeletion([Factory::FILTER => [$qF1, $qF2]]);
  }
}
