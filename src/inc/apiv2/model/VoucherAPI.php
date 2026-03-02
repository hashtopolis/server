<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\inc\utils\AgentUtils;

use Hashtopolis\dba\models\RegVoucher;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpConflict;
use Hashtopolis\inc\HTException;


class VoucherAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/vouchers";
  }
  
  public static function getDBAclass(): string {
    return RegVoucher::class;
  }
  
  /**
   * @throws HttpConflict
   */
  protected function createObject(array $data): int {
    $voucher = AgentUtils::createVoucher($data[RegVoucher::VOUCHER]);
    return $voucher->getId();
  }
  
  /**
   * @throws HTException
   */
  protected function deleteObject(object $object): void {
    AgentUtils::deleteVoucher($object->getId());
  }
}
