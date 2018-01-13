<?php

use DBA\FilePretask;
use DBA\Pretask;
use DBA\QueryFilter;
use DBA\SupertaskPretask;

class PretaskHandler implements Handler {
  public function __construct($fileId = null) {
    //we need nothing to load
  }
  
  public function handle($action) {
    /** @var Login $LOGIN */
    global $LOGIN;
    
    switch ($action) {
      case DPretaskAction::DELETE_PRETASK:
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->delete($_POST['pretaskId']);
        break;
      case DPretaskAction::RENAME_PRETASK:
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->rename($_POST['name'], $_POST['pretaskId']);
        break;
      case DPretaskAction::SET_TIME:
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->setChunkTime($_POST['chunktime'], $_POST['pretaskId']);
        break;
      case DPretaskAction::SET_COLOR:
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->setColor($_POST['color'], $_POST['pretaskId']);
        break;
      case DPretaskAction::SET_PRIORITY:
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->setPriority($_POST['priority'], $_POST['pretaskId']);
        break;
      case DPretaskAction::SET_CPU_TASK:
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->setCpuTask($_POST['isCpu'], $_POST['pretaskId']);
        break;
      case DPretaskAction::SET_SMALL_TASK:
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->setSmallTask($_POST['isSmall'], $_POST['pretaskId']);
        break;
      case DPretaskAction::CREATE_TASK:
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->createPretask($_POST['name'], $_POST['cmdline'], $_POST['chunk'], $_POST['status'], $_POST['color'], $_POST['cpuOnly'], $_POST['isSmall'], $_POST['benchType']);
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
  
  private function createPretask($name, $cmdLine, $chunkTime, $statusTimer, $color, $cpuOnly, $isSmall, $benchmarkType) {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;
    
    if (strlen($name) == 0) {
      UI::addMessage(UI::ERROR, "Name cannot be empty!");
      return;
    }
    else if (strpos($cmdLine, $CONFIG->getVal(DConfig::HASHLIST_ALIAS)) === false) {
      UI::addMessage(UI::ERROR, "The attack command does not contain the hashlist alias!");
      return;
    }
    else if (Util::containsBlacklistedChars($cmdLine)) {
      UI::addMessage(UI::ERROR, "The command must contain no blacklisted characters!");
      return;
    }
    $chunkTime = intval($chunkTime);
    $statusTimer = intval($statusTimer);
    if (strlen($color) > 0 && preg_match("/[0-9A-Fa-f]{6}/", $color) == 0) {
      $color = "";
    }
    else if ($cpuOnly < 0 || $cpuOnly > 1) {
      $cpuOnly = 0;
    }
    else if ($isSmall < 0 || $isSmall > 1) {
      $isSmall = 0;
    }
    else if ($benchmarkType < 0 || $benchmarkType > 1) {
      $benchmarkType = 0;
    }
    else if ($chunkTime <= 0) {
      $chunkTime = $CONFIG->getVal(DConfig::CHUNK_DURATION);
    }
    else if ($statusTimer <= 0) {
      $statusTimer = $CONFIG->getVal(DConfig::STATUS_TIMER);
    }
    $pretask = new Pretask(0, htmlentities($name, ENT_QUOTES, "UTF-8"), $cmdLine, $chunkTime, $statusTimer, $color, $isSmall, $cpuOnly, $benchmarkType, 0, 0);
    $pretask = $FACTORIES::getPretaskFactory()->save($pretask);
    
    // handle files
    if (isset($_POST["adfile"])) {
      foreach ($_POST["adfile"] as $fileId) {
        $file = $FACTORIES::getFileFactory()->get($fileId);
        if ($file !== null) {
          $filePretask = new FilePretask(0, $file->getId(), $pretask->getId());
          $FACTORIES::getFilePretaskFactory()->save($filePretask);
        }
      }
    }
    
    header("Location: pretasks.php");
    die();
  }
  
  private function delete($pretaskId) {
    global $FACTORIES;
    
    $pretask = $FACTORIES::getPretaskFactory()->get($pretaskId);
    if ($pretaskId === null) {
      UI::addMessage(UI::ERROR, "Invalid pretask!");
      return;
    }
    
    // delete connections to supertasks
    $qF = new QueryFilter(SupertaskPretask::PRETASK_ID, $pretask->getId(), "=");
    $FACTORIES::getSupertaskPretaskFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    
    // delete connections to files
    $qF = new QueryFilter(FilePretask::PRETASK_ID, $pretask->getId(), "=");
    $FACTORIES::getFilePretaskFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    
    $FACTORIES::getPretaskFactory()->delete($pretask);
    header("Location: pretasks.php");
    die();
  }
  
  private function rename($name, $pretaskId) {
    global $FACTORIES;
    
    $pretask = $FACTORIES::getPretaskFactory()->get($pretaskId);
    if ($pretaskId === null) {
      UI::addMessage(UI::ERROR, "Invalid pretask!");
      return;
    }
    else if (strlen($name) == 0) {
      UI::addMessage(UI::ERROR, "Name cannot be empty!");
      return;
    }
    
    $pretask->setTaskName(htmlentities($name, ENT_QUOTES, "UTF-8"));
    $FACTORIES::getPretaskFactory()->update($pretask);
  }
  
  private function setChunkTime($chunkTime, $pretaskId) {
    global $FACTORIES;
    
    $pretask = $FACTORIES::getPretaskFactory()->get($pretaskId);
    if ($pretaskId === null) {
      UI::addMessage(UI::ERROR, "Invalid pretask!");
      return;
    }
    
    $chunkTime = intval($chunkTime);
    if ($chunkTime <= 0) {
      UI::addMessage(UI::ERROR, "Invalid chunk time!");
      return;
    }
    $pretask->setChunkTime($chunkTime);
    $FACTORIES::getPretaskFactory()->update($pretask);
  }
  
  private function setColor($color, $pretaskId) {
    global $FACTORIES;
    
    $pretask = $FACTORIES::getPretaskFactory()->get($pretaskId);
    if ($pretaskId === null) {
      UI::addMessage(UI::ERROR, "Invalid pretask!");
      return;
    }
    else if (strlen($color) > 0 && preg_match("/[0-9A-Fa-f]{6}/", $color) == 0) {
      UI::addMessage(UI::WARN, "Invalid color was entered!");
      $color = "";
    }
    $pretask->setColor($color);
    $FACTORIES::getPretaskFactory()->update($pretask);
  }
  
  private function setPriority($priority, $pretaskId) {
    global $FACTORIES;
    
    $pretask = $FACTORIES::getPretaskFactory()->get($pretaskId);
    if ($pretaskId === null) {
      UI::addMessage(UI::ERROR, "Invalid pretask!");
      return;
    }
    $priority = intval($priority);
    if ($priority < 0) {
      $priority = 0;
    }
    $pretask->setPriority($priority);
    $FACTORIES::getPretaskFactory()->update($pretask);
    if (isset($_GET['super'])) {
      header("Location: supertasks.php");
      die();
    }
  }
  
  private function setSmallTask($isSmall, $pretaskId) {
    global $FACTORIES;
    
    $pretask = $FACTORIES::getPretaskFactory()->get($pretaskId);
    if ($pretaskId === null) {
      UI::addMessage(UI::ERROR, "Invalid pretask!");
      return;
    }
    $isSmall = intval($isSmall);
    if ($isSmall < 0 || $isSmall > 1) {
      $isSmall = 0;
    }
    $pretask->setIsSmall($isSmall);
    $FACTORIES::getPretaskFactory()->update($pretask);
  }
  
  private function setCpuTask($isCpu, $pretaskId) {
    global $FACTORIES;
    
    $pretask = $FACTORIES::getPretaskFactory()->get($pretaskId);
    if ($pretaskId === null) {
      UI::addMessage(UI::ERROR, "Invalid pretask!");
      return;
    }
    $isCpu = intval($isCpu);
    if ($isCpu < 0 || $isCpu > 1) {
      $isCpu = 0;
    }
    $pretask->setIsCpuTask($isCpu);
    $FACTORIES::getPretaskFactory()->update($pretask);
  }
}