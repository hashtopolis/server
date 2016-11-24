<?php

require_once(dirname(__FILE__) . "/inc/load.php");

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']));
  die();
}
else if ($LOGIN->getLevel() < 5) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("hashlists/index");
$MENU->setActive("lists_norm");

//catch agents actions here...
if (isset($_POST['action'])) {
  switch ($_POST['action']) {
    //TODO: handler needs to be done + add new hashlists handling there
    case 'preconf':
      if ($LOGIN->getLevel() < 20) {
        break;
      }
      $hlist = intval($_POST["hashlist"]);
      $res = $DB->query("SELECT * FROM hashlists WHERE id=$hlist");
      $hashlist = $res->fetch();
      $DB = $FACTORIES::getagentsFactory()->getDB();
      $addc = 0;
      $filc = 0;
      if (isset($_POST["task"]) && $hashlist) {
        $res = $DB->query("SELECT IFNULL(MAX(priority),0) AS base FROM tasks WHERE hashlist IS NOT NULL");
        $task = $res->fetch();
        $base = $task["base"];
        foreach ($_POST["task"] as $ida) {
          $id = intval($ida);
          if ($id > 0) {
            $res = $DB->query("SELECT name,attackcmd,chunktime,statustimer,autoadjust,priority FROM tasks WHERE tasks.id=$id");
            $task = $res->fetch();
            if ($hashlist['hexsalt'] == 1 && strpos($task['attackcmd'], "--hex-salt") === false) {
              $task['attackcmd'] = "--hex-salt " . $task['attackcmd'];
            }
            $addq = $DB->query("INSERT INTO tasks (name, attackcmd, hashlist, chunktime, statustimer, autoadjust, priority) VALUES ('HL" . $hlist . "_" . substr($DB->quote($task["name"]), 1, -1) . "', " . $DB->quote($task["attackcmd"]) . ", $hlist, " . $task["chunktime"] . ", " . $task["statustimer"] . ", " . $task["autoadjust"] . ", " . ($task["priority"] > 0 ? $base + $task["priority"] : 0) . ")");
            $addc += $addq->rowCount();
            $tid = $DB->lastInsertId();
            $filq = $DB->query("INSERT INTO taskfiles (task, file) SELECT $tid,file FROM taskfiles WHERE task=$id");
            $filc += $filq->rowCount();
          }
        }
      }
      if ($addc == 0) {
        $message = "<div class='alert alert-warning'>No new tasks were created!</div>";
      }
      else {
        header("Location: tasks.php");
        die();
      }
      break;
    case 'wordlist':
      if ($LOGIN->getLevel() < 20) {
        break;
      }
      // create wordlist from hashlist cracked hashes
      $hlist = intval($_POST["hashlist"]);
      $res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT format FROM hashlists WHERE id=$hlist");
      $res = $res->fetch();
      $message = "<div class='alert alert-neutral'>";
      if ($res) {
        $format = $res["format"];
        
        // create proper superhashlist field if needed
        list($superhash, $hlisty) = Util::superList($hlist, $format);
        
        $kvery = "SELECT plaintext FROM " . Util::getStaticArray($format, 'formattables') . " WHERE hashlist IN ($hlisty) AND plaintext IS NOT NULL";
        $res = $FACTORIES::getagentsFactory()->getDB()->query($kvery);
        if ($res->rowCount() > 0) {
          $wlist = "Wordlist_" . $hlist . "_" . date("Y-m-d_H-i-s") . ".txt";
          $message .= "Opening wordlist for writing...<br>";
          $fx = fopen("files/" . $wlist, "w");
          $p = 0;
          foreach ($res as $entry) {
            $plain = $entry["plaintext"];
            if (strlen($plain) >= 8 && substr($plain, 0, 5) == "\$HEX[" && substr($plain, strlen($plain) - 1, 1) == "]") {
              // strip $HEX[]
              $nplain = "";
              $plain = Util::hextobin(substr($plain, 5, strlen($plain) - 6));
            }
            fwrite($fx, $plain . "\n");
            $p++;
          }
          fclose($fx);
          $message .= "Written $p words.<br>";
          Util::insertFile("files/" . $wlist);
        }
        else {
          $message .= "Nothing cracked.";
        }
      }
      else {
        $message .= "No such hashlist!";
      }
      $message .= "</div>";
      break;
    case 'hashlistsecret':
      if ($LOGIN->getLevel() < 30) {
        break;
      }
      // switch hashlist secret state
      $hlist = intval($_POST["hashlist"]);
      $secret = intval($_POST["secret"]);
      $res = $FACTORIES::getagentsFactory()->getDB()->exec("UPDATE hashlists SET secret=$secret WHERE id=$hlist");
      if ($secret == 1) {
        $FACTORIES::getagentsFactory()->getDB()->exec("DELETE hashlistusers FROM hashlistusers JOIN agents ON agents.id=hashlistusers.agent WHERE hashlistusers.hashlist=$hlist AND agents.trusted<$secret");
      }
      if (!$res) {
        $message = "<div class='alert alert-danger'>Could not change hashlist secrecy!</div>";
      }
      else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
        die();
      }
      break;
    case 'hashlistrename':
      if ($LOGIN->getLevel() < 20) {
        break;
      }
      // change hashlist name
      $hlist = intval($_POST["hashlist"]);
      $name = $DB->quote(htmlentities($_POST["name"], false, "UTF-8"));
      $kv = $FACTORIES::getagentsFactory()->getDB()->exec("UPDATE hashlists SET name=$name WHERE id=$hlist");
      if (!$kv) {
        $message = "<div class='alert alert-danger'>Could not rename hashlist!</div>";
      }
      else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
        die();
      }
      break;
    case 'hashlistzapp':
      if ($LOGIN->getLevel() < 20) {
        break;
      }
      // pre-crack hashes processor
      $hlist = intval($_POST["hashlist"]);
      $res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT hashlists.*,IFNULL(hashes.salted,0) AS salted FROM hashlists LEFT JOIN (SELECT hashlist,1 AS salted FROM hashes WHERE hashlist=$hlist AND salt!='' LIMIT 1) hashes ON hashlists.format=0 AND hashes.hashlist=hashlists.id WHERE hashlists.id=$hlist");
      $list = $res->fetch();
      if ($list) {
        $format = $list["format"];
        $salted = $list["salted"];
        
        $fs = substr($FACTORIES::getagentsFactory()->getDB()->quote($_POST["separator"]), 1, -1);
        $source = $_POST["source"];
        // switch based on source
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
        $tmpfile = "zaplist_$hlist";
        $message = "<div class='alert alert-neutral'>";
        if (Util::uploadFile($tmpfile, $source, $sourcedata)) {
          $hsize = 0;
          if (file_exists($tmpfile)) {
            $hsize = filesize($tmpfile);
          }
          if ($hsize > 0) {
            $message .= "Opening file $tmpfile ($hsize B)...";
            $hhandle = fopen($tmpfile, "rb");
            $message .= "OK<br>";
            $pocet = 0;
            $chyby = 0;
            $cas_start = time();
            
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
              $message .= "not found - assuming single hash";
            }
            $message .= "<br>";
            
            // create proper superhashlist field if needed
            list($superhash, $hlisty) = Util::superList($hlist, $format);
            
            // now read the lines
            $message .= "Importing pre-cracked hashes from text file...<br>";
            rewind($hhandle);
            $zapy = 0;
            $chyby = 0;
            $skipy = 0;
            $total = 0;
            
            // create temporary hell to handle all that crack/crap
            $FACTORIES::getagentsFactory()->getDB()->query("CREATE TEMPORARY TABLE tmphlcracks (hashlist INT NOT NULL, zaps TINYINT DEFAULT 0, PRIMARY KEY (hashlist));");
            $FACTORIES::getagentsFactory()->getDB()->query("INSERT INTO tmphlcracks (hashlist) SELECT id FROM hashlists WHERE id IN ($hlisty);");
            
            
            $FACTORIES::getagentsFactory()->getDB()->exec("START TRANSACTION");
            $zaptable = Util::getStaticArray($format, 'formattables');
            while (!feof($hhandle)) {
              $dato = stream_get_line($hhandle, 1024, $ls);
              if ($dato == "") {
                continue;
              }
              $total++;
              $kv = "UPDATE $zaptable JOIN hashlists ON $zaptable.hashlist=hashlists.id JOIN tmphlcracks ON tmphlcracks.hashlist=$zaptable.hashlist SET tmphlcracks.zaps=1,$zaptable.chunk=0,$zaptable.plaintext=";
              $datko = explode($fs, $dato);
              $zaphash = "";
              $zapsalt = "";
              $zapplain = "";
              // distribute data into vars
              if ($salted == 1) {
                if (count($datko) >= 3) {
                  $zaphash = $datko[0];
                  $zapsalt = $datko[1];
                  $zapplain = $datko[2];
                  if (count($datko) > 3) {
                    $ans = $DB->query("SELECT * FROM $zaptable WHERE hash=" . $DB->quote($zaphash) . " AND hashlist=$hlist");
                    $data = $ans->fetch();
                    if ($data) {
                      $complete = $data['hash'] . $fs . $data['salt'] . $fs;
                      $zapplain = str_replace($complete, "", $dato);
                      $zaphash = $data['hash'];
                      $zapsalt = $data['salt'];
                    }
                  }
                }
                else {
                  $message .= "Bad line: $dato<br>";
                  $chyby++;
                  continue;
                }
              }
              else {
                if (count($datko) >= 2) {
                  $zaphash = $datko[0];
                  $zapplain = $datko[1];
                  for ($i = 2; $i < count($datko); $i++) {
                    $zapplain .= $fs . $datko[$i];
                  }
                }
                else {
                  $message .= "Bad line: $dato<br>";
                  $chyby++;
                  continue;
                }
              }
              //overwritting condition
              if (isset($_POST["overwrite"]) && $_POST["overwrite"] == "1") {
                $over = true;
              }
              else {
                $over = false;
              }
              $kv2 = ",$zaptable.time=" . time() . ",hashlists.cracked=hashlists.cracked+" . ($over ? "IF($zaptable.plaintext IS NULL,1,0)" : "1") . " WHERE $zaptable.hashlist IN ($hlisty)" . ($over ? "" : " AND $zaptable.plaintext IS NULL");
              switch ($format) {
                case 0:
                  $kv2 .= " AND $zaptable.hash=" . $FACTORIES::getagentsFactory()->getDB()->quote($zaphash);
                  if ($zapsalt != "") {
                    $kv2 .= " AND $zaptable.salt=" . $FACTORIES::getagentsFactory()->getDB()->quote($zapsalt);
                  }
                  break;
                case 1:
                  $kv2 .= " AND $zaptable.essid=" . $FACTORIES::getagentsFactory()->getDB()->quote($zaphash);
                  break;
              }
              if ($zapplain != "") {
                $vysledek = $FACTORIES::getagentsFactory()->getDB()->query($kv . $FACTORIES::getagentsFactory()->getDB()->quote($zapplain) . $kv2);
                if (!$vysledek) {
                  $vysledek = $FACTORIES::getagentsFactory()->getDB()->query($kv . "\$HEX[" . Util::bintohex($zapplain) . "]" . $kv2);
                }
                if ($vysledek) {
                  $aff = $vysledek->rowCount();
                  if ($aff == 0) {
                    $skipy++;
                  }
                  else {
                    $zapy++;
                  }
                }
                else {
                  $message .= "Problems pre-cracking hash " . $zaphash . " ($kv--$kv2)<br>";
                  $chyby++;
                }
              }
              else {
                $skipy++;
              }
              if ($total % 10000 == 0) {
                $message .= "Read $total lines...<br>";
              }
            }
            $FACTORIES::getagentsFactory()->getDB()->exec("COMMIT");
            $cas_stop = time();
            
            $FACTORIES::getagentsFactory()->getDB()->exec("INSERT IGNORE INTO zapqueue (hashlist,agent,time,chunk) SELECT hashlistusers.hashlist,hashlistusers.agent," . time() . ",0 FROM hashlistusers JOIN tmphlcracks ON hashlistusers.hashlist=tmphlcracks.hashlist AND tmphlcracks.zaps=1");
            $FACTORIES::getagentsFactory()->getDB()->exec("DROP TABLE tmphlcracks");
            
            // evaluate, what have we accomplished
            if ($superhash) {
              // recount cracked
              $FACTORIES::getagentsFactory()->getDB()->exec("SET @ctotal=(SELECT SUM(hashlists.cracked) FROM superhashlists JOIN hashlists ON superhashlists.hashlist=hashlists.id WHERE superhashlists.id=$hlist)");
              $FACTORIES::getagentsFactory()->getDB()->exec("UPDATE hashlists SET cracked=@ctotal WHERE id=$hlist AND format=3");
            }
            $message .= "Pre-cracking completed ($zapy hashes pre-cracked, $skipy skipped for duplicity or empty plaintext, $chyby SQL errors, took " . ($cas_stop - $cas_start) . " sec)";
            fclose($hhandle);
            if (file_exists($tmpfile)) {
              unlink($tmpfile);
            }
            if ($superhash) {
              header("Location: superhashlists.php");
              die();
            }
            else {
              header("Location: hashlists.php");
              die();
            }
          }
          else {
            $message .= "Pre-cracked file is empty!";
          }
          if (file_exists($tmpfile)) {
            unlink($tmpfile);
          }
        }
        $message .= "</div>";
      }
      else {
        $message = "<div class='alert alert-danger'>Invalid hashlist!</div>";
      }
      break;
    case 'export':
      if ($LOGIN->getLevel() < 20) {
        break;
      }
      // export cracked hashes to a file
      $hlist = intval($_POST["hashlist"]);
      $res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT format FROM hashlists WHERE id=$hlist");
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
  }
}

