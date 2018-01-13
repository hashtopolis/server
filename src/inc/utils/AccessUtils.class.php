<?php

use DBA\AccessGroup;
use DBA\AccessGroupAgent;
use DBA\AccessGroupUser;
use DBA\Agent;
use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\Task;
use DBA\User;

class AccessUtils {
  /**
   * @param $agent Agent
   * @param $user User
   * @return bool true if user has access to agent
   */
  public static function userCanAccessAgent($agent, $user) {
    global $FACTORIES;
    
    $qF = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=", $FACTORIES::getAccessGroupAgentFactory());
    $jF = new JoinFilter($FACTORIES::getAccessGroupAgentFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupAgent::ACCESS_GROUP_ID);
    $joined = $FACTORIES::getAccessGroupFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    /** @var $accessGroupsAgent AccessGroup[] */
    $accessGroupsAgent = $joined[$FACTORIES::getAccessGroupFactory()->getModelName()];
    
    $qF = new QueryFilter(AccessGroupUser::USER_ID, $user->getId(), "=", $FACTORIES::getAccessGroupUserFactory());
    $jF = new JoinFilter($FACTORIES::getAccessGroupUserFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupUser::ACCESS_GROUP_ID);
    $joined = $FACTORIES::getAccessGroupFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    /** @var $accessGroupsUser AccessGroup[] */
    $accessGroupsUser = $joined[$FACTORIES::getAccessGroupFactory()->getModelName()];
    
    return sizeof(AccessUtils::intersection($accessGroupsAgent, $accessGroupsUser)) > 0;
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
    global $FACTORIES;
    
    $qF = new QueryFilter(AccessGroupUser::USER_ID, $user->getId(), "=", $FACTORIES::getAccessGroupUserFactory());
    $jF = new JoinFilter($FACTORIES::getAccessGroupUserFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupUser::ACCESS_GROUP_ID);
    $joined = $FACTORIES::getAccessGroupFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    /** @var $accessGroupsUser AccessGroup[] */
    return $joined[$FACTORIES::getAccessGroupFactory()->getModelName()];
  }
  
  /**
   * @param $agent Agent
   * @return AccessGroup[]
   */
  public static function getAccessGroupsOfAgent($agent) {
    global $FACTORIES;
    
    $qF = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=", $FACTORIES::getAccessGroupAgentFactory());
    $jF = new JoinFilter($FACTORIES::getAccessGroupAgentFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupAgent::ACCESS_GROUP_ID);
    $joined = $FACTORIES::getAccessGroupFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    /** @var $accessGroupsUser AccessGroup[] */
    return $joined[$FACTORIES::getAccessGroupFactory()->getModelName()];
  }
  
  /**
   * Gets the first access group (which is the default access group. If it does not exist, it created the default access group.
   *
   * @return AccessGroup
   */
  public static function getOrCreateDefaultAccessGroup() {
    global $FACTORIES;
    
    $accessGroup = $FACTORIES::getAccessGroupFactory()->get(1);
    if ($accessGroup == null) {
      $accessGroup = new AccessGroup(1, "Default Group");
      $accessGroup = $FACTORIES::getAccessGroupFactory()->save($accessGroup);
    }
    return $accessGroup;
  }
  
  /**
   * @param $agent Agent
   * @param $task Task
   * @return bool true if agent is allowed to access task
   */
  public static function agentCanAccessTask($agent, $task) {
    global $FACTORIES;
    
    // load access groups of agent
    $accessGroups = AccessUtils::getAccessGroupsOfAgent($agent);
    $accessGroupsIds = Util::arrayOfIds($accessGroups);
    
    // load task info
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!in_array($taskWrapper->getAccessGroupId(), $accessGroupsIds)) {
      return false; // task is in an access group which agent is not allowed to access
    }
    
    $hashlists = Util::checkSuperHashlist($FACTORIES::getHashlistFactory()->get($taskWrapper->getHashlistId()));
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