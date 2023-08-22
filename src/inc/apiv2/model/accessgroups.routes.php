<?php
use DBA\Factory;
use DBA\AccessGroup;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class AccessGroupAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/accessgroups";
    }

    public static function getDBAclass(): string {
      return AccessGroup::class;
    }

    protected function getFactory(): object {
      return Factory::getAccessGroupFactory();
    }

    public function getExpandables(): array {
      return ["userMembers", "agentMembers"];
    }

    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [];
    }

    protected function createObject($mappedQuery, $QUERY): int {
      /* Parameter is used as primary key in database */

      $object = AccessGroupUtils::createGroup($QUERY[AccessGroup::GROUP_NAME]);
      return $object->getId();
    }

    protected function deleteObject(object $object): void {
      AccessGroupUtils::deleteGroup($object->getId());
    }
}

AccessGroupAPI::register($app);