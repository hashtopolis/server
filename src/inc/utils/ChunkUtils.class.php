<?php
use DBA\Chunk;
use DBA\Task;
use DBA\Assignment;
use DBA\Factory;

class ChunkUtils{
  /**
   * @param $chunk Chunk
   * @param $task Task
   * @param $assignment Assignment
   */
  public static function handleExistingChunk($chunk, $task, $assignment) {
    $disptolerance = 1 + SConfig::getInstance()->getVal(DConfig::DISP_TOLERANCE) / 100;

    $agentChunkSize = ChunkUtils::calculateChunkSize($task->getKeyspace(), $assignment->getBenchmark(), $task->getChunkTime(), 1, $task->getStaticChunks(), $task->getChunkSize());
    $agentChunkSizeMax = ChunkUtils::calculateChunkSize($task->getKeyspace(), $assignment->getBenchmark(), $task->getChunkTime(), $disptolerance, $task->getStaticChunks(), $task->getChunkSize());
    if (($chunk->getCheckpoint() == $chunk->getSkip() || SConfig::getInstance()->getVal(DConfig::DISABLE_TRIMMING)) && $agentChunkSizeMax >= $chunk->getLength()) {
      //chunk has not started yet
      $chunk->setProgress(0);
      $chunk->setDispatchTime(time());
      $chunk->setSolveTime(0);
      $chunk->setState(DHashcatStatus::INIT);
      $chunk->setAgentId($assignment->getAgentId());
      Factory::getChunkFactory()->update($chunk);
      return $chunk;
    }
    else if ($chunk->getCheckpoint() == $chunk->getSkip() || SConfig::getInstance()->getVal(DConfig::DISABLE_TRIMMING)) {
      //split chunk into two parts
      $originalLength = $chunk->getLength();
      $firstPart = $chunk;
      $firstPart->setLength($agentChunkSize);
      $firstPart->setAgentId($assignment->getAgentId());
      $firstPart->setDispatchTime(time());
      $firstPart->setSolveTime(0);
      $firstPart->setState(DHashcatStatus::INIT);
      $firstPart->setProgress(0);
      Factory::getChunkFactory()->update($firstPart);
      $secondPart = new Chunk(null, $task->getId(), $firstPart->getSkip() + $firstPart->getLength(), $originalLength - $firstPart->getLength(), null, 0, 0, $firstPart->getSkip() + $firstPart->getLength(), 0, DHashcatStatus::INIT, 0, 0);
      Factory::getChunkFactory()->save($secondPart);
      return $firstPart;
    }
    else {
      if ($chunk->getLength() + $chunk->getSkip() - $chunk->getCheckpoint() == 0) {
        // special case when remaining chunk length gets 0
        $chunk->setProgress(10000);
        $chunk->setState(DHashcatStatus::ABORTED_CHECKPOINT);
        Factory::getChunkFactory()->update($chunk);
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
        DHashcatStatus::INIT, 
        0, 
        0, 
        0
      );
      $chunk->setLength($chunk->getCheckpoint() - $chunk->getSkip());
      $chunk->setProgress(10000);
      $chunk->setState(DHashcatStatus::ABORTED_CHECKPOINT);
      Factory::getChunkFactory()->update($chunk);
      $newChunk = Factory::getChunkFactory()->save($newChunk);
      return $newChunk;
    }
  }

  /**
   * @param Task $task
   * @param Assignment $assignment
   * @return DBA\Chunk|null
   */
  public static function createNewChunk($task, $assignment){
    $disptolerance = 1 + SConfig::getInstance()->getVal(DConfig::DISP_TOLERANCE) / 100;

    // if we have set a skip keyspace we set the the current progress to the skip which was set initially
    if ($task->getSkipKeyspace() > $task->getKeyspaceProgress()) {
      $task->setKeyspaceProgress($task->getSkipKeyspace());
      Factory::getTaskFactory()->update($task);
    }

    $remaining = $task->getKeyspace() - $task->getKeyspaceProgress();
    if ($remaining == 0 && $task->getKeyspace() != DPrince::PRINCE_KEYSPACE) {
      return null;
    }
    $agentChunkSize = ChunkUtils::calculateChunkSize($task->getKeyspace(), $assignment->getBenchmark(), $task->getChunkTime(), 1, $task->getStaticChunks(), $task->getChunkSize());
    $start = $task->getKeyspaceProgress();
    $length = $agentChunkSize;
    if ($remaining / $length <= $disptolerance && $task->getKeyspace() != DPrince::PRINCE_KEYSPACE) {
      $length = $remaining;
    }
    $newProgress = $task->getKeyspaceProgress() + $length;
    $task->setKeyspaceProgress($newProgress);
    Factory::getTaskFactory()->update($task);
    $chunk = new Chunk(null, $task->getId(), $start, $length, $assignment->getAgentId(), time(), 0, $start, 0, DHashcatStatus::INIT, 0, 0);
    $chunk = Factory::getChunkFactory()->save($chunk);
    return $chunk;
  }

  /**
   * @param int $keyspace
   * @param string $benchmark
   * @param int $chunkTime
   * @param float $tolerance
   * @param int $staticChunking
   * @param int $chunkSize
   * @throws HTException
   * @return int
   */
  public static function calculateChunkSize($keyspace, $benchmark, $chunkTime, $tolerance = 1, $staticChunking = DTaskStaticChunking::NORMAL, $chunkSize = 0) {
    global $QUERY;

    if ($chunkTime <= 0) {
      $chunkTime = SConfig::getInstance()->getVal(DConfig::CHUNK_DURATION);
    }
    else if($staticChunking > DTaskStaticChunking::NORMAL){
      switch($staticChunking){
        case DTaskStaticChunking::CHUNK_SIZE:
          if($chunkSize == 0){
            throw new HTException("Invalid chunk size for static chunk size set!");
          }
          return $chunkSize;
        case DTaskStaticChunking::NUM_CHUNKS:
          if($chunkSize == 0){
            throw new HTException("Invalid number of static chunks set!");
          }
          else if($chunkSize > 10000){ // just protection to avoid millions or whatever chunk number
            throw new HTException("Too large number of static chunks, most likely because of misconfiguration!");
          }
          return ceil($keyspace/$chunkSize);
        default:
          throw new HTException("Unknown static chunking method!");
      }
    }

    if (strpos($benchmark, ":") === false) {
      // old benchmarking method
      if ($benchmark == 0) {
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

      // NEW VARIANT
      $factor = $chunkTime / $benchmark[1] * 1000;
      $size = floor($factor * $benchmark[0]);
    }

    $chunkSize = $size * $tolerance;
    if ($chunkSize <= 0) {
      $chunkSize = 1;
      if(is_array($benchmark)){
        $benchmark = implode(":", $benchmark);
      }
      Util::createLogEntry("API", $QUERY[PQuery::TOKEN], DLogEntry::WARN, "Calculated chunk size was 0 on benchmark $benchmark!");
    }

    return $chunkSize;
  }
}