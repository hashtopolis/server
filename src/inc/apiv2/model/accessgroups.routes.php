<?php
use DBA\Factory;

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

    public static function getExpandables(): array {
      return ["userMembers", "agentMembers"];
    }

    public static function getToManyRelationships(): array {
      return [
        'userMembers' => [
          'intermidiate' => AccessGroupUser::class, 
          'filterField' => AccessGroupUser::ACCESS_GROUP_ID,
          'joinField' => User::USER_ID,
          'relationType' => User::class,
        ],
        'agentMembers' => [
          'intermidiate' =>AccessGroupAgent::class, 
          'filterField' => AccessGroupAgent::ACCESS_GROUP_ID,
          'joinField' => Agent::AGENT_ID,
          'relationType' => Agent::class,
        ],
      ];
    }

    protected static function fetchExpandObjects(array $objects, string $expand): mixed {     
      /* Ensure we receive the proper type */
      array_walk($objects, function($obj) { assert($obj instanceof AccessGroup); });

      /* Expand requested section */
      switch($expand) {
        case 'userMembers':
          return self::getManyToOneRelationViaIntermediate(
            $objects,
            AccessGroup::ACCESS_GROUP_ID,
            Factory::getAccessGroupUserFactory(),
            AccessGroupUser::ACCESS_GROUP_ID,
            Factory::getUserFactory(),
            User::USER_ID
          );
        case 'agentMembers':
          return self::getManyToOneRelationViaIntermediate(
            $objects,
            AccessGroup::ACCESS_GROUP_ID,
            Factory::getAccessGroupAgentFactory(),
            AccessGroupAgent::ACCESS_GROUP_ID,
            Factory::getAgentFactory(),
            Agent::AGENT_ID
          );
        default:
          throw new BadFunctionCallException("Internal error: Expansion '$expand' not implemented!");
      }
    }

    protected function createObject(array $data): int {
      $object = AccessGroupUtils::createGroup($data[AccessGroup::GROUP_NAME]);
      return $object->getId();
    }

    protected function deleteObject(object $object): void {
      AccessGroupUtils::deleteGroup($object->getId());
    }
}

AccessGroupAPI::register($app);