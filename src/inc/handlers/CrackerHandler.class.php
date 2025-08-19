<?php

class CrackerHandler implements Handler {
  public function __construct($hashcatId = null) {
    //nothing
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DCrackerBinaryAction::DELETE_BINARY_TYPE:
          AccessControl::getInstance()->checkPermission(DCrackerBinaryAction::DELETE_BINARY_TYPE_PERM);
          CrackerUtils::deleteBinaryType($_POST['binaryTypeId']);
          header("Location: crackers.php");
          die();
        case DCrackerBinaryAction::DELETE_BINARY:
          AccessControl::getInstance()->checkPermission(DCrackerBinaryAction::DELETE_BINARY_PERM);
          CrackerUtils::deleteBinary($_POST['binaryId']);
          break;
        case DCrackerBinaryAction::CREATE_BINARY_TYPE:
          AccessControl::getInstance()->checkPermission(DCrackerBinaryAction::CREATE_BINARY_TYPE_PERM);
          CrackerUtils::createBinaryType($_POST['name']);
          header("Location: crackers.php");
          die();
        case DCrackerBinaryAction::CREATE_BINARY:
          AccessControl::getInstance()->checkPermission(DCrackerBinaryAction::CREATE_BINARY_PERM);
          $binary = CrackerUtils::createBinary($_POST['version'], $_POST['name'], $_POST['url'], $_POST['binaryTypeId']);
          header("Location: crackers.php?id=" . $binary->getCrackerBinaryTypeId());
          die();
        case DCrackerBinaryAction::EDIT_BINARY:
          AccessControl::getInstance()->checkPermission(DCrackerBinaryAction::EDIT_BINARY_PERM);
          $binaryType = CrackerUtils::updateBinary($_POST['version'], $_POST['name'], $_POST['url'], $_POST['binaryId']);
          header("Location: crackers.php?id=" . $binaryType->getId());
          die();
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch (Exception $e) {
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }
}