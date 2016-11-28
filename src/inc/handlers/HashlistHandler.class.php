<?php
/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 24.11.16
 * Time: 16:44
 */

class HashlistHandler implements Handler {
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
    global $LOGIN;
    
    switch ($action) {
      case 'preconf':
        if ($LOGIN->getLevel() < 20) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->preconf();
        break;
      case 'wordlist':
        if ($LOGIN->getLevel() < 20) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->createWordlists();
        break;
      case 'hashlistsecret':
        if ($LOGIN->getLevel() < 30) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->toggleSecret();
        break;
      case 'hashlistrename':
        if ($LOGIN->getLevel() < 20) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->rename();
        break;
      case 'hashlistzapp':
        if ($LOGIN->getLevel() < 20) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->processZap();
        break;
      case 'export':
        if ($LOGIN->getLevel() < 20) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->export();
        break;
      case 'hashlistzap':
        if ($LOGIN->getLevel() < 20) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->zap();
        break;
      case 'hashlistdelete':
        if ($LOGIN->getLevel() < 30) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->delete();
        break;
      case 'newhashlistp':
        $this->create();
        break;
      default:
        UI::addMessage("danger", "Invalid action!");
        break;
    }
  }
  
  private function create(){
    global $FACTORIES;
  
    $name = htmlentities($_POST["name"], false, "UTF-8");
    $salted = (isset($_POST["salted"]) && intval($_POST["salted"]) == 1)?"1":"0";
    $secret = (isset($_POST["secret"]) && intval($_POST["secret"]) == 1)?"1":"0";
    $hexsalted = (isset($_POST["hexsalted"]) && $salted && intval($_POST["hexsalted"]) == 1)?"1":"0";
    $separator = $_POST["separator"];
    $format = intval($_POST["format"]);
    $hashtype = intval($_POST["hashtype"]);
    $saltSeparator = $_POST['separator'];
    if($format < 0 || $format > 2){
      UI::printError("ERROR", "Invalid hashlist format!");
    }
    else if(strlen($name) == 0){
      UI::printError("ERROR", "Hashlist name cannot be empty!");
    }
    else if($salted == '1' && strlen($saltSeparator) == 0){
      UI::printError("ERROR", "Salt separator cannot be empty when hashes are salted!");
    }
    
    $this->hashlist = new Hashlist(0, $name, $format, $hashtype, 0, $separator, 0, $secret, $hexsalted, $salted);
    $this->hashlist = $FACTORIES::getHashlistFactory()->save($this->hashlist);
  
    $source = $_POST["source"];
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
    $tmpfile = dirname(__FILE__)."/../../tmp/hashlist_".$this->hashlist->getId();
    if (!Util::uploadFile($tmpfile, $source, $sourcedata) && file_exists($tmpfile)) {
      UI::printError("ERROR", "Failed to process file!");
    }
    else if(!file_exists($tmpfile)){
      UI::printError("ERROR", "Required file does not exist!");
    }
    $file = fopen($tmpfile, "rb");
    if(!$file){
      UI::printError("ERROR", "Failed to open file!");
    }
    $added = 0;
    
    switch($format) {
      case 0:
        $buf = fread($file, 1024);
        $lineSeparators = array("\r\n", "\n", "\r");
        $lineSeparator = "";
        foreach ($lineSeparators as $sep) {
          if (strpos($buf, $sep) !== false) {
            $lineSeparator = $sep;
            break;
          }
        }
        if ($salted) {
          // find out if the first line contains field separator
          rewind($file);
          $bufline = stream_get_line($file, 1024, $lineSeparator);
          if (strpos($bufline, $saltSeparator) === false && $lineSeparator != "") {
            UI::printError("ERROR", "Salted hashes separator not found in file!");
          }
        }
        else {
          $saltSeparator = "";
        }
        rewind($file);
        $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
        $values = array();
        $bufferCount = 0;
        while (!feof($file)) {
          $line = stream_get_line($file, 1024, $lineSeparator);
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
          $values[] = new Hash(0, $this->hashlist->getId(), $hash, $salt, "", 0, 0, 0);
          $bufferCount++;
          if ($bufferCount >= 10000) {
            $result = $FACTORIES::getHashFactory()->massSave($values);
            $added += $result->rowCount();
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
        header("Location: hashlists.php?id=" . $this->hashlist->getId());
        die();
      case 1:
        $added = 0;
        while (!feof($file)) {
          $data = fread($file, 392);
          if (strlen($data) != 392) {
            UI::printError("ERROR", "Data file only contains " . strlen($data) . " bytes!");
          }
          $network = "";
          for ($i = 0; $i < 36; $i++) {
            $byte = $data[$i];
            if ($byte != "\x00") {
              $network .= $byte;
            }
            else {
              break;
            }
          }
          $hash = new HashBinary(0, $this->hashlist->getId(), $network, Util::bintohex($data), "", 0, 0, 0);
          $FACTORIES::getHashBinaryFactory()->save($hash);
          $added++;
        }
        fclose($file);
        unlink($tmpfile);
        $this->hashlist->setHashCount($added);
        $FACTORIES::getHashlistFactory()->update($this->hashlist);
        header("Location: hashlists.php?id=" . $this->hashlist->getId());
        die();
      case 2:
        if (!feof($file)) {
          $data = fread($file, Util::filesize($tmpfile));
          $hash = new HashBinary(0, $this->hashlist->getId(), "", Util::bintohex($data), "", 0, 0, 0);
          $FACTORIES::getHashBinaryFactory()->save($hash);
        }
        fclose($file);
        unlink($tmpfile);
        $this->hashlist->setHashCount(1);
        $FACTORIES::getHashlistFactory()->update($this->hashlist);
        header("Location: hashlists.php?id=" . $this->hashlist->getId());
        die();
    }
  }
  
  private function zap(){
    global $FACTORIES, $OBJECTS;
  
    $this->hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
    if($this->hashlist == null){
      UI::printError("ERROR", "Invalid hashlist!");
    }
    $type = $FACTORIES::getHashTypeFactory()->get($this->hashlist->getHashtypeId());
    
    $OBJECTS['list'] = new DataSet(array('hashlist' => $this->hashlist, 'hashtype' => $type));
    $OBJECTS['zap'] = true;
    $OBJECTS['impfiles'] = Util::scanImportDirectory();
  }
  
  private function export(){
    global $FACTORIES, $CONFIG;
  
    // export cracked hashes to a file
    $this->hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
    if($this->hashlist == null){
      UI::printError("ERROR", "Invalid hashlist!");
    }
    $hashlists = Util::checkSuperHashlist($this->hashlist);
    $tmpfile = dirname(__FILE__)."/../../file/Pre-cracked_" . $this->hashlist->getId() . "_" . date("d-m-Y_H-i-s") . ".txt";
    $factory = $FACTORIES::getHashFactory();
    $format = $FACTORIES::getHashlistFactory()->get($hashlists[0]);
    if($format != 0){
      $factory = $FACTORIES::getHashBinaryFactory();
    }
    $file = fopen($tmpfile, "wb");
    if(!$file){
      UI::printError("ERROR", "Failed to write file!");
    }
    
    $qF1 = new ContainFilter("hashlistId", $hashlists);
    $qF2 = new QueryFilter("isCracked", "1", "=");
    $count = $factory->countFilter(array('filter' => array($qF1, $qF2)));
    $pagingSize = 5000;
    if($CONFIG->getVal('pagingSize') !== false){
      $pagingSize = $CONFIG->getVal('pagingSize');
    }
    $separator = $CONFIG->getVal("fieldseparator");
    for($x=0;$x*$pagingSize<$count;$x++){
      $oF = new OrderFilter("hashId", "ASC LIMIT ".($x*$pagingSize).",$pagingSize");
      $entries = $factory->filter(array('filter' => array($qF1, $qF2), 'order' => array($oF)));
      $buffer = "";
      foreach($entries as $entry){
        switch($format){
          case 0:
            if($this->hashlist->isSalted()){
              $buffer .= $entry->getHash().$separator.$entry->getSalt().$separator.$entry->getPlaintext()."\n";
            }
            else{
              $buffer .= $entry->getHash().$separator.$entry->getPlaintext()."\n";
            }
            break;
          case 1:
            $buffer .= $entry->getEssid().$separator.$entry->getPlaintext()."\n";
            break;
          case 2:
            $buffer .= $entry->getPlaintext()."\n";
            break;
        }
      }
      fputs($file, $buffer);
    }
    fclose($file);
    UI::addMessage("success", "Cracked hashes from hashlist exported successfully!");
  }
  
  private function delete() {
    global $FACTORIES;
  
    $this->hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
    if ($this->hashlist == null) {
      UI::printError("ERROR", "Invalid hashlist!");
    }
  
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
  
    $qF = new QueryFilter("hashlistId", $this->hashlist->getId(), "=");
    $jF = new JoinFilter($FACTORIES::getHashlistFactory(), "hashlistId", "hashlistId");
    $superlists = $FACTORIES::getSuperHashlistHashlistFactory()->filter(array('filter' => array($qF), 'join' => array($jF)));
    for ($x = 0; $x < sizeof($superlists['Hashlist']); $x++) {
      $superlists['Hashlist'][$x]->setHashCount($superlists['Hashlist'][$x]->getHashCount() - $this->hashlist->getHashCount());
      $superlists['Hashlist'][$x]->setCracked($superlists['Hashlist'][$x]->getCracked() - $this->hashlist->getCracked());
      $FACTORIES::getHashlistFactory()->update($superlists['Hashlist'][$x]);
    }
  
    //TODO: delete from zapqueue
  
    $qF = new QueryFilter("hashlistId", $this->hashlist->getId(), "=");
    $tasks = $FACTORIES::getTaskFactory()->filter(array('filter' => array($qF)));
    $taskList = array();
    foreach ($tasks as $task) {
      $taskList[] = $task->getId();
    }
    $FACTORIES::getSuperHashlistHashlistFactory()->massDeletion(array('filter' => array($qF)));
  
    if (sizeof($taskList) > 0) {
      $qF = new ContainFilter("taskId", $taskList);
      $FACTORIES::getTaskFileFactory()->massDeletion(array('filter' => array($qF)));
      $FACTORIES::getAssignmentFactory()->massDeletion(array('filter' => array($qF)));
      $FACTORIES::getChunkFactory()->massDeletion(array('filter' => array($qF)));
    }
    foreach($tasks as $task){
      $FACTORIES::getTaskFactory()->delete($task);
    }
    
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
    switch($this->hashlist->getFormat()){
      case 0:
        $count = $FACTORIES::getHashlistFactory()->countFilter(array());
        if($count > 1) {
          $deleted = 1;
          $qF = new QueryFilter("hashlistId", $this->hashlist->getId(), "=");
          $oF = new OrderFilter("hashId", "ASC LIMIT 20000");
          while ($deleted > 0) {
            $result = $FACTORIES::getHashFactory()->massDeletion(array('filter' => array($qF), 'order' => array($oF)));
            $deleted = $result->rowCount();
            $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
            $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
          }
        }
        else{
          $FACTORIES::getAgentFactory()->getDB()->query("TRUNCAT TABLE Hash");
        }
        break;
      case 1:
      case 2:
        $qF = new QueryFilter("hashlistId", $this->hashlist->getId(), "=");
        $FACTORIES::getHashBinaryFactory()->massDeletion(array('filter' => array($qF)));
        break;
      case 3:
        $qF = new QueryFilter("superhashlistId", $this->hashlist->getId(), "=");
        $FACTORIES::getSuperHashlistHashlistFactory()->massDeletion(array('filter' => array($qF)));
        break;
    }
    $FACTORIES::getHashlistFactory()->delete($this->hashlist);
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
    switch($this->hashlist->getFormat()){
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
  
  private function processZap(){
    global $FACTORIES;
  
    // pre-crack hashes processor
    $this->hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
    if($this->hashlist == null){
      UI::printError("ERROR", "Invalid hashlist!");
    }
    $separator = $_POST["separator"];
    $source = $_POST["source"];
    $salted = $this->hashlist->getIsSalted();
    
    // check which source was used
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
    $tmpfile = dirname(__FILE__)."/../../tmp/zaplist_".$this->hashlist->getId();
    if (!Util::uploadFile($tmpfile, $source, $sourcedata)) {
      UI::addMessage("danger", "Failed to process file!");
      return;
    }
    $size = Util::filesize($tmpfile);
    if($size == 0){
      UI::addMessage("danger", "File is empty!");
      return;
    }
    $file = fopen($tmpfile, "rb");
    if(!$file){
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
    $hashlists = Util::checkSuperHashlist($this->hashlist);
    $hashFactory = $FACTORIES::getHashFactory();
    if($hashlists[0]->getFormat() != 0) {
      $hashFactory = $FACTORIES::getHashBinaryFactory();
    }
    //start inserting
    $FACTORIES::getAgentFactory()->getDB()->exec("START TRANSACTION");
    $totalLines = 0;
    $newCracked = 0;
    $crackedIn = array();
    foreach($hashlists as $l){
      $crackedIn[$l->getId()] = 0;
    }
    $alreadyCracked = 0;
    $notFound = 0;
    $invalid = 0;
    $bufferCount = 0;
    $hashlistIds = array();
    foreach($hashlists as $l){
      $hashlistIds[] = $l->getId();
    }
    while (!feof($file)) {
      $data = stream_get_line($file, 1024, $lineSeparator);
      if(strlen($data) == 0){
        continue;
      }
      $totalLines++;
      $split = explode($separator, $data);
      if($salted == '1'){
        if(sizeof($split) < 3){
          $invalid++;
          continue;
        }
        $hash = $split[0];
        $qF1 = new QueryFilter("hash", $hash, "=");
        $qF2 = new ContainFilter("hashlistId", $hashlistIds);
        $hashEntry = $hashFactory->filter(array('filter' => array($qF1, $qF2)), true);
        if($hashEntry == null){
          $notFound++;
          continue;
        }
        else if($hashEntry->getIsCracked() == '1'){
          $alreadyCracked++;
          continue;
        }
        $plain = str_replace($hash.$separator.$hashEntry->getSalt().$separator, "", $data);
        $hashEntry->setPlaintext($plain);
        $hashEntry->setIsCracked(1);
        $hashFactory->update($hashEntry);
        $newCracked++;
        $crackedIn[$hashEntry->getHashlistId()]++;
      }
      else{
        if(sizeof($split) < 2){
          $invalid++;
          continue;
        }
        $hash = $split[0];
        $qF1 = new QueryFilter("hash", $hash, "=");
        $qF2 = new ContainFilter("hashlistId", $hashlistIds);
        $hashEntry = $hashFactory->filter(array('filter' => array($qF1, $qF2)), true);
        if($hashEntry == null){
          $notFound++;
          continue;
        }
        else if($hashEntry->getIsCracked() == '1'){
          $alreadyCracked++;
          continue;
        }
        $plain = str_replace($hash.$separator, "", $data);
        $hashEntry->setPlaintext($plain);
        $hashEntry->setIsCracked(1);
        $hashFactory->update($hashEntry);
        $crackedIn[$hashEntry->getHashlistId()]++;
        $newCracked++;
      }
      $bufferCount++;
      if($bufferCount > 1000){
        foreach($hashlists as $l) {
          $ll = $FACTORIES::getHashlistFactory()->get($l->getId());
          $ll->setCracked($ll->getCracked() + $crackedIn[$ll->getId()]);
          $FACTORIES::getHashlistFactory()->update($ll);
        }
        $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
        $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
        $bufferCount = 0;
      }
    }
    $endTime = time();
    fclose($file);
    if(file_exists($tmpfile)){
      unlink($tmpfile);
    }
    
    //finish
    foreach($hashlists as $l) {
      $ll = $FACTORIES::getHashlistFactory()->get($l->getId());
      $ll->setCracked($ll->getCracked() + $crackedIn[$ll->getId()]);
      $FACTORIES::getHashlistFactory()->update($ll);
    }
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
    UI::addMessage("success", "Processed pre-cracked hashes: $totalLines total lines, $newCracked new cracked hashes, $alreadyCracked were already cracked, $invalid invalid lines, $notFound not matching entries (".($endTime-$startTime)."s)!");
  }
  
  private function rename(){
    global $FACTORIES;
  
    // change hashlist name
    $this->hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
    if($this->hashlist == null){
      UI::printError("ERROR", "Invalid hashlist!");
    }
    $name = htmlentities($_POST["name"], false, "UTF-8");
    $this->hashlist->setHashlistName($name);
    $FACTORIES::getHashlistFactory()->update($this->hashlist);
    Util::refresh();
  }
  
  private function toggleSecret(){
    global $FACTORIES;
    
    // switch hashlist secret state
    $this->hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
    if($this->hashlist == null){
      UI::printError("ERROR", "Invalid hashlist!");
    }
    $secret = intval($_POST["secret"]);
    $this->hashlist->setSecret($secret);
    $FACTORIES::getHashlistFactory()->update($this->hashlist);
    if($secret == 1){
      //handle agents which are assigned to hashlists which are secret now
      //TODO: not sure if this code works
      $jF1 = new JoinFilter($FACTORIES::getTaskFactory(), "taskId", "taskId");
      $jF2 = new JoinFilter($FACTORIES::getHashlistFactory(), "hashlistId", "hashlistId");
      $joined = $FACTORIES::getAssignmentFactory()->filter(array('join' => array($jF1, $jF2)));
      for($x=0;$x<sizeof($joined['Assignment']);$x++){
        if($joined['Hashlist'][$x]->getId() == $this->hashlist->getId()){
          $FACTORIES::getAssignmentFactory()->delete($joined['Assignment'][$x]);
        }
      }
    }
    Util::refresh();
  }
  
  private function createWordlists(){
    global $FACTORIES, $CONFIG;
    
    // create wordlist from hashlist cracked hashes
    $this->hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
    if($this->hashlist == null){
      UI::printError("ERROR", "Invalid hashlist!");
    }
    
    $lists = Util::checkSuperHashlist($this->hashlist);
    if(sizeof($lists) == 0){
      UI::printError("ERROR", "Failed to determine the hashlists which should get exported!");
    }
    
    $wordlistName = "Wordlist_" . $this->hashlist->getId() . "_" . date("d.m.Y_H.i.s") . ".txt";
    $wordlistFile = fopen(dirname(__FILE__)."/../../files/" . $wordlistName, "wb");
    if($wordlistFile === false){
      UI::printError("ERROR", "Failed to write wordlist file!");
    }
    $wordCount = 0;
    $pagingSize = 5000;
    if($CONFIG->getVal('pagingSize') !== false){
      $pagingSize = $CONFIG->getVal('pagingSize');
    }
    foreach($lists as $list){
      $hashFactory = $FACTORIES::getHashFactory();
      if($list->getFormat() != 0){
        $hashFactory = $FACTORIES::getHashBinaryFactory();
      }
      //get number of hashes we need to export
      $qF1 = new QueryFilter("hashlistId", $list->getId(), "=");
      $qF2 = new QueryFilter("plaintext", "", "<>");
      $size = $hashFactory->filter(array('filter' => array($qF1, $qF2)));
      for($x=0;$x*$pagingSize<$size;$x++){
        $buffer = "";
        $oF = new OrderFilter("hashId", "ASC LIMIT ".($x*$pagingSize).", $pagingSize");
        $hashes = $hashFactory->filter(array('filter' => array($qF1, $qF2), 'order' => array($oF)));
        foreach($hashes as $hash){
          $plain = $hash->getPlaintext();
          if (strlen($plain) >= 8 && substr($plain, 0, 5) == "\$HEX[" && substr($plain, strlen($plain) - 1, 1) == "]") {
            $plain = Util::hextobin(substr($plain, 5, strlen($plain) - 6));
          }
          $buffer .= $plain ."\n";
          $wordCount++;
        }
        fputs($wordlistFile, $buffer);
      }
    }
    fclose($wordlistFile);
    
    //add file to files list
    $file = new File(0, $wordlistName, Util::filesize(dirname(__FILE__)."/../../$wordlistName"), $this->hashlist->getSecret(), 0);
    $FACTORIES::getFileFactory()->save($file);
    UI::addMessage("success", "Exported $wordCount found plains to $wordlistName successfully!");
  }
  
  private function preconf(){
    global $FACTORIES;
  
    $this->hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
    if($this->hashlist == null){
      UI::printError("ERROR", "Invalid hashlist!");
    }
    
    $addCount = 0;
    $fileCount = 0;
    if(isset($_POST['task'])){
      $qF = new QueryFilter("hashlistId", "0", "<>");
      $oF = new OrderFilter("priority", "DESC LIMIT 1");
      $highest = $FACTORIES::getHashlistFactory()->filter(array('filter' => array($qF), 'order' => array($oF)), true);
      $priorityBase = 1;
      if($highest != null){
        $priorityBase = $highest->getPriority() + 1;
      }
      foreach($_POST['task'] as $pretask){
        $task = $FACTORIES::getTaskFactory()->get($pretask);
        if($task != null){
          if ($this->hashlist->getHexSalt() == 1 && strpos($task->getAttackCmd(), "--hex-salt") === false) {
            $task->setAttackCmd("--hex-salt ".$task->getAttackCmd());
            $taskPriority = 0;
            $oldTaskId = $task->getId();
            if($task->getPriority() > 0){
              $taskPriority = $priorityBase + $task->getPriority();
            }
            $task->setPriority($taskPriority);
            $task->setId(0);
            $task->setHashlistId($this->hashlist->getId());
            $task = $FACTORIES::getTaskFactory()->save($task);
            $addCount++;
            
            //copy all file associations of the preconf task to the new task
            $qF = new QueryFilter("taskId", $oldTaskId, "=");
            $files = $FACTORIES::getTaskFileFactory()->filter(array('filter' => array($qF)));
            foreach($files as $file){
              $file->setTask($task->getId());
              $file->setId(0);
              $FACTORIES::getTaskFileFactory()->save($file);
              $fileCount++;
            }
          }
        }
      }
      if($addCount > 0) {
        UI::addMessage("success", "Successfully created $addCount new tasks with $fileCount files! You will be forward to the tasks page in 5 seconds.");
        UI::setForward("tasks.php", 5);
      }
      else{
        UI::addMessage("danger", "Didn't create any tasks!");
      }
    }
  }
}