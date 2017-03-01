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

/**
 *
 * @author Sein
 *
 *         Bunch of useful static functions.
 */
class Util {
  /**
   * TODO: document me
   */
  public static function cast($obj, $to_class) {
    return DBA\Util::cast($obj, $to_class);
  }
  
  public static function createLogEntry($issuer, $issuerId, $level, $message) {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;
    
    $count = $FACTORIES::getLogEntryFactory()->countFilter(array());
    if ($count > $CONFIG->getVal(DConfig::NUMBER_LOGENTRIES) * 1.2) {
      // if we have exceeded the log entry limit by 20%, delete the oldest ones
      $toDelete = round($CONFIG->getVal(DConfig::NUMBER_LOGENTRIES) * 0.2);
      $oF = new OrderFilter(LogEntry::TIME, "ASC LIMIT $toDelete");
      $FACTORIES::getLogEntryFactory()->massDeletion(array($FACTORIES::ORDER => $oF));
    }
    
    $entry = new LogEntry(0, $issuer, $issuerId, $level, $message, time());
    $FACTORIES::getLogEntryFactory()->save($entry);
  }
  
  /**
   * Scans the import-directory for files.
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
   * Calculates variable. Used in Templates
   * @param $in mixed calculation to be done
   * @return mixed
   */
  public static function calculate($in) {
    return $in;
  }
  
