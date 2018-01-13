<?php

use DBA\OrderFilter;

class APIDownloadBinary extends APIBasic {
  public function execute($QUERY = array()) {
    global $FACTORIES;
    
    if (!PQueryDownloadBinary::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::DOWNLOAD_BINARY, "Invalid download query!");
    }
    $this->checkToken(PActions::DOWNLOAD_BINARY, $QUERY);
    $this->updateAgent(PActions::DOWNLOAD_BINARY);
    
    // provide agent with requested download
    switch ($QUERY[PQueryDownloadBinary::BINARY_TYPE]) {
      case PValuesDownloadBinaryType::EXTRACTOR:
        // downloading 7zip
        $filename = "7zr";
        switch ($this->agent->getOs()) {
          case DOperatingSystem::LINUX:
            $filename .= ".unix";
            break;
          case DOperatingSystem::WINDOWS:
            $filename .= ".exe";
            break;
          case DOperatingSystem::OSX:
            $filename .= ".osx";
            break;
        }
        //TODO: build url based on config value
        $url = explode("/", $_SERVER['REQUEST_URI']);
        unset($url[sizeof($url) - 1]);
        unset($url[sizeof($url) - 1]);
        $path = Util::buildServerUrl() . implode("/", $url) . "/static/" . $filename;
        $this->sendResponse(array(
            PResponseBinaryDownload::ACTION => PActions::DOWNLOAD_BINARY,
            PResponseBinaryDownload::RESPONSE => PValues::SUCCESS,
            PResponseBinaryDownload::EXECUTABLE => $path
          )
        );
        break;
      case PValuesDownloadBinaryType::CRACKER:
        $crackerBinary = $FACTORIES::getCrackerBinaryFactory()->get($QUERY[PQueryDownloadBinary::BINARY_VERSION_ID]);
        if ($crackerBinary == null) {
          $this->sendErrorResponse(PActions::DOWNLOAD_BINARY, "Invalid cracker binary type id!");
        }
        //$crackerBinaryType = $FACTORIES::getCrackerBinaryTypeFactory()->get($crackerBinary->getCrackerBinaryTypeId());
        
        // TODO: extension building should be somewhere in utils
        switch ($this->agent->getOs()) {
          case DOperatingSystem::LINUX:
            $ext = ".unix";
            break;
          case DOperatingSystem::WINDOWS:
            $ext = ".exe";
            break;
          case DOperatingSystem::OSX:
            $ext = ".osx";
            break;
          default:
            $ext = "";
        }
        $this->sendResponse(array(
            PResponseBinaryDownload::ACTION => PActions::DOWNLOAD_BINARY,
            PResponseBinaryDownload::RESPONSE => PValues::SUCCESS,
            PResponseBinaryDownload::URL => $crackerBinary->getDownloadUrl(),
            PResponseBinaryDownload::EXECUTABLE => $crackerBinary->getBinaryName() . $ext
          )
        );
        break;
      default:
        $this->sendErrorResponse(PActions::DOWNLOAD_BINARY, "Unknown download type!");
    }
  }
}