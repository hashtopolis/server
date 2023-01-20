<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Factory;
use DBA\User;
use DBA\QueryFilter;
use DBA\OrderFilter;



require_once(dirname(__FILE__) . "/shared.inc.php");


class UserAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/users";
    }

    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public static function getDBAclass(): string {
      return User::class;
    }

    protected function getFactory(): object {
      return Factory::getUserFactory();
    }

    public function getExpandables(): array {
      return ["rightGroup"];
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
      /* Parameter is used as primary key in database */
      $object = UserUtils::createUser(
          $QUERY[User::USERNAME],
          $QUERY[User::EMAIL],
          $QUERY[User::RIGHT_GROUP_ID],
          $this->getUser()
      );

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(User::USERNAME, $QUERY[User::USERNAME], '='),
        new QueryFilter(User::EMAIL, $QUERY[User::EMAIL], '='),
        new QueryFilter(User::RIGHT_GROUP_ID, $QUERY[User::RIGHT_GROUP_ID], '=')
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
}

UserAPI::register($app);