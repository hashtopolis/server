<?php

use DBA\Assignment;
use DBA\Chunk;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Factory;

class APIGetChunk extends APIBasic {
  public function execute($QUERY = array()) {
    if (!PQueryGetChunk::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::GET_CHUNK, "Invalid chunk query!");
    }
    $this->checkToken(PActions::GET_CHUNK, $QUERY);
    $this->updateAgent(PActions::GET_CHUNK);

    if(HealthUtils::checkNeeded($this->agent)){
      $this->sendResponse(array(
          PResponseGetChunk::ACTION => PActions::GET_CHUNK,
          PResponseGetChunk::RESPONSE => PValues::SUCCESS,
          PResponseGetChunk::CHUNK_STATUS => PValuesChunkType::HEALTH_CHECK
        )
      );
    }

    $task = Factory::getTaskFactory()->get($QUERY[PQueryGetChunk::TASK_ID]);
    if ($task == null) {
      $this->sendErrorResponse(PActions::GET_CHUNK, "Invalid task ID!");
    }

    $qF1 = new QueryFilter(Assignment::AGENT_ID, $this->agent->getId(), "=");
    $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
    if ($assignment == null) {
      $this->sendErrorResponse(PActions::GET_CHUNK, "You are not assigned to this task!");
    }
    else if ($task->getKeyspace() == 0) {
      $this->sendResponse(array(
          PResponseGetChunk::ACTION => PActions::GET_CHUNK,
          PResponseGetChunk::RESPONSE => PValues::SUCCESS,
          PResponseGetChunk::CHUNK_STATUS => PValuesChunkType::KEYSPACE_REQUIRED
        )
      );
    }
    else if ($assignment->getBenchmark() == 0 && $task->getIsSmall() == 0 && $task->getStaticChunks() == DTaskStaticChunking::NORMAL) { // benchmark only required on non-small tasks and on non-special chunk tasks
      $this->sendResponse(array(
          PResponseGetChunk::ACTION => PActions::GET_CHUNK,
          PResponseGetChunk::RESPONSE => PValues::SUCCESS,
          PResponseGetChunk::CHUNK_STATUS => PValuesChunkType::BENCHMARK_REQUIRED
        )
      );
    }
    else if ($this->agent->getIsActive() == 0) {
      $this->sendErrorResponse(PActions::GET_CHUNK, "Agent is inactive!");
    }

    LockUtils::get(Lock::CHUNKING);
    $task = Factory::getTaskFactory()->get($task->getId());
    Factory::getAgentFactory()->getDB()->beginTransaction();
    $task = TaskUtils::checkTask($task, $this->agent);
    if ($task == null) { // agent needs a new task
      Factory::getAgentFactory()->getDB()->commit();
      LockUtils::release(Lock::CHUNKING);
      $this->sendResponse(array(
          PResponseGetChunk::ACTION => PActions::GET_CHUNK,
          PResponseGetChunk::RESPONSE => PValues::SUCCESS,
          PResponseGetChunk::CHUNK_STATUS => PValuesChunkType::FULLY_DISPATCHED
        )
      );
    }

    $bestTask = TaskUtils::getBestTask($this->agent);
    if ($bestTask == null) {
      // this is a special case where this task is either not allowed anymore, or it has priority 0 so it doesn't get auto assigned
      if (!AccessUtils::agentCanAccessTask($this->agent, $task)) {
        Factory::getAgentFactory()->getDB()->commit();
        LockUtils::release(Lock::CHUNKING);
        $this->sendErrorResponse(PActions::GET_CHUNK, "Not allowed to work on this task!");
      }
    }

    // if the best task is not the one we are working on, we should switch
    $bestTask = TaskUtils::getImportantTask($bestTask, $task);
    if ($bestTask->getId() != $task->getId()) {
      Factory::getAgentFactory()->getDB()->commit();
      LockUtils::release(Lock::CHUNKING);
      $this->sendErrorResponse(PActions::GET_CHUNK, "Task with higher priority available!");
    }

    // find a chunk to assign
    $qF1 = new QueryFilter(Chunk::PROGRESS, 10000, "<");
    $qF2 = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $oF = new OrderFilter(Chunk::SKIP, "ASC");
    $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => $oF]);
    foreach ($chunks as $chunk) {
      if ($chunk->getAgentId() == $this->agent->getId()) {
        $this->sendChunk(ChunkUtils::handleExistingChunk($chunk, $task, $assignment));
      }
      $timeoutTime = time() - SConfig::getInstance()->getVal(DConfig::CHUNK_TIMEOUT);
      if ($chunk->getState() == DHashcatStatus::ABORTED || $chunk->getState() == DHashcatStatus::STATUS_ABORTED_RUNTIME || max($chunk->getDispatchTime(), $chunk->getSolveTime()) < $timeoutTime) {
        $this->sendChunk(ChunkUtils::handleExistingChunk($chunk, $task, $assignment));
      }
    }
    $chunk = ChunkUtils::createNewChunk($task, $assignment);
    if($chunk == null){
      Factory::getAgentFactory()->getDB()->commit();
      LockUtils::release(Lock::CHUNKING);
      $this->sendResponse(array(
          PResponseGetChunk::ACTION => PActions::GET_CHUNK,
          PResponseGetChunk::RESPONSE => PValues::SUCCESS,
          PResponseGetChunk::CHUNK_STATUS => PValuesChunkType::FULLY_DISPATCHED
        )
      );
    }
    $this->sendChunk($chunk);
  }

  /**
   * @param $chunk Chunk
   */
  protected function sendChunk($chunk) {
    Factory::getAgentFactory()->getDB()->commit();
    LockUtils::release(Lock::CHUNKING);
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