if(isset($_GET['new'])){
  //new hashlist
  $MENU->setActive("lists_new");
  $OBJECTS['impfiles'] = Util::scanImportDirectory();
  $OBJECTS['hashtypes'] = $FACTORIES::getHashTypeFactory()->filter(array());
  $TEMPLATE = new Template("hashlists/new");
}
else if (isset($_GET['id'])) {
  //show hashlist detail page
  $jF = new JoinFilter($FACTORIES::getHashTypeFactory(), "hashtypeId", "hashtypeId");
  $qF = new QueryFilter("hashlistId", $_GET['id'], "=");
  $joined = $FACTORIES::getHashlistFactory()->filter(array('join' => array($jF), 'filter' => array($qF)));
  if(sizeof($joined['Hashlist']) == 0){
    UI::printError("ERROR", "Hashlist not found!");
  }
  $list = new DataSet(array('hashlist' => $joined['Hashlist'][0], 'hashtype' => $joined['HashType'][0]));
  $OBJECTS['list'] = $list;
  
  //check if the list is a superhashlist
  if ($list->getVal('hashlist')->getFormat() == 3) {
    $jF = new JoinFilter($FACTORIES::getSuperHashlistHashlistFactory(), "hashlistId", "hashlistId");
    $qF = new QueryFilter("superhashlistId", $list->getVal('hashlist')->getId(), "=");
    $joined = $FACTORIES::getHashlistFactory()->filter(array('join' => array($jF), 'filter' => array($qF)));
    $sublists = array();
    for($x=0;$x<sizeof($joined['Hashlist']);$x++){
      $sublists[] = new DataSet(array('hashlist' => $joined['Hashlist'][$x], 'superhashlist' => $joined['SuperHashlist'][$x]));
    }
    $OBJECTS['sublists'] = $sublists;
  }
  
  //load tasks assigned to hashlist
  $qF = new QueryFilter("hashlistId", $list->getVal('hashlist')->getId(), "=");
  $tasks = $FACTORIES::getTaskFactory()->filter(array('filter' => array($qF)));
  $hashlistTasks = array();
  foreach($tasks as $task){
    $isActive = false;
    $qF = new QueryFilter("taskId", $task->getId(), "=");
    $chunks = $FACTORIES::getChunkFactory()->filter(array('filter' => array($qF)));
    $sum = array('dispatched' => $task->getProgress(), 'searched' => 0, 'cracked' => 0);
    foreach($chunks as $chunk){
      $sum['searched'] += $chunk->getProgress();
      $sum['cracked'] += $chunk->getCracked();
      if(time() - $CONFIG->getVal('chunktimeout') < max($chunk->getDispatchTime(), $chunk->getSolveTime())){
        $isActive = true;
      }
    }
    $set = new DataSet(array('task' => $task, 'isActive' => $isActive, 'searched' => $sum['searched'], 'dispatched' => $sum['dispatched'], 'cracked' =>$sum['cracked']));
    $hashlistTasks[] = $set;
  }
  $OBJECTS['tasks'] = $hashlistTasks;
  
  //load list of available preconfigured tasks
  $qF = new QueryFilter("hashlistId", "0", "=");
  $preTasks = $FACTORIES::getTaskFactory()->filter(array('filter' => array($qF)));
  $OBJECTS['preTasks'] = $preTasks;
  $TEMPLATE = new Template("hashlists/detail");
}
else {
  //load all hashlists
  $jF = new JoinFilter($FACTORIES::getHashTypeFactory(), "hashtypeId", "hashtypeId");
  $joinedHashlists = $FACTORIES::getHashlistFactory()->filter(array('join' => array($jF)));
  $hashlists = array();
  for($x=0;$x<sizeof($joinedHashlists['Hashlist']);$x++){
    $hashlists[] = new DataSet(array('hashlist' => $joinedHashlists['Hashlist'][$x], 'hashtype' => $joinedHashlists['HashType'][$x]));
  }
  $OBJECTS['hashlists'] = $hashlists;
  $OBJECTS['numHashlists'] = sizeof($hashlists);
}

echo $TEMPLATE->render($OBJECTS);




