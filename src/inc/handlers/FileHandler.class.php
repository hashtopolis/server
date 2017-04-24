<?php
use DBA\File;
use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\Task;
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
      case 'edit':
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->saveChanges();
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
  
  private function saveChanges() {
    global $FACTORIES;
    
    $file = $FACTORIES::getFileFactory()->get($_POST['fileId']);
    if ($file == null) {
      UI::addMessage(UI::ERROR, "Invalid file ID!");
      return;
    }
    $newName = str_replace(" ", "_", htmlentities($_POST['filename'], false, "UTF-8"));
    if (strlen($newName) == 0) {
      UI::addMessage(UI::ERROR, "Filename cannot be empty!");
      return;
    }
    $qF = new QueryFilter(File::FILENAME, $newName, "=");
    $files = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => $qF));
    if (sizeof($files) > 0) {
      UI::addMessage(UI::ERROR, "This filename is already used!");
      return;
    }
    
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
    
    //check where the file is used and replace the filename in all the tasks
    $qF = new QueryFilter(TaskFile::FILE_ID, $file->getId(), "=", $FACTORIES::getTaskFileFactory());
    $jF = new JoinFilter($FACTORIES::getTaskFileFactory(), Task::TASK_ID, TaskFile::TASK_ID);
    $joined = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    foreach ($joined[$FACTORIES::getTaskFactory()->getModelName()] as $task) {
      /** @var $task Task */
      $task->setAttackCmd(str_replace($file->getFilename(), $newName, $task->getAttackCmd()));
      $FACTORIES::getTaskFactory()->update($task);
    }
    
    $success = rename(dirname(__FILE__) . "/../../files/" . $file->getFilename(), dirname(__FILE__) . "/../../files/" . $newName);
    if (!$success) {
      UI::addMessage(UI::ERROR, "Failed to rename file!");
      return;
    }
    $file->setFilename($newName);
    $FACTORIES::getFileFactory()->update($file);
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
  }
  
  private function add() {
    $fileCount = 0;
    $source = $_POST["source"];
    if (!file_exists(dirname(__FILE__) . "/../../files")) {
      mkdir(dirname(__FILE__) . "/../../files");
    }
    
    switch ($source) {
      case "upload":
        // from http upload
        $uploaded = $_FILES["upfile"];
        $numFiles = count($_FILES["upfile"]["name"]);
        for ($i = 0; $i < $numFiles; $i++) {
          // copy all uploaded attached files to proper directory
          $realname = str_replace(" ", "_", htmlentities(basename($uploaded["name"][$i]), false, "UTF-8"));
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
              UI::addMessage(UI::ERROR, "Failed to insert file $realname into DB!");
            }
          }
          else {
            UI::addMessage(UI::ERROR, "Failed to copy file $realname to the right place! " . $resp[1]);
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
          $realname = str_replace(" ", "_", htmlentities(basename($import), false, "UTF-8"));
          $tmpfile = dirname(__FILE__) . "/../../files/" . $realname;
          $resp = Util::uploadFile($tmpfile, $source, $realname);
          if ($resp[0]) {
            $resp = Util::insertFile($tmpfile, $realname, @$_GET['view']);
            if ($resp) {
              $fileCount++;
            }
            else {
              UI::addMessage(UI::ERROR, "Failed to insert file $realname into DB!");
            }
          }
          else {
            UI::addMessage(UI::ERROR, "Failed to copy file $realname to the right place! " . $resp[1]);
          }
        }
        break;
      
      case "url":
        // from url
        $realname = str_replace(" ", "_", htmlentities(basename($_POST["url"]), false, "UTF-8"));
        $tmpfile = dirname(__FILE__) . "/../../files/" . $realname;
        $resp = Util::uploadFile($tmpfile, $source, $_POST["url"]);
        if ($resp[0]) {
          $resp = Util::insertFile($tmpfile, $realname, @$_GET['view']);
          if ($resp) {
            $fileCount++;
          }
          else {
            UI::addMessage(UI::ERROR, "Failed to insert file $realname into DB!");
          }
        }
        else {
          UI::addMessage(UI::ERROR, "Failed to copy file $realname to the right place! " . $resp[1]);
        }
        break;
    }
    UI::addMessage(UI::SUCCESS, "Successfully added $fileCount files!");
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