<?php
use DBA\Factory;
use DBA\OrderFilter;
use DBA\QueryFilter;

use DBA\AccessGroup;
use DBA\AccessGroupUser;
use DBA\RightGroup;
use DBA\User;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class UserAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/users";
    }

    public static function getDBAclass(): string {
      return User::class;
    }

    public static function getToOneRelationships(): array {
      return [
        'globalPermissionGroup' => [
          'key' => User::RIGHT_GROUP_ID, 

          'relationType' => RightGroup::class,
          'relationKey' => RightGroup::RIGHT_GROUP_ID,
        ],
      ];
    }

    public static function getToManyRelationships(): array {
      return [
        'accessGroups' => [
          'key' => User::USER_ID,
          
          'junctionTableType' => AccessGroupUser::class,
          'junctionTableFilterField' => AccessGroupUser::USER_ID,
          'junctionTableJoinField' => AccessGroupUser::ACCESS_GROUP_ID,

          'relationType' => AccessGroup::class,
          'relationKey' => AccessGroup::ACCESS_GROUP_ID,        
        ],
      ];
    }




    protected static function fetchExpandObjects(array $objects, string $expand): mixed {        
      array_walk($objects, function($obj) { assert($obj instanceof User); });

      /* Expand requested section */
      switch($expand) {
        case 'accessGroups':
          return self::getManyToManyRelationViaIntermediate(
            $objects,
            User::USER_ID,
            Factory::getAccessGroupUserFactory(),
            AccessGroupUser::USER_ID,
            Factory::getAccessGroupFactory(),
            AccessGroup::ACCESS_GROUP_ID
          );
        case 'globalPermissionGroup':
          return self::getForeignKeyRelation(
            $objects,
            User::RIGHT_GROUP_ID,
            Factory::getRightGroupFactory(),
            RightGroup::RIGHT_GROUP_ID
          );
        default:
          throw new BadFunctionCallException("Internal error: Expansion '$expand' not implemented!");
      }
    }  

    protected function createObject($data): int {
      UserUtils::createUser(
          $data[User::USERNAME],
          $data[User::EMAIL],
          $data[User::RIGHT_GROUP_ID],
          $this->getCurrentUser()
      );

      /* Hackish way to retreive object since Id is not returned on creation */
      $qFs = [
        new QueryFilter(User::USERNAME, $data[USER::USERNAME], '='),
        new QueryFilter(User::EMAIL, $data[User::EMAIL], '='),
        new QueryFilter(User::RIGHT_GROUP_ID, $data[User::RIGHT_GROUP_ID], '=')
      ];

      $oF = new OrderFilter(User::USER_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      assert(count($objects) == 1);

      return $objects[0]->getId();
    }

    function getAllPostParameters(array $features): array {

      $features = parent::getAllPostParameters($features);
      unset($features[User::IS_VALID]);
      return $features;
    }

    protected function deleteObject(object $object): void {
      UserUtils::deleteUser($object->getId(), $this->getCurrentUser());
    }

    public function updateObject(object $object, $data, $processed = []): void {    
      $key = USER::RIGHT_GROUP_ID;
      if (array_key_exists($key, $data)) {
        array_push($processed, $key);
        UserUtils::setRights($object->getId(), $data[$key], $this->getCurrentUser());
      }

      $key = USER::IS_VALID;
      if (array_key_exists($key, $data)) {
        array_push($processed, $key);
        if ($data[$key] == True) {
          UserUtils::enableUser($object->getId());
        } else {
          UserUtils::disableUser($object->getId(), $this->getCurrentUser());
        }
      }

      parent::updateObject($object, $data, $processed);
    }

}

UserAPI::register($app);