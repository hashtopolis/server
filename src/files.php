<?php

require_once(dirname(__FILE__) . "/inc/load.php");

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']));
  die();
}
else if ($LOGIN->getLevel() < 20) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("files/index");
$MENU->setActive("files");
$message = "";

//catch actions here...
if (isset($_POST['action'])) {
  switch ($_POST['action']) {
    //TODO: implement file handler
    case 'filedelete':
      if ($LOGIN->getLevel() < 30) {
        break;
      }
      // delete global file
      $fid = intval($_POST["file"]);
      $FACTORIES::getagentsFactory()->getDB()->exec("START TRANSACTION");
      $res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT * FROM files WHERE id=$fid");
      $file = $res->fetch();
      if ($file) {
        $fname = $file["filename"];
        $res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT 1 FROM taskfiles WHERE file=$fid");
        if ($res->rowCount() > 0) {
          // file is used
          $message = "<div class='alert alert-danger'>File is used in a task.</div>";
        }
        else {
          $ans2 = true;
          $ans1 = $FACTORIES::getagentsFactory()->getDB()->exec("DELETE FROM files WHERE id=$fid");
          if ($ans1 && file_exists("files/" . $fname)) {
            $ans2 = unlink("files/" . $fname);
          }
          if ($ans1 && $ans2) {
            $FACTORIES::getagentsFactory()->getDB()->exec("COMMIT");
            header("Location: files.php");
            die();
          }
          else {
            $FACTORIES::getagentsFactory()->getDB()->exec("ROLLBACK");
            $message = "<div class='alert alert-danger'>Could not delete file!</div>";
          }
        }
      }
      else {
        $message = "<div class='alert alert-danger'>Such file is not defined.</div>";
      }
      break;
    case 'filesecret':
      if ($LOGIN->getLevel() < 30) {
        break;
      }
      // switch global file secret state
      $fid = intval($_POST["file"]);
      $secret = intval($_POST["secret"]);
      $res = $FACTORIES::getagentsFactory()->getDB()->exec("UPDATE files SET secret=$secret WHERE id=$fid");
      if (!$res) {
        $message = "<div class='alert alert-danger'>Could not change global file secrecy!</div>";
      }
      else {
        header("Location: files.php");
        die();
      }
      break;
    case 'addfile':
      $pocetup = 0;
      $source = $_POST["source"];
      if (!file_exists("files")) {
        $message .= "<div class='alert alert-success'>First imported file, creating files subdir...";
        if (mkdir("files")) {
          $message .= "OK<br>";
        }
        $message .= "</div>";
      }
      $message .= "<div class='alert alert-neutral'>";
      
      $allok = true;
      switch ($source) {
        case "upload":
          // from http upload
          $soubory = $_FILES["upfile"];
          $pocet = count($_FILES["upfile"]["name"]);
          for ($i = 0; $i < $pocet; $i++) {
            // copy all uploaded attached files to proper directory
            $realname = htmlentities(basename($soubory["name"][$i]), false, "UTF-8");
            if ($realname == "") {
              continue;
            }
            
            $nsoubor = array();
            foreach ($soubory as $klic => $soubor) {
              $nsoubor[$klic] = $soubor[$i];
            }
            $tmpfile = "files/" . $realname;
            $resp = Util::uploadFile($tmpfile, $source, $nsoubor);
            $message .= $resp[1];
            if ($resp[0]) {
              $resp = Util::insertFile($tmpfile);
              $message .= $resp[1];
              if ($resp[0]) {
                $pocetup++;
              }
              else {
                $allok = false;
              }
            }
            else {
              $allok = false;
            }
          }
          break;
        
        case "import":
          // from import dir
          $soubory = $_POST["imfile"];
          $pocet = count($soubory);
          foreach ($soubory as $soubor) {
            if ($soubor[0] == '.') {
              continue;
            }
            // copy all uploaded attached files to proper directory
            $realname = htmlentities(basename($soubor), false, "UTF-8");
            $tmpfile = "files/" . $realname;
            $resp = Util::uploadFile($tmpfile, $source, $realname);
            $message .= $resp[1];
            if ($resp[0]) {
              $resp = Util::insertFile($tmpfile);
              $message .= $resp[1];
              if ($resp[0]) {
                $pocetup++;
              }
              else {
                $allok = false;
              }
            }
            else {
              $allok = false;
            }
          }
          break;
        
        case "url":
          // from url
          $realname = htmlentities(basename($_POST["url"]), false, "UTF-8");
          $tmpfile = "files/" . $realname;
          $resp = Util::uploadFile($tmpfile, $source, $_POST["url"]);
          $message .= $resp[1];
          if ($resp[0]) {
            $resp = Util::insertFile($tmpfile);
            $message .= $resp[1];
            if ($resp[0]) {
              $pocetup++;
            }
            else {
              $allok = false;
            }
          }
          else {
            $allok = false;
          }
          break;
      }
      if ($allok) {
        header("Location: files.php");
        die();
      }
      $message .= "</div>";
      break;
  }
}

$view = "dict";
if(isset($_GET['view']) && in_array($_GET['view'], array('dict', 'rule'))){
  $view = $_GET['view'];
}


$qF = new QueryFilter("fileType", array_search($view, array('dict', 'rule')), "=");
$oF = new OrderFilter("filename", "ASC");
$OBJECTS['fileType'] = ($view == "dict")?"Wordlists":"Rules";
$OBJECTS['view'] = $view;
$OBJECTS['files'] = $FACTORIES::getFileFactory()->filter(array('filter' => $qF, 'order' => $oF));;
$OBJECTS['impfiles'] = Util::scanImportDirectory();
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




