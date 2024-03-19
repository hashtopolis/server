<?php
use DBA\File;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class RecountFileFilesHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/recountFileLines";
  }

  public static function getAvailableMethods(): array {
    return ['POST'];
  }

  public function getRequiredPermissions(string $method): array
  {
    return [File::PERM_UPDATE];
  }

  public function getFormFields(): array 
  {
    return  [
      File::FILE_ID => ["type" => "int"],
    ];
  }

  public function actionPost($data): array|null {
    // first retrieve the file, as fileCountLines does not check any permissions, therfore to be sure call getFile() first, even if it is not required technically
    FileUtils::getFile($data[File::FILE_ID], $this->getCurrentUser());

    FileUtils::fileCountLines($data[File::FILE_ID]);
    
    return $this->object2Array(FileUtils::getFile($data[File::FILE_ID], $this->getCurrentUser()));
  }
}

RecountFileFilesHelperAPI::register($app);