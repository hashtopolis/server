<?php

use DBA\CrackerBinaryType;
use DBA\Supertask;
use DBA\SupertaskPretask;
use DBA\QueryFilter;
use DBA\Pretask;
use DBA\JoinFilter;
use DBA\TaskWrapper;
use DBA\Task;
use DBA\OrderFilter;
use DBA\User;

class SupertaskUtils {
  /**
   * @param int $supertaskId
   * @param string $newName
   * @throws HTException
   */
  public static function renameSupertask($supertaskId, $newName) {
    global $FACTORIES;
    
    $supertask = SupertaskUtils::getSupertask($supertaskId);
    $name = htmlentities($newName, ENT_QUOTES, "UTF-8");
    $supertask->setSupertaskName($name);
    $FACTORIES::getSupertaskFactory()->update($supertask);
  }
  
  /**
   * @param int $supertaskId
   * @throws HTException
   */
  public static function deleteSupertask($supertaskId) {
    global $FACTORIES;
    
    $supertask = SupertaskUtils::getSupertask($supertaskId);
    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    
    $qF = new QueryFilter(SupertaskPretask::SUPERTASK_ID, $supertask->getId(), "=", $FACTORIES::getSupertaskPretaskFactory());
    $jF = new JoinFilter($FACTORIES::getSupertaskPretaskFactory(), Pretask::PRETASK_ID, SupertaskPretask::PRETASK_ID);
    $joinedTasks = $FACTORIES::getPretaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    
    $FACTORIES::getSupertaskPretaskFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    
    for ($i = 0; $i < sizeof($joinedTasks[$FACTORIES::getPretaskFactory()->getModelName()]); $i++) {
      /** @var $task Pretask */
      $task = $joinedTasks[$FACTORIES::getPretaskFactory()->getModelName()][$i];
      if ($task->getIsMaskImport() == 1) {
        $FACTORIES::getPretaskFactory()->delete($task);
      }
    }
    
    $FACTORIES::getSupertaskFactory()->delete($supertask);
    $FACTORIES::getAgentFactory()->getDB()->commit();
  }
  
