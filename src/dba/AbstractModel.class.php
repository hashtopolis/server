<?php

namespace DBA;

abstract class AbstractModel {
  /**
   * Returns a dict with all keys and associated values for this array
   * This is used for update queries.
   */
  abstract function getKeyValueDict();
  
  /**
   * This function should return the primary key of the used object.
   */
  abstract function getPrimaryKey();
  
  /**
   * This function should return the value of the primary key of the used object
   */
  abstract function getPrimaryKeyValue();
  
  /**
   * This function is used to set the id to the real database value
   * @param $id string
   * @return
   */
  abstract function setId($id): void;
  
  /**
   * this function returns the models id
   */
  abstract function getId();
}
