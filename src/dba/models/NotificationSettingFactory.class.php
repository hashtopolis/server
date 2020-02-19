<?php

namespace DBA;

class NotificationSettingFactory extends AbstractModelFactory {
  function getModelName() {
    return "NotificationSetting";
  }
  
  function getModelTable() {
    return "NotificationSetting";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return NotificationSetting
   */
  function getNullObject() {
    $o = new NotificationSetting(-1, null, null, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return NotificationSetting
   */
  function createObjectFromDict($pk, $dict) {
    $o = new NotificationSetting($dict['notificationSettingId'], $dict['action'], $dict['objectId'], $dict['notification'], $dict['userId'], $dict['receiver'], $dict['isActive']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return NotificationSetting|NotificationSetting[]
   */
  function filter($options, $single = false) {
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
   * @return NotificationSetting
   */
  function get($pk) {
    return Util::cast(parent::get($pk), NotificationSetting::class);
  }
  
  /**
   * @param NotificationSetting $model
   * @return NotificationSetting
   */
  function save($model) {
    return Util::cast(parent::save($model), NotificationSetting::class);
  }
}