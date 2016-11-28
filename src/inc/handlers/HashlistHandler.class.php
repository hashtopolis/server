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
        $this->createWordlist();
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
          break;
        }
        $hlist = intval($_POST["hashlist"]);
        $res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT hashlists.*,IFNULL(hashes.salted,0) AS salted FROM hashlists LEFT JOIN (SELECT hashlist,1 AS salted FROM hashes WHERE hashlist=$hlist AND salt!='' LIMIT 1) hashes ON hashlists.format=0 AND hashes.hashlist=hashlists.id WHERE hashlists.id=$hlist");
        $list = $res->fetch();
        if ($list) {
          $listSet = new DataSet();
          $listSet->setValues($list);
          $OBJECTS['list'] = $listSet;
          $OBJECTS['zap'] = true;
          $impfiles = array();
          if (file_exists("import") && is_dir("import")) {
            $impdir = opendir("import");
            $impfiles = array();
            while ($f = readdir($impdir)) {
              if (($f != ".") && ($f != "..") && (!is_dir($f))) {
                $impfiles[] = $f;
              }
            }
            $OBJECTS['impfiles'] = $impfiles;
          }
        }
        else {
          $message = "<div class='alert alert-danger'>Invalid hashlist!</div>";
        }
        break;
      case 'hashlistdelete':
        if ($LOGIN->getLevel() < 30) {
          break;
        }
        // delete hashlist
        $message = "<div class='alert alert-neutral'>";
        $hlist = intval($_POST["hashlist"]);
        $FACTORIES::getagentsFactory()->getDB()->exec("START TRANSACTION");
        $res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT hashlists.format,hashlists.hashcount FROM hashlists WHERE hashlists.id=$hlist");
        $list = $res->fetch();
        $hcount = $list["hashcount"];
    
        // decrease supercount by that of deleted hashlist
        $ans0 = $FACTORIES::getagentsFactory()->getDB()->query("UPDATE hashlists JOIN superhashlists ON superhashlists.id=hashlists.id AND hashlists.format=3 AND superhashlists.hashlist=$hlist JOIN hashlists hashlists2 ON hashlists2.id=superhashlists.hashlist SET hashlists.cracked=hashlists.cracked-hashlists2.cracked,hashlists.hashcount=hashlists.hashcount-hashlists2.hashcount");
    
        // then actually delete the list
        $ans1 = $ans0 && $FACTORIES::getagentsFactory()->getDB()->query("DELETE FROM hashlists WHERE id=$hlist");
        $ans2 = $ans1 && $FACTORIES::getagentsFactory()->getDB()->query("DELETE FROM hashlistusers WHERE hashlist=$hlist");
        $ans3 = $ans2 && $FACTORIES::getagentsFactory()->getDB()->query("DELETE FROM zapqueue WHERE hashlist=$hlist");
    
        // and its tasks
        $ans4 = $ans3 && $FACTORIES::getagentsFactory()->getDB()->query("DELETE FROM taskfiles WHERE task IN (SELECT id FROM tasks WHERE hashlist=$hlist)");
        $ans5 = $ans4 && $FACTORIES::getagentsFactory()->getDB()->query("DELETE FROM assignments WHERE task IN (SELECT id FROM tasks WHERE hashlist=$hlist)");
        $ans6 = $ans5 && $FACTORIES::getagentsFactory()->getDB()->query("DELETE FROM chunks WHERE task IN (SELECT id FROM tasks WHERE hashlist=$hlist)");
        $ans7 = $ans6 && $FACTORIES::getagentsFactory()->getDB()->query("DELETE FROM tasks WHERE hashlist=$hlist");
    
        $ans8 = $ans7 && $FACTORIES::getagentsFactory()->getDB()->query("DELETE FROM superhashlists WHERE hashlist=$hlist");
    
        if ($ans8) {
          $FACTORIES::getagentsFactory()->getDB()->exec("COMMIT");
          $message .= "Deleted hashlist and associated zaps.<br>";
          switch ($list["format"]) {
            case 0:
              $res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT 1 FROM hashlists WHERE format=0");
              if ($res->rowCount() > 0) {
                $message .= "Deleting the actual rows (this is going to take A LONG TIME!)...<br>";
                $hdelete = 0;
                $kolik = 1;
                $cas_pinfo = time();
                $cas_start = time();
                $FACTORIES::getagentsFactory()->getDB()->exec("START TRANSACTION");
                while ($kolik > 0) {
                  $kver = "DELETE FROM " . Util::getStaticArray(0, 'formattables') . " WHERE hashlist=$hlist LIMIT 20000";
                  $ans1 = $DB->query($kver);
                  $kolik = $ans1->rowCount();
                  $hdelete += $kolik;
                  if (time() >= $cas_pinfo + 10) {
                    $message .= "Progress: $hdelete/$hcount, time spent: " . (time() - $cas_start) . " sec<br>";
                    $DB->exec("COMMIT");
                    $DB->exec("START TRANSACTION");
                    $cas_pinfo = time();
                  }
                }
                $FACTORIES::getagentsFactory()->getDB()->exec("COMMIT");
              }
              else {
                $message .= "This was the last hashlist, truncating the table.";
                $FACTORIES::getagentsFactory()->getDB()->exec("TRUNCATE TABLE " . Util::getStaticArray(0, 'formattables'));
              }
              header("Location: hashlists.php");
              die();
              break;
        
            case 1:
            case 2:
              $message .= "Deleting binary hashes...<br>";
              $FACTORIES::getagentsFactory()->getDB()->exec("DELETE FROM hashes_binary WHERE hashlist=$hlist");
              header("Location: hashlists.php");
              die();
              break;
            case 3:
              $message .= "Deleting superhashlist links...<br>";
              $FACTORIES::getagentsFactory()->getDB()->exec("DELETE FROM superhashlists WHERE id=$hlist");
              header("Location: superhashlists.php");
              die();
              break;
          }
        }
        else {
          $FACTORIES::getagentsFactory()->getDB()->exec("ROLLBACK");
          $message .= "Problems deleting hashlist!";
        }
        $message .= "</div>";
        break;
      case 'newhashlistp':
        // new hashlist creator
        $name = $DB->quote(htmlentities($_POST["name"], false, "UTF-8"));
        $salted = (isset($_POST["salted"]) && intval($_POST["salted"]) == 1);
        $hexsalted = (isset($_POST["hexsalted"]) && $salted && intval($_POST["hexsalted"]) == 1);
        if ($hexsalted) {
          $hexsalted = 1;
        }
        else {
          $hexsalted = 0;
        }
        $fs = substr($DB->quote($_POST["separator"]), 1, -1);
        $format = $_POST["format"];
        $hashtype = intval($_POST["hashtype"]);
        $message = "<div class='alert alert-neutral'>";
        if ($format >= 0 && $format <= 2) {
          if (strlen($name) == 0) {
            $message .= "You must specify hashlist name";
          }
          else {
            $message .= "Creating hashlist in the DB...";
            $res = $DB->exec("INSERT INTO hashlists (name, format,hashtype, hexsalt) VALUES ($name, $format, $hashtype, $hexsalted)");
            if ($res) {
              // insert succeeded
              $id = $DB->lastInsertId();
              $message .= "OK (id: $id)<br>";
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
              $tmpfile = "hashlist_$id";
              if (Util::uploadFile($tmpfile, $source, $sourcedata) && file_exists($tmpfile)) {
                $hsize = filesize($tmpfile);
                if ($hsize > 0) {
                  $message .= "Opening file $tmpfile ($hsize B)...";
                  $hhandle = fopen($tmpfile, "rb");
                  $message .= "OK<br>";
              
                  $pocet = 0;
                  $chyby = 0;
                  $cas_start = time();
              
                  switch ($format) {
                    case 0:
                      $message .= "Determining line separator...";
                      // read buffer and get the pointer back to start
                      $buf = fread($hhandle, 1024);
                      $seps = array("\r\n", "\n", "\r");
                      $ls = "";
                      foreach ($seps as $sep) {
                        if (strpos($buf, $sep) !== false) {
                          $ls = $sep;
                          $message .= Util::bintohex($ls);
                          break;
                        }
                      }
                      if ($ls == "") {
                        $message .= "nothing (assuming one hash)";
                      }
                      $message .= "<br>";
                      if ($salted) {
                        // find out if the first line contains field separator
                        $message .= "Searching for field separator inside the first line...";
                        rewind($hhandle);
                        $bufline = stream_get_line($hhandle, 1024, $ls);
                        if (strpos($bufline, $fs) === false) {
                          $message .= "NOTHING - assuming unsalted hashes";
                          $fs = "";
                        }
                        else {
                          $message .= "OK - assuming salted hashes";
                        }
                        $message .= "<br>";
                      }
                      else {
                        $fs = "";
                      }
                      // now read the lines
                      $message .= "Importing hashes from text file...<br>";
                      rewind($hhandle);
                  
                      $tmpfull = $DB->quote(dirname(__FILE__) . "/chunk_$id");
                  
                      // how many hashes to import at once:
                      $loopsize = 100000;
                  
                      while (!feof($hhandle)) {
                        $tmpchunk = fopen("chunk_$id", "w");
                        $chunklines = 0;
                        while (!feof($hhandle) && $chunklines < $loopsize) {
                          $dato = stream_get_line($hhandle, 1024, $ls);
                          if ($dato == "") {
                            continue;
                          }
                          fwrite($tmpchunk, $dato . $ls);
                          $chunklines++;
                        }
                        fclose($tmpchunk);
                        $message .= "Loading $chunklines lines...";
                        $cas_xstart = time();
                        // try fast load data
                        $kv = "LOAD DATA INFILE $tmpfull IGNORE INTO TABLE hashes " . ($fs == "" ? "" : "FIELDS TERMINATED BY '$fs' ") . "LINES TERMINATED BY " . $DB->quote($ls) . " (hash, salt) SET hashlist=$id";
                        $kvr = false;
                        try {
                          $kvr = $DB->query($kv);
                        }
                        catch (Exception $e) {
                          $kvr = false;
                        }
                        if ($kvr) {
                          $message .= "OK";
                          $pocet += $kvr->rowCount();
                        }
                        else {
                          // load data failed, could be bad privileges or mysql on different server than www
                          $message .= "fail, inserting...";
                          $slow = fopen("chunk_$id", "r");
                          $DB->exec("START TRANSACTION");
                          $buffer = array();
                          $bufferCount = 0;
                          while (!feof($slow)) {
                            $dato = stream_get_line($slow, 1024, $ls);
                            if ($fs == "") {
                              $hash = $dato;
                              $salt = "";
                            }
                            else {
                              $poz = strpos($dato, $fs);
                              if ($poz !== false) {
                                $hash = substr($dato, 0, $poz);
                                $salt = substr($dato, $poz + 1);
                              }
                              else {
                                $hash = $dato;
                                $salt = "";
                              }
                            }
                            if (strlen($hash) == 0) {
                              continue; //this is a problem from files which contain empty lines
                            }
                            $hash = $DB->quote($hash);
                            $salt = $DB->quote($salt);
                            $buffer[] = "($id, $hash, $salt)";
                            $bufferCount++;
                            if ($bufferCount >= 10000) {
                              $check = $DB->query("INSERT IGNORE INTO hashes (hashlist,hash,salt) VALUES " . implode(", ", $buffer));
                              $pocet += $check->rowCount();
                              $buffer = array();
                              $bufferCount = 0;
                            }
                          }
                          if (sizeof($buffer) > 0) {
                            $check = $DB->query("INSERT IGNORE INTO hashes (hashlist,hash,salt) VALUES " . implode(", ", $buffer));
                            $pocet += $check->rowCount();
                          }
                          fclose($slow);
                          $DB->exec("COMMIT");
                        }
                        $message .= " (took " . (time() - $cas_xstart) . "s, total $pocet)<br>";
                      }
                      unlink("chunk_$id");
                      break;
                    case 1:
                      $message .= "Importing wireless networks...<br>";
                      while (!feof($hhandle)) {
                        $dato = fread($hhandle, 392);
                        if (strlen($dato) == 392) {
                          $nazev = "";
                          for ($i = 0; $i < 36; $i++) {
                            $znak = $dato[$i];
                            if ($znak != "\x00") {
                              $nazev .= $znak;
                            }
                            else {
                              break;
                            }
                          }
                          $message .= "Found network $nazev";
                          $res = $DB->query("INSERT INTO hashes_binary (hashlist, essid, hash) VALUES ($id, '$nazev',x'" . Util::bintohex($dato) . "')");
                          if ($res) {
                            $pocet += $res->rowCount();
                          }
                          else {
                            $chyby++;
                          }
                        }
                        else {
                          if (strlen($dato) > 0) {
                            $message .= "Found garbage (only " . strlen($dato) . " bytes)";
                          }
                        }
                        $message .= "<br>";
                      }
                      break;
                    case 2:
                      if (!feof($hhandle)) {
                        $dato = fread($hhandle, $hsize);
                        $message .= "Inserting binary file as one hash...<br>";
                        $res = $DB->query("INSERT INTO hashes_binary (hashlist, hash) VALUES ($id, x'" . Util::bintohex($dato) . "')");
                        if ($res) {
                          $message .= "OK";
                          $pocet = $res->rowCount();
                        }
                        else {
                          $message .= "ERROR";
                          $chyby++;
                        }
                        $message .= "<br>";
                      }
                      break;
                  }
                  fclose($hhandle);
                  $message .= "<br>";
                  $cas_stop = time();
              
                  // evaluate, what have we accomplished
                  if ($pocet > 0) {
                    $DB->exec("UPDATE hashlists SET hashcount=$pocet WHERE id=$id");
                    $message .= "Insert completed ($pocet hashes inserted, $chyby errors, took " . ($cas_stop - $cas_start) . " sec)";
                  }
                  else {
                    $message .= "ERROR";
                    $DB->exec("DELETE FROM hashlists WHERE id=$id");
                    $message .= "Nothing was inserted ($chyby errors). Perhaps empty hashlist or database problem?";
                  }
                }
                else {
                  $message .= "Hashlist file is empty!";
                }
                unlink($tmpfile);
              }
              /*header("Location: hashlists.php");
              die();*/
            }
            else {
              $message .= "ERROR: " . $DB->errorInfo();
            }
            $message .= "<br>";
          }
        }
        else {
          $message .= "Select correct hashlist format";
        }
        $message .= "</div>";
        break;
      default:
        UI::addMessage("danger", "Invalid action!");
        break;
    }
  }
  
  private function export(){
    global $FACTORIES;
  
    // export cracked hashes to a file
    $this->hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
    if($this->hashlist == null){
      UI::printError("ERROR", "Invalid hashlist!");
    }
    $hashlists =
    
    
    $list = $res->fetch();
    if ($list) {
      $format = $list['format'];
      // create proper superhashlist field if needed
      list($superhash, $hlisty) = Util::superList($hlist, $format);
    
      $tmpfile = "Pre-cracked_" . $hlist . "_" . date("Y-m-d_H-i-s") . ".txt";
      $tmpfull = dirname(__FILE__) . "/files/" . $tmpfile;
      $salted = false;
      $kvery1 = "SELECT ";
      switch ($format) {
        case 0:
          $res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT 1 FROM hashes WHERE hashlist IN ($hlisty) AND salt!='' LIMIT 1");
          if ($res->rowCount() > 0) {
            $kvery1 .= "hash,salt,plaintext";
            $salted = true;
          }
          else {
            $kvery1 .= "hash,plaintext";
          }
          break;
        case 1:
          $kvery1 .= "essid AS hash,plaintext";
          break;
        case 2:
          $kvery1 .= "plaintext";
          break;
      }
      $kvery2 = " INTO OUTFILE '$tmpfull' FIELDS TERMINATED BY " . $FACTORIES::getagentsFactory()->getDB()->quote($CONFIG->getVal("fieldseparator")) . " ESCAPED BY '' LINES TERMINATED BY '\\n'";
      $kvery3 = " FROM " . Util::getStaticArray($format, 'formattables') . " WHERE hashlist IN ($hlisty) AND plaintext IS NOT NULL";
      if (!file_exists("files")) {
        mkdir("files");
      }
      $kvery = $kvery1 . $kvery2 . $kvery3;
      $res = false;
      try {
        $res = $FACTORIES::getagentsFactory()->getDB()->exec($kvery);
      }
      catch (Exception $e) {
        $res = false;
      }
      $message = "<div class='alert alert-neutral'>";
      if (!$res) {
        $message .= "File export failed, trying SELECT with file output<br>";
        $kvery = $kvery1 . $kvery3;
        $res = $FACTORIES::getagentsFactory()->getDB()->query($kvery);
        $res = $res->fetchAll();
        $fexp = fopen("files/" . $tmpfile, "w");
        foreach ($res as $entry) {
          fwrite($fexp, $entry["hash"] . ($salted ? $CONFIG->getVal("fieldseparator") . $entry["salt"] : "") . $CONFIG->getVal("fieldseparator") . $entry["plaintext"] . "\n");
        }
        $res = true;
        fclose($fexp);
      }
      if ($res) {
        if (Util::insertFile("files/" . $tmpfile)) {
          $message .= "Cracked hashes from hashlist $hlist exported.</div>";
          /*if($superhash){
            header("Location: superhashlists.php");
            die();
          }
          else{
            header("Location: hashlists.php");
            die();
          }*/
        }
        else {
          $message .= "Cracked hashes exported, but the file is missing.</div>";
        }
      }
      else {
        $message .= "Could not export hashlist $hlist</div>";
      }
    }
    else {
      $message = "<div class='alert alert-danger'>No such hashlist.</div>";
    }
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
    $salted = $_POST['salted'];
    
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
    $tmpfile = sys_get_temp_dir()."/zaplist_".$this->hashlist->getId();
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
      $crackedIn[$l] = 0;
    }
    $alreadyCracked = 0;
    $notFound = 0;
    $invalid = 0;
    $bufferCount = 0;
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
        $qF2 = new ContainFilter("hashlistId", $hashlists);
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
        $qF2 = new ContainFilter("hashlistId", $hashlists);
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
          $ll = $FACTORIES::getHashlistFactory()->get($l);
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
      $ll = $FACTORIES::getHashlistFactory()->get($l);
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