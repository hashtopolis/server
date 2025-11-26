<?php

use DBA\AccessGroup;
use DBA\Chunk;
use DBA\ContainFilter;
use DBA\TaskWrapper;
use DBA\UpdateSet;
use DBA\QueryFilter;
use DBA\AccessGroupUser;
use DBA\AccessGroupAgent;
use DBA\Hashlist;
use DBA\Factory;
use DBA\File;

require_once __DIR__ . '/../apiv2/common/ErrorHandler.class.php';
class AccessGroupUtils {
  /**
   * @param int $groupId
   * @return AccessGroupUser[]
   */
  public static function getUsers($groupId) {
    $qF = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $groupId, "=");
    return Factory::getAccessGroupUserFactory()->filter([Factory::FILTER => $qF]);
  }
  
  /**
   * @param int $groupId
   * @return AccessGroupAgent[]
   */
  public static function getAgents($groupId) {
    $qF = new QueryFilter(AccessGroupAgent::ACCESS_GROUP_ID, $groupId, "=");
    return Factory::getAccessGroupAgentFactory()->filter([Factory::FILTER => $qF]);
  }
  
  /**
   * @return AccessGroup[]
   */
  public static function getGroups() {
    return Factory::getAccessGroupFactory()->filter([]);
  }
  
  /**
   * @param string $groupName
   * @return AccessGroup
   * @throws HTException
   */
  public static function createGroup($groupName) {
    if (strlen($groupName) == 0 || strlen($groupName) > DLimits::ACCESS_GROUP_MAX_LENGTH) {
      throw new HttpError("Access group name is too short or too long!");
    }
    
    $qF = new QueryFilter(AccessGroup::GROUP_NAME, $groupName, "=");
    $check = Factory::getAccessGroupFactory()->filter([Factory::FILTER => $qF], true);
    if ($check !== null) {
      throw new HttpConflict("There is already an access group with the same name!");
    }
    $group = new AccessGroup(null, $groupName);
    $group = Factory::getAccessGroupFactory()->save($group);
    return $group;
  }
  
  /**
   * @param int $groupId
   * @param $user
   * @throws HTException
   */
  public static function abortChunksGroup($groupId, $user) {
    $accessGroups = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user));
    if (!in_array($groupId, $accessGroups)) {
      throw new HTException("User is not a member of this access group!");
    }
    
    $groupAgents = AccessGroupUtils::getAgents($groupId);
    foreach ($groupAgents as $groupAgent) {
      $agentId = $groupAgent->getAgentId();
      $qF1 = new QueryFilter(Chunk::AGENT_ID, $agentId, "=");
      $qF2 = new ContainFilter(Chunk::STATE, [DHashcatStatus::INIT, DHashcatStatus::RUNNING]);
      $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
      foreach ($chunks as $chunk) {
        TaskUtils::abortChunk($chunk->getId(), $user);
      }
    }
  }
  
  /**
   * @param int $agentId
   * @param int $groupId
   * @throws HTException
   */
  public static function addAgent($agentId, $groupId) {
    $group = AccessGroupUtils::getGroup($groupId);
    $agent = AgentUtils::getAgent($agentId);
    
    $qF1 = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=");
    $qF2 = new QueryFilter(AccessGroupAgent::ACCESS_GROUP_ID, $group->getId(), "=");
    $check = Factory::getAccessGroupAgentFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
    if (sizeof($check) > 0) {
      throw new HTException("Agent is already member of this group!");
    }
    
    $accessGroupAgent = new AccessGroupAgent(null, $group->getId(), $agent->getId());
    Factory::getAccessGroupAgentFactory()->save($accessGroupAgent);
  }
  
  /**
   * @param int $userId
   * @param int $groupId
   * @throws HTException
   */
  public static function addUser($userId, $groupId) {
    $group = AccessGroupUtils::getGroup($groupId);
    $user = UserUtils::getUser($userId);
    
    $qF1 = new QueryFilter(AccessGroupUser::USER_ID, $user->getId(), "=");
    $qF2 = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $group->getId(), "=");
    $check = Factory::getAccessGroupUserFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
    if (sizeof($check) > 0) {
      throw new HTException("User is already member of this group!");
    }
    
    $accessGroupUser = new AccessGroupUser(null, $group->getId(), $user->getId());
    Factory::getAccessGroupUserFactory()->save($accessGroupUser);
  }
  
  /**
   * @param int $agentId
   * @param int $groupId
   * @throws HTException
   */
  public static function removeAgent($agentId, $groupId) {
    $group = AccessGroupUtils::getGroup($groupId);
    $agent = AgentUtils::getAgent($agentId);
    
    $qF1 = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=");
    $qF2 = new QueryFilter(AccessGroupAgent::ACCESS_GROUP_ID, $group->getId(), "=");
    $accessGroupAgent = Factory::getAccessGroupAgentFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
    if ($accessGroupAgent === null) {
      throw new HTException("Agent is not member of this group!");
    }
    Factory::getAccessGroupAgentFactory()->delete($accessGroupAgent);
  }
  
  /**
   * @param int $userId
   * @param int $groupId
   * @throws HTException
   */
  public static function removeUser($userId, $groupId) {
    $group = AccessGroupUtils::getGroup($groupId);
    $user = UserUtils::getUser($userId);
    
    $qF1 = new QueryFilter(AccessGroupUser::USER_ID, $user->getId(), "=");
    $qF2 = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $group->getId(), "=");
    $accessGroupUser = Factory::getAccessGroupUserFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
    if ($accessGroupUser === null) {
      throw new HTException("User is not member of this group!");
    }
    Factory::getAccessGroupUserFactory()->delete($accessGroupUser);
  }
  
  /**
   * @param int $groupId
   * @throws HTException
   */
  public static function deleteGroup($groupId) {
    $group = AccessGroupUtils::getGroup($groupId);
    $default = AccessUtils::getOrCreateDefaultAccessGroup();
    if ($default->getId() == $group->getId()) {
      throw new HTException("You cannot delete the default group!");
    }
    
    // update association of tasks with this group
    $qF = new QueryFilter(TaskWrapper::ACCESS_GROUP_ID, $group->getId(), "=");
    $uS = new UpdateSet(TaskWrapper::ACCESS_GROUP_ID, $default->getId());
    Factory::getTaskWrapperFactory()->massUpdate([Factory::FILTER => $qF, Factory::UPDATE => $uS]);
    
    // update associations of hashlists with this group
    $qF = new QueryFilter(Hashlist::ACCESS_GROUP_ID, $group->getId(), "=");
    $uS = new UpdateSet(Hashlist::ACCESS_GROUP_ID, $default->getId());
    Factory::getHashlistFactory()->massUpdate([Factory::FILTER => $qF, Factory::UPDATE => $uS]);

    // update associations of files with this group
    $qF = new QueryFilter(File::ACCESS_GROUP_ID, $group->getId(), "=");
    $uS = new UpdateSet(File::ACCESS_GROUP_ID, $default->getId());
    Factory::getFileFactory()->massUpdate([Factory::FILTER => $qF, Factory::UPDATE => $uS]);
    
    // delete all associations to users
    $qF = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $group->getId(), "=");
    Factory::getAccessGroupUserFactory()->massDeletion([Factory::FILTER => $qF]);
    
    // delete all associations to agents
    $qF = new QueryFilter(AccessGroupAgent::ACCESS_GROUP_ID, $group->getId(), "=");
    Factory::getAccessGroupAgentFactory()->massDeletion([Factory::FILTER => $qF]);
    
    // delete access group
    Factory::getAccessGroupFactory()->delete($group);
  }
  
  /**
   * @param int $groupId
   * @return AccessGroup
   * @throws HTException
   */
  public static function getGroup($groupId) {
    $group = Factory::getAccessGroupFactory()->get($groupId);
    if ($group === null) {
      throw new HTException("Invalid group!");
    }
    return $group;
  }
}
