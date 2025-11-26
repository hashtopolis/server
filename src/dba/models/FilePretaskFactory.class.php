<?php

namespace DBA;

class FilePretaskFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "FilePretask";
  }
  
  function getModelTable(): string {
    return "FilePretask";
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
   * @return FilePretask
   */
  function getNullObject(): FilePretask {
    return new FilePretask(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return FilePretask
   */
  function createObjectFromDict($pk, $dict): FilePretask {
    return new FilePretask($dict['filePretaskId'], $dict['fileId'], $dict['pretaskId']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return FilePretask|FilePretask[]
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
      return Util::cast(parent::filter($options, $single), FilePretask::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, FilePretask::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?FilePretask
   */
  function get($pk): ?FilePretask {
    return Util::cast(parent::get($pk), FilePretask::class);
  }
  
  /**
   * @param FilePretask $model
   * @return FilePretask
   */
  function save($model): FilePretask {
    return Util::cast(parent::save($model), FilePretask::class);
  }
}