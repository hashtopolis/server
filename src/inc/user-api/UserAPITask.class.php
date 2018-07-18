<?php

class UserAPITask extends UserAPIBasic {
  public function execute($QUERY = array()) {
    global $FACTORIES;

    try {
      switch($QUERY[UQuery::REQUEST]){
        case USectionTask::LIST_TASKS:
          $this->listTasks($QUERY);
          break;
        case USectionTask::GET_TASK:
          $this->getTask($QUERY);
          break;
        case USectionTask::LIST_SUBTASKS:
          $this->listSubtasks($QUERY);
          break;
        case USectionTask::LIST_PRETASKS:
          $this->listPreTasks($QUERY);
          break;
        case USectionTask::GET_PRETASK:
          $this->getPretask($QUERY);
          break;
        case USectionTask::LIST_SUPERTASKS:
          $this->listSupertasks($QUERY);
          break;
        case USectionTask::GET_SUPERTASK:
          $this->getSupertask($QUERY);
          break;
        case USectionTask::GET_CHUNK:
          $this->getChunk($QUERY);
          break;
        case USectionTask::CREATE_TASK:
          $this->createTask($QUERY);
          break;
        case USectionTask::RUN_PRETASK:
          $this->runPretask($QUERY);
          break;
        case USectionTask::RUN_SUPERTASK:
          $this->runSupertask($QUERY);
          break;
        case USectionTask::CREATE_PRETASK:
          $this->createPretask($QUERY);
          break;
        case USectionTask::CREATE_SUPERTASK:
          $this->createSupertask($QUERY);
          break;
        case USectionTask::IMPORT_SUPERTASK:
          $this->importSupertask($QUERY);
          break;
        case USectionTask::SET_TASK_PRIORITY:
          $this->setTaskPriority($QUERY);
          break;
        case USectionTask::SET_SUPERTASK_PRIORITY:
          $this->setSuperTaskPriority($QUERY);
          break;
        case USectionTask::SET_TASK_NAME:
          $this->setTaskName($QUERY);
          break;
        case USectionTask::SET_TASK_COLOR:
          $this->setTaskColor($QUERY);
          break;
        case USectionTask::SET_TASK_CPU_ONLY:
          $this->setCpuTask($QUERY);
          break;
        case USectionTask::SET_TASK_SMALL:
          $this->setSmallTask($QUERY);
          break;
        case USectionTask::TASK_UNASSIGN_AGENT:
          $this->unassignAgent($QUERY);
          break;
        case USectionTask::DELETE_TASK:
          $this->deleteTask($QUERY);
          break;
        case USectionTask::PURGE_TASK:
          $this->purgeTask($QUERY);
          break;
        case USectionTask::SET_PRETASK_PRIORITY:
          $this->setPretaskPriority($QUERY);
          break;
        case USectionTask::SET_PRETASK_NAME:
          $this->setPretaskName($QUERY);
          break;
        case USectionTask::SET_PRETASK_COLOR:
          $this->setPretaskColor($QUERY);
          break;
        case USectionTask::SET_PRETASK_CHUNKSIZE:
          $this->setPretaskChunksize($QUERY);
          break;
        case USectionTask::SET_PRETASK_CPU_ONLY:
          $this->setPretaskCpuOnly($QUERY);
          break;
        case USectionTask::SET_PRETASK_SMALL:
          $this->setPretaskSmall($QUERY);
          break;
        case USectionTask::DELETE_PRETASK:
          $this->deletePretask($QUERY);
          break;
        case USectionTask::SET_SUPERTASK_NAME:
          $this->setSupertaskName($QUERY);
          break;
        case USectionTask::DELETE_SUPERTASK:
          $this->deleteSupertask($QUERY);
          break;
        default:
          $this->sendErrorResponse($QUERY[UQuery::SECTION], "INV", "Invalid section request!");
      }
    }
    catch(HTException $e){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], $e->getMessage());
    }
  }

  /**
   * @param array $QUERY 
   * @throws HTException 
   */
  private function deleteSupertask($QUERY){
    if(!isset($QUERY[UQueryTask::SUPERTASK_ID])){
      throw new HTException("Invalid query!");
    }
    TaskUtils::deleteSupertask($QUERY[UQueryTask::SUPERTASK_ID], $this->user);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setSupertaskName($QUERY){
    if(!isset($QUERY[UQueryTask::SUPERTASK_ID]) || !isset($QUERY[UQueryTask::SUPERTASK_NAME])){
      throw new HTException("Invalid query!");
    }
    TaskUtils::renameSupertask($QUERY[UQueryTask::SUPERTASK_ID], $QUERY[UQueryTask::SUPERTASK_NAME], $this->user);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function deletePretask($QUERY){
    if(!isset($QUERY[UQueryTask::PRETASK_ID])){
      throw new HTException("Invalid query!");
    }
    PretaskUtils::deletePretask($QUERY[UQueryTask::PRETASK_ID]);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setPretaskSmall($QUERY){
    if(!isset($QUERY[UQueryTask::PRETASK_ID]) || !isset($QUERY[UQueryTask::PRETASK_SMALL])){
      throw new HTException("Invalid query!");
    }
    PretaskUtils::setSmallTask($QUERY[UQueryTask::PRETASK_ID], ($QUERY[UQueryTask::PRETASK_SMALL])?1:0);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setPretaskCpuOnly($QUERY){
    if(!isset($QUERY[UQueryTask::PRETASK_ID]) || !isset($QUERY[UQueryTask::PRETASK_CPU_ONLY])){
      throw new HTException("Invalid query!");
    }
    PretaskUtils::setCpuOnlyTask($QUERY[UQueryTask::PRETASK_ID], ($QUERY[UQueryTask::PRETASK_CPU_ONLY])?1:0);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setPretaskChunksize($QUERY){
    if(!isset($QUERY[UQueryTask::PRETASK_ID]) || !isset($QUERY[UQueryTask::PRETASK_CHUNKSIZE])){
      throw new HTException("Invalid query!");
    }
    PretaskUtils::setChunkTime($QUERY[UQueryTask::PRETASK_ID], $QUERY[UQueryTask::PRETASK_CHUNKSIZE]);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setPretaskColor($QUERY){
    if(!isset($QUERY[UQueryTask::PRETASK_ID]) || !isset($QUERY[UQueryTask::PRETASK_COLOR])){
      throw new HTException("Invalid query!");
    }
    PretaskUtils::setColor($QUERY[UQueryTask::PRETASK_ID], $QUERY[UQueryTask::PRETASK_COLOR]);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setPretaskName($QUERY){
    if(!isset($QUERY[UQueryTask::PRETASK_ID]) || !isset($QUERY[UQueryTask::PRETASK_NAME])){
      throw new HTException("Invalid query!");
    }
    PretaskUtils::renamePretask($QUERY[UQueryTask::PRETASK_ID], $QUERY[UQueryTask::PRETASK_NAME]);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setPretaskPriority($QUERY){
    if(!isset($QUERY[UQueryTask::PRETASK_ID]) || !isset($QUERY[UQueryTask::PRETASK_PRIORITY])){
      throw new HTException("Invalid query!");
    }
    PretaskUtils::setPriority($QUERY[UQueryTask::PRETASK_ID], $QUERY[UQueryTask::PRETASK_PRIORITY]);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function purgeTask($QUERY){
    if(!isset($QUERY[UQueryTask::TASK_ID])){
      throw new HTException("Invalid query!");
    }
    TaskUtils::purgeTask($QUERY[UQueryTask::TASK_ID], $this->user);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function deleteTask($QUERY){
    if(!isset($QUERY[UQueryTask::TASK_ID])){
      throw new HTException("Invalid query!");
    }
    TaskUtils::delete($QUERY[UQueryTask::TASK_ID], $this->user, true);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function unassignAgent($QUERY){
    if(!isset($QUERY[UQueryTask::AGENT_ID])){
      throw new HTException("Invalid query!");
    }
    AgentUtils::assign($QUERY[UQueryTask::AGENT_ID], 0, $this->user);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setSmallTask($QUERY){
    if(!isset($QUERY[UQueryTask::TASK_ID]) || !isset($QUERY[UQueryTask::TASK_SMALL])){
      throw new HTException("Invalid query!");
    }
    TaskUtils::setSmallTask($QUERY[UQueryTask::TASK_ID], $QUERY[UQueryTask::TASK_SMALL], $this->user);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setCpuTask($QUERY){
    if(!isset($QUERY[UQueryTask::TASK_ID]) || !isset($QUERY[UQueryTask::TASK_CPU_ONLY])){
      throw new HTException("Invalid query!");
    }
    TaskUtils::setCpuTask($QUERY[UQueryTask::TASK_ID], $QUERY[UQueryTask::TASK_CPU_ONLY], $this->user);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setTaskColor($QUERY){
    if(!isset($QUERY[UQueryTask::TASK_ID]) || !isset($QUERY[UQueryTask::TASK_COLOR])){
      throw new HTException("Invalid query!");
    }
    TaskUtils::updateColor($QUERY[UQueryTask::TASK_ID], $QUERY[UQueryTask::TASK_COLOR], $this->user);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setTaskName($QUERY){
    if(!isset($QUERY[UQueryTask::TASK_ID]) || !isset($QUERY[UQueryTask::TASK_NAME])){
      throw new HTException("Invalid query!");
    }
    TaskUtils::rename($QUERY[UQueryTask::TASK_ID], $QUERY[UQueryTask::TASK_NAME], $this->user);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setTaskPriority($QUERY){
    if(!isset($QUERY[UQueryTask::TASK_ID]) || !isset($QUERY[UQueryTask::TASK_PRIORITY])){
      throw new HTException("Invalid query!");
    }
    TaskUtils::updatePriority($QUERY[UQueryTask::TASK_ID], $QUERY[UQueryTask::TASK_PRIORITY], $this->user);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setSupertaskPriority($QUERY){
    if(!isset($QUERY[UQueryTask::SUPERTASK_ID]) || !isset($QUERY[UQueryTask::SUPERTASK_PRIORITY])){
      throw new HTException("Invalid query!");
    }
    TaskUtils::setSupertaskPriority($QUERY[UQueryTask::SUPERTASK_ID], $QUERY[UQueryTask::SUPERTASK_PRIORITY], $this->user);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function importSupertask($QUERY){
    $toCheck = [
      UQueryTask::TASK_NAME,
      UQueryTask::TASK_CPU_ONLY,
      UQueryTask::TASK_SMALL,
      UQueryTask::TASK_CRACKER_TYPE,
      UQueryTask::MASKS,
      UQueryTask::TASK_OPTIMIZED
    ];
    foreach($toCheck as $input){
      if(!isset($QUERY[$input])){
        throw new HTException("Invalid query (missing $input)!");
      }
    }
    SupertaskUtils::importSupertask(
      $QUERY[UQueryTask::TASK_NAME],
      $QUERY[UQueryTask::TASK_CPU_ONLY],
      $QUERY[UQueryTask::TASK_SMALL],
      $QUERY[UQueryTask::TASK_OPTIMIZED],
      $QUERY[UQueryTask::TASK_CRACKER_TYPE],
      $QUERY[UQueryTask::MASKS]);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function createSupertask($QUERY){
    if(!isset($QUERY[UQueryTask::TASK_NAME]) || !isset($QUERY[UQueryTask::PRETASKS])){
      throw new HTException("Invalid query!");
    }
    SupertaskUtils::createSupertask($QUERY[UQueryTask::TASK_NAME], $QUERY[UQueryTask::PRETASKS]);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function createPretask($QUERY){
    $toCheck = [
      UQueryTask::TASK_NAME,
      UQueryTask::TASK_ATTACKCMD,
      UQueryTask::TASK_CHUNKSIZE,
      UQueryTask::TASK_STATUS,
      UQueryTask::TASK_BENCHTYPE,
      UQueryTask::TASK_COLOR,
      UQueryTask::TASK_CPU_ONLY,
      UQueryTask::TASK_SMALL,
      UQueryTask::TASK_CRACKER_TYPE,
      UQueryTask::TASK_FILES,
      UQueryTask::TASK_PRIORITY
    ];
    foreach($toCheck as $input){
      if(!isset($QUERY[$input])){
        throw new HTException("Invalid query (missing $input)!");
      }
    }
    $priority = $QUERY[UQueryTask::TASK_PRIORITY];
    if($priority < 0){
      $priority = 0;
    }
    PretaskUtils::createPretask(
      $QUERY[UQueryTask::TASK_NAME],
      $QUERY[UQueryTask::TASK_ATTACKCMD],
      $QUERY[UQueryTask::TASK_CHUNKSIZE],
      $QUERY[UQueryTask::TASK_STATUS],
      $QUERY[UQueryTask::TASK_COLOR],
      ($QUERY[UQueryTask::TASK_CPU_ONLY])?1:0,
      ($QUERY[UQueryTask::TASK_SMALL])?1:0,
      $QUERY[UQueryTask::TASK_BENCHTYPE],
      $QUERY[UQueryTask::TASK_FILES],
      $QUERY[UQueryTask::TASK_CRACKER_TYPE],
      $priority
    );
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function runSupertask($QUERY){
    if(!isset($QUERY[UQueryTask::SUPERTASK_ID]) || !isset($QUERY[UQueryTask::TASK_HASHLIST]) || !isset($QUERY[UQueryTask::TASK_CRACKER_VERSION])){
      throw new HTException("Invalid query!");
    }
    SupertaskUtils::runSupertask($QUERY[UQueryTask::SUPERTASK_ID], $QUERY[UQueryTask::TASK_HASHLIST], $QUERY[UQueryTask::TASK_CRACKER_VERSION]);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function runPretask($QUERY){
    if(!isset($QUERY[UQueryTask::PRETASK_ID]) || !isset($QUERY[UQueryTask::TASK_HASHLIST]) || !isset($QUERY[UQueryTask::TASK_CRACKER_VERSION])){
      throw new HTException("Invalid query!");
    }
    PretaskUtils::runPretask($QUERY[UQueryTask::PRETASK_ID], $QUERY[UQueryTask::TASK_HASHLIST], $QUERY[UQueryTask::TASK_NAME], $QUERY[UQueryTask::TASK_CRACKER_VERSION]);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function createTask($QUERY){
    $toCheck = [
      UQueryTask::TASK_NAME,
      UQueryTask::TASK_HASHLIST,
      UQueryTask::TASK_ATTACKCMD,
      UQueryTask::TASK_CHUNKSIZE,
      UQueryTask::TASK_STATUS,
      UQueryTask::TASK_BENCHTYPE,
      UQueryTask::TASK_COLOR,
      UQueryTask::TASK_CPU_ONLY,
      UQueryTask::TASK_SMALL,
      UQueryTask::TASK_SKIP,
      UQueryTask::TASK_CRACKER_VERSION,
      UQueryTask::TASK_FILES
    ];
    foreach($toCheck as $input){
      if(!isset($QUERY[$input])){
        throw new HTException("Invalid query!");
      }
    }
    TaskUtils::createTask(
      $QUERY[UQueryTask::TASK_HASHLIST],
      $QUERY[UQueryTask::TASK_NAME],
      $QUERY[UQueryTask::TASK_ATTACKCMD],
      $QUERY[UQueryTask::TASK_CHUNKSIZE],
      $QUERY[UQueryTask::TASK_STATUS],
      $QUERY[UQueryTask::TASK_BENCHTYPE],
      $QUERY[UQueryTask::TASK_COLOR],
      $QUERY[UQueryTask::TASK_CPU_ONLY],
      $QUERY[UQueryTask::TASK_SMALL],
      $QUERY[UQueryTask::TASK_SKIP],
      $QUERY[UQueryTask::TASK_PRIORITY],
      $QUERY[UQueryTask::TASK_FILES],
      $QUERY[UQueryTask::TASK_CRACKER_VERSION],
      $this->user
    );
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function getChunk($QUERY){
    if(!isset($QUERY[UQueryTask::CHUNK_ID])){
      throw new HTException("Invalid query!");
    }
    $chunk = TaskUtils::getChunk($QUERY[UQueryTask::CHUNK_ID], $this->user);

    $response = [
      UResponseTask::SECTION => $QUERY[UQueryTask::SECTION],
      UResponseTask::REQUEST => $QUERY[UQueryTask::REQUEST],
      UResponseTask::RESPONSE => UValues::OK,
      UResponseTask::CHUNK_ID => (int)$chunk->getId(),
      UResponseTask::CHUNK_START => (int)$chunk->getSkip(),
      UResponseTask::CHUNK_LENGTH => (int)$chunk->getLength(),
      UResponseTask::CHUNK_CHECKPOINT => (int)$chunk->getCheckpoint(),
      UResponseTask::CHUNK_PROGRESS => (float)($chunk->getProgress()/100),
      UResponseTask::CHUNK_TASK => (int)$chunk->getTaskId(),
      UResponseTask::CHUNK_AGENT => (int)$chunk->getAgentId(),
      UResponseTask::CHUNK_DISPATCHED => (int)$chunk->getDispatchTime(),
      UResponseTask::CHUNK_ACTIVITY => (int)$chunk->getSolveTime(),
      UResponseTask::CHUNK_STATE => (int)$chunk->getState(),
      UResponseTask::CHUNK_CRACKED => (int)$chunk->getCracked(),
      UResponseTask::CHUNK_SPEED => (int)$chunk->getSpeed()
    ];
    $this->sendResponse($response);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function getSupertask($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryTask::SUPERTASK_ID])){
      throw new HTException("Invalid query!");
    }
    $supertask = SupertaskUtils::getSupertask($QUERY[UQueryTask::SUPERTASK_ID]);
    $pretasks = SupertaskUtils::getPretasksOfSupertask($supertask->getId());

    $taskList = array();
    $response = [
      UResponseTask::SECTION => $QUERY[UQueryTask::SECTION],
      UResponseTask::REQUEST => $QUERY[UQueryTask::REQUEST],
      UResponseTask::RESPONSE => UValues::OK,
      UResponseTask::SUPERTASK_ID => (int)$supertask->getId(),
      UResponseTask::SUPERTASK_NAME => $supertask->getSupertaskName()
    ];
    foreach ($pretasks as $pretask) {
      $taskList[] = [
        UResponseTask::PRETASKS_ID => (int)$pretask->getId(),
        UResponseTask::PRETASKS_NAME => $pretask->getTaskName(),
        UResponseTask::PRETASKS_PRIORITY => (int)$pretask->getPriority()
      ];
    }
    $response[UResponseTask::PRETASKS] = $taskList;
    $this->sendResponse($response);
  }

  /**
   * @param array $QUERY
   */
  private function listSupertasks($QUERY){
    $supertasks = SupertaskUtils::getAllSupertasks();
    $taskList = array();
    $response = [
      UResponseTask::SECTION => $QUERY[UQueryTask::SECTION],
      UResponseTask::REQUEST => $QUERY[UQueryTask::REQUEST],
      UResponseTask::RESPONSE => UValues::OK
    ];
    foreach ($supertasks as $supertask) {
      $taskList[] = [
        UResponseTask::SUPERTASKS_ID => (int)$supertask->getId(),
        UResponseTask::SUPERTASKS_NAME => $supertask->getSupertaskName()
      ];
    }
    $response[UResponseTask::SUPERTASKS] = $taskList;
    $this->sendResponse($response);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function getPretask($QUERY){
    if(!isset($QUERY[UQueryTask::PRETASK_ID])){
      throw new HTException("Invalid query!");
    }
    $pretask = PretaskUtils::getPretask($QUERY[UQueryTask::PRETASK_ID]);

    $response = [
      UResponseTask::SECTION => $QUERY[UQueryTask::SECTION],
      UResponseTask::REQUEST => $QUERY[UQueryTask::REQUEST],
      UResponseTask::RESPONSE => UValues::OK,
      UResponseTask::PRETASK_ID => (int)$pretask->getId(),
      UResponseTask::PRETASK_NAME => $pretask->getTaskName(),
      UResponseTask::PRETASK_ATTACK => $pretask->getAttackCmd(),
      UResponseTask::PRETASK_CHUNKSIZE => (int)$pretask->getChunkTime(),
      UResponseTask::PRETASK_COLOR => (strlen($pretask->getColor()) == 0)?null:$pretask->getColor(),
      UResponseTask::PRETASK_BENCH_TYPE => ($pretask->getUseNewBench() == 1)?"speed":"runtime",
      UResponseTask::PRETASK_STATUS => (int)$pretask->getStatusTimer(),
      UResponseTask::PRETASK_PRIORITY => (int)$pretask->getPriority(),
      UResponseTask::PRETASK_CPU_ONLY => ($pretask->getIsCpuTask() == 1)?true:false,
      UResponseTask::PRETASK_SMALL => ($pretask->getIsSmall() == 1)?true:false
    ];

    $files = TaskUtils::getFilesOfPretask($pretask);
    $arr = [];
    foreach($files as $file){
      $arr[] = [
        UResponseTask::PRETASK_FILES_ID => (int)$file->getId(),
        UResponseTask::PRETASK_FILES_NAME => $file->getFilename(),
        UResponseTask::PRETASK_FILES_SIZE => (int)$file->getSize()
      ];
    }
    $response[UResponseTask::PRETASK_FILES] = $arr;
    $this->sendResponse($response);
  }

  /**
   * @param array $QUERY
   */
  private function listPreTasks($QUERY){
    $pretasks = PretaskUtils::getPretasks(false);
    $taskList = array();
    $response = [
      UResponseTask::SECTION => $QUERY[UQueryTask::SECTION],
      UResponseTask::REQUEST => $QUERY[UQueryTask::REQUEST],
      UResponseTask::RESPONSE => UValues::OK
    ];
    foreach ($pretasks as $pretask) {
      $taskList[] = [
        UResponseTask::PRETASKS_ID => (int)$pretask->getId(),
        UResponseTask::PRETASKS_NAME => $pretask->getTaskName(),
        UResponseTask::PRETASKS_PRIORITY => (int)$pretask->getPriority()
      ];
    }
    $response[UResponseTask::PRETASKS] = $taskList;
    $this->sendResponse($response);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function listSubTasks($QUERY){
    if(!isset($QUERY[UQueryTask::SUPERTASK_ID])){
      throw new HTException("Invalid query!");
    }
    $supertask = SupertaskUtils::getRunningSupertask($QUERY[UQueryTask::SUPERTASK_ID], $this->user);
    $subtasks = SupertaskUtils::getRunningSubtasks($supertask->getId(), $this->user);

    $taskList = array();
    $response = [
      UResponseTask::SECTION => $QUERY[UQueryTask::SECTION],
      UResponseTask::REQUEST => $QUERY[UQueryTask::REQUEST],
      UResponseTask::RESPONSE => UValues::OK
    ];
    foreach ($subtasks as $subtask) {
      $taskList[] = [
        UResponseTask::TASKS_ID => (int)$subtask->getId(),
        UResponseTask::TASKS_NAME => $subtask->getTaskName(),
        UResponseTask::TASKS_PRIORITY => (int)$subtask->getPriority()
      ];
    }
    $response[UResponseTask::SUBTASKS] = $taskList;
    $this->sendResponse($response);
  }

  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function getTask($QUERY){
    if(!isset($QUERY[UQueryTask::TASK_ID])){
      throw new HTException("Invalid query!");
    }
    $task = TaskUtils::getTask($QUERY[UQueryTask::TASK_ID], $this->user);
    $taskWrapper = TaskUtils::getTaskWrapper($task->getTaskWrapperId(), $this->user);

    $url = explode("/", $_SERVER['PHP_SELF']);
    unset($url[sizeof($url) - 1]);
    $response = [
      UResponseTask::SECTION => $QUERY[UQueryTask::SECTION],
      UResponseTask::REQUEST => $QUERY[UQueryTask::REQUEST],
      UResponseTask::RESPONSE => UValues::OK,
      UResponseTask::TASK_ID => (int)$task->getId(),
      UResponseTask::TASK_NAME => $task->getTaskName(),
      UResponseTask::TASK_ATTACK => $task->getAttackCmd(),
      UResponseTask::TASK_CHUNKSIZE => (int)$task->getChunkTime(),
      UResponseTask::TASK_COLOR => $task->getColor(),
      UResponseTask::TASK_BENCH_TYPE => ($task->getUseNewBench() == 1)?"speed":"runtime",
      UResponseTask::TASK_STATUS => (int)$task->getStatusTimer(),
      UResponseTask::TASK_PRIORITY => (int)$task->getPriority(),
      UResponseTask::TASK_CPU_ONLY => ($task->getIsCpuTask() == 1)?true:false,
      UResponseTask::TASK_SMALL => ($task->getIsSmall() == 1)?true:false,
      UResponseTask::TASK_SKIP => (int)$task->getSkipKeyspace(),
      UResponseTask::TASK_KEYSPACE => (int)$task->getKeyspace(),
      UResponseTask::TASK_DISPATCHED => (int)$task->getKeyspaceProgress(),
      UResponseTask::TASK_HASHLIST => (int)$taskWrapper->getHashlistId(),
      UResponseTask::TASK_IMAGE => Util::buildServerUrl() . implode("/", $url)."/taskimg.php?task=".$task->getId(),
    ];

    $files = TaskUtils::getFilesOfTask($task);
    $arr = [];
    foreach($files as $file){
      $arr[] = [
        UResponseTask::TASK_FILES_ID => (int)$file->getId(),
        UResponseTask::TASK_FILES_NAME => $file->getFilename(),
        UResponseTask::TASK_FILES_SIZE => (int)$file->getSize()
      ];
    }
    $response[UResponseTask::TASK_FILES] = $arr;

    $chunks = TaskUtils::getChunks($task->getId());
    $speed = 0;
    $searched = 0;
    $chunkIds = [];
    foreach($chunks as $chunk){
      if($chunk->getSpeed() > 0){
        $speed += $chunk->getSpeed();
      }
      $searched += $chunk->getCheckpoint() - $chunk->getSkip();
      $chunkIds[] = (int)$chunk->getId();
    }
    $response[UResponseTask::TASK_SPEED] = (int)$speed;
    $response[UResponseTask::TASK_SEARCHED] = (int)$searched;
    $response[UResponseTask::TASK_CHUNKS] = $chunkIds;

    $assignments = TaskUtils::getAssignments($task->getId());
    $arr = [];
    foreach ($assignments as $assignment) {
      $speed = 0;
      foreach($chunks as $chunk){
        if($chunk->getAgentId() == $assignment->getAgentId() && $chunk->getSpeed() > 0){
          $speed = $chunk->getSpeed();
          break;
        }
      }
      $arr[] = [
        UResponseTask::TASK_AGENTS_ID => (int)$assignment->getAgentId(),
        UResponseTask::TASK_AGENTS_BENCHMARK => $assignment->getBenchmark(),
        UResponseTask::TASK_AGENTS_SPEED => (int)$speed
      ];
    }
    $response[UResponseTask::TASK_AGENTS] = $arr;
    $this->sendResponse($response);
  }

  /**
   * @param array $QUERY
   */
  private function listTasks($QUERY){
    $taskWrappers = TaskUtils::getTaskWrappersForUser($this->user);
    $taskList = array();
    $response = [
      UResponseTask::SECTION => $QUERY[UQueryTask::SECTION],
      UResponseTask::REQUEST => $QUERY[UQueryTask::REQUEST],
      UResponseTask::RESPONSE => UValues::OK
    ];
    foreach ($taskWrappers as $taskWrapper) {
      if($taskWrapper->getTaskType() == DTaskTypes::NORMAL){
        $task = TaskUtils::getTaskOfWrapper($taskWrapper->getId());
        $taskList[] = [
          UResponseTask::TASKS_ID => (int)$task->getId(),
          UResponseTask::TASKS_NAME => $task->getTaskName(),
          UResponseTask::TASKS_TYPE => 0,
          UResponseTask::TASKS_HASHLIST => (int)$taskWrapper->getHashlistId(),
          UResponseTask::TASKS_PRIORITY => (int)$taskWrapper->getPriority()
        ];
      }
      else{
        $taskList[] = [
          UResponseTask::TASKS_SUPERTASK_ID => (int)$taskWrapper->getId(),
          UResponseTask::TASKS_NAME => $taskWrapper->getTaskWrapperName(),
          UResponseTask::TASKS_TYPE => 1,
          UResponseTask::TASKS_HASHLIST => (int)$taskWrapper->getHashlistId(),
          UResponseTask::TASKS_PRIORITY => (int)$taskWrapper->getPriority()
        ];
      }
    }
    $response[UResponseTask::TASKS] = $taskList;
    $this->sendResponse($response);
  }
}