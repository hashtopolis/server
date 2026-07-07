<?php

namespace Hashtopolis\inc\api;

use Exception;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PResponse;
use Hashtopolis\inc\agent\PResponseGetFileStatus;
use Hashtopolis\inc\agent\PValues;

class APIGetFileStatus extends APIBasic {
  /**
   * @throws Exception
   */
  public function execute(array $QUERY = array()): void {
    $deleteRequests = Factory::getFileDeleteFactory()->filter([]);
    $files = [];
    foreach ($deleteRequests as $deleteRequest) {
      $files[] = $deleteRequest->getFilename();
    }
    
    $this->sendResponse(array(
        PResponse::ACTION => PActions::GET_FILE_STATUS,
        PResponse::RESPONSE => PValues::SUCCESS,
        PResponseGetFileStatus::FILENAMES => $files
      )
    );
  }
}