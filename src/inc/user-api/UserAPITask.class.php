<?php
use DBA\ContainFilter;
use DBA\OrderFilter;
use DBA\TaskWrapper;
use DBA\QueryFilter;
use DBA\Task;
use DBA\Supertask;
use DBA\Assignment;
use DBA\Chunk;
use DBA\Pretask;
use DBA\JoinFilter;
use DBA\SupertaskPretask;
use DBA\AccessGroupUser;
use DBA\FileTask;
use DBA\FilePretask;
use DBA\File;

class UserAPITask extends UserAPIBasic {
  public function execute($QUERY = array()) {
    global $FACTORIES;

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
      default:
        $this->sendErrorResponse($QUERY[UQuery::SECTION], "INV", "Invalid section request!");
    }
  }

  private function purgeTask($QUERY){
    if(!isset($QUERY[UQueryTask::TASK_ID])){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
    }
    $error = TaskUtils::purgeTask($QUERY[UQueryTask::TASK_ID], $this->user);
    $this->checkForError($QUERY, $error);
  }

  private function deleteTask($QUERY){
    if(!isset($QUERY[UQueryTask::TASK_ID])){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
    }
    $error = TaskUtils::delete($QUERY[UQueryTask::TASK_ID], $this->user, true);
    $this->checkForError($QUERY, $error);
  }

  private function unassignAgent($QUERY){
    if(!isset($QUERY[UQueryTask::AGENT_ID])){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
    }
    $error = AgentUtils::assign($QUERY[UQueryTask::AGENT_ID], 0, $this->user);
    $this->checkForError($QUERY, $error);
  }

  private function setSmallTask($QUERY){
    if(!isset($QUERY[UQueryTask::TASK_ID]) || !isset($QUERY[UQueryTask::TASK_SMALL])){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
    }
    $error = TaskUtils::setSmallTask($QUERY[UQueryTask::TASK_ID], $QUERY[UQueryTask::TASK_SMALL], $this->user);
    $this->checkForError($QUERY, $error);
  }

  private function setCpuTask($QUERY){
    if(!isset($QUERY[UQueryTask::TASK_ID]) || !isset($QUERY[UQueryTask::TASK_CPU_ONLY])){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
    }
    $error = TaskUtils::setCpuTask($QUERY[UQueryTask::TASK_ID], $QUERY[UQueryTask::TASK_CPU_ONLY], $this->user);
    $this->checkForError($QUERY, $error);
  }

  private function setTaskColor($QUERY){
    if(!isset($QUERY[UQueryTask::TASK_ID]) || !isset($QUERY[UQueryTask::TASK_COLOR])){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
    }
    $error = TaskUtils::updateColor($QUERY[UQueryTask::TASK_ID], $QUERY[UQueryTask::TASK_COLOR], $this->user);
    $this->checkForError($QUERY, $error);
  }

  private function setTaskName($QUERY){
    if(!isset($QUERY[UQueryTask::TASK_ID]) || !isset($QUERY[UQueryTask::TASK_NAME])){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
    }
    $error = TaskUtils::rename($QUERY[UQueryTask::TASK_ID], $QUERY[UQueryTask::TASK_NAME], $this->user);
    $this->checkForError($QUERY, $error);
  }

  private function setTaskPriority($QUERY){
    if(!isset($QUERY[UQueryTask::TASK_ID]) || !isset($QUERY[UQueryTask::TASK_PRIORITY])){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
    }
    $error = TaskUtils::updatePriority($QUERY[UQueryTask::TASK_ID], $QUERY[UQueryTask::TASK_PRIORITY], $this->user);
    $this->checkForError($QUERY, $error);
  }

  private function setSupertaskPriority($QUERY){
    if(!isset($QUERY[UQueryTask::SUPERTASK_ID]) || !isset($QUERY[UQueryTask::SUPERTASK_PRIORITY])){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
    }
    $error = TaskUtils::setSupertaskPriority($QUERY[UQueryTask::SUPERTASK_ID], $QUERY[UQueryTask::SUPERTASK_PRIORITY], $this->user);
    $this->checkForError($QUERY, $error);
  }

  private function importSupertask($QUERY){
    global $FACTORIES;

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
        $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query (missing $input)!");
      }
    }
    $name = $QUERY[UQueryTask::TASK_NAME];
    $isCpuOnly = ($QUERY[UQueryTask::TASK_CPU_ONLY])?1:0;
    $isSmall = ($QUERY[UQueryTask::TASK_SMALL])?1:0;
    $useOptimized = ($QUERY[UQueryTask::TASK_OPTIMIZED])?true:false;
    $crackerBinaryType = $FACTORIES::getCrackerBinaryTypeFactory()->get($QUERY[UQueryTask::TASK_CRACKER_TYPE]);
    if($crackerBinaryType == null){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid cracker type ID!");
    }

    $masks = $QUERY[UQueryTask::MASKS];
    if(!is_array($masks)){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Masks need to be provided as array!");
    }
    TaskUtils::prepareImportMasks($masks);
    if (sizeof($masks) == 0) {
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "No valid masks found!");
    }

    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $pretasks = TaskUtils::createImportPretasks($masks, $isSmall, $isCpuOnly, $crackerBinaryType, $useOptimized);

    $supertask = new Supertask(0, $name);
    $supertask = $FACTORIES::getSupertaskFactory()->save($supertask);
    foreach ($pretasks as $preTask) {
      $relation = new SupertaskPretask(0, $supertask->getId(), $preTask->getId());
      $FACTORIES::getSupertaskPretaskFactory()->save($relation);
    }
    $FACTORIES::getAgentFactory()->getDB()->commit();
    $this->sendSuccessResponse($QUERY);
  }

  private function createSupertask($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryTask::TASK_NAME]) || !isset($QUERY[UQueryTask::PRETASKS])){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
    }
    $name = $QUERY[UQueryTask::TASK_NAME];
    $pretasks = $QUERY[UQueryTask::PRETASKS];
    if(!is_array($pretasks) || sizeof($pretasks) == 0){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Cannot create empty supertask!");
    }
    $tasks = [];
    foreach($pretasks as $pretaskId){
      $pretask = $FACTORIES::getPretaskFactory()->get($pretaskId);
      if($pretask == null){
        $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid preconfigured task ID ($pretaskId)!");
      }
      $tasks[] = $pretask;
    }

    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $supertask = new Supertask(0, $name);
    $supertask = $FACTORIES::getSupertaskFactory()->save($supertask);

    foreach($tasks as $pretask){
      $supertaskPretask = new SupertaskPretask(0, $supertask->getId(), $pretask->getId());
      $FACTORIES::getSupertaskPretaskFactory()->save($supertaskPretask);
    }

    $FACTORIES::getAgentFactory()->getDB()->commit();
    $this->sendSuccessResponse($QUERY);
  }

  private function createPretask($QUERY){
    global $FACTORIES, $CONFIG;

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
        $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query (missing $input)!");
      }
    }
    $name = $QUERY[UQueryTask::TASK_NAME];
    $cracker = $FACTORIES::getCrackerBinaryTypeFactory()->get($QUERY[UQueryTask::TASK_CRACKER_TYPE]);
    if($cracker == null){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid cracker type ID!");
    }
    $attackCmd = $QUERY[UQueryTask::TASK_ATTACKCMD];
    if(strpos($attackCmd, $CONFIG->getVal(DConfig::HASHLIST_ALIAS)) === false){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Attack command does not contain hashlist alias!");
    }
    else if(Util::containsBlacklistedChars($attackCmd)){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Attack command contains blacklisted characters!");
    }
    $chunksize = $QUERY[UQueryTask::TASK_CHUNKSIZE];
    if(!is_numeric($chunksize) || $chunksize < 1){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid chunk size!");
    }
    $status = $QUERY[UQueryTask::TASK_STATUS];
    if(!is_numeric($status) || $status < 1){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid status timer!");
    }
    $benchtype = $QUERY[UQueryTask::TASK_BENCHTYPE];
    if($benchtype != 'speed' && $benchtype != 'runtime'){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid benchmark type!");
    }
    $benchtype = ($benchtype == 'speed')?1:0;
    $color = $QUERY[UQueryTask::TASK_COLOR];
    if (preg_match("/[0-9A-Za-z]{6}/", $color) != 1) {
      $color = null;
    }
    $isCpuOnly = ($QUERY[UQueryTask::TASK_CPU_ONLY])?1:0;
    $isSmall = ($QUERY[UQueryTask::TASK_SMALL])?1:0;
    $priority = $QUERY[UQueryTask::TASK_PRIORITY];
    if($priority < 0){
      $priority = 0;
    }

    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();

    $pretask = new Pretask(
      0,
      $name,
      $attackCmd,
      $chunksize,
      $status,
      $color,
      $isSmall,
      $isCpuOnly,
      $benchtype,
      $priority,
      0,
      $cracker->getId()
    );

    $pretask = $FACTORIES::getPretaskFactory()->save($pretask);

    $files = $QUERY[UQueryTask::TASK_FILES];
    if (is_array($files) && sizeof($files) > 0) {
      foreach ($files as $fileId) {
        $taskFile = new FilePretask(0, $fileId, $pretask->getId());
        $FACTORIES::getFilePretaskFactory()->save($taskFile);
      }
    }

    $FACTORIES::getAgentFactory()->getDB()->commit();
    $this->sendSuccessResponse($QUERY);
  }

  private function runSupertask($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryTask::SUPERTASK_ID]) || !isset($QUERY[UQueryTask::TASK_HASHLIST]) || !isset($QUERY[UQueryTask::TASK_CRACKER_VERSION])){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
    }
    $supertask = $FACTORIES::getSupertaskFactory()->get($QUERY[UQueryTask::SUPERTASK_ID]);
    if($supertask == null){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid supertask ID!");
    }
    $hashlist = $FACTORIES::getHashlistFactory()->get($QUERY[UQueryTask::TASK_HASHLIST]);
    if($hashlist == null){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid hashlist ID!");
    }
    $cracker = $FACTORIES::getCrackerBinaryFactory()->get($QUERY[UQueryTask::TASK_CRACKER_VERSION]);
    if($cracker == null){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid cracker ID!");
    }
    $qF = new QueryFilter(SupertaskPretask::SUPERTASK_ID, $supertask->getId(), "=", $FACTORIES::getSupertaskPretaskFactory());
    $jF = new JoinFilter($FACTORIES::getSupertaskPretaskFactory(), Pretask::PRETASK_ID, SupertaskPretask::PRETASK_ID);
    $joined = $FACTORIES::getPretaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    $pretasks = $joined[$FACTORIES::getPretaskFactory()->getModelName()];

    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();

    $wrapperPriority = 0;
    foreach ($pretasks as $pretask) {
      if ($wrapperPriority == 0 || $wrapperPriority > $pretask->getPriority()) {
        $wrapperPriority = $pretask->getPriority();
      }
    }

    $taskWrapper = new TaskWrapper(0, $wrapperPriority, DTaskTypes::SUPERTASK, $hashlist->getId(), $hashlist->getAccessGroupId(), $supertask->getSupertaskName());
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->save($taskWrapper);

    foreach ($pretasks as $pretask) {
      $crackerBinaryId = $cracker->getId();
      if($cracker->getCrackerBinaryTypeId() != $pretask->getCrackerBinaryTypeId()){
        $crackerBinaryId = CrackerBinaryUtils::getNewestVersion($pretask->getCrackerBinaryTypeId());
      }

      $task = new Task(0, $pretask->getTaskName(), $pretask->getAttackCmd(), $pretask->getChunkTime(), $pretask->getStatusTimer(), 0, 0, $pretask->getPriority(), $pretask->getColor(), $pretask->getIsSmall(), $pretask->getIsCpuTask(), $pretask->getUseNewBench(), 0, $crackerBinaryId, $cracker->getCrackerBinaryTypeId(), $taskWrapper->getId());
      if ($hashlist->getHexSalt() == 1 && strpos($task->getAttackCmd(), "--hex-salt") === false) {
        $task->setAttackCmd("--hex-salt " . $task->getAttackCmd());
      }
      $task = $FACTORIES::getTaskFactory()->save($task);
      TaskUtils::copyPretaskFiles($pretask, $task);
    }

    $FACTORIES::getAgentFactory()->getDB()->commit();
    $this->sendSuccessResponse($QUERY);
  }

  private function runPretask($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryTask::PRETASK_ID]) || !isset($QUERY[UQueryTask::TASK_HASHLIST]) || !isset($QUERY[UQueryTask::TASK_CRACKER_VERSION])){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
    }
    $pretask = $FACTORIES::getPretaskFactory()->get($QUERY[UQueryTask::PRETASK_ID]);
    if($pretask == null){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid preconfigured task ID!");
    }
    $hashlist = $FACTORIES::getHashlistFactory()->get($QUERY[UQueryTask::TASK_HASHLIST]);
    if($hashlist == null){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid hashlist ID!");
    }
    $name = $QUERY[UQueryTask::TASK_NAME];
    if (strlen($name) == 0) {
      $name = "HL" . $hashlist->getId() . "_" . date("Ymd_Hi");
    }
    $cracker = $FACTORIES::getCrackerBinaryFactory()->get($QUERY[UQueryTask::TASK_CRACKER_VERSION]);
    if($cracker == null){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid cracker ID!");
    }
    else if($pretask->getCrackerBinaryTypeId() != $cracker->getCrackerBinaryTypeId()){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Provided cracker does not match the type of the pretask!");
    }

    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $taskWrapper = new TaskWrapper(0, $pretask->getPriority(), DTaskTypes::NORMAL, $hashlist->getId(), $hashlist->getAccessGroupId(), "");
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->save($taskWrapper);

    $task = new Task(
      0,
      $name,
      $pretask->getAttackCmd(),
      $pretask->getChunkTime(),
      $pretask->getStatusTimer(),
      0,
      0,
      $pretask->getPriority(),
      $pretask->getColor(),
      $pretask->getIsSmall(),
      $pretask->getIsCpuTask(),
      $pretask->getUseNewBench(),
      0,
      $cracker->getId(),
      $cracker->getCrackerBinaryTypeId(),
      $taskWrapper->getId()
    );
    $task = $FACTORIES::getTaskFactory()->save($task);

    $qF = new QueryFilter(FilePretask::PRETASK_ID, $pretask->getId(), "=", $FACTORIES::getFilePretaskFactory());
    $jF = new JoinFilter($FACTORIES::getFilePretaskFactory(), File::FILE_ID, FilePretask::FILE_ID);
    $joined = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    $files = $joined[$FACTORIES::getFileFactory()->getModelName()];
    foreach ($files as $file) {
      $taskFile = new FileTask(0, $file->getId(), $task->getId());
      $FACTORIES::getFileTaskFactory()->save($taskFile);
    }

    $FACTORIES::getAgentFactory()->getDB()->commit();

    $payload = new DataSet(array(DPayloadKeys::TASK => $task));
    NotificationHandler::checkNotifications(DNotificationType::NEW_TASK, $payload);
    $this->sendSuccessResponse($QUERY);
  }

  private function createTask($QUERY){
    global $FACTORIES, $CONFIG;

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
        $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
      }
    }
    $hashlist = $FACTORIES::getHashlistFactory()->get($QUERY[UQueryTask::TASK_HASHLIST]);
    if($hashlist == null){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid hashlist ID!");
    }
    $name = $QUERY[UQueryTask::TASK_NAME];
    if (strlen($name) == 0) {
      $name = "HL" . $hashlist->getId() . "_" . date("Ymd_Hi");
    }
    $accessGroup = $FACTORIES::getAccessGroupFactory()->get($hashlist->getAccessGroupId());
    $qF1 = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $accessGroup->getId(), "=");
    $qF2 = new QueryFilter(AccessGroupUser::USER_ID, $this->user->getId(), "=");
    $accessGroupUser = $FACTORIES::getAccessGroupUserFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
    if ($accessGroupUser == null) {
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "You have no access to this hashlist!");
    }
    $cracker = $FACTORIES::getCrackerBinaryFactory()->get($QUERY[UQueryTask::TASK_CRACKER_VERSION]);
    if($cracker == null){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid cracker ID!");
    }
    $attackCmd = $QUERY[UQueryTask::TASK_ATTACKCMD];
    if(strpos($attackCmd, $CONFIG->getVal(DConfig::HASHLIST_ALIAS)) === false){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Attack command does not contain hashlist alias!");
    }
    else if(Util::containsBlacklistedChars($attackCmd)){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Attack command contains blacklisted characters!");
    }
    $chunksize = $QUERY[UQueryTask::TASK_CHUNKSIZE];
    if(!is_numeric($chunksize) || $chunksize < 1){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid chunk size!");
    }
    $status = $QUERY[UQueryTask::TASK_STATUS];
    if(!is_numeric($status) || $status < 1){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid status timer!");
    }
    $benchtype = $QUERY[UQueryTask::TASK_BENCHTYPE];
    if($benchtype != 'speed' && $benchtype != 'runtime'){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid benchmark type!");
    }
    $benchtype = ($benchtype == 'speed')?1:0;
    $color = $QUERY[UQueryTask::TASK_COLOR];
    if (preg_match("/[0-9A-Za-z]{6}/", $color) != 1) {
      $color = null;
    }
    $isCpuOnly = ($QUERY[UQueryTask::TASK_CPU_ONLY])?1:0;
    $isSmall = ($QUERY[UQueryTask::TASK_SMALL])?1:0;
    $skip = $QUERY[UQueryTask::TASK_SKIP];
    if($skip < 0){
      $skip = 0;
    }
    $priority = $QUERY[UQueryTask::TASK_PRIORITY];
    if($priority < 0){
      $priority = 0;
    }

    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $taskWrapper = new TaskWrapper(0, $priority, DTaskTypes::NORMAL, $hashlist->getId(), $accessGroup->getId(), "");
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->save($taskWrapper);

    $task = new Task(
      0,
      $name,
      $attackCmd,
      $chunksize,
      $status,
      0,
      0,
      $priority,
      $color,
      $isSmall,
      $isCpuOnly,
      $benchtype,
      $skip,
      $cracker->getId(),
      $cracker->getCrackerBinaryTypeId(),
      $taskWrapper->getId()
    );
    $task = $FACTORIES::getTaskFactory()->save($task);

    $files = $QUERY[UQueryTask::TASK_FILES];
    if (is_array($files) && sizeof($files) > 0) {
      foreach ($files as $fileId) {
        $taskFile = new FileTask(0, $fileId, $task->getId());
        $FACTORIES::getFileTaskFactory()->save($taskFile);
      }
    }

    $FACTORIES::getAgentFactory()->getDB()->commit();

    $payload = new DataSet(array(DPayloadKeys::TASK => $task));
    NotificationHandler::checkNotifications(DNotificationType::NEW_TASK, $payload);
    $this->sendSuccessResponse($QUERY);
  }

  private function getChunk($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryTask::CHUNK_ID])){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
    }
    $chunk = $FACTORIES::getChunkFactory()->get($QUERY[UQueryTask::CHUNK_ID]);
    if($chunk == null){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid chunk ID!");
    }

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

  private function getSupertask($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryTask::SUPERTASK_ID])){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
    }
    $supertask = $FACTORIES::getSupertaskFactory()->get($QUERY[UQueryTask::SUPERTASK_ID]);
    if($supertask == null){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid supertask ID!");
    }

    $oF = new OrderFilter(Pretask::PRIORITY, "DESC", $FACTORIES::getPretaskFactory());
    $jF = new JoinFilter($FACTORIES::getSupertaskPretaskFactory(), Pretask::PRETASK_ID, SupertaskPretask::PRETASK_ID);
    $joined = $FACTORIES::getPretaskFactory()->filter(array($FACTORIES::ORDER => $oF, $FACTORIES::JOIN => $jF));
    $pretasks = $joined[$FACTORIES::getPretaskFactory()->getModelName()];

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

  private function listSupertasks($QUERY){
    global $FACTORIES;

    $supertasks = $FACTORIES::getSupertaskFactory()->filter(array());

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

  private function getPretask($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryTask::PRETASK_ID])){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
    }
    $pretask = $FACTORIES::getPretaskFactory()->get($QUERY[UQueryTask::PRETASK_ID]);
    if($pretask == null){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid preconfigured task!");
    }

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

  private function listPreTasks($QUERY){
    global $FACTORIES;

    $oF = new OrderFilter(Pretask::PRIORITY, "DESC");
    $qF = new QueryFilter(Pretask::IS_MASK_IMPORT, 0, "=");
    $pretasks = $FACTORIES::getPretaskFactory()->filter(array($FACTORIES::ORDER => $oF, $FACTORIES::FILTER => $qF));

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

  private function listSubTasks($QUERY){
    global $FACTORIES;

    $supertask = $this->checkSupertask($QUERY);
    $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $supertask->getId(), "=");
    $oF = new OrderFilter(Task::PRIORITY, "DESC");
    $subtasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => $oF));

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

  private function getTask($QUERY){
    global $FACTORIES;

    [$task, $taskWrapper] = $this->checkTask($QUERY);
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

    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $oF = new OrderFilter(Chunk::DISPATCH_TIME, "DESC");
    $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => $oF));

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

    $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignments = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => $qF));
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
   * @return Supertask
   */
  private function checkSupertask($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryTask::SUPERTASK_ID])){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
    }
    $supertask = $FACTORIES::getTaskWrapperFactory()->get($QUERY[UQueryTask::SUPERTASK_ID]);
    if($supertask == null){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid taskwrapper ID!");
    }
    $accessGroupIds = Util::getAccessGroupIds($this->user->getId());
    if(!in_array($supertask->getAccessGroupId(), $accessGroupIds)){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "No access to this task!");
    }
    return $supertask;
  }

  /**
   * @param array $QUERY
   * @return array(Task TaskWrapper)
   */
  private function checkTask($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryTask::TASK_ID])){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
    }
    $task = $FACTORIES::getTaskFactory()->get($QUERY[UQueryTask::TASK_ID]);
    if($task == null){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid task ID!");
    }
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    $accessGroupIds = Util::getAccessGroupIds($this->user->getId());
    if(!in_array($taskWrapper->getAccessGroupId(), $accessGroupIds)){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "No access to this task!");
    }
    return [$task, $taskWrapper];
  }

  private function listTasks($QUERY){
    global $FACTORIES;

    $accessGroupIds = Util::getAccessGroupIds($this->user->getId());

    $qF = new ContainFilter(TaskWrapper::ACCESS_GROUP_ID, $accessGroupIds);
    $oF1 = new OrderFilter(TaskWrapper::PRIORITY, "DESC");
    $oF2 = new OrderFilter(TaskWrapper::TASK_WRAPPER_ID, "DESC");
    $taskWrappers = $FACTORIES::getTaskWrapperFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => array($oF1, $oF2)));

    $taskList = array();
    $response = [
      UResponseTask::SECTION => $QUERY[UQueryTask::SECTION],
      UResponseTask::REQUEST => $QUERY[UQueryTask::REQUEST],
      UResponseTask::RESPONSE => UValues::OK
    ];
    foreach ($taskWrappers as $taskWrapper) {
      if($taskWrapper->getTaskType() == DTaskTypes::NORMAL){
        $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
        $task = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF), true);
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