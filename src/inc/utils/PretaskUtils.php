<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\inc\DataSet;
use Hashtopolis\dba\models\Pretask;
use Hashtopolis\dba\models\FilePretask;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\OrderFilter;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\SupertaskPretask;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DNotificationType;
use Hashtopolis\inc\defines\DPayloadKeys;
use Hashtopolis\inc\defines\DTaskTypes;
use Hashtopolis\inc\handlers\NotificationHandler;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\Util;

class PretaskUtils {
  /**
   * @param int $pretaskId
   * @param string $attackCmd
   * @throws HTException
   * @throws Exception
   */
  public static function changeAttack(int $pretaskId, string $attackCmd): void {
    $pretask = PretaskUtils::getPretask($pretaskId);
    if (!str_contains($attackCmd, SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS))) {
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
  public static function getFromTask(Task $copy): Pretask {
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
   * @throws Exception
   */
  public static function getDefault(): Pretask {
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
   * @throws Exception
   */
  public static function setCpuOnlyTask(int $pretaskId, int $isCpuOnly): void {
    $pretask = PretaskUtils::getPretask($pretaskId);
    if ($isCpuOnly < 0 || $isCpuOnly > 1) {
      throw new HTException("Invalid cpuOnly value!");
    }
    Factory::getPretaskFactory()->set($pretask, Pretask::IS_CPU_TASK, $isCpuOnly);
  }
  
  /**
   * @param int $pretaskId
   * @param int $isSmall
   * @throws HTException
   * @throws Exception
   */
  public static function setSmallTask(int $pretaskId, int $isSmall): void {
    $pretask = PretaskUtils::getPretask($pretaskId);
    if ($isSmall < 0 || $isSmall > 1) {
      throw new HTException("Invalid cpuOnly value!");
    }
    Factory::getPretaskFactory()->set($pretask, Pretask::IS_SMALL, $isSmall);
  }
  
  /**
   * @param int $pretaskId
   * @param int $priority
   * @throws HTException
   * @throws Exception
   */
  public static function setPriority(int $pretaskId, int $priority): void {
    $pretask = PretaskUtils::getPretask($pretaskId);
    Factory::getPretaskFactory()->set($pretask, Pretask::PRIORITY, $priority);
  }
  
  /**
   * @param int $pretaskId
   * @param int $maxAgents
   * @throws HTException
   * @throws Exception
   */
  public static function setMaxAgents(int $pretaskId, int $maxAgents): void {
    $pretask = PretaskUtils::getPretask($pretaskId);
    if ($maxAgents < 0) {
      throw new HTException("Max agents cannot be negative!");
    }
    Factory::getPretaskFactory()->set($pretask, Pretask::MAX_AGENTS, $maxAgents);
  }
  
  /**
   * @param int $pretaskId
   * @param string $color
   * @throws HTException
   * @throws Exception
   */
  public static function setColor(int $pretaskId, string $color): void {
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
   * @throws Exception
   */
  public static function setChunkTime(int $pretaskId, int $chunkTime): void {
    $pretask = PretaskUtils::getPretask($pretaskId);
    if ($chunkTime <= 0) {
      throw new HTException("Invalid chunk time!");
    }
    Factory::getPretaskFactory()->set($pretask, Pretask::CHUNK_TIME, $chunkTime);
  }
  
  /**
   * @param int $pretaskId
   * @param string $newName
   * @throws HTException
   * @throws Exception
   */
  public static function renamePretask(int $pretaskId, string $newName): void {
    $pretask = PretaskUtils::getPretask($pretaskId);
    if (strlen($newName) == 0) {
      throw new HTException("Name cannot be empty!");
    }
    Factory::getPretaskFactory()->set($pretask, Pretask::TASK_NAME, htmlentities($newName, ENT_QUOTES, "UTF-8"));
  }
  
  /**
   * @param int $pretaskId
   * @throws HTException
   * @throws Exception
   */
  public static function deletePretask(int $pretaskId): void {
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
   * @throws Exception
   */
  public static function getPretasks(bool $includeMaskImports = false): array {
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
   * @throws Exception
   */
  public static function getPretask(int $pretaskId): Pretask {
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
   * @throws Exception
   */
  public static function runPretask(int $pretaskId, int $hashlistId, string $name, int $crackerBinaryId): void {
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
    $taskWrapper = new TaskWrapper(null, $pretask->getPriority(), $pretask->getMaxAgents(), DTaskTypes::NORMAL, $hashlist->getId(), $hashlist->getAccessGroupId(), "", 0, 0);
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
   * @param array|null $files
   * @param int $crackerBinaryTypeId
   * @param int|null $maxAgents
   * @param int $priority
   * @return Pretask
   * @throws HttpError
   * @throws Exception
   */
  public static function createPretask(string $name, string $cmdLine, int $chunkTime, int $statusTimer, string $color, int $cpuOnly, int $isSmall, int $benchmarkType, array|null $files, int $crackerBinaryTypeId, int|null $maxAgents, int $priority = 0): Pretask {
    $crackerBinaryType = Factory::getCrackerBinaryTypeFactory()->get($crackerBinaryTypeId);
    
    if (strlen($name) == 0) {
      throw new HttpError("Name cannot be empty!");
    }
    else if (!str_contains($cmdLine, SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS))) {
      throw new HttpError("The attack command does not contain the hashlist alias!");
    }
    else if (strlen($cmdLine) > 65535) {
      throw new HttpError("Attack command is too long (max 65535 characters)!");
    }
    else if (Util::containsBlacklistedChars($cmdLine)) {
      throw new HttpError("The command must contain no blacklisted characters!");
    }
    else if ($crackerBinaryType == null) {
      throw new HttpError("Invalid cracker binary type!");
    }
    $maxAgents = intval($maxAgents);
    if (strlen($color) > 0 && preg_match("/[0-9A-Fa-f]{6}/", $color) == 0) {
      $color = "";
    }
    else if ($cpuOnly < 0 || $cpuOnly > 1) {
      throw new HttpError("Invalid cpuOnly value!");
    }
    else if ($isSmall < 0 || $isSmall > 1) {
      throw new HttpError("Invalid isSmall value!");
    }
    else if ($benchmarkType < 0 || $benchmarkType > 1) {
      throw new HttpError("Invalid benchmark type!");
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
    $files = $files ?? [];
    foreach ($files as $fileId) {
      $file = Factory::getFileFactory()->get($fileId);
      if ($file !== null) {
        $filePretask = new FilePretask(null, $file->getId(), $pretask->getId());
        Factory::getFilePretaskFactory()->save($filePretask);
      }
    }
    return $pretask;
  }
}

