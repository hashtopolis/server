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
    elseif ($feature['type'] == 'json') {
      $type = "object";
    }
    elseif ($feature['type'] == 'array') {
      $type = "array";
      $sub_type = $this->typeLookup(['type' => $feature['subtype'] ?? 'int', 'choices' => 'unset'])['type'];
    }
    elseif ($feature['type'] == 'bool') {
      $type = "boolean";
    }
    elseif (str_starts_with($feature['type'], 'str(')) {
      $type = "string";
    }
    elseif ($feature['type'] == 'str' || $feature['type'] == 'string') {
      $type = "string";
    }
    else {
      throw new HttpErrorException("Cast for type  '" . $feature['type'] . "' not implemented");
    }

    if (is_array($feature['choices'])) {
      $type_enum = array_keys($feature['choices']);
    }

    /**
     * The names of the constants backing the choices, when the feature names
     * them. They document the values for a reader and for code generators,
     * which turn them into the members of a generated enum type.
     */
    $type_enum_names = null;
    if ($type_enum !== null && is_array($feature['choice_names'] ?? null)) {
      $type_enum_names = array_values($feature['choice_names']);
    }

    return [
      "type" => $type,
      "type_format" => $type_format,
      "type_enum" => $type_enum,
      "type_enum_labels" => $type_enum !== null ? array_values($feature['choices']) : null,
      "type_enum_names" => $type_enum_names,
      "subtype" => $sub_type
    ];
  }

  /**
   * Compose the description of a property out of its own prose and, for a
   * property limited to a set of values, what each of those values means.
   *
   * Either part may be absent. Null is returned when there is nothing to say,
   * so that a property nobody has documented carries no description at all
   * rather than a restatement of its own name.
   *
   * @param string|null $prose the description the feature declares, if any
   * @param array<int, int|string>|null $values the allowed values, if the property is limited to a set
   * @param array<int, string>|null $labels what each of those values means, in the same order
   */
  public static function composeDescription(?string $prose, ?array $values, ?array $labels): ?string {
    $parts = [];
    if ($prose !== null && trim($prose) !== '') {
      $parts[] = trim($prose);
    }
    if ($values !== null && $labels !== null) {
      $lines = ["One of:", ""];
      foreach ($values as $i => $value) {
        $lines[] = "* `" . (string)$value . "` - " . $labels[$i];
      }
      $parts[] = implode("\n", $lines);
    }
    return count($parts) > 0 ? implode("\n\n", $parts) : null;
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

      $property = ["type" => $isNullable ? [$ret["type"], "null"] : $ret["type"]];
      if ($ret["type_format"] !== null) {
        $property["format"] = $ret["type_format"];
      }
      if ($ret["type_enum"] !== null) {
        /**
         * A flat enum, rather than a oneOf of single-const branches: Swagger UI
         * renders neither const nor the title of a oneOf branch, so the branches
         * read as a bare value with no hint that the value set is closed. What
         * each value means goes into the description instead.
         *
         * A nullable property accepts null, so null is one of its enum values.
         */
        $property["enum"] = $isNullable ? array_merge($ret["type_enum"], [null]) : $ret["type_enum"];
        if ($ret["type_enum_names"] !== null) {
          $property["x-enum-varnames"] = $ret["type_enum_names"];
        }
      }
      $description = self::composeDescription(
        $feature['description'] ?? null,
        $ret["type_enum"],
        $ret["type_enum_labels"]
      );
      if ($description !== null) {
        $property["description"] = $description;
      }
      if ($ret["subtype"] !== null) {
        if ($ret["type"] === "object") {
          $property["additionalProperties"]["type"] = $ret["subtype"];
        } else {
          $property["items"]["type"] = $ret["subtype"];
        }
      }
      $propertyVal[$feature['alias']] = $property;
    }
    return $propertyVal;
  }
}
