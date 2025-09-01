<?php

use DBA\Aggregation;
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
use DBA\FileDelete;
use DBA\Factory;
use DBA\Speed;
use Composer\Semver\Comparator;

/**
 *
 * @author Sein
 *
 *         Bunch of useful static functions.
 */
class Util {
  /**
   * Determines the file extension of the given file name (based on the last dot).
   * Returns an empty string if no extension was found.
   *
   * @param string $filename
   * @return string
   */
  public static function extractFileExtension($filename) {
    $split = explode(".", $filename);
    if (sizeof($split) == 1) {
      return "";
    }
    return $split[sizeof($split) - 1];
  }
  
  /**
   * Downloads the data at the given url and saves it at the specified destination.
   * It will overwrite files if they already exist.
   *
   * @param string $url
   * @param string $dest
   * @throws HTException
   */
  public static function downloadFromUrl($url, $dest) {
    $furl = fopen($url, "rb");
    if (!$furl) {
      throw new HTException("Failed to open URL!");
    }
    $fileLocation = fopen($dest, "w");
    if (!$fileLocation) {
      throw new HTException("Failed to open destination file!");
    }
    $buffersize = 131072;
    while (!feof($furl)) {
      if (!$data = fread($furl, $buffersize)) {
        throw new HTException("Data reading error!");
      }
      fwrite($fileLocation, $data);
    }
    fclose($fileLocation);
    fclose($furl);
  }
  
