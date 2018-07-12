<?php
use DBA\User;
use DBA\ApiKey;
use DBA\QueryFilter;

abstract class UserAPIBasic {
  /** @var User */
  protected $user = null;
  /** @var ApiKey */
  protected $apiKey = null;

  /**
   * @param array $QUERY input query sent to the API
   */
  public abstract function execute($QUERY = array());

  protected function sendResponse($RESPONSE) {
    header("Content-Type: application/json");
    echo json_encode($RESPONSE);
    die();
  }

  protected function updateApi() {
    global $FACTORIES;

    $this->apiKey->setAccessCount($this->apiKey->getAccessCount() + 1);
    $FACTORIES::getApiKeyFactory()->update($this->apiKey);
  }

  public function sendErrorResponse($section, $request, $msg) {
    $ANS = array();
    $ANS[UResponseErrorMessage::SECTION] = $section;
    $ANS[UResponseErrorMessage::REQUEST] = $request;
    $ANS[UResponseErrorMessage::RESPONSE] = PValues::ERROR;
    $ANS[UResponseErrorMessage::MESSAGE] = $msg;
    header("Content-Type: application/json");
    echo json_encode($ANS);
    die();
  }

  public function checkApiKey($section, $request, $QUERY) {
    global $FACTORIES;

    $qF = new QueryFilter(ApiKey::ACCESS_KEY, $QUERY[UQuery::ACCESS_KEY], "=");
    $apiKey = $FACTORIES::getApiKeyFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if ($apiKey == null) {
      $this->sendErrorResponse($section, $request, "Invalid access key!");
    }
    else if($apiKey->getStartValid() > time() || $apiKey->getEndValid() < time()){
      $this->sendErrorResponse($section, $request, "Expired access key!");
    }
    else if(!$this->hasPermission($section, $request, $apiKey)){
      $this->sendErrorResponse($section, $request, "Permission denied!");
    }
    $this->apiKey = $apiKey;
    $this->user = $FACTORIES::getApiKeyFactory()->get($apiKey->getUserId());
    $this->updateApi();
  }

  /**
   * @param string $section
   * @param string $request
   * @param ApiKey $apiKey
   */
  private function hasPermission($section, $request, $apiKey){
    global $FACTORIES;

    $apiGroup = $FACTORIES::getApiGroupFactory()->get($apiKey->getApiGroupId());
    if($apiGroup->getPermissions() == 'ALL'){
      return true;
    }
    $json = json_decode($apiGroup->getPermissions(), true);
    if(!isset($json[$section])){
      return false;
    }
    else if(!isset($json[$section][$request])){
      return false;
    }
    else if($json[$section][$request] == true){
      return true;
    }
    return false;
  }
}





