  /**
   * @param int $taskWrapperId
   * @param User $user
   * @return Task[]
   */
  public static function getRunningSubtasks($taskWrapperId) {
    global $FACTORIES;
    
    $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapperId, "=");
    $oF = new OrderFilter(Task::PRIORITY, "DESC");
    return $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => $oF));
  }
  
  /**
   * @param int $taskWrapperId
   * @param User $user
   * @throws HTException
   * @return TaskWrapper
   */
  public static function getRunningSupertask($taskWrapperId, $user) {
    global $FACTORIES;
    
    $supertask = $FACTORIES::getTaskWrapperFactory()->get($taskWrapperId);
    if ($supertask == null) {
      throw new HTException("Invalid taskwrapper ID!");
    }
    else if (!AccessUtils::userCanAccessTask($supertask, $user)) {
      throw new HTException("No access to this task!");
    }
    return $supertask;
  }
  
  /**
   * @return Supertask[]
   */
  public static function getAllSupertasks() {
    global $FACTORIES;
    
    $oF = new OrderFilter(Supertask::SUPERTASK_ID, "ASC");
    return $FACTORIES::getSupertaskFactory()->filter(array($FACTORIES::ORDER => $oF));
  }
  
  /**
   * @param int $supertaskId
   * @return Pretask[]
   */
  public static function getPretasksOfSupertask($supertaskId) {
    global $FACTORIES;
    
    $oF = new OrderFilter(Pretask::PRIORITY, "DESC", $FACTORIES::getPretaskFactory());
    $qF = new QueryFilter(SupertaskPretask::SUPERTASK_ID, $supertaskId, "=");
    $jF = new JoinFilter($FACTORIES::getSupertaskPretaskFactory(), Pretask::PRETASK_ID, SupertaskPretask::PRETASK_ID);
    $joined = $FACTORIES::getPretaskFactory()->filter(array($FACTORIES::ORDER => $oF, $FACTORIES::JOIN => $jF, $FACTORIES::FILTER => $qF));
    return $joined[$FACTORIES::getPretaskFactory()->getModelName()];
  }
  
  /**
   * @param int $supertaskId
   * @throws HTException
   * @return Supertask
   */
  public static function getSupertask($supertaskId) {
    global $FACTORIES;
    
    $supertask = $FACTORIES::getSupertaskFactory()->get($supertaskId);
    if ($supertask == null) {
      throw new HTException("Invalid supertask ID!");
    }
    return $supertask;
  }
  
  /**
   * @param int $supertaskId
   * @param int $hashlistId
   * @param int $crackerId
   * @throws HTException
   */
  public static function runSupertask($supertaskId, $hashlistId, $crackerId) {
    global $FACTORIES;
    
    $supertask = $FACTORIES::getSupertaskFactory()->get($supertaskId);
    if ($supertask == null) {
      throw new HTException("Invalid supertask ID!");
    }
    $hashlist = $FACTORIES::getHashlistFactory()->get($hashlistId);
    if ($hashlist == null) {
      throw new HTException("Invalid hashlist ID!");
    }
    $cracker = $FACTORIES::getCrackerBinaryFactory()->get($crackerId);
    if ($cracker == null) {
      throw new HTException("Invalid cracker ID!");
    }
    $qF = new QueryFilter(SupertaskPretask::SUPERTASK_ID, $supertask->getId(), "=", $FACTORIES::getSupertaskPretaskFactory());
    $jF = new JoinFilter($FACTORIES::getSupertaskPretaskFactory(), Pretask::PRETASK_ID, SupertaskPretask::PRETASK_ID);
    $joined = $FACTORIES::getPretaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    /** @var $pretasks Pretask[] */
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
      if ($cracker->getCrackerBinaryTypeId() != $pretask->getCrackerBinaryTypeId()) {
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
  }
  
  /**
   * @param string $name
   * @param int[] $pretasks
   * @throws HTException
   */
  public static function createSupertask($name, $pretasks) {
    global $FACTORIES;
    
    if (!is_array($pretasks) || sizeof($pretasks) == 0) {
      throw new HTException("Cannot create empty supertask!");
    }
    $name = htmlentities($name, ENT_QUOTES, "UTF-8");
    $tasks = [];
    foreach ($pretasks as $pretaskId) {
      $pretask = $FACTORIES::getPretaskFactory()->get($pretaskId);
      if ($pretask == null) {
        throw new HTException("Invalid preconfigured task ID ($pretaskId)!");
      }
      $tasks[] = $pretask;
    }
    
    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $supertask = new Supertask(0, $name);
    $supertask = $FACTORIES::getSupertaskFactory()->save($supertask);
    
    foreach ($tasks as $pretask) {
      $supertaskPretask = new SupertaskPretask(0, $supertask->getId(), $pretask->getId());
      $FACTORIES::getSupertaskPretaskFactory()->save($supertaskPretask);
    }
    
    $FACTORIES::getAgentFactory()->getDB()->commit();
  }
  
  /**
   * @param string $name
   * @param boolean $isCpuOnly
   * @param boolean $isSmall
   * @param boolean $useOptimized
   * @param int $crackerBinaryTypeId
   * @param array $masks
   * @throws HTException
   */
  public static function importSupertask($name, $isCpuOnly, $isSmall, $useOptimized, $crackerBinaryTypeId, $masks) {
    global $FACTORIES;
    
    $name = htmlentities($name, ENT_QUOTES, "UTF-8");
    $isCpuOnly = ($isCpuOnly) ? 1 : 0;
    $isSmall = ($isSmall) ? 1 : 0;
    $useOptimized = ($useOptimized) ? true : false;
    $crackerBinaryType = $FACTORIES::getCrackerBinaryTypeFactory()->get($crackerBinaryTypeId);
    if ($crackerBinaryType == null) {
      throw new HTException("Invalid cracker type ID!");
    }
    else if (!is_array($masks)) {
      throw new HTException("Masks need to be provided as array!");
    }
    SupertaskUtils::prepareImportMasks($masks);
    if (sizeof($masks) == 0) {
      throw new HTException("No valid masks found!");
    }
    
    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $pretasks = SupertaskUtils::createImportPretasks($masks, $isSmall, $isCpuOnly, $crackerBinaryType, $useOptimized);
    
    $supertask = new Supertask(0, $name);
    $supertask = $FACTORIES::getSupertaskFactory()->save($supertask);
    foreach ($pretasks as $preTask) {
      $relation = new SupertaskPretask(0, $supertask->getId(), $preTask->getId());
      $FACTORIES::getSupertaskPretaskFactory()->save($relation);
    }
    $FACTORIES::getAgentFactory()->getDB()->commit();
  }
  
  /**
   * @param $masks
   * @param $isSmall
   * @param $isCpu
   * @param $crackerBinaryType CrackerBinaryType
   * @param bool $useOptimized
   * @return array
   */
  private static function createImportPretasks($masks, $isSmall, $isCpu, $crackerBinaryType, $useOptimized = false) {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;
    
    // create the preconf tasks
    $preTasks = array();
    $priority = sizeof($masks) + 1;
    foreach ($masks as $mask) {
      $pattern = $mask[sizeof($mask) - 1];
      $cmd = "";
      switch (sizeof($mask)) {
        case 5:
          $cmd = " -4 " . $mask[3] . $cmd;
        case 4:
          $cmd = " -3 " . $mask[2] . $cmd;
        case 3:
          $cmd = " -2 " . $mask[1] . $cmd;
        case 2:
          $cmd = " -1 " . $mask[0] . $cmd;
        case 1:
          $cmd .= " $pattern";
      }
      if ($useOptimized) {
        $cmd .= " -O ";
      }
      $cmd = str_replace("COMMA_PLACEHOLDER", "\\,", $cmd);
      $cmd = str_replace("HASH_PLACEHOLDER", "\\#", $cmd);
      $preTaskName = implode(",", $mask);
      $preTaskName = str_replace("COMMA_PLACEHOLDER", "\\,", $preTaskName);
      $preTaskName = str_replace("HASH_PLACEHOLDER", "\\#", $preTaskName);
      
      $pretask = new Pretask(0, $preTaskName, $CONFIG->getVal(DConfig::HASHLIST_ALIAS) . " -a 3 " . $cmd, $CONFIG->getVal(DConfig::CHUNK_DURATION), $CONFIG->getVal(DConfig::STATUS_TIMER), "", $isSmall, $isCpu, 0, $priority, 1, $crackerBinaryType->getId());
      $pretask = $FACTORIES::getPretaskFactory()->save($pretask);
      $preTasks[] = $pretask;
      $priority--;
    }
    return $preTasks;
  }
  
  /**
   * @param array $masks
   */
  private static function prepareImportMasks(&$masks) {
    for ($i = 0; $i < sizeof($masks); $i++) {
      if (strlen($masks[$i]) == 0) {
        unset($masks[$i]);
        continue;
      }
      $mask = str_replace("\\,", "COMMA_PLACEHOLDER", $masks[$i]);
      $mask = str_replace("\\#", "HASH_PLACEHOLDER", $mask);
      if (strpos($mask, "#") !== false) {
        $mask = substr($mask, 0, strpos($mask, "#"));
      }
      $mask = explode(",", $mask);
      if (sizeof($mask) > 5) {
        unset($masks[$i]);
        continue;
      }
      $masks[$i] = $mask;
    }
  }
}