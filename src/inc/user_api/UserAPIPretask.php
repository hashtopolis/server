<?php

namespace Hashtopolis\inc\user_api;

use Exception;
use Hashtopolis\inc\defines\UQuery;
use Hashtopolis\inc\defines\UQueryTask;
use Hashtopolis\inc\defines\UResponseTask;
use Hashtopolis\inc\defines\USectionPretask;
use Hashtopolis\inc\defines\UValues;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\PretaskUtils;
use Hashtopolis\inc\utils\TaskUtils;

class UserAPIPretask extends UserAPIBasic {
  public function execute($QUERY = array()) {
    try {
      switch ($QUERY[UQuery::REQUEST]) {
        case USectionPretask::LIST_PRETASKS:
          $this->listPreTasks($QUERY);
          break;
        case USectionPretask::GET_PRETASK:
          $this->getPretask($QUERY);
          break;
        case USectionPretask::CREATE_PRETASK:
          $this->createPretask($QUERY);
          break;
        case USectionPretask::SET_PRETASK_PRIORITY:
          $this->setPretaskPriority($QUERY);
          break;
        case USectionPretask::SET_PRETASK_MAX_AGENTS:
          $this->setPretaskMaxAgents($QUERY);
          break;
        case USectionpretask::SET_PRETASK_NAME:
          $this->setPretaskName($QUERY);
          break;
        case USectionPretask::SET_PRETASK_COLOR:
          $this->setPretaskColor($QUERY);
          break;
        case USectionPretask::SET_PRETASK_CHUNKSIZE:
          $this->setPretaskChunksize($QUERY);
          break;
        case USectionPretask::SET_PRETASK_CPU_ONLY:
          $this->setPretaskCpuOnly($QUERY);
          break;
        case USectionPretask::SET_PRETASK_SMALL:
          $this->setPretaskSmall($QUERY);
          break;
        case USectionpretask::DELETE_PRETASK:
          $this->deletePretask($QUERY);
          break;
        default:
          $this->sendErrorResponse($QUERY[UQuery::SECTION], "INV", "Invalid section request!");
      }
    }
    catch (Exception $e) {
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], $e->getMessage());
    }
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function deletePretask($QUERY) {
    if (!isset($QUERY[UQueryTask::PRETASK_ID])) {
      throw new HTException("Invalid query!");
    }
    PretaskUtils::deletePretask($QUERY[UQueryTask::PRETASK_ID]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setPretaskSmall($QUERY) {
    if (!isset($QUERY[UQueryTask::PRETASK_ID]) || !isset($QUERY[UQueryTask::PRETASK_SMALL])) {
      throw new HTException("Invalid query!");
    }
    PretaskUtils::setSmallTask($QUERY[UQueryTask::PRETASK_ID], $QUERY[UQueryTask::PRETASK_SMALL]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setPretaskCpuOnly($QUERY) {
    if (!isset($QUERY[UQueryTask::PRETASK_ID]) || !isset($QUERY[UQueryTask::PRETASK_CPU_ONLY])) {
      throw new HTException("Invalid query!");
    }
    PretaskUtils::setCpuOnlyTask($QUERY[UQueryTask::PRETASK_ID], $QUERY[UQueryTask::PRETASK_CPU_ONLY]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setPretaskChunksize($QUERY) {
    if (!isset($QUERY[UQueryTask::PRETASK_ID]) || !isset($QUERY[UQueryTask::PRETASK_CHUNKSIZE])) {
      throw new HTException("Invalid query!");
    }
    PretaskUtils::setChunkTime($QUERY[UQueryTask::PRETASK_ID], $QUERY[UQueryTask::PRETASK_CHUNKSIZE]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setPretaskColor($QUERY) {
    if (!isset($QUERY[UQueryTask::PRETASK_ID]) || !isset($QUERY[UQueryTask::PRETASK_COLOR])) {
      throw new HTException("Invalid query!");
    }
    PretaskUtils::setColor($QUERY[UQueryTask::PRETASK_ID], $QUERY[UQueryTask::PRETASK_COLOR]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setPretaskName($QUERY) {
    if (!isset($QUERY[UQueryTask::PRETASK_ID]) || !isset($QUERY[UQueryTask::PRETASK_NAME])) {
      throw new HTException("Invalid query!");
    }
    PretaskUtils::renamePretask($QUERY[UQueryTask::PRETASK_ID], $QUERY[UQueryTask::PRETASK_NAME]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setPretaskPriority($QUERY) {
    if (!isset($QUERY[UQueryTask::PRETASK_ID]) || !isset($QUERY[UQueryTask::PRETASK_PRIORITY])) {
      throw new HTException("Invalid query!");
    }
    PretaskUtils::setPriority($QUERY[UQueryTask::PRETASK_ID], $QUERY[UQueryTask::PRETASK_PRIORITY]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setPretaskMaxAgents($QUERY) {
    if (!isset($QUERY[UQueryTask::PRETASK_ID]) || !isset($QUERY[UQueryTask::PRETASK_MAX_AGENTS])) {
      throw new HTException("Invalid query!");
    }
    PretaskUtils::setMaxAgents($QUERY[UQueryTask::PRETASK_ID], $QUERY[UQueryTask::PRETASK_MAX_AGENTS], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function createPretask($QUERY) {
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
      UQueryTask::TASK_PRIORITY,
      UQueryTask::TASK_MAX_AGENTS
    ];
    foreach ($toCheck as $input) {
      if (!isset($QUERY[$input])) {
        throw new HTException("Invalid query (missing $input)!");
      }
    }
    $priority = $QUERY[UQueryTask::TASK_PRIORITY];
    if ($priority < 0) {
      $priority = 0;
    }
    $maxAgents = $QUERY[UQueryTask::TASK_MAX_AGENTS];
    if ($maxAgents < 0) {
      $maxAgents = 0;
    }
    PretaskUtils::createPretask(
      $QUERY[UQueryTask::TASK_NAME],
      $QUERY[UQueryTask::TASK_ATTACKCMD],
      $QUERY[UQueryTask::TASK_CHUNKSIZE],
      $QUERY[UQueryTask::TASK_STATUS],
      $QUERY[UQueryTask::TASK_COLOR],
      ($QUERY[UQueryTask::TASK_CPU_ONLY]) ? 1 : 0,
      ($QUERY[UQueryTask::TASK_SMALL]) ? 1 : 0,
      ($QUERY[UQueryTask::TASK_BENCHTYPE] == 'speed') ? 1 : 0,
      $QUERY[UQueryTask::TASK_FILES],
      $QUERY[UQueryTask::TASK_CRACKER_TYPE],
      $maxAgents,
      $priority
    );
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function getPretask($QUERY) {
    if (!isset($QUERY[UQueryTask::PRETASK_ID])) {
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
      UResponseTask::PRETASK_COLOR => (strlen($pretask->getColor()) == 0) ? null : $pretask->getColor(),
      UResponseTask::PRETASK_BENCH_TYPE => ($pretask->getUseNewBench() == 1) ? "speed" : "runtime",
      UResponseTask::PRETASK_STATUS => (int)$pretask->getStatusTimer(),
      UResponseTask::PRETASK_PRIORITY => (int)$pretask->getPriority(),
      UResponseTask::PRETASK_MAX_AGENTS => (int)$pretask->getMaxAgents(),
      UResponseTask::PRETASK_CPU_ONLY => ($pretask->getIsCpuTask() == 1) ? true : false,
      UResponseTask::PRETASK_SMALL => ($pretask->getIsSmall() == 1) ? true : false
    ];
    
    $files = TaskUtils::getFilesOfPretask($pretask);
    $arr = [];
    foreach ($files as $file) {
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
  private function listPreTasks($QUERY) {
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
        UResponseTask::PRETASKS_PRIORITY => (int)$pretask->getPriority(),
        UResponseTask::PRETASKS_MAX_AGENTS => (int)$pretask->getMaxAgents()
      ];
    }
    $response[UResponseTask::PRETASKS] = $taskList;
    $this->sendResponse($response);
  }
}