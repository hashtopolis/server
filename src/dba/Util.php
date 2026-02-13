<?php

namespace Hashtopolis\dba;

class Util {
  /**
   * Used to cast database objects into their corresponding type
   *
   * @param $obj
   * @param $to_class
   * @return mixed
   */
  public static function cast($obj, $to_class): mixed {
    if ($obj == null) {
      return null;
    }
    else if (class_exists($to_class)) {
      $obj_in = serialize($obj);
      $obj_in_split = explode(":", $obj_in);
      unset($obj_in_split[0]);
      unset($obj_in_split[1]);
      unset($obj_in_split[2]);
      $obj_out = 'O:' . strlen($to_class) . ':"' . $to_class . '":' . implode(":", $obj_in_split);
      return unserialize($obj_out);
    }
    else {
      return null;
    }
  }
  
  /**
   * Used to create the full select string of a table query
   * @param $table string
   * @param $keys array
   * @return string
   */
  public static function createPrefixedString(string $table, array $keys): string {
    $arr = array();
    foreach ($keys as $key) {
      $arr[] = "$table.$key AS " . $table . "_" . $key;
    }
    return implode(", ", $arr);
  }
}
