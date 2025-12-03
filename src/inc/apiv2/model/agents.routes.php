<?php

use DBA\ContainFilter;
use DBA\Factory;

use DBA\AccessGroup;
use DBA\AccessGroupAgent;
use DBA\Agent;
use DBA\AgentError;
use DBA\AgentStat;
use DBA\Assignment;
use DBA\Chunk;
use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\Task;
use DBA\User;
use JetBrains\PhpStorm\NoReturn;

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
  
  protected function getUpdateHandlers($id, $current_user): array {
    return [
      Agent::IGNORE_ERRORS => fn($value) => AgentUtils::changeIgnoreErrors($id, $value, $current_user),
    ];
  }
  
  /**
   * Overridable function to aggregate data in the object. active chunk of agent is appended to
   * $included_data.
   *
   * @param object $object the agent object were data is aggregated from
   * @param array &$includedData
   * @return array not used here 
   */
  static function aggregateData(object $object, array &$included_data = [], array $aggregateFieldsets = null): array {
    $agentId = $object->getId();
    $qFs = [];
    $qFs[] = new QueryFilter(Chunk::AGENT_ID, $agentId, "=");
    $qFs[] = new QueryFilter(Chunk::STATE, DHashcatStatus::RUNNING, "=");

    $active_chunk = Factory::getChunkFactory()->filter([Factory::FILTER => $qFs], true);
    if ($active_chunk !== NULL) {
      $included_data["chunks"][$agentId] = [$active_chunk];
    }

    return [];
  }
  
  protected function getSingleACL(User $user, object $object): bool {
    $accessGroupsUser = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user));
    /** @var Agent $object */
    $accessGroupsAgent = Util::arrayOfIds(AccessUtils::getAccessGroupsOfAgent($object));
    
    return count(array_intersect($accessGroupsAgent, $accessGroupsUser)) > 0;
  }
  
  protected function getFilterACL(): array {
    $accessGroups = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getCurrentUser()));
    
    return [
      Factory::JOIN => [
        new JoinFilter(Factory::getAccessGroupAgentFactory(), Agent::AGENT_ID, AccessGroupAgent::AGENT_ID)
      ], Factory::FILTER => [new ContainFilter(AccessGroupAgent::ACCESS_GROUP_ID, $accessGroups),
      ]
    ];
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
      'agentErrors' => [
        'key' => Agent::AGENT_ID,
        
        'relationType' => AgentError::class,
        'relationKey' => AgentError::AGENT_ID,
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
  
  #[NoReturn] protected function createObject(array $data): int {
    assert(False, "Agents cannot be created via API");
  }
  
  /**
   * @throws HTException
   */
  protected function deleteObject(object $object): void {
    AgentUtils::delete($object->getId(), $this->getCurrentUser());
  }
}

AgentAPI::register($app);