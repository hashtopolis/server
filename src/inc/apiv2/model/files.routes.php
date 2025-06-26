<?php

use DBA\AccessGroup;
use DBA\ContainFilter;
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;

use DBA\File;
use DBA\User;

include_once __DIR__ . "/../common/ErrorHandler.class.php";

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class FileAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/files";
    }
  
    public static function getDBAclass(): string {
      return File::class;
    }
  
    protected function getSingleACL(User $user, object $object): bool {
      $accessGroupsUser = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user));
      
      return in_array($object->getAccessGroupId(), $accessGroupsUser);
    }
  
    protected function getFilterACL(): array {
      $accessGroups = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getCurrentUser()));
      
      return [
        Factory::FILTER => [
          new ContainFilter(File::ACCESS_GROUP_ID, $accessGroups),
        ]
      ];
    }
    
    public static function getToOneRelationships(): array {
      return [
        'accessGroup' => [
          'key' => File::ACCESS_GROUP_ID, 

          'relationType' => AccessGroup::class,
          'relationKey' => AccessGroup::ACCESS_GROUP_ID,
        ],
      ];
    }

    public function getFormFields(): array {
      // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
      return [
        "sourceType" => ['type' => 'str'],
        "sourceData" => ['type' => 'str']
      ];
    }

    static protected function getImportPath(): string
    {
      return Factory::getStoredValueFactory()->get(DDirectories::IMPORT)->getVal() . '/';
    }
    
    static protected function getFilesPath(): string
    {
      return Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . '/';
    }

    /* Includes: 
     * Experimental support for renaming import file to target file 
     */
  /**
   * @throws HTException
   * @throws HttpError
   */
  protected function createObject(array $data): int {
      /* Validate target filename */  
      $realname = str_replace(" ", "_", htmlentities(basename($data[File::FILENAME]), ENT_QUOTES, "UTF-8"));
      if ($data[File::FILENAME] != $realname) {
        throw new HttpError(File::FILENAME . " is invalid filename suggestion '$realname'");
      }

      /* Pre-checking to allow saving some time in repairing edge cases */
      if (file_exists($this->getFilesPath() . $data[File::FILENAME])) {
        throw new HttpError("File '" . $data[File::FILENAME] . "' already exists in 'files' folder, cannot continue!");
      }

      /* Prepare dummy request for insert */
      $dummyPost = [ 
        "filename" => $data[File::FILENAME],
        "accessGroupId" => $data[File::ACCESS_GROUP_ID],
      ];
      switch ($data["sourceType"]) {
        case "inline":
          // TODO: Should be validated as parameter input instead
          $decoded = base64_decode($data["sourceData"], true);
          if ($decoded === false) {
            throw new HttpError("sourceData not valid base64 encoding");
          }
          $dummyPost["data"] = $decoded;
          break;
        case "import":
          $realname = str_replace(" ", "_", htmlentities(basename($data["sourceData"]), ENT_QUOTES, "UTF-8"));
          if ($data["sourceData"] != $realname) {
            throw new HttpError("sourceData is invalid filename suggestion '$realname'");
          }
          /* Renaming files will require target file to be checked before renaming */
          if (!file_exists($this->getImportPath() . $data["sourceData"])) {
            throw new HttpError("File '" . $data["sourceData"] . "' not found in import folder");
          }
          /* We are renaming sourceData file to filename file, check if filename is not there already 
             this can be skipped if they are the same */
          if (file_exists($this->getImportPath() . $data[File::FILENAME]) && $data[File::FILENAME] != $data["sourceData"]) {
            throw new HttpError("File required temporary file '" . $data[File::FILENAME] . "' exists import folder, cannot continue");
          }
          /* Since we are renaming the file _before_ import the name is temporary changed */
          $dummyPost["imfile"] = [$data[File::FILENAME]];
          break;
        default:
          // TODO: Choice validation are model based checks
          throw new HttpError("sourceType value '" . $data["sourceType"] . "' is not supported (choices inline, import");
      }

      /* TODO: Hackish view to revert back to required (hardcoded) view */
      $view = [ 
        DFileType::OTHER => 'other',
        DFileType::RULE => 'rule',
        DFileType::WORDLIST => 'dict'
      ][$data[File::FILE_TYPE]];


      /* Prepare renaming file if required */
      $doRenameImport = (($data["sourceType"] == "import") && ($data[File::FILENAME] != $data["sourceData"]));
      if ($doRenameImport) {
        rename(
          $this->getImportPath() . $data["sourceData"],
          $this->getImportPath() . $data[File::FILENAME]
        );
      };

      try {
        /* Create the file, calculating (e.g. lines) and checking validity (e.g. file exists) */
        FileUtils::add($data["sourceType"], $data[File::FILENAME], $dummyPost, $view);
      } catch (Exception $e) {
        /* In case of errors, ensure old state is restored */
        if (($data["sourceType"] == "import") && ($data[File::FILENAME] != $data["sourceData"])) {
          rename(
            $this->getImportPath() . $data[File::FILENAME],
            $this->getImportPath() . $data["sourceData"]
          );
        };
        throw $e;
      }

      /* Hackish way to retrieve object since Id is not returned on creation */
      $qFs = [
        new QueryFilter(File::FILENAME, $data[File::FILENAME], '='),
        new QueryFilter(File::FILE_TYPE, $data[File::FILE_TYPE], '='),
        new QueryFilter(File::ACCESS_GROUP_ID, $data[File::ACCESS_GROUP_ID], '=')
      ];
      $oF = new OrderFilter(File::FILE_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      assert(count($objects) == 1);

      /* Manually set secret, since it not set when adding file */
      FileUtils::switchSecret($objects[0]->getId(), ($data[File::IS_SECRET]) ? 1 : 0, $this->getCurrentUser());

      /* On successfully insert, return ID */
      return $objects[0]->getId();
    }

    protected function getUpdateHandlers($id, $current_user): array {
      return [
        File::FILE_TYPE => fn ($value) => FileUtils::setFileType($id, $value, $current_user)
      ];
    }
  
  
  /**
   * @throws HTException
   */
  protected function deleteObject(object $object): void {
      FileUtils::delete($object->getId(), $this->getCurrentUser());
    }
}

FileAPI::register($app);
