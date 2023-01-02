<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Factory;
use DBA\NotificationSetting;
use DBA\QueryFilter;
use DBA\OrderFilter;



require_once(dirname(__FILE__) . "/shared.inc.php");


class NotificationSettingAPI extends AbstractBaseAPI {
    protected function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    protected function getFeatures(): array {
      return NotificationSetting::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getNotificationSettingFactory();
    }

    protected function getExpandables(): array {
      return ['user'];
    }
 
    protected function getFilterACL(): array {
      return [];
    }

    protected function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  ['actionFilter' => ['type' => 'str(256)']];
    }

    protected function checkPermission(object $object): bool
    {
      return true;
    }
    

    protected function createObject($QUERY): int {
      Login::getInstance()
      $dummyPost = [];
      switch (DNotificationType::getObjectType($QUERY['action'])) {
        case DNotificationObjectType::USER:
          $dummyPost['user'] = $QUERY['actionFilter'];
          break;
        case DNotificationObjectType::AGENT:
          $dummyPost['agents'] = $QUERY['actionFilter'];
          break;
        case DNotificationObjectType::HASHLIST:
          $dummyPost['hashlists'] = $QUERY['actionFilter'];
          break;
        case DNotificationObjectType::TASK:
          $dummyPost['tasks'] = $QUERY['actionFilter'];
          break;
      }


      NotificationUtils::createNotificaton(
        $QUERY['action'],
        $QUERY['notification'],
        $QUERY['receiver'],
        $dummyPost,
        $this->getUser(),
      );

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(NotificationSetting::ACTION, $QUERY['action'], '='),
        new QueryFilter(NotificationSetting::NOTIFICATION, $QUERY['notification'], '='),
        new QueryFilter(NotificationSetting::RECEIVER, $QUERY['receiver'], '='),
      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(NotificationSetting::NOTIFICATION_SETTING_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      /* No unique properties set on columns, thus multiple entries could exists, pick the latest (DESC ordering used) */
      assert(count($objects) > 1);
      
      return $objects[0]->getId();
    }

    protected function deleteObject(object $object): void {
      NotificationUtils::delete($object, $this->getUser());
    }
}


$app->group("/api/v2/ui/notifications", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \NotificationSettingAPI::class . ':get');
    $group->post('', \NotificationSettingAPI::class . ':post');
});


$app->group("/api/v2/ui/notifications/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \NotificationSettingAPI::class . ':getOne');
    $group->patch('', \NotificationSettingAPI::class . ':patchOne');
    $group->delete('', \NotificationSettingAPI::class . ':deleteOne');
});