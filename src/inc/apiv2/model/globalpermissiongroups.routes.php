<?php
use DBA\Factory;

use DBA\User;
use DBA\RightGroup;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class GlobalPermissionGroupAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/globalpermissiongroups";
    }

    public static function getDBAclass(): string {
      return RightGroup::class;
    }

    public static function getToManyRelationships(): array {
      return [
        'userMembers' => [
          'key' => RightGroup::RIGHT_GROUP_ID,
          
          'relationType' => User::class,
          'relationKey' => User::RIGHT_GROUP_ID,        
        ],
      ];
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
   
    protected function createObject(array $data): int {
      $group = AccessControlUtils::createGroup($data[RightGroup::GROUP_NAME]);
      $id = $group->getId();

      // The utils function does not allow to set permissions directly. This call is to workaround this.
      // This causes the issue that if some error happens during updating the object the object is still created
      // but the permissions will not be set.
      $this->updateObject($id, $data);
      
      return $id;
    }

    protected function deleteObject(object $object): void {
      AccessControlUtils::deleteGroup($object->getId());
    }

    protected function getUpdateHandlers($id, $current_user): array {
      return [
        RightGroup::PERMISSIONS => fn ($value) => $this->updatePermissions($id, $value)
      ];
    }

    /**
     * NOTE: If ANY CRUD-permission is satisfied the corresponding OLD-permission is set
     */
    private function updatePermissions($id, $value) {
      $permissions = unserialize($value);
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
      AccessControlUtils::updateGroupPermissions($id, $permData);
    }
}

GlobalPermissionGroupAPI::register($app);