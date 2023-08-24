<?php
use DBA\Factory;
use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;

use DBA\AccessGroup;
use DBA\AccessGroupUser;
use DBA\User;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class UserAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/users";
    }

    public static function getDBAclass(): string {
      return User::class;
    }

    protected function getFactory(): object {
      return Factory::getUserFactory();
    }

    public function getExpandables(): array {
      return ["accessGroups", "globalPermissionGroup"];
    }

    protected function doExpand(object $object, string $expand): mixed {
      assert($object instanceof User);
      switch($expand) {
        case 'accessGroups':
          $qF = new QueryFilter(AccessGroupUser::USER_ID, $object->getId(), "=", Factory::getAccessGroupUserFactory());
          $jF = new JoinFilter(Factory::getAccessGroupUserFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupUser::ACCESS_GROUP_ID);
          return $this->joinQuery(Factory::getAccessGroupFactory(), $qF, $jF);
      case 'globalPermissionGroup':
          $obj = Factory::getRightGroupFactory()->get($object->getRightGroupId());
          return $this->obj2Array($obj);   
      }
    }  

    protected function createObject($data): int {
      UserUtils::createUser(
          $data[User::USERNAME],
          $data[User::EMAIL],
          $data[User::RIGHT_GROUP_ID],
          $this->getUser()
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


    protected function deleteObject(object $object): void {
      UserUtils::deleteUser($object->getId(), $this->getUser());
    }

    public function updateObject(object $object, $data, $processed = []): void {    
      $key = USER::RIGHT_GROUP_ID;
      if (array_key_exists($key, $data)) {
        array_push($processed, $key);
        UserUtils::setRights($object->getId(), $data[$key], $this->getUser());
      }

      $key = USER::IS_VALID;
      if (array_key_exists($key, $data)) {
        array_push($processed, $key);
        if ($data[$key] == True) {
          UserUtils::enableUser($object->getId());
        } else {
          UserUtils::disableUser($object->getId(), $this->getUser());
        }
      }

      parent::updateObject($object, $data, $processed);
    }

}

UserAPI::register($app);