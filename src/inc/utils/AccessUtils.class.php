<?php

use DBA\AccessGroup;
use DBA\AccessGroupAgent;
use DBA\AccessGroupUser;
use DBA\Agent;
use DBA\JoinFilter;
use DBA\QueryFilter;
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
  private static function intersection($accessGroupsAgent, $accessGroupsUser) {
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
}