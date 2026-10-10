<?php

namespace Hashtopolis\inc\apiv2\helper;

use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\models\File;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\FileChecksumUtils;
use Hashtopolis\inc\utils\FileUtils;

class RecomputeFileChecksumHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/recomputeFileChecksum";
  }

  public static function getAvailableMethods(): array {
    return ['POST'];
  }

  public function getRequiredPermissions(string $method): array {
    return [File::PERM_UPDATE];
  }

  /**
   * FileId is the id of the file whose checksum needs to be recomputed.
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
   * Endpoint to enqueue the checksum computation of a file, for when it is missing or the content on
   * disk was replaced. The checksum is computed asynchronously by the background job runner, so the
   * returned File still carries the old value until that job ran.
   * @param $data
   * @return AbstractModel|array|null
   * @throws HTException
   */
  public function actionPost($data): AbstractModel|array|null {
    // first retrieve the file, as this checks the access permissions, which enqueueing the job does not do
    FileUtils::getFile($data[File::FILE_ID], $this->getCurrentUser());

    FileChecksumUtils::enqueue($data[File::FILE_ID], $this->getCurrentUser());

    /* Return the File itself so it comes back as a resource object under data.
       Only the helper's own permission (File update) is checked; the serializer
       no longer demands the model route's create permission. */
    return FileUtils::getFile($data[File::FILE_ID], $this->getCurrentUser());
  }
}
