<?php

namespace Hashtopolis\inc\apiv2\helper;

use Hashtopolis\dba\AbstractModel;
use Hashtopolis\inc\utils\BackgroundJobUtils;
use Hashtopolis\inc\utils\FileUtils;
use Hashtopolis\dba\models\File;
use Hashtopolis\inc\defines\DBackgroundJobType;
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
   * Endpoint to enqueue the recounting of files for when there is size mismatch. The line count is
   * calculated asynchronously by the background job runner.
   * @param $data
   * @return AbstractModel|array|null
   * @throws HTException
   */
  public function actionPost($data): AbstractModel|array|null {
    // first retrieve the file, as this checks the access permissions, which enqueueing the job does not do
    FileUtils::getFile($data[File::FILE_ID], $this->getCurrentUser());
    
    BackgroundJobUtils::enqueue(DBackgroundJobType::RECOUNT_FILE, [File::FILE_ID => $data[File::FILE_ID]], $this->getCurrentUser());
    
    /* Return the File itself so it comes back as a resource object under data.
       Only the helper's own permission (File update) is checked; the serializer
       no longer demands the model route's create permission. */
    return FileUtils::getFile($data[File::FILE_ID], $this->getCurrentUser());
  }
}
