<?php

namespace Hashtopolis\inc\apiv2\openapi;

use Middlewares\Utils\HttpErrorException;

/**
 * Maps DBA model feature definitions (and sample values) to JSON schema
 * property definitions.
 */
class FeatureTypeMapper {
  /**
   * @throws HttpErrorException
   */
  public function typeLookup($feature): array {
    $type_format = null;
    $type_enum = null;
    $sub_type = null;
    if ($feature['type'] == 'int') {
      $type = "integer";
    }
    elseif ($feature['type'] == 'uint64') {
      /* TODO: Specify integer ranges */
      $type = "integer";
    }
    elseif ($feature['type'] == 'int64') {
      $type = "integer";
      $type_format = "int64";
    }
    elseif ($feature['type'] == 'dict') {
      $type = "object";
      if ($feature['subtype'] !== 'unset') {
        $sub_type = $this->typeLookup(['type' => $feature['subtype'], 'choices' => 'unset'])['type'];
      }
    }
    elseif ($feature['type'] == 'array') {
      $type = "array";
      $sub_type = "integer"; //TODO: subtype is hardcoded because we only have int arrays
    }
    elseif ($feature['type'] == 'bool') {
      $type = "boolean";
    }
    elseif (str_starts_with($feature['type'], 'str(')) {
      $type = "string";
    }
    elseif ($feature['type'] == 'str') {
      $type = "string";
    }
    else {
      throw new HttpErrorException("Cast for type  '" . $feature['type'] . "' not implemented");
    }

    // JSON:API requires resource ids to be strings. Foreign-key columns are
    // tagged with a "reference" marker in the model features; emit them as
    // string ids no matter how they are stored internally.
    if (!empty($feature['reference'])) {
      $type = "string";
      $type_format = null;
    }

    if (is_array($feature['choices'])) {
      $type_enum = array_keys($feature['choices']);
    }

    return [
      "type" => $type,
      "type_format" => $type_format,
      "type_enum" => $type_enum,
      "type_enum_labels" => $type_enum !== null ? array_values($feature['choices']) : null,
      "subtype" => $sub_type
    ];
  }

  /**
   * Turns a map of sample values (the getResponse() of a helper) into the
   * schema properties describing it.
   */
  public function mapToProperties($map): array {
    return array_map(function ($value) {
      if (is_int($value)) {
        $type = "integer";
      } elseif (is_float($value)) {
        $type = "number";
      } elseif (is_bool($value)) {
        $type = "boolean";
      } elseif (is_array($value) || is_object($value)) {
        $type = "object";
      } else {
        $type = "string";
      }
      return [
        "type" => $type,
        "default" => $value,
      ];
    }, $map);
  }

  /**
   * @throws HttpErrorException
   */
  public function makeProperties($features, $skipPK = false): array {
    $propertyVal = [];
    foreach ($features as $feature) {
      if ($skipPK && $feature['pk']) {
        continue;
      }
      /**
       * A feature can carry its own schema for values the feature types cannot
       * describe, such as an object whose members differ in type. It is taken
       * verbatim, so the type lookup does not apply to it.
       */
      if (!empty($feature['openapi_schema'])) {
        $propertyVal[$feature['alias']] = $feature['openapi_schema'];
        continue;
      }
      $ret = $this->typeLookup($feature);
      $isNullable = $feature['null'] ?? false;
      if ($ret["type_enum"] !== null && $ret["type_enum_labels"] !== null) {
        $oneOfItems = [];
        foreach ($ret["type_enum"] as $i => $val) {
          $item = ["const" => $val, "title" => $ret["type_enum_labels"][$i], "type" => $ret["type"]];
          if ($ret["type_format"] !== null) {
            $item["format"] = $ret["type_format"];
          }
          $oneOfItems[] = $item;
        }
        if ($isNullable) {
          $oneOfItems[] = ["type" => "null"];
        }
        $propertyVal[$feature['alias']] = ["oneOf" => $oneOfItems];
      } else {
        $propertyVal[$feature['alias']]["type"] = $isNullable ? [$ret["type"], "null"] : $ret["type"];
        if ($ret["type_format"] !== null) {
          $propertyVal[$feature['alias']]["format"] = $ret["type_format"];
        }
        if ($ret["type_enum"] !== null) {
          $propertyVal[$feature['alias']]["enum"] = $ret["type_enum"];
        }
      }
      if ($ret["subtype"] !== null) {
        if ($ret["type"] === "object") {
          $propertyVal[$feature['alias']]["additionalProperties"]["type"] = $ret["subtype"];
        } else {
          $propertyVal[$feature['alias']]["items"]["type"] = $ret["subtype"];
        }
      }
    }
    return $propertyVal;
  }
}
