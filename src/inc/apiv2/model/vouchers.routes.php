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

    protected function getFactory(): object {
      return Factory::getRegVoucherFactory();
    }
    
    protected function createObject(array $data): int {
      AgentUtils::createVoucher($data[RegVoucher::VOUCHER]);

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(RegVoucher::VOUCHER, $data[RegVoucher::VOUCHER], '=')
      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(RegVoucher::REG_VOUCHER_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      assert(count($objects) == 1);
      
      return $objects[0]->getId();
    }

    protected function deleteObject(object $object): void {
      AgentUtils::deleteVoucher($object->getId());
    }
}

VoucherAPI::register($app);