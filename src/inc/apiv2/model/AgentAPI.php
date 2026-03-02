<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\inc\utils\AgentUtils;
use Hashtopolis\inc\defines\DHashcatStatus;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;

use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\AccessGroupAgent;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\AgentError;
use Hashtopolis\dba\models\AgentStat;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\Util;


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
      Agent::AGENT_NAME => fn($value) => AgentUtils::rename($id, $value, $current_user),
    ];
  }
  
  /**
   * Overridable function to aggregate data in the object. active chunk of agent is appended to
   * $included_data.
   *
   * @param object $object the agent object were data is aggregated from
   * @param array &$included_data
   * @param array|null $aggregateFieldsets
   * @return array not used here
   */
  static function aggregateData(object $object, array &$included_data = [], ?array $aggregateFieldsets = null): array {
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
      ], Factory::FILTER => [new ContainFilter(AccessGroupAgent::ACCESS_GROUP_ID, $accessGroups, Factory::getAccessGroupAgentFactory()),
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
  
  public static function getToOneRelationships(): array {
    return [
      'user' => [
        'key' => Agent::USER_ID,
        
        'relationType' => User::class,
        'relationKey' => User::USER_ID,
      ],
    ];
  }
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    throw new HttpError("Agents cannot be created via API");
  }
  
  /**
   * @throws HTException
   */
  protected function deleteObject(object $object): void {
    AgentUtils::delete($object->getId(), $this->getCurrentUser());
  }
}
