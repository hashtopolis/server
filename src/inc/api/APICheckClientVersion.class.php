<?php

class APICheckClientVersion extends APIBasic {
  public function execute($QUERY = array()) {
    global $FACTORIES;
    
    // check if provided hash is the same as script and send file contents if not
    if (!PQueryCheckClientVersion::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::CHECK_CLIENT_VERSION, 'Invalid version check query!');
    }
    $this->checkToken(PActions::CHECK_CLIENT_VERSION, $QUERY);
    
    $version = $QUERY[PQueryUpdate::VERSION];
    $type = $QUERY[PQueryUpdate::TYPE];
    
    $qF = new QueryFilter(AgentBinary::TYPE, $type, "=");
    $result = $FACTORIES::getAgentBinaryFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if ($result == null) {
      $this->sendErrorResponse(PActions::CHECK_CLIENT_VERSION, "Type not found!");
    }
    
    // TODO: build this url based on the config variables
    $base = explode("/", $_SERVER['PHP_SELF']);
    unset($base[sizeof($base) - 1]);
    unset($base[sizeof($base) - 1]);
    $base = implode("/", $base);
    
    $this->updateAgent(PActions::CHECK_CLIENT_VERSION);
    if (Util::versionComparison($result->getVersion(), $version) == -1) {
      $this->sendResponse(array(
          PResponseUpdate::ACTION => PActions::CHECK_CLIENT_VERSION,
          PResponseUpdate::RESPONSE => PValues::SUCCESS,
          PResponseUpdate::VERSION => PValuesUpdateVersion::NEW_VERSION,
          PResponseUpdate::URL => Util::buildServerUrl() . $base . "/agents.php?download=" . $result->getId()
        )
      );
    }
    else {
      $this->sendResponse(array(
          PResponseUpdate::ACTION => PActions::CHECK_CLIENT_VERSION,
          PResponseUpdate::RESPONSE => PValues::SUCCESS,
          PResponseUpdate::VERSION => PValuesUpdateVersion::UP_TO_DATE
        )
      );
    }
  }
}