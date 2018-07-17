<?php

class CrackerHandler implements Handler {
  public function __construct($hashcatId = null) {
    //nothing
  }

  public function handle($action) {
    global $ACCESS_CONTROL;

    try{
      switch ($action) {
        case DCrackerBinaryAction::DELETE_BINARY_TYPE:
          $ACCESS_CONTROL->checkPermission(DCrackerBinaryAction::DELETE_BINARY_TYPE_PERM);
          CrackerUtils::deleteBinaryType($_POST['binaryTypeId']);
          header("Location: crackers.php");
          die();
        case DCrackerBinaryAction::DELETE_BINARY:
          $ACCESS_CONTROL->checkPermission(DCrackerBinaryAction::DELETE_BINARY_PERM);
          CrackerUtils::deleteBinary($_POST['binaryId']);
          break;
        case DCrackerBinaryAction::CREATE_BINARY_TYPE:
          $ACCESS_CONTROL->checkPermission(DCrackerBinaryAction::CREATE_BINARY_TYPE_PERM);
          $this->createBinaryType($_POST['name']);
          header("Location: crackers.php");
          die();
        case DCrackerBinaryAction::CREATE_BINARY:
          $ACCESS_CONTROL->checkPermission(DCrackerBinaryAction::CREATE_BINARY_PERM);
          $binaryType = CrackerUtils::createBinary($_POST['version'], $_POST['name'], $_POST['url'], $_POST['binaryTypeId']);
          header("Location: crackers.php?id=" . $binaryType->getId());
          die();
        case DCrackerBinaryAction::EDIT_BINARY:
          $ACCESS_CONTROL->checkPermission(DCrackerBinaryAction::EDIT_BINARY_PERM);
          $binaryType = CrackerUtils::updateBinary($_POST['version'], $_POST['name'], $_POST['url'], $_POST['binaryId']);
          header("Location: crackers.php?id=" . $binaryType->getId());
          die();
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch(HTException $e){
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }
}