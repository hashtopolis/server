<?php


use DBA\ContainFilter;
use DBA\CrackerBinary;
use DBA\CrackerBinaryType;
use DBA\QueryFilter;
use DBA\Task;

class CrackerHandler implements Handler {
  public function __construct($hashcatId = null) {
    //nothing
  }
  
  public function handle($action) {
    /** @var $LOGIN Login */
    global $LOGIN;
    
    switch ($action) {
      case DCrackerBinaryAction::DELETE_BINARY_TYPE:
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->deleteBinaryType($_POST['binaryTypeId']);
        break;
      case DCrackerBinaryAction::DELETE_BINARY:
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->deleteBinary($_POST['binaryId']);
        break;
      case DCrackerBinaryAction::CREATE_BINARY_TYPE:
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->createBinaryType($_POST['name']);
        break;
      case DCrackerBinaryAction::CREATE_BINARY:
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->createBinary($_POST['version'], $_POST['name'], $_POST['url'], $_POST['binaryTypeId'], $_POST['platform']);
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
  
  private function deleteBinaryType($binaryTypeId) {
    global $FACTORIES;
    
    $binaryType = $FACTORIES::getCrackerBinaryTypeFactory()->get($binaryTypeId);
    if ($binaryType === null) {
      UI::addMessage(UI::ERROR, "Invalid binary type!");
      return;
    }
    
    $qF = new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $binaryType->getId(), "=");
    $binaries = $FACTORIES::getCrackerBinaryFactory()->filter(array($FACTORIES::FILTER => $qF));
    $versionIds = Util::arrayOfIds($binaries);
    
    $qF = new ContainFilter(Task::CRACKER_BINARY_ID, $versionIds);
    $check = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF));
    if (sizeof($check) > 0) {
      UI::addMessage(UI::ERROR, "There are tasks which use binaries of this cracker!");
      return;
    }
    
    // delete
    $FACTORIES::getCrackerBinaryFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $FACTORIES::getCrackerBinaryTypeFactory()->delete($binaryType);
    header("Location: crackers.php");
    die();
  }
  
  private function deleteBinary($binaryId) {
    global $FACTORIES;
    
    $binary = $FACTORIES::getCrackerBinaryFactory()->get($binaryId);
    if ($binary === null) {
      UI::addMessage(UI::ERROR, "Invalid version!");
      return;
    }
    $qF = new QueryFilter(Task::CRACKER_BINARY_ID, $binary->getId(), "=");
    $check = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF));
    if (sizeof($check) > 0) {
      UI::addMessage(UI::ERROR, "There are tasks which use this binary!");
      return;
    }
    $FACTORIES::getCrackerBinaryFactory()->delete($binary);
  }
  
  private function createBinary($version, $name, $url, $binaryTypeId, $platform) {
    global $FACTORIES;
    
    $binaryType = $FACTORIES::getCrackerBinaryTypeFactory()->get($binaryTypeId);
    if ($binaryType === null) {
      UI::addMessage(UI::ERROR, "Invalid cracker binary type!");
      return;
    }
    else if (strlen($version) == 0 || strlen($name) == 0 || strlen($url) == 0) {
      UI::addMessage(UI::ERROR, "Please provide all information!");
      return;
    }
    $binary = new CrackerBinary(0, $binaryType->getId(), $version, $platform, $url, $name);
    $FACTORIES::getCrackerBinaryFactory()->save($binary);
    UI::addMessage(UI::SUCCESS, "Version was created successfully!");
    header("Location: crackers.php?id=" . $binaryType->getId());
    die();
  }
  
  private function createBinaryType($typeName) {
    global $FACTORIES;
    
    $qF = new QueryFilter(CrackerBinaryType::TYPE_NAME, $typeName, "=");
    $check = $FACTORIES::getCrackerBinaryTypeFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if ($check !== null) {
      UI::addMessage(UI::ERROR, "This binary type already exists!");
      return;
    }
    $binaryType = new CrackerBinaryType(0, $typeName, 1);
    $FACTORIES::getCrackerBinaryTypeFactory()->save($binaryType);
    header("Location: crackers.php");
    die();
  }
}