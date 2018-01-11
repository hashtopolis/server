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
        // TODO: this needs to be updated for generic cracker binaries
        $this->sendErrorResponse(PActions::DOWNLOAD_BINARY, "Crackers not available yet!");
        $oF = new OrderFilter(HashcatRelease::TIME, "DESC LIMIT 1");
        $hashcat = $FACTORIES::getHashcatReleaseFactory()->filter(array($FACTORIES::ORDER => array($oF)), true);
        if ($hashcat == null) {
          API::sendErrorResponse(PQueryDownload::ACTION, "No Hashcat release available!");
        }
        
        $postfix = array("hashcat64.bin", "hashcat64.exe", "hashcat");
        $executable = $postfix[$this->agent->getOs()];
        
        if ($this->agent->getHcVersion() == $hashcat->getVersion() && (!isset($QUERY[PQueryDownload::FORCE_UPDATE]) || $QUERY[PQueryDownload::FORCE_UPDATE] != '1')) {
          $this->sendResponse(array(
              PResponseDownload::ACTION => PActions::DOWNLOAD,
              PResponseDownload::RESPONSE => PValues::SUCCESS,
              PResponseDownload::VERSION => PValuesDownloadVersion::UP_TO_DATE,
              PResponseDownload::EXECUTABLE => $executable
            )
          );
        }
        
        $url = $hashcat->getUrl();
        $rootdir = $hashcat->getRootdir();
        
        $this->agent->setHcVersion($hashcat->getVersion());
        $FACTORIES::getAgentFactory()->update($this->agent);
        $this->sendResponse(array(
            PResponseDownload::ACTION => PActions::DOWNLOAD,
            PResponseDownload::RESPONSE => PValues::SUCCESS,
            PResponseDownload::VERSION => PValuesDownloadVersion::NEW_VERSION,
            PResponseDownload::URL => $url,
            PResponseDownload::ROOT_DIR => $rootdir,
            PResponseDownload::EXECUTABLE => $executable
          )
        );
        break;
      default:
        API::sendErrorResponse(PActions::DOWNLOAD, "Unknown download type!");
    }
  }
}