  /**
   * Saves a file into the DB using the FileFactory
   * @param $path string
   * @param $name string
   * @param $type string
   * @return bool result of the save()-function.
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
   * @param $agent Agent
   * @param int $priority
   * @return Task
   */
  public static function getBestTask($agent, $priority = 0) {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;
    
    $priorityFilter = new QueryFilter(Task::PRIORITY, $priority, ">");
    $trustedFilter = new QueryFilter(Hashlist::SECRET, $agent->getIsTrusted(), "<=", $FACTORIES::getHashlistFactory()); //check if the agent is trusted to work on this hashlist
    $cpuFilter = new QueryFilter(Task::IS_CPU_TASK, $agent->getCpuOnly(), "="); //assign non-cpu tasks only to non-cpu agents and vice versa
    $crackedFilter = new ComparisonFilter(Hashlist::CRACKED, Hashlist::HASH_COUNT, "<", $FACTORIES::getHashlistFactory());
    $hashlistIDJoin = new JoinFilter($FACTORIES::getHashlistFactory(), Hashlist::HASHLIST_ID, Task::HASHLIST_ID);
    $descOrder = new OrderFilter(Task::PRIORITY, "DESC");
    
    // we first load all tasks and go down by priority and take the first one which matches completely
    
    $joinedTasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => array($priorityFilter, $trustedFilter, $cpuFilter, $crackedFilter), $FACTORIES::JOIN => array($hashlistIDJoin), $FACTORIES::ORDER => array($descOrder)));
    for ($i = 0; $i < sizeof($joinedTasks['Task']); $i++) {
      /** @var $task Task */
      /** @var $hashlist Hashlist */
      $task = $joinedTasks['Task'][$i];
      $hashlist = $joinedTasks['Hashlist'][$i];
      $qF = new QueryFilter(TaskFile::TASK_ID, $task->getId(), "=", $FACTORIES::getTaskFileFactory());
      $jF = new JoinFilter($FACTORIES::getTaskFileFactory(), File::FILE_ID, TaskFile::FILE_ID);
      $joinedFiles = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
      $allowed = true;
      foreach ($joinedFiles["File"] as $file) {
        /** @var $file File */
        if ($file->getSecret() > $agent->getIsTrusted()) {
          $allowed = false;
        }
      }
      if (!$allowed) {
        continue; // the client has not enough access to all required files
      }
      
      // now check if the task is fully dispatched
      $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
      $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
      $dispatched = 0;
      $sumProgress = 0;
      foreach ($chunks as $chunk) {
        $sumProgress += $chunk->getProgress();
        $isTimeout = false;
        // if the chunk times out, we need to remove the agent from it, so it can be done by others
        if ($chunk->getRprogress() < 10000 && time() - $chunk->getSolveTime() > $CONFIG->getVal(DConfig::CHUNK_TIMEOUT)) {
          //$chunk->setAgentId(null);
          //$FACTORIES::getChunkFactory()->update($chunk);
          $isTimeout = true;
        }
        
        // if the chunk has no agent or it's assigned to the current agent, it's also not completely dispatched yet
        if ($chunk->getRprogress() < 10000 && ($isTimeout || $chunk->getAgentId() == $agent->getId())) {
          continue; // so it's not count to the dispatched sum
        }
        $dispatched += $chunk->getLength();
      }
      if ($task->getKeyspace() != 0 && $dispatched == $task->getKeyspace()) {
        // task is fully dispatched
        continue;
      }
      
      if (($task->getKeyspace() == $sumProgress && $task->getKeyspace() != 0) || $hashlist->getCracked() == $hashlist->getHashCount()) {
        //task is finished
        $task->setPriority(0);
        //TODO: make massUpdate
        foreach ($chunks as $chunk) {
          $chunk->setProgress($chunk->getLength());
          $chunk->setRprogress(10000);
          $FACTORIES::getChunkFactory()->update($chunk);
        }
        $task->setProgress($task->getKeyspace());
        $FACTORIES::getTaskFactory()->update($task);
        continue;
      }
      
      // if we want to check single assignments we should make sure that the assigned one is not blocking when he becomes inactive.
      // so if an agent is inactive on a small task we unassign him that we can assign another one to it
      if ($task->getIsSmall()) {
        $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
        $jF = new JoinFilter($FACTORIES::getAgentFactory(), Assignment::AGENT_ID, Agent::AGENT_ID);
        $joined = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
        $removed = 0;
        for ($i = 0; $i < sizeof($joined['Agent']); $i++) {
          /** @var Agent $ag */
          $ag = $joined['Agent'][$i];
          if (time() - $ag->getLastAct() > $CONFIG->getVal(DConfig::AGENT_TIMEOUT) || $ag->getIsActive() == 0) {
            $FACTORIES::getAssignmentFactory()->delete($joined['Assignment'][$i]); // delete timed out
            $removed++;
          }
        }
        if ($removed < sizeof($joined['Agent'])) {
          continue; // still some assigned
        }
      }
      
      // if one matches now, it's the best choice (hopefully)
      return $task;
    }
    return null;
  }
  
  /**
   * @param $task Task
   * @param $agent Agent
   * @return bool
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
    $qF = new QueryFilter(TaskFile::TASK_ID, $task->getId(), "=");
    $jF = new JoinFilter($FACTORIES::getFileFactory(), File::FILE_ID, TaskFile::FILE_ID);
    $joinedFiles = $FACTORIES::getTaskFileFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    foreach ($joinedFiles['File'] as $file) {
      /** @var $file File */
      if ($file->getSecret() > $agent->getIsTrusted()) {
        return false;
      }
    }
    return true;
  }
  
  /**
   * @param $task Task
   * @param $agent Agent
   * @return bool
   */
  public static function taskCanBeUsed($task, $agent) {
    global $FACTORIES;
    
    if (!self::agentHasAccessToTask($task, $agent)) {
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
    if ($task->getKeyspace() != $dispatched) {
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
    //TODO NOT YET IMPLEMENTED
    global $FACTORIES;
    
    $entry = $FACTORIES::getStoredValueFactory()->get("lastZapCleaning");
    if ($entry == null) {
      $entry = new StoredValue("lastZapCleaning", 0);
      $FACTORIES::getStoredValueFactory()->save($entry);
    }
    if (time() - $entry->getVal() > 600) {
      //TODO: zap cleaning
      $entry->setVal(time());
      $FACTORIES::getStoredValueFactory()->update($entry);
    }
  }
  
  /**
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
   * Refreshes the page
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
   * @param $list hashlist-object
   * @return Hashlist[] of all superhashlists belonging to the $list
   */
  public static function checkSuperHashlist($list) {
    global $FACTORIES;
    
    if ($list->getFormat() == 3) {
      $hashlistJoinFilter = new JoinFilter($FACTORIES::getHashlistFactory(), Hashlist::HASHLIST_ID, SuperHashlistHashlist::HASHLIST_ID);
      $superHashListFilter = new QueryFilter(SuperHashlistHashlist::SUPER_HASHLIST_ID, $list->getId(), "=");
      $joined = $FACTORIES::getSuperHashlistHashlistFactory()->filter(array($FACTORIES::JOIN => $hashlistJoinFilter, $FACTORIES::FILTER => $superHashListFilter));
      $lists = $joined['Hashlist'];
      return $lists;
    }
    return array($list);
  }
  
  //OLD PART
  /**
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
   * Checks if a file is writable
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
   * @param $prog int progress so far
   * @param $total int total to be done
   * @return string either the check.png with Finished or an empty string
   */
  public static function tickdone($prog, $total) {
    // show tick of progress is done
    if ($total > 0 && $prog == $total) {
      return " <img src='static/check.png' alt='Finished'>";
    }
    return "";
  }
  
  /**
   * Used in Template
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
  
  public static function escapeSpecial($string) {
    $string = htmlentities($string, false, "UTF-8");
    $string = str_replace('"', '&#34;', $string);
    $string = str_replace("'", "&#39;", $string);
    $string = str_replace('`', '&#96;', $string);
    return $string;
  }
  
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
   * @param $val string of the array
   * @param $id int index of the array
   * @return string the element or empty string
   */
  public static function getStaticArray($val, $id) { // TODO: this function should get obsolete later
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
          return $platforms;
        }
        return $platforms[$val];
        break;
    }
    return "";
  }
  
  /**
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
   * @param $target string File you want to write to
   * @param $type string paste, upload, import or url
   * @param $sourcedata string
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
            $msg = "File upload failed: ". $hashfile['error'];
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
  
  public static function buildServerUrl() {
    $protocol = (isset($_SERVER['HTTPS']) && (strcasecmp('off', $_SERVER['HTTPS']) !== 0)) ? "https://" : "https://";
    $hostname = $_SERVER['HTTP_HOST'];
    $port = $_SERVER['SERVER_PORT'];
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
   * TODO Document me
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
   * @param $type
   * @param $msg
   * @return string
   */
  public static function getMessage($type, $msg) {
    return "<div class='alert alert-$type'>$msg</div>";
  }
}
