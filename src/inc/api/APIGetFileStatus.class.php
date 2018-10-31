<?php

use DBA\Factory;

class APIGetFileStatus extends APIBasic {
  public function execute($QUERY = array()) {
    $deleteRequests = Factory::getFileDeleteFactory()->filter([]);
    $files = [];
    foreach ($deleteRequests as $deleteRequest) {
      $files[] = $deleteRequest->getFilename();
    }
    
    $this->sendResponse(array(
        PResponseGetFileStatus::ACTION => PActions::GET_FILE_STATUS,
        PResponseGetFileStatus::RESPONSE => PValues::SUCCESS,
        PResponseGetFileStatus::FILENAMES => $files
      )
    );
  }
}