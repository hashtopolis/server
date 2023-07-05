<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\RegVoucher;
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;


require_once(dirname(__FILE__) . "/shared.inc.php");


class VoucherAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/vouchers";
    }

   public static function getDBAclass(): string {
      return RegVoucher::class;
    }

    protected function getFactory(): object {
      return Factory::getRegVoucherFactory();
    }

    public function getExpandables(): array {
      return [];
    }

    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [];
    }

    protected function createObject($QUERY): int {
      AgentUtils::createVoucher($QUERY[RegVoucher::VOUCHER]);

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(RegVoucher::VOUCHER, $QUERY[RegVoucher::VOUCHER], '=')
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