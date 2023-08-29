<?php
use DBA\Factory;
use DBA\JoinFilter;
use DBA\QueryFilter;

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

    public function getExpandables(): array {
      return ["userMembers", "agentMembers"];
    }

    protected function doExpand(object $object, string $expand): mixed {
      assert($object instanceof AccessGroup);
      switch($expand) {
        case 'userMembers':
          $qF = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $object->getId(), "=", Factory::getAccessGroupUserFactory());
          $jF = new JoinFilter(Factory::getAccessGroupUserFactory(), User::USER_ID, AccessGroupUser::USER_ID);
          return $this->joinQuery(Factory::getUserFactory(), $qF, $jF);
        case 'agentMembers':
          $qF = new QueryFilter(AccessGroupAgent::ACCESS_GROUP_ID, $object->getId(), "=", Factory::getAccessGroupAgentFactory());
          $jF = new JoinFilter(Factory::getAccessGroupAgentFactory(), Agent::AGENT_ID, AccessGroupAgent::AGENT_ID);
          return $this->joinQuery(Factory::getAgentFactory(), $qF, $jF);
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