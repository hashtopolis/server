<?php

use DBA\Assignment;
use DBA\Chunk;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Factory;

class APIGetChunk extends APIBasic {
  /**
   * @param array $QUERY
   * @throws HTException
   * @throws Exception
   */
  public function execute($QUERY = array()) {
    if (!PQueryGetChunk::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::GET_CHUNK, "Invalid chunk query!");
    }
    $this->checkToken(PActions::GET_CHUNK, $QUERY);
    $this->updateAgent(PActions::GET_CHUNK);
    
    DServerLog::log(DServerLog::DEBUG, "Requesting a chunk...", [$this->agent]);
    
    if (HealthUtils::checkNeeded($this->agent)) {
      DServerLog::log(DServerLog::DEBUG, "Notifying agent about health check", [$this->agent]);
      $this->sendResponse(array(
          PResponseGetChunk::ACTION => PActions::GET_CHUNK,
          PResponseGetChunk::RESPONSE => PValues::SUCCESS,
          PResponseGetChunk::CHUNK_STATUS => PValuesChunkType::HEALTH_CHECK
        )
      );
    }
    
    $task = Factory::getTaskFactory()->get($QUERY[PQueryGetChunk::TASK_ID]);
    if ($task == null) {
      DServerLog::log(DServerLog::WARNING, "Requested chunk on invalid task!", [$this->agent]);
      $this->sendErrorResponse(PActions::GET_CHUNK, "Invalid task ID!");
    }
    
    $qF1 = new QueryFilter(Assignment::AGENT_ID, $this->agent->getId(), "=");
    $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
    if ($assignment == null) {
      DServerLog::log(DServerLog::WARNING, "Requested chunk on task it is not assigned to!", [$this->agent]);
      $this->sendErrorResponse(PActions::GET_CHUNK, "You are not assigned to this task!");
    }
    else if ($task->getKeyspace() == 0) {
      DServerLog::log(DServerLog::TRACE, "Need to measure keyspace!", [$this->agent, $task]);
      $this->sendResponse(array(
          PResponseGetChunk::ACTION => PActions::GET_CHUNK,
          PResponseGetChunk::RESPONSE => PValues::SUCCESS,
          PResponseGetChunk::CHUNK_STATUS => PValuesChunkType::KEYSPACE_REQUIRED
        )
      );
    }
    else if ($assignment->getBenchmark() == 0 && $task->getIsSmall() == 0 && $task->getStaticChunks() == DTaskStaticChunking::NORMAL) { // benchmark only required on non-small tasks and on non-special chunk tasks
      DServerLog::log(DServerLog::TRACE, "Need to run a benchmark!", [$this->agent, $task]);
      $this->sendResponse(array(
          PResponseGetChunk::ACTION => PActions::GET_CHUNK,
          PResponseGetChunk::RESPONSE => PValues::SUCCESS,
          PResponseGetChunk::CHUNK_STATUS => PValuesChunkType::BENCHMARK_REQUIRED
        )
      );
    }
    else if ($this->agent->getIsActive() == 0) {
      DServerLog::log(DServerLog::TRACE, "Agent is inactive!", [$this->agent]);
      $this->sendErrorResponse(PActions::GET_CHUNK, "Agent is inactive!");
    }
    
    LockUtils::get(Lock::CHUNKING);
    DServerLog::log(DServerLog::TRACE, "Retrieved lock for chunking!", [$this->agent]);
    $task = Factory::getTaskFactory()->get($task->getId());
    Factory::getAgentFactory()->getDB()->beginTransaction();
    DServerLog::log(DServerLog::DEBUG, "Checking task...", [$this->agent, $task]);
    $task = TaskUtils::checkTask($task, $this->agent);
    if ($task == null) { // agent needs a new task
      DServerLog::log(DServerLog::DEBUG, "Task is fully dispatched", [$this->agent]);
      Factory::getAgentFactory()->getDB()->commit();
      LockUtils::release(Lock::CHUNKING);
      DServerLog::log(DServerLog::TRACE, "Released lock for chunking!", [$this->agent]);
      $this->sendResponse(array(
          PResponseGetChunk::ACTION => PActions::GET_CHUNK,
          PResponseGetChunk::RESPONSE => PValues::SUCCESS,
          PResponseGetChunk::CHUNK_STATUS => PValuesChunkType::FULLY_DISPATCHED
        )
      );
    }
    
    DServerLog::log(DServerLog::TRACE, "Search for best task...", [$this->agent]);
    $bestTask = TaskUtils::getBestTask($this->agent);
    if ($bestTask == null) {
      DServerLog::log(DServerLog::TRACE, "No best task available! (Probably because permissions changed)", [$this->agent]);
      // this is a special case where this task is either not allowed anymore, or it has priority 0 so it doesn't get auto assigned
      if (!AccessUtils::agentCanAccessTask($this->agent, $task)) {
        Factory::getAgentFactory()->getDB()->commit();
        LockUtils::release(Lock::CHUNKING);
        DServerLog::log(DServerLog::INFO, "Not allowed to work on requested task", [$this->agent, $task]);
        DServerLog::log(DServerLog::TRACE, "Released lock for chunking!", [$this->agent]);
        $this->sendErrorResponse(PActions::GET_CHUNK, "Not allowed to work on this task!");
      }
      if (TaskUtils::isSaturatedByOtherAgents($task, $this->agent)) {
        Factory::getAgentFactory()->getDB()->commit();
        LockUtils::release(Lock::CHUNKING);
        DServerLog::log(DServerLog::TRACE, "Released lock for chunking!", [$this->agent]);
        $this->sendErrorResponse(PActions::GET_CHUNK, "Task already saturated by other agents, no other task available!");
      }
    }

