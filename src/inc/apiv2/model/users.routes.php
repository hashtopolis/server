<?php
use DBA\Factory;
use DBA\User;
use DBA\QueryFilter;
use DBA\OrderFilter;

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

    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [];
    }

    protected function createObject($mappedQuery, $QUERY): int {
      /* Parameter is used as primary key in database */
      $features = $this->getFeatures();
      $object = UserUtils::createUser(
          $mappedQuery[$features[USER::USERNAME]['alias']],
          $mappedQuery[User::EMAIL],
          $mappedQuery[$features[User::RIGHT_GROUP_ID]['alias']],
          $this->getUser()
      );

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(User::USERNAME, $mappedQuery[$features[USER::USERNAME]['alias']], '='),
        new QueryFilter(User::EMAIL, $mappedQuery[User::EMAIL], '='),
        new QueryFilter(User::RIGHT_GROUP_ID, $mappedQuery[$features[User::RIGHT_GROUP_ID]['alias']], '=')
      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(User::USER_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      assert(count($objects) == 1);

      return $objects[0]->getId();
    }


    protected function deleteObject(object $object): void {
      UserUtils::deleteUser($object->getId(), $this->getUser());
    }

    public function updateObject(object $object, $data, $mappedFeatures, $processed = []): void {    
      $features = $this->getFeatures();
      $key = $features[USER::RIGHT_GROUP_ID]['alias'];

      if (array_key_exists($key, $data)) {
        array_push($processed, $key);
        UserUtils::setRights($object->getId(), $data[$key], $this->getUser());
      }

      $key = $features[USER::IS_VALID]['alias'];
      if (array_key_exists($key, $data)) {
        array_push($processed, $key);
        if ($data[$key] == True) {
          UserUtils::enableUser($object->getId());
        } else {
          UserUtils::disableUser($object->getId(), $this->getUser());
        }
      }

      parent::updateObject($object, $data, $mappedFeatures, $processed);
    }

}

UserAPI::register($app);