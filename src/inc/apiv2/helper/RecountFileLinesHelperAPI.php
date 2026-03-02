<?php

namespace Hashtopolis\inc\apiv2\helper;

use Hashtopolis\inc\utils\FileUtils;
use Hashtopolis\dba\models\File;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\HTException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class RecountFileLinesHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/recountFileLines";
  }
  
  public static function getAvailableMethods(): array {
    return ['POST'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [File::PERM_UPDATE];
  }
  
  /**
   * FileId is the id of the file that needs to be recounted.
   */
  public function getFormFields(): array {
    return [
      File::FILE_ID => ["type" => "int"],
    ];
  }
  
  public static function getResponse(): string {
    return "File";
  }
  
  /**
   * Endpoint to recount files for when there is size mismatch
   * @param $data
   * @return object|array|null
   * @throws HTException
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   */
  public function actionPost($data): object|array|null {
    // first retrieve the file, as fileCountLines does not check any permissions, therefore to be sure call getFile() first, even if it is not required technically
    FileUtils::getFile($data[File::FILE_ID], $this->getCurrentUser());
    
    FileUtils::fileCountLines($data[File::FILE_ID]);
    
    return $this->object2Array(FileUtils::getFile($data[File::FILE_ID], $this->getCurrentUser()));
  }
}
