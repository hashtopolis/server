<?php

use DBA\AbstractModel;
use DBA\AccessGroup;
use DBA\AccessGroupUser;
use DBA\AgentZap;
use DBA\Assignment;
use DBA\Chunk;
use DBA\ContainFilter;
use DBA\CrackerBinary;
use DBA\File;
use DBA\FileTask;
use DBA\Hash;
use DBA\Hashlist;
use DBA\HashlistHashlist;
use DBA\JoinFilter;
use DBA\LogEntry;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\StoredValue;
use DBA\Task;
use DBA\TaskWrapper;
use DBA\Zap;
use DBA\AgentBinary;
use DBA\AgentStat;

/**
 *
 * @author Sein
 *
 *         Bunch of useful static functions.
 */
class Util {
	/**
	 * @param string $type
	 * @param string $version
	 */
	public static function checkAgentVersion($type, $version){
		global $FACTORIES;

		$qF = new QueryFilter(AgentBinary::TYPE, $type, "=");
		$binary = $FACTORIES::getAgentBinaryFactory()->filter(array($FACTORIES::FILTER => $qF), true);
		if ($binary != null) {
			if (Util::versionComparison($binary->getVersion(), $version) == 1) {
				echo "update $type version... ";
				$binary->setVersion($version);
				$FACTORIES::getAgentBinaryFactory()->update($binary);
				echo "OK";
			}
		}
	}

  public static function isYubikeyEnabled() {
    /** @var $CONFIG DataSet */
    global $CONFIG;

    $clientId = $CONFIG->getVal(DConfig::YUBIKEY_ID);
    if (!is_numeric($clientId) || $clientId <= 0) {
      return false;
    }
    $secretKey = $CONFIG->getVal(DConfig::YUBIKEY_KEY);
    if (!base64_decode($secretKey)) {
      return false;
    }
    $apiUrl = $CONFIG->getVal(DConfig::YUBIKEY_URL);
    if (filter_var($apiUrl, FILTER_VALIDATE_URL) === false) {
      return false;
    }
    return true;
  }

  /**
   * @param $issuer string API or User
   * @param $issuerId string either the ID of the user or the token of the client
   * @param $level string
   * @param $message string
   */
  public static function createLogEntry($issuer, $issuerId, $level, $message) {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;

    $count = $FACTORIES::getLogEntryFactory()->countFilter(array());
    if ($count > $CONFIG->getVal(DConfig::NUMBER_LOGENTRIES) * 1.2) {
      // if we have exceeded the log entry limit by 20%, delete the oldest ones
      $toDelete = floor($CONFIG->getVal(DConfig::NUMBER_LOGENTRIES) * 0.2);
      $oF = new OrderFilter(LogEntry::TIME, "ASC LIMIT $toDelete");
      $FACTORIES::getLogEntryFactory()->massDeletion(array($FACTORIES::ORDER => $oF));
    }

    $entry = new LogEntry(0, $issuer, $issuerId, $level, $message, time());
    $FACTORIES::getLogEntryFactory()->save($entry);

    switch ($level) {
      case DLogEntry::ERROR:
        NotificationHandler::checkNotifications(DNotificationType::LOG_ERROR, new DataSet(array(DPayloadKeys::LOG_ENTRY => $entry)));
        break;
      case DLogEntry::FATAL:
        NotificationHandler::checkNotifications(DNotificationType::LOG_FATAL, new DataSet(array(DPayloadKeys::LOG_ENTRY => $entry)));
        break;
      case DLogEntry::WARN:
        NotificationHandler::checkNotifications(DNotificationType::LOG_WARN, new DataSet(array(DPayloadKeys::LOG_ENTRY => $entry)));
        break;
    }
  }

  /**
   * Scans the import-directory for files. Directories are ignored.
   * @return array of all files in the top-level directory /../import
   */
  public static function scanImportDirectory() {
    $directory = dirname(__FILE__) . "/../import";
    if (file_exists($directory) && is_dir($directory)) {
      $importDirectory = opendir($directory);
      $importFiles = array();
      while ($file = readdir($importDirectory)) {
        if ($file[0] != '.' && $file != "." && $file != ".." && !is_dir($file)) {
          $importFiles[] = new DataSet(array("file" => $file, "size" => Util::filesize($directory . "/" . $file)));
        }
      }
      sort($importFiles);
      return $importFiles;
    }
    return array();
  }

  /**
   * Calculates variable. Used in Templates.
   * @param $in mixed calculation to be done
   * @return mixed
   */
  public static function calculate($in) {
    return $in;
  }

  /**
   * Saves a file into the DB using the FileFactory.
   * @param $path string
   * @param $name string
   * @param $type string
   * @return bool true if the save of the file model succeeded
   */
  public static function insertFile($path, $name, $type) {
    global $FACTORIES;

    $fileType = DFileType::OTHER;
    if ($type == 'rule') {
      $fileType = DFileType::RULE;
    }
    else if ($type == 'dict'){
      $fileType = DFileType::WORDLIST;
    }
    $file = new File(0, $name, Util::filesize($path), 1, $fileType);
    $file = $FACTORIES::getFileFactory()->save($file);
    if ($file == null) {
      return false;
    }
    return true;
  }

