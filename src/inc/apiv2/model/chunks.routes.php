<?php

use DBA\AccessGroupAgent;
use DBA\ContainFilter;
use DBA\Factory;

use DBA\Agent;
use DBA\Chunk;
use DBA\Hashlist;
use DBA\JoinFilter;
use DBA\Task;
use DBA\TaskWrapper;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


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
  
    protected function getFilterACL(): array {
      $accessGroups = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getCurrentUser()));
      
      return [
        Factory::JOIN => [
          new JoinFilter(Factory::getAccessGroupAgentFactory(), Chunk::AGENT_ID, AccessGroupAgent::AGENT_ID),
          new JoinFilter(Factory::getTaskFactory(), Chunk::TASK_ID, Task::TASK_ID),
          new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID, Factory::getTaskFactory()),
          new JoinFilter(Factory::getHashlistFactory(), TaskWrapper::HASHLIST_ID, Hashlist::HASHLIST_ID, Factory::getTaskWrapperFactory()),
        ],
        Factory::FILTER => [
          new ContainFilter(AccessGroupAgent::ACCESS_GROUP_ID, $accessGroups, Factory::getAccessGroupAgentFactory()),
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

    protected function createObject(array $data): int {
      /* Dummy code to implement abstract functions */
      assert(False, "Chunks cannot be created via API");
      return -1;
    }

    public function updateObject(int $objectId, array $data): void {
      assert(False, "Chunks cannot be updated via API");
    }

    protected function deleteObject(object $object): void {
      /* Dummy code to implement abstract functions */
      assert(False, "Chunks cannot be deleted via API");
    }
}

ChunkAPI::register($app);