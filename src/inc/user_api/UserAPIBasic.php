<?php

namespace Hashtopolis\inc\user_api;

use Hashtopolis\dba\models\User;
use Hashtopolis\dba\models\ApiKey;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\defines\UQuery;
use Hashtopolis\inc\defines\UQueryTask;
use Hashtopolis\inc\defines\UResponse;
use Hashtopolis\inc\defines\UResponseErrorMessage;
use Hashtopolis\inc\defines\UValues;
use PValues;

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
  
  protected function checkForError($QUERY, $error, $response = null) {
    if ($error !== false) {
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], $error);
    }
    else if ($response != null) {
      $this->sendResponse($response);
    }
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * Used to send a generic success response if no additional data is sent
   * @param array $QUERY original query
   */
  protected function sendSuccessResponse($QUERY) {
    $this->sendResponse(array(
        UResponse::SECTION => $QUERY[UQuery::SECTION],
        UResponse::REQUEST => $QUERY[UQuery::REQUEST],
        UResponse::RESPONSE => UValues::OK
      )
    );
  }
  
  protected function updateApi() {
    $this->apiKey->setAccessCount($this->apiKey->getAccessCount() + 1);
    Factory::getApiKeyFactory()->update($this->apiKey);
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
    $qF = new QueryFilter(ApiKey::ACCESS_KEY, $QUERY[UQuery::ACCESS_KEY], "=");
    $apiKey = Factory::getApiKeyFactory()->filter([Factory::FILTER => $qF], true);
    if ($apiKey == null) {
      $this->sendErrorResponse($section, $request, "Invalid access key!");
    }
    else if ($apiKey->getStartValid() > time() || $apiKey->getEndValid() < time()) {
      $this->sendErrorResponse($section, $request, "Expired access key!");
    }
    else if (!$this->hasPermission($section, $request, $apiKey)) {
      $this->sendErrorResponse($section, $request, "Permission denied!");
    }
    $this->apiKey = $apiKey;
    $this->user = Factory::getUserFactory()->get($apiKey->getUserId());
    $this->updateApi();
  }
  
  /**
   * @param string $section
   * @param string $request
   * @param ApiKey $apiKey
   */
  public function hasPermission($section, $request, $apiKey) {
    $apiGroup = Factory::getApiGroupFactory()->get($apiKey->getApiGroupId());
    if ($apiGroup->getPermissions() == 'ALL') {
      return true;
    }
    $json = json_decode($apiGroup->getPermissions(), true);
    if (!isset($json[$section])) {
      return false;
    }
    else if (!isset($json[$section][$request])) {
      return false;
    }
    else if ($json[$section][$request] == true) {
      return true;
    }
    return false;
  }
}
