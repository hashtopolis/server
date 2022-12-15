<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Factory;
use DBA\File;
use DBA\QueryFilter;
use DBA\OrderFilter;
use Middlewares\Utils\HttpErrorException;

require_once(dirname(__FILE__) . "/shared.inc.php");


class FileAPI extends AbstractBaseAPI {
    protected function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    protected function getFeatures(): array {
      return File::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getFileFactory();
    }

    protected function getExpandables(): array {
      return ["accessGroup"];
    }

    protected function getFilterACL(): array {
      return [];
    }

    protected function getFormFields(): array {
      // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
      return [
        "sourceType" => ['type' => 'str'],
        "sourceData" => ['type' => 'str']
      ];
    }

    protected function checkPermission(object $object): bool
    {
      return true;
    }

    static protected function getImportPath(): string
    {
      return dirname(__file__) . '/../../import/';
    }
    
    static protected function getFilesPath(): string
    {
      return dirname(__file__) . '/../../files/';
    }

    /* Includes: 
     * Experimental support for renaming import file to target file 
     */
    protected function createObject($QUERY): int {
      /* Validate target filename */  
      $realname = str_replace(" ", "_", htmlentities(basename($QUERY[File::FILENAME]), ENT_QUOTES, "UTF-8"));
      if ($QUERY[File::FILENAME] != $realname) {
        throw new HttpErrorException(File::FILENAME . " is invalid filename suggestion '$realname'");
      }

      /* Pre-checking to allow saving some time in repairing edge cases */
      if (file_exists($this->getFilesPath() . $QUERY[File::FILENAME])) {
        throw new HttpErrorException("File '" . $QUERY[File::FILENAME] . "' already exists in 'files' folder, cannot continue!");
      }

      /* Prepare dummy request for insert */
      $dummyPost = [ 
        "filename" => $QUERY[File::FILENAME],
        "accessGroupId" => $QUERY[File::ACCESS_GROUP_ID],
      ];
      switch ($QUERY["sourceType"]) {
        case "inline":
          // TODO: Should be validated as parameter input instead
          $decoded = base64_decode($QUERY["sourceData"], true);
          if ($decoded === false) {
            throw new HttpErrorException("sourceData not valid base64 encoding");
          }
          $dummyPost["data"] = $decoded;
          break;
        case "import":
          $realname = str_replace(" ", "_", htmlentities(basename($QUERY["sourceData"]), ENT_QUOTES, "UTF-8"));
          if ($QUERY["sourceData"] != $realname) {
            throw new HttpErrorException("sourceData is invalid filename suggestion '$realname'");
          }
          /* Renaming files will require target file to be checked before renaming */
          if (!file_exists($this->getImportPath() . $QUERY["sourceData"])) {
            throw new HttpErrorException("File '" . $QUERY["sourceData"] . "' not found in import folder");
          }
          if (file_exists($this->getImportPath() . $QUERY[File::FILENAME])) {
            throw new HttpErrorException("File required temponary file '" . $QUERY[File::FILENAME] . "' exists import folder, cannot continue");
          }
          /* Since we are renaming the file _before_ import the name is temponary changed */
          $dummyPost["imfile"] = [$QUERY[File::FILENAME]];
          break;
        default:
          // TODO: Choice validation are model based checks
          throw new HttpErrorException("sourceType value '" . $QUERY["sourceType"] . "' is not supported (choices inline, import");
      }

      /* TODO: Hackish view to revert back to required (hardcoded) view */
      $view = [ 
        DFileType::OTHER => 'other',
        DFileType::RULE => 'rule',
        DFileType::WORDLIST => 'dict'
      ][$QUERY[File::FILE_TYPE]];


      /* Prepare renaming file if required */
      $doRenameImport = (($QUERY["sourceType"] == "import") && ($QUERY[File::FILENAME] != $QUERY["sourceData"]));
      if ($doRenameImport) {
        rename(
          $this->getImportPath() . $QUERY["sourceData"],
          $this->getImportPath() . $QUERY[File::FILENAME]
        );
      };

      try {
        /* Create the file, calculating (e.g. lines) and checking validity (e.g. file exists) */
        FileUtils::add($QUERY["sourceType"], $QUERY[File::FILENAME], $dummyPost, $view);
      } catch (Exception $e) {
        /* In case of errors, ensure old state is restored */
        if (($QUERY["sourceType"] == "import") && ($QUERY[File::FILENAME] != $QUERY["sourceData"])) {
          rename(
            $this->getImportPath() . $QUERY[File::FILENAME],
            $this->getImportPath() . $QUERY["sourceData"]
          );
        };
        throw $e;
      }

      /* Hackish way to retrieve object since Id is not returned on creation */
      $qFs = [
        new QueryFilter(File::FILENAME, $QUERY[File::FILENAME], '='),
        new QueryFilter(File::FILE_TYPE, $QUERY[File::FILE_TYPE], '='),
        new QueryFilter(File::ACCESS_GROUP_ID, $QUERY[File::ACCESS_GROUP_ID], '=')
      ];
      $oF = new OrderFilter(File::FILE_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      assert(count($objects) == 1);

      /* Manually set secret, since it not set when adding file */
      FileUtils::switchSecret($objects[0]->getId(), ($QUERY[File::IS_SECRET]) ? 1 : 0, $this->getUser());

      /* On succesfully insert, return ID */
      return $objects[0]->getId();
    }


    protected function deleteObject(object $object): void {
      FileUtils::delete($object->getId(), $this->getUser());
    }
}


$app->group("/api/v2/ui/files", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \FileAPI::class . ':get');
    $group->post('', \FileAPI::class . ':post');
});


$app->group("/api/v2/ui/files/{id:[0-9]+}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \FileAPI::class . ':getOne');
    $group->patch('', \FileAPI::class . ':patchOne');
    $group->delete('', \FileAPI::class . ':deleteOne');
});