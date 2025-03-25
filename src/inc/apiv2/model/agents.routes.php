<?php
use DBA\Factory;

use DBA\AccessGroup;
use DBA\AccessGroupAgent;
use DBA\Agent;
use DBA\AgentStat;
use DBA\Assignment;
use DBA\Chunk;
use DBA\Task;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class AgentAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/agents";
    }

    public static function getAvailableMethods(): array {
      return ['GET', 'PATCH', 'DELETE'];
    }

    public static function getDBAclass(): string {
      return Agent::class;
    }

    public static function getToManyRelationships(): array {
      return [
        'accessGroups' => [
          'key' => Agent::AGENT_ID,
          
          'junctionTableType' => AccessGroupAgent::class,
          'junctionTableFilterField' => AccessGroupAgent::AGENT_ID,
          'junctionTableJoinField' => AccessGroupAgent::ACCESS_GROUP_ID,

          'relationType' => AccessGroup::class,
          'relationKey' => AccessGroup::ACCESS_GROUP_ID,        
        ],
        'agentStats' => [
          'key' => Agent::AGENT_ID,
          
          'relationType' => AgentStat::class,
          'relationKey' => AgentStat::AGENT_ID,        
        ],
        'chunks' => [
          'key' => Agent::AGENT_ID,

          'relationType' => Chunk::class,
          'relationKey' => Chunk::AGENT_ID,        
        ],
        'tasks' => [
          'key' => Agent::AGENT_ID,
          
          'junctionTableType' => Assignment::class,
          'junctionTableFilterField' => Assignment::AGENT_ID,
          'junctionTableJoinField' => Assignment::TASK_ID,

          'relationType' => Task::class,
          'relationKey' => Task::TASK_ID,        
        ],
        'assignments' => [
          'key' => Agent::AGENT_ID,

          'relationType' => Assignment::class,
          'relationKey' => Assignment::AGENT_ID,        
        ],
        

      ];
    }
   
    protected function createObject(array $data): int {
      assert(False, "Agents cannot be created via API");
      return -1;
    }

    protected function deleteObject(object $object): void {
      AgentUtils::delete($object->getId(), $this->getCurrentUser());
    }
}

AgentAPI::register($app);