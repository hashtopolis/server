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
    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public function getFeatures(): array {
      return User::getFeatures();
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


$app->group("/api/v2/ui/users", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \UserAPI::class . ':get');
    $group->post('', \UserAPI::class . ':post');
});


$app->group("/api/v2/ui/users/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \UserAPI::class . ':getOne');
    /* FIXME: Duplicate groupNames are allowed when using patches, how-ever on creation this is checked */
    $group->patch('', \UserAPI::class . ':patchOne');
    $group->delete('', \UserAPI::class . ':deleteOne');
});