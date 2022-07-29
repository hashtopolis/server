<?php

use DBA\Pretask;
use DBA\FilePretask;
use DBA\TaskWrapper;
use DBA\Task;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\SupertaskPretask;
use DBA\Factory;

class PretaskUtils {
  /**
   * @param int $pretaskId
   * @param string $attackCmd
   * @throws HTException
   */
  public static function changeAttack($pretaskId, $attackCmd) {
    $pretask = PretaskUtils::getPretask($pretaskId);
    if (strpos($attackCmd, SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS)) === false) {
      throw new HTException("The attack command does not contain the hashlist alias!");
    }
    else if (Util::containsBlacklistedChars($attackCmd)) {
      throw new HTException("The command must contain no blacklisted characters!");
    }
    Factory::getPretaskFactory()->set($pretask, Pretask::ATTACK_CMD, $attackCmd);
  }
  
  /**
   * @param Task $copy
   * @return Pretask
   */
  public static function getFromTask($copy) {
    return new Pretask(
      null,
      $copy->getTaskName(),
      $copy->getAttackCmd(),
      $copy->getChunkTime(),
      $copy->getStatusTimer(),
      $copy->getColor(),
      $copy->getIsSmall(),
      $copy->getIsCpuTask(),
      $copy->getUseNewBench(),
      0,
      $copy->getMaxAgents(),
      0,
      $copy->getCrackerBinaryTypeId()
    );
  }
  
  /**
   * @return Pretask
   */
  public static function getDefault() {
    return new Pretask(
      null,
      '',
      SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS) . " ",
      SConfig::getInstance()->getVal(DConfig::CHUNK_DURATION),
      SConfig::getInstance()->getVal(DConfig::STATUS_TIMER),
      '',
      0,
      0,
      SConfig::getInstance()->getVal(DConfig::DEFAULT_BENCH),
      0,
      0,
      0,
      0
    );
  }
  
  /**
   * @param int $pretaskId
   * @param int $isCpuOnly
   * @throws HTException
   */
  public static function setCpuOnlyTask($pretaskId, $isCpuOnly) {
    $pretask = PretaskUtils::getPretask($pretaskId);
    if (is_bool($isCpuOnly)) {
      $isCpuOnly = ($isCpuOnly) ? 1 : 0;
    }
    if (!is_numeric($isCpuOnly) || $isCpuOnly < 0 || $isCpuOnly > 1) {
      throw new HTException("Invalid boolean value!");
    }
    Factory::getPretaskFactory()->set($pretask, Pretask::IS_CPU_TASK, $isCpuOnly);
  }
  
  /**
   * @param int $pretaskId
   * @param int $isSmall
   * @throws HTException
   */
  public static function setSmallTask($pretaskId, $isSmall) {
    $pretask = PretaskUtils::getPretask($pretaskId);
    if (is_bool($isSmall)) {
      $isSmall = ($isSmall) ? 1 : 0;
    }
    if (!is_numeric($isSmall) || $isSmall < 0 || $isSmall > 1) {
      throw new HTException("Invalid boolean value!");
    }
    Factory::getPretaskFactory()->set($pretask, Pretask::IS_SMALL, $isSmall);
  }
  
  /**
   * @param int $pretaskId
   * @param int $priority
   * @throws HTException
   */
  public static function setPriority($pretaskId, $priority) {
    $pretask = PretaskUtils::getPretask($pretaskId);
    if (!is_numeric($priority)) {
      throw new HTException("Priority needs to be a number!");
    }
    Factory::getPretaskFactory()->set($pretask, Pretask::PRIORITY, intval($priority));
  }

    /**
   * @param int $pretaskId
   * @param int $maxAgents
   * @throws HTException
   */
  public static function setMaxAgents($pretaskId, $maxAgents) {
    $pretask = PretaskUtils::getPretask($pretaskId);
    if (!is_numeric($maxAgents)) {
      throw new HTException("Max agents needs to be a number!");
    }
    $maxAgents = intval($maxAgents);
    Factory::getPretaskFactory()->set($pretask, Pretask::MAX_AGENTS, $maxAgents);
  }
  
  /**
   * @param int $pretaskId
   * @param string $color
   * @throws HTException
   */
  public static function setColor($pretaskId, $color) {
    $pretask = PretaskUtils::getPretask($pretaskId);
    if (strlen($color) > 0 && preg_match("/[0-9A-Fa-f]{6}/", $color) == 0) {
      throw new HTException("Invalid color!");
    }
    Factory::getPretaskFactory()->set($pretask, Pretask::COLOR, $color);
  }
  
  /**
   * @param int $pretaskId
   * @param int $chunkTime
   * @throws HTException
   */
  public static function setChunkTime($pretaskId, $chunkTime) {
    $pretask = PretaskUtils::getPretask($pretaskId);
    $chunkTime = intval($chunkTime);
    if ($chunkTime <= 0) {
      throw new HTException("Invalid chunk time!");
    }
    Factory::getPretaskFactory()->set($pretask, Pretask::CHUNK_TIME, $chunkTime);
  }
  
  /**
   * @param int $pretaskId
   * @param string $newName
   * @throws HTException
   */
  public static function renamePretask($pretaskId, $newName) {
    $pretask = PretaskUtils::getPretask($pretaskId);
    if (strlen($newName) == 0) {
      throw new HTException("Name cannot be empty!");
    }
    Factory::getPretaskFactory()->set($pretask, Pretask::TASK_NAME, htmlentities($newName, ENT_QUOTES, "UTF-8"));
  }
  
  /**
   * @param int $pretaskId
   * @throws HTException
   */
  public static function deletePretask($pretaskId) {
    $pretask = PretaskUtils::getPretask($pretaskId);
    
    // delete connections to supertasks
    $qF = new QueryFilter(SupertaskPretask::PRETASK_ID, $pretask->getId(), "=");
    Factory::getSupertaskPretaskFactory()->massDeletion([Factory::FILTER => $qF]);
    
    // delete connections to files
    $qF = new QueryFilter(FilePretask::PRETASK_ID, $pretask->getId(), "=");
    Factory::getFilePretaskFactory()->massDeletion([Factory::FILTER => $qF]);
    
    Factory::getPretaskFactory()->delete($pretask);
  }
  
  /**
   * @param boolean $includeMaskImports
   * @return Pretask[]
   */
  public static function getPretasks($includeMaskImports = false) {
    $oF = new OrderFilter(Pretask::PRIORITY, "DESC");
    if ($includeMaskImports) {
      $pretasks = Factory::getPretaskFactory()->filter([Factory::ORDER => $oF]);
    }
    else {
      $qF = new QueryFilter(Pretask::IS_MASK_IMPORT, 0, "=");
      $pretasks = Factory::getPretaskFactory()->filter([Factory::ORDER => $oF, Factory::FILTER => $qF]);
    }
    return $pretasks;
  }
  
  /**
   * @param int $pretaskId
   * @return Pretask
   * @throws HTException
   */
  public static function getPretask($pretaskId) {
    $pretask = Factory::getPretaskFactory()->get($pretaskId);
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
    $pretask = Factory::getPretaskFactory()->get($pretaskId);
    if ($pretask == null) {
      throw new HTException("Invalid preconfigured task ID!");
    }
    $hashlist = Factory::getHashlistFactory()->get($hashlistId);
    if ($hashlist == null) {
      throw new HTException("Invalid hashlist ID!");
    }
    $name = htmlentities($name, ENT_QUOTES, "UTF-8");
    if (strlen($name) == 0) {
      $name = "Task_" . $hashlist->getId() . "_" . date("Ymd_Hi");
    }
    $cracker = Factory::getCrackerBinaryFactory()->get($crackerBinaryId);
    if ($cracker == null) {
      throw new HTException("Invalid cracker ID!");
    }
    else if ($pretask->getCrackerBinaryTypeId() != $cracker->getCrackerBinaryTypeId()) {
      throw new HTException("Provided cracker does not match the type of the pretask!");
    }
    
    Factory::getAgentFactory()->getDB()->beginTransaction();
    $taskWrapper = new TaskWrapper(null, $pretask->getPriority(), DTaskTypes::NORMAL, $hashlist->getId(), $hashlist->getAccessGroupId(), "", 0, 0);
    $taskWrapper = Factory::getTaskWrapperFactory()->save($taskWrapper);
    
    $task = new Task(
      null,
      $name,
      $pretask->getAttackCmd(),
      $pretask->getChunkTime(),
      $pretask->getStatusTimer(),
      0,
      0,
      $pretask->getPriority(),
      $pretask->getMaxAgents(),
      $pretask->getColor(),
      $pretask->getIsSmall(),
      $pretask->getIsCpuTask(),
      $pretask->getUseNewBench(),
      0,
      $cracker->getId(),
      $cracker->getCrackerBinaryTypeId(),
      $taskWrapper->getId(),
      0,
      '',
      0,
      0,
      0,
      0,
      ''
    );
    $task = Factory::getTaskFactory()->save($task);
    TaskUtils::copyPretaskFiles($pretask, $task);
    Factory::getAgentFactory()->getDB()->commit();
    
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
   * @param int $maxAgents
   * @param int $priority
   * @throws HTException
   */
  public static function createPretask($name, $cmdLine, $chunkTime, $statusTimer, $color, $cpuOnly, $isSmall, $benchmarkType, $files, $crackerBinaryTypeId, $maxAgents, $priority = 0) {
    $crackerBinaryType = Factory::getCrackerBinaryTypeFactory()->get($crackerBinaryTypeId);
    
    if (strlen($name) == 0) {
      throw new HTException("Name cannot be empty!");
    }
    else if (strpos($cmdLine, SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS)) === false) {
      throw new HTException("The attack command does not contain the hashlist alias!");
    }
    else if (strlen($attackCmd) > 256) {
      throw new HTException("Attack command is too long (max 256 characters)!");
    }
    else if (Util::containsBlacklistedChars($cmdLine)) {
      throw new HTException("The command must contain no blacklisted characters!");
    }
    else if ($crackerBinaryType == null) {
      throw new HTException("Invalid cracker binary type!");
    }
    $chunkTime = intval($chunkTime);
    $statusTimer = intval($statusTimer);
    $maxAgents = intval($maxAgents);
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
      $chunkTime = SConfig::getInstance()->getVal(DConfig::CHUNK_DURATION);
    }
    else if ($statusTimer <= 0) {
      $statusTimer = SConfig::getInstance()->getVal(DConfig::STATUS_TIMER);
    }
    $pretask = new Pretask(null,
      htmlentities($name, ENT_QUOTES, "UTF-8"),
      $cmdLine,
      $chunkTime,
      $statusTimer,
      $color,
      $isSmall,
      $cpuOnly,
      $benchmarkType,
      $priority,
      $maxAgents,
      0,
      $crackerBinaryType->getId()
    );
    $pretask = Factory::getPretaskFactory()->save($pretask);
    
    // handle files
    foreach ($files as $fileId) {
      $file = Factory::getFileFactory()->get($fileId);
      if ($file !== null) {
        $filePretask = new FilePretask(null, $file->getId(), $pretask->getId());
        Factory::getFilePretaskFactory()->save($filePretask);
      }
    }
  }
}

