<?php

use DBA\Pretask;
use DBA\FilePretask;
use DBA\TaskWrapper;
use DBA\Task;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\SupertaskPretask;

class PretaskUtils {
  /**
   * @param int $pretaskId
   * @param int $isCpuOnly
   * @throws HTException
   */
  public static function setCpuOnlyTask($pretaskId, $isCpuOnly) {
    global $FACTORIES;
    
    $pretask = PretaskUtils::getPretask($pretaskId);
    $isCpuOnly = intval($isCpuOnly);
    if ($isCpuOnly < 0 || $isCpuOnly > 1) {
      $isCpuOnly = 0;
    }
    $pretask->setIsCpuTask($isCpuOnly);
    $FACTORIES::getPretaskFactory()->update($pretask);
  }
  
  /**
   * @param int $pretaskId
   * @param int $isSmall
   * @throws HTException
   */
  public static function setSmallTask($pretaskId, $isSmall) {
    global $FACTORIES;
    
    $pretask = PretaskUtils::getPretask($pretaskId);
    $isSmall = intval($isSmall);
    if ($isSmall < 0 || $isSmall > 1) {
      $isSmall = 0;
    }
    $pretask->setIsSmall($isSmall);
    $FACTORIES::getPretaskFactory()->update($pretask);
  }
  
  /**
   * @param int $pretaskId
   * @param int $priority
   * @throws HTException
   */
  public static function setPriority($pretaskId, $priority) {
    global $FACTORIES;
    
    $pretask = PretaskUtils::getPretask($pretaskId);
    $priority = intval($priority);
    if ($priority < 0) {
      $priority = 0;
    }
    $pretask->setPriority($priority);
    $FACTORIES::getPretaskFactory()->update($pretask);
  }
  
  /**
   * @param int $pretaskId
   * @param string $color
   * @throws HTException
   */
  public static function setColor($pretaskId, $color) {
    global $FACTORIES;
    
    $pretask = PretaskUtils::getPretask($pretaskId);
    if (strlen($color) > 0 && preg_match("/[0-9A-Fa-f]{6}/", $color) == 0) {
      $color = "";
    }
    $pretask->setColor($color);
    $FACTORIES::getPretaskFactory()->update($pretask);
  }
  
  /**
   * @param int $pretaskId
   * @param int $chunkTime
   * @throws HTException
   */
  public static function setChunkTime($pretaskId, $chunkTime) {
    global $FACTORIES;
    
    $pretask = PretaskUtils::getPretask($pretaskId);
    $chunkTime = intval($chunkTime);
    if ($chunkTime <= 0) {
      throw new HTException("Invalid chunk time!");
    }
    $pretask->setChunkTime($chunkTime);
    $FACTORIES::getPretaskFactory()->update($pretask);
  }
  
  /**
   * @param int $pretaskId
   * @param string $newName
   * @throws HTException
   */
  public static function renamePretask($pretaskId, $newName) {
    global $FACTORIES;
    
    $pretask = PretaskUtils::getPretask($pretaskId);
    if (strlen($newName) == 0) {
      throw new HTException("Name cannot be empty!");
    }
    $pretask->setTaskName(htmlentities($newName, ENT_QUOTES, "UTF-8"));
    $FACTORIES::getPretaskFactory()->update($pretask);
  }
  
  /**
   * @param int $pretaskId
   * @throws HTException
   */
  public static function deletePretask($pretaskId) {
    global $FACTORIES;
    
    $pretask = PretaskUtils::getPretask($pretaskId);
    
    // delete connections to supertasks
    $qF = new QueryFilter(SupertaskPretask::PRETASK_ID, $pretask->getId(), "=");
    $FACTORIES::getSupertaskPretaskFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    
    // delete connections to files
    $qF = new QueryFilter(FilePretask::PRETASK_ID, $pretask->getId(), "=");
    $FACTORIES::getFilePretaskFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    
    $FACTORIES::getPretaskFactory()->delete($pretask);
  }
  
  /**
   * @param boolean $includeMaskImports
   * @return Pretask[]
   */
  public static function getPretasks($includeMaskImports = false) {
    global $FACTORIES;
    
    $oF = new OrderFilter(Pretask::PRIORITY, "DESC");
    if ($includeMaskImports) {
      $pretasks = $FACTORIES::getPretaskFactory()->filter(array($FACTORIES::ORDER => $oF));
    }
    else {
      $qF = new QueryFilter(Pretask::IS_MASK_IMPORT, 0, "=");
      $pretasks = $FACTORIES::getPretaskFactory()->filter(array($FACTORIES::ORDER => $oF, $FACTORIES::FILTER => $qF));
    }
    return $pretasks;
  }
  
  /**
   * @param int $pretaskId
   * @throws HTException
   * @return Pretask
   */
  public static function getPretask($pretaskId) {
    global $FACTORIES;
    
    $pretask = $FACTORIES::getPretaskFactory()->get($pretaskId);
    if ($pretask == null) {
      throw new HTException("Invalid preconfigured task!");
    }
    return $pretask;
  }
  
