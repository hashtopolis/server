<?php
use DBA\ContainFilter;
use DBA\OrderFilter;
use DBA\TaskWrapper;
use DBA\QueryFilter;
use DBA\Task;
use DBA\AbstractModel;
use DBA\Assignment;
use DBA\Chunk;

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
        // TODO:
        break;
      case USectionTask::LIST_PRETASKS:
        // TODO:
        break;
      case USectionTask::GET_PRETASK:
        // TODO:
        break;
      case USectionTask::LIST_SUPERTASKS:
        // TODO:
        break;
      case USectionTask::GET_SUPERTASK:
        // TODO:
        break;
      case USectionTask::GET_CHUNK:
        // TODO:
        break;
      default:
        $this->sendErrorResponse($QUERY[UQuery::SECTION], "INV", "Invalid section request!");
    }
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
      UResponseTask::TASK_IMAGE => Util::buildServerUrl() . implode("/", $url)."/api/taskimg.php?task=".$task->getId(),
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
   * @return array(Task TaskWrapper)
   */
  private function checkTask($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryTask::TASK_ID])){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid query!");
    }
    $task = $FACTORIES::getTaskFactory()->get($QUERY[UQueryTask::TASK_ID]);
    if($task == null){
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], "Invalid agent ID!");
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