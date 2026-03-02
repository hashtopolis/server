<?php

namespace Hashtopolis\inc\user_api;

use Exception;
use Hashtopolis\inc\defines\UQuery;
use Hashtopolis\inc\defines\UQueryTask;
use Hashtopolis\inc\defines\UResponseTask;
use Hashtopolis\inc\defines\USectionSupertask;
use Hashtopolis\inc\defines\UValues;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\SupertaskUtils;

class UserAPISupertask extends UserAPIBasic {
  public function execute($QUERY = array()) {
    try {
      switch ($QUERY[UQuery::REQUEST]) {
        case USectionSupertask::LIST_SUPERTASKS:
          $this->listSupertasks($QUERY);
          break;
        case USectionSupertask::GET_SUPERTASK:
          $this->getSupertask($QUERY);
          break;
        case USectionSupertask::CREATE_SUPERTASK:
          $this->createSupertask($QUERY);
          break;
        case USectionSupertask::IMPORT_SUPERTASK:
          $this->importSupertask($QUERY);
          break;
        case USectionSupertask::SET_SUPERTASK_NAME:
          $this->setSupertaskName($QUERY);
          break;
        case USectionSupertask::DELETE_SUPERTASK:
          $this->deleteSupertask($QUERY);
          break;
        case USectionSupertask::BULK_SUPERTASK:
          $this->bulkSupertask($QUERY);
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
  private function deleteSupertask($QUERY) {
    if (!isset($QUERY[UQueryTask::SUPERTASK_ID])) {
      throw new HTException("Invalid query!");
    }
    SupertaskUtils::deleteSupertask($QUERY[UQueryTask::SUPERTASK_ID]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setSupertaskName($QUERY) {
    if (!isset($QUERY[UQueryTask::SUPERTASK_ID]) || !isset($QUERY[UQueryTask::SUPERTASK_NAME])) {
      throw new HTException("Invalid query!");
    }
    SupertaskUtils::renameSupertask($QUERY[UQueryTask::SUPERTASK_ID], $QUERY[UQueryTask::SUPERTASK_NAME]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function bulkSupertask($QUERY) {
    $toCheck = [
      UQueryTask::SUPERTASK_NAME,
      UQueryTask::TASK_CPU_ONLY,
      UQueryTask::TASK_SMALL,
      UQueryTask::TASK_CRACKER_TYPE,
      UQueryTask::TASK_BENCHTYPE,
      UQueryTask::TASK_ATTACKCMD,
      UQueryTask::TASK_BASEFILES,
      UQueryTask::TASK_ITERFILES
    ];
    foreach ($toCheck as $input) {
      if (!isset($QUERY[$input])) {
        throw new HTException("Invalid query (missing $input)!");
      }
    }
    SupertaskUtils::bulkSupertask(
      $QUERY[UQueryTask::SUPERTASK_NAME],
      $QUERY[UQueryTask::TASK_ATTACKCMD],
      $QUERY[UQueryTask::TASK_CPU_ONLY],
      $QUERY[UQueryTask::TASK_SMALL],
      $QUERY[UQueryTask::TASK_CRACKER_TYPE],
      $QUERY[UQueryTask::TASK_BENCHTYPE],
      $QUERY[UQueryTask::TASK_BASEFILES],
      $QUERY[UQueryTask::TASK_ITERFILES],
      $this->user
    );
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function importSupertask($QUERY) {
    $toCheck = [
      UQueryTask::SUPERTASK_NAME,
      UQueryTask::TASK_CPU_ONLY,
      UQueryTask::TASK_SMALL,
      UQueryTask::TASK_CRACKER_TYPE,
      UQueryTask::MASKS,
      UQueryTask::TASK_OPTIMIZED,
      UQueryTask::TASK_BENCHTYPE
    ];
    foreach ($toCheck as $input) {
      if (!isset($QUERY[$input])) {
        throw new HTException("Invalid query (missing $input)!");
      }
    }
    SupertaskUtils::importSupertask(
      $QUERY[UQueryTask::SUPERTASK_NAME],
      $QUERY[UQueryTask::TASK_CPU_ONLY],
      $QUERY[UQueryTask::TASK_SMALL],
      $QUERY[UQueryTask::TASK_OPTIMIZED],
      $QUERY[UQueryTask::TASK_CRACKER_TYPE],
      $QUERY[UQueryTask::MASKS],
      $QUERY[UQueryTask::TASK_BENCHTYPE]
    );
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function createSupertask($QUERY) {
    if (!isset($QUERY[UQueryTask::SUPERTASK_NAME]) || !isset($QUERY[UQueryTask::PRETASKS])) {
      throw new HTException("Invalid query!");
    }
    SupertaskUtils::createSupertask($QUERY[UQueryTask::SUPERTASK_NAME], $QUERY[UQueryTask::PRETASKS]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function getSupertask($QUERY) {
    if (!isset($QUERY[UQueryTask::SUPERTASK_ID])) {
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
  private function listSupertasks($QUERY) {
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
}