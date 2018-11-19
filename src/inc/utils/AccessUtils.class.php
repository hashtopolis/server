<?php

use DBA\AccessGroup;
use DBA\AccessGroupAgent;
use DBA\AccessGroupUser;
use DBA\Agent;
use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\Task;
use DBA\User;
use DBA\TaskWrapper;
use DBA\Hashlist;
use DBA\Factory;
use DBA\File;

class AccessUtils {
  /**
   * @param Hashlist[]|Hashlist $hashlists
   * @param User $user
   * @return boolean
   */
  public static function userCanAccessHashlists($hashlists, $user) {
    if (!is_array($hashlists)) {
      $hashlists = array($hashlists);
    }
    
    $accessGroupIds = Util::getAccessGroupIds($user->getId());
    foreach ($hashlists as $hashlist) {
      if (!in_array($hashlist->getAccessGroupId(), $accessGroupIds)) {
        return false;
      }
    }
    return true;
  }
  
  /**
   * @param $agent Agent
   * @param $user User
   * @return bool true if user has access to agent
   */
  public static function userCanAccessAgent($agent, $user) {
    $qF = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=", Factory::getAccessGroupAgentFactory());
    $jF = new JoinFilter(Factory::getAccessGroupAgentFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupAgent::ACCESS_GROUP_ID);
    $joined = Factory::getAccessGroupFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    /** @var $accessGroupsAgent AccessGroup[] */
    $accessGroupsAgent = $joined[Factory::getAccessGroupFactory()->getModelName()];
    
    $qF = new QueryFilter(AccessGroupUser::USER_ID, $user->getId(), "=", Factory::getAccessGroupUserFactory());
    $jF = new JoinFilter(Factory::getAccessGroupUserFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupUser::ACCESS_GROUP_ID);
    $joined = Factory::getAccessGroupFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    /** @var $accessGroupsUser AccessGroup[] */
    $accessGroupsUser = $joined[Factory::getAccessGroupFactory()->getModelName()];
    
    return sizeof(AccessUtils::intersection($accessGroupsAgent, $accessGroupsUser)) > 0;
  }
  
  /**
   * @param TaskWrapper $taskWrapper
   * @param User $user
   * @return boolean
   */
  public static function userCanAccessTask($taskWrapper, $user) {
    $accessGroupIds = Util::getAccessGroupIds($user->getId());
    if (!in_array($taskWrapper->getAccessGroupId(), $accessGroupIds)) {
      return false;
    }
    return true;
  }
  
  /**
   * @param File $file
   * @param User $user
   * @return boolean
   */
  public static function userCanAccessFile($file, $user) {
    $accessGroupIds = Util::getAccessGroupIds($user->getId());
    if (!in_array($file->getAccessGroupId(), $accessGroupIds)) {
      return false;
    }
    return true;
  }
  
  /**
   * @param $accessGroupsAgent AccessGroup[]
   * @param $accessGroupsUser AccessGroup[]
   * @return AccessGroup[]
   */
  public static function intersection($accessGroupsAgent, $accessGroupsUser) {
    if (sizeof($accessGroupsUser) == 0 || sizeof($accessGroupsAgent) == 0) {
      return array();
    }
    $intersect = array();
    foreach ($accessGroupsAgent as $accessGroupA) {
      foreach ($accessGroupsUser as $accessGroupU) {
        if ($accessGroupA->getId() == $accessGroupU->getId()) {
          $intersect[] = $accessGroupA;
          break;
        }
      }
    }
    return $intersect;
  }
  
  /**
   * @param $user User
   * @return AccessGroup[]
   */
  public static function getAccessGroupsOfUser($user) {
    $qF = new QueryFilter(AccessGroupUser::USER_ID, $user->getId(), "=", Factory::getAccessGroupUserFactory());
    $jF = new JoinFilter(Factory::getAccessGroupUserFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupUser::ACCESS_GROUP_ID);
    $joined = Factory::getAccessGroupFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    /** @var $accessGroupsUser AccessGroup[] */
    return $joined[Factory::getAccessGroupFactory()->getModelName()];
  }
  
  /**
   * @param $agent Agent
   * @return AccessGroup[]
   */
  public static function getAccessGroupsOfAgent($agent) {
    $qF = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=", Factory::getAccessGroupAgentFactory());
    $jF = new JoinFilter(Factory::getAccessGroupAgentFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupAgent::ACCESS_GROUP_ID);
    $joined = Factory::getAccessGroupFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    /** @var $accessGroupsUser AccessGroup[] */
    return $joined[Factory::getAccessGroupFactory()->getModelName()];
  }
  
  /**
   * Gets the first access group (which is the default access group. If it does not exist, it created the default access group.
   *
   * @return AccessGroup
   */
  public static function getOrCreateDefaultAccessGroup() {
    $accessGroup = Factory::getAccessGroupFactory()->get(1);
    if ($accessGroup == null) {
      $accessGroup = new AccessGroup(1, "Default Group");
      $accessGroup = Factory::getAccessGroupFactory()->save($accessGroup);
    }
    return $accessGroup;
  }
  
  /**
   * @param $agent Agent
   * @param $task Task
   * @return bool true if agent is allowed to access task
   */
  public static function agentCanAccessTask($agent, $task) {
    // load access groups of agent
    $accessGroups = AccessUtils::getAccessGroupsOfAgent($agent);
    $accessGroupsIds = Util::arrayOfIds($accessGroups);
    
    // load task info
    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!in_array($taskWrapper->getAccessGroupId(), $accessGroupsIds)) {
      return false; // task is in an access group which agent is not allowed to access
    }
    
    $hashlists = Util::checkSuperHashlist(Factory::getHashlistFactory()->get($taskWrapper->getHashlistId()));
    foreach ($hashlists as $hashlist) {
      if ($hashlist->getIsSecret() > $agent->getIsTrusted()) {
        return false; // hashlist is secret and agent is not trusted
      }
      else if (!in_array($hashlist->getAccessGroupId(), $accessGroupsIds)) {
        return false; // agent is not in the access group to which the hashlist is assigned
      }
    }
    
    $files = TaskUtils::getFilesOfTask($task);
    foreach ($files as $file) {
      if ($file->getIsSecret() > $agent->getIsTrusted()) {
        return false; // at least one file is secret and the agent is not trusted
      }
    }
    return true;
  }
}