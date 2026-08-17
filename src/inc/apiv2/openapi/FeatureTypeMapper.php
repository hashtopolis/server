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
   * Turns a sample value (the getResponse() of a helper) into the schema
   * describing it. Lists and maps are walked, so a nested sample keeps its
   * structure instead of collapsing into an untyped object carrying the whole
   * sample as a value.
   */
  public function mapToSchema(mixed $value): array {
    if (is_null($value)) {
      /* The sample says nothing about the type beyond it being nullable */
      return ["type" => ["string", "null"]];
    }
    if (is_bool($value)) {
      return ["type" => "boolean", "example" => $value];
    }
    if (is_int($value)) {
      return ["type" => "integer", "example" => $value];
    }
    if (is_float($value)) {
      return ["type" => "number", "example" => $value];
    }
    if (is_string($value)) {
      return ["type" => "string", "example" => $value];
    }
    if (is_object($value)) {
      $value = get_object_vars($value);
    }
    if (!is_array($value)) {
      return ["type" => "string"];
    }
    if (count($value) === 0) {
      return ["type" => "array"];
    }

    if (array_is_list($value)) {
      /**
       * The entries of a sample list need not all carry the same keys, so the
       * item schema merges the properties of all of them to describe the most
       * complete entry.
       */
      $items = $this->mapToSchema($value[0]);
      $mergedProperties = [];
      foreach ($value as $entry) {
        $entrySchema = $this->mapToSchema($entry);
        if (isset($entrySchema["properties"])) {
          $mergedProperties = array_merge($mergedProperties, $entrySchema["properties"]);
        }
      }
      if (count($mergedProperties) > 0) {
        $items["properties"] = $mergedProperties;
      }
      return ["type" => "array", "items" => $items];
    }

    return [
      "type" => "object",
      "properties" => array_map(fn($entry) => $this->mapToSchema($entry), $value)
    ];
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
