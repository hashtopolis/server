<?php

use DBA\AgentError;
use DBA\Assignment;
use DBA\Chunk;
use DBA\ContainFilter;
use DBA\File;
use DBA\FilePretask;
use DBA\FileTask;
use DBA\Hash;
use DBA\HashBinary;
use DBA\Hashlist;
use DBA\HashlistHashlist;
use DBA\JoinFilter;
use DBA\NotificationSetting;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Task;
use DBA\TaskWrapper;
use DBA\Zap;

class HashlistHandler implements Handler {
  /**
   * @var Hashlist $hashlist
   */
  private $hashlist;
  
  public function __construct($hashlistId = null) {
    global $FACTORIES;
    
    if ($hashlistId == null) {
      $this->hashlist = null;
      return;
    }
    
    $this->hashlist = $FACTORIES::getHashlistFactory()->get($hashlistId);
    if ($this->hashlist == null) {
      UI::printError("FATAL", "Hashlist with ID $hashlistId not found!");
    }
  }
  
  public function handle($action) {
    /** @var Login $LOGIN */
    global $LOGIN;
    
    switch ($action) {
      case DHashlistAction::APPLY_PRECONFIGURED_TASKS:
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->preconf();
        break;
      case DHashlistAction::CREATE_WORDLIST:
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->createWordlists();
        break;
      case DHashlistAction::SET_SECRET:
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->toggleSecret();
        break;
      case DHashlistAction::RENAME_HASHLIST:
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->rename();
        break;
      case DHashlistAction::PROCESS_ZAP:
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->processZap();
        break;
      case DHashlistAction::EXPORT_HASHLIST:
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->export();
        break;
      case DHashlistAction::ZAP_HASHLIST:
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->zap();
        break;
      case DHashlistAction::DELETE_HASHLIST:
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->delete();
        break;
      case DHashlistAction::CREATE_HASHLIST:
        $this->create();
        break;
      case DHashlistAction::CREATE_SUPERHASHLIST:
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->createSuperhashlist();
        break;
      case DHashlistAction::CREATE_LEFTLIST:
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->leftlist();
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
  
  private function leftlist() {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;
    
    $hashlist = $FACTORIES::getHashlistFactory()->get($_POST['hashlist']);
    if ($hashlist == null) {
      UI::printError("ERROR", "Invalid hashlist!");
    }
    else if ($hashlist->getFormat() == DHashlistFormat::WPA || $hashlist->getFormat() == DHashlistFormat::BINARY) {
      UI::printError("ERROR", "You cannot create left lists for binary hashes!");
    }
    $hashlists = Util::checkSuperHashlist($hashlist);
    if ($hashlists[0]->getFormat() != DHashlistFormat::PLAIN) {
      UI::printError("ERROR", "You cannot create left lists for binary hashes!");
    }
    
    $hashlistIds = array();
    foreach ($hashlists as $hl) {
      $hashlistIds[] = $hl->getId();
    }
    
    $qF1 = new QueryFilter(Hash::IS_CRACKED, "0", "=");
    $qF2 = new ContainFilter(Hash::HASHLIST_ID, $hashlistIds);
    
    header("Content-Type: application/force-download");
    header("Content-Description: " . $hashlist->getHashlistName() . "_left.txt");
    header("Content-Disposition: attachment; filename=\"" . $hashlist->getHashlistName() . "_left.txt\"");
    
    $count = 0;
    do {
      $oF = new OrderFilter(Hash::HASH_ID, "ASC LIMIT " . $count . ",5000");
      $hashEntries = $FACTORIES::getHashFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2), $FACTORIES::ORDER => $oF));
      $output = "";
      foreach ($hashEntries as $hashEntry) {
        $output .= $hashEntry->getHash();
        if ($hashlist->getIsSalted()) {
          $output .= $CONFIG->getVal(DConfig::FIELD_SEPARATOR) . $hashEntry->getSalt();
        }
        $output .= "\n";
      }
      echo $output;
      $count += 5000;
    } while (sizeof($hashEntries) > 0);
    die(); // download finished
  }
  
  private function createSuperhashlist() {
    global $FACTORIES;
    
    $hashlists = $_POST["hlist"];
    for ($i = 0; $i < sizeof($hashlists); $i++) {
      if (intval($hashlists[$i]) <= 0) {
        unset($hashlists[$i]);
      }
    }
    if (sizeof($hashlists) == 0) {
      UI::printError("ERROR", "No hashlists selected!");
    }
    $name = htmlentities($_POST["name"], ENT_QUOTES, "UTF-8");
    $qF = new ContainFilter(Hashlist::HASHLIST_ID, $hashlists);
    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $lists = $FACTORIES::getHashlistFactory()->filter(array($FACTORIES::FILTER => $qF));
    if (strlen($name) == 0) {
      $name = "SHL_" . $lists[0]->getHashtypeId();
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
      else if ($accessGroupId != $list->getAccessGroupId()) {
        UI::printError("ERROR", "You cannot create superhashlists from hashlists which belong to different access groups");
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
    header("Location: superhashlists.php");
    die();
  }
  
  private function create() {
    /** @var $LOGIN Login */
    /** @var $CONFIG DataSet */
    global $FACTORIES, $LOGIN, $CONFIG;
    
    $name = htmlentities($_POST["name"], ENT_QUOTES, "UTF-8");
    $salted = (isset($_POST["salted"]) && intval($_POST["salted"]) == 1) ? "1" : "0";
    $secret = (isset($_POST["secret"]) && intval($_POST["secret"]) == 1) ? "1" : "0";
    $hexsalted = (isset($_POST["hexsalted"]) && $salted && intval($_POST["hexsalted"]) == 1) ? "1" : "0";
    $separator = $_POST["separator"];
    $format = intval($_POST["format"]);
    $hashtype = intval($_POST["hashtype"]);
    $saltSeparator = $_POST['separator'];
    $accessGroup = $FACTORIES::getAccessGroupFactory()->get($_POST['accessGroupId']);
    
    if ($format < 0 || $format > 2) {
      UI::printError("ERROR", "Invalid hashlist format!");
    }
    else if ($accessGroup == null) {
      UI::printError("ERROR", "Invalid access group selected!");
    }
    else if (sizeof(AccessUtils::intersection(array($accessGroup), AccessUtils::getAccessGroupsOfUser($LOGIN->getUser()))) == 0) {
      UI::printError("ERROR", "Access group with no rights selected!");
    }
    else if (strlen($name) == 0) {
      UI::printError("ERROR", "Hashlist name cannot be empty!");
    }
    else if ($salted == '1' && strlen($saltSeparator) == 0) {
      UI::printError("ERROR", "Salt separator cannot be empty when hashes are salted!");
    }
    
    $this->hashlist = new Hashlist(0, $name, $format, $hashtype, 0, $separator, 0, $secret, $hexsalted, $salted, $accessGroup->getId());
    $this->hashlist = $FACTORIES::getHashlistFactory()->save($this->hashlist);
    
    $source = $_POST["source"];
    $dataSource = "";
    switch ($source) {
      case "paste":
        $dataSource = $_POST["hashfield"];
        break;
      case "upload":
        $dataSource = $_FILES["hashfile"];
        break;
      case "import":
        $dataSource = $_POST["importfile"];
        break;
      case "url":
        $dataSource = $_POST["url"];
        break;
    }
    $tmpfile = dirname(__FILE__) . "/../../tmp/hashlist_" . $this->hashlist->getId();
    if (!Util::uploadFile($tmpfile, $source, $dataSource) && file_exists($tmpfile)) {
      UI::printError("ERROR", "Failed to process file!");
    }
    else if (!file_exists($tmpfile)) {
      UI::printError("ERROR", "Required file does not exist!");
    }
    else if (Util::countLines($tmpfile) > $CONFIG->getVal(DConfig::MAX_HASHLIST_SIZE)) {
      UI::printError("ERROR", "Hashlist has too many lines!");
    }
    $file = fopen($tmpfile, "rb");
    if (!$file) {
      UI::printError("ERROR", "Failed to open file!");
    }
    $added = 0;
    
    switch ($format) {
      case DHashlistFormat::PLAIN:
        if ($salted) {
          // find out if the first line contains field separator
          rewind($file);
          $bufline = stream_get_line($file, 1024);
          if (strpos($bufline, $saltSeparator) === false) {
            UI::printError("ERROR", "Salted hashes separator not found in file!");
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
          $values[] = new Hash(0, $this->hashlist->getId(), $hash, $salt, "", 0, null, 0);
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
        $this->hashlist->setHashCount($added);
        $FACTORIES::getHashlistFactory()->update($this->hashlist);
        $FACTORIES::getAgentFactory()->getDB()->commit();
        Util::createLogEntry("User", $LOGIN->getUserID(), DLogEntry::INFO, "New Hashlist created: " . $this->hashlist->getHashlistName());
        
        NotificationHandler::checkNotifications(DNotificationType::NEW_HASHLIST, new DataSet(array(DPayloadKeys::HASHLIST => $this->hashlist)));
        
        header("Location: hashlists.php?id=" . $this->hashlist->getId());
        die();
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
          $hash = new HashBinary(0, $this->hashlist->getId(), $mac_ap . $CONFIG->getVal(DConfig::FIELD_SEPARATOR) . $mac_cli . $CONFIG->getVal(DConfig::FIELD_SEPARATOR) . $network, Util::bintohex($data), null, 0, null, 0);
          $FACTORIES::getHashBinaryFactory()->save($hash);
          $added++;
        }
        fclose($file);
        unlink($tmpfile);
        $this->hashlist->setHashCount($added);
        $FACTORIES::getHashlistFactory()->update($this->hashlist);
        Util::createLogEntry("User", $LOGIN->getUserID(), DLogEntry::INFO, "New Hashlist created: " . $this->hashlist->getHashlistName());
        
        NotificationHandler::checkNotifications(DNotificationType::NEW_HASHLIST, new DataSet(array(DPayloadKeys::HASHLIST => $this->hashlist)));
        
        header("Location: hashlists.php?id=" . $this->hashlist->getId());
        die();
      case DHashlistFormat::BINARY:
        if (!feof($file)) {
          $data = fread($file, Util::filesize($tmpfile));
          $hash = new HashBinary(0, $this->hashlist->getId(), "", Util::bintohex($data), "", 0, null, 0);
          $FACTORIES::getHashBinaryFactory()->save($hash);
        }
        fclose($file);
        unlink($tmpfile);
        $this->hashlist->setHashCount(1);
        $FACTORIES::getHashlistFactory()->update($this->hashlist);
        Util::createLogEntry("User", $LOGIN->getUserID(), DLogEntry::INFO, "New Hashlist created: " . $this->hashlist->getHashlistName());
        
        NotificationHandler::checkNotifications(DNotificationType::NEW_HASHLIST, new DataSet(array(DPayloadKeys::HASHLIST => $this->hashlist)));
        
        header("Location: hashlists.php?id=" . $this->hashlist->getId());
        die();
    }
  }
  
  private function zap() {
    global $FACTORIES, $OBJECTS;
    
    $this->hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
    if ($this->hashlist == null) {
      UI::printError("ERROR", "Invalid hashlist!");
    }
    $type = $FACTORIES::getHashTypeFactory()->get($this->hashlist->getHashTypeId());
    
    $OBJECTS['list'] = new DataSet(array('hashlist' => $this->hashlist, 'hashtype' => $type));
    $OBJECTS['zap'] = true;
    $OBJECTS['impfiles'] = Util::scanImportDirectory();
  }
  
  private function export() {
    /** @var DataSet $CONFIG */
    global $FACTORIES, $CONFIG;
    
    // export cracked hashes to a file
    $this->hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
    if ($this->hashlist == null) {
      UI::printError("ERROR", "Invalid hashlist!");
    }
    $hashlists = Util::checkSuperHashlist($this->hashlist);
    $tmpname = "Pre-cracked_" . $this->hashlist->getId() . "_" . date("d-m-Y_H-i-s") . ".txt";
    $tmpfile = dirname(__FILE__) . "/../../files/$tmpname";
    $factory = $FACTORIES::getHashFactory();
    $format = $FACTORIES::getHashlistFactory()->get($hashlists[0]->getId());
    if ($format->getFormat() != 0) {
      $factory = $FACTORIES::getHashBinaryFactory();
    }
    $file = fopen($tmpfile, "wb");
    if (!$file) {
      UI::printError("ERROR", "Failed to write file!");
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
      $oF = new OrderFilter(Hash::HASH_ID, "ASC LIMIT " . ($x * $pagingSize) . ",$pagingSize");
      $entries = $factory->filter(array($FACTORIES::FILTER => array($qF1, $qF2), $FACTORIES::ORDER => array($oF)));
      $buffer = "";
      foreach ($entries as $entry) {
        switch ($format->getFormat()) {
          case 0:
            if ($this->hashlist->getIsSalted()) {
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
    
    $file = new File(0, $tmpname, Util::filesize($tmpfile), $this->hashlist->getIsSecret(), 0);
    $FACTORIES::getFileFactory()->save($file);
    UI::addMessage(UI::SUCCESS, "Cracked hashes from hashlist exported successfully!");
  }
  
  private function delete() {
    global $FACTORIES;
    
    $this->hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
    if ($this->hashlist == null) {
      UI::printError("ERROR", "Invalid hashlist!");
    }
    
    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    
    $qF = new QueryFilter(HashlistHashlist::HASHLIST_ID, $this->hashlist->getId(), "=", $FACTORIES::getHashlistHashlistFactory());
    $jF = new JoinFilter($FACTORIES::getHashlistFactory(), HashlistHashlist::PARENT_HASHLIST_ID, Hashlist::HASHLIST_ID, $FACTORIES::getHashlistHashlistFactory());
    $joined = $FACTORIES::getHashlistHashlistFactory()->filter(array($FACTORIES::FILTER => array($qF), $FACTORIES::JOIN => array($jF)));
    /** @var $superHashlists Hashlist[] */
    $superHashlists = $joined[$FACTORIES::getHashlistFactory()->getModelName()];
    $toDelete = array();
    $toUpdate = array();
    foreach ($superHashlists as $superHashlist) {
      $superHashlist->setHashCount($superHashlist->getHashCount() - $this->hashlist->getHashCount());
      $superHashlist->setCracked($superHashlist->getCracked() - $this->hashlist->getCracked());
      
      if ($superHashlist->getHashCount() <= 0) {
        // this superhashlist has no hashlist which belongs to it anymore -> delete it
        $toDelete = $superHashlist;
      }
      else {
        $toUpdate = $superHashlist;
      }
    }
    $FACTORIES::getHashlistHashlistFactory()->massDeletion(array($FACTORIES::FILTER => array($qF)));
    
    $qF = new QueryFilter(Zap::HASHLIST_ID, $this->hashlist->getId(), "=");
    $FACTORIES::getZapFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    
    $payload = new DataSet(array(DPayloadKeys::HASHLIST => $this->hashlist));
    NotificationHandler::checkNotifications(DNotificationType::DELETE_HASHLIST, $payload);
    
    $qF = new QueryFilter(NotificationSetting::OBJECT_ID, $this->hashlist->getId(), "=");
    $notifications = $FACTORIES::getNotificationSettingFactory()->filter(array($FACTORIES::FILTER => $qF));
    foreach ($notifications as $notification) {
      if (DNotificationType::getObjectType($notification->getAction()) == DNotificationObjectType::HASHLIST) {
        $FACTORIES::getNotificationSettingFactory()->delete($notification);
      }
    }
    
    $qF = new QueryFilter(TaskWrapper::HASHLIST_ID, $this->hashlist->getId(), "=");
    $taskWrappers = $FACTORIES::getTaskWrapperFactory()->filter(array($FACTORIES::FILTER => $qF));
    $taskList = array();
    foreach ($taskWrappers as $taskWrapper) {
      $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
      $tasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => array($qF)));
      foreach ($tasks as $task) {
        $taskList[] = $task;
      }
    }
    
    switch ($this->hashlist->getFormat()) {
      case 0:
        $count = $FACTORIES::getHashlistFactory()->countFilter(array());
        if ($count > 1) {
          $deleted = 1;
          $qF = new QueryFilter(Hash::HASHLIST_ID, $this->hashlist->getId(), "=");
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
        $qF = new QueryFilter(HashBinary::HASHLIST_ID, $this->hashlist->getId(), "=");
        $FACTORIES::getHashBinaryFactory()->massDeletion(array($FACTORIES::FILTER => array($qF)));
        break;
      case 3:
        $qF = new QueryFilter(HashlistHashlist::PARENT_HASHLIST_ID, $this->hashlist->getId(), "=");
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
    
    $FACTORIES::getHashlistFactory()->delete($this->hashlist);
    
    $FACTORIES::getAgentFactory()->getDB()->commit();
    
    switch ($this->hashlist->getFormat()) {
      case 0:
      case 1:
      case 2:
        header("Location: hashlists.php");
        break;
      case 3:
        header("Location: superhashlists.php");
        break;
    }
    die();
  }
  
  private function processZap() {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;
    
    // pre-crack hashes processor
    $this->hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
    if ($this->hashlist == null) {
      UI::printError("ERROR", "Invalid hashlist!");
    }
    $separator = $_POST["separator"];
    $source = $_POST["source"];
    $salted = $this->hashlist->getIsSalted();
    
    // check which source was used
    $sourcedata = "";
    switch ($source) {
      case "paste":
        $sourcedata = $_POST["hashfield"];
        break;
      case "upload":
        $sourcedata = $_FILES["hashfile"];
        break;
      case "import":
        $sourcedata = $_POST["importfile"];
        break;
      case "url":
        $sourcedata = $_POST["url"];
        break;
    }
    
    //put input into a temp file
    $tmpfile = dirname(__FILE__) . "/../../tmp/zaplist_" . $this->hashlist->getId();
    if (!Util::uploadFile($tmpfile, $source, $sourcedata)) {
      UI::addMessage(UI::ERROR, "Failed to process file!");
      return;
    }
    $size = Util::filesize($tmpfile);
    if ($size == 0) {
      UI::addMessage(UI::ERROR, "File is empty!");
      return;
    }
    $file = fopen($tmpfile, "rb");
    if (!$file) {
      UI::printError("ERROR", "Processing of temporary file failed!");
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
    $hashlists = Util::checkSuperHashlist($this->hashlist);
    $inSuperHashlists = array();
    $hashlist = $hashlists[0];
    if (sizeof($hashlists) == 1 && $hashlist->getId() == $this->hashlist->getId()) {
      $qF = new QueryFilter(HashlistHashlist::HASHLIST_ID, $this->hashlist->getId(), "=");
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
    
    if ($this->hashlist->getFormat() == DHashlistFormat::SUPERHASHLIST) {
      $total = array_sum($crackedIn);
      $this->hashlist = $FACTORIES::getHashlistFactory()->get($this->hashlist->getId());
      $this->hashlist->setCracked($this->hashlist->getCracked() + $total);
      $FACTORIES::getHashlistFactory()->update($this->hashlist);
    }
    if (sizeof($inSuperHashlists) > 0) {
      $total = array_sum($crackedIn);
      foreach ($inSuperHashlists as $super) {
        $superHashlist = $FACTORIES::getHashlistFactory()->get($super->getSuperHashlistId());
        $superHashlist->setCracked($superHashlist->getCracked() + $total);
        $FACTORIES::getHashlistFactory()->update($superHashlist);
      }
    }
    $FACTORIES::getAgentFactory()->getDB()->commit();
    UI::addMessage(UI::SUCCESS, "Processed pre-cracked hashes: $totalLines total lines, $newCracked new cracked hashes, $alreadyCracked were already cracked, $invalid invalid lines, $notFound not matching entries (" . ($endTime - $startTime) . "s)!");
    if ($tooLong > 0) {
      UI::addMessage(UI::WARN, "$tooLong entries with too long plaintext");
    }
  }
  
  private function rename() {
    global $FACTORIES;
    
    // change hashlist name
    $this->hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
    if ($this->hashlist == null) {
      UI::printError("ERROR", "Invalid hashlist!");
    }
    $name = htmlentities($_POST["name"], ENT_QUOTES, "UTF-8");
    $this->hashlist->setHashlistName($name);
    $FACTORIES::getHashlistFactory()->update($this->hashlist);
    Util::refresh();
  }
  
  private function toggleSecret() {
    global $FACTORIES;
    
    // switch hashlist secret state
    $this->hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
    if ($this->hashlist == null) {
      UI::printError("ERROR", "Invalid hashlist!");
    }
    $secret = intval($_POST["secret"]);
    $this->hashlist->setIsSecret($secret);
    $FACTORIES::getHashlistFactory()->update($this->hashlist);
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
        if ($hashlist->getId() == $this->hashlist->getId()) {
          $FACTORIES::getAssignmentFactory()->delete($joined[$FACTORIES::getAssignmentFactory()->getModelName()][$x]);
        }
      }
    }
    Util::refresh();
  }
  
  private function createWordlists() {
    /** @var DataSet $CONFIG */
    global $FACTORIES, $CONFIG;
    
    // create wordlist from hashlist cracked hashes
    $this->hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
    if ($this->hashlist == null) {
      UI::printError("ERROR", "Invalid hashlist!");
    }
    
    $lists = Util::checkSuperHashlist($this->hashlist);
    if (sizeof($lists) == 0) {
      UI::printError("ERROR", "Failed to determine the hashlists which should get exported!");
    }
    
    $wordlistName = "Wordlist_" . $this->hashlist->getId() . "_" . date("d.m.Y_H.i.s") . ".txt";
    $wordlistFilename = dirname(__FILE__) . "/../../files/" . $wordlistName;
    $wordlistFile = fopen($wordlistFilename, "wb");
    if ($wordlistFile === false) {
      UI::printError("ERROR", "Failed to write wordlist file!");
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
    $file = new File(0, $wordlistName, Util::filesize($wordlistFilename), $this->hashlist->getIsSecret(), 0);
    $FACTORIES::getFileFactory()->save($file);
    UI::addMessage(UI::SUCCESS, "Exported $wordCount found plains to $wordlistName successfully!");
  }
  
  private function preconf() {
    global $FACTORIES;
    
    $this->hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
    if ($this->hashlist == null) {
      UI::printError("ERROR", "Invalid hashlist!");
    }
    
    $addCount = 0;
    $fileCount = 0;
    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    if (isset($_POST['task'])) {
      $oF = new OrderFilter(Task::PRIORITY, "DESC LIMIT 1");
      $highest = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::ORDER => array($oF)), true);
      $priorityBase = 1;
      if ($highest != null) {
        $priorityBase = $highest->getPriority() + 1;
      }
      foreach ($_POST['task'] as $pretask) {
        $task = $FACTORIES::getPretaskFactory()->get($pretask);
        if ($task != null) {
          if ($this->hashlist->getHexSalt() == 1 && strpos($task->getAttackCmd(), "--hex-salt") === false) {
            $task->setAttackCmd("--hex-salt " . $task->getAttackCmd());
          }
          $taskPriority = 0;
          if ($task->getPriority() > 0) {
            $taskPriority = $priorityBase + $task->getPriority();
          }
          $taskWrapper = new TaskWrapper(0, $taskPriority, DTaskTypes::NORMAL, $this->hashlist->getId(), $this->hashlist->getAccessGroupId(), "");
          $taskWrapper = $FACTORIES::getTaskWrapperFactory()->save($taskWrapper);
          
          $newTask = new Task(0, $task->getTaskName(), $task->getAttackCmd(), $task->getChunkTime(), $task->getStatusTimer(), 0, 0, $taskPriority, $task->getColor(), $task->getIsSmall(), $task->getIsCpuTask(), $task->getUseNewBench(), 0, CrackerBinaryUtils::getNewestVersion($task->getCrackerBinaryTypeId())->getId(), $task->getCrackerBinaryTypeId(), $taskWrapper->getId());
          $newTask = $FACTORIES::getTaskFactory()->save($newTask);
          $addCount++;
          
          $qF = new QueryFilter(FilePretask::PRETASK_ID, $task->getId(), "=");
          $files = $FACTORIES::getFilePretaskFactory()->filter(array($FACTORIES::FILTER => array($qF)));
          foreach ($files as $file) {
            $fileTask = new FileTask(0, $file->getFileId(), $newTask->getId());
            $FACTORIES::getFileTaskFactory()->save($fileTask);
            $fileCount++;
          }
          
          $payload = new DataSet(array(DPayloadKeys::TASK => $task));
          NotificationHandler::checkNotifications(DNotificationType::NEW_TASK, $payload);
        }
      }
      $FACTORIES::getAgentFactory()->getDB()->commit();
      if ($addCount > 0) {
        UI::addMessage(UI::SUCCESS, "Successfully created $addCount new tasks with $fileCount files! You will be forward to the tasks page in 5 seconds.");
        UI::setForward("tasks.php", 5);
      }
      else {
        UI::addMessage(UI::ERROR, "Didn't create any tasks!");
      }
    }
  }
}