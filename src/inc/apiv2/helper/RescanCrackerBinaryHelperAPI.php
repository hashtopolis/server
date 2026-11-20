<?php

namespace Hashtopolis\inc\apiv2\helper;

use Exception;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\apiv2\error\ResourceNotFoundError;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\inc\utils\CrackerUtils;
use Hashtopolis\inc\Util;

class RescanCrackerBinaryHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/rescanCrackerBinary";
  }

  public static function getAvailableMethods(): array {
    return ['POST'];
  }

  public function getRequiredPermissions(string $method): array {
    return [CrackerBinary::PERM_UPDATE];
  }

  /**
   * crackerBinaryId is the id of the cracker binary which should be scanned again.
   */
  public function getFormFields(): array {
    return [
      CrackerBinary::CRACKER_BINARY_ID => ["type" => "int"],
    ];
  }

  public static function getResponse(): string {
    return "CrackerBinary";
  }

  /**
   * Endpoint to trigger a new scan of the supported hash-modes of a hashcat
   * cracker binary, e.g. after a previous scan failed. A binary which is only
   * referenced by an url is downloaded again first, so the scan works on a
   * fresh local copy of the archive. The scan itself runs asynchronously in the
   * background job runner; if a scan of the binary is already pending, no
   * additional one is queued. Binaries of other types are rejected, their
   * hashtypes are associated manually.
   *
   * @param $data
   * @return AbstractModel|array|null
   * @throws HttpError
   * @throws HttpForbidden
   * @throws ResourceNotFoundError
   * @throws Exception
   */
  public function actionPost($data): AbstractModel|array|null {
    if (!isset($data[CrackerBinary::CRACKER_BINARY_ID])) {
      throw new HttpError("Required parameter 'crackerBinaryId' not specified!");
    }
    $binaryId = $data[CrackerBinary::CRACKER_BINARY_ID];
    $binary = Factory::getCrackerBinaryFactory()->get($binaryId);
    if ($binary === null) {
      throw new ResourceNotFoundError("Cracker binary not found!");
    }
    // the access group is checked before the type of the binary, so the
    // response does not disclose anything about binaries the user cannot access
    $accessGroupIds = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getCurrentUser()));
    if (!in_array($binary->getAccessGroupId(), $accessGroupIds)) {
      throw new HttpForbidden("No access to this cracker binary!");
    }
    if (!CrackerUtils::isHashcatBinary($binary)) {
      throw new HttpError("Only cracker binaries of the hashcat type can be scanned, the hashtypes of other binaries are associated manually!");
    }

    try {
      CrackerUtils::checkCrackerBinary($binary->getId(), $this->getCurrentUser());
    }
    catch (HTException $e) {
      // e.g. the archive of an url-referenced binary could not be downloaded again
      throw new HttpError($e->getMessage());
    }

    return CrackerUtils::getBinary($binary->getId());
  }
}
