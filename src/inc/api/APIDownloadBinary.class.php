<?php

use DBA\Factory;

class APIDownloadBinary extends APIBasic {
  public function execute($QUERY = array()) {
    if (!PQueryDownloadBinary::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::DOWNLOAD_BINARY, "Invalid download query!");
    }
    $this->checkToken(PActions::DOWNLOAD_BINARY, $QUERY);
    $this->updateAgent(PActions::DOWNLOAD_BINARY);
    
    // provide agent with requested download
    switch ($QUERY[PQueryDownloadBinary::BINARY_TYPE]) {
      case PValuesDownloadBinaryType::EXTRACTOR:
        // downloading 7zip
        DServerLog::log(DServerLog::TRACE, "Agent " . $this->agent->getId() . " downloaded 7zr binary");
        $filename = "7zr" . Util::getFileExtension($this->agent->getOs());
        $path = Util::buildServerUrl() . SConfig::getInstance()->getVal(DConfig::BASE_URL) . "/static/" . $filename;
        $this->sendResponse(array(
            PResponseBinaryDownload::ACTION => PActions::DOWNLOAD_BINARY,
            PResponseBinaryDownload::RESPONSE => PValues::SUCCESS,
            PResponseBinaryDownload::EXECUTABLE => $path
          )
        );
        break;
      case PValuesDownloadBinaryType::UFTPD:
        DServerLog::log(DServerLog::TRACE, "Agent " . $this->agent->getId() . " downloaded uftpd binary");
        $filename = "uftpd" . Util::getFileExtension($this->agent->getOs());
        $path = Util::buildServerUrl() . SConfig::getInstance()->getVal(DConfig::BASE_URL) . "/static/" . $filename;
        $this->sendResponse(array(
            PResponseBinaryDownload::ACTION => PActions::DOWNLOAD_BINARY,
            PResponseBinaryDownload::RESPONSE => PValues::SUCCESS,
            PResponseBinaryDownload::EXECUTABLE => $path
          )
        );
        break;
      case PValuesDownloadBinaryType::CRACKER:
        $crackerBinary = Factory::getCrackerBinaryFactory()->get($QUERY[PQueryDownloadBinary::BINARY_VERSION_ID]);
        if ($crackerBinary == null) {
          $this->sendErrorResponse(PActions::DOWNLOAD_BINARY, "Invalid cracker binary type id!");
        }
        $crackerBinaryType = Factory::getCrackerBinaryTypeFactory()->get($crackerBinary->getCrackerBinaryTypeId());
        DServerLog::log(DServerLog::TRACE, "Agent " . $this->agent->getId() . " downloaded cracker binary " . $crackerBinary->getId());
        
        $ext = Util::getFileExtension($this->agent->getOs());
        $this->sendResponse(array(
            PResponseBinaryDownload::ACTION => PActions::DOWNLOAD_BINARY,
            PResponseBinaryDownload::RESPONSE => PValues::SUCCESS,
            PResponseBinaryDownload::URL => $crackerBinary->getDownloadUrl(),
            PResponseBinaryDownload::NAME => $crackerBinaryType->getTypeName(),
            PResponseBinaryDownload::EXECUTABLE => $crackerBinary->getBinaryName() . $ext
          )
        );
        break;
      case PValuesDownloadBinaryType::PRINCE:
        $url = SConfig::getInstance()->getVal(DConfig::PRINCE_LINK);
        if (strlen($url) == 0) {
          $this->sendErrorResponse(PActions::DOWNLOAD_BINARY, "No prince binary URL is configured on the server!");
        }
        DServerLog::log(DServerLog::TRACE, "Agent " . $this->agent->getId() . " downloaded prince binary");
        $this->sendResponse(array(
            PResponseBinaryDownload::ACTION => PActions::DOWNLOAD_BINARY,
            PResponseBinaryDownload::RESPONSE => PValues::SUCCESS,
            PResponseBinaryDownload::URL => $url,
          )
        );
      default:
        DServerLog::log(DServerLog::WARNING, "Agent " . $this->agent->getId() . " requested invalid binary download: " . $QUERY[PQueryDownloadBinary::BINARY_TYPE]);
        $this->sendErrorResponse(PActions::DOWNLOAD_BINARY, "Unknown download type!");
    }
  }
}