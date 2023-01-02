<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\CrackerBinary;
use DBA\Factory;

require_once(dirname(__FILE__) . "/shared.inc.php");


class CrackerBinaryAPI extends AbstractBaseAPI {
    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public function getFeatures(): array {
      return CrackerBinary::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getCrackerBinaryFactory();
    }

    public function getExpandables(): array {
      return ["crackerBinaryType"];
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
      $object = CrackerUtils::createBinary(
        $QUERY[CrackerBinary::VERSION],
        $QUERY[CrackerBinary::BINARY_NAME],
        $QUERY[CrackerBinary::DOWNLOAD_URL],
        $QUERY[CrackerBinary::CRACKER_BINARY_TYPE_ID]
      );

      return $object->getId();
    }

    protected function deleteObject(object $object): void {
      Factory::getCrackerBinaryFactory()->delete($object);
    }
}


$app->group("/api/v2/ui/crackers", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \CrackerBinaryAPI::class . ':get');
    $group->post('', \CrackerBinaryAPI::class . ':post');
});


$app->group("/api/v2/ui/crackers/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \CrackerBinaryAPI::class . ':getOne');
    $group->patch('', \CrackerBinaryAPI::class . ':patchOne');
    $group->delete('', \CrackerBinaryAPI::class . ':deleteOne');
});