    if (TaskUtils::isSaturatedByOtherAgents($task, $this->agent)) {
      Factory::getAgentFactory()->getDB()->commit();
      LockUtils::release(Lock::CHUNKING);
      DServerLog::log(DServerLog::TRACE, "Released lock for chunking!", [$this->agent]);
      $this->sendErrorResponse(PActions::GET_CHUNK, "Task already saturated by other agents, other tasks available!");
    }
    else {
      DServerLog::log(DServerLog::TRACE, "Determine important task", [$this->agent, $task, $bestTask]);
      $bestTask = TaskUtils::getImportantTask($bestTask, $task);

      if ($bestTask->getId() != $task->getId()) {
        Factory::getAgentFactory()->getDB()->commit();
        DServerLog::log(DServerLog::INFO, "Task with higher priority available!", [$this->agent]);
        LockUtils::release(Lock::CHUNKING);
        DServerLog::log(DServerLog::TRACE, "Released lock for chunking!", [$this->agent]);
        $this->sendErrorResponse(PActions::GET_CHUNK, "Task with higher priority available!");
      }
    }
    
    // find a chunk to assign
    DServerLog::log(DServerLog::DEBUG, "Searching existing chunk...", [$this->agent, $task]);
    $qF1 = new QueryFilter(Chunk::PROGRESS, 10000, "<");
    $qF2 = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $oF = new OrderFilter(Chunk::SKIP, "ASC");
    $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => $oF]);
    $qF1 = new QueryFilter(Chunk::PROGRESS, null, "=");
    /** @var $chunks Chunk[] */
    $chunks = array_merge($chunks, Factory::getChunkFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => $oF]));
    foreach ($chunks as $chunk) {
      if ($chunk->getAgentId() == $this->agent->getId()) {
        DServerLog::log(DServerLog::DEBUG, "Found chunk of same agent which is not done yet.", [$this->agent, $task, $chunk]);
        $this->sendChunk(ChunkUtils::handleExistingChunk($chunk, $task, $assignment));
      }
      $timeoutTime = time() - SConfig::getInstance()->getVal(DConfig::CHUNK_TIMEOUT);
      if ($chunk->getState() == DHashcatStatus::ABORTED || $chunk->getState() == DHashcatStatus::STATUS_ABORTED_RUNTIME || max($chunk->getDispatchTime(), $chunk->getSolveTime()) < $timeoutTime) {
        DServerLog::log(DServerLog::DEBUG, "Found existing chunk which is not done yet", [$this->agent, $task, $chunk]);
        $this->sendChunk(ChunkUtils::handleExistingChunk($chunk, $task, $assignment));
      }
    }
    DServerLog::log(DServerLog::DEBUG, "Create new chunk for agent", [$this->agent, $task]);
    $chunk = ChunkUtils::createNewChunk($task, $assignment);
    if ($chunk == null) {
      DServerLog::log(DServerLog::DEBUG, "Could not create a chunk, task is fully dispatched", [$this->agent, $task]);
      Factory::getAgentFactory()->getDB()->commit();
      LockUtils::release(Lock::CHUNKING);
      DServerLog::log(DServerLog::TRACE, "Released lock for chunking!", [$this->agent]);
      $this->sendResponse(array(
          PResponseGetChunk::ACTION => PActions::GET_CHUNK,
          PResponseGetChunk::RESPONSE => PValues::SUCCESS,
          PResponseGetChunk::CHUNK_STATUS => PValuesChunkType::FULLY_DISPATCHED
        )
      );
    }
    DServerLog::log(DServerLog::DEBUG, "Sending new chunk to agent", [$this->agent, $task, $chunk]);
    $this->sendChunk($chunk);
  }
  
  /**
   * @param $chunk Chunk
   */
  protected function sendChunk($chunk) {
    if ($chunk == null) {
      return; // this can be safely done before the commit/release, because the only sendChunk which comes really at the end check for null before, so a lock which is not released cannot happen
    }
    Factory::getAgentFactory()->getDB()->commit();
    LockUtils::release(Lock::CHUNKING);
    DServerLog::log(DServerLog::TRACE, "Released lock for chunking!", [$this->agent]);
    $this->sendResponse(array(
        PResponseGetChunk::ACTION => PActions::GET_CHUNK,
        PResponseGetChunk::RESPONSE => PValues::SUCCESS,
        PResponseGetChunk::CHUNK_STATUS => PValuesChunkType::OK,
        PResponseGetChunk::CHUNK_ID => (int)($chunk->getId()),
        PResponseGetChunk::KEYSPACE_SKIP => (int)($chunk->getSkip()),
        PResponseGetChunk::KEYSPACE_LENGTH => (int)($chunk->getLength())
      )
    );
  }
}