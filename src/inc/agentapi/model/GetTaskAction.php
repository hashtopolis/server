<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Exception;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\models\File;
use Hashtopolis\dba\models\FileTask;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agent\PResponseGetTask;
use Hashtopolis\inc\agent\PValues;
use Hashtopolis\inc\agent\PValuesTask;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentResponseTrait;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\utils\HealthUtils;
use Hashtopolis\inc\utils\TaskUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``getTask`` action.
 *
 * Returns the task the agent should work on.  The selection logic:
 * 1. If a health check is pending for this agent → ``taskId: -1``.
 * 2. If the agent is inactive → ``taskId: null, reason: "Agent is inactive!"``.
 * 3. Find the best task via ``TaskUtils::getBestTask``; if none is available,
 *    check the current assignment; if that is also fulfilled or saturated →
 *    ``taskId: null, reason: "No suitable task available!"``.
 * 4. If a task is found, create or update the assignment and return the full
 *    task descriptor (attack command, hashlist, cracker, brain config, etc.).
 */
final class GetTaskAction implements AgentAction {
    use AgentResponseTrait;

    public function __invoke(Request $request, Response $response): ResponseInterface {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        if (!isset($body[PQuery::TOKEN])) {
            return $this->error($response, PActions::GET_TASK, 'Invalid task query!');
        }

        /** @var Agent $agent */
        $agent = $request->getAttribute(AgentAction::AGENT_ATTRIBUTE);
        $agent = $this->updateAgent($agent, PActions::GET_TASK);

        DServerLog::log(DServerLog::TRACE, 'Requesting a task...', [$agent]);

        if (HealthUtils::checkNeeded($agent)) {
            DServerLog::log(DServerLog::INFO, 'Notified about pending health check', [$agent]);
            return $this->success($response, PActions::GET_TASK, [
                PResponseGetTask::TASK_ID => PValuesTask::HEALTH_CHECK,
            ]);
        }

        if ($agent->getIsActive() == 0) {
            DServerLog::log(DServerLog::TRACE, 'Agent is inactive and cannot get a task', [$agent]);
            return $this->success($response, PActions::GET_TASK, [
                PResponseGetTask::TASK_ID => PValues::NONE,
                PResponseGetTask::REASON  => 'Agent is inactive!',
            ]);
        }

        DServerLog::log(DServerLog::TRACE, 'Searching for assignment and best task', [$agent]);
        $qF = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), '=');
        $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF], true);
        $task = TaskUtils::getBestTask($agent);
        DServerLog::log(DServerLog::TRACE, 'Search results', [$agent, $assignment, $task]);

        if ($task === null) {
            if ($assignment === null) {
                return $this->noTask($response);
            }
            $currentTask = Factory::getTaskFactory()->get($assignment->getTaskId());
            $currentTask = TaskUtils::checkTask($currentTask);
            if ($currentTask === null) {
                DServerLog::log(DServerLog::TRACE, 'No best task available and current assigned task is fullfilled', [$agent]);
                Factory::getAssignmentFactory()->delete($assignment);
                return $this->noTask($response);
            }
            if (TaskUtils::isSaturatedByOtherAgents($currentTask, $agent)) {
                Factory::getAssignmentFactory()->delete($assignment);
                return $this->noTask($response);
            }
            DServerLog::log(DServerLog::TRACE, 'Current task is running, continue with this', [$agent, $currentTask]);
            return $this->sendTask($response, $agent, $currentTask, $assignment);
        }

        if ($assignment !== null) {
            $currentTask = Factory::getTaskFactory()->get($assignment->getTaskId());
            if ($currentTask === null) {
                DServerLog::log(DServerLog::TRACE, 'Current task does not exist anymore, send new one', [$agent, $task]);
                return $this->sendTask($response, $agent, $task, $assignment);
            }
            $currentTask = TaskUtils::checkTask($currentTask);
            if ($currentTask === null) {
                DServerLog::log(DServerLog::TRACE, 'Current task is done or permissions changed, send new one', [$agent, $task]);
                return $this->sendTask($response, $agent, $task, $assignment);
            }
            if (TaskUtils::isSaturatedByOtherAgents($currentTask, $agent)) {
                return $this->sendTask($response, $agent, $task, $assignment);
            }
            DServerLog::log(DServerLog::TRACE, 'Current task is fine, send the more important one', [$agent, $currentTask, $task]);
            return $this->sendTask($response, $agent, TaskUtils::getImportantTask($task, $currentTask), $assignment);
        }

        DServerLog::log(DServerLog::TRACE, 'Task available, but nothing assigned, send new one', [$agent, $task]);
        return $this->sendTask($response, $agent, $task, $assignment);
    }

    private function noTask(Response $response): ResponseInterface {
        return $this->success($response, PActions::GET_TASK, [
            PResponseGetTask::TASK_ID => PValues::NONE,
            PResponseGetTask::REASON  => 'No suitable task available!',
        ]);
    }
  
  /**
   * @throws Exception
   */
  private function sendTask(Response $response, Agent $agent, Task $task, ?Assignment $assignment): ResponseInterface {
        if ($assignment === null) {
            $assignment = new Assignment(null, $task->getId(), $agent->getId(), '0');
            $assignment = Factory::getAssignmentFactory()->save($assignment);
            DServerLog::log(DServerLog::TRACE, 'No assignment present, created', [$agent, $assignment]);
        }
        else {
            if ($assignment->getTaskId() != $task->getId()) {
                $qF = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), '=');
                Factory::getAssignmentFactory()->massDeletion([Factory::FILTER => $qF]);
                DServerLog::log(DServerLog::TRACE, 'Current task does not match assignment, delete it', [$agent, $assignment]);
                $assignment = new Assignment(null, $task->getId(), $agent->getId(), '0');
                $assignment = Factory::getAssignmentFactory()->save($assignment);
                DServerLog::log(DServerLog::TRACE, 'Created new assignment', [$agent, $assignment]);
            }
        }

        $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
        if ($taskWrapper === null) {
            DServerLog::log(DServerLog::FATAL, 'Inconsistency between taskWrapper and task', [$agent, $task]);
            return $this->error($response, PActions::GET_TASK, 'Inconsistent TaskWrapper information!');
        }
        $hashlist = Factory::getHashlistFactory()->get($taskWrapper->getHashlistId());
        if ($hashlist === null) {
            DServerLog::log(DServerLog::TRACE, 'Inconsistency between taskWrapper and hashlist', [$agent, $taskWrapper]);
            return $this->error($response, PActions::GET_TASK, 'Inconsistent TaskWrapper-Hashlist information');
        }

        $taskFiles = [];
        $qF = new QueryFilter(FileTask::TASK_ID, $task->getId(), '=', Factory::getFileTaskFactory());
        $jF = new JoinFilter(Factory::getFileTaskFactory(), File::FILE_ID, FileTask::FILE_ID);
        $joined = Factory::getFileFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
        /** @var File[] $files */
        $files = $joined[Factory::getFileFactory()->getModelName()];
        foreach ($files as $file) {
            $taskFiles[] = $file->getFilename();
        }

        $hashtype = Factory::getHashTypeFactory()->get($hashlist->getHashTypeId());

        DServerLog::log(DServerLog::TRACE, 'Sending task to agent', [$agent, $task, $taskFiles]);

        $brain = $hashlist->getBrainId() && !$task->getForcePipe() && !$task->getUsePreprocessor();

        $fields = [
            PResponseGetTask::TASK_ID              => (int)$task->getId(),
            PResponseGetTask::ATTACK_COMMAND       => $task->getAttackCmd(),
            PResponseGetTask::CMD_PARAMETERS       => ' --hash-type=' . $hashlist->getHashTypeId() . ' ' . $agent->getCmdPars(),
            PResponseGetTask::HASHLIST_ID          => (int)$taskWrapper->getHashlistId(),
            PResponseGetTask::BENCHMARK            => (int)SConfig::getInstance()->getVal(DConfig::BENCHMARK_TIME),
            PResponseGetTask::STATUS_TIMER         => (int)$task->getStatusTimer(),
            PResponseGetTask::FILES                => $taskFiles,
            PResponseGetTask::CRACKER_ID           => $task->getCrackerBinaryId(),
            PResponseGetTask::BENCHTYPE            => ($task->getUseNewBench() == 1) ? 'speed' : 'run',
            PResponseGetTask::HASHLIST_ALIAS       => SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS),
            PResponseGetTask::KEYSPACE             => $task->getKeyspace(),
            PResponseGetTask::USE_PREPROCESSOR     => (bool)$task->getUsePreprocessor(),
            PResponseGetTask::PREPROCESSOR         => $task->getUsePreprocessor(),
            PResponseGetTask::PREPROCESSOR_COMMAND => $task->getPreprocessorCommand(),
            PResponseGetTask::ENFORCE_PIPE         => (bool)$task->getForcePipe(),
            PResponseGetTask::SLOW_HASH            => (bool)$hashtype->getIsSlowHash(),
            PResponseGetTask::USE_BRAIN            => $brain,
        ];

        if ($brain) {
            $fields[PResponseGetTask::BRAIN_HOST] = SConfig::getInstance()->getVal(DConfig::HASHCAT_BRAIN_HOST);
            $fields[PResponseGetTask::BRAIN_PORT] = intval(SConfig::getInstance()->getVal(DConfig::HASHCAT_BRAIN_PORT));
            $fields[PResponseGetTask::BRAIN_PASS] = SConfig::getInstance()->getVal(DConfig::HASHCAT_BRAIN_PASS);
            $fields[PResponseGetTask::BRAIN_FEATURES] = (int)$hashlist->getBrainFeatures();
        }

        return $this->success($response, PActions::GET_TASK, $fields);
    }
}
