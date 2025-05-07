<?php

class UserAPIGroup extends UserAPIBasic {
  public function execute($QUERY = array()) {
    try {
      switch ($QUERY[UQuery::REQUEST]) {
        case USectionGroup::LIST_GROUPS:
          $this->listGroups($QUERY);
          break;
        case USectionGroup::GET_GROUP:
          $this->getGroup($QUERY);
          break;
        case USectionGroup::CREATE_GROUP:
          $this->createGroup($QUERY);
          break;
        case USectionGroup::ABORT_CHUNKS_GROUP:
          $this->abortChunksGroup($QUERY);
          break;
        case USectionGroup::DELETE_GROUP:
          $this->deleteGroup($QUERY);
          break;
        case USectionGroup::ADD_AGENT:
          $this->addAgent($QUERY);
          break;
        case USectionGroup::ADD_USER:
          $this->addUser($QUERY);
          break;
        case USectionGroup::REMOVE_AGENT:
          $this->removeAgent($QUERY);
          break;
        case USectionGroup::REMOVE_USER:
          $this->removeUser($QUERY);
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
  private function removeUser($QUERY) {
    if (!isset($QUERY[UQueryGroup::GROUP_ID]) || !isset($QUERY[UQueryGroup::USER_ID])) {
      throw new HTException("Invalid query!");
    }
    AccessGroupUtils::removeUser($QUERY[UQueryGroup::USER_ID], $QUERY[UQueryGroup::GROUP_ID]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function removeAgent($QUERY) {
    if (!isset($QUERY[UQueryGroup::GROUP_ID]) || !isset($QUERY[UQueryGroup::AGENT_ID])) {
      throw new HTException("Invalid query!");
    }
    AccessGroupUtils::removeAgent($QUERY[UQueryGroup::AGENT_ID], $QUERY[UQueryGroup::GROUP_ID]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function addUser($QUERY) {
    if (!isset($QUERY[UQueryGroup::GROUP_ID]) || !isset($QUERY[UQueryGroup::USER_ID])) {
      throw new HTException("Invalid query!");
    }
    AccessGroupUtils::addUser($QUERY[UQueryGroup::USER_ID], $QUERY[UQueryGroup::GROUP_ID]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function addAgent($QUERY) {
    if (!isset($QUERY[UQueryGroup::GROUP_ID]) || !isset($QUERY[UQueryGroup::AGENT_ID])) {
      throw new HTException("Invalid query!");
    }
    AccessGroupUtils::addAgent($QUERY[UQueryGroup::AGENT_ID], $QUERY[UQueryGroup::GROUP_ID]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function deleteGroup($QUERY) {
    if (!isset($QUERY[UQueryGroup::GROUP_ID])) {
      throw new HTException("Invalid query!");
    }
    AccessGroupUtils::deleteGroup($QUERY[UQueryGroup::GROUP_ID]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function createGroup($QUERY) {
    if (!isset($QUERY[UQueryGroup::GROUP_NAME])) {
      throw new HTException("Invalid query!");
    }
    AccessGroupUtils::createGroup($QUERY[UQueryGroup::GROUP_NAME]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function abortChunksGroup($QUERY) {
    if (!isset($QUERY[UQueryGroup::GROUP_ID])) {
      throw new HTException("Invalid query!");
    }
    AccessGroupUtils::abortChunksGroup($QUERY[UQueryGroup::GROUP_ID], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function getGroup($QUERY) {
    if (!isset($QUERY[UQueryGroup::GROUP_ID])) {
      throw new HTException("Invalid query!");
    }
    $group = AccessGroupUtils::getGroup($QUERY[UQueryGroup::GROUP_ID]);
    $response = [
      UResponseGroup::SECTION => $QUERY[UQueryGroup::SECTION],
      UResponseGroup::REQUEST => $QUERY[UQueryGroup::REQUEST],
      UResponseGroup::RESPONSE => UValues::OK,
      UResponseGroup::GROUP_ID => (int)$group->getId(),
      UResponseGroup::GROUP_NAME => $group->getGroupName()
    ];
    $users = AccessGroupUtils::getUsers($group->getId());
    $list = [];
    foreach ($users as $user) {
      $list[] = (int)$user->getUserId();
    }
    $response[UResponseGroup::USERS] = $list;
    $agents = AccessGroupUtils::getAgents($group->getId());
    $list = [];
    foreach ($agents as $agent) {
      $list[] = (int)$agent->getAgentId();
    }
    $response[UResponseGroup::AGENTS] = $list;
    $this->sendResponse($response);
  }
  
  /**
   * @param array $QUERY
   */
  private function listGroups($QUERY) {
    $groups = AccessGroupUtils::getGroups();
    $list = [];
    $response = [
      UResponseGroup::SECTION => $QUERY[UQueryGroup::SECTION],
      UResponseGroup::REQUEST => $QUERY[UQueryGroup::REQUEST],
      UResponseGroup::RESPONSE => UValues::OK
    ];
    foreach ($groups as $group) {
      $list[] = [
        UResponseGroup::GROUPS_ID => (int)$group->getId(),
        UResponseGroup::GROUPS_NAME => $group->getGroupName()
      ];
    }
    $response[UResponseGroup::GROUPS] = $list;
    $this->sendResponse($response);
  }
}