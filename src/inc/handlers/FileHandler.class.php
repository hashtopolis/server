<?php
use DBA\QueryFilter;
use DBA\TaskFile;

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 18.11.16
 * Time: 20:21
 */
class FileHandler implements Handler {
  public function __construct($fileId = null) {
    //we need nothing to load
  }
  
  public function handle($action) {
    /** @var Login $LOGIN */
    global $LOGIN;
    
    switch ($action) {
      case 'filedelete':
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->delete();
        break;
      case 'filesecret':
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->switchSecret();
        break;
      case 'addfile':
        $this->add();
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
  
  private function add() {
    $fileCount = 0;
    $source = $_POST["source"];
    if (!file_exists(dirname(__FILE__) . "/../../files")) {
      mkdir(dirname(__FILE__) . "/../../files");
    }
    
    $allok = true;
    switch ($source) {
      case "upload":
        // from http upload
        $uploaded = $_FILES["upfile"];
        $numFiles = count($_FILES["upfile"]["name"]);
        for ($i = 0; $i < $numFiles; $i++) {
          // copy all uploaded attached files to proper directory
          $realname = htmlentities(basename($uploaded["name"][$i]), false, "UTF-8");
          if ($realname == "") {
            continue;
          }
          
          $toMove = array();
          foreach ($uploaded as $key => $upload) {
            $toMove[$key] = $upload[$i];
          }
          $tmpfile = dirname(__FILE__) . "/../../files/" . $realname;
          $resp = Util::uploadFile($tmpfile, $source, $toMove);
          if ($resp[0]) {
            $resp = Util::insertFile($tmpfile, $realname, @$_GET['view']);
            if ($resp) {
              $fileCount++;
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
        $imports = $_POST["imfile"];
        if (!$imports) {
          break;
        }
        foreach ($imports as $import) {
          if ($import[0] == '.') {
            continue;
          }
          // copy all uploaded attached files to proper directory
          $realname = htmlentities(basename($import), false, "UTF-8");
          $tmpfile = dirname(__FILE__) . "/../../files/" . $realname;
          $resp = Util::uploadFile($tmpfile, $source, $realname);
          if ($resp[0]) {
            $resp = Util::insertFile($tmpfile, $realname, @$_GET['view']);
            if ($resp) {
              $fileCount++;
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
        $tmpfile = dirname(__FILE__) . "/../../files/" . $realname;
        $resp = Util::uploadFile($tmpfile, $source, $_POST["url"]);
        if ($resp[0]) {
          $resp = Util::insertFile($tmpfile, $realname, @$_GET['view']);
          if ($resp) {
            $fileCount++;
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
      UI::addMessage(UI::SUCCESS, "Successfully added $fileCount files!");
    }
    else {
      UI::addMessage(UI::ERROR, "Something went wrong when adding files!");
    }
  }
  
  private function switchSecret() {
    global $FACTORIES;
    
    // switch global file secret state
    $file = $FACTORIES::getFileFactory()->get($_POST['file']);
    $secret = intval($_POST["secret"]);
    $file->setSecret($secret);
    $FACTORIES::getFileFactory()->update($file);
  }
  
  private function delete() {
    global $FACTORIES;
    
    $file = $FACTORIES::getFileFactory()->get($_POST['file']);
    if ($file == null) {
      UI::printError("ERROR", "File does not exist!");
    }
    $qF = new QueryFilter(TaskFile::FILE_ID, $file->getId(), "=");
    $tasks = $FACTORIES::getTaskFileFactory()->filter(array($FACTORIES::FILTER => $qF));
    if (sizeof($tasks) > 0) {
      UI::addMessage(UI::ERROR, "This file is currently used in a task!");
    }
    else {
      $FACTORIES::getFileFactory()->delete($file);
      unlink(dirname(__FILE__) . "/../../files/" . $file->getFilename());
      UI::addMessage(UI::SUCCESS, "Successfully deleted file!");
    }
  }
}