<?php

use DBA\Hashlist;
use DBA\Factory;

class HashlistHandler implements Handler {
  /**
   * @var Hashlist $hashlist
   */
  private $hashlist;
  
  public function __construct($hashlistId = null) {
    if ($hashlistId == null) {
      $this->hashlist = null;
      return;
    }
    
    $this->hashlist = Factory::getHashlistFactory()->get($hashlistId);
    if ($this->hashlist == null) {
      UI::printError("FATAL", "Hashlist with ID $hashlistId not found!");
    }
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DHashlistAction::APPLY_PRECONFIGURED_TASKS:
          AccessControl::getInstance()->checkPermission(DHashlistAction::APPLY_PRECONFIGURED_TASKS_PERM);
          $count = HashlistUtils::applyPreconfTasks($_POST['hashlist'], (isset($_POST['task'])) ? $_POST['task'] : [], AccessControl::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "Successfully created $count new tasks! You will be forward to the tasks page in 5 seconds.");
          UI::setForward("tasks.php", 5);
          break;
        case DHashlistAction::CREATE_WORDLIST:
          AccessControl::getInstance()->checkPermission(DHashlistAction::CREATE_WORDLIST_PERM);
          $data = HashlistUtils::createWordlists($_POST['hashlist'], AccessControl::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "Exported " . $data[0] . " found plains to " . $data[1] . " successfully!");
          break;
        case DHashlistAction::SET_SECRET:
          AccessControl::getInstance()->checkPermission(DHashlistAction::SET_SECRET_PERM);
          HashlistUtils::setSecret($_POST['hashlist'], @$_POST['secret'], AccessControl::getInstance()->getUser());
          break;
        case DHashlistAction::SET_ARCHIVED:
          AccessControl::getInstance()->checkPermission(DHashlistAction::SET_ARCHIVED_PERM);
          HashlistUtils::setArchived($_POST['hashlist'], @$_POST['archived'], AccessControl::getInstance()->getUser());
          break;
        case DHashlistAction::RENAME_HASHLIST:
          AccessControl::getInstance()->checkPermission(DHashlistAction::RENAME_HASHLIST_PERM);
          HashlistUtils::rename($_POST['hashlist'], $_POST['name'], AccessControl::getInstance()->getUser());
          break;
        case DHashlistAction::PROCESS_ZAP:
          AccessControl::getInstance()->checkPermission(DHashlistAction::PROCESS_ZAP_PERM);
          $data = HashlistUtils::processZap($_POST['hashlist'], $_POST['separator'], $_POST['source'], $_POST, $_FILES, AccessControl::getInstance()->getUser(), (isset($_POST["overwrite"]) && intval($_POST["overwrite"]) == 1) ? true : false);
          UI::addMessage(UI::SUCCESS, "Processed pre-cracked hashes: " . $data[0] . " total lines, " . $data[1] . " new cracked hashes, " . $data[2] . " were already cracked, " . $data[3] . " invalid lines, " . $data[4] . " not matching entries (" . $data[5] . "s)!");
          if ($data[6] > 0) {
            UI::addMessage(UI::WARN, $data[6] . " entries with too long plaintext");
          }
          break;
        case DHashlistAction::EXPORT_HASHLIST:
          AccessControl::getInstance()->checkPermission(DHashlistAction::EXPORT_HASHLIST_PERM);
          HashlistUtils::export($_POST['hashlist'], AccessControl::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "Cracked hashes from hashlist exported successfully!");
          break;
        case DHashlistAction::ZAP_HASHLIST:
          AccessControl::getInstance()->checkPermission(DHashlistAction::ZAP_HASHLIST_PERM);
          $this->zap();
          break;
        case DHashlistAction::DELETE_HASHLIST:
          AccessControl::getInstance()->checkPermission(DHashlistAction::DELETE_HASHLIST_PERM);
          $format = HashlistUtils::delete($_POST['hashlist'], AccessControl::getInstance()->getUser());
          if ($format > DHashlistFormat::BINARY) {
            header("Location: superhashlists.php");
          }
          else {
            header("Location: hashlists.php");
          }
          die();
        case DHashlistAction::CREATE_HASHLIST:
          AccessControl::getInstance()->checkPermission(DHashlistAction::CREATE_HASHLIST_PERM);
          $hashlist = HashlistUtils::createHashlist(
            $_POST['name'],
            (isset($_POST["salted"]) && intval($_POST["salted"]) == 1) ? true : false,
            (isset($_POST["secret"]) && intval($_POST["secret"]) == 1) ? true : false,
            (isset($_POST["hexsalted"]) && intval($_POST["hexsalted"]) == 1) ? true : false,
            $_POST['separator'],
            $_POST['format'],
            $_POST['hashtype'],
            $_POST['separator'],
            $_POST['accessGroupId'],
            $_POST['source'],
            $_POST,
            $_FILES,
            AccessControl::getInstance()->getUser(),
            (isset($_POST["useBrain"]) && intval($_POST["useBrain"]) == 1) ? 1 : 0,
            (isset($_POST['brain-features'])) ? intval($_POST['brain-features']) : 0
          );
          header("Location: hashlists.php?id=" . $hashlist->getId());
          die();
        case DHashlistAction::CREATE_SUPERHASHLIST:
          AccessControl::getInstance()->checkPermission(DHashlistAction::CREATE_SUPERHASHLIST_PERM);
          HashlistUtils::createSuperhashlist($_POST['hlist'], $_POST['name'], Login::getInstance()->getUser());
          header("Location: superhashlists.php");
          die();
        case DHashlistAction::CREATE_LEFTLIST:
          AccessControl::getInstance()->checkPermission(DHashlistAction::CREATE_LEFTLIST_PERM);
          $file = HashlistUtils::leftlist($_POST['hashlist'], Login::getInstance()->getUser());
          UI::addMessage(UI::SUCCESS, "Created left list: " . $file->getFilename());
          break;
        case DHashlistAction::EDIT_NOTES:
          AccessControl::getInstance()->checkPermission(DHashlistAction::EDIT_NOTES_PERM);
          HashlistUtils::editNotes($_POST['hashlist'], $_POST['notes'], Login::getInstance()->getUser());
          break;
        case DHashlistAction::SET_ACCESS_GROUP:
          AccessControl::getInstance()->checkPermission(DHashlistAction::SET_ACCESS_GROUP_PERM);
          HashlistUtils::changeAccessGroup($_POST['hashlist'], $_POST['accessGroupId'], Login::getInstance()->getUser());
          break;
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch (Exception $e) {
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }
  
  /**
   * @throws HTException
   */
  private function zap() {
    $this->hashlist = HashlistUtils::getHashlist($_POST['hashlist']);
    $type = Factory::getHashTypeFactory()->get($this->hashlist->getHashTypeId());
    
    UI::add('list', new DataSet(['hashlist' => $this->hashlist, 'hashtype' => $type]));
    UI::add('zap', true);
    UI::add('impfiles', Util::scanImportDirectory());
  }
}
