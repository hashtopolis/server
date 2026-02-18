<?php

namespace Hashtopolis\inc\user_api;

use Hashtopolis\inc\utils\AccountUtils;
use Exception;
use Hashtopolis\inc\defines\UQuery;
use Hashtopolis\inc\defines\UQueryAccount;
use Hashtopolis\inc\defines\UQueryTask;
use Hashtopolis\inc\defines\UResponseAccount;
use Hashtopolis\inc\defines\USectionAccount;
use Hashtopolis\inc\defines\UValues;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\user_api\UserAPIBasic;

class UserAPIAccount extends UserAPIBasic {
  public function execute($QUERY = array()) {
    try {
      switch ($QUERY[UQuery::REQUEST]) {
        case USectionAccount::GET_INFORMATION:
          $this->getInformation($QUERY);
          break;
        case USectionAccount::SET_EMAIL:
          $this->setEmail($QUERY);
          break;
        case USectionAccount::SET_SESSION_LENGTH:
          $this->setSessionLenght($QUERY);
          break;
        case USectionAccount::CHANGE_PASSWORD:
          $this->changePassword($QUERY);
          break;
        default:
          $this->sendErrorResponse($QUERY[UQuery::SECTION], "INV", "Invalid section request!");
      }
    }
    catch (Exception $e) {
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], $e->getMessage());
    }
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function changePassword($QUERY) {
    if (!isset($QUERY[UQueryAccount::OLD_PASS]) || !isset($QUERY[UQueryAccount::NEW_PASS])) {
      throw new HTException("Invalid query!");
    }
    AccountUtils::changePassword($QUERY[UQueryAccount::OLD_PASS], $QUERY[UQueryAccount::NEW_PASS], $QUERY[UQueryAccount::NEW_PASS], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setSessionLenght($QUERY) {
    if (!isset($QUERY[UQueryAccount::SESSION_LENGTH])) {
      throw new HTException("Invalid query!");
    }
    AccountUtils::updateSessionLifetime($QUERY[UQueryAccount::SESSION_LENGTH], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setEmail($QUERY) {
    if (!isset($QUERY[UQueryAccount::EMAIL])) {
      throw new HTException("Invalid query!");
    }
    AccountUtils::setEmail($QUERY[UQueryAccount::EMAIL], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   */
  private function getInformation($QUERY) {
    $response = [
      UResponseAccount::SECTION => $QUERY[UQueryAccount::SECTION],
      UResponseAccount::REQUEST => $QUERY[UQueryAccount::REQUEST],
      UResponseAccount::RESPONSE => UValues::OK,
      UResponseAccount::USER_ID => (int)$this->user->getId(),
      UResponseAccount::EMAIL => $this->user->getEmail(),
      UResponseAccount::RIGHT_GROUP_ID => (int)$this->user->getRightGroupId(),
      UResponseAccount::SESSION_LENGTH => (int)$this->user->getSessionLifetime()
    ];
    $this->sendResponse($response);
  }
}