<?php /** @noinspection PhpMissingBreakStatementInspection */

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\dba\models\Supertask;
use Hashtopolis\dba\models\SupertaskPretask;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\Pretask;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\OrderFilter;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\File;
use Hashtopolis\dba\models\FilePretask;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DTaskTypes;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\Util;

class SupertaskUtils {
  /**
   * @param string $name
   * @param string $command
   * @param bool $isCpuOnly
   * @param bool $isSmall
   * @param int $crackerBinaryTypeId
   * @param string $benchtype
   * @param string[] $basefiles
   * @param string[] $iterfiles
   * @param User $user
   * @throws HTException
   */
  public static function bulkSupertask($name, $command, $isCpuOnly, $maxAgents, $isSmall, $crackerBinaryTypeId, $benchtype, $basefiles, $iterfiles, $user): Supertask {
    $isCpuOnly = ($isCpuOnly) ? 1 : 0;
    $isSmall = ($isSmall) ? 1 : 0;
    $benchtype = ($benchtype == 'speed') ? 1 : 0;
    $crackerBinaryType = Factory::getCrackerBinaryTypeFactory()->get($crackerBinaryTypeId);
    if ($crackerBinaryType == null) {
      throw new HTException("Invalid cracker type ID!");
    }
    else if (strpos($command, SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS)) === false) {
      throw new HTException("Command line must contain hashlist alias (" . SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS) . ")!");
    }
    else if (Util::containsBlacklistedChars($command)) {
      throw new HTException("The command must contain no blacklisted characters!");
    }
    else if (!is_array($iterfiles) || sizeof($iterfiles) == 0) {
      throw new HTException("At least one file needs to be selected to iterate over!");
    }
    else if (strpos($command, "FILE") === false) {
      throw new HTException("No placeholder (FILE) for the iteration!");
    }
    
    if (!is_array($basefiles)) {
      $basefiles = [];
    }
    
    $basefilesChecked = [];
    foreach ($basefiles as $basefile) {
      $file = Factory::getFileFactory()->get($basefile);
      if ($file == null) {
        throw new HTException("Invalid file selected!");
      }
      else if (!AccessUtils::userCanAccessFile($file, $user)) {
        throw new HTException("For at least one file you don't have enough access rights!");
      }
      $basefilesChecked[] = $file;
    }
    
    $iterfilesChecked = [];
    foreach ($iterfiles as $iterfile) {
      $file = Factory::getFileFactory()->get($iterfile);
      if ($file == null) {
        throw new HTException("Invalid file selected!");
      }
      else if (!AccessUtils::userCanAccessFile($file, $user)) {
        throw new HTException("For at least one file you don't have enough access rights!");
      }
      $iterfilesChecked[] = $file;
    }
    
    Factory::getAgentFactory()->getDB()->beginTransaction();
    $pretasks = SupertaskUtils::createIterationPretasks($command, $name, $basefilesChecked, $iterfilesChecked, $isSmall, $maxAgents, $isCpuOnly, $crackerBinaryType, $benchtype);
    
    $supertask = new Supertask(null, $name);
    $supertask = Factory::getSupertaskFactory()->save($supertask);
    foreach ($pretasks as $preTask) {
      $relation = new SupertaskPretask(null, $supertask->getId(), $preTask->getId());
      Factory::getSupertaskPretaskFactory()->save($relation);
    }
    Factory::getAgentFactory()->getDB()->commit();
    return $supertask;
  }
  
  /**
   * @param string $command
   * @param string $name
   * @param File[] $basefiles
   * @param File[] $iterfiles
   * @param int $isSmall
   * @param int $isCpuOnly
   * @param CrackerBinaryType $crackerBinaryType
   * @param int $benchtype
   * @return Pretask[]
   */
  public static function createIterationPretasks($command, $name, $basefiles, $iterfiles, $isSmall, $maxAgents, $isCpuOnly, $crackerBinaryType, $benchtype) {
    // create the preconf tasks
    $preTasks = array();
    $priority = sizeof($iterfiles) + 1;
    foreach ($iterfiles as $iterfile) {
      $cmd = str_replace('FILE', $iterfile->getFilename(), $command);
      $preTaskName = $name . " + " . $iterfile->getFilename();
      
      $pretask = new Pretask(
        null,
        $preTaskName,
        $cmd,
        SConfig::getInstance()->getVal(DConfig::CHUNK_DURATION),
        SConfig::getInstance()->getVal(DConfig::STATUS_TIMER),
        "",
        $isSmall,
        $isCpuOnly,
        $benchtype,
        $priority,
        $maxAgents,
        0,
        $crackerBinaryType->getId()
      );
      $pretask = Factory::getPretaskFactory()->save($pretask);
      
      // save files
      $pretaskFiles = [];
      foreach ($basefiles as $basefile) {
        $pretaskFiles[] = new FilePretask(null, $basefile->getId(), $pretask->getId());
      }
      $pretaskFiles[] = new FilePretask(null, $iterfile->getId(), $pretask->getId());
      Factory::getFilePretaskFactory()->massSave($pretaskFiles);
      
      $preTasks[] = $pretask;
      $priority--;
    }
    return $preTasks;
  }
  
  /**
   * @param int $supertaskId
   * @param string $newName
   * @throws HTException
   */
  public static function renameSupertask($supertaskId, $newName) {
    $supertask = SupertaskUtils::getSupertask($supertaskId);
    Factory::getSupertaskFactory()->set($supertask, Supertask::SUPERTASK_NAME, $newName);
  }
  
  /**
   * @param int $supertaskId
   * @throws HTException
   */
  public static function deleteSupertask($supertaskId) {
    $supertask = SupertaskUtils::getSupertask($supertaskId);
    Factory::getAgentFactory()->getDB()->beginTransaction();
    
    $qF = new QueryFilter(SupertaskPretask::SUPERTASK_ID, $supertask->getId(), "=", Factory::getSupertaskPretaskFactory());
    $jF = new JoinFilter(Factory::getSupertaskPretaskFactory(), Pretask::PRETASK_ID, SupertaskPretask::PRETASK_ID);
    $joinedTasks = Factory::getPretaskFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    
    Factory::getSupertaskPretaskFactory()->massDeletion([Factory::FILTER => $qF]);
    /** @var $pretasks Pretask[] */
    $pretasks = $joinedTasks[Factory::getPretaskFactory()->getModelName()];
    
    foreach ($pretasks as $pretask) {
      if ($pretask->getIsMaskImport() == 1) {
        Factory::getPretaskFactory()->delete($pretask);
      }
    }
    
    Factory::getSupertaskFactory()->delete($supertask);
    Factory::getAgentFactory()->getDB()->commit();
  }
  
  /**
   * @param int $taskWrapperId
   * @return Task[]
   */
  public static function getRunningSubtasks($taskWrapperId) {
    $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapperId, "=");
    $oF = new OrderFilter(Task::PRIORITY, "DESC");
    return Factory::getTaskFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]);
  }
  
  /**
   * @param int $taskWrapperId
   * @param User $user
   * @return TaskWrapper
   * @throws HTException
   */
  public static function getRunningSupertask($taskWrapperId, $user) {
    $supertask = Factory::getTaskWrapperFactory()->get($taskWrapperId);
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
    $oF = new OrderFilter(Supertask::SUPERTASK_ID, "ASC");
    return Factory::getSupertaskFactory()->filter([Factory::ORDER => $oF]);
  }
  
  /**
   * @param int $supertaskId
   * @return Pretask[]
   */
  public static function getPretasksOfSupertask($supertaskId) {
    $oF = new OrderFilter(Pretask::PRIORITY, "DESC", Factory::getPretaskFactory());
    $qF = new QueryFilter(SupertaskPretask::SUPERTASK_ID, $supertaskId, "=", Factory::getSupertaskPretaskFactory());
    $jF = new JoinFilter(Factory::getSupertaskPretaskFactory(), Pretask::PRETASK_ID, SupertaskPretask::PRETASK_ID);
    $joined = Factory::getPretaskFactory()->filter([Factory::ORDER => $oF, Factory::JOIN => $jF, Factory::FILTER => $qF]);
    return $joined[Factory::getPretaskFactory()->getModelName()];
  }
  
  /**
   * @param int $supertaskId
   * @return Supertask
   * @throws HTException
   */
  public static function getSupertask($supertaskId) {
    $supertask = Factory::getSupertaskFactory()->get($supertaskId);
    if ($supertask == null) {
      throw new HTException("Invalid supertask ID!");
    }
    return $supertask;
  }
  
  /**
   * @param int $supertaskId
   * @param int $hashlistId
   * @param int $crackerId
   * @param bool $skipCompleted when true, pretasks whose equivalent attack has already been fully
   *                            exhausted against the hashlist are skipped instead of being
   *                            re-instantiated (see TaskUtils::findCompletedEquivalent). Default
   *                            false preserves the previous behavior for all existing callers.
   * @return array{taskWrapper: TaskWrapper|null, skippedPretasks: array<int, array{pretaskId: int, matchingTaskId: int}>}
   *         the created TaskWrapper (null when every pretask was skipped) and the list of skipped pretasks
   * @throws HTException
   */
  public static function runSupertask($supertaskId, $hashlistId, $crackerId, $skipCompleted = false) {
    $supertask = Factory::getSupertaskFactory()->get($supertaskId);
    if ($supertask == null) {
      throw new HTException("Invalid supertask ID!");
    }
    $hashlist = Factory::getHashlistFactory()->get($hashlistId);
    if ($hashlist == null) {
      throw new HTException("Invalid hashlist ID!");
    }
    else if ($hashlist->getIsArchived()) {
      throw new HTException("Supertask cannot be applied to an archived hashlist!");
    }
    $cracker = Factory::getCrackerBinaryFactory()->get($crackerId);
    if ($cracker == null) {
      throw new HTException("Invalid cracker ID!");
    }
    $qF = new QueryFilter(SupertaskPretask::SUPERTASK_ID, $supertask->getId(), "=", Factory::getSupertaskPretaskFactory());
    $jF = new JoinFilter(Factory::getSupertaskPretaskFactory(), Pretask::PRETASK_ID, SupertaskPretask::PRETASK_ID);
    $joined = Factory::getPretaskFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    /** @var $pretasks Pretask[] */
    $pretasks = $joined[Factory::getPretaskFactory()->getModelName()];

    // Resolve the effective task spec for each pretask, and (when requested) skip the ones whose
    // equivalent attack has already been fully exhausted on this hashlist. The skip check only
    // reads, so it runs before opening the transaction.
    $skippedPretasks = [];
    $toRun = [];
    foreach ($pretasks as $pretask) {
      $crackerBinaryId = $cracker->getId();
      if ($cracker->getCrackerBinaryTypeId() != $pretask->getCrackerBinaryTypeId()) {
        $crackerBinaryId = CrackerBinaryUtils::getNewestVersion($pretask->getCrackerBinaryTypeId())->getId();
      }
      $attackCmd = $pretask->getAttackCmd();
      if ($hashlist->getHexSalt() == 1 && strpos($attackCmd, "--hex-salt") === false) {
        $attackCmd = "--hex-salt " . $attackCmd;
      }
      if ($skipCompleted) {
        $match = TaskUtils::findCompletedEquivalent(
          $hashlist->getId(),
          $attackCmd,
          TaskUtils::getFileIdsOfPretask($pretask),
          $crackerBinaryId,
          $cracker->getCrackerBinaryTypeId()
        );
        if ($match !== null) {
          $skippedPretasks[] = ["pretaskId" => $pretask->getId(), "matchingTaskId" => $match->getId()];
          continue;
        }
      }
      $toRun[] = ["pretask" => $pretask, "attackCmd" => $attackCmd, "crackerBinaryId" => $crackerBinaryId];
    }

    // Every pretask was already completed: do not create an empty wrapper.
    if (count($toRun) == 0 && $skipCompleted) {
      return ["taskWrapper" => null, "skippedPretasks" => $skippedPretasks];
    }

    Factory::getAgentFactory()->getDB()->beginTransaction();

    $wrapperPriority = 0;
    $wrapperMaxAgents = 0;
    foreach ($toRun as $item) {
      $pretask = $item["pretask"];
      if ($wrapperPriority == 0 || $wrapperPriority > $pretask->getPriority()) {
        $wrapperPriority = $pretask->getPriority();
      }
    }

    $taskWrapper = new TaskWrapper(null, $wrapperPriority, $wrapperMaxAgents, DTaskTypes::SUPERTASK, $hashlist->getId(), $hashlist->getAccessGroupId(), $supertask->getSupertaskName(), 0, 0);
    $taskWrapper = Factory::getTaskWrapperFactory()->save($taskWrapper);

    foreach ($toRun as $item) {
      $pretask = $item["pretask"];
      $task = new Task(
        null,
        $pretask->getTaskName(),
        $item["attackCmd"],
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
        $item["crackerBinaryId"],
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
    }

    Factory::getAgentFactory()->getDB()->commit();
    return ["taskWrapper" => $taskWrapper, "skippedPretasks" => $skippedPretasks];
  }
  
  /**
   * @param string $name
   * @param int[] $pretasks
   * @return Supertask
   * @throws HttpError
   */
  public static function createSupertask(string $name, array|null $pretasks): Supertask {
    if (sizeof($pretasks) == 0) {
      throw new HttpError("Cannot create empty supertask!");
    }
    $tasks = [];
    foreach ($pretasks as $pretaskId) {
      $pretask = Factory::getPretaskFactory()->get($pretaskId);
      if ($pretask == null) {
        throw new HttpError("Invalid preconfigured task ID ($pretaskId)!");
      }
      $tasks[] = $pretask;
    }
    
    Factory::getAgentFactory()->getDB()->beginTransaction();
    $supertask = new Supertask(null, $name);
    $supertask = Factory::getSupertaskFactory()->save($supertask);
    
    foreach ($tasks as $pretask) {
      $supertaskPretask = new SupertaskPretask(null, $supertask->getId(), $pretask->getId());
      Factory::getSupertaskPretaskFactory()->save($supertaskPretask);
    }
    
    Factory::getAgentFactory()->getDB()->commit();
    return Factory::getSupertaskFactory()->get($supertask->getId());
  }
  
  /**
   * @param string $name
   * @param boolean $isCpuOnly
   * @param boolean $isSmall
   * @param boolean $useOptimized
   * @param int $crackerBinaryTypeId
   * @param array $masks
   * @param string $benchtype
   * @throws HTException
   */
  public static function importSupertask($name, $isCpuOnly, $maxAgents, $isSmall, $useOptimized, $crackerBinaryTypeId, $masks, $benchtype): Supertask {
    $isCpuOnly = ($isCpuOnly) ? 1 : 0;
    $isSmall = ($isSmall) ? 1 : 0;
    $useOptimized = ($useOptimized) ? true : false;
    $benchtype = ($benchtype == 'speed') ? 1 : 0;
    $crackerBinaryType = Factory::getCrackerBinaryTypeFactory()->get($crackerBinaryTypeId);
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
    
    Factory::getAgentFactory()->getDB()->beginTransaction();
    $pretasks = SupertaskUtils::createImportPretasks($masks, $isSmall, $maxAgents, $isCpuOnly, $crackerBinaryType, $useOptimized, $benchtype);
    
    $supertask = new Supertask(null, $name);
    $supertask = Factory::getSupertaskFactory()->save($supertask);
    foreach ($pretasks as $preTask) {
      $relation = new SupertaskPretask(null, $supertask->getId(), $preTask->getId());
      Factory::getSupertaskPretaskFactory()->save($relation);
    }
    Factory::getAgentFactory()->getDB()->commit();
    return $supertask;
  }
  
  /**
   * @param $masks
   * @param $isSmall
   * @param $isCpu
   * @param $crackerBinaryType CrackerBinaryType
   * @param bool $useOptimized
   * @param int $newBench
   * @return array
   */
  private static function createImportPretasks($masks, $isSmall, $maxAgents, $isCpu, $crackerBinaryType, $useOptimized = false, $newBench = 1) {
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
      $preTaskName = $pattern;
      $preTaskName = str_replace("COMMA_PLACEHOLDER", "\\,", $preTaskName);
      $preTaskName = str_replace("HASH_PLACEHOLDER", "\\#", $preTaskName);
      
      $pretask = new Pretask(
        null,
        $preTaskName,
        SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS) . " -a 3 " . $cmd,
        SConfig::getInstance()->getVal(DConfig::CHUNK_DURATION),
        SConfig::getInstance()->getVal(DConfig::STATUS_TIMER),
        "",
        $isSmall,
        $isCpu,
        $newBench,
        $priority,
        $maxAgents,
        1,
        $crackerBinaryType->getId()
      );
      $pretask = Factory::getPretaskFactory()->save($pretask);
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
  
  /**
   * @param $supertaskId
   * @param $pretaskId
   * @throws HTException
   */
  public static function removePretaskFromSupertask($supertaskId, $pretaskId) {
    if ($supertaskId == null) {
      throw new HTException("Invalid supertask ID!");
    }
    if ($pretaskId == null) {
      throw new HTException("Invalid pretask ID!");
    }
    $qF1 = new QueryFilter(SupertaskPretask::SUPERTASK_ID, $supertaskId, "=");
    $qF2 = new QueryFilter(SupertaskPretask::PRETASK_ID, $pretaskId, "=");
    $supertaskPretask = Factory::getSupertaskPretaskFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
    Factory::getSupertaskPretaskFactory()->delete($supertaskPretask);
    
    // check if the preconfigured task was from an import. in this case also delete it
    $pretask = PretaskUtils::getPretask($pretaskId);
    if ($pretask->getIsMaskImport() == 1) {
      PretaskUtils::deletePretask($pretaskId);
    }
  }
  
  /**
   * @param $supertaskId
   * @param $pretaskId
   * @throws HTException
   */
  public static function addPretaskToSupertask($supertaskId, $pretaskId) {
    if ($supertaskId == null) {
      throw new HTException("Invalid supertask ID!");
    }
    if ($pretaskId == null) {
      throw new HTException("Invalid pretask ID!");
    }
    $supertaskPretask = new SupertaskPretask(null, $supertaskId, $pretaskId);
    Factory::getSupertaskPretaskFactory()->save($supertaskPretask);
  }
}
