<?php

namespace Hashtopolis\inc\apiv2\model;

use Exception;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Aggregation;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\inc\utils\AgentUtils;
use Hashtopolis\inc\defines\DHashcatStatus;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\ExistsFilter;
use Hashtopolis\dba\Factory;

use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\AccessGroupAgent;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\AgentError;
use Hashtopolis\dba\models\AgentStat;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\Util;


/**
 * @extends AbstractModelAPI<Agent>
 */
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
  
  public function getAggregateFieldsets(): array {
    return [
      'agent' => [
        'crackingTime' => [$this, 'getAggregateCrackingTime'],
      ]
    ];
  }
  
  /**
   * @param Agent $object
   * @return int
   * @throws Exception
   */
  protected function getAggregateCrackingTime(AbstractModel $object): int {
    // in order to make sense of the diff, we need to make sure that both values solve time and dispatch time are set (i.e. >0).
    $qF1 = new QueryFilter(Chunk::AGENT_ID, $object->getId(), "=");
    $qF2 = new QueryFilter(Chunk::SOLVE_TIME, 0, ">");
    $qF3 = new QueryFilter(Chunk::DISPATCH_TIME, 0, ">");
    $agg1 = new Aggregation(Chunk::SOLVE_TIME, Aggregation::SUM);
    $agg2 = new Aggregation(Chunk::DISPATCH_TIME, Aggregation::SUM);
    $results = Factory::getChunkFactory()->multicolAggregationFilter([Factory::FILTER => [$qF1, $qF2, $qF3]], [$agg1, $agg2]);
    return $results[$agg1->getName()] - $results[$agg2->getName()];
  }
  
  /**
   * Overridable function to aggregate data in the object. active chunk of agent is appended to
   * $included_data.
   *
   * @param AbstractModel $object the agent object were data is aggregated from
   * @param array &$includedData
   * @param array|null $aggregateFieldsets
   * @return array not used here
   * @throws Exception
   */
  function aggregateData(AbstractModel $object, array &$includedData = [], ?array $aggregateFieldsets = null): array {
    $agentId = $object->getId();
    $qFs = [];
    $qFs[] = new QueryFilter(Chunk::AGENT_ID, $agentId, "=");
    $qFs[] = new QueryFilter(Chunk::STATE, DHashcatStatus::RUNNING, "=");
    
    $active_chunk = Factory::getChunkFactory()->filter([Factory::FILTER => $qFs], true);
    if ($active_chunk !== NULL) {
      $includedData["chunks"][$agentId] = [$active_chunk];
    }
    
    return parent::aggregateData($object, $includedData, $aggregateFieldsets);
  }
  
  /**
   * @param Agent $object
   * @throws Exception
   */
  protected function getSingleACL(User $user, AbstractModel $object): bool {
    $accessGroupsUser = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user));
    /** @var Agent $object */
    $accessGroupsAgent = Util::arrayOfIds(AccessUtils::getAccessGroupsOfAgent($object));
    
    return count(array_intersect($accessGroupsAgent, $accessGroupsUser)) > 0;
  }
  
  /**
   * @throws Exception
   */
  protected function getFilterACL(): array {
    $accessGroups = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getCurrentUser()));
    
    return [
      Factory::FILTER => [
        new ExistsFilter(Factory::getAccessGroupAgentFactory(), AccessGroupAgent::AGENT_ID, Agent::AGENT_ID, [new ContainFilter(AccessGroupAgent::ACCESS_GROUP_ID, $accessGroups, Factory::getAccessGroupAgentFactory())])
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
   * @param Agent $object
   * @throws HTException
   */
  protected function deleteObject(AbstractModel $object): void {
    AgentUtils::delete($object->getId(), $this->getCurrentUser());
  }
}
