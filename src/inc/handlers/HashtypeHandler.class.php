<?php
use DBA\Hashlist;
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
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
  
  private function add() {
    /** @var $LOGIN Login */
    global $FACTORIES, $LOGIN;
    
    $hashtype = $FACTORIES::getHashTypeFactory()->get($_POST['id']);
    if ($hashtype != null) {
      UI::addMessage(UI::ERROR, "This hash number is already used!");
      return;
    }
    $desc = htmlentities($_POST['description']);
    if (strlen($desc) == 0 || $_POST['id'] < 0) {
      UI::addMessage(UI::ERROR, "Invalid inputs!");
      return;
    }
    
    $salted = 0;
    if ($_POST['isSalted']) {
      $salted = 1;
    }
    
    $hashtype = new HashType($_POST['id'], $desc, $salted);
    if (!$FACTORIES::getHashTypeFactory()->save($hashtype)) {
      UI::addMessage(UI::ERROR, "Failed to add new hash type!");
      return;
    }
    Util::createLogEntry("User", $LOGIN->getUserID(), DLogEntry::INFO, "New Hashtype added: " . $hashtype->getDescription());
    UI::addMessage(UI::SUCCESS, "New hashtype created successfully!");
  }
  
  private function delete() {
    global $FACTORIES;
    
    $hashtype = $FACTORIES::getHashTypeFactory()->get($_POST['type']);
    if ($hashtype == null) {
      UI::addMessage(UI::ERROR, "Invalid hashtype!");
      return;
    }
    
    $qF = new QueryFilter(Hashlist::HASH_TYPE_ID, $hashtype->getId(), "=");
    $hashlists = $FACTORIES::getHashlistFactory()->filter(array($FACTORIES::FILTER => array($qF)));
    if (sizeof($hashlists) > 0) {
      UI::addMessage(UI::ERROR, "You cannot delete this hashtype! There are hashlists present which are of this type!");
      return;
    }
    
    $FACTORIES::getHashTypeFactory()->delete($hashtype);
    UI::addMessage(UI::SUCCESS, "Hashtype was deleted successfully!");
  }
}