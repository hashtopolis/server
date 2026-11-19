<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\models\SupertaskPretask;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;


/**
 * The association of a pretask with a supertask, carrying the priority the
 * pretask has within that supertask. Associations are created and removed
 * through the pretasks relationship of the supertask, which enforces the
 * cracker binary type consistency and the minimum of one pretask, so only the
 * priority can be changed here.
 *
 * @extends AbstractModelAPI<SupertaskPretask>
 */
class SupertaskPretaskAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/supertaskpretasks";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET', 'PATCH'];
  }
  
  public static function getDBAclass(): string {
    return SupertaskPretask::class;
  }
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    throw new HttpError("Supertask pretask associations cannot be created via this endpoint, add the pretask to the pretasks relationship of the supertask instead");
  }
  
  /**
   * @param SupertaskPretask $object
   * @throws HttpError
   */
  protected function deleteObject(AbstractModel $object): void {
    throw new HttpError("Supertask pretask associations cannot be deleted via this endpoint, remove the pretask from the pretasks relationship of the supertask instead");
  }
}
