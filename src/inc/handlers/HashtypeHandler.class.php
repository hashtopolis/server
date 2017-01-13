<?php
use DBA\HashType;
use DBA\QueryFilter;

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 18.11.16
 * Time: 20:21
 */
class HashtypeHandler implements Handler {
  public function __construct($hashtypeId = null) {
    //we need nothing to load
  }
  
  public function handle($action) {
    switch ($action) {
      case 'delete':
        $this->delete();
        break;
      case 'add':
        $this->add();
        break;
      default:
        UI::addMessage("danger", "Invalid action!");
        break;
    }
  }
  
  private function add() {
    global $FACTORIES;
    
    $hashtype = $FACTORIES::getHashTypeFactory()->get($_POST['id']);
    if ($hashtype != null) {
      UI::addMessage("danger", "This hash number is already used!");
      return;
    }
    $desc = htmlentities($_POST['description']);
    if (strlen($desc) == 0 || $_POST['id'] < 0) {
      UI::addMessage("danger", "Invalid inputs!");
      return;
    }
    
    $hashtype = new HashType($_POST['id'], $desc);
    if (!$FACTORIES::getHashTypeFactory()->save($hashtype)) {
      UI::addMessage("danger", "Failed to add new hash type!");
      return;
    }
    UI::addMessage("success", "New hashtype created successfully!");
  }
  
  private function delete() {
    global $FACTORIES;
    
    $hashtype = $FACTORIES::getHashTypeFactory()->get($_POST['type']);
    if ($hashtype == null) {
      UI::addMessage("danger", "Invalid hashtype!");
      return;
    }
    
    $qF = new QueryFilter("hashtypeId", $hashtype->getId(), "=");
    $hashlists = $FACTORIES::getHashlistFactory()->filter(array('filter' => array($qF)));
    if (sizeof($hashlists) > 0) {
      UI::addMessage("danger", "You cannot delete this hashtype! There are hashlists present which are of this type!");
      return;
    }
    
    $FACTORIES::getHashTypeFactory()->delete($hashtype);
    UI::addMessage("success", "Hashtype was deleted successfully!");
  }
}