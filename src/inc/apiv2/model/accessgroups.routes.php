<?php

use DBA\AccessGroup;
use DBA\AccessGroupAgent;
use DBA\AccessGroupUser;
use DBA\Agent;
use DBA\User;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class AccessGroupAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/accessgroups";
  }
  
  public static function getDBAclass(): string {
    return AccessGroup::class;
  }
  
  public static function getToManyRelationships(): array {
    return [
      'userMembers' => [
        'key' => AccessGroup::ACCESS_GROUP_ID,
        
        'junctionTableType' => AccessGroupUser::class,
        'junctionTableFilterField' => AccessGroupUser::ACCESS_GROUP_ID,
        'junctionTableJoinField' => AccessGroupUser::USER_ID,
        
        'relationType' => User::class,
        'relationKey' => User::USER_ID,
      ],
      'agentMembers' => [
        'key' => AccessGroup::ACCESS_GROUP_ID,
        
        'junctionTableType' => AccessGroupAgent::class,
        'junctionTableFilterField' => AccessGroupAgent::ACCESS_GROUP_ID,
        'junctionTableJoinField' => AccessGroupAgent::AGENT_ID,
        
        'relationType' => Agent::class,
        'relationKey' => Agent::AGENT_ID,
      ],
    ];
  }
  
  
  /**
   * @throws HTException
   */
  protected function createObject(array $data): int {
    $object = AccessGroupUtils::createGroup($data[AccessGroup::GROUP_NAME]);
    return $object->getId();
  }
  
  /**
   * @throws HTException
   */
  protected function deleteObject(object $object): void {
    AccessGroupUtils::deleteGroup($object->getId());
  }
}

AccessGroupAPI::register($app);