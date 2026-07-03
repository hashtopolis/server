<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<NotificationSetting>
 */
class NotificationSettingFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "NotificationSetting";
  }
  
  function getModelTable(): string {
    return "NotificationSetting";
  }

  function isMapping(): bool {
    return False;
  }
  
  function isCachable(): bool {
    return false;
  }
  
  function getCacheValidTime(): int {
    return -1;
  }
  
  /**
   * @return NotificationSetting
   */
  function getNullObject(): NotificationSetting {
    return new NotificationSetting(-1, null, null, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return NotificationSetting
   */
  function createObjectFromDict(array $dict): NotificationSetting {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new NotificationSetting($dict['notificationsettingid'], $dict['action'], $dict['objectid'], $dict['notification'], $dict['userid'], $dict['receiver'], $dict['isactive']);
  }
}
