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
    global $ACCESS_CONTROL;

    try {
      switch ($action) {
        case DHashlistAction::APPLY_PRECONFIGURED_TASKS:
          $ACCESS_CONTROL->checkPermission(DHashlistAction::APPLY_PRECONFIGURED_TASKS_PERM);
          $count = HashlistUtils::applyPreconfTasks($_POST['hashlist'], (isset($_POST['task'])) ? $_POST['task'] : [], $ACCESS_CONTROL->getUser());
          UI::addMessage(UI::SUCCESS, "Successfully created $count new tasks! You will be forward to the tasks page in 5 seconds.");
          UI::setForward("tasks.php", 5);
          break;
        case DHashlistAction::CREATE_WORDLIST:
          $ACCESS_CONTROL->checkPermission(DHashlistAction::CREATE_WORDLIST_PERM);
          $data = HashlistUtils::createWordlists($_POST['hashlist'], $ACCESS_CONTROL->getUser());
          UI::addMessage(UI::SUCCESS, "Exported " . $data[0] . " found plains to " . $data[1] . " successfully!");
          break;
        case DHashlistAction::SET_SECRET:
          $ACCESS_CONTROL->checkPermission(DHashlistAction::SET_SECRET_PERM);
          HashlistUtils::setSecret($_POST['hashlistId'], $_POST['secret'], $ACCESS_CONTROL->getUser());
          break;
        case DHashlistAction::RENAME_HASHLIST:
          $ACCESS_CONTROL->checkPermission(DHashlistAction::RENAME_HASHLIST_PERM);
          HashlistUtils::rename($_POST['hashlist'], $_POST['name'], $ACCESS_CONTROL->getUser());
          break;
        case DHashlistAction::PROCESS_ZAP:
          $ACCESS_CONTROL->checkPermission(DHashlistAction::PROCESS_ZAP_PERM);
          $data = HashlistUtils::processZap($_POST['hashlist'], $_POST['separator'], $_POST['source'], $_POST, $_FILES, $ACCESS_CONTROL->getUser());
          UI::addMessage(UI::SUCCESS, "Processed pre-cracked hashes: " . $data[0] . " total lines, " . $data[1] . " new cracked hashes, " . $data[2] . " were already cracked, " . $data[3] . " invalid lines, " . $data[4] . " not matching entries (" . $data[5] . "s)!");
          if ($data[6] > 0) {
            UI::addMessage(UI::WARN, $data[6] . " entries with too long plaintext");
          }
          break;
        case DHashlistAction::EXPORT_HASHLIST:
          $ACCESS_CONTROL->checkPermission(DHashlistAction::EXPORT_HASHLIST_PERM);
          HashlistUtils::export($_POST['hashlist'], $ACCESS_CONTROL->getUser());
          UI::addMessage(UI::SUCCESS, "Cracked hashes from hashlist exported successfully!");
          break;
        case DHashlistAction::ZAP_HASHLIST:
          $ACCESS_CONTROL->checkPermission(DHashlistAction::ZAP_HASHLIST_PERM);
          $this->zap();
          break;
        case DHashlistAction::DELETE_HASHLIST:
          $ACCESS_CONTROL->checkPermission(DHashlistAction::DELETE_HASHLIST_PERM);
          $format = HashlistUtils::delete($_POST['hashlist'], $ACCESS_CONTROL->getUser());
          if ($format > DHashlistFormat::BINARY) {
            header("Location: superhashlists.php");
          }
          else {
            header("Location: hashlists.php");
          }
          die();
        case DHashlistAction::CREATE_HASHLIST:
          $ACCESS_CONTROL->checkPermission(DHashlistAction::CREATE_HASHLIST_PERM);
          $hashlist = HashlistUtils::createHashlist(
            $_POST['name'],
            (isset($_POST["salted"]) && intval($_POST["salted"]) == 1) ? true : false,
            (isset($_POST["secret"]) && intval($_POST["secret"]) == 1) ? true : false,
            (isset($_POST["hexsalted"]) && (isset($_POST["secret"]) && intval($_POST["secret"]) == 1) && intval($_POST["hexsalted"]) == 1) ? true : false,
            $_POST['separator'],
            $_POST['format'],
            $_POST['hashtype'],
            $_POST['separator'],
            $_POST['accessGroupId'],
            $_POST['source'],
            $_POST,
            $_FILES,
            $ACCESS_CONTROL->getUser()
          );
          header("Location: hashlists.php?id=" . $hashlist->getId());
          die();
        case DHashlistAction::CREATE_SUPERHASHLIST:
          $ACCESS_CONTROL->checkPermission(DHashlistAction::CREATE_SUPERHASHLIST_PERM);
          HashlistUtils::createSuperhashlist($_POST['hlist'], $_POST['name'], $ACCESS_CONTROL->getUser());
          header("Location: superhashlists.php");
          die();
        case DHashlistAction::CREATE_LEFTLIST:
          $ACCESS_CONTROL->checkPermission(DHashlistAction::CREATE_LEFTLIST_PERM);
          $file = HashlistUtils::leftlist($_POST['hashlist'], $ACCESS_CONTROL->getUser());
          UI::addMessage(UI::SUCCESS, "Created left list: " . $file->getFilename());
          break;
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch (HTException $e) {
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }

  /**
   * @throws HTException
   */
  private function zap() {
    global $OBJECTS;

    $this->hashlist = HashlistUtils::getHashlist($_POST['hashlist']);
    $type = Factory::getHashTypeFactory()->get($this->hashlist->getHashTypeId());

    $OBJECTS['list'] = new DataSet(array('hashlist' => $this->hashlist, 'hashtype' => $type));
    $OBJECTS['zap'] = true;
    $OBJECTS['impfiles'] = Util::scanImportDirectory();
  }
}