  /**
   * @param $task Task
   * @return array
   */
  public static function getTaskInfo($task) {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;

    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
    $progress = 0;
    $cracked = 0;
    $maxTime = 0;
    $totalTimeSpent = 0;
    foreach ($chunks as $chunk) {
      if ($chunk->getDispatchTime() > 0 && $chunk->getSolveTime() > 0) {
        $totalTimeSpent += $chunk->getSolveTime() - $chunk->getDispatchTime();
      }
      $progress += $chunk->getCheckpoint() - $chunk->getSkip();
      $cracked += $chunk->getCracked();
      if ($chunk->getDispatchTime() > $maxTime) {
        $maxTime = $chunk->getDispatchTime();
      }
      if ($chunk->getSolveTime() > $maxTime) {
        $maxTime = $chunk->getSolveTime();
      }
    }

    $isActive = false;
    if (time() - $maxTime < $CONFIG->getVal(DConfig::CHUNK_TIMEOUT) && ($progress < $task->getKeyspace() || $task->getIsPrince() && $task->getKeyspace() == DPrince::PRINCE_KEYSPACE)) {
      $isActive = true;
    }
    return array($progress, $cracked, $isActive, sizeof($chunks), ($totalTimeSpent > 0) ? round($cracked * 60 / $totalTimeSpent, 2) : 0);
  }

  /**
   * @param $task Task
   * @return array
   */
  public static function getFileInfo($task) {
    global $FACTORIES;

    $qF = new QueryFilter(FileTask::TASK_ID, $task->getId(), "=", $FACTORIES::getFileTaskFactory());
    $jF = new JoinFilter($FACTORIES::getFileTaskFactory(), FileTask::FILE_ID, File::FILE_ID);
    $joinedFiles = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    /** @var $files File[] */
    $files = $joinedFiles[$FACTORIES::getFileFactory()->getModelName()];
    $sizeFiles = 0;
    $fileSecret = false;
    foreach ($files as $file) {
      if ($file->getIsSecret() == 1) {
        $fileSecret = true;
      }
      $sizeFiles += $file->getSize();
    }
    return array(sizeof($files), $fileSecret, $sizeFiles, $files);
  }

  /**
   * @param $task Task
   * @return array
   */
  public static function getChunkInfo($task) {
    global $FACTORIES;

    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
    $cracked = 0;
    foreach ($chunks as $chunk) {
      $cracked += $chunk->getCracked();
    }

    $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $numAssignments = $FACTORIES::getAssignmentFactory()->countFilter(array($FACTORIES::FILTER => $qF));

    return array(sizeof($chunks), $cracked, $numAssignments);
  }

  /**
   * @param $userId int
   * @return array
   */
  public static function getAccessGroupIds($userId) {
    global $FACTORIES;

    $qF = new QueryFilter(AccessGroupUser::USER_ID, $userId, "=", $FACTORIES::getAccessGroupUserFactory());
    $jF = new JoinFilter($FACTORIES::getAccessGroupUserFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupUser::ACCESS_GROUP_ID);
    $joined = $FACTORIES::getAccessGroupFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    /** @var $accessGroups AccessGroup[] */
    $accessGroups = $joined[$FACTORIES::getAccessGroupFactory()->getModelName()];
    return Util::arrayOfIds($accessGroups);
  }

