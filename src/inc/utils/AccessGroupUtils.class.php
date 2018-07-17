<?php
use DBA\AccessGroup;
use DBA\TaskWrapper;
use DBA\QueryFilter;
use DBA\AccessGroupUser;
use DBA\AccessGroupAgent;

class AccessGroupUtils {
  /**
   * @param string $groupName 
   * @throws HTException 
   * @return AccessGroup
   */
  public static function createGroup($groupName) {
    global $FACTORIES;

    if (strlen($groupName) == 0 || strlen($groupName) > DLimits::ACCESS_GROUP_MAX_LENGTH) {
      throw new HTException("Access group name is too short or too long!");
    }

    $qF = new QueryFilter(AccessGroup::GROUP_NAME, $groupName, "=");
    $check = $FACTORIES::getAccessGroupFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if ($check !== null) {
      throw new HTException("There is already an access group with the same name!");
    }
    $group = new AccessGroup(0, $groupName);
    $group = $FACTORIES::getAccessGroupFactory()->save($group);
    return $group;
  }

  /**
   * @param int $agentId
   * @param int $groupId
   * @throws HTException
   */
  public static function addAgent($agentId, $groupId) {
    global $FACTORIES;

    $group = AccessGroupUtils::getGroup($groupId);
    $agent = AgentUtils::getAgent($agentId);

    $qF1 = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=");
    $qF2 = new QueryFilter(AccessGroupAgent::ACCESS_GROUP_ID, $group->getId(), "=");
    $check = $FACTORIES::getAccessGroupAgentFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)));
    if (sizeof($check) > 0) {
      throw new HTException("Agent is already member of this group!");
    }

    $accessGroupAgent = new AccessGroupAgent(0, $group->getId(), $agent->getId());
    $FACTORIES::getAccessGroupAgentFactory()->save($accessGroupAgent);
  }

  /**
   * @param int $userId
   * @param int $groupId
   * @throws HTException
   */
  public static function addUser($userId, $groupId) {
    global $FACTORIES;

    $group = AccessGroupUtils::getGroup($groupId);
    $user = UserUtils::getUser($userId);

    $qF1 = new QueryFilter(AccessGroupUser::USER_ID, $user->getId(), "=");
    $qF2 = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $group->getId(), "=");
    $check = $FACTORIES::getAccessGroupUserFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)));
    if (sizeof($check) > 0) {
      throw new HTException("User is already member of this group!");
    }

    $accessGroupUser = new AccessGroupUser(0, $group->getId(), $user->getId());
    $FACTORIES::getAccessGroupUserFactory()->save($accessGroupUser);
  }

  /**
   * @param int $agentId
   * @param int $groupId
   * @throws HTException
   */
  public static function removeAgent($agentId, $groupId) {
    global $FACTORIES;

    $group = AccessGroupUtils::getGroup($groupId);
    $agent = AgentUtils::getAgent($agentId);

    $qF1 = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=");
    $qF2 = new QueryFilter(AccessGroupAgent::ACCESS_GROUP_ID, $group->getId(), "=");
    $accessGroupAgent = $FACTORIES::getAccessGroupAgentFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
    if ($accessGroupAgent === null) {
      throw new HTException("Agent is not member of this group!");
    }
    $FACTORIES::getAccessGroupAgentFactory()->delete($accessGroupAgent);
  }

  /**
   * @param int $userId
   * @param int $groupId
   * @throws HTException
   */
  public static function removeUser($userId, $groupId) {
    global $FACTORIES;

    $group = AccessGroupUtils::getGroup($groupId);
    $user = UserUtils::getUser($userId);

    $qF1 = new QueryFilter(AccessGroupUser::USER_ID, $user->getId(), "=");
    $qF2 = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $group->getId(), "=");
    $accessGroupUser = $FACTORIES::getAccessGroupUserFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
    if ($accessGroupUser === null) {
      throw new HTException("User is not member of this group!");
    }
    $FACTORIES::getAccessGroupUserFactory()->delete($accessGroupUser);
  }

  /**
   * @param int $groupId
   * @throws HTException
   */
  public static function deleteGroup($groupId) {
    global $FACTORIES;

    $group = AccessGroupUtils::getGroup($groupId);

    // delete association of tasks with this group
    $qF = new QueryFilter(TaskWrapper::ACCESS_GROUP_ID, $group->getId(), "=");
    $uS = new UpdateSet(TaskWrapper::ACCESS_GROUP_ID, null);
    $FACTORIES::getTaskWrapperFactory()->massUpdate(array($FACTORIES::FILTER => $qF, $FACTORIES::UPDATE => $uS));

    // delete all associations to users
    $qF = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $group->getId(), "=");
    $FACTORIES::getAccessGroupUserFactory()->massDeletion(array($FACTORIES::FILTER => $qF));

    // delete all associations to agents
    $qF = new QueryFilter(AccessGroupAgent::ACCESS_GROUP_ID, $group->getId(), "=");
    $FACTORIES::getAccessGroupAgentFactory()->massDeletion(array($FACTORIES::FILTER => $qF));

    // delete access group
    $FACTORIES::getAccessGroupFactory()->delete($group);
  }

  /**
   * @param int $groupId
   * @throws HTException
   * @return AccessGroup
   */
  public static function getGroup($groupId){
    global $FACTORIES;

    $group = $FACTORIES::getAccessGroupFactory()->get($groupId);
    if ($group === null) {
      throw new HTException("Invalid group!");
    }
    return $group;
  }
}