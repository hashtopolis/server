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
    public static function getBaseUri(): string {
      return "/api/v2/ui/notifications";
    }

    public static function getDBAclass(): string {
      return NotificationSetting::class;
    }

    protected function getFactory(): object {
      return Factory::getNotificationSettingFactory();
    }

    public function getExpandables(): array {
      return ['user'];
    }
 
    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  ['actionFilter' => ['type' => 'str(256)']];
    }

    protected function createObject($QUERY): int {
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
      assert(count($objects) >= 1);
      
      return $objects[0]->getId();
    }

    protected function deleteObject(object $object): void {
      NotificationUtils::delete($object->getId(), $this->getUser());
    }
}

NotificationSettingAPI::register($app);