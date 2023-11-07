<?php
use DBA\Factory;
use DBA\OrderFilter;
use DBA\QueryFilter;

use DBA\NotificationSetting;
use DBA\User;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");

class NotificationSettingAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/notifications";
    }

    public static function getDBAclass(): string {
      return NotificationSetting::class;
    }

    public static function getExpandables(): array {
      return ['user'];
    }
 
    protected static function fetchExpandObjects(array $objects, string $expand): mixed {     
      /* Ensure we receive the proper type */
      array_walk($objects, function($obj) { assert($obj instanceof NotificationSetting); });

      /* Expand requested section */
      switch($expand) {
        case 'user':
          return self::getForeignKeyRelation(
            $objects,
            NotificationSetting::USER_ID,
            Factory::getUserFactory(),
            User::USER_ID
          );
        default:
          throw new BadFunctionCallException("Internal error: Expansion '$expand' not implemented!");
      }
    }
    
    public function getFormFields(): array {
      return  ['actionFilter' => ['type' => 'str(256)']];
    }

    protected function createObject(array $data): int {
      $dummyPost = [];
      switch (DNotificationType::getObjectType($data['action'])) {
        case DNotificationObjectType::USER:
          $dummyPost['user'] = $data['actionFilter'];
          break;
        case DNotificationObjectType::AGENT:
          $dummyPost['agents'] = $data['actionFilter'];
          break;
        case DNotificationObjectType::HASHLIST:
          $dummyPost['hashlists'] = $data['actionFilter'];
          break;
        case DNotificationObjectType::TASK:
          $dummyPost['tasks'] = $data['actionFilter'];
          break;
      }


      NotificationUtils::createNotificaton(
        $data[NotificationSetting::ACTION],
        $data[NotificationSetting::NOTIFICATION],
        $data[NotificationSetting::RECEIVER],
        $dummyPost,
        $this->getCurrentUser(),
      );

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(NotificationSetting::ACTION, $data[NotificationSetting::ACTION], '='),
        new QueryFilter(NotificationSetting::NOTIFICATION, $data[NotificationSetting::NOTIFICATION], '='),
        new QueryFilter(NotificationSetting::RECEIVER, $data[NotificationSetting::RECEIVER], '='),
      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(NotificationSetting::NOTIFICATION_SETTING_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      /* No unique properties set on columns, thus multiple entries could exists, pick the latest (DESC ordering used) */
      assert(count($objects) >= 1);
      
      return $objects[0]->getId();
    }

    protected function deleteObject(object $object): void {
      NotificationUtils::delete($object->getId(), $this->getCurrentUser());
    }
}

NotificationSettingAPI::register($app);