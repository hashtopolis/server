<?php

use DBA\AccessGroup;
use DBA\AccessGroupUser;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\RightGroup;
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;


require_once(dirname(__FILE__) . "/shared.inc.php");


class AccessPermissionGroupsAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/globalpermissiongroups";
    }

    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public static function getDBAclass(): string {
      return RightGroup::class;
    }    

    public function getFeatures(): array {
      return RightGroup::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getRightGroupFactory();
    }

    public function getExpandables(): array {
      return ['user'];
    }

    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [];
    }

    protected function checkPermission(object $object): bool
    {
      return true;
    }
    
    protected function createObject($QUERY): int {
      $features = $this->getFeatures();
      $group = AccessControlUtils::createGroup($QUERY[$features[RightGroup::GROUP_NAME]['alias']]);

      // The utils function does not allow to set permissions directly. This call is to workaround this.
      // This causes the issue that if some error happens during updating the object the object is still created
      // but the permissions will not be set.
      $mappedFeatures = $this->getMappedFeatures();
      $this->updateObject($group, $QUERY, $mappedFeatures);
      
      return $group->getId();
    }

    protected function deleteObject(object $object): void {
      AccessControlUtils::deleteGroup($object->getId());
    }

    public function updateObject(object $object, $data, $mappedFeatures, $processed = []): void {    
      $key = RightGroup::PERMISSIONS;
      if (array_key_exists($key, $data)) {
        array_push($processed, $key);
        // TODO: Modify data to conform with updateGroupPermssions input

        $permData = [];
        foreach($data[$key] as $key2 => $value) {
          array_push($permData, $key2 . "-" . (($value) ? "1" : '0'));
        }
        AccessControlUtils::updateGroupPermissions($object->getId(), $permData);
      }

      parent::updateObject($object, $data, $mappedFeatures, $processed);
    }
}

AccessPermissionGroupsAPI::register($app);