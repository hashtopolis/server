<?php

namespace DBA;

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
   * @param string $pk
   * @param array $dict
   * @return NotificationSetting
   */
  function createObjectFromDict($pk, $dict): NotificationSetting {
    return new NotificationSetting($dict['notificationSettingId'], $dict['action'], $dict['objectId'], $dict['notification'], $dict['userId'], $dict['receiver'], $dict['isActive']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return NotificationSetting|NotificationSetting[]
   */
  function filter(array $options, bool $single = false) {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), NotificationSetting::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, NotificationSetting::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?NotificationSetting
   */
  function get($pk): ?NotificationSetting {
    return Util::cast(parent::get($pk), NotificationSetting::class);
  }
  
  /**
   * @param NotificationSetting $model
   * @return NotificationSetting
   */
  function save($model): NotificationSetting {
    return Util::cast(parent::save($model), NotificationSetting::class);
  }
}