  /**
   * Loads the last speed data on a specific task. Either for the full task, or a specific agent.
   * The data is provided as an associative array with the timestamps as keys.
   *
   * @param int $taskId
   * @param int $limit
   * @param int $agentId corresponding agent to show data from, 0 to sum up from all agents on this task
   * @param int $delta time distance between the data points
   * @return int[]
   */
  public static function getSpeedDataSet($taskId, $limit = 50, $agentId = 0, $delta = 10) {
    // if agentId is 0 we need to find out how many agents there are to find how many entries we would need max
    $requestLimit = intval($limit) * $delta / 5;
    if ($agentId == 0) { // This might be to rewritten, it's just an estimation how to calculate an ideal number of entries to be requested
      // we cannot request all entries here as this number might grow quite quickly over time
      $qF = new QueryFilter(Assignment::TASK_ID, $taskId, "=");
      $agentCount = Factory::getAssignmentFactory()->countFilter([Factory::FILTER => $qF]) + 1;
      $requestLimit = $agentCount * $limit * $delta / 5;
    }
    
    $qF1 = new QueryFilter(Speed::TASK_ID, $taskId, "=");
    $oF = new OrderFilter(Speed::SPEED_ID, "DESC LIMIT $requestLimit");
    if ($agentId > 0) {
      $qF2 = new QueryFilter(Speed::AGENT_ID, $agentId, "=");
      $entries = Factory::getSpeedFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => $oF]);
    }
    else {
      $entries = Factory::getSpeedFactory()->filter([Factory::FILTER => $qF1, Factory::ORDER => $oF]);
    }
    
    if (sizeof($entries) == 0) {
      return [];
    }
    
    $data = [];
    $used = [];
    for ($i = 0; $i < $limit; $i++) {
      $data[$i] = 0;
      $used[$i] = [];
    }
    
    $first = $entries[0]->getTime();
    foreach ($entries as $entry) {
      $pos = $limit - 1 - floor(($first - $entry->getTime()) / $delta);
      if ($pos < 0) {
        continue; // too old entry
      }
      else if (in_array($entry->getAgentId(), $used[$pos])) {
        continue; // if we already have a newer entry in this range, we ignore it
      }
      $data[$pos] += $entry->getSpeed();
      $used[$pos][] = $entry->getAgentId();
    }
    
    // prepare with timestamps
    $first = round($first, -log10($delta));
    $timestampData = [];
    foreach ($data as $key => $val) {
      $timestampData[$first - ($limit - 1 - $key) * $delta] = $val;
    }
    return $timestampData;
  }
  
  /**
   * Get the hashtype name by its ID
   *
   * @param int $hashtypeId
   * @return string
   */
  public static function getHashtypeById($hashtypeId) {
    $hashtype = Factory::getHashTypeFactory()->get($hashtypeId);
    if ($hashtype == null) {
      return "N/A";
    }
    return $hashtype->getDescription();
  }
  
  /**
   * Get the commit hash and branch (if available) of the Hashtopolis server.
   *
   * @param bool $hashOnly
   * @return string
   */
  public static function getGitCommit($hashOnly = false) {
    $gitcommit = "";
    $gitfolder = dirname(__FILE__) . "/../../.git";
    if (file_exists($gitfolder) && is_dir($gitfolder)) {
      $head = file_get_contents($gitfolder . "/HEAD");
      $branch = trim(substr($head, strlen("ref: refs/heads/"), -1));
      if (file_exists($gitfolder . "/refs/heads/" . $branch)) {
        $commit = trim(file_get_contents($gitfolder . "/refs/heads/" . $branch));
        if ($hashOnly) {
          return $commit;
        }
        $gitcommit = "commit " . substr($commit, 0, 7) . " branch $branch";
      }
      else {
        $commit = $head;
        if ($hashOnly) {
          return $commit;
        }
        $gitcommit = "commit " . substr($commit, 0, 7);
      }
    }
    return $gitcommit;
  }
  
  /**
   * @param string $type
   * @param string $version
   * @param bool $silent
   */
  public static function checkAgentVersion($type, $version, $silent = false) {
    $qF = new QueryFilter(AgentBinary::BINARY_TYPE, $type, "=");
    if (Util::databaseColumnExists("AgentBinary", "type")) {
      // This check is needed for older updates when agentbinary column still got old 'type' name
      $qF = new QueryFilter("type", $type, "=");
    }
    $binary = Factory::getAgentBinaryFactory()->filter([Factory::FILTER => $qF], true);
    if ($binary != null) {
      if (Comparator::lessThan($binary->getVersion(), $version)) {
        if (!$silent) {
          echo "update $type version... ";
        }
        Factory::getAgentBinaryFactory()->set($binary, AgentBinary::VERSION, $version);
        if (!$silent) {
          echo "OK";
        }
      }
    }
  }
  
  /**
   * @return boolean
   */
  public static function isYubikeyEnabled() {
    $clientId = SConfig::getInstance()->getVal(DConfig::YUBIKEY_ID);
    if (!is_numeric($clientId) || $clientId <= 0) {
      return false;
    }
    $secretKey = SConfig::getInstance()->getVal(DConfig::YUBIKEY_KEY);
    if (!base64_decode($secretKey)) {
      return false;
    }
    $apiUrl = SConfig::getInstance()->getVal(DConfig::YUBIKEY_URL);
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
    $count = Factory::getLogEntryFactory()->countFilter(array());
    if ($count > SConfig::getInstance()->getVal(DConfig::NUMBER_LOGENTRIES) * 1.2) {
      // if we have exceeded the log entry limit by 20%, delete the oldest ones
      $toDelete = floor(SConfig::getInstance()->getVal(DConfig::NUMBER_LOGENTRIES) * 0.2);
      $oF = new OrderFilter(LogEntry::TIME, "ASC LIMIT $toDelete");
      Factory::getLogEntryFactory()->massDeletion([Factory::ORDER => $oF]);
    }
    
    $entry = new LogEntry(null, $issuer, $issuerId, $level, $message, time());
    Factory::getLogEntryFactory()->save($entry);
    
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
   * Scan the report template directory for templates. If no type is specified it will return all found.
   *
   * @param string $type
   * @param bool $pretty
   * @return string[] found report template file names
   */
  public static function scanReportDirectory($type = "", $pretty = false) {
    $directory = dirname(__FILE__) . "/../templates/report/";
    if (file_exists($directory) && is_dir($directory)) {
      $reportDir = opendir($directory);
      $reports = array();
      while ($file = readdir($reportDir)) {
        if ($file[0] != '.' && $file != "." && $file != ".." && !is_dir($file) && strpos($file, ".tex") !== false) {
          if (strlen($type) > 0 && strpos($file, $type . "-") !== 0) {
            continue;
          }
          if ($pretty) {
            $reports[] = ucfirst(substr(str_replace(".template.tex", "", $file), strlen($type) + 1));
          }
          else {
            $reports[] = $file;
          }
        }
      }
      return $reports;
    }
    return [];
  }
  
  /**
   * Escapes special chars before they can be entered into the report template to avoid mess-up with latex
   *
   * @param string $string
   * @return string
   */
  public static function texEscape($string) {
    $output = "";
    for ($i = 0; $i < strlen($string); $i++) {
      if ($string[$i] == '#') {
        $output .= "\\#";
      }
      else if ($string[$i] == '\\') {
        $output .= "\\textbackslash";
      }
      else if ($string[$i] == '_') {
        $output .= "\\_";
      }
      else {
        $output .= $string[$i];
      }
    }
    return $output;
  }
  
  /**
   * Scans the import-directory for files. Directories are ignored.
   * @return array of all files in the top-level directory /../import
   */
  public static function scanImportDirectory() {
    $directory = Factory::getStoredValueFactory()->get(DDirectories::IMPORT)->getVal() . "/";
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
   * @param $accessGroupId int
   * @return bool true if the save of the file model succeeded
   */
  public static function insertFile($path, $name, $type, $accessGroupId) {
    $fileType = DFileType::OTHER;
    if ($type == 'rule') {
      $fileType = DFileType::RULE;
    }
    else if ($type == 'dict') {
      $fileType = DFileType::WORDLIST;
    }
    
    // check if there is an old deletion request for the same filename
    $qF = new QueryFilter(FileDelete::FILENAME, $name, "=");
    Factory::getFileDeleteFactory()->massDeletion([Factory::FILTER => $qF]);
    if ($fileType == DFileType::RULE) {
      $file = new File(null, $name, Util::filesize($path), 1, $fileType, $accessGroupId, Util::rulefileLineCount($path));
    }
    else {
      $file = new File(null, $name, Util::filesize($path), 1, $fileType, $accessGroupId, Util::fileLineCount($path));
    }
    $file = Factory::getFileFactory()->save($file);
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
    $qF1 = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    
    $agg1 = new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM);
    $agg2 = new Aggregation(Chunk::SKIP, Aggregation::SUM);
    $agg3 = new Aggregation(Chunk::CRACKED, Aggregation::SUM);
    $agg4 = new Aggregation(Chunk::SPEED, Aggregation::SUM);
    $agg5 = new Aggregation(Chunk::DISPATCH_TIME, Aggregation::MAX);
    $agg6 = new Aggregation(Chunk::SOLVE_TIME, Aggregation::MAX);
    $agg7 = new Aggregation(Chunk::CHUNK_ID, Aggregation::COUNT);
    $agg8 = new Aggregation(Chunk::SOLVE_TIME, Aggregation::SUM);
    $agg9 = new Aggregation(Chunk::DISPATCH_TIME, Aggregation::SUM);
    
    $results = Factory::getChunkFactory()->multicolAggregationFilter([Factory::FILTER => $qF1], [$agg1, $agg2, $agg3, $agg4, $agg5, $agg6, $agg7, $agg8, $agg9]);
    
    $totalTimeSpent = $results[$agg8->getName()] - $results[$agg9->getName()];
    
    $progress = $results[$agg1->getName()] - $results[$agg2->getName()];
    $cracked = $results[$agg3->getName()];
    $speed = $results[$agg4->getName()];
    $maxTime = max($results[$agg5->getName()], $results[$agg6->getName()]);
    $numChunks = $results[$agg7->getName()];
    
    $isActive = false;
    if (time() - $maxTime < SConfig::getInstance()->getVal(DConfig::CHUNK_TIMEOUT) && ($progress < $task->getKeyspace() || $task->getUsePreprocessor() && $task->getKeyspace() == DPrince::PRINCE_KEYSPACE)) {
      $isActive = true;
    }
    return array($progress, $cracked, $isActive, $numChunks, ($totalTimeSpent > 0) ? round($cracked * 60 / $totalTimeSpent, 2) : 0, $speed);
  }
  
  /**
   * @param $task Task
   * @param $accessGroups AccessGroup[]
   * @return array
   */
  public static function getFileInfo($task, $accessGroups) {
    $qF = new QueryFilter(FileTask::TASK_ID, $task->getId(), "=", Factory::getFileTaskFactory());
    $jF = new JoinFilter(Factory::getFileTaskFactory(), FileTask::FILE_ID, File::FILE_ID);
    $joinedFiles = Factory::getFileFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    /** @var $files File[] */
    $files = $joinedFiles[Factory::getFileFactory()->getModelName()];
    $sizeFiles = 0;
    $fileSecret = false;
    $noAccess = false;
    foreach ($files as $file) {
      if ($file->getIsSecret() == 1) {
        $fileSecret = true;
      }
      if (!in_array($file->getAccessGroupId(), Util::arrayOfIds($accessGroups))) {
        $noAccess = true;
      }
      $sizeFiles += $file->getSize();
    }
    return array(sizeof($files), $fileSecret, $sizeFiles, $files, $noAccess);
  }
  
  /**
   * @param $task Task
   * @return array
   */
  public static function getChunkInfo($task) {
    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $agg1 = new Aggregation(Chunk::CRACKED, "SUM");
    $agg2 = new Aggregation(Chunk::CHUNK_ID, "COUNT");
    $results = Factory::getChunkFactory()->multicolAggregationFilter([Factory::FILTER => $qF], [$agg1, $agg2]);
    
    $cracked = $results[$agg1->getName()];
    $numChunks = $results[$agg2->getName()];
    
    $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $numAssignments = Factory::getAssignmentFactory()->countFilter([Factory::FILTER => $qF]);
    
    return array($numChunks, $cracked, $numAssignments);
  }
  
  /**
   * @param $userId int
   * @return array
   */
  public static function getAccessGroupIds($userId) {
    $qF = new QueryFilter(AccessGroupUser::USER_ID, $userId, "=", Factory::getAccessGroupUserFactory());
    $jF = new JoinFilter(Factory::getAccessGroupUserFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupUser::ACCESS_GROUP_ID);
    $joined = Factory::getAccessGroupFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    /** @var $accessGroups AccessGroup[] */
    $accessGroups = $joined[Factory::getAccessGroupFactory()->getModelName()];
    return Util::arrayOfIds($accessGroups);
  }
  
  public static function loadTasks($archived = false) {
    $accessGroupIds = Util::getAccessGroupIds(Login::getInstance()->getUserID());
    $accessGroups = AccessUtils::getAccessGroupsOfUser(Login::getInstance()->getUser());
    
    $qF1 = new ContainFilter(TaskWrapper::ACCESS_GROUP_ID, $accessGroupIds);
    $qF2 = new QueryFilter(TaskWrapper::IS_ARCHIVED, ($archived) ? 1 : 0, "=");
    $oF1 = new OrderFilter(TaskWrapper::PRIORITY, "DESC");
    $oF2 = new OrderFilter(TaskWrapper::TASK_WRAPPER_ID, "DESC");
    $taskWrappers = Factory::getTaskWrapperFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => [$oF1, $oF2]]);
    
    $taskList = array();
    foreach ($taskWrappers as $taskWrapper) {
      $set = new DataSet();
      $set->addValue('taskType', $taskWrapper->getTaskType());
      
      $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
      if ($taskWrapper->getTaskType() == DTaskTypes::SUPERTASK) {
        // supertask
        $set->addValue('supertaskName', $taskWrapper->getTaskWrapperName());
        
        $hashlist = Factory::getHashlistFactory()->get($taskWrapper->getHashlistId());
        
        $set->addValue('hashlistId', $hashlist->getId());
        $set->addValue('taskWrapperId', $taskWrapper->getId());
        $set->addValue('hashlistName', $hashlist->getHashlistName());
        $set->addValue('hashlistSecret', $hashlist->getIsSecret());
        $set->addValue('hashCount', $hashlist->getHashCount());
        $set->addValue('hashlistCracked', $hashlist->getCracked());
        $set->addValue('priority', $taskWrapper->getPriority());
        $set->addValue('maxAgents', $taskWrapper->getMaxAgents());
        $set->addValue('cracked', $taskWrapper->getCracked());
        
        $taskList[] = $set;
      }
      else {
        // normal task
        $task = Factory::getTaskFactory()->filter([Factory::FILTER => $qF], true);
        if ($task == null) {
          Util::createLogEntry(DLogEntryIssuer::USER, Login::getInstance()->getUserID(), DLogEntry::WARN, "TaskWrapper (" . $taskWrapper->getId() . ") for normal task existing with containing no task!");
          continue;
        }
        $taskInfo = Util::getTaskInfo($task);
        $fileInfo = Util::getFileInfo($task, $accessGroups);
        if ($fileInfo[4]) {
          continue;
        }
        
        $chunkInfo = Util::getChunkInfo($task);
        $hashlist = Factory::getHashlistFactory()->get($taskWrapper->getHashlistId());
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
        $set->addValue('usePreprocessor', $task->getUsePreprocessor());
        $set->addValue('priority', $task->getPriority());
        $set->addValue('maxAgents', $task->getMaxAgents());
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
        $set->addValue('speed', $taskInfo[5]);
        $taskList[] = $set;
      }
    }
    UI::add('taskList', $taskList);
  }
  
  /**
   * @param $taskWrapper TaskWrapper
   * @return bool
   */
  public static function checkTaskWrapperCompleted($taskWrapper) {
    $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
    $tasks = Factory::getTaskFactory()->filter([Factory::FILTER => $qF]);
    foreach ($tasks as $task) {
      $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
      $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);
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
  
  /**
   * Checks if it is longer than 10 mins since the last time it was checked if there are
   * any old agent statistic entries which can be deleted. If necessary, check is executed
   * and old entries are deleted.
   */
  public static function agentStatCleaning() {
    $entry = Factory::getStoredValueFactory()->get(DStats::LAST_STAT_CLEANING);
    if ($entry == null) {
      $entry = new StoredValue(DStats::LAST_STAT_CLEANING, 0);
      Factory::getStoredValueFactory()->save($entry);
    }
    if (time() - $entry->getVal() > 600) {
      $lifetime = intval(SConfig::getInstance()->getVal(DConfig::AGENT_DATA_LIFETIME));
      if ($lifetime <= 0) {
        $lifetime = 3600;
      }
      $qF = new QueryFilter(AgentStat::TIME, time() - $lifetime, "<=");
      Factory::getAgentStatFactory()->massDeletion([Factory::FILTER => $qF]);
      
      Factory::getStoredValueFactory()->set($entry, StoredValue::VAL, time());
    }
  }
  
  /**
   * Used by the solver. Cleans the zap-queue
   */
  public static function zapCleaning() {
    $entry = Factory::getStoredValueFactory()->get(DZaps::LAST_ZAP_CLEANING);
    if ($entry == null) {
      $entry = new StoredValue(DZaps::LAST_ZAP_CLEANING, 0);
      Factory::getStoredValueFactory()->save($entry);
    }
    if (time() - $entry->getVal() > 600) {
      $zapFilter = new QueryFilter(Zap::SOLVE_TIME, time() - 600, "<=");
      
      // delete dependencies on AgentZap
      $zaps = Factory::getZapFactory()->filter([Factory::FILTER => $zapFilter]);
      $zapIds = Util::arrayOfIds($zaps);
      $uS = new UpdateSet(AgentZap::LAST_ZAP_ID, null);
      $qF = new ContainFilter(AgentZap::LAST_ZAP_ID, $zapIds);
      Factory::getAgentZapFactory()->massUpdate([Factory::FILTER => $qF, Factory::UPDATE => $uS]);
      
      Factory::getZapFactory()->massDeletion([Factory::FILTER => $zapFilter]);
      
      Factory::getStoredValueFactory()->set($entry, StoredValue::VAL, time());
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
    if ($fp === false) {
      return -1;
    }
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
   * This counts the number of lines in a given file
   * @param $file string Filepath you want to get the size from
   * @return int -1 if the file doesn't exist, else filesize
   */
  public static function fileLineCount($file) {
    if (!file_exists($file)) {
      return -1;
    }
    // find out what a prettier solution for this would be, as opposed to setting the max execution time to an arbitrary two hours
    ini_set('max_execution_time', '7200');
    $file = new \SplFileObject($file, 'r');
    $file->seek(PHP_INT_MAX);
    
    return $file->key();
  }
  
  /**
   * This counts the number of lines in a rule file, excluding lines starting with # and empty lines
   * @param $file string Filepath you want to get the size from
   * @return int -1 if the file doesn't exist, else filesize
   */
  public static function rulefileLineCount($file) {
    if (!file_exists($file)) {
      return -1;
    }
    // find out what a prettier solution for this would be, as opposed to setting the max execution time to an arbitrary two hours
    ini_set('max_execution_time', '7200');
    $lineCount = 0;
    $handle = fopen($file, "r");
    while (!feof($handle)) {
      $line = fgets($handle);
      if (!(Util::startsWith($line, '#') or trim($line) == "")) {
        $lineCount = $lineCount + 1;
      }
    }
    
    fclose($handle);
    return $lineCount;
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
    if ($hashlist->getFormat() == DHashlistFormat::SUPERHASHLIST) {
      $jF = new JoinFilter(Factory::getHashlistFactory(), HashlistHashlist::HASHLIST_ID, Hashlist::HASHLIST_ID);
      $qF = new QueryFilter(HashlistHashlist::PARENT_HASHLIST_ID, $hashlist->getId(), "=");
      $joined = Factory::getHashlistHashlistFactory()->filter([Factory::JOIN => $jF, Factory::FILTER => $qF]);
      return $joined[Factory::getHashlistFactory()->getModelName()];
    }
    return array($hashlist);
  }
  
  /**
   * @param $hashlist Hashlist
   * @return Hashlist[] all superhashlists which the hashlist is part of
   */
  public static function getParentSuperHashlists($hashlist) {
    if ($hashlist->getFormat() == DHashlistFormat::SUPERHASHLIST) {
      return [];
    }
    $jF = new JoinFilter(Factory::getHashlistFactory(), HashlistHashlist::PARENT_HASHLIST_ID, Hashlist::HASHLIST_ID);
    $qF = new QueryFilter(HashlistHashlist::HASHLIST_ID, $hashlist->getId(), "=", Factory::getHashlistHashlistFactory());
    $joined = Factory::getHashlistHashlistFactory()->filter([Factory::JOIN => $jF, Factory::FILTER => $qF]);
    return $joined[Factory::getHashlistFactory()->getModelName()];
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
    $user = Factory::getUserFactory()->get($id);
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
    for ($i = 0; $i < strlen(SConfig::getInstance()->getVal(DConfig::BLACKLIST_CHARS)); $i++) {
      if (strpos($string, SConfig::getInstance()->getVal(DConfig::BLACKLIST_CHARS)[$i]) !== false) {
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
      "HCCAPX / PMKID",
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
    if (Comparator::greaterThan($binary1->getVersion(), $binary2->getVersion()){
      return 1;
    }
    else if (Comparator::lessThan($binary1->getVersion(), $binary2->getVersion()){
      return -1;
    }
    return 0;
  }
  
  /**
   * @param string $versionString1
   * @param string $versionString2
   * @return int 1 if version2 is newer, 0 if equal and -1 if version1 is newer
   */
  public static function updateVersionComparison($versionString1, $versionString2) {
    if (!Util::startsWith($versionString1, "update_v") || !Util::startsWith($versionString2, "update_v")) {
      return Util::startsWith($versionString1, "update_v") ? -1 : 1;
    }
    $version1 = substr($versionString1, 8, strpos($versionString1, "_", 7) - 8);
    $version2 = substr($versionString2, 8, strpos($versionString2, "_", 7) - 8);
    
    if(Comparator::greaterThan($version2, $version1)){
      return 1;
    }
    else if(Comparator::lessThan($version2, $version1)){
      return -1;
    }
    return 0;
  }
  
  /**
   * Shows big numbers with the right suffixes (k, M, G)
   * @param int $num integer you want formatted
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
    $scales = array(
      "",
      "k",
      "M",
      "G"
    );
    return Util::niceround($num, 2) . " " . $scales[$r];
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
        $percentage -= 1 / (pow(10, $decs));
      }
      if ($percentage == 0 && $part > 0) {
        $percentage += 1 / (pow(10, $decs));
      }
    }
    else {
      $percentage = 0;
    }
    return Util::niceround($percentage, $decs);
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
          if (file_exists(Factory::getStoredValueFactory()->get(DDirectories::IMPORT)->getVal() . "/" . $sourcedata)) {
            rename(Factory::getStoredValueFactory()->get(DDirectories::IMPORT)->getVal() . "/" . $sourcedata, $target);
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
    // when the server hostname is set on the config, use this
    if (strlen(SConfig::getInstance()->getVal(DConfig::BASE_HOST)) > 0) {
      return SConfig::getInstance()->getVal(DConfig::BASE_HOST);
    }
    
    $protocol = (isset($_SERVER['HTTPS']) && (strcasecmp('off', $_SERVER['HTTPS']) !== 0)) ? "https://" : "http://";
    $hostname = $_SERVER['HTTP_HOST'];
    $port = $_SERVER['SERVER_PORT'];

    if ($protocol == "https://" && $port == 443 || $protocol == "http://" && $port == 80) {
      $port = "";
    }
    else {
      $port = ":$port";
      $hostname = substr($hostname, 0, strrpos($hostname, ":")); //Needs to use strrpos in case of ipv6 because of multiple ':' characters
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
        $return .= ".";
        for ($i = 0; $i < $dec; $i++) {
          $return .= "0";
        }
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
    $boundary = uniqid('np');
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "From: " . SConfig::getInstance()->getVal(Dconfig::EMAIL_SENDER_NAME) . " <" . SConfig::getInstance()->getVal(DConfig::EMAIL_SENDER) . ">\r\n";
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
   * @param string $charset
   * @return string random string
   */
  public static function randomString($length, $charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789") {
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
   * @note dev
   * Sets the max length of hashes in the database
   * @param $limit int limit for hash length
   * @return bool true on success
   */
  public static function setMaxHashLength($limit) {
    if ($limit < 1) {
      return false;
    }
    
    $DB = Factory::getAgentFactory()->getDB();
    $DB->beginTransaction();
    $result = $DB->query("SELECT MAX(LENGTH(" . Hash::HASH . ")) as maxLength FROM " . Factory::getHashFactory()->getModelTable());
    $maxLength = $result->fetch()['maxLength'];
    if ($limit >= $maxLength) {
      if ($DB->query("ALTER TABLE " . Factory::getHashFactory()->getModelTable() . " MODIFY " . Hash::HASH . " VARCHAR($limit) NOT NULL;") === false) {
        return false;
      }
      else if ($DB->query("ALTER TABLE " . Factory::getZapFactory()->getModelTable() . " MODIFY " . Hash::HASH . " VARCHAR($limit) NOT NULL;") === false) {
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
    if ($limit < 1) {
      return false;
    }
    
    $DB = Factory::getAgentFactory()->getDB();
    $result = $DB->query("SELECT MAX(LENGTH(" . Hash::PLAINTEXT . ")) as maxLength FROM " . Factory::getHashFactory()->getModelTable());
    $maxLength = $result->fetch()['maxLength'];
    if ($limit >= $maxLength) {
      if ($DB->query("ALTER TABLE " . Factory::getHashFactory()->getModelTable() . " MODIFY " . Hash::PLAINTEXT . " VARCHAR($limit);") === false) {
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
  
  // new function added: fileLineCount(). This function is independent of OS.
  // check whether we can remove one of these functions
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
  
  public static function getMinorVersion($version) {
    $split = explode(".", $version);
    return $split[0] . "." . $split[1];
  }
  
  public static function databaseColumnExists($table, $column) {
    $result = Factory::getAgentFactory()->getDB()->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result->rowCount() > 0;
  }
  
  public static function databaseTableExists($table) {
    $result = Factory::getAgentFactory()->getDB()->query("SHOW TABLES LIKE '$table';");
    return $result->rowCount() > 0;
  }
  
  public static function databaseIndexExists($table, $column) {
    $result = Factory::getAgentFactory()->getDB()->query("SHOW INDEX FROM `$table` WHERE Column_name='$column'");
    return $result->rowCount() > 0;
  }
  
  public static function checkDataDirectory($key, $dir) {
    $entry = Factory::getStoredValueFactory()->get($key);
    if ($entry == null) {
      $entry = new StoredValue($key, $dir);
      Factory::getStoredValueFactory()->save($entry);
    }
    else {
      // update if needed
      if ($entry->getVal() != $dir) {
        $entry->setVal($dir);
        Factory::getStoredValueFactory()->update($entry);
      }
    }
  }
}







