<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\UpdateSet;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\AccessGroupUser;
use Hashtopolis\dba\models\AccessGroupAgent;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\File;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\error\HttpConflict;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\defines\DHashcatStatus;
use Hashtopolis\inc\defines\DLimits;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\Util;

class AccessGroupUtils {
  /**
   * @param int $groupId
   * @return AccessGroupUser[]
   * @throws Exception
   */
  public static function getUsers(int $groupId): array {
    $qF = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $groupId, "=");
    return Factory::getAccessGroupUserFactory()->filter([Factory::FILTER => $qF]);
  }
  
  /**
   * @param int $groupId
   * @return AccessGroupAgent[]
   * @throws Exception
   */
  public static function getAgents(int $groupId): array {
    $qF = new QueryFilter(AccessGroupAgent::ACCESS_GROUP_ID, $groupId, "=");
    return Factory::getAccessGroupAgentFactory()->filter([Factory::FILTER => $qF]);
  }
  
  /**
   * @return AccessGroup[]
   * @throws Exception
   */
  public static function getGroups(): array {
    return Factory::getAccessGroupFactory()->filter([]);
  }
  
  /**
   * @param string $groupName
   * @return AccessGroup
   * @throws HttpError
   * @throws HttpConflict
   * @throws Exception
   */
  public static function createGroup(string $groupName): AccessGroup {
    if (strlen($groupName) == 0 || strlen($groupName) > DLimits::ACCESS_GROUP_MAX_LENGTH) {
      throw new HttpError("Access group name is too short or too long!");
    }
    
    $qF = new QueryFilter(AccessGroup::GROUP_NAME, $groupName, "=");
    $check = Factory::getAccessGroupFactory()->filter([Factory::FILTER => $qF], true);
    if ($check !== null) {
      throw new HttpConflict("There is already an access group with the same name!");
    }
    $group = new AccessGroup(null, $groupName);
    return Factory::getAccessGroupFactory()->save($group);
  }
  
  /**
   * @throws HTException
   * @throws Exception
   */
  public static function rename(int $accessGroupId, string $newname): void {
    $accessGroup = AccessGroupUtils::getGroup($accessGroupId);
    $name = htmlentities($newname, ENT_QUOTES, "UTF-8");
    if (strlen($name) == 0) {
      throw new HTException("AccessGroup name cannot be empty!");
    }
    Factory::getAccessGroupFactory()->set($accessGroup, AccessGroup::GROUP_NAME, $name);
  }
  
  /**
   * @param int $groupId
   * @param User $user
   * @throws HTException
   * @throws Exception
   */
  public static function abortChunksGroup(int $groupId, User $user): void {
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
   * @throws Exception
   */
  public static function addAgent(int $agentId, int $groupId): void {
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
   * @throws Exception
   */
  public static function addUser(int $userId, int $groupId): void {
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
   * @throws Exception
   */
  public static function removeAgent(int $agentId, int $groupId): void {
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
   * @throws Exception
   */
  public static function removeUser(int $userId, int $groupId): void {
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
   * @throws Exception
   */
  public static function deleteGroup(int $groupId): void {
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
   * @throws Exception
   */
  public static function getGroup(int $groupId): AccessGroup {
    $group = Factory::getAccessGroupFactory()->get($groupId);
    if ($group === null) {
      throw new HTException("Invalid group!");
    }
    return $group;
  }
}
