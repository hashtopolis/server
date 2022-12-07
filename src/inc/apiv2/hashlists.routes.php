<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Hashlist;
use DBA\Factory;
use DBA\ContainFilter;

use Middlewares\Utils\HttpErrorException;

require_once(dirname(__FILE__) . "/shared.inc.php");


class HashlistAPI extends AbstractBaseAPI {
    protected function getPermission(): string {
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    protected function getFeatures(): array {
      return Hashlist::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getHashlistFactory();
    }

    protected function getExpandables(): array {
      return ["accessGroup", "hashType", "hashes"];
    }

    protected function getFilterACL(): array {
      return [new ContainFilter(Hashlist::ACCESS_GROUP_ID, Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getUser())))];
    }

    protected function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [
        UQueryHashlist::HASHLIST_SEPARATOR => ['type' => 'str'],
        "sourceType" => ['type' => 'str'],
        "sourceData" => ['type' => 'str'],
      ];
    }

    protected function checkPermission(object $object): bool
    {
      return AccessUtils::userCanAccessHashlists($object, $this->getUser());
    }
    

    protected function createObject($QUERY): int {
      // Cast to createHashlist compatible upload format
      $dummyPost = [];
      switch ($QUERY["sourceType"]) {
        case "paste":
          $dummyPost["hashfield"] = base64_decode($QUERY[UQueryHashlist::HASHLIST_DATA]);
          break;
        case "import":
          $dummyPost["importfile"] = $QUERY["sourceData"];
          break;
        case "url":
          $dummyPost["url"] = $QUERY["sourceData"];
          break;
        default:
          // TODO: Choice validation are model based checks
          throw new HttpErrorException("sourceType value '" . $QUERY["sourceType"] . "' is not supported (choices paste, import, url");
      }

      // TODO: validate input is valid base64 encoded
      if ($QUERY["sourceType"] == "paste") {
        if (strlen($QUERY["sourceData"]) == 0) {
          // TODO: Should be 400 instead
          throw new HttpErrorException("sourceType=paste, requires sourceData to be non-empty");
        }
      }
      
      $hashlist = HashlistUtils::createHashlist(
        $QUERY[UQueryHashlist::HASHLIST_NAME],
        $QUERY[UQueryHashlist::HASHLIST_IS_SALTED],
        $QUERY[UQueryHashlist::HASHLIST_IS_SECRET],
        $QUERY[UQueryHashlist::HASHLIST_HEX_SALTED],
        $QUERY[UQueryHashlist::HASHLIST_SEPARATOR],
        $QUERY[UQueryHashlist::HASHLIST_FORMAT],
        $QUERY[UQueryHashlist::HASHLIST_HASHTYPE_ID],
        (array_key_exists("saltSeperator", $QUERY)) ? $QUERY["saltSeparator"] : $QUERY[UQueryHashlist::HASHLIST_SEPARATOR],
        $QUERY[UQueryHashlist::HASHLIST_ACCESS_GROUP_ID],
        $QUERY["sourceType"],
        $dummyPost,
        [],
        $this->getUser(),
        $QUERY[UQueryHashlist::HASHLIST_USE_BRAIN],
        $QUERY[UQueryHashlist::HASHLIST_BRAIN_FEATURES]
      );

      // Modify fields not set on hashlist creation
      if (array_key_exists("notes", $QUERY)) {
        HashlistUtils::editNotes($hashlist->getId(), $QUERY["notes"], $this->getUser());
      };
      HashlistUtils::setArchived($hashlist->getId(), $QUERY[UQueryHashlist::HASHLIST_IS_ARCHIVED], $this->getUser());

      return $hashlist->getId();
    }

    protected function deleteObject(object $object): void {
      HashlistUtils::delete($object->getId(), $this->getUser());
    }
}


$app->group("/api/v2/ui/hashlists", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \HashlistAPI::class . ':get');
    $group->post('', \HashlistAPI::class . ':post');
});


$app->group("/api/v2/ui/hashlists/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \HashlistAPI::class . ':getOne');
    $group->patch('', \HashlistAPI::class . ':patchOne');
    $group->delete('', \HashlistAPI::class . ':deleteOne');
});