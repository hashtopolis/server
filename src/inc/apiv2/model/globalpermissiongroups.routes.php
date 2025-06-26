<?php
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
     * Temporary exception until old API is removed and we
     * are allowed to write CRUD permissions to database
     */
    protected static function db2json(array $feature, mixed $val): mixed {
      if ($feature['alias'] == 'permissions') {
        return AccessUtils::getPermissionArrayConverted($val);
      } else {
        // Consider all other fields normal conversions
        return parent::db2json($feature, $val);
      }
    }
  
  /**
   * @throws ResourceNotFoundError
   * @throws HttpForbidden
   * @throws HttpError
   */
  protected function createObject(array $data): int {
      $group = AccessControlUtils::createGroup($data[RightGroup::GROUP_NAME]);
      $id = $group->getId();

      // The utils function does not allow to set permissions directly. This call is to workaround this.
      // This causes the issue that if some error happens during updating the object the object is still created
      // but the permissions will not be set.
      $this->updateObject($id, $data);
      
      return $id;
    }
  
  /**
   * @throws HttpError
   */
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
   * @throws HTException
   */
    private function updatePermissions($id, $value): void {
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

      $legacyPerms = [];
      foreach($permissions as $crudPerm => $value) {
        if (array_key_exists($crudPerm, $c2o)) {
          $filled_perms = array_fill_keys($c2o[$crudPerm], $value);
          $legacyPerms = array_merge($legacyPerms, $filled_perms);
        }
      }

      AccessControlUtils::addToPermissions($id, $legacyPerms);
    }
}

GlobalPermissionGroupAPI::register($app);