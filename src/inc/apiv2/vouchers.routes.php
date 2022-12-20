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
    protected function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    protected function getFeatures(): array {
      return RegVoucher::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getRegVoucherFactory();
    }

    protected function getExpandables(): array {
      return [];
    }

    protected function getFilterACL(): array {
      return [];
    }

    protected function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [];
    }

    protected function checkPermission(object $object): bool
    {
      return true;
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
      Factory::getRegVoucherFactory()->delete($object);
    }
}


$app->group("/api/v2/ui/vouchers", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \VoucherAPI::class . ':get');
    $group->post('', \VoucherAPI::class . ':post');
});


$app->group("/api/v2/ui/vouchers/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \VoucherAPI::class . ':getOne');
    $group->patch('', \VoucherAPI::class . ':patchOne');
    $group->delete('', \VoucherAPI::class . ':deleteOne');
});