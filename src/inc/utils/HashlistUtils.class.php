<?php

use DBA\Hash;
use DBA\QueryFilter;
use DBA\ContainFilter;
use DBA\OrderFilter;
use DBA\Hashlist;
use DBA\HashlistHashlist;
use DBA\HashBinary;
use DBA\User;
use DBA\File;
use DBA\JoinFilter;
use DBA\NotificationSetting;
use DBA\TaskWrapper;
use DBA\Task;
use DBA\FileTask;
use DBA\Assignment;
use DBA\Chunk;
use DBA\AgentError;
use DBA\Zap;
use DBA\AgentZap;
use DBA\Factory;
use DBA\Speed;

class HashlistUtils {
  /**
   * @param int $hashlistId
   * @param string $notes
   * @param User $user
   * @throws HTException
   */
  public static function editNotes($hashlistId, $notes, $user) {
    $hashlist = HashlistUtils::getHashlist($hashlistId);
    if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
      throw new HTException("No access to hashlist!");
    }
    Factory::getHashlistFactory()->set($hashlist, Hashlist::NOTES, htmlentities($notes, ENT_QUOTES, "UTF-8"));
  }
  
  /**
   * @param string $hash
   * @param User $user
   * @return Hash
   * @throws HTException
   */
  public static function getHash($hash, $user) {
    $qF = new QueryFilter(Hash::HASH, $hash, "=");
    $hashes = Factory::getHashFactory()->filter([Factory::FILTER => $qF]);
    foreach ($hashes as $hash) {
      if ($hash->getIsCracked() != 1) {
        continue;
      }
      $hashlist = HashlistUtils::getHashlist($hash->getHashlistId());
      if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
        continue;
      }
      return $hash;
    }
    return null;
  }
  
  /**
   * @param User $user
   * @return Hashlist[]
   */
  public static function getHashlists($user, $archived = false) {
    $qF1 = new QueryFilter(Hashlist::FORMAT, DHashlistFormat::SUPERHASHLIST, "<>");
    $qF2 = new ContainFilter(Hashlist::ACCESS_GROUP_ID, Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user)));
    $qF3 = new QueryFilter(Hashlist::IS_ARCHIVED, $archived ? 1 : 0, "=");
    return Factory::getHashlistFactory()->filter([Factory::FILTER => [$qF1, $qF2, $qF3]]);
  }
  
  /**
   * @param User $user
   * @return Hashlist[]
   */
  public static function getSuperhashlists($user) {
    $qF1 = new QueryFilter(Hashlist::FORMAT, DHashlistFormat::SUPERHASHLIST, "=");
    $qF2 = new ContainFilter(Hashlist::ACCESS_GROUP_ID, Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user)));
    return Factory::getHashlistFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
  }
  
  /**
   * @param int $hashlistId
   * @param int[] $pretasks
   * @param User $user
   * @return int
   * @throws HTException
   */
  public static function applyPreconfTasks($hashlistId, $pretasks, $user) {
    $hashlist = HashlistUtils::getHashlist($hashlistId);
    if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
      throw new HTException("No access to hashlist!");
    }
    
    $addCount = 0;
    Factory::getAgentFactory()->getDB()->beginTransaction();
    $oF = new OrderFilter(Task::PRIORITY, "DESC LIMIT 1");
    $highest = Factory::getTaskFactory()->filter([Factory::ORDER => $oF], true);
    $priorityBase = 1;
    if ($highest != null) {
      $priorityBase = $highest->getPriority() + 1;
    }
    foreach ($pretasks as $pretask) {
      $task = Factory::getPretaskFactory()->get($pretask);
      if ($task != null) {
        if ($hashlist->getHexSalt() == 1 && strpos($task->getAttackCmd(), "--hex-salt") === false) {
          $task->setAttackCmd("--hex-salt " . $task->getAttackCmd());
        }
        $taskPriority = 0;
        if ($task->getPriority() > 0) {
          $taskPriority = $priorityBase + $task->getPriority();
        }
        $taskMaxAgent = $task->getMaxAgents();
        $taskWrapper = new TaskWrapper(null, $taskPriority, $taskMaxAgent, DTaskTypes::NORMAL, $hashlist->getId(), $hashlist->getAccessGroupId(), "", 0, 0);
        $taskWrapper = Factory::getTaskWrapperFactory()->save($taskWrapper);
        
        $newTask = new Task(
          null,
          $task->getTaskName(),
          $task->getAttackCmd(),
          $task->getChunkTime(),
          $task->getStatusTimer(),
          0,
          0,
          $taskPriority,
          $task->getMaxAgents(),
          $task->getColor(),
          $task->getIsSmall(),
          $task->getIsCpuTask(),
          $task->getUseNewBench(),
          0,
          CrackerBinaryUtils::getNewestVersion($task->getCrackerBinaryTypeId())->getId(),
          $task->getCrackerBinaryTypeId(),
          $taskWrapper->getId(),
          0,
          '',
          0,
          0,
          0,
          0,
          ''
        );
        $newTask = Factory::getTaskFactory()->save($newTask);
        $addCount++;
        
        TaskUtils::copyPretaskFiles($task, $newTask);
        
        $payload = new DataSet(array(DPayloadKeys::TASK => $newTask));
        NotificationHandler::checkNotifications(DNotificationType::NEW_TASK, $payload);
      }
    }
    Factory::getAgentFactory()->getDB()->commit();
    if ($addCount == 0) {
      throw new HTException("Didn't create any tasks!");
    }
    return $addCount;
  }
  
  /**
   * @param int $hashlistId
   * @param User $user
   * @return array
   * @throws HTException
   */
  public static function createWordlists($hashlistId, $user) {
    // create wordlist from hashlist cracked hashes
    $hashlist = HashlistUtils::getHashlist($hashlistId);
    $lists = Util::checkSuperHashlist($hashlist);
    if (sizeof($lists) == 0) {
      throw new HTException("Failed to determine the hashlists which should get exported!");
    }
    else if (!AccessUtils::userCanAccessHashlists($lists, $user)) {
      throw new HTException("No access to hashlist!");
    }
    
    $wordlistName = "Wordlist_" . $hashlist->getId() . "_" . date("d.m.Y_H.i.s") . ".txt";
    $wordlistFilename = Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . "/" . $wordlistName;
    $wordlistFile = fopen($wordlistFilename, "wb");
    if ($wordlistFile === false) {
      throw new HTException("Failed to write wordlist file!");
    }
    $wordCount = 0;
    $pagingSize = 5000;
    if (SConfig::getInstance()->getVal(DConfig::HASHES_PAGE_SIZE) !== false) {
      $pagingSize = SConfig::getInstance()->getVal(DConfig::HASHES_PAGE_SIZE);
    }
    foreach ($lists as $list) {
      $hashFactory = Factory::getHashFactory();
      if ($list->getFormat() != 0) {
        $hashFactory = Factory::getHashBinaryFactory();
      }
      //get number of hashes we need to export
      $qF1 = new QueryFilter(Hash::HASHLIST_ID, $list->getId(), "=");
      $qF2 = new QueryFilter(Hash::IS_CRACKED, "1", "=");
      $size = $hashFactory->countFilter([Factory::FILTER => [$qF1, $qF2]]);
      for ($x = 0; $x * $pagingSize < $size; $x++) {
        $buffer = "";
        $oF = new OrderFilter(Hash::HASH_ID, "ASC LIMIT " . ($x * $pagingSize) . ", $pagingSize");
        $hashes = $hashFactory->filter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => $oF]);
        foreach ($hashes as $hash) {
          $plain = $hash->getPlaintext();
          if (strlen($plain) >= 8 && substr($plain, 0, 5) == "\$HEX[" && substr($plain, strlen($plain) - 1, 1) == "]") {
            $plain = Util::hextobin(substr($plain, 5, strlen($plain) - 6));
          }
          $buffer .= $plain . "\n";
          $wordCount++;
        }
        fputs($wordlistFile, $buffer);
      }
    }
    fclose($wordlistFile);
    
    //add file to files list
    $file = new File(null, $wordlistName, Util::filesize($wordlistFilename), $hashlist->getIsSecret(), 0, $hashlist->getAccessGroupId(), $wordCount);
    $file = Factory::getFileFactory()->save($file);
    # TODO: returning wordCount and wordlistName are not really required here as the name and the count are already given in the file object
    return [$wordCount, $wordlistName, $file];
  }
  
  /**
   * @param int $hashlistId
   * @param int $isSecret
   * @param User $user
   * @throws HTException
   */
  public static function setSecret($hashlistId, $isSecret, $user) {
    // switch hashlist secret state
    $hashlist = HashlistUtils::getHashlist($hashlistId);
    if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
      throw new HTException("No access to hashlist!");
    }
    Factory::getHashlistFactory()->set($hashlist, Hashlist::IS_SECRET, intval($isSecret));
    if (intval($isSecret) == 1) {
      //handle agents which are assigned to hashlists which are secret now
      $jF1 = new JoinFilter(Factory::getTaskFactory(), Task::TASK_ID, Assignment::TASK_ID, Factory::getAssignmentFactory());
      $jF2 = new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID, Factory::getTaskWrapperFactory());
      $jF3 = new JoinFilter(Factory::getHashlistFactory(), Hashlist::HASHLIST_ID, TaskWrapper::HASHLIST_ID, Factory::getTaskWrapperFactory());
      $joined = Factory::getAssignmentFactory()->filter([Factory::JOIN => [$jF1, $jF2, $jF3]]);
      /** @var $assignments Assignment[] */
      $assignments = $joined[Factory::getAssignmentFactory()->getModelName()];
      for ($x = 0; $x < sizeof($assignments); $x++) {
        /** @var $hashlist Hashlist */
        $hashlist = $joined[Factory::getHashlistFactory()->getModelName()][$x];
        if ($hashlist->getId() == $hashlist->getId()) {
          Factory::getAssignmentFactory()->delete($joined[Factory::getAssignmentFactory()->getModelName()][$x]);
        }
      }
    }
  }
  
  /**
   * @param int $hashlistId
   * @param int $isSecret
   * @param User $user
   * @throws HTException
   */
  public static function setArchived($hashlistId, $isArchived, $user) {
    // switch hashlist archived state
    $hashlist = HashlistUtils::getHashlist($hashlistId);
    if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
      throw new HTException("No access to hashlist!");
    }
    
    // check if there is any task which is not archived yet
    $qF1 = new QueryFilter(TaskWrapper::IS_ARCHIVED, 0, "=");
    $qF2 = new QueryFilter(TaskWrapper::HASHLIST_ID, $hashlist->getId(), "=");
    $count = Factory::getTaskWrapperFactory()->countFilter([Factory::FILTER => [$qF1, $qF2]]);
    if ($count > 0) {
      throw new HTException("Hashlist cannot be archived as there are still unarchived tasks belonging to it!");
    }
    
    // check if the hashlist is part of a superhashlist
    $qF = new QueryFilter(HashlistHashlist::HASHLIST_ID, $hashlist->getId(), "=");
    $count = Factory::getHashlistHashlistFactory()->countFilter([Factory::FILTER => $qF]);
    if ($count > 0) {
      throw new HTException("Hashlist cannot be archived as it is part of an existing superhashlist!");
    }
    
    Factory::getHashlistFactory()->set($hashlist, Hashlist::IS_ARCHIVED, intval($isArchived));
  }
  
  /**
   * @param int $hashlistId
   * @param string $name
   * @param User $user
   * @throws HTException
   */
  public static function rename($hashlistId, $name, $user) {
    // change hashlist name
    $hashlist = HashlistUtils::getHashlist($hashlistId);
    if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
      throw new HTException("No access to hashlist!");
    }
    Factory::getHashlistFactory()->set($hashlist, Hashlist::HASHLIST_NAME, htmlentities($name, ENT_QUOTES, "UTF-8"));
  }
  
  /**
   * @param int $hashlistId
   * @param string $separator
   * @param string $source
   * @param array $post
   * @param array $files
   * @param User $user
   * @return int[]
   * @throws HTException
   */
  public static function processZap($hashlistId, $separator, $source, $post, $files, $user) {
    // pre-crack hashes processor
    $hashlist = HashlistUtils::getHashlist($hashlistId);
    if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
      throw new HTException("No access to hashlist!");
    }
    $salted = $hashlist->getIsSalted();
    
    // check which source was used
    $sourcedata = "";
    switch ($source) {
      case "paste":
        $sourcedata = $post["hashfield"];
        break;
      case "upload":
        $sourcedata = $files["hashfile"];
        break;
      case "import":
        $sourcedata = $post["importfile"];
        break;
      case "url":
        $sourcedata = $post["url"];
        break;
    }
    
    //put input into a temp file
    $tmpfile = "/tmp/zaplist_" . $hashlist->getId();
    if (!Util::uploadFile($tmpfile, $source, $sourcedata)) {
      throw new HTException("Failed to process file!");
    }
    $size = Util::filesize($tmpfile);
    if ($size == 0) {
      throw new HTException("File is empty!");
    }
    $file = fopen($tmpfile, "rb");
    if (!$file) {
      throw new HTException("Processing of temporary file failed!");
    }
    $startTime = time();
    
    //find the line separator
    $lineSeparators = array("\r\n", "\n", "\r");
    $lineSeparator = "";

    // This will loop through the buffer until it finds a line separator
    while (!feof($file)) {
      $buffer = fread($file, 1024);
      foreach ($lineSeparators as $ls) {
        if (strpos($buffer, $ls) !== false) {
          $lineSeparator = $ls;
          break;
        }
      }
      if (!empty($lineSeparator)) {
        break;
      }
    }
    rewind($file);
    Factory::getAgentFactory()->getDB()->beginTransaction();
    $hashlists = Util::checkSuperHashlist($hashlist);
    $inSuperHashlists = array();
    $hashlist = $hashlists[0];
    if (sizeof($hashlists) == 1 && $hashlist->getId() == $hashlist->getId()) {
      $qF = new QueryFilter(HashlistHashlist::HASHLIST_ID, $hashlist->getId(), "=");
      $inSuperHashlists = Factory::getHashlistHashlistFactory()->filter([Factory::FILTER => $qF]);
    }
    $hashFactory = Factory::getHashFactory();
    if ($hashlist->getFormat() != DHashlistFormat::PLAIN) {
      $hashFactory = Factory::getHashBinaryFactory();
    }
    //start inserting
    $totalLines = 0;
    $newCracked = 0;
    $tooLong = 0;
    $crackedIn = array();
    $zaps = array();
    foreach ($hashlists as $l) {
      $crackedIn[$l->getId()] = 0;
    }
    $alreadyCracked = 0;
    $notFound = 0;
    $invalid = 0;
    $bufferCount = 0;
    $hashlistIds = array();
    foreach ($hashlists as $l) {
      $hashlistIds[] = $l->getId();
      $crackedIn[$l->getId()] = 0;
    }
    while (!feof($file)) {
      $data = '';
      while(($line = stream_get_line($file, 1024, $lineSeparator)) !== false){
        $data .= $line;
        // seek back the length of lineSeparator and check if it indeed was a line separator
        // If no lineSeperator was found, make sure not to check but just to keep reading
        if (strlen($lineSeparator) > 0) {
          fseek($file, strlen($lineSeparator) * -1, SEEK_CUR);
          if (fread($file, strlen($lineSeparator)) === $lineSeparator) {
            break;
          }
        }
      }
      if (strlen($data) == 0) {
        continue;
      }
      $totalLines++;
      $split = explode($separator, $data);
      if ($salted == '1') {
        if (sizeof($split) < 3) {
          $invalid++;
          continue;
        }
        $hash = $split[0];
        $qF1 = new QueryFilter(Hash::HASH, $hash, "=");
        $qF2 = new ContainFilter(Hash::HASHLIST_ID, $hashlistIds);
        $hashEntry = $hashFactory->filter([Factory::FILTER => [$qF1, $qF2]], true);
        if ($hashEntry == null) {
          $notFound++;
          continue;
        }
        else if ($hashEntry->getIsCracked() == 1) {
          $alreadyCracked++;
          continue;
        }
        $plain = str_replace($hash . $separator . $hashEntry->getSalt() . $separator, "", $data);
        if (strlen($plain) > SConfig::getInstance()->getVal(DConfig::PLAINTEXT_MAX_LENGTH)) {
          $tooLong++;
          continue;
        }
        $hashFactory->mset($hashEntry, [Hash::PLAINTEXT => $plain, Hash::IS_CRACKED => 1, Hash::TIME_CRACKED => time()]);
        $newCracked++;
        $crackedIn[$hashEntry->getHashlistId()]++;
        if ($hashlist->getFormat() == DHashlistFormat::PLAIN) {
          $zaps[] = new Zap(null, $hashEntry->getHash(), time(), null, $hashlist->getId());
        }
      }
      else {
        if (sizeof($split) < 2) {
          $invalid++;
          continue;
        }
        else if ($hashlist->getFormat() == DHashlistFormat::WPA) {
          if (sizeof($split) < 4) {
            $invalid++;
            continue;
          }
          $hash = $split[0] . $separator . $split[1] . $separator . $split[2];
          $qF1 = new QueryFilter(HashBinary::ESSID, $hash, "=");
          $qF2 = new ContainFilter(Hash::HASHLIST_ID, $hashlistIds);
          $hashEntries = $hashFactory->filter([Factory::FILTER => [$qF1, $qF2]]);
        }
        else {
          $hash = $split[0];
          $qF1 = new QueryFilter(Hash::HASH, $hash, "=");
          $qF2 = new ContainFilter(Hash::HASHLIST_ID, $hashlistIds);
          $hashEntries = $hashFactory->filter([Factory::FILTER => [$qF1, $qF2]]);
        }
        if (sizeof($hashEntries) == 0) {
          $notFound++;
          continue;
        }
        foreach ($hashEntries as $hashEntry) {
          if ($hashEntry->getIsCracked() == 1) {
            $alreadyCracked++;
            continue;
          }
          $plain = str_replace($hash . $separator, "", $data);
          if (strlen($plain) > SConfig::getInstance()->getVal(DConfig::PLAINTEXT_MAX_LENGTH)) {
            $tooLong++;
            continue;
          }
          $hashFactory->mset($hashEntry, [Hash::PLAINTEXT => $plain, Hash::IS_CRACKED => 1, Hash::TIME_CRACKED => time()]);
          $crackedIn[$hashEntry->getHashlistId()]++;
          if ($hashlist->getFormat() == DHashlistFormat::PLAIN) {
            $zaps[] = new Zap(null, $hashEntry->getHash(), time(), null, $hashlist->getId());
          }
          $newCracked++;
        }
      }
      $bufferCount++;
      if ($bufferCount > 1000) {
        foreach ($hashlists as $l) {
          $ll = Factory::getHashlistFactory()->get($l->getId());
          Factory::getHashlistFactory()->inc($ll, Hashlist::CRACKED, $crackedIn[$ll->getId()]);
        }
        Factory::getAgentFactory()->getDB()->commit();
        Factory::getAgentFactory()->getDB()->beginTransaction();
        foreach ($hashlists as $l) {
          $crackedIn[$l->getId()] = 0;
        }
        $bufferCount = 0;
        if (sizeof($zaps) > 0) {
          Factory::getZapFactory()->massSave($zaps);
        }
        $zaps = array();
      }
    }
    $endTime = time();
    fclose($file);
    if (file_exists($tmpfile)) {
      unlink($tmpfile);
    }
    
    //finish
    foreach ($hashlists as $l) {
      $ll = Factory::getHashlistFactory()->get($l->getId());
      Factory::getHashlistFactory()->inc($ll, Hashlist::CRACKED, $crackedIn[$ll->getId()]);
    }
    if (sizeof($zaps) > 0) {
      Factory::getZapFactory()->massSave($zaps);
    }
    
    if ($hashlist->getFormat() == DHashlistFormat::SUPERHASHLIST) {
      $hashlist = Factory::getHashlistFactory()->get($hashlist->getId());
      Factory::getHashlistFactory()->inc($hashlist, Hashlist::CRACKED, array_sum($crackedIn));
    }
    if (sizeof($inSuperHashlists) > 0) {
      $total = array_sum($crackedIn);
      foreach ($inSuperHashlists as $super) {
        $superHashlist = Factory::getHashlistFactory()->get($super->getParentHashlistId());
        Factory::getHashlistFactory()->inc($superHashlist, Hashlist::CRACKED, $total);
      }
    }
    Factory::getAgentFactory()->getDB()->commit();
    return [$totalLines, $newCracked, $alreadyCracked, $invalid, $notFound, ($endTime - $startTime), $tooLong];
  }
  
  /**
   * @param int $hashlistId
   * @param User $user
   * @return int
   * @throws HTException
   */
  public static function delete($hashlistId, $user) {
    $hashlist = HashlistUtils::getHashlist($hashlistId);
    if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
      throw new HTException("No access to this hashlist!");
    }
    
    Factory::getAgentFactory()->getDB()->beginTransaction();
    
    $qF = new QueryFilter(HashlistHashlist::HASHLIST_ID, $hashlist->getId(), "=", Factory::getHashlistHashlistFactory());
    $jF = new JoinFilter(Factory::getHashlistFactory(), HashlistHashlist::PARENT_HASHLIST_ID, Hashlist::HASHLIST_ID, Factory::getHashlistHashlistFactory());
    $joined = Factory::getHashlistHashlistFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    /** @var $superHashlists Hashlist[] */
    $superHashlists = $joined[Factory::getHashlistFactory()->getModelName()];
    $toDelete = [];
    foreach ($superHashlists as $superHashlist) {
      Factory::getHashlistFactory()->dec($superHashlist, Hashlist::HASH_COUNT, $hashlist->getHashCount());
      Factory::getHashlistFactory()->dec($superHashlist, Hashlist::CRACKED, $hashlist->getCracked());
      
      if ($superHashlist->getHashCount() <= 0) {
        // this superhashlist has no hashlist which belongs to it anymore -> delete it
        $toDelete[] = $superHashlist;
      }
    }
    
    // when we delete all zaps, we have to make sure that from agentZap, there are no references to zaps of this hashlist
    $qF = new QueryFilter(Zap::HASHLIST_ID, $hashlist->getId(), "=");
    $zapIds = Util::arrayOfIds(Factory::getZapFactory()->filter([Factory::FILTER => $qF]));
    $qF1 = new ContainFilter(AgentZap::LAST_ZAP_ID, $zapIds);
    $uS = new UpdateSet(AgentZap::LAST_ZAP_ID, null);
    Factory::getAgentZapFactory()->massUpdate([Factory::UPDATE => $uS, Factory::FILTER => $qF1]);
    Factory::getZapFactory()->massDeletion([Factory::FILTER => $qF]);
    
    Factory::getHashlistHashlistFactory()->massDeletion([Factory::FILTER => $qF]);
    
    $payload = new DataSet(array(DPayloadKeys::HASHLIST => $hashlist));
    NotificationHandler::checkNotifications(DNotificationType::DELETE_HASHLIST, $payload);
    
    $qF = new QueryFilter(NotificationSetting::OBJECT_ID, $hashlist->getId(), "=");
    $notifications = Factory::getNotificationSettingFactory()->filter([Factory::FILTER => $qF]);
    foreach ($notifications as $notification) {
      if (DNotificationType::getObjectType($notification->getAction()) == DNotificationObjectType::HASHLIST) {
        Factory::getNotificationSettingFactory()->delete($notification);
      }
    }
    
    $qF = new QueryFilter(TaskWrapper::HASHLIST_ID, $hashlist->getId(), "=");
    $taskWrappers = Factory::getTaskWrapperFactory()->filter([Factory::FILTER => $qF]);
    $taskList = array();
    foreach ($taskWrappers as $taskWrapper) {
      $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
      $tasks = Factory::getTaskFactory()->filter([Factory::FILTER => $qF]);
      foreach ($tasks as $task) {
        $taskList[] = $task;
      }
    }
    
    switch ($hashlist->getFormat()) {
      case 0:
        $count = Factory::getHashlistFactory()->countFilter([]);
        if ($count > 1) {
          $deleted = 1;
          $qF = new QueryFilter(Hash::HASHLIST_ID, $hashlist->getId(), "=");
          $oF = new OrderFilter(Hash::HASH_ID, "ASC LIMIT 20000");
          while ($deleted > 0) {
            $result = Factory::getHashFactory()->massDeletion([Factory::FILTER => $qF, Factory::ORDER => $oF]);
            $deleted = $result->rowCount();
            Factory::getAgentFactory()->getDB()->commit();
            Factory::getAgentFactory()->getDB()->beginTransaction();
          }
        }
        else {
          // in case there is only one hashlist to delete, truncate the Hash table.
          Factory::getAgentFactory()->getDB()->query("TRUNCATE TABLE Hash");
          // Make sure that a transaction is active, this is what the rest of the function expects.
          Factory::getAgentFactory()->getDB()->beginTransaction();
        }
        break;
      case 1:
      case 2:
        $qF = new QueryFilter(HashBinary::HASHLIST_ID, $hashlist->getId(), "=");
        Factory::getHashBinaryFactory()->massDeletion([Factory::FILTER => $qF]);
        break;
      case 3:
        $qF = new QueryFilter(HashlistHashlist::PARENT_HASHLIST_ID, $hashlist->getId(), "=");
        Factory::getHashlistHashlistFactory()->massDeletion([Factory::FILTER => $qF]);
        break;
    }
    
    if (sizeof($taskList) > 0) {
      $qF = new ContainFilter(FileTask::TASK_ID, Util::arrayOfIds($taskList));
      Factory::getFileTaskFactory()->massDeletion([Factory::FILTER => $qF]);
      $qF = new ContainFilter(Assignment::TASK_ID, Util::arrayOfIds($taskList));
      Factory::getAssignmentFactory()->massDeletion([Factory::FILTER => $qF]);
      $qF = new ContainFilter(Chunk::TASK_ID, Util::arrayOfIds($taskList));
      Factory::getChunkFactory()->massDeletion([Factory::FILTER => $qF]);
      $qF = new ContainFilter(Speed::TASK_ID, Util::arrayOfIds($taskList));
      Factory::getSpeedFactory()->massDeletion([Factory::FILTER => $qF]);
      $qF = new ContainFilter(AgentError::TASK_ID, Util::arrayOfIds($taskList));
      Factory::getAgentErrorFactory()->massDeletion([Factory::FILTER => $qF]);
    }
    foreach ($taskList as $task) {
      Factory::getTaskFactory()->delete($task);
    }
    
    foreach ($taskWrappers as $taskWrapper) {
      Factory::getTaskWrapperFactory()->delete($taskWrapper);
    }
    
    // delete superhashlists (this must wait until here because of constraints)
    foreach ($toDelete as $hl) {
      Factory::getHashlistFactory()->delete($hl);
    }
    
    Factory::getHashlistFactory()->delete($hashlist);
    
    Factory::getAgentFactory()->getDB()->commit();
    return $hashlist->getFormat();
  }
  
  /**
   * @param int $hashlistId
   * @return Hashlist
   * @throws HTException
   */
  public static function getHashlist($hashlistId) {
    $hashlist = Factory::getHashlistFactory()->get($hashlistId);
    if ($hashlist == null) {
      throw new HTException("Invalid hashlist!");
    }
    return $hashlist;
  }
  
  /**
   * @param int $hashlistId
   * @param User $user
   * @return File
   * @throws HTException
   */
  public static function export($hashlistId, $user) {
    // export cracked hashes to a file
    $hashlist = HashlistUtils::getHashlist($hashlistId);
    $hashlists = Util::checkSuperHashlist($hashlist);
    
    if (!AccessUtils::userCanAccessHashlists($hashlists, $user)) {
      throw new HTException("No access to the hashlists!");
    }
    
    $tmpname = "Pre-cracked_" . $hashlist->getId() . "_" . date("d-m-Y_H-i-s") . ".txt";
    $tmpfile = Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . "/$tmpname";
    $factory = Factory::getHashFactory();
    $format = Factory::getHashlistFactory()->get($hashlists[0]->getId());
    $orderObject = Hash::HASH_ID;
    if ($format->getFormat() != 0) {
      $factory = Factory::getHashBinaryFactory();
      $orderObject = HashBinary::HASH_BINARY_ID;
    }
    $file = fopen($tmpfile, "wb");
    if (!$file) {
      throw new HTException("Failed to write file!");
    }
    
    $hashlistIds = array();
    foreach ($hashlists as $hashlist) {
      $hashlistIds[] = $hashlist->getId();
    }
    $qF1 = new ContainFilter(Hash::HASHLIST_ID, $hashlistIds);
    $qF2 = new QueryFilter(Hash::IS_CRACKED, "1", "=");
    $count = $factory->countFilter([Factory::FILTER => [$qF1, $qF2]]);
    $pagingSize = 5000;
    if (SConfig::getInstance()->getVal(DConfig::HASHES_PAGE_SIZE) !== false) {
      $pagingSize = SConfig::getInstance()->getVal(DConfig::HASHES_PAGE_SIZE);
    }
    $separator = SConfig::getInstance()->getVal(DConfig::FIELD_SEPARATOR);
    for ($x = 0; $x * $pagingSize < $count; $x++) {
      $oF = new OrderFilter($orderObject, "ASC LIMIT " . ($x * $pagingSize) . ",$pagingSize");
      $entries = $factory->filter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => $oF]);
      $buffer = "";
      foreach ($entries as $entry) {
        switch ($format->getFormat()) {
          case 0:
            if ($hashlist->getIsSalted()) {
              $buffer .= $entry->getHash() . $separator . $entry->getSalt() . $separator . $entry->getPlaintext() . "\n";
            }
            else {
              $buffer .= $entry->getHash() . $separator . $entry->getPlaintext() . "\n";
            }
            break;
          case 1:
            $buffer .= $entry->getEssid() . $separator . $entry->getPlaintext() . "\n";
            break;
          case 2:
            $buffer .= $entry->getPlaintext() . "\n";
            break;
        }
      }
      fputs($file, $buffer);
    }
    fclose($file);
    usleep(1000000);
    
    $file = new File(null, $tmpname, Util::filesize($tmpfile), $hashlist->getIsSecret(), 0, $hashlist->getAccessGroupId(), null);
    $file = Factory::getFileFactory()->save($file);
    return $file;
  }
  
  /**
   * @param string $name
   * @param boolean $isSalted
   * @param boolean $isSecret
   * @param boolean $isHexSalted
   * @param string $separator
   * @param int $format
   * @param int $hashtype
   * @param string $saltSeparator
   * @param int $accessGroupId
   * @param string $source
   * @param array $post
   * @param array $files
   * @param User $user
   * @param int $brainId
   * @param int $brainFeatures
   * @return Hashlist
   * @throws HTException
   */
  public static function createHashlist($name, $isSalted, $isSecret, $isHexSalted, $separator, $format, $hashtype, $saltSeparator, $accessGroupId, $source, $post, $files, $user, $brainId, $brainFeatures) {
    $name = htmlentities($name, ENT_QUOTES, "UTF-8");
    $salted = ($isSalted) ? "1" : "0";
    $secret = ($isSecret) ? "1" : "0";
    $hexsalted = ($isHexSalted) ? "1" : "0";
    $brainId = ($brainId) ? "1" : "0";
    $format = intval($format);
    $hashtype = intval($hashtype);
    $accessGroup = Factory::getAccessGroupFactory()->get($accessGroupId);
    $brainFeatures = intval($brainFeatures);
    
    if ($format < DHashlistFormat::PLAIN || $format > DHashlistFormat::BINARY) {
      throw new HTException("Invalid hashlist format!");
    }
    else if ($accessGroup == null) {
      throw new HTException("Invalid access group selected!");
    }
    else if (sizeof(AccessUtils::intersection(array($accessGroup), AccessUtils::getAccessGroupsOfUser($user))) == 0) {
      throw new HTException("Access group with no rights selected!");
    }
    else if (strlen($name) == 0) {
      throw new HTException("Hashlist name cannot be empty!");
    }
    else if ($salted == '1' && strlen($saltSeparator) == 0) {
      throw new HTException("Salt separator cannot be empty when hashes are salted!");
    }
    else if ($brainId && !SConfig::getInstance()->getVal(DConfig::HASHCAT_BRAIN_ENABLE)) {
      throw new HTException("Hashcat brain cannot be used if not enabled in config!");
    }
    else if ($brainId && $brainFeatures < 1 || $brainFeatures > 3) {
      throw new HTException("Invalid brain features selected!");
    }
    
    Factory::getAgentFactory()->getDB()->beginTransaction();
    $hashlist = new Hashlist(null, $name, $format, $hashtype, 0, $separator, 0, $secret, $hexsalted, $salted, $accessGroup->getId(), '', $brainId, $brainFeatures, 0);
    $hashlist = Factory::getHashlistFactory()->save($hashlist);
    
    $dataSource = "";
    switch ($source) {
      case "paste":
        $dataSource = $post["hashfield"];
        break;
      case "upload":
        $dataSource = $files["hashfile"];
        break;
      case "import":
        $dataSource = $post["importfile"];
        break;
      case "url":
        $dataSource = $post["url"];
        break;
    }
    $tmpfile = "/tmp/hashlist_" . $hashlist->getId();
    if (!Util::uploadFile($tmpfile, $source, $dataSource) && file_exists($tmpfile)) {
      Factory::getAgentFactory()->getDB()->rollback();
      throw new HTException("Failed to process file!");
    }
    else if (!file_exists($tmpfile)) {
      Factory::getAgentFactory()->getDB()->rollback();
      throw new HTException("Required file does not exist!");
    }
    // replace countLines with fileLineCount? Seems like a better option, not OS-dependent
    else if (Util::countLines($tmpfile) > SConfig::getInstance()->getVal(DConfig::MAX_HASHLIST_SIZE)) {
      Factory::getAgentFactory()->getDB()->rollback();
      throw new HTException("Hashlist has too many lines!");
    }
    $file = fopen($tmpfile, "rb");
    if (!$file) {
      throw new HTException("Failed to open file!");
    }
    Factory::getAgentFactory()->getDB()->commit();
    $added = 0;
    $preFound = 0;
    
    switch ($format) {
      case DHashlistFormat::PLAIN:
        if ($salted) {
          // find out if the first line contains field separator
          rewind($file);
          $bufline = stream_get_line($file, 1024);
          if (strpos($bufline, $saltSeparator) === false) {
            throw new HTException("Salted hashes separator not found in file!");
          }
        }
        else {
          $saltSeparator = "";
        }
        rewind($file);
        Factory::getAgentFactory()->getDB()->beginTransaction();
        $values = array();
        $bufferCount = 0;
        while (!feof($file)) {
          $line = trim(fgets($file));
          if (strlen($line) == 0) {
            continue;
          }
          $hash = $line;
          $salt = "";
          if ($saltSeparator != "") {
            $pos = strpos($line, $saltSeparator);
            if ($pos !== false) {
              $hash = substr($line, 0, $pos);
              $salt = substr($line, $pos + 1);
            }
          }
          if (strlen($hash) == 0) {
            continue;
          }
          //TODO: check hash length here
          
          // if selected check if it is cracked
          $found = null;
          if (SConfig::getInstance()->getVal(DConfig::HASHLIST_IMPORT_CHECK)) {
            $qF = new QueryFilter(Hash::HASH, $hash, "=");
            $check = Factory::getHashFactory()->filter([Factory::FILTER => $qF]);
            foreach ($check as $c) {
              if ($c->getIsCracked()) {
                $found = $c;
                break;
              }
            }
          }
          if ($found == null) {
            $values[] = new Hash(null, $hashlist->getId(), $hash, $salt, "", 0, null, 0, 0);
          }
          else {
            $values[] = new Hash(null, $hashlist->getId(), $hash, $salt, $found->getPlaintext(), time(), null, 1, 0);
            $preFound++;
          }
          $bufferCount++;
          if ($bufferCount >= 10000) {
            $result = Factory::getHashFactory()->massSave($values);
            $added += $result->rowCount();
            Factory::getAgentFactory()->getDB()->commit();
            Factory::getAgentFactory()->getDB()->beginTransaction();
            $values = array();
            $bufferCount = 0;
          }
        }
        if (sizeof($values) > 0) {
          $result = Factory::getHashFactory()->massSave($values);
          $added += $result->rowCount();
        }
        fclose($file);
        unlink($tmpfile);
        Factory::getHashlistFactory()->mset($hashlist, [Hashlist::HASH_COUNT => $added, Hashlist::CRACKED => $preFound]);
        Factory::getAgentFactory()->getDB()->commit();
        Util::createLogEntry("User", $user->getId(), DLogEntry::INFO, "New Hashlist created: " . $hashlist->getHashlistName());
        
        NotificationHandler::checkNotifications(DNotificationType::NEW_HASHLIST, new DataSet(array(DPayloadKeys::HASHLIST => $hashlist)));
        break;
      case DHashlistFormat::WPA:
        $added = 0;
        $values = [];
        while (!feof($file)) {
          if ($hashlist->getHashTypeId() == 2500) { // HCCAPX hashes
            $data = fread($file, 393);
            if (strlen($data) == 0) {
              break;
            }
            if (strlen($data) != 393) {
              UI::printError("ERROR", "Data file only contains " . strlen($data) . " bytes!");
            }
            // get the SSID
            $network = "";
            for ($i = 10; $i < 42; $i++) {
              $byte = $data[$i];
              if ($byte != "\x00") {
                $network .= $byte;
              }
              else {
                break;
              }
            }
            // get the AP MAC
            $mac_ap = "";
            for ($i = 59; $i < 65; $i++) {
              $mac_ap .= $data[$i];
            }
            $mac_ap = Util::bintohex($mac_ap);
            // get the Client MAC
            $mac_cli = "";
            for ($i = 97; $i < 103; $i++) {
              $mac_cli .= $data[$i];
            }
            $mac_cli = Util::bintohex($mac_cli);
            $hash = new HashBinary(null, $hashlist->getId(), $mac_ap . SConfig::getInstance()->getVal(DConfig::FIELD_SEPARATOR) . $mac_cli . SConfig::getInstance()->getVal(DConfig::FIELD_SEPARATOR) . Util::bintohex($network), Util::bintohex($data), null, 0, null, 0, 0);
            Factory::getHashBinaryFactory()->save($hash);
            $added++;
          }
          else { // PMKID hashes
            $line = trim(fgets($file));
            if (strlen($line) == 0) {
              continue;
            }
            if (strpos($line, "*") !== false) {
              // in case the other format with * as separator was used, we convert it to : to be consistent with the newest format
              $line = str_replace("*", ":", $line);
            }
            $split = explode(":", $line);
            $mac_ap = $split[1];
            $mac_cli = $split[2];
            if (sizeof($split) > 3) { // -m 16800
              $network = $split[3];
              $identification = $mac_ap . SConfig::getInstance()->getVal(DConfig::FIELD_SEPARATOR) . $mac_cli . SConfig::getInstance()->getVal(DConfig::FIELD_SEPARATOR) . $network;
            }
            else { // -m 16801
              $identification = $mac_ap . SConfig::getInstance()->getVal(DConfig::FIELD_SEPARATOR) . $mac_cli;
            }
            $hash = new HashBinary(null, $hashlist->getId(), $identification, Util::bintohex($line . "\n"), null, 0, null, 0, 0);
            Factory::getHashBinaryFactory()->save($hash);
            $added++;
          }
        }
        fclose($file);
        unlink($tmpfile);
        
        Factory::getHashlistFactory()->set($hashlist, Hashlist::HASH_COUNT, $added);
        Util::createLogEntry("User", $user->getId(), DLogEntry::INFO, "New Hashlist created: " . $hashlist->getHashlistName());
        
        NotificationHandler::checkNotifications(DNotificationType::NEW_HASHLIST, new DataSet(array(DPayloadKeys::HASHLIST => $hashlist)));
        break;
      case DHashlistFormat::BINARY:
        if (!feof($file)) {
          $data = fread($file, Util::filesize($tmpfile));
          $hash = new HashBinary(null, $hashlist->getId(), "", Util::bintohex($data), "", 0, null, 0, 0);
          Factory::getHashBinaryFactory()->save($hash);
        }
        fclose($file);
        unlink($tmpfile);
        Factory::getHashlistFactory()->set($hashlist, Hashlist::HASH_COUNT, 1);
        Util::createLogEntry("User", $user->getId(), DLogEntry::INFO, "New Hashlist created: " . $hashlist->getHashlistName());
        
        NotificationHandler::checkNotifications(DNotificationType::NEW_HASHLIST, new DataSet(array(DPayloadKeys::HASHLIST => $hashlist)));
        break;
    }
    return $hashlist;
  }
  
  /**
   * @param int[] $hashlists
   * @param string $name
   * @param User $user
   * @throws HTException
   */
  public static function createSuperhashlist($hashlists, $name, $user) {
    for ($i = 0; $i < sizeof($hashlists); $i++) {
      if (intval($hashlists[$i]) <= 0) {
        unset($hashlists[$i]);
      }
    }
    if (sizeof($hashlists) == 0) {
      throw new HTException("No hashlists selected!");
    }
    $name = htmlentities($name, ENT_QUOTES, "UTF-8");
    $qF = new ContainFilter(Hashlist::HASHLIST_ID, $hashlists);
    Factory::getAgentFactory()->getDB()->beginTransaction();
    $lists = Factory::getHashlistFactory()->filter([Factory::FILTER => $qF]);
    if (strlen($name) == 0) {
      $name = "SHL_" . $lists[0]->getHashtypeId();
    }
    else if (!AccessUtils::userCanAccessHashlists($lists, $user)) {
      throw new HTException("You have no access to at least one of the provided hashlists!");
    }
    $hashcount = 0;
    $cracked = 0;
    foreach ($lists as $list) {
      $hashcount += $list->getHashCount();
      $cracked += $list->getCracked();
    }
    // check if all have the same access group
    $accessGroupId = null;
    foreach ($lists as $list) {
      if ($accessGroupId == null) {
        $accessGroupId = $list->getAccessGroupId();
        continue;
      }
      else if ($list->getFormat() == DHashlistFormat::SUPERHASHLIST) {
        throw new HTException("You cannot create a new superhashlist containing a superhashlist!");
      }
      else if ($accessGroupId != $list->getAccessGroupId()) {
        throw new HTException("You cannot create superhashlists from hashlists which belong to different access groups");
      }
      else if ($list->getIsArchived()) {
        throw new HTException("You cannot create a superhashlist containing archived hashlists!");
      }
    }
    
    $superhashlist = new Hashlist(null, $name, DHashlistFormat::SUPERHASHLIST, $lists[0]->getHashtypeId(), $hashcount, $lists[0]->getSaltSeparator(), $cracked, 0, $lists[0]->getHexSalt(), $lists[0]->getIsSalted(), $accessGroupId, '', 0, 0, 0);
    $superhashlist = Factory::getHashlistFactory()->save($superhashlist);
    $relations = array();
    foreach ($lists as $list) {
      $relations[] = new HashlistHashlist(null, $superhashlist->getId(), $list->getId());
    }
    Factory::getHashlistHashlistFactory()->massSave($relations);
    Factory::getAgentFactory()->getDB()->commit();
  }
  
  /**
   * @param int $hashlistId
   * @param User $user
   * @return File
   * @throws HTException
   */
  public static function leftlist($hashlistId, $user) {
    $hashlist = HashlistUtils::getHashlist($hashlistId);
    if ($hashlist->getFormat() == DHashlistFormat::WPA || $hashlist->getFormat() == DHashlistFormat::BINARY) {
      throw new HTException("You cannot create left lists for binary hashes!");
    }
    $hashlists = Util::checkSuperHashlist($hashlist);
    if (!AccessUtils::userCanAccessHashlists($hashlists, $user)) {
      throw new HTException("No access to this task!");
    }
    if ($hashlists[0]->getFormat() != DHashlistFormat::PLAIN) {
      throw new HTException("You cannot create left lists for binary hashes!");
    }
    
    $hashlistIds = array();
    foreach ($hashlists as $hl) {
      $hashlistIds[] = $hl->getId();
    }
    
    $tmpname = "Leftlist_" . $hashlist->getId() . "_" . date("d-m-Y_H-i-s") . ".txt";
    $tmpfile = Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . "/$tmpname";
    
    $file = fopen($tmpfile, "wb");
    if (!$file) {
      throw new HTException("Failed to write file!");
    }
    
    $qF1 = new ContainFilter(Hash::HASHLIST_ID, $hashlistIds);
    $qF2 = new QueryFilter(Hash::IS_CRACKED, "0", "=");
    $count = Factory::getHashFactory()->countFilter([Factory::FILTER => [$qF1, $qF2]]);
    $pagingSize = 5000;
    if (SConfig::getInstance()->getVal(DConfig::HASHES_PAGE_SIZE) !== false) {
      $pagingSize = SConfig::getInstance()->getVal(DConfig::HASHES_PAGE_SIZE);
    }
    for ($x = 0; $x * $pagingSize < $count; $x++) {
      $oF = new OrderFilter(Hash::HASH_ID, "ASC LIMIT " . ($x * $pagingSize) . ",$pagingSize");
      $entries = Factory::getHashFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => $oF]);
      $buffer = "";
      foreach ($entries as $entry) {
        $buffer .= $entry->getHash();
        if ($hashlist->getIsSalted()) {
          $buffer .= SConfig::getInstance()->getVal(DConfig::FIELD_SEPARATOR) . $entry->getSalt();
        }
        $buffer .= "\n";
      }
      fputs($file, $buffer);
    }
    fclose($file);
    usleep(1000000);
    
    $file = new File(null, $tmpname, Util::filesize($tmpfile), $hashlist->getIsSecret(), 0, $hashlist->getAccessGroupId(), null);
    return Factory::getFileFactory()->save($file);
  }
  
  /**
   * @param $hashlistId int
   * @param $user User
   * @return array
   * @throws HTException
   */
  public static function getCrackedHashes($hashlistId, $user) {
    $hashlist = HashlistUtils::getHashlist($hashlistId);
    $lists = Util::checkSuperHashlist($hashlist);
    if (!AccessUtils::userCanAccessHashlists($lists, $user)) {
      throw new HTException("No access to the hashlists!");
    }
    
    $hashlistIds = Util::arrayOfIds($lists);
    $qF1 = new ContainFilter(Hash::HASHLIST_ID, $hashlistIds);
    $qF2 = new QueryFilter(Hash::IS_CRACKED, 1, "=");
    $hashes = [];
    $entries = Factory::getHashFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
    foreach ($entries as $entry) {
      $arr = [
        "hash" => $entry->getHash(),
        "plain" => $entry->getPlaintext(),
        "crackpos" => $entry->getCrackPos()
      ];
      if (strlen($entry->getSalt()) > 0) {
        $arr["hash"] .= $hashlist->getSaltSeparator() . $entry->getSalt();
      }
      $hashes[] = $arr;
    }
    $entries_binary = Factory::getHashBinaryFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
    foreach ($entries_binary as $entry) {
      $arr = [
        "hash" => $entry->getHash(),
        "plain" => $entry->getPlaintext(),
        "crackpos" => $entry->getCrackPos()
      ];
      $hashes[] = $arr;
    }
    return $hashes;
  }
  
  /**
   * @param $hashlistId int
   * @param $accessGroupId int
   * @param $user User
   * @throws HTException
   */
  public static function changeAccessGroup($hashlistId, $accessGroupId, $user) {
    $hashlist = HashlistUtils::getHashlist($hashlistId);
    $accessGroup = AccessGroupUtils::getGroup($accessGroupId);
    
    $userAccessGroupIds = Util::getAccessGroupIds($user->getId());
    if (!in_array($accessGroupId, $userAccessGroupIds) || !in_array($hashlist->getAccessGroupId(), $userAccessGroupIds)) {
      throw new HTException("No access to this group!");
    }
    
    $qF = new QueryFilter(Hashlist::HASHLIST_ID, $hashlist->getId(), "=");
    $uS = new UpdateSet(Hashlist::ACCESS_GROUP_ID, $accessGroup->getId(), "=");
    Factory::getHashlistFactory()->massUpdate([Factory::FILTER => $qF, Factory::UPDATE => $uS]);
    
    $qF = new QueryFilter(TaskWrapper::HASHLIST_ID, $hashlist->getId(), "=");
    $uS = new UpdateSet(TaskWrapper::ACCESS_GROUP_ID, $accessGroup->getId());
    Factory::getTaskWrapperFactory()->massUpdate([Factory::FILTER => $qF, Factory::UPDATE => $uS]);
  }
}
