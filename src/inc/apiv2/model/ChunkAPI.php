<?php

namespace Hashtopolis\inc\apiv2\model;

use Exception;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\dba\models\AccessGroupAgent;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\ExistsFilter;
use Hashtopolis\dba\Factory;

use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\Util;


/**
 * @extends AbstractModelAPI<Chunk>
 */
class ChunkAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/chunks";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET'];
  }
  
  public static function getDBAclass(): string {
    return Chunk::class;
  }
  
  /**
   * @throws Exception
   */
  protected function getSingleACL(User $user, object $object): bool {
    $accessGroupsUser = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user));
    
    $qF1 = new ContainFilter(Hashlist::ACCESS_GROUP_ID, $accessGroupsUser, Factory::getHashlistFactory());
    $qF2 = new QueryFilter(Chunk::CHUNK_ID, $object->getId(), "=");
    $jF1 = new JoinFilter(Factory::getTaskFactory(), Chunk::TASK_ID, Task::TASK_ID);
    $jF2 = new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID, Factory::getTaskFactory());
    $jF3 = new JoinFilter(Factory::getHashlistFactory(), TaskWrapper::HASHLIST_ID, Hashlist::HASHLIST_ID, Factory::getTaskWrapperFactory());
    $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::JOIN => [$jF1, $jF2, $jF3]])[Factory::getChunkFactory()->getModelName()];
    
    return count($chunks) > 0;
  }
  
  /**
   * @throws Exception
   */
  protected function getFilterACL(): array {
    $accessGroups = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getCurrentUser()));
    $baseFilter = new QueryFilter(Chunk::AGENT_ID, null, "=");
    return [
      Factory::JOIN => [
        new JoinFilter(Factory::getTaskFactory(), Chunk::TASK_ID, Task::TASK_ID),
        new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID, Factory::getTaskFactory()),
        new JoinFilter(Factory::getHashlistFactory(), TaskWrapper::HASHLIST_ID, Hashlist::HASHLIST_ID, Factory::getTaskWrapperFactory()),
      ],
      Factory::FILTER => [
        // Exists filter is needed because user and agent can match in multiple accessgroups,
        // Making an inner join return too much elements, which would result in duplicate chunks being returned
        new ExistsFilter(Factory::getAccessGroupAgentFactory(), AccessGroupAgent::AGENT_ID, Chunk::AGENT_ID, [new ContainFilter(AccessGroupAgent::ACCESS_GROUP_ID, $accessGroups, Factory::getAccessGroupAgentFactory())], $baseFilter),
        new ContainFilter(Hashlist::ACCESS_GROUP_ID, $accessGroups, Factory::getHashlistFactory()),
      ]
    ];
  }
  
  public static function getToOneRelationships(): array {
    return [
      'agent' => [
        'key' => Chunk::AGENT_ID,
        
        'relationType' => Agent::class,
        'relationKey' => Agent::AGENT_ID,
      ],
      'task' => [
        'key' => Chunk::TASK_ID,
        
        'relationType' => Task::class,
        'relationKey' => Task::TASK_ID,
      ],
    ];
  }
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    throw new HttpError("Chunks cannot be created via API");
  }
  
  /**
   * @throws HttpError
   */
  public function updateObject(int $objectId, array $data): void {
    throw new HttpError("Chunks cannot be updated via API");
  }
  
  /**
   * @throws HttpError
   */
  protected function deleteObject(object $object): void {
    throw new HttpError("Chunks cannot be deleted via API");
  }
}
