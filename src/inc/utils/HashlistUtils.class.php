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

class HashlistUtils {
  /**
   * @param string $hash
   * @param User $user
   * @return Hash
   */
  public static function getHash($hash, $user){
    global $FACTORIES;

    $qF = new QueryFilter(Hash::HASH, $hash, "=");
    $hashes = $FACTORIES::getHashFactory()->filter(array($FACTORIES::FILTER => $qF));
    foreach($hashes as $hash){
      if($hash->getIsCracked() != 1){
        continue;
      }
      $hashlist = HashlistUtils::getHashlist($hash->getHashlistId());
      if(!AccessUtils::userCanAccessHashlists($hashlist, $user)){
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
  public static function getHashlists($user) {
    global $FACTORIES;

    $qF1 = new QueryFilter(Hashlist::FORMAT, DHashlistFormat::SUPERHASHLIST, "<>");
    $qF2 = new ContainFilter(Hashlist::ACCESS_GROUP_ID, Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user)));
    return $FACTORIES::getHashlistFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)));
  }

  /**
   * @param User $user
   * @return Hashlist[]
   */
  public static function getSuperhashlists($user) {
    global $FACTORIES;

    $qF1 = new QueryFilter(Hashlist::FORMAT, DHashlistFormat::SUPERHASHLIST, "=");
    $qF2 = new ContainFilter(Hashlist::ACCESS_GROUP_ID, Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user)));
    return $FACTORIES::getHashlistFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)));
  }

  /**
   * @param int $hashlistId
   * @param int[] $pretasks
   * @param User $user
   * @throws HTException
   * @return int
   */
  public static function applyPreconfTasks($hashlistId, $pretasks, $user) {
    global $FACTORIES;

    $hashlist = HashlistUtils::getHashlist($hashlistId);
    if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
      throw new HTException("No access to hashlist!");
    }

    $addCount = 0;
    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $oF = new OrderFilter(Task::PRIORITY, "DESC LIMIT 1");
    $highest = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::ORDER => array($oF)), true);
    $priorityBase = 1;
    if ($highest != null) {
      $priorityBase = $highest->getPriority() + 1;
    }
    foreach ($pretasks as $pretask) {
      $task = $FACTORIES::getPretaskFactory()->get($pretask);
      if ($task != null) {
        if ($hashlist->getHexSalt() == 1 && strpos($task->getAttackCmd(), "--hex-salt") === false) {
          $task->setAttackCmd("--hex-salt " . $task->getAttackCmd());
        }
        $taskPriority = 0;
        if ($task->getPriority() > 0) {
          $taskPriority = $priorityBase + $task->getPriority();
        }
        $taskWrapper = new TaskWrapper(0, $taskPriority, DTaskTypes::NORMAL, $hashlist->getId(), $hashlist->getAccessGroupId(), "", 0);
        $taskWrapper = $FACTORIES::getTaskWrapperFactory()->save($taskWrapper);

        $newTask = new Task(
          0, 
          $task->getTaskName(), 
          $task->getAttackCmd(), 
          $task->getChunkTime(), 
          $task->getStatusTimer(), 
          0, 
          0, 
          $taskPriority, 
          $task->getColor(), 
          $task->getIsSmall(), 
          $task->getIsCpuTask(), 
          $task->getUseNewBench(), 
          0, 
          CrackerBinaryUtils::getNewestVersion($task->getCrackerBinaryTypeId())->getId(), 
          $task->getCrackerBinaryTypeId(), 
          $taskWrapper->getId(), 
          0,
          0
        );
        $newTask = $FACTORIES::getTaskFactory()->save($newTask);
        $addCount++;

        TaskUtils::copyPretaskFiles($task, $newTask);

        $payload = new DataSet(array(DPayloadKeys::TASK => $task));
        NotificationHandler::checkNotifications(DNotificationType::NEW_TASK, $payload);
      }
    }
    $FACTORIES::getAgentFactory()->getDB()->commit();
    if ($addCount == 0) {
      throw new HTException("Didn't create any tasks!");
    }
    return $addCount;
  }

  /**
   * @param int $hashlistId
   * @param User $user
   * @throws HTException
   * @return array
   */
  public static function createWordlists($hashlistId, $user) {
    /** @var DataSet $CONFIG */
    global $FACTORIES, $CONFIG;

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
    $wordlistFilename = dirname(__FILE__) . "/../../files/" . $wordlistName;
    $wordlistFile = fopen($wordlistFilename, "wb");
    if ($wordlistFile === false) {
      throw new HTException("Failed to write wordlist file!");
    }
    $wordCount = 0;
    $pagingSize = 5000;
    if ($CONFIG->getVal(DConfig::HASHES_PAGE_SIZE) !== false) {
      $pagingSize = $CONFIG->getVal(DConfig::HASHES_PAGE_SIZE);
    }
    foreach ($lists as $list) {
      $hashFactory = $FACTORIES::getHashFactory();
      if ($list->getFormat() != 0) {
        $hashFactory = $FACTORIES::getHashBinaryFactory();
      }
      //get number of hashes we need to export
      $qF1 = new QueryFilter(Hash::HASHLIST_ID, $list->getId(), "=");
      $qF2 = new QueryFilter(Hash::IS_CRACKED, "1", "=");
      $size = $hashFactory->countFilter(array($FACTORIES::FILTER => array($qF1, $qF2)));
      for ($x = 0; $x * $pagingSize < $size; $x++) {
        $buffer = "";
        $oF = new OrderFilter(Hash::HASH_ID, "ASC LIMIT " . ($x * $pagingSize) . ", $pagingSize");
        $hashes = $hashFactory->filter(array($FACTORIES::FILTER => array($qF1, $qF2), $FACTORIES::ORDER => array($oF)));
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
    $file = new File(0, $wordlistName, Util::filesize($wordlistFilename), $hashlist->getIsSecret(), 0, $hashlist->getAccessGroupId());
    $FACTORIES::getFileFactory()->save($file);
    return [$wordCount, $wordlistName, $file];
  }

  /**
   * @param int $hashlistId
   * @param int $isSecret
   * @param User $user
   * @throws HTException
   */
  public static function setSecret($hashlistId, $isSecret, $user) {
    global $FACTORIES;

    // switch hashlist secret state
    $hashlist = HashlistUtils::getHashlist($hashlistId);
    if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
      throw new HTException("No access to hashlist!");
    }
    $secret = intval($isSecret);
    $hashlist->setIsSecret($secret);
    $FACTORIES::getHashlistFactory()->update($hashlist);
    if ($secret == 1) {
      //handle agents which are assigned to hashlists which are secret now
      //TODO: not sure if this code works
      $jF1 = new JoinFilter($FACTORIES::getTaskFactory(), Task::TASK_ID, Assignment::TASK_ID, $FACTORIES::getAssignmentFactory());
      $jF2 = new JoinFilter($FACTORIES::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID, $FACTORIES::getTaskWrapperFactory());
      $jF3 = new JoinFilter($FACTORIES::getHashlistFactory(), Hashlist::HASHLIST_ID, TaskWrapper::HASHLIST_ID, $FACTORIES::getTaskWrapperFactory());
      $joined = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::JOIN => array($jF1, $jF2, $jF3)));
      for ($x = 0; $x < sizeof($joined[$FACTORIES::getAssignmentFactory()->getModelName()]); $x++) {
        /** @var $hashlist Hashlist */
        $hashlist = $joined[$FACTORIES::getHashlistFactory()->getModelName()][$x];
        if ($hashlist->getId() == $hashlist->getId()) {
          $FACTORIES::getAssignmentFactory()->delete($joined[$FACTORIES::getAssignmentFactory()->getModelName()][$x]);
        }
      }
    }
  }

  /**
   * @param int $hashlistId
   * @param string $name
   * @param User $user
   * @throws HTException
   */
  public static function rename($hashlistId, $name, $user) {
    global $FACTORIES;

    // change hashlist name
    $hashlist = HashlistUtils::getHashlist($hashlistId);
    if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
      throw new HTException("No access to hashlist!");
    }
    $name = htmlentities($name, ENT_QUOTES, "UTF-8");
    $hashlist->setHashlistName($name);
    $FACTORIES::getHashlistFactory()->update($hashlist);
  }

  /**
   * @param int $hashlistId
   * @param string $separator
   * @param string $source
   * @param array $post
   * @param array $files
   * @param User $user
   * @throws HTException
   * @return int[]
   */
  public static function processZap($hashlistId, $separator, $source, $post, $files, $user) {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;

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
    $tmpfile = dirname(__FILE__) . "/../../tmp/zaplist_" . $hashlist->getId();
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
    $buffer = fread($file, 1024);
    $lineSeparators = array("\r\n", "\n", "\r");
    $lineSeparator = "";
    foreach ($lineSeparators as $sep) {
      if (strpos($buffer, $sep) !== false) {
        $lineSeparator = $sep;
        break;
      }
    }
    rewind($file);
    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $hashlists = Util::checkSuperHashlist($hashlist);
    $inSuperHashlists = array();
    $hashlist = $hashlists[0];
    if (sizeof($hashlists) == 1 && $hashlist->getId() == $hashlist->getId()) {
      $qF = new QueryFilter(HashlistHashlist::HASHLIST_ID, $hashlist->getId(), "=");
      $inSuperHashlists = $FACTORIES::getHashlistHashlistFactory()->filter(array($FACTORIES::FILTER => $qF));
    }
    $hashFactory = $FACTORIES::getHashFactory();
    if ($hashlist->getFormat() != DHashlistFormat::PLAIN) {
      $hashFactory = $FACTORIES::getHashBinaryFactory();
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
      $data = stream_get_line($file, 1024, $lineSeparator);
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
        $hashEntry = $hashFactory->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
        if ($hashEntry == null) {
          $notFound++;
          continue;
        }
        else if ($hashEntry->getIsCracked() == 1) {
          $alreadyCracked++;
          continue;
        }
        $plain = str_replace($hash . $separator . $hashEntry->getSalt() . $separator, "", $data);
        if (strlen($plain) > $CONFIG->getVal(DConfig::PLAINTEXT_MAX_LENGTH)) {
          $tooLong++;
          continue;
        }
        $hashEntry->setPlaintext($plain);
        $hashEntry->setIsCracked(1);
        $hashFactory->update($hashEntry);
        $newCracked++;
        $crackedIn[$hashEntry->getHashlistId()]++;
        if ($hashlist->getFormat() == DHashlistFormat::PLAIN) {
          $zaps[] = new Zap(0, $hashEntry->getHash(), time(), null, $hashlist->getId());
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
          $hashEntries = $hashFactory->filter(array($FACTORIES::FILTER => array($qF1, $qF2)));
        }
        else {
          $hash = $split[0];
          $qF1 = new QueryFilter(Hash::HASH, $hash, "=");
          $qF2 = new ContainFilter(Hash::HASHLIST_ID, $hashlistIds);
          $hashEntries = $hashFactory->filter(array($FACTORIES::FILTER => array($qF1, $qF2)));
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
          if (strlen($plain) > $CONFIG->getVal(DConfig::PLAINTEXT_MAX_LENGTH)) {
            $tooLong++;
            continue;
          }
          $hashEntry->setPlaintext($plain);
          $hashEntry->setIsCracked(1);
          $hashFactory->update($hashEntry);
          $crackedIn[$hashEntry->getHashlistId()]++;
          if ($hashlist->getFormat() == DHashlistFormat::PLAIN) {
            $zaps[] = new Zap(0, $hashEntry->getHash(), time(), null, $hashlist->getId());
          }
          $newCracked++;
        }
      }
      $bufferCount++;
      if ($bufferCount > 1000) {
        foreach ($hashlists as $l) {
          $ll = $FACTORIES::getHashlistFactory()->get($l->getId());
          $ll->setCracked($ll->getCracked() + $crackedIn[$ll->getId()]);
          $FACTORIES::getHashlistFactory()->update($ll);
        }
        $FACTORIES::getAgentFactory()->getDB()->commit();
        $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
        $crackedIn = array();
        $bufferCount = 0;
        if (sizeof($zaps) > 0) {
          $FACTORIES::getZapFactory()->massSave($zaps);
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
      $ll = $FACTORIES::getHashlistFactory()->get($l->getId());
      $ll->setCracked($ll->getCracked() + $crackedIn[$ll->getId()]);
      $FACTORIES::getHashlistFactory()->update($ll);
    }
    if (sizeof($zaps) > 0) {
      $FACTORIES::getZapFactory()->massSave($zaps);
    }

    if ($hashlist->getFormat() == DHashlistFormat::SUPERHASHLIST) {
      $total = array_sum($crackedIn);
      $hashlist = $FACTORIES::getHashlistFactory()->get($hashlist->getId());
      $hashlist->setCracked($hashlist->getCracked() + $total);
      $FACTORIES::getHashlistFactory()->update($hashlist);
    }
    if (sizeof($inSuperHashlists) > 0) {
      $total = array_sum($crackedIn);
      foreach ($inSuperHashlists as $super) {
        $superHashlist = $FACTORIES::getHashlistFactory()->get($super->getParentHashlistId());
        $superHashlist->setCracked($superHashlist->getCracked() + $total);
        $FACTORIES::getHashlistFactory()->update($superHashlist);
      }
    }
    $FACTORIES::getAgentFactory()->getDB()->commit();
    return [$totalLines, $newCracked, $alreadyCracked, $invalid, $notFound, ($endTime - $startTime), $tooLong];
  }

  /**
   * @param int $hashlistId
   * @param User $user
   * @return int
   * @throws HTException
   */
  public static function delete($hashlistId, $user) {
    global $FACTORIES;

    $hashlist = HashlistUtils::getHashlist($hashlistId);
    if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
      throw new HTException("No access to this hashlist!");
    }

    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();

    $qF = new QueryFilter(HashlistHashlist::HASHLIST_ID, $hashlist->getId(), "=", $FACTORIES::getHashlistHashlistFactory());
    $jF = new JoinFilter($FACTORIES::getHashlistFactory(), HashlistHashlist::PARENT_HASHLIST_ID, Hashlist::HASHLIST_ID, $FACTORIES::getHashlistHashlistFactory());
    $joined = $FACTORIES::getHashlistHashlistFactory()->filter(array($FACTORIES::FILTER => array($qF), $FACTORIES::JOIN => array($jF)));
    /** @var $superHashlists Hashlist[] */
    $superHashlists = $joined[$FACTORIES::getHashlistFactory()->getModelName()];
    $toDelete = array();
    $toUpdate = array();
    foreach ($superHashlists as $superHashlist) {
      $superHashlist->setHashCount($superHashlist->getHashCount() - $hashlist->getHashCount());
      $superHashlist->setCracked($superHashlist->getCracked() - $hashlist->getCracked());

      if ($superHashlist->getHashCount() <= 0) {
        // this superhashlist has no hashlist which belongs to it anymore -> delete it
        $toDelete = $superHashlist;
      }
      else {
        $toUpdate = $superHashlist;
      }
    }
    $FACTORIES::getHashlistHashlistFactory()->massDeletion(array($FACTORIES::FILTER => array($qF)));

    $qF = new QueryFilter(Zap::HASHLIST_ID, $hashlist->getId(), "=");
    $FACTORIES::getZapFactory()->massDeletion(array($FACTORIES::FILTER => $qF));

    $payload = new DataSet(array(DPayloadKeys::HASHLIST => $hashlist));
    NotificationHandler::checkNotifications(DNotificationType::DELETE_HASHLIST, $payload);

    $qF = new QueryFilter(NotificationSetting::OBJECT_ID, $hashlist->getId(), "=");
    $notifications = $FACTORIES::getNotificationSettingFactory()->filter(array($FACTORIES::FILTER => $qF));
    foreach ($notifications as $notification) {
      if (DNotificationType::getObjectType($notification->getAction()) == DNotificationObjectType::HASHLIST) {
        $FACTORIES::getNotificationSettingFactory()->delete($notification);
      }
    }

    $qF = new QueryFilter(TaskWrapper::HASHLIST_ID, $hashlist->getId(), "=");
    $taskWrappers = $FACTORIES::getTaskWrapperFactory()->filter(array($FACTORIES::FILTER => $qF));
    $taskList = array();
    foreach ($taskWrappers as $taskWrapper) {
      $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
      $tasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => array($qF)));
      foreach ($tasks as $task) {
        $taskList[] = $task;
      }
    }

    switch ($hashlist->getFormat()) {
      case 0:
        $count = $FACTORIES::getHashlistFactory()->countFilter(array());
        if ($count > 1) {
          $deleted = 1;
          $qF = new QueryFilter(Hash::HASHLIST_ID, $hashlist->getId(), "=");
          $oF = new OrderFilter(Hash::HASH_ID, "ASC LIMIT 20000");
          while ($deleted > 0) {
            $result = $FACTORIES::getHashFactory()->massDeletion(array($FACTORIES::FILTER => array($qF), $FACTORIES::ORDER => array($oF)));
            $deleted = $result->rowCount();
            $FACTORIES::getAgentFactory()->getDB()->commit();
            $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
          }
        }
        else {
          $FACTORIES::getAgentFactory()->getDB()->query("TRUNCATE TABLE Hash");
        }
        break;
      case 1:
      case 2:
        $qF = new QueryFilter(HashBinary::HASHLIST_ID, $hashlist->getId(), "=");
        $FACTORIES::getHashBinaryFactory()->massDeletion(array($FACTORIES::FILTER => array($qF)));
        break;
      case 3:
        $qF = new QueryFilter(HashlistHashlist::PARENT_HASHLIST_ID, $hashlist->getId(), "=");
        $FACTORIES::getHashlistHashlistFactory()->massDeletion(array($FACTORIES::FILTER => array($qF)));
        break;
    }

    if (sizeof($taskList) > 0) {
      $qF = new ContainFilter(FileTask::TASK_ID, Util::arrayOfIds($taskList));
      $FACTORIES::getFileTaskFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
      $qF = new ContainFilter(Assignment::TASK_ID, Util::arrayOfIds($taskList));
      $FACTORIES::getAssignmentFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
      $qF = new ContainFilter(Chunk::TASK_ID, Util::arrayOfIds($taskList));
      $FACTORIES::getChunkFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
      $qF = new ContainFilter(AgentError::TASK_ID, Util::arrayOfIds($taskList));
      $FACTORIES::getAgentErrorFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    }
    foreach ($taskList as $task) {
      $FACTORIES::getTaskFactory()->delete($task);
    }

    foreach ($taskWrappers as $taskWrapper) {
      $FACTORIES::getTaskWrapperFactory()->delete($taskWrapper);
    }

    // update/delete superhashlists (this must wait until here because of constraints
    foreach ($toDelete as $hl) {
      $FACTORIES::getHashlistFactory()->delete($hl);
    }
    foreacH ($toUpdate as $hl) {
      $FACTORIES::getHashlistFactory()->update($hl);
    }

    $FACTORIES::getHashlistFactory()->delete($hashlist);

    $FACTORIES::getAgentFactory()->getDB()->commit();
    return $hashlist->getFormat();
  }

  /**
   * @param int $hashlistId
   * @throws HTException
   * @return Hashlist
   */
  public static function getHashlist($hashlistId) {
    global $FACTORIES;

    $hashlist = $FACTORIES::getHashlistFactory()->get($hashlistId);
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
    /** @var DataSet $CONFIG */
    global $FACTORIES, $CONFIG;

    // export cracked hashes to a file
    $hashlist = HashlistUtils::getHashlist($hashlistId);
    $hashlists = Util::checkSuperHashlist($hashlist);

    if (!AccessUtils::userCanAccessHashlists($hashlists, $user)) {
      throw new HTException("No access to the hashlists!");
    }

    $tmpname = "Pre-cracked_" . $hashlist->getId() . "_" . date("d-m-Y_H-i-s") . ".txt";
    $tmpfile = dirname(__FILE__) . "/../../files/$tmpname";
    $factory = $FACTORIES::getHashFactory();
    $format = $FACTORIES::getHashlistFactory()->get($hashlists[0]->getId());
    $orderObject = Hash::HASH_ID;
    if ($format->getFormat() != 0) {
      $factory = $FACTORIES::getHashBinaryFactory();
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
    $count = $factory->countFilter(array($FACTORIES::FILTER => array($qF1, $qF2)));
    $pagingSize = 5000;
    if ($CONFIG->getVal(DConfig::HASHES_PAGE_SIZE) !== false) {
      $pagingSize = $CONFIG->getVal(DConfig::HASHES_PAGE_SIZE);
    }
    $separator = $CONFIG->getVal(DConfig::FIELD_SEPARATOR);
    for ($x = 0; $x * $pagingSize < $count; $x++) {
      $oF = new OrderFilter($orderObject, "ASC LIMIT " . ($x * $pagingSize) . ",$pagingSize");
      $entries = $factory->filter(array($FACTORIES::FILTER => array($qF1, $qF2), $FACTORIES::ORDER => array($oF)));
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

    $file = new File(0, $tmpname, Util::filesize($tmpfile), $hashlist->getIsSecret(), 0, $hashlist->getAccessGroupId());
    $file = $FACTORIES::getFileFactory()->save($file);
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
   * @throws HTException
   * @return Hashlist
   */
  public static function createHashlist($name, $isSalted, $isSecret, $isHexSalted, $separator, $format, $hashtype, $saltSeparator, $accessGroupId, $source, $post, $files, $user) {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;

    $name = htmlentities($name, ENT_QUOTES, "UTF-8");
    $salted = ($isSalted) ? "1" : "0";
    $secret = ($isSecret) ? "1" : "0";
    $hexsalted = ($isHexSalted) ? "1" : "0";
    $format = intval($format);
    $hashtype = intval($hashtype);
    $accessGroup = $FACTORIES::getAccessGroupFactory()->get($accessGroupId);

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

    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $hashlist = new Hashlist(0, $name, $format, $hashtype, 0, $separator, 0, $secret, $hexsalted, $salted, $accessGroup->getId());
    $hashlist = $FACTORIES::getHashlistFactory()->save($hashlist);

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
    $tmpfile = dirname(__FILE__) . "/../../tmp/hashlist_" . $hashlist->getId();
    if (!Util::uploadFile($tmpfile, $source, $dataSource) && file_exists($tmpfile)) {
      $FACTORIES::getAgentFactory()->getDB()->rollback();
      throw new HTException("Failed to process file!");
    }
    else if (!file_exists($tmpfile)) {
      $FACTORIES::getAgentFactory()->getDB()->rollback();
      throw new HTException("Required file does not exist!");
    }
    else if (Util::countLines($tmpfile) > $CONFIG->getVal(DConfig::MAX_HASHLIST_SIZE)) {
      $FACTORIES::getAgentFactory()->getDB()->rollback();
      throw new HTException("Hashlist has too many lines!");
    }
    $file = fopen($tmpfile, "rb");
    if (!$file) {
      throw new HTException("Failed to open file!");
    }
    $FACTORIES::getAgentFactory()->getDB()->commit();
    $added = 0;

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
        $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
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
          $values[] = new Hash(0, $hashlist->getId(), $hash, $salt, "", 0, null, 0);
          $bufferCount++;
          if ($bufferCount >= 10000) {
            $result = $FACTORIES::getHashFactory()->massSave($values);
            $added += $result->rowCount();
            $FACTORIES::getAgentFactory()->getDB()->commit();
            $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
            $values = array();
            $bufferCount = 0;
          }
        }
        if (sizeof($values) > 0) {
          $result = $FACTORIES::getHashFactory()->massSave($values);
          $added += $result->rowCount();
        }
        fclose($file);
        unlink($tmpfile);
        $hashlist->setHashCount($added);
        $FACTORIES::getHashlistFactory()->update($hashlist);
        $FACTORIES::getAgentFactory()->getDB()->commit();
        Util::createLogEntry("User", $user->getId(), DLogEntry::INFO, "New Hashlist created: " . $hashlist->getHashlistName());

        NotificationHandler::checkNotifications(DNotificationType::NEW_HASHLIST, new DataSet(array(DPayloadKeys::HASHLIST => $hashlist)));
        break;
      case DHashlistFormat::WPA:
        $added = 0;
        while (!feof($file)) {
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
          // we cannot save the network name here, as on the submission we don't get this
          $hash = new HashBinary(0, $hashlist->getId(), $mac_ap . $CONFIG->getVal(DConfig::FIELD_SEPARATOR) . $mac_cli . $CONFIG->getVal(DConfig::FIELD_SEPARATOR) . $network, Util::bintohex($data), null, 0, null, 0);
          $FACTORIES::getHashBinaryFactory()->save($hash);
          $added++;
        }
        fclose($file);
        unlink($tmpfile);
        $hashlist->setHashCount($added);
        $FACTORIES::getHashlistFactory()->update($hashlist);
        Util::createLogEntry("User", $user->getId(), DLogEntry::INFO, "New Hashlist created: " . $hashlist->getHashlistName());

        NotificationHandler::checkNotifications(DNotificationType::NEW_HASHLIST, new DataSet(array(DPayloadKeys::HASHLIST => $hashlist)));
        break;
      case DHashlistFormat::BINARY:
        if (!feof($file)) {
          $data = fread($file, Util::filesize($tmpfile));
          $hash = new HashBinary(0, $hashlist->getId(), "", Util::bintohex($data), "", 0, null, 0);
          $FACTORIES::getHashBinaryFactory()->save($hash);
        }
        fclose($file);
        unlink($tmpfile);
        $hashlist->setHashCount(1);
        $FACTORIES::getHashlistFactory()->update($hashlist);
        Util::createLogEntry("User", $user->getId(), DLogEntry::INFO, "New Hashlist created: " . $hashlist->getHashlistName());

        NotificationHandler::checkNotifications(DNotificationType::NEW_HASHLIST, new DataSet(array(DPayloadKeys::HASHLIST => $hashlist)));
        break;
    }
    return $hashlist;
  }

  /**
   * @param int[] $hashlists
   * @param string $name
   * @throws HTException
   */
  public static function createSuperhashlist($hashlists, $name, $user) {
    global $FACTORIES;

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
    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $lists = $FACTORIES::getHashlistFactory()->filter(array($FACTORIES::FILTER => $qF));
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
    }

    $superhashlist = new Hashlist(0, $name, DHashlistFormat::SUPERHASHLIST, $lists[0]->getHashtypeId(), $hashcount, $lists[0]->getSaltSeparator(), $cracked, 0, $lists[0]->getHexSalt(), $lists[0]->getIsSalted(), $accessGroupId);
    $superhashlist = $FACTORIES::getHashlistFactory()->save($superhashlist);
    $relations = array();
    foreach ($lists as $list) {
      $relations[] = new HashlistHashlist(0, $superhashlist->getId(), $list->getId());
    }
    $FACTORIES::getHashlistHashlistFactory()->massSave($relations);
    $FACTORIES::getAgentFactory()->getDB()->commit();
  }

  /**
   * @param int $hashlistId
   * @param User $user
   * @return File
   * @throws HTException
   */
  public static function leftlist($hashlistId, $user) {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;

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
    $tmpfile = dirname(__FILE__) . "/../../files/$tmpname";

    $file = fopen($tmpfile, "wb");
    if (!$file) {
      throw new HTException("Failed to write file!");
    }

    $qF1 = new ContainFilter(Hash::HASHLIST_ID, $hashlistIds);
    $qF2 = new QueryFilter(Hash::IS_CRACKED, "0", "=");
    $count = $FACTORIES::getHashFactory()->countFilter(array($FACTORIES::FILTER => array($qF1, $qF2)));
    $pagingSize = 5000;
    if ($CONFIG->getVal(DConfig::HASHES_PAGE_SIZE) !== false) {
      $pagingSize = $CONFIG->getVal(DConfig::HASHES_PAGE_SIZE);
    }
    for ($x = 0; $x * $pagingSize < $count; $x++) {
      $oF = new OrderFilter(Hash::HASH_ID, "ASC LIMIT " . ($x * $pagingSize) . ",$pagingSize");
      $entries = $FACTORIES::getHashFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2), $FACTORIES::ORDER => array($oF)));
      $buffer = "";
      foreach ($entries as $entry) {
        $buffer .= $entry->getHash();
        if ($hashlist->getIsSalted()) {
          $buffer .= $CONFIG->getVal(DConfig::FIELD_SEPARATOR) . $entry->getSalt();
        }
        $buffer .= "\n";
      }
      fputs($file, $buffer);
    }
    fclose($file);
    usleep(1000000);

    $file = new File(0, $tmpname, Util::filesize($tmpfile), $hashlist->getIsSecret(), 0, $hashlist->getAccessGroupId());
    $file = $FACTORIES::getFileFactory()->save($file);
    return $file;
  }
}