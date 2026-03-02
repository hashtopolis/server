<?php

namespace Hashtopolis\inc\user_api;

use Hashtopolis\inc\utils\AccessControlUtils;
use Exception;
use Hashtopolis\inc\apiv2\common\error\HttpConflict;
use Hashtopolis\inc\apiv2\common\error\HttpError;
use Hashtopolis\inc\defines\UQuery;
use Hashtopolis\inc\defines\UQueryAccess;
use Hashtopolis\inc\defines\UQueryTask;
use Hashtopolis\inc\defines\UResponseAccess;
use Hashtopolis\inc\defines\USectionAccess;
use Hashtopolis\inc\defines\UValues;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\user_api\UserAPIBasic;

class UserAPIAccess extends UserAPIBasic {
  public function execute($QUERY = array()) {
    try {
      switch ($QUERY[UQuery::REQUEST]) {
        case USectionAccess::LIST_GROUPS:
          $this->listGroups($QUERY);
          break;
        case USectionAccess::GET_GROUP:
          $this->getGroup($QUERY);
          break;
        case USectionAccess::CREATE_GROUP:
          $this->createGroup($QUERY);
          break;
        case USectionAccess::DELETE_GROUP:
          $this->deleteGroup($QUERY);
          break;
        case USectionAccess::SET_PERMISSIONS:
          $this->setPermissions($QUERY);
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
  private function setPermissions($QUERY) {
    if (!isset($QUERY[UQueryAccess::RIGHT_GROUP_ID]) || !isset($QUERY[UQueryAccess::PERMISSIONS])) {
      throw new HTException("Invalid query!");
    }
    $perm = $QUERY[UQueryAccess::PERMISSIONS];
    $prepared = [];
    foreach ($perm as $key => $p) {
      $prepared[] = $key . "-" . (($p) ? "1" : "0");
    }
    $changed = AccessControlUtils::updateGroupPermissions($QUERY[UQueryAccess::RIGHT_GROUP_ID], $prepared);
    if ($changed) {
      $response = [
        UResponseAccess::SECTION => $QUERY[UQueryAccess::SECTION],
        UResponseAccess::REQUEST => $QUERY[UQueryAccess::REQUEST],
        UResponseAccess::RESPONSE => UValues::OK,
        UResponseAccess::WARNING => "Some permissions were updated due to dependencies!"
      ];
      $this->sendResponse($response);
    }
    else {
      $this->sendSuccessResponse($QUERY);
    }
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   * @throws HttpError
   */
  private function deleteGroup($QUERY) {
    if (!isset($QUERY[UQueryAccess::RIGHT_GROUP_ID])) {
      throw new HTException("Invalid query!");
    }
    AccessControlUtils::deleteGroup($QUERY[UQueryAccess::RIGHT_GROUP_ID]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   * @throws HttpError
   * @throws HttpConflict
   */
  private function createGroup($QUERY) {
    if (!isset($QUERY[UQueryAccess::RIGHT_GROUP_NAME])) {
      throw new HTException("Invalid query!");
    }
    AccessControlUtils::createGroup($QUERY[UQueryAccess::RIGHT_GROUP_NAME]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function getGroup($QUERY) {
    if (!isset($QUERY[UQueryAccess::RIGHT_GROUP_ID])) {
      throw new HTException("Invalid query!");
    }
    $group = AccessControlUtils::getGroup($QUERY[UQueryAccess::RIGHT_GROUP_ID]);
    $members = AccessControlUtils::getMembers($group->getId());
    $list = [];
    $response = [
      UResponseAccess::SECTION => $QUERY[UQueryAccess::SECTION],
      UResponseAccess::REQUEST => $QUERY[UQueryAccess::REQUEST],
      UResponseAccess::RESPONSE => UValues::OK,
      UResponseAccess::RIGHT_GROUP_ID => (int)$group->getId(),
      UResponseAccess::RIGHT_GROUP_NAME => $group->getGroupName(),
      UResponseAccess::PERMISSIONS => ($group->getPermissions() == 'ALL') ? 'ALL' : json_decode($group->getPermissions(), true),
    ];
    foreach ($members as $user) {
      $list[] = (int)$user->getId();
    }
    $response[UResponseAccess::MEMBERS] = $list;
    $this->sendResponse($response);
  }
  
  /**
   * @param array $QUERY
   */
  private function listGroups($QUERY) {
    $groups = AccessControlUtils::getGroups();
    $list = [];
    $response = [
      UResponseAccess::SECTION => $QUERY[UQueryAccess::SECTION],
      UResponseAccess::REQUEST => $QUERY[UQueryAccess::REQUEST],
      UResponseAccess::RESPONSE => UValues::OK
    ];
    foreach ($groups as $group) {
      $list[] = [
        UResponseAccess::RIGHT_GROUPS_ID => (int)$group->getId(),
        UResponseAccess::RIGHT_GROUPS_NAME => $group->getGroupName()
      ];
    }
    $response[UResponseAccess::RIGHT_GROUPS] = $list;
    $this->sendResponse($response);
  }
}