<?php

use DBA\Factory;
use DBA\OrderFilter;
use DBA\QueryFilter;

use DBA\RegVoucher;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


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

VoucherAPI::register($app);