  public static function loadTasks($archived = false) {
    /** @var $LOGIN Login */
    global $FACTORIES, $OBJECTS, $LOGIN;

    $accessGroupIds = Util::getAccessGroupIds($LOGIN->getUserID());

    $qF1 = new ContainFilter(TaskWrapper::ACCESS_GROUP_ID, $accessGroupIds);
    $qF2 = new QueryFilter(TaskWrapper::IS_ARCHIVED, ($archived)?1:0, "=");
    $oF1 = new OrderFilter(TaskWrapper::PRIORITY, "DESC");
    $oF2 = new OrderFilter(TaskWrapper::TASK_WRAPPER_ID, "DESC");
    $taskWrappers = $FACTORIES::getTaskWrapperFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2), $FACTORIES::ORDER => array($oF1, $oF2)));

    $taskList = array();
    foreach ($taskWrappers as $taskWrapper) {
      $set = new DataSet();
      $set->addValue('taskType', $taskWrapper->getTaskType());

      $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
      if ($taskWrapper->getTaskType() == DTaskTypes::SUPERTASK) {
        // supertask
        $set->addValue('supertaskName', $taskWrapper->getTaskWrapperName());

        $hashlist = $FACTORIES::getHashlistFactory()->get($taskWrapper->getHashlistId());

        $set->addValue('hashlistId', $hashlist->getId());
        $set->addValue('taskWrapperId', $taskWrapper->getId());
        $set->addValue('hashlistName', $hashlist->getHashlistName());
        $set->addValue('hashlistSecret', $hashlist->getIsSecret());
        $set->addValue('hashCount', $hashlist->getHashCount());
        $set->addValue('hashlistCracked', $hashlist->getCracked());
        $set->addValue('priority', $taskWrapper->getPriority());

        $oF = new OrderFilter(Task::PRIORITY, "DESC");
        $tasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => $oF));
        $subtaskList = array();

        $tasksDone = 0;
        $isActive = false;
        $cracked = 0;
        $numAssignments = 0;
        $numFiles = 0;
        $fileSecret = false;
        $filesSize = 0;

        foreach ($tasks as $task) {
          $subSet = new DataSet();
          $subSet->addValue('color', $task->getColor());
          $subSet->addValue('taskId', $task->getId());
          $subSet->addValue('attackCmd', $task->getAttackCmd());
          $subSet->addValue('taskName', $task->getTaskName());
          $subSet->addValue('keyspace', $task->getKeyspace());
          $subSet->addValue('cpuOnly', $task->getIsCpuTask());
          $subSet->addValue('isSmall', $task->getIsSmall());
          $subSet->addValue('isPrince', $task->getIsPrince());
          $subSet->addValue('chunkTime', $task->getChunkTime());
          $subSet->addValue('taskProgress', $task->getKeyspaceProgress());
          $subSet->addValue('priority', $task->getPriority());


          $taskInfo = Util::getTaskInfo($task);
          $fileInfo = Util::getFileInfo($task);
          $chunkInfo = Util::getChunkInfo($task);

          $subSet->addValue('sumProgress', $taskInfo[0]);
          $subSet->addValue('numFiles', $fileInfo[0]);
          $subSet->addValue('fileSecret', $fileInfo[1]);
          $subSet->addValue('filesSize', $fileInfo[2]);
          $subSet->addValue('numChunks', $chunkInfo[0]);
          $subSet->addValue('isActive', $taskInfo[2]);
          $subSet->addValue('cracked', $taskInfo[1]);
          $subSet->addValue('numAssignments', $chunkInfo[2]);
          $subSet->addValue('performance', $taskInfo[4]);

          if ($taskInfo[0] >= $task->getKeyspace() && $task->getKeyspace() > 0) {
            $tasksDone++;
          }
          $isActive = $isActive || $taskInfo[2];
          $cracked += $taskInfo[1];
          $numAssignments += $chunkInfo[2];
          $numFiles += $fileInfo[0];
          $fileSecret = $fileSecret || $fileInfo[1];
          $filesSize += $fileInfo[2];

          $subtaskList[] = $subSet;
        }

        $set->addValue('tasksDone', $tasksDone);
        $set->addValue('isActive', $isActive);
        $set->addValue('cracked', $cracked);
        $set->addValue('numAssignments', $numAssignments);
        $set->addValue('numFiles', $numFiles);
        $set->addValue('fileSecret', $fileSecret);
        $set->addValue('filesSize', $filesSize);
        $set->addValue('numTasks', sizeof($tasks));
        $set->addValue('subtaskList', $subtaskList);
        $taskList[] = $set;
      }
      else {
        // normal task
        $task = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF), true);
        if ($task == null) {
          Util::createLogEntry(DLogEntryIssuer::USER, $LOGIN->getUserID(), DLogEntry::WARN, "TaskWrapper (" . $taskWrapper->getId() . ") for normal task existing with containing no task!");
          continue;
        }
        $taskInfo = Util::getTaskInfo($task);
        $fileInfo = Util::getFileInfo($task);
        $chunkInfo = Util::getChunkInfo($task);
        $hashlist = $FACTORIES::getHashlistFactory()->get($taskWrapper->getHashlistId());
        $set->addValue('taskId', $task->getId());
        $set->addValue('color', $task->getColor());
        $set->addValue('hasColor', (strlen($task->getColor()) == 0) ? false : true);
        $set->addValue('attackCmd', $task->getAttackCmd());
        $set->addValue('taskName', $task->getTaskName());
        $set->addValue('isCpu', $task->getIsCpuTask());
        $set->addValue('isSmall', $task->getIsSmall());
        $set->addValue('hashlistId', $taskWrapper->getHashlistId());
        $set->addValue('hashlistName', $hashlist->getHashlistName());
        $set->addValue('hashCount', $hashlist->getHashCount());
        $set->addValue('hashlistCracked', $hashlist->getCracked());
        $set->addValue('chunkTime', $task->getChunkTime());
        $set->addValue('isSecret', $hashlist->getIsSecret());
        $set->addValue('isPrince', $task->getIsPrince());
        $set->addValue('priority', $task->getPriority());
        $set->addValue('keyspace', $task->getKeyspace());
        $set->addValue('isActive', $taskInfo[2]);
        $set->addValue('sumProgress', $taskInfo[0]);
        $set->addValue('numFiles', $fileInfo[0]);
        $set->addValue('taskProgress', $task->getKeyspaceProgress());
        $set->addValue('fileSecret', $fileInfo[1]);
        $set->addValue('fileSizes', $fileInfo[2]);
        $set->addValue('numAssignments', $chunkInfo[2]);
        $set->addValue('crackedCount', $chunkInfo[1]);
        $set->addValue('numChunks', $chunkInfo[0]);
        $set->addValue('performance', $taskInfo[4]);
        $taskList[] = $set;
      }
    }
    $OBJECTS['taskList'] = $taskList;
  }

  /**
   * @param $taskWrapper TaskWrapper
   * @return bool
   */
  public static function checkTaskWrapperCompleted($taskWrapper) {
    global $FACTORIES;

    $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
    $tasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF));
    foreach ($tasks as $task) {
      $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
      $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
      $sumProg = 0;
      foreach ($chunks as $chunk) {
        if ($chunk->getProgress() < 10000) {
          return false;
        }
        else {
          $sumProg += $chunk->getLength();
        }
      }
      if ($task->getKeyspace() == 0 || $sumProg < $task->getKeyspace()) {
        return false;
      }
    }
    return true;
  }

  public static function agentStatCleaning() {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;

    $entry = $FACTORIES::getStoredValueFactory()->get(DStats::LAST_STAT_CLEANING);
    if ($entry == null) {
      $entry = new StoredValue(DStats::LAST_STAT_CLEANING, 0);
      $FACTORIES::getStoredValueFactory()->save($entry);
    }
    if (time() - $entry->getVal() > 600) {
      $lifetime = intval($CONFIG->getVal(DConfig::AGENT_DATA_LIFETIME));
      if($lifetime <= 0){
        $lifetime = 3600;
      }
      $qF = new QueryFilter(AgentStat::TIME, time() - $lifetime, "<=");
      $FACTORIES::getAgentStatFactory()->massDeletion(array($FACTORIES::FILTER => $qF));

      $entry->setVal(time());
      $FACTORIES::getStoredValueFactory()->update($entry);
    }
  }

  /**
   * Used by the solver. Cleans the zap-queue
   */
  public static function zapCleaning() {
    global $FACTORIES;

    $entry = $FACTORIES::getStoredValueFactory()->get(DZaps::LAST_ZAP_CLEANING);
    if ($entry == null) {
      $entry = new StoredValue(DZaps::LAST_ZAP_CLEANING, 0);
      $FACTORIES::getStoredValueFactory()->save($entry);
    }
    if (time() - $entry->getVal() > 600) {
      $zapFilter = new QueryFilter(Zap::SOLVE_TIME, time() - 600, "<=");

      // delete dependencies on AgentZap
      $zaps = $FACTORIES::getZapFactory()->filter(array($FACTORIES::FILTER => $zapFilter));
      $zapIds = Util::arrayOfIds($zaps);
      $uS = new UpdateSet(AgentZap::LAST_ZAP_ID, null);
      $qF = new ContainFilter(AgentZap::LAST_ZAP_ID, $zapIds);
      $FACTORIES::getAgentZapFactory()->massUpdate(array($FACTORIES::FILTER => $qF, $FACTORIES::UPDATE => $uS));

      $FACTORIES::getZapFactory()->massDeletion(array($FACTORIES::FILTER => $zapFilter));

      $entry->setVal(time());
      $FACTORIES::getStoredValueFactory()->update($entry);
    }
  }

  /**
   * This filesize is able to determine the file size of a given file, also if it's bigger than 4GB which causes
   * some problems with the built-in filesize() function of PHP.
   * @param $file string Filepath you want to get the size from
   * @return int -1 if the file doesn't exist, else filesize
   */
  public static function filesize($file) {
    if (!file_exists($file)) {
      return -1;
    }
    $fp = fopen($file, "rb");
    $pos = 0;
    $size = 1073741824;
    fseek($fp, 0, SEEK_SET);
    while ($size > 1) {
      fseek($fp, $size, SEEK_CUR);

      if (fgetc($fp) === false) {
        fseek($fp, -$size, SEEK_CUR);
        $size = (int)($size / 2);
      }
      else {
        fseek($fp, -1, SEEK_CUR);
        $pos += $size;
      }
    }

    while (fgetc($fp) !== false) {
      $pos++;
    }

    return $pos;
  }

  /**
   * Refreshes the page with the current url, also includes the query string.
   */
  public static function refresh() {
    global $_SERVER;

    $url = $_SERVER['PHP_SELF'];
    if (strlen($_SERVER['QUERY_STRING']) > 0) {
      $url .= "?" . $_SERVER['QUERY_STRING'];
    }
    header("Location: $url");
    die();
  }

  /**
   * Checks if the given list is a superhashlist and returns an array containing all hashlists belonging to this superhashlist.
   * If the hashlist is not a superhashlist it just returns an array containing the list itself.
   *
   * @param $hashlist Hashlist
   * @return Hashlist[] of all superhashlists belonging to the $list
   */
  public static function checkSuperHashlist($hashlist) {
    global $FACTORIES;

    if ($hashlist->getFormat() == 3) {
      $hashlistJoinFilter = new JoinFilter($FACTORIES::getHashlistFactory(), Hashlist::HASHLIST_ID, HashlistHashlist::HASHLIST_ID);
      $superHashListFilter = new QueryFilter(HashlistHashlist::PARENT_HASHLIST_ID, $hashlist->getId(), "=");
      $joined = $FACTORIES::getHashlistHashlistFactory()->filter(array($FACTORIES::JOIN => $hashlistJoinFilter, $FACTORIES::FILTER => $superHashListFilter));
      $lists = $joined[$FACTORIES::getHashlistFactory()->getModelName()];
      return $lists;
    }
    return array($hashlist);
  }

  /**
   * Tries to determine the IP of the client.
   * @return string 0.0.0.0 or the client IP
   */
  public static function getIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }
    if (!$ip) {
      return "0.0.0.0";
    }
    return $ip;
  }

  /**
   * Checks if files are writable. If at least one of the files in the list is not writable it returns false.
   * @param $arr array of files to check
   * @return bool
   */
  public static function checkWriteFiles($arr) {
    foreach ($arr as $path) {
      if (!is_writable($path)) {
        return false;
      }
    }
    return true;
  }

  /**
   * Iterates through all chars, converts them to 0x__ and concats the hexes
   * @param $binString String you want to convert
   * @return string Hex-String
   */
  public static function bintohex($binString) {
    $return = "";
    for ($i = 0; $i < strlen($binString); $i++) {
      $hex = dechex(ord($binString[$i]));
      while (strlen($hex) < 2) {
        $hex = "0" . $hex;
      }
      $return .= $hex;
    }
    return $return;
  }

  /**
   * Checks if the task is completed and returns the html tick image if this is the case.
   * @param $prog int progress so far
   * @param $total int total to be done
   * @return string either the check.png with Finished or an empty string
   */
  public static function tickdone($prog, $total) {
    // show tick of progress is done
    if ($total > 0 && $prog >= $total) {
      return ' <span class="fas fa-check" aria-hidden="true"></span>';
    }
    return "";
  }

  /**
   * Returns the username from the given userId
   * @param $id int ID for the user
   * @return string username or unknown-id
   */
  public static function getUsernameById($id) {
    global $FACTORIES;

    $user = $FACTORIES::getUserFactory()->get($id);
    if ($user === null) {
      return "Unknown" . (strlen($id) > 0) ? "-$id" : "";
    }
    return $user->getUsername();
  }

  /**
   * Used in Template. Converts seconds to human readable format
   * @param $seconds
   * @return string
   */
  public static function sectotime($seconds) {
    $return = "";
    if ($seconds > 86400) {
      $days = floor($seconds / 86400);
      $return = $days . "d ";
      $seconds = $seconds % 86400;
    }
    $return .= gmdate("H:i:s", $seconds);
    return $return;
  }

  /**
   * Escapes some special string which should be put as value in form fields to avoid breaking. This function should still be used
   * together with htmlentities(), this function just cares about some special cases which are not handled by htmlentities().
   * @param $string string to check
   * @return string escaped string
   */
  public static function escapeSpecial($string) {
    $string = htmlentities($string, ENT_QUOTES, "UTF-8");
    $string = str_replace('"', '&#34;', $string);
    $string = str_replace("'", "&#39;", $string);
    $string = str_replace('`', '&#96;', $string);
    return $string;
  }

  /**
   * Checks if the given string contains characters which are blacklisted
   * @param $string string
   * @return bool true if at least one character is in the blacklist
   */
  public static function containsBlacklistedChars($string) {
    /** @var $CONFIG DataSet */
    global $CONFIG;

    for ($i = 0; $i < strlen($CONFIG->getVal(DConfig::BLACKLIST_CHARS)); $i++) {
      if (strpos($string, $CONFIG->getVal(DConfig::BLACKLIST_CHARS)[$i]) !== false) {
        return true;
      }
    }
    return false;
  }

  /**
   * Used in Template
   * TODO: this should be made a bit better
   * @param $val string of the array
   * @param $id int index of the array
   * @return string the element or empty string
   */
  public static function getStaticArray($val, $id) {
    $platforms = array(
      "unknown",
      "NVidia",
      "AMD",
      "CPU"
    );
    $oses = array(
      '<span class="fab fa-linux" aria-hidden="true"></span>',
      '<span class="fab fa-windows" aria-hidden="true"></span>',
      '<span class="fab fa-apple" aria-hidden="true"></span>'
    );
    $formats = array(
      "Text",
      "HCCAPX",
      "Binary",
      "Superhashlist"
    );
    $formattables = array(
      "hashes",
      "hashes_binary",
      "hashes_binary"
    );
    $states = array(
      "New",
      "Init",
      "Running",
      "Paused",
      "Exhausted",
      "Cracked",
      "Aborted",
      "Quit",
      "Bypass",
      "Trimmed",
      "Aborting..."
    );
    switch ($id) {
      case 'os':
        if ($val == '-1') {
          return $platforms[0];
        }
        return $oses[$val];
      case 'states':
        return $states[$val];
      case 'formats':
        return $formats[$val];
      case 'formattables':
        return $formattables[$val];
      case 'platforms':
        if ($val == '-1') {
          return $platforms[0];
        }
        return $platforms[$val];
    }
    return "";
  }

  /**
   * @param $binary1 CrackerBinary
   * @param $binary2 CrackerBinary
   * @return int
   */
  public static function versionComparisonBinary($binary1, $binary2) {
    return Util::versionComparison($binary1->getVersion(), $binary2->getVersion());
  }

  /**
   * @param $version1
   * @param $version2
   * @return int 1 if version2 is newer, 0 if equal and -1 if version1 is newer
   */
  public static function versionComparison($version1, $version2) {
    $version1 = explode(".", $version1);
    $version2 = explode(".", $version2);

    for ($i = 0; $i < sizeof($version1) && $i < sizeof($version2); $i++) {
      $num1 = (int)$version1[$i];
      $num2 = (int)$version2[$i];
      if ($num1 > $num2) {
        return -1;
      }
      else if ($num1 < $num2) {
        return 1;
      }
    }
    if (sizeof($version1) > sizeof($version2)) {
      return -1;
    }
    else if (sizeof($version1) < sizeof($version2)) {
      return 1;
    }
    return 0;
  }

  /**
   * Shows big numbers with the right suffixes (k, M, G)
   * @param $num int integer you want formatted
   * @param int $threshold default 1024
   * @param int $divider default 1024
   * @return string Formatted Integer
   */
  public static function nicenum($num, $threshold = 1024, $divider = 1024) {
    $r = 0;
    while ($num > $threshold) {
      $num /= $divider;
      $r++;
    }
    $rs = array(
      "",
      "k",
      "M",
      "G"
    );
    $return = Util::niceround($num, 2);
    return $return . " " . $rs[$r];
  }

  /**
   * Formats percentage nicely
   * @param $part int progress
   * @param $total int total value
   * @param int $decs decimals you want rounded
   * @return string formatted percentage
   */
  public static function showperc($part, $total, $decs = 2) {
    if ($total > 0) {
      $percentage = round(($part / $total) * 100, $decs);
      if ($percentage == 100 && $part < $total) {
        $percentage -= 1 / (10 ^ $decs);
      }
      if ($percentage == 0 && $part > 0) {
        $percentage += 1 / (10 ^ $decs);
      }
    }
    else {
      $percentage = 0;
    }
    $return = Util::niceround($percentage, $decs);
    return $return;
  }

  /**
   * Puts a given file at the right place, depending on which action is used to add a file.
   * TODO: this function can be improved, some else blocks can be removed when handling a bit differently
   * @param $target string File you want to write to
   * @param $type string paste, upload, import or url
   * @param $sourcedata string|array
   * @return array (boolean, string) success, msg detailing what happened
   */
  public static function uploadFile($target, $type, $sourcedata) {
    $success = false;
    $msg = "ALL_OK";
    if (!file_exists($target)) {
      switch ($type) {
        case "paste":
          if (file_put_contents($target, $sourcedata)) {
            $success = true;
          }
          else {
            $msg = "Unable to save pasted content!";
          }
          break;

        case "upload":
          $hashfile = $sourcedata;
          if ($hashfile["error"] == 0) {
            if (move_uploaded_file($hashfile["tmp_name"], $target) && file_exists($target)) {
              $success = true;
            }
            else {
              $msg = "Failed to move uploaded file to right place!";
            }
          }
          else {
            $msg = "File upload failed: " . $hashfile['error'];
          }
          break;

        case "import":
          if (file_exists(dirname(__FILE__) . "/../import/" . $sourcedata)) {
            rename(dirname(__FILE__) . "/../import/" . $sourcedata, $target);
            if (file_exists($target)) {
              $success = true;
            }
            else {
              $msg = "Renaming of file from import directory failed!";
            }
          }
          else {
            $msg = "Source file in import directory does not exist!";
          }
          break;

        case "url":
          $furl = fopen($sourcedata, "rb");
          if (!$furl) {
            $msg = "Could not open url at source data!";
          }
          else {
            $fileLocation = fopen($target, "w");
            if (!$fileLocation) {
              $msg = "Could not open target file!";
            }
            else {
              $downed = 0;
              $buffersize = 131072;
              $last_logged = time();
              while (!feof($furl)) {
                if (!$data = fread($furl, $buffersize)) {
                  $msg = "READ ERROR on download";
                  break;
                }
                fwrite($fileLocation, $data);
                $downed += strlen($data);
                if ($last_logged < time() - 10) {
                  $last_logged = time();
                }
              }
              fclose($fileLocation);
              $success = true;
            }
            fclose($furl);
          }
          break;

        default:
          $msg = "Unknown import type!";
          break;
      }
    }
    else {
      $msg = "File already exists!";
    }
    return array($success, $msg);
  }

  public static function getFileExtension($os) {
    switch ($os) {
      case DOperatingSystem::LINUX:
        $ext = ".bin";
        break;
      case DOperatingSystem::WINDOWS:
        $ext = ".exe";
        break;
      case DOperatingSystem::OSX:
        $ext = ".osx";
        break;
      default:
        $ext = "";
    }
    return $ext;
  }

  /**
   * This function determines the protocol, domain and port of the webserver and puts it together as baseurl.
   * @return string basic server url
   */
  public static function buildServerUrl() {
    /** @var $CONFIG DataSet */
    global $CONFIG;

    // when the server hostname is set on the config, use this
    if (strlen($CONFIG->getVal(DConfig::BASE_HOST)) > 0) {
      return $CONFIG->getVal(DConfig::BASE_HOST);
    }

    $protocol = (isset($_SERVER['HTTPS']) && (strcasecmp('off', $_SERVER['HTTPS']) !== 0)) ? "https://" : "http://";
    $hostname = $_SERVER['HTTP_HOST'];
    $port = $_SERVER['SERVER_PORT'];
    if (strpos($hostname, ":") !== false) {
      $hostname = substr($hostname, 0, strpos($hostname, ":"));
    }
    if ($protocol == "https://" && $port == 443 || $protocol == "http://" && $port == 80) {
      $port = "";
    }
    else {
      $port = ":$port";
    }
    return $protocol . $hostname . $port;
  }

  /**
   * Round to a specific amount of decimal points
   * @param $num Number
   * @param $dec Number of decimals
   * @return string Rounded value
   */
  public static function niceround($num, $dec) {
    $return = strval(round($num, $dec));
    if ($dec > 0) {
      $pointPosition = strpos($return, ".");
      if ($pointPosition === false) {
        $return .= ".00";
      }
      else {
        while (strlen($return) - $pointPosition <= $dec) {
          $return .= "0";
        }
      }
    }
    return $return;
  }

  /**
   * Cut a string to a certain number of letters. If the string is too long, instead replaces the last three letters with ...
   * @param $string String you want to short
   * @param $length Number of Elements you want the string to have
   * @return string Formatted string
   */
  public static function shortenstring($string, $length) {
    // shorten string that would be too long
    $return = "<span title='$string'>";
    if (strlen($string) > $length) {
      $return .= substr($string, 0, $length - 3) . "...";
    }
    else {
      $return .= $string;
    }
    $return .= "</span>";
    return $return;
  }

  /**
   * Adds 0s to the beginning of a number until it reaches size.
   * @param $number
   * @param $size
   * @return string
   */
  public static function prefixNum($number, $size) {
    $formatted = "" . $number;
    while (strlen($formatted) < $size) {
      $formatted = "0" . $formatted;
    }
    return $formatted;
  }

  /**
   * Converts a given string to hex code.
   *
   * @param string $string
   *          string to convert
   * @return string converted string into hex
   */
  public static function strToHex($string) {
    return implode(unpack("H*", $string));
  }

  /**
   * @param $a Chunk
   * @param $b Chunk
   * @return int
   */
  public static function compareChunksTime($a, $b) {
    if ($a->getDispatchTime() == $b->getDispatchTime()) {
      return 0;
    }
    return ($a->getDispatchTime() < $b->getDispatchTime()) ? -1 : 1;
  }

  /**
   * This sends a given email with text and subject to the address.
   *
   * @param string $address
   *          email address of the receiver
   * @param string $subject
   *          subject of the email
   * @param string $text
   *          html content of the email
   * @param string $plaintext plaintext version of the email content
   * @return true on success, false on failure
   */
  public static function sendMail($address, $subject, $text, $plaintext) {
    /** @var $CONFIG DataSet */
    global $CONFIG;

    $boundary = uniqid('np');

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "From: " . $CONFIG->getVal(Dconfig::EMAIL_SENDER_NAME) . " <" . $CONFIG->getVal(DConfig::EMAIL_SENDER) . ">\r\n";
    $headers .= "To: " . $address . "\r\n";
    $headers .= "Content-Type: multipart/alternative;boundary=" . $boundary . "\r\n";

    $plainMessage = "\r\n\r\n--" . $boundary . "\r\n";
    $plainMessage .= "Content-type: text/plain;charset=utf-8\r\n\r\n";
    $plainMessage .= $plaintext;

    $htmlMessage = "\r\n\r\n--" . $boundary . "\r\n";
    $htmlMessage .= "Content-type: text/html;charset=utf-8\r\n\r\n";
    $htmlMessage .= $text;
    $htmlMessage .= "\r\n\r\n--" . $boundary . "--";

    if (!mail($address, $subject, $plainMessage . $htmlMessage, $headers)) {
      return false;
    }
    return true;
  }

  /**
   * Generates a random string with mixedalphanumeric chars
   *
   * @param int $length
   *          length of random string to generate
   * @return string random string
   */
  public static function randomString($length) {
    $charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $result = "";
    for ($x = 0; $x < $length; $x++) {
      $result .= $charset[mt_rand(0, strlen($charset) - 1)];
    }
    return $result;
  }

  /**
   * Checks if $search starts with $pattern. Shortcut for strpos==0
   * @param $search
   * @param $pattern
   * @return bool
   */
  public static function startsWith($search, $pattern) {
    if (strpos($search, $pattern) === 0) {
      return true;
    }
    return false;
  }

  /**
   * if pattern is empty or if pattern is at the end of search
   * @param $search
   * @param $pattern
   * @return bool
   */
  public static function endsWith($search, $pattern) {
    // search forward starting from end minus needle length characters
    return $pattern === "" || (($temp = strlen($search) - strlen($pattern)) >= 0 && strpos($search, $pattern, $temp) !== FALSE);
  }

  /**
   * Converts a hex to binary
   * @param $data
   * @return string
   */
  public static function hextobin($data) {
    $res = "";
    for ($i = 0; $i < strlen($data) - 1; $i += 2) {
      $res .= chr(hexdec(substr($data, $i, 2)));
    }
    return $res;
  }

  /**
   * Get an alert div with type and msg
   * TODO: should not be in util. better use the struct/messages template
   * @param $type
   * @param $msg
   * @return string
   */
  public static function getMessage($type, $msg) {
    return "<div class='alert alert-$type'>$msg</div>";
  }

  /**
   * @note dev
   * Sets the max length of hashes in the database
   * @param $limit int limit for hash length
   * @return bool true on success
   */
  public static function setMaxHashLength($limit) {
    global $FACTORIES;

    if ($limit < 1) {
      return false;
    }

    $DB = $FACTORIES::getAgentFactory()->getDB();
    $DB->beginTransaction();
    $result = $DB->query("SELECT MAX(LENGTH(" . Hash::HASH . ")) as maxLength FROM " . $FACTORIES::getHashFactory()->getModelTable());
    $maxLength = $result->fetch()['maxLength'];
    if ($limit >= $maxLength) {
      if ($DB->query("ALTER TABLE " . $FACTORIES::getHashFactory()->getModelTable() . " MODIFY " . Hash::HASH . " VARCHAR($limit) NOT NULL;") === false) {
        return false;
      }
      else if ($DB->query("ALTER TABLE " . $FACTORIES::getZapFactory()->getModelTable() . " MODIFY " . Hash::HASH . " VARCHAR($limit) NOT NULL;") === false) {
        return false;
      }
    }
    else {
      return false;
    }
    $DB->commit();
    return true;
  }

  /**
   * @note dev
   * Sets the max length of plaintexts in the database
   * @param $limit int limit for hash length
   * @return bool true on success
   */
  public static function setPlaintextMaxLength($limit) {
    global $FACTORIES;

    if ($limit < 1) {
      return false;
    }

    $DB = $FACTORIES::getAgentFactory()->getDB();
    $result = $DB->query("SELECT MAX(LENGTH(" . Hash::PLAINTEXT . ")) as maxLength FROM " . $FACTORIES::getHashFactory()->getModelTable());
    $maxLength = $result->fetch()['maxLength'];
    if ($limit >= $maxLength) {
      if ($DB->query("ALTER TABLE " . $FACTORIES::getHashFactory()->getModelTable() . " MODIFY " . Hash::PLAINTEXT . " VARCHAR($limit);") === false) {
        return false;
      }
    }
    else {
      return false;
    }
    return true;
  }

  /**
   * @param $array AbstractModel[]
   * @return array
   */
  public static function arrayOfIds($array) {
    $arr = array();
    foreach ($array as $entry) {
      $arr[] = $entry->getId();
    }
    return $arr;
  }

  public static function countLines($tmpfile) {
    if (stripos(PHP_OS, "WIN") === 0) {
      // windows line count
      $ret = exec('find /c /v "" "' . $tmpfile . '"');
      $ret = str_replace('-', '', str_ireplace($tmpfile . ':', '', $ret));
      return intval($ret);
    }
    return intval(exec("wc -l '$tmpfile'"));
  }

  /**
   * Checks a given array of device names to see if they can be shortened with the defined patterns and replacements.
   *
   * @param $deviceArray string[]
   * @return string[]
   */
  public static function compressDevices($deviceArray) {
    $compressed = array();
    foreach ($deviceArray as $device) {
      foreach (DDeviceCompress::COMPRESSION as $pattern => $replacement) {
        if (strpos($device, $pattern) !== false) {
          $device = str_replace($pattern, $replacement, $device);
        }
      }
      $compressed[] = $device;
    }
    return $compressed;
  }
}







