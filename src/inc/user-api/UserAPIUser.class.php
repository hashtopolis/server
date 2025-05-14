<?php

class UserAPIUser extends UserAPIBasic {
  public function execute($QUERY = array()) {
    try {
      switch ($QUERY[UQuery::REQUEST]) {
        case USectionUser::LIST_USERS:
          $this->listUsers($QUERY);
          break;
        case USectionUser::GET_USER:
          $this->getUser($QUERY);
          break;
        case USectionUser::CREATE_USER:
          $this->createUser($QUERY);
          break;
        case USectionUser::DISABLE_USER:
          $this->disableUser($QUERY);
          break;
        case USectionUser::ENABLE_USER:
          $this->enableUser($QUERY);
          break;
        case USectionUser::SET_USER_PASSWORD:
          $this->setUserPassword($QUERY);
          break;
        case USectionUser::SET_USER_RIGHT_GROUP:
          $this->setRightGroup($QUERY);
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
  private function setRightGroup($QUERY) {
    if (!isset($QUERY[UQueryUser::USER_ID]) || !isset($QUERY[UQueryUser::USER_RIGHT_GROUP_ID])) {
      throw new HTException("Invalid query!");
    }
    UserUtils::setRights($QUERY[UQueryUser::USER_ID], $QUERY[UQueryUser::USER_RIGHT_GROUP_ID], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setUserPassword($QUERY) {
    if (!isset($QUERY[UQueryUser::USER_ID]) || !isset($QUERY[UQueryUser::USER_PASSWORD])) {
      throw new HTException("Invalid query!");
    }
    UserUtils::setPassword($QUERY[UQueryUser::USER_ID], $QUERY[UQueryUser::USER_PASSWORD], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function enableUser($QUERY) {
    if (!isset($QUERY[UQueryUser::USER_ID])) {
      throw new HTException("Invalid query!");
    }
    UserUtils::enableUser($QUERY[UQueryUser::USER_ID]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function disableUser($QUERY) {
    if (!isset($QUERY[UQueryUser::USER_ID])) {
      throw new HTException("Invalid query!");
    }
    UserUtils::disableUser($QUERY[UQueryUser::USER_ID], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function createUser($QUERY) {
    $toCheck = [
      UQueryUser::USER_USERNAME,
      UQueryUser::USER_EMAIL,
      UQueryUser::RIGHT_GROUP_ID
    ];
    foreach ($toCheck as $input) {
      if (!isset($QUERY[$input])) {
        throw new HTException("Invalid query!");
      }
    }
    UserUtils::createUser(
      $QUERY[UQueryUser::USER_USERNAME],
      $QUERY[UQueryUser::USER_EMAIL],
      $QUERY[UQueryUser::RIGHT_GROUP_ID],
      $this->user
    );
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function getUser($QUERY) {
    if (!isset($QUERY[UQueryUser::USER_ID])) {
      throw new HTException("Invalid query!");
    }
    $user = UserUtils::getUser($QUERY[UQueryUser::USER_ID]);
    $response = [
      UResponseUser::SECTION => $QUERY[UQueryUser::SECTION],
      UResponseUser::REQUEST => $QUERY[UQueryUser::REQUEST],
      UResponseUser::RESPONSE => UValues::OK,
      UResponseUser::USER_ID => (int)$user->getId(),
      UResponseUser::USER_USERNAME => $user->getUsername(),
      UResponseUser::USER_EMAIL => $user->getEmail(),
      UResponseUser::USER_RIGHT_GROUP_ID => (int)$user->getRightGroupId(),
      UResponseUser::USER_REGISTERED => (int)$user->getRegisteredSince(),
      UResponseUser::USER_LAST_LOGIN => (int)$user->getLastLoginDate(),
      UResponseUser::USER_IS_VALID => ($user->getIsValid() == 1) ? true : false,
      UResponseUser::USER_SESSION_LIFETIME => (int)$user->getSessionLifetime()
    ];
    $this->sendResponse($response);
  }
  
  /**
   * @param array $QUERY
   */
  private function listUsers($QUERY) {
    $users = UserUtils::getUsers();
    $list = [];
    $response = [
      UResponseUser::SECTION => $QUERY[UQueryUser::SECTION],
      UResponseUser::REQUEST => $QUERY[UQueryUser::REQUEST],
      UResponseUser::RESPONSE => UValues::OK
    ];
    foreach ($users as $user) {
      $list[] = [
        UResponseUser::USERS_ID => (int)$user->getId(),
        UResponseUser::USERS_USERNAME => $user->getUsername()
      ];
    }
    $response[UResponseUser::USERS] = $list;
    $this->sendResponse($response);
  }
}