  /**
   * @param int $pretaskId
   * @param int $hashlistId
   * @param string $name
   * @param int $crackerBinaryId
   * @throws HTException
   */
  public static function runPretask($pretaskId, $hashlistId, $name, $crackerBinaryId) {
    global $FACTORIES;
    
    $pretask = $FACTORIES::getPretaskFactory()->get($pretaskId);
    if ($pretask == null) {
      throw new HTException("Invalid preconfigured task ID!");
    }
    $hashlist = $FACTORIES::getHashlistFactory()->get($hashlistId);
    if ($hashlist == null) {
      throw new HTException("Invalid hashlist ID!");
    }
    $name = htmlentities($name, ENT_QUOTES, "UTF-8");
    if (strlen($name) == 0) {
      $name = "Task_" . $hashlist->getId() . "_" . date("Ymd_Hi");
    }
    $cracker = $FACTORIES::getCrackerBinaryFactory()->get($crackerBinaryId);
    if ($cracker == null) {
      throw new HTException("Invalid cracker ID!");
    }
    else if ($pretask->getCrackerBinaryTypeId() != $cracker->getCrackerBinaryTypeId()) {
      throw new HTException("Provided cracker does not match the type of the pretask!");
    }
    
    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $taskWrapper = new TaskWrapper(0, $pretask->getPriority(), DTaskTypes::NORMAL, $hashlist->getId(), $hashlist->getAccessGroupId(), "", 0);
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
      $taskWrapper->getId(),
      0,
      0,
      '',
      0,
      0
    );
    $task = $FACTORIES::getTaskFactory()->save($task);
    TaskUtils::copyPretaskFiles($pretask, $task);
    $FACTORIES::getAgentFactory()->getDB()->commit();
    
    $payload = new DataSet(array(DPayloadKeys::TASK => $task));
    NotificationHandler::checkNotifications(DNotificationType::NEW_TASK, $payload);
  }
  
  /**
   * @param string $name
   * @param string $cmdLine
   * @param int $chunkTime
   * @param int $statusTimer
   * @param string $color
   * @param int $cpuOnly
   * @param int $isSmall
   * @param int $benchmarkType
   * @param array $files
   * @param int $crackerBinaryTypeId
   * @throws HTException
   */
  public static function createPretask($name, $cmdLine, $chunkTime, $statusTimer, $color, $cpuOnly, $isSmall, $benchmarkType, $files, $crackerBinaryTypeId, $priority = 0) {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;
    
    $crackerBinaryType = $FACTORIES::getCrackerBinaryTypeFactory()->get($crackerBinaryTypeId);
    
    if (strlen($name) == 0) {
      throw new HTException("Name cannot be empty!");
    }
    else if (strpos($cmdLine, $CONFIG->getVal(DConfig::HASHLIST_ALIAS)) === false) {
      throw new HTException("The attack command does not contain the hashlist alias!");
    }
    else if (Util::containsBlacklistedChars($cmdLine)) {
      throw new HTException("The command must contain no blacklisted characters!");
    }
    else if ($crackerBinaryType == null) {
      throw new HTException("Invalid cracker binary type!");
    }
    $chunkTime = intval($chunkTime);
    $statusTimer = intval($statusTimer);
    if (strlen($color) > 0 && preg_match("/[0-9A-Fa-f]{6}/", $color) == 0) {
      $color = "";
    }
    else if ($cpuOnly < 0 || $cpuOnly > 1) {
      throw new HTException("Invalid cpuOnly value!");
    }
    else if ($isSmall < 0 || $isSmall > 1) {
      throw new HTException("Invalid isSmall value!");
    }
    else if ($benchmarkType < 0 || $benchmarkType > 1) {
      throw new HTException("Invalid benchmark type!");
    }
    else if ($chunkTime <= 0) {
      $chunkTime = $CONFIG->getVal(DConfig::CHUNK_DURATION);
    }
    else if ($statusTimer <= 0) {
      $statusTimer = $CONFIG->getVal(DConfig::STATUS_TIMER);
    }
    $pretask = new Pretask(0,
      htmlentities($name, ENT_QUOTES, "UTF-8"),
      $cmdLine,
      $chunkTime,
      $statusTimer,
      $color,
      $isSmall,
      $cpuOnly,
      $benchmarkType,
      $priority,
      0,
      $crackerBinaryType->getId()
    );
    $pretask = $FACTORIES::getPretaskFactory()->save($pretask);
    
    // handle files
    foreach ($files as $fileId) {
      $file = $FACTORIES::getFileFactory()->get($fileId);
      if ($file !== null) {
        $filePretask = new FilePretask(0, $file->getId(), $pretask->getId());
        $FACTORIES::getFilePretaskFactory()->save($filePretask);
      }
    }
  }
}

