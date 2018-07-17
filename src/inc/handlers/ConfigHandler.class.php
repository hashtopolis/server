<?php

class ConfigHandler implements Handler {
  public function __construct($configId = null) {
    //we need nothing to load
  }

  public function handle($action) {
    global $ACCESS_CONTROL, $LOGIN;

    try{
      switch ($action) {
        case DConfigAction::UPDATE_CONFIG:
          $ACCESS_CONTROL->checkPermission(DConfigAction::UPDATE_CONFIG_PERM);
          ConfigUtils::updateConfig($_POST);
          UI::addMessage(UI::SUCCESS, "Config was updated!");
          break;
        case DConfigAction::REBUILD_CACHE:
          $ACCESS_CONTROL->checkPermission(DConfigAction::REBUILD_CACHE_PERM);
          $ret = ConfigUtils::rebuildCache();
          UI::addMessage(UI::SUCCESS, "Updated all chunks and hashlists. Corrected " . $ret[0] . " chunks and " . $ret[1] . " hashlists.");
          break;
        case DConfigAction::RESCAN_FILES:
          $ACCESS_CONTROL->checkPermission(DConfigAction::RESCAN_FILES_PERM);
          ConfigUtils::scanFiles();
          UI::addMessage(UI::SUCCESS, "File scan was successfull, no actions required!");
          break;
        case DConfigAction::CLEAR_ALL:
          $ACCESS_CONTROL->checkPermission(DConfigAction::CLEAR_ALL_PERM);
          ConfigUtils::clearAll($LOGIN->getUser());
          break;
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch(HTException $e){
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
    catch(HTMessages $m){
      UI::addMessage(UI::ERROR, $m->getHTMLMessage());
    }
  }
}