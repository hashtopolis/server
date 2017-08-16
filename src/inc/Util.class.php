<?php

use DBA\Agent;
use DBA\Assignment;
use DBA\Chunk;
use DBA\ComparisonFilter;
use DBA\File;
use DBA\Hashlist;
use DBA\JoinFilter;
use DBA\LogEntry;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\StoredValue;
use DBA\SuperHashlistHashlist;
use DBA\Task;
use DBA\TaskFile;
use DBA\TaskTask;
use DBA\Zap;

/**
 *
 * @author Sein
 *
 *         Bunch of useful static functions.
 */
class Util {
  /**
   * TODO: document me
   * @param $obj
   * @param $to_class
   * @return mixed|null
   */
  public static function cast($obj, $to_class) {
    return DBA\Util::cast($obj, $to_class);
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
    
    $fileType = 0;
    if ($type == 'rule') {
      $fileType = 1;
    }
    $file = new File(0, $name, Util::filesize($path), 1, $fileType);
    $file = $FACTORIES::getFileFactory()->save($file);
    if ($file == null) {
      return false;
    }
    return true;
  }
  
  /**
   * Searches for the best task which can be assigned to a given agent. It respects all configuration for cpuOnly tasks,
   * trusted states, etc.
   * @param $agent Agent
   * @param $priority int
   * @param int $supertask sets if a the task where it should be searched for the best task is a supertask
   * @return Task current best task or null if there is no optimal task (priority bigger than the given limit) available
   */
  public static function getBestTask($agent, $priority = 0, $supertask = 0) {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;
    
    $priorityFilter = new QueryFilter(Task::PRIORITY, $priority, ">");
    $typeFilter = new QueryFilter(Task::TASK_TYPE, DTaskTypes::SUBTASK, "<"); // task has to be either a supertask or a normal task
    if ($supertask > 0) {
      // this is the case when we want to find the best subtask for a given supertask
      $typeFilter = new QueryFilter(Task::TASK_TYPE, DTaskTypes::SUBTASK, "=");
    }
    $trustedFilter = new QueryFilter(Hashlist::SECRET, $agent->getIsTrusted(), "<=", $FACTORIES::getHashlistFactory()); //check if the agent is trusted to work on this hashlist
    $cpuFilter = new QueryFilter(Task::IS_CPU_TASK, $agent->getCpuOnly(), "="); //assign non-cpu tasks only to non-cpu agents and vice versa
    $crackedFilter = new ComparisonFilter(Hashlist::CRACKED, Hashlist::HASH_COUNT, "<", $FACTORIES::getHashlistFactory());
    $hashlistIDJoin = new JoinFilter($FACTORIES::getHashlistFactory(), Hashlist::HASHLIST_ID, Task::HASHLIST_ID);
    $descOrder = new OrderFilter(Task::PRIORITY, "DESC");
    
    $joinFilter = array($hashlistIDJoin);
    $queryFilter = array($priorityFilter, $trustedFilter, $cpuFilter, $crackedFilter, $typeFilter);
    if ($supertask > 0) {
      $joinFilter[] = new JoinFilter($FACTORIES::getTaskTaskFactory(), Task::TASK_ID, TaskTask::SUBTASK_ID);
      $queryFilter[] = new QueryFilter(TaskTask::TASK_ID, $supertask, "=", $FACTORIES::getTaskTaskFactory());
    }
    
    // we first load all tasks and go down by priority and take the first one which matches completely
    
    $joinedTasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $queryFilter, $FACTORIES::JOIN => $joinFilter, $FACTORIES::ORDER => array($descOrder)));
    for ($i = 0; $i < sizeof($joinedTasks[$FACTORIES::getTaskFactory()->getModelName()]); $i++) {
      /** @var $task Task */
      /** @var $hashlist Hashlist */
      $task = $joinedTasks[$FACTORIES::getTaskFactory()->getModelName()][$i];
      $hashlist = $joinedTasks[$FACTORIES::getHashlistFactory()->getModelName()][$i];
      
      if (!Util::agentHasAccessToTask($task, $agent)) {
        continue; // the client has not enough access to all required files
      }
      else if (Util::taskIsFullyDispatched($task, $agent, $hashlist)) {
        continue; // task is fully dispatched
      }
      
      // if we want to check single assignments we should make sure that the assigned one is not blocking when he becomes inactive.
      // so if an agent is inactive on a small task we unassign him that we can assign another one to it
      if ($task->getIsSmall()) {
        $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
        $jF = new JoinFilter($FACTORIES::getAgentFactory(), Assignment::AGENT_ID, Agent::AGENT_ID);
        $joined = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
        $removed = 0;
        for ($z = 0; $z < sizeof($joined[$FACTORIES::getAgentFactory()->getModelName()]); $z++) {
          /** @var Agent $ag */
          $ag = $joined[$FACTORIES::getAgentFactory()->getModelName()][$z];
          if (time() - $ag->getLastTime() > $CONFIG->getVal(DConfig::AGENT_TIMEOUT) || $ag->getIsActive() == 0) {
            $FACTORIES::getAssignmentFactory()->delete($joined[$FACTORIES::getAssignmentFactory()->getModelName()][$z]); // delete timed out
            $removed++;
          }
        }
        if ($removed < sizeof($joined[$FACTORIES::getAgentFactory()->getModelName()])) {
          continue; // still some assigned
        }
      }
      
      // if one matches now, it's the best choice (hopefully)
      return $task;
    }
    return null;
  }
  
  public static function loadTasks($supertask = 0) {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG, $OBJECTS;
    
    $jF = new JoinFilter($FACTORIES::getHashlistFactory(), Hashlist::HASHLIST_ID, Task::HASHLIST_ID);
    $oF1 = new OrderFilter(Task::PRIORITY, "DESC");
    $oF2 = new OrderFilter(Task::TASK_ID, "ASC");
    $options = array($FACTORIES::JOIN => array($jF), $FACTORIES::ORDER => array($oF1, $oF2));
    if ($supertask > 0) {
      $options[$FACTORIES::JOIN][] = new JoinFilter($FACTORIES::getTaskTaskFactory(), Task::TASK_ID, TaskTask::SUBTASK_ID);
      $options[$FACTORIES::FILTER] = new QueryFilter(TaskTask::TASK_ID, $supertask, "=", $FACTORIES::getTaskTaskFactory());
    }
    else {
      $options[$FACTORIES::FILTER] = new QueryFilter(Task::TASK_TYPE, DTaskTypes::SUBTASK, "<");
    }
    $joinedTasks = $FACTORIES::getTaskFactory()->filter($options);
    $tasks = array();
    for ($z = 0; $z < sizeof($joinedTasks[$FACTORIES::getTaskFactory()->getModelName()]); $z++) {
      $set = new DataSet();
      $set->addValue('Task', $joinedTasks[$FACTORIES::getTaskFactory()->getModelName()][$z]);
      $set->addValue('Hashlist', $joinedTasks[$FACTORIES::getHashlistFactory()->getModelName()][$z]);
      
      /** @var $task Task */
      $task = $joinedTasks[$FACTORIES::getTaskFactory()->getModelName()][$z];
      $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
      $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
      $progress = 0;
      $cracked = 0;
      $maxTime = 0;
      foreach ($chunks as $chunk) {
        $progress += $chunk->getProgress();
        $cracked += $chunk->getCracked();
        if ($chunk->getDispatchTime() > $maxTime) {
          $maxTime = $chunk->getDispatchTime();
        }
        if ($chunk->getSolveTime() > $maxTime) {
          $maxTime = $chunk->getSolveTime();
        }
      }
      
      $isActive = false;
      if (time() - $maxTime < $CONFIG->getVal(DConfig::CHUNK_TIMEOUT)) {
        $isActive = true;
      }
      
      $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
      $assignments = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => $qF));
      
      $qF = new QueryFilter(TaskFile::TASK_ID, $task->getId(), "=", $FACTORIES::getTaskFileFactory());
      $jF = new JoinFilter($FACTORIES::getTaskFileFactory(), TaskFile::FILE_ID, File::FILE_ID);
      $joinedFiles = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
      $sizes = 0;
      $secret = false;
      for ($x = 0; $x < sizeof($joinedFiles[$FACTORIES::getFileFactory()->getModelName()]); $x++) {
        $file = \DBA\Util::cast($joinedFiles[$FACTORIES::getFileFactory()->getModelName()][$x], \DBA\File::class);
        $sizes += $file->getSize();
        if ($file->getSecret() == '1') {
          $secret = true;
        }
      }
      
      $numFiles = sizeof($joinedFiles[$FACTORIES::getFileFactory()->getModelName()]);
      $numAssignments = sizeof($assignments);
      if ($task->getTaskType() == DTaskTypes::SUPERTASK) {
        Util::loadTasks($task->getId());
        /** @var $subTasks DataSet */
        $subTasks = $OBJECTS['subTasks'];
        foreach ($subTasks->getVal($task->getId()) as $subTask) {
          /** @var $subTask DataSet */
          $numFiles += $subTask->getVal('numFiles');
          $sizes += $subTask->getVal('filesSize');
          if ($subTask->getVal('fileSecret')) {
            $secret = true;
          }
          $numAssignments += $subTask->getVal('numAssignments');
          if ($subTask->getVal('isActive')) {
            $isActive = true;
          }
          $cracked += $subTask->getVal('cracked');
        }
      }
      
      $set->addValue('numFiles', $numFiles);
      $set->addValue('filesSize', $sizes);
      $set->addValue('fileSecret', $secret);
      $set->addValue('numAssignments', $numAssignments);
      $set->addValue('isActive', $isActive);
      $set->addValue('sumprog', $progress);
      $set->addValue('cracked', $cracked);
      $set->addValue('numChunks', sizeof($chunks));
      
      $tasks[] = $set;
    }
    if ($supertask > 0) {
      /** @var $subTasks DataSet */
      if (isset($OBJECTS['subTasks'])) {
        $subTasks = $OBJECTS['subTasks'];
      }
      else {
        $subTasks = new DataSet();
      }
      $subTasks->addValue($supertask, $tasks);
      $OBJECTS['subTasks'] = $subTasks;
    }
    else {
      $OBJECTS['tasks'] = $tasks;
    }
  }
  
  /**
   * @param $supertask Task
   * @return bool
   */
  public static function checkSupertaskCompleted($supertask) {
    global $FACTORIES;
    
    $jF = new JoinFilter($FACTORIES::getTaskTaskFactory(), Task::TASK_ID, TaskTask::SUBTASK_ID);
    $qF = new QueryFilter(TaskTask::TASK_ID, $supertask->getId(), "=", $FACTORIES::getTaskTaskFactory());
    $joined = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    foreach ($joined[$FACTORIES::getTaskFactory()->getModelName()] as $task) {
      /** @var $task Task */
      $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
      $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
      $sumProg = 0;
      foreach ($chunks as $chunk) {
        $sumProg += $chunk->getProgress();
      }
      if ($sumProg < $task->getKeyspace() || $task->getKeyspace() == 0) {
        return false;
      }
    }
    return true;
  }
  
  /**
   * @param $task Task
   * @return Task
   */
  public static function getSupertaskOfTask($task) {
    global $FACTORIES;
    
    $qF = new QueryFilter(TaskTask::SUBTASK_ID, $task->getId(), "=", $FACTORIES::getTaskTaskFactory());
    $jF = new JoinFilter($FACTORIES::getTaskTaskFactory(), Task::TASK_ID, TaskTask::TASK_ID);
    $joined = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    if (sizeof($joined[$FACTORIES::getTaskFactory()->getModelName()]) == 0) {
      return null;
    }
    return $joined[$FACTORIES::getTaskFactory()->getModelName()][0];
  }
  
  /**
   * @param $task Task
   * @param $agent Agent
   * @param $hashlist Hashlist
   * @return bool
   */
  public static function taskIsFullyDispatched($task, $agent, $hashlist) {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;
    
    $testTasks = Util::getSubTasks($task);
    
    $fullCount = 0;
    foreach ($testTasks as $t) {
      // now check if the task is fully dispatched
      $qF = new QueryFilter(Chunk::TASK_ID, $t->getId(), "=");
      $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
      $dispatched = 0;
      $sumProgress = 0;
      foreach ($chunks as $chunk) {
        $sumProgress += $chunk->getProgress();
        $isTimeout = false;
        // if the chunk times out, we need to remove the agent from it, so it can be done by others
        if ($chunk->getRprogress() < 10000 && time() - $chunk->getSolveTime() > $CONFIG->getVal(DConfig::CHUNK_TIMEOUT)) {
          $isTimeout = true;
        }
        
        // if the chunk has no agent or it's assigned to the current agent, it's also not completely dispatched yet
        if ($chunk->getRprogress() < 10000 && ($isTimeout || $chunk->getAgentId() == $agent->getId() || $chunk->getAgentId() == null)) {
          // nothing as it does not count to the dispatched sum
        }
        else {
          $dispatched += $chunk->getLength();
        }
      }
      if ($t->getKeyspace() != 0 && $dispatched >= $t->getKeyspace()) {
        // task is fully dispatched
        $fullCount++;
        continue;
      }
      
      if (($t->getKeyspace() <= $sumProgress && $t->getKeyspace() != 0) || $hashlist->getCracked() == $hashlist->getHashCount()) {
        //task is finished
        $t->setPriority(0);
        //TODO: make massUpdate
        foreach ($chunks as $chunk) {
          $chunk->setProgress($chunk->getLength());
          $chunk->setRprogress(10000);
          $FACTORIES::getChunkFactory()->update($chunk);
        }
        $t->setProgress($t->getKeyspace());
        $FACTORIES::getTaskFactory()->update($t);
        $fullCount++;
        continue;
      }
    }
    if ($fullCount >= sizeof($testTasks)) {
      return true;
    }
    return false;
  }
  
  /**
   * Determines if an agent can be granted access to the given task, so if the agent has trusted if at least one file
   * of the task or the hashlist requires it.
   * @param $task Task
   * @param $agent Agent
   * @return bool true if access to task can be given to agent
   */
  public static function agentHasAccessToTask($task, $agent) {
    global $FACTORIES;
    
    // check if the agent has rights to get this
    $hashlists = Util::checkSuperHashlist($FACTORIES::getHashlistFactory()->get($task->getHashlistId()));
    foreach ($hashlists as $hashlist) {
      if ($hashlist->getSecret() > $agent->getIsTrusted()) {
        return false;
      }
    }
    
    $testTasks = Util::getSubTasks($task);
    foreach ($testTasks as $t) {
      $qF = new QueryFilter(TaskFile::TASK_ID, $t->getId(), "=");
      $jF = new JoinFilter($FACTORIES::getFileFactory(), File::FILE_ID, TaskFile::FILE_ID);
      $joinedFiles = $FACTORIES::getTaskFileFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
      foreach ($joinedFiles[$FACTORIES::getFileFactory()->getModelName()] as $file) {
        /** @var $file File */
        if ($file->getSecret() > $agent->getIsTrusted()) {
          return false;
        }
      }
    }
    return true;
  }
  
  /**
   * @param $task Task
   * @return Task[]
   */
  public static function getSubTasks($task) {
    global $FACTORIES;
    
    $testTasks = array($task);
    if ($task->getTaskType() == DTaskTypes::SUPERTASK) {
      $qF = new QueryFilter(TaskTask::TASK_ID, $task->getId(), "=", $FACTORIES::getTaskTaskFactory());
      $jF = new JoinFilter($FACTORIES::getTaskTaskFactory(), Task::TASK_ID, TaskTask::SUBTASK_ID);
      $oF = new OrderFilter(Task::PRIORITY, "DESC");
      $joined = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF, $FACTORIES::ORDER => $oF));
      $testTasks = array();
      foreach ($joined[$FACTORIES::getTaskFactory()->getModelName()] as $t) {
        $testTasks[] = $t;
      }
    }
    return $testTasks;
  }
  
  /**
   * Tests if this task can be used to run for this agent. It checks if there are incomplete chunks available which are currently
   * not worked on, or if there is keyspace left which needs to be dispatched.
   * @param $task Task
   * @param $agent Agent
   * @return bool true if task has at least one chunk left to give to the agent
   */
  public static function taskCanBeUsed($task, $agent) {
    global $FACTORIES;
    
    if (!self::agentHasAccessToTask($task, $agent)) {
      return false;
    }
    
    // check if the task is not needed anymore because all hashes already got cracked
    $hashlist = $FACTORIES::getHashlistFactory()->get($task->getHashlistId());
    if ($hashlist->getCracked() >= $hashlist->getHashCount()) {
      if ($task->getPriority() > 0) {
        $task->setPriority(0);
        $FACTORIES::getTaskFactory()->update($task);
      }
      return false;
    }
    
    if ($task->getKeyspace() == 0) {
      return true;
    }
    
    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
    $dispatched = 0;
    $uncompletedChunk = null;
    foreach ($chunks as $chunk) {
      $dispatched += $chunk->getLength();
      if ($uncompletedChunk == null && 10000 != $chunk->getRprogress() && ($chunk->getAgentId() == null || $chunk->getAgentId() == $agent->getId())) {
        $uncompletedChunk = $chunk;
      }
    }
    if ($task->getKeyspace() > $dispatched) {
      return true; // task is not fully dispatched
    }
    else if ($uncompletedChunk != null) {
      return true; // there is at least one chunk with no agent or the agent which is requesting
    }
    
    return false;
  }
  
  /**
   * Used by the solver. Cleans the zap-queue
   */
  public static function zapCleaning() {
    global $FACTORIES;
    
    //TODO: make this as constant
    $entry = $FACTORIES::getStoredValueFactory()->get("lastZapCleaning");
    if ($entry == null) {
      $entry = new StoredValue("lastZapCleaning", 0);
      $FACTORIES::getStoredValueFactory()->save($entry);
    }
    if (time() - $entry->getVal() > 600) {
      
      $qF = new QueryFilter(Zap::SOLVE_TIME, time() - 600, "<=");
      $FACTORIES::getZapFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
      
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
   * @param $list hashlist-object
   * @return Hashlist[] of all superhashlists belonging to the $list
   */
  public static function checkSuperHashlist($list) {
    global $FACTORIES;
    
    if ($list->getFormat() == 3) {
      $hashlistJoinFilter = new JoinFilter($FACTORIES::getHashlistFactory(), Hashlist::HASHLIST_ID, SuperHashlistHashlist::HASHLIST_ID);
      $superHashListFilter = new QueryFilter(SuperHashlistHashlist::SUPER_HASHLIST_ID, $list->getId(), "=");
      $joined = $FACTORIES::getSuperHashlistHashlistFactory()->filter(array($FACTORIES::JOIN => $hashlistJoinFilter, $FACTORIES::FILTER => $superHashListFilter));
      $lists = $joined[$FACTORIES::getHashlistFactory()->getModelName()];
      return $lists;
    }
    return array($list);
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
   * TODO: remove this function and add this tick as a glyphicon and also without using this function
   * @param $prog int progress so far
   * @param $total int total to be done
   * @return string either the check.png with Finished or an empty string
   */
  public static function tickdone($prog, $total) {
    // show tick of progress is done
    if ($total > 0 && $prog >= $total) {
      return " <img src='static/check.png' alt='Finished'>";
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
      return "Unknown-$id";
    }
    return $user->getUsername();
  }
  
  /**
   * Used in Template. Subtracts two variables
   * TODO: this should be removed, as it can be done by Util::calculate
   * TODO: also check if we really need this calculating function or if it can be done otherwise
   * @param $x int value 1
   * @param $y int value 2
   * @return mixed
   */
  public static function subtract($x, $y) {
    return ($x - $y);
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
      "<img src='static/unix.png' alt='Unix' title='Linux'>",
      "<img src='static/win.png' alt='Win' title='Windows'>",
      "<img src='static/osx.png' alt='OS X' title='OS X'>"
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
        return $oses[$val];
        break;
      case 'states':
        return $states[$val];
        break;
      case 'formats':
        return $formats[$val];
        break;
      case 'formattables':
        return $formattables[$val];
        break;
      case 'platforms':
        if ($val == '-1') {
          return $platforms[0];
        }
        return $platforms[$val];
        break;
    }
    return "";
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
          if (file_exists("import/" . $sourcedata)) {
            rename("import/" . $sourcedata, $target);
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
   * This sends a given email with text and subject to the address.
   *
   * @param string $address
   *          email address of the receiver
   * @param string $subject
   *          subject of the email
   * @param string $text
   *          html content of the email
   * @return true on success, false on failure
   */
  public static function sendMail($address, $subject, $text) {
    $header = "Content-type: text/html; charset=utf8\r\n";
    // TODO: make sender email configurable
    $header .= "From: Hashtopussy <noreply@hashtopussy>\r\n";
    if (!mail($address, $subject, $text, $header)) {
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
   * Is used by DBA
   * TODO: this function is not used anymore, can be removed after checking that DBA\Util::createPrefixedString() is used.
   * @param $table
   * @param $dict
   * @return string
   */
  public static function createPrefixedString($table, $dict) {
    $arr = array();
    foreach ($dict as $key => $val) {
      $arr[] = "`" . $table . "`" . "." . "`" . $key . "`" . " AS `" . $table . "." . $key . "`";
    }
    return implode(", ", $arr);
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
  
  public static function checkCSRF($csrf) {
    global $OBJECTS;
    
    if (!isset($_SESSION['csrf']) || $csrf !== $_SESSION['csrf']) {
      unset($_SESSION['csrf']);
      UI::addMessage(UI::ERROR, "Invalid form submission!");
      return false;
    }
    $_SESSION['csrf'] = Util::randomString(30);
    $OBJECTS['csrf'] = $_SESSION['csrf'];
    return true;
  }
}
