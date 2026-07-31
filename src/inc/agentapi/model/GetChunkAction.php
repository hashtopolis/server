<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Exception;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\OrderFilter;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agent\PQueryGetChunk;
use Hashtopolis\inc\agent\PResponseGetChunk;
use Hashtopolis\inc\agent\PValuesChunkType;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentResponseTrait;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DHashcatStatus;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\defines\DTaskStaticChunking;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\inc\utils\ChunkUtils;
use Hashtopolis\inc\utils\HealthUtils;
use Hashtopolis\inc\utils\Lock;
use Hashtopolis\inc\utils\LockUtils;
use Hashtopolis\inc\utils\TaskUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``getChunk`` action.
 *
 * Core chunk-dispatch logic.  Returns one of several ``status`` values:
 * ``health_check``, ``keyspace_required``, ``benchmark``, ``fully_dispatched``,
 * or ``OK`` (with chunkId, skip, length).  Uses a file lock to ensure only one
 * agent at a time creates chunks for a given task.
 */
final class GetChunkAction implements AgentAction {
    use AgentResponseTrait;

    public function __invoke(Request $request, Response $response): ResponseInterface {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        if (!isset($body[PQuery::TOKEN]) || !isset($body[PQueryGetChunk::TASK_ID])) {
            return $this->error($response, PActions::GET_CHUNK, 'Invalid chunk query!');
        }

        /** @var Agent $agent */
        $agent = $request->getAttribute(AgentAction::AGENT_ATTRIBUTE);
        $agent = $this->updateAgent($agent, PActions::GET_CHUNK);

        DServerLog::log(DServerLog::DEBUG, 'Requesting a chunk...', [$agent]);

        if (HealthUtils::checkNeeded($agent)) {
            DServerLog::log(DServerLog::DEBUG, 'Notifying agent about health check', [$agent]);
            return $this->success($response, PActions::GET_CHUNK, [
                PResponseGetChunk::CHUNK_STATUS => PValuesChunkType::HEALTH_CHECK,
            ]);
        }

        $task = Factory::getTaskFactory()->get($body[PQueryGetChunk::TASK_ID]);
        if ($task === null) {
            DServerLog::log(DServerLog::WARNING, 'Requested chunk on invalid task!', [$agent]);
            return $this->error($response, PActions::GET_CHUNK, 'Invalid task ID!');
        }

        $qF1 = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), '=');
        $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), '=');
        $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
        if ($assignment === null) {
            DServerLog::log(DServerLog::WARNING, 'Requested chunk on task it is not assigned to!', [$agent]);
            return $this->error($response, PActions::GET_CHUNK, 'You are not assigned to this task!');
        }

        if ($task->getKeyspace() == 0) {
            DServerLog::log(DServerLog::TRACE, 'Need to measure keyspace!', [$agent, $task]);
            return $this->success($response, PActions::GET_CHUNK, [
                PResponseGetChunk::CHUNK_STATUS => PValuesChunkType::KEYSPACE_REQUIRED,
            ]);
        }

        if ($assignment->getBenchmark() == 0 && $task->getIsSmall() == 0 && $task->getStaticChunks() == DTaskStaticChunking::NORMAL) {
            DServerLog::log(DServerLog::TRACE, 'Need to run a benchmark!', [$agent, $task]);
            return $this->success($response, PActions::GET_CHUNK, [
                PResponseGetChunk::CHUNK_STATUS => PValuesChunkType::BENCHMARK_REQUIRED,
            ]);
        }

        if ($agent->getIsActive() == 0) {
            DServerLog::log(DServerLog::TRACE, 'Agent is inactive!', [$agent]);
            return $this->error($response, PActions::GET_CHUNK, 'Agent is inactive!');
        }

        $lockFile = Lock::CHUNKING . $task->getId();
        LockUtils::get($lockFile);
        DServerLog::log(DServerLog::TRACE, 'Retrieved lock for chunking!', [$agent]);

        $task = Factory::getTaskFactory()->get($task->getId());
        Factory::getAgentFactory()->getDB()->beginTransaction();
        DServerLog::log(DServerLog::DEBUG, 'Checking task...', [$agent, $task]);
        $task = TaskUtils::checkTask($task, $agent);

        if ($task === null) {
            DServerLog::log(DServerLog::DEBUG, 'Task is fully dispatched', [$agent]);
            Factory::getAgentFactory()->getDB()->commit();
            LockUtils::release($lockFile);
            DServerLog::log(DServerLog::TRACE, 'Released lock for chunking!', [$agent]);
            return $this->success($response, PActions::GET_CHUNK, [
                PResponseGetChunk::CHUNK_STATUS => PValuesChunkType::FULLY_DISPATCHED,
            ]);
        }

        DServerLog::log(DServerLog::TRACE, 'Search for best task...', [$agent]);
        $bestTask = TaskUtils::getBestTask($agent);
        if ($bestTask === null) {
            DServerLog::log(DServerLog::TRACE, 'No best task available! (Probably because permissions changed)', [$agent]);
            if (!AccessUtils::agentCanAccessTask($agent, $task)) {
                Factory::getAgentFactory()->getDB()->commit();
                LockUtils::release($lockFile);
                DServerLog::log(DServerLog::INFO, 'Not allowed to work on requested task', [$agent, $task]);
                DServerLog::log(DServerLog::TRACE, 'Released lock for chunking!', [$agent]);
                return $this->error($response, PActions::GET_CHUNK, 'Not allowed to work on this task!');
            }
            if (TaskUtils::isSaturatedByOtherAgents($task, $agent)) {
                Factory::getAgentFactory()->getDB()->commit();
                LockUtils::release($lockFile);
                DServerLog::log(DServerLog::TRACE, 'Released lock for chunking!', [$agent]);
                return $this->error($response, PActions::GET_CHUNK, 'Task already saturated by other agents, no other task available!');
            }
        }

        if (TaskUtils::isSaturatedByOtherAgents($task, $agent)) {
            Factory::getAgentFactory()->getDB()->commit();
            LockUtils::release($lockFile);
            DServerLog::log(DServerLog::TRACE, 'Released lock for chunking!', [$agent]);
            return $this->error($response, PActions::GET_CHUNK, 'Task already saturated by other agents, other tasks available!');
        }

        DServerLog::log(DServerLog::TRACE, 'Determine important task', [$agent, $task, $bestTask]);
        $bestTask = TaskUtils::getImportantTask($bestTask, $task);

        if ($bestTask->getId() != $task->getId()) {
            Factory::getAgentFactory()->getDB()->commit();
            DServerLog::log(DServerLog::INFO, 'Task with higher priority available!', [$agent]);
            LockUtils::release($lockFile);
            DServerLog::log(DServerLog::TRACE, 'Released lock for chunking!', [$agent]);
            return $this->error($response, PActions::GET_CHUNK, 'Task with higher priority available!');
        }

        DServerLog::log(DServerLog::DEBUG, 'Searching existing chunk...', [$agent, $task]);
        $qF1 = new QueryFilter(Chunk::PROGRESS, 10000, '<');
        $qF2 = new QueryFilter(Chunk::TASK_ID, $task->getId(), '=');
        $oF = new OrderFilter(Chunk::SKIP, 'ASC');
        $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => $oF]);
        $qF1 = new QueryFilter(Chunk::PROGRESS, null, '=');
        /** @var Chunk[] $chunks */
        $chunks = array_merge($chunks, Factory::getChunkFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => $oF]));

        foreach ($chunks as $chunk) {
            if ($chunk->getAgentId() == $agent->getId()) {
                DServerLog::log(DServerLog::DEBUG, 'Found chunk of same agent which is not done yet.', [$agent, $task, $chunk]);
                $result = $this->sendChunk($response, ChunkUtils::handleExistingChunk($chunk, $task, $assignment));
                if ($result !== null) {
                    return $result;
                }
            }
            $timeoutTime = time() - SConfig::getInstance()->getVal(DConfig::CHUNK_TIMEOUT);
            if ($chunk->getState() == DHashcatStatus::ABORTED || $chunk->getState() == DHashcatStatus::STATUS_ABORTED_RUNTIME || max($chunk->getDispatchTime(), $chunk->getSolveTime()) < $timeoutTime) {
                DServerLog::log(DServerLog::DEBUG, 'Found existing chunk which is not done yet', [$agent, $task, $chunk]);
                $result = $this->sendChunk($response, ChunkUtils::handleExistingChunk($chunk, $task, $assignment));
                if ($result !== null) {
                    return $result;
                }
            }
        }

        DServerLog::log(DServerLog::DEBUG, 'Create new chunk for agent', [$agent, $task]);
        $chunk = ChunkUtils::createNewChunk($task, $assignment);
        if ($chunk === null) {
            DServerLog::log(DServerLog::DEBUG, 'Could not create a chunk, task is fully dispatched', [$agent, $task]);
            Factory::getAgentFactory()->getDB()->commit();
            LockUtils::release($lockFile);
            DServerLog::log(DServerLog::TRACE, 'Released lock for chunking!', [$agent]);
            return $this->success($response, PActions::GET_CHUNK, [
                PResponseGetChunk::CHUNK_STATUS => PValuesChunkType::FULLY_DISPATCHED,
            ]);
        }

        DServerLog::log(DServerLog::DEBUG, 'Sending new chunk to agent', [$agent, $task, $chunk]);
        $result = $this->sendChunk($response, $chunk);
        return $result ?? $this->success($response, PActions::GET_CHUNK, [
            PResponseGetChunk::CHUNK_STATUS => PValuesChunkType::FULLY_DISPATCHED,
        ]);
    }
  
  /**
   * Commit the DB transaction, release the lock, and return the OK chunk
   * response.  Returns null if the chunk is null (caller should continue
   * to the next chunk or create a new one).
   * @throws Exception
   */
    private function sendChunk(Response $response, ?Chunk $chunk): ?ResponseInterface {
        if ($chunk === null) {
            return null;
        }
        Factory::getAgentFactory()->getDB()->commit();
        LockUtils::release(Lock::CHUNKING . $chunk->getTaskId());
        return $this->success($response, PActions::GET_CHUNK, [
            PResponseGetChunk::CHUNK_STATUS    => PValuesChunkType::OK,
            PResponseGetChunk::CHUNK_ID        => (int)$chunk->getId(),
            PResponseGetChunk::KEYSPACE_SKIP   => (int)$chunk->getSkip(),
            PResponseGetChunk::KEYSPACE_LENGTH => (int)$chunk->getLength(),
        ]);
    }
}
