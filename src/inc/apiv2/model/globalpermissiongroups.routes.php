<?php
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;

use DBA\User;
use DBA\RightGroup;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class GlobalPermissionGroupsAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/globalpermissiongroups";
    }

    public static function getDBAclass(): string {
      return RightGroup::class;
    }    

    protected function getFactory(): object {
      return Factory::getRightGroupFactory();
    }

    public function getExpandables(): array {
      return ['user'];
    }

    protected function doExpand(object $object, string $expand): mixed {
      assert($object instanceof RightGroup);
      switch($expand) {
        case 'user':
          $qF = new QueryFilter(User::RIGHT_GROUP_ID, $object->getId(), "=");
          return $this->filterQuery(Factory::getUserFactory(), $qF);
      }
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
    
    protected function createObject(array $data): int {
      $group = AccessControlUtils::createGroup($data[RightGroup::GROUP_NAME]);

      // The utils function does not allow to set permissions directly. This call is to workaround this.
      // This causes the issue that if some error happens during updating the object the object is still created
      // but the permissions will not be set.
      $this->updateObject($group, $data);
      
      return $group->getId();
    }

    protected function deleteObject(object $object): void {
      AccessControlUtils::deleteGroup($object->getId());
    }


    /**
     * NOTE: If ANY CRUD-permission is satisfied the corresponding OLD-permission is set
     */
    public function updateObject(object $object, $data, $processed = []): void {    
      /* Use quirk on 'permissions' since this is casted to 'incorrect' DB representation already */
      $permissions = unserialize($data[RightGroup::PERMISSIONS]);
      $key = RightGroup::PERMISSIONS;
      if (array_key_exists($key, $data)) {
        array_push($processed, $key);

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
        
        // Get enabled 'old-style' permissions
        $legacyPerms = [];
        foreach($permissions as $crudPerm => $value) {
          if ($value === true) {
            $legacyPerms = array_merge($legacyPerms, $c2o[$crudPerm]);
          }
        }
        
        // Modify data to conform with updateGroupPermssions input
        $permData = [];
        foreach($legacyPerms as $key) {
          array_push($permData, $key . "-1");
        }
        AccessControlUtils::updateGroupPermissions($object->getId(), $permData);
      }

      parent::updateObject($object, $data, $processed);
    }
}

GlobalPermissionGroupsAPI::register($app);