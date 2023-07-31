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


class GlobalPermissionGroupsAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/globalpermissiongroups";
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

    /** 
     * Rewrite permissions DB values to CRUD field values
     * Temponary exception until old API is removed and we 
     * are allowed to write CRUD permissions to database
     */
    protected static function db2json(array $feature, mixed $val): mixed {
      if ($feature['alias'] == 'permissions') {
        $all_perms = array_unique(array_merge(...array_values(self::$acl_mapping)));

        if ($val == 'ALL') {
          // Special case ALL should set all permissions to true
          $retval_perms = array_combine($all_perms, array_fill(0,count($all_perms), true));
        }
        else {
          // Create listing of enabled permissions based on permission set in database
          $user_available_perms = array();
          foreach(json_decode($val) as $rightgroup_perm => $permission_set) {
            if ($permission_set) {
              $user_available_perms = array_unique(array_merge($user_available_perms, self::$acl_mapping[$rightgroup_perm]));
            }
          }

          // Create output document
          $retval_perms = array_combine($all_perms, array_fill(0,count($all_perms), false));
          foreach($user_available_perms as $perm) {
            $retval_perms[$perm] = True;
          }
        }
        // Ensure output is sorted for easy debugging
        ksort($retval_perms);
        return $retval_perms; 
      } else {
        // Consider all other fields normal conversions
        return parent::db2json($feature, $val);
      }
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [];
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

        /*
         * NOTE: If ANY CRUD-permission is satisfied the corresponding OLD-permission is set
         */

        // Build reverse mapping to speed-up lookups for CRUD-permission to OLD-permission
        $c2o = array();
        foreach (self::$acl_mapping as $oldPerm => $crudPerms) {
          foreach($crudPerms as $crudPerm) {
            if (array_key_exists($crudPerm, $c2o)) {
              array_push($c2o[$crudPerm], $oldPerm);
            } else {
              $c2o[$crudPerm] = [$oldPerm];
            }
          }
        }
        
        $oldPerms = [];
        foreach($data[$key] as $crudPerm => $value) {
          if ($value === true) {
            $oldPerms = array_merge($oldPerms, $c2o[$crudPerm]);
          }
        }
        // Modify data to conform with updateGroupPermssions input
        $permData = [];
        foreach($oldPerms as $key) {
          array_push($permData, $key . "-1");
        }
        AccessControlUtils::updateGroupPermissions($object->getId(), $permData);
      }

      parent::updateObject($object, $data, $mappedFeatures, $processed);
    }
}

GlobalPermissionGroupsAPI::register($app);