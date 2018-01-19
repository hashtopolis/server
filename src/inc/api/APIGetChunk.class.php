<?php

use DBA\Assignment;
use DBA\Chunk;
use DBA\ComparisonFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Task;

class APIGetChunk extends APIBasic {
  public function execute($QUERY = array()) {
    /** @var DataSet $CONFIG */
    global $FACTORIES, $CONFIG;
    
    if (!PQueryGetChunk::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::GET_CHUNK, "Invalid chunk query!");
    }
    $this->checkToken(PActions::GET_CHUNK, $QUERY);
    $this->updateAgent(PActions::GET_CHUNK);
    
    $task = $FACTORIES::getTaskFactory()->get($QUERY[PQueryGetChunk::TASK_ID]);
    if ($task == null) {
      $this->sendErrorResponse(PActions::GET_CHUNK, "Invalid task ID!");
    }
    
    $qF1 = new QueryFilter(Assignment::AGENT_ID, $this->agent->getId(), "=");
    $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
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
    else if ($assignment->getBenchmark() == 0 && $task->getIsSmall() == 0) { // benchmark only required on non-small tasks
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
    
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
    $task = TaskUtils::checkTask($task);
    if ($task == null) { // agent needs a new task
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
        $this->sendErrorResponse(PActions::GET_CHUNK, "Not allowed to work on this task!");
      }
    }
    
    // if the best task is not the one we are working on, we should switch
    $bestTask = TaskUtils::getImportantTask($bestTask, $task);
    if ($bestTask->getId() != $task->getId()) {
      $this->sendErrorResponse(PActions::GET_CHUNK, "Task with higher priority available!");
    }
    
    // find a chunk to assign
    $qF1 = new QueryFilter(Chunk::PROGRESS, 10000, "<");
    $qF2 = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $oF = new OrderFilter(Chunk::SKIP, "ASC");
    $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2), $FACTORIES::ORDER => $oF));
    foreach ($chunks as $chunk) {
      if ($chunk->getAgentId() == $this->agent->getId()) {
        $this->handleExistingChunk($chunk, $task, $assignment);
      }
      $timeoutTime = time() - $CONFIG->getVal(DConfig::CHUNK_TIMEOUT);
      if ($chunk->getState() == DHashcatStatus::ABORTED || $chunk->getState() == DHashcatStatus::STATUS_ABORTED_RUNTIME || max($chunk->getDispatchTime(), $chunk->getSolveTime()) < $timeoutTime) {
        $this->handleExistingChunk($chunk, $task, $assignment);
      }
    }
    $this->createNewChunk($task, $assignment);
  }
  
  /**
   * @param $task Task
   * @param $assignment Assignment
   */
  protected function createNewChunk($task, $assignment) {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;
    
    $disptolerance = 1 + $CONFIG->getVal(DConfig::DISP_TOLERANCE) / 100;
    
    // if we have set a skip keyspace we set the the current progress to the skip which was set initially
    if ($task->getSkipKeyspace() > $task->getKeyspaceProgress()) {
      $task->setKeyspaceProgress($task->getSkipKeyspace());
      $FACTORIES::getTaskFactory()->update($task);
    }
    
    $remaining = $task->getKeyspace() - $task->getKeyspaceProgress();
    if ($remaining == 0) {
      $this->sendResponse(array(
          PResponseGetChunk::ACTION => PActions::GET_CHUNK,
          PResponseGetChunk::RESPONSE => PValues::SUCCESS,
          PResponseGetChunk::CHUNK_STATUS => PValuesChunkType::FULLY_DISPATCHED
        )
      );
    }
    $agentChunkSize = $this->calculateChunkSize($task->getKeyspace(), $assignment->getBenchmark(), $task->getChunkTime(), 1);
    $start = $task->getKeyspaceProgress();
    $length = $agentChunkSize;
    if ($remaining / $length <= $disptolerance) {
      $length = $remaining;
    }
    $newProgress = $task->getKeyspaceProgress() + $length;
    $task->setKeyspaceProgress($newProgress);
    $FACTORIES::getTaskFactory()->update($task);
    $chunk = new Chunk(0, $task->getId(), $start, $length, $this->agent->getId(), time(), 0, $start, 0, DHashcatStatus::INIT, 0, 0);
    $FACTORIES::getChunkFactory()->save($chunk);
    $this->sendChunk($chunk);
  }
  
  /**
   * @param $chunk Chunk
   */
  protected function sendChunk($chunk) {
    global $FACTORIES;
    
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
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
  
  /**
   * @param $chunk Chunk
   * @param $task Task
   * @param $assignment Assignment
   */
  protected function handleExistingChunk($chunk, $task, $assignment) {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;
    
    $disptolerance = 1 + $CONFIG->getVal(DConfig::DISP_TOLERANCE) / 100;
    
    $agentChunkSize = $this->calculateChunkSize($task->getKeyspace(), $assignment->getBenchmark(), $task->getChunkTime(), 1);
    $agentChunkSizeMax = $this->calculateChunkSize($task->getKeyspace(), $assignment->getBenchmark(), $task->getChunkTime(), $disptolerance);
    if ($chunk->getCheckpoint() == $chunk->getSkip() && $agentChunkSizeMax > $chunk->getLength()) {
      //chunk has not started yet
      $chunk->setProgress(0);
      $chunk->setDispatchTime(time());
      $chunk->setSolveTime(0);
      $chunk->setState(DHashcatStatus::INIT);
      $chunk->setAgentId($this->agent->getId());
      $FACTORIES::getChunkFactory()->update($chunk);
      $this->sendChunk($chunk);
    }
    else if ($chunk->getCheckpoint() == $chunk->getSkip()) {
      //split chunk into two parts
      $originalLength = $chunk->getLength();
      $firstPart = $chunk;
      $firstPart->setLength($agentChunkSize);
      $firstPart->setAgentId($this->agent->getId());
      $firstPart->setDispatchTime(time());
      $firstPart->setSolveTime(0);
      $firstPart->setState(DHashcatStatus::INIT);
      $firstPart->setProgress(0);
      $FACTORIES::getChunkFactory()->update($firstPart);
      $secondPart = new Chunk(0, $task->getId(), $firstPart->getSkip() + $firstPart->getLength(), $originalLength - $firstPart->getLength(), null, 0, 0, $firstPart->getSkip() + $firstPart->getLength(), 0, DHashcatStatus::INIT, 0, 0);
      $FACTORIES::getChunkFactory()->save($secondPart);
      $this->sendChunk($firstPart);
    }
    else {
      if ($chunk->getLength() + $chunk->getSkip() - $chunk->getCheckpoint() == 0) {
        // special case when chunk length gets 0
        $this->createNewChunk($task, $assignment);
        return;
      }
      $newChunk = new Chunk(0, $task->getId(), $chunk->getCheckpoint(), $chunk->getLength() + $chunk->getSkip() - $chunk->getCheckpoint(), $this->agent->getId(), time(), 0, $chunk->getCheckpoint(), DHashcatStatus::INIT, 0, 0, 0);
      $chunk->setLength($chunk->getCheckpoint() - $chunk->getSkip());
      $chunk->setProgress(10000);
      $chunk->setState(DHashcatStatus::ABORTED_CHECKPOINT);
      $FACTORIES::getChunkFactory()->update($chunk);
      $newChunk = $FACTORIES::getChunkFactory()->save($newChunk);
      $this->sendChunk($newChunk);
    }
  }
  
  protected function calculateChunkSize($keyspace, $benchmark, $chunkTime, $tolerance = 1) {
    /** @var DataSet $CONFIG */
    global $CONFIG, $QUERY;
    
    if ($chunkTime <= 0) {
      $chunkTime = $CONFIG->getVal(DConfig::CHUNK_DURATION);
    }
    if (strpos($benchmark, ":") === false) {
      // old benchmarking method
      if (strlen($benchmark) == 0) {
        // special case on small tasks, so we just create a chunk with the size of the keyspace
        return $keyspace;
      }
      
      $size = floor($keyspace * $benchmark * $chunkTime / 100);
    }
    else {
      // new benchmarking method
      $benchmark = explode(":", $benchmark);
      if (sizeof($benchmark) != 2 || $benchmark[0] <= 0 || $benchmark[1] <= 0) {
        return 0;
      }
      
      $benchmark[1] *= 2 / 3;
      
      $factor = $chunkTime * 1000 / $benchmark[1];
      if ($factor <= 0.25) {
        $benchmark[0] /= 4;
      }
      else if ($factor <= 0.5) {
        $benchmark[0] /= 2;
      }
      else {
        $factor = floor($factor);
      }
      if ($factor == 0) {
        $factor = 1;
      }
      $size = $benchmark[0] * $factor;
    }
    
    $chunkSize = $size * $tolerance;
    if ($chunkSize <= 0) {
      $chunkSize = 1;
      Util::createLogEntry("API", $QUERY[PQuery::TOKEN], DLogEntry::WARN, "Calculated chunk size was 0 on benchmark $benchmark!");
    }
    
    return $chunkSize;
  }
}