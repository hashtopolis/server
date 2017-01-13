<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class FileFactory extends AbstractModelFactory {
  function getModelName() {
    return "File";
  }
  
  function getModelTable() {
    return "File";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }

  /**
   * @return File
   */
  function getNullObject() {
    $o = new File(-1, null, null, null, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return File
   */
  function createObjectFromDict($pk, $dict) {
    $o = new File($pk, $dict['filename'], $dict['size'], $dict['secret'], $dict['fileType']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return File|File[]
   */
  function filter($options, $single = false) {
    if($single){
      return Util::cast(parent::filter($options, $single), File::class);
    }
    $objects = parent::filter($options, $single);
    $models = array();
    foreach($objects as $object){
      $models[] = Util::cast($object, File::class);
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return File
   */
  function get($pk) {
    return Util::cast(parent::get($pk), File::class);
  }

  /**
   * @param File $model
   * @return File
   */
  function save($model) {
    return Util::cast(parent::save($model), File::class);
  }
}