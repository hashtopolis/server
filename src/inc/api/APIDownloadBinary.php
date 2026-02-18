<?php

namespace Hashtopolis\inc\api;

use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQueryDownloadBinary;
use Hashtopolis\inc\agent\PResponseBinaryDownload;
use Hashtopolis\inc\agent\PValues;
use Hashtopolis\inc\agent\PValuesDownloadBinaryType;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\Util;

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
      case PValuesDownloadBinaryType::PREPROCESSOR:
        $preprocessor = Factory::getPreprocessorFactory()->get($QUERY[PQueryDownloadBinary::PREPROCESSOR_ID]);
        if ($preprocessor == null) {
          $this->sendErrorResponse(PActions::DOWNLOAD_BINARY, "Invalid preprocessor id!");
        }
        DServerLog::log(DServerLog::TRACE, "Agent " . $this->agent->getId() . " downloaded preprocessor " . $preprocessor->getId());
        
        $ext = Util::getFileExtension($this->agent->getOs());
        $this->sendResponse(array(
            PResponseBinaryDownload::ACTION => PActions::DOWNLOAD_BINARY,
            PResponseBinaryDownload::RESPONSE => PValues::SUCCESS,
            PResponseBinaryDownload::URL => $preprocessor->getUrl(),
            PResponseBinaryDownload::NAME => $preprocessor->getName(),
            PResponseBinaryDownload::EXECUTABLE => $preprocessor->getBinaryName() . $ext,
            PResponseBinaryDownload::KEYSPACE_CMD => $preprocessor->getKeyspaceCommand(),
            PResponseBinaryDownload::SKIP_CMD => $preprocessor->getSkipCommand(),
            PResponseBinaryDownload::LIMIT_CMD => $preprocessor->getLimitCommand()
          )
        );
        break;
      default:
        DServerLog::log(DServerLog::WARNING, "Agent " . $this->agent->getId() . " requested invalid binary download: " . $QUERY[PQueryDownloadBinary::BINARY_TYPE]);
        $this->sendErrorResponse(PActions::DOWNLOAD_BINARY, "Unknown download type!");
    }
  }
}