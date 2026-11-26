<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\NotificationSetting;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\defines\DAccessControl;
use Hashtopolis\inc\defines\DNotificationType;
use Hashtopolis\inc\notifications\HashtopolisNotification;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

/**
 * createNotificationSetting() takes the permission check from its caller, so the legacy UI and APIv2
 * can each answer it from their own notion of the logged-in user.
 */
final class NotificationUtilsTest extends TestBase {
  private function notificationName(): string {
    return array_key_first(HashtopolisNotification::getInstances());
  }

  private function create(string $actionType, array $post, callable $hasPermission): NotificationSetting {
    $user = $this->createUser('notif_owner');
    $setting = NotificationUtils::createNotificationSetting($actionType, $this->notificationName(), '  someone  ', $post, $user, $hasPermission);
    $this->registerDatabaseObject(Factory::getNotificationSettingFactory(), $setting);
    $this->assertSame($user->getId(), $setting->getUserId());
    return $setting;
  }

  public function testTheActionTypesPermissionIsAskedFor(): void {
    $asked = [];
    $setting = $this->create(DNotificationType::NEW_TASK, [], function (string|array $perm) use (&$asked): bool {
      $asked[] = $perm;
      return true;
    });

    $this->assertSame([DAccessControl::VIEW_TASK_ACCESS], $asked);
    $this->assertSame(DNotificationType::NEW_TASK, $setting->getAction());
    $this->assertSame('someone', $setting->getReceiver());
    $this->assertNull($setting->getObjectId());
  }

  public function testADeniedActionTypeIsRefused(): void {
    $user = $this->createUser('notif_denied');

    $this->expectException(HttpError::class);
    NotificationUtils::createNotificationSetting(DNotificationType::NEW_TASK, $this->notificationName(), 'someone', [], $user, fn($perm) => false);
  }

  public function testUserActionTypesNeedUserConfigAccess(): void {
    $user = $this->createUser('notif_userconfig');
    // Grants everything but the user configuration permission
    $hasPermission = fn($perm) => $perm !== DAccessControl::USER_CONFIG_ACCESS;

    $this->expectException(HttpError::class);
    NotificationUtils::createNotificationSetting(DNotificationType::USER_DELETED, $this->notificationName(), 'someone', ['users' => 'ALL'], $user, $hasPermission);
  }

  /**
   * The user a user notification is about is the object of the notification; the notification stays
   * owned by the user who created it.
   */
  public function testAUserNotificationIsOwnedByItsCreatorNotItsSubject(): void {
    $subject = $this->createUser('notif_subject');

    $setting = $this->create(DNotificationType::USER_DELETED, ['users' => $subject->getId()], fn($perm) => true);

    $this->assertSame($subject->getId(), $setting->getObjectId());
  }
}
