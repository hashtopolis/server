<?php

namespace Hashtopolis\inc\apiv2\openapi;

use InvalidArgumentException;

/**
 * Per-model corrections to the response attribute schemas the generator
 * derives from the DBA model features.
 *
 * The features describe the database column, which is not always what a
 * response carries: AbstractBaseAPI::obj2Resource drops every attribute that
 * is not flagged 'public' once the caller lacks the read permission of the
 * model (see AbstractBaseAPI::checkPermissions, which registers the class via
 * addPublicAttributeClass). Such an attribute is absent from the response
 * while the model still declares its column NOT NULL, so the derived schema
 * demands it and a permission-filtered response fails validation.
 *
 * Two independent corrections are available per model, both keyed by the
 * attribute alias as it appears in the response:
 *
 * - OPTIONAL_ATTRIBUTES: the attribute may be missing, so it is dropped from
 *   the "required" list of the attributes object. This is what permission
 *   filtering does: the key is not there at all.
 * - NULLABLE_ATTRIBUTES: the attribute may be null, so "null" joins its type
 *   (or a "null" branch its oneOf). The key stays required.
 *
 * Both the openapi.json route and ci/tools/generate-openapi.php build with
 * defaults(), so the served spec and the committed one agree.
 */
class SpecOverrides {
  /** Attributes a response may omit; they are dropped from "required". */
  public const OPTIONAL_ATTRIBUTES = 'optionalAttributes';

  /** Attributes a response may send as null; "null" joins their type. */
  public const NULLABLE_ATTRIBUTES = 'nullableAttributes';

  private const KEYS = [self::OPTIONAL_ATTRIBUTES, self::NULLABLE_ATTRIBUTES];

  /** @var array<string, array<string, list<string>>> */
  private array $byModel = [];

  /**
   * @param array<string, array<string, list<string>>> $overrides model name
   *   (as the component schemas spell it, e.g. "User") to a map of
   *   OPTIONAL_ATTRIBUTES and/or NULLABLE_ATTRIBUTES to attribute aliases
   */
  public function __construct(array $overrides = []) {
    foreach ($overrides as $model => $entry) {
      if (!is_string($model) || $model === '') {
        throw new InvalidArgumentException("Spec override keys must be model names");
      }
      if (!is_array($entry)) {
        throw new InvalidArgumentException("Spec override for '$model' must be an array");
      }
      $normalized = [];
      foreach ($entry as $key => $aliases) {
        if (!in_array($key, self::KEYS, true)) {
          throw new InvalidArgumentException(
            "Unknown spec override '$key' for '$model', expected one of: " . implode(", ", self::KEYS)
          );
        }
        if (!is_array($aliases)) {
          throw new InvalidArgumentException("Spec override '$key' of '$model' must be a list of attribute names");
        }
        foreach ($aliases as $alias) {
          if (!is_string($alias) || $alias === '') {
            throw new InvalidArgumentException("Spec override '$key' of '$model' must only name attributes");
          }
        }
        $normalized[$key] = array_values(array_unique($aliases));
      }
      $this->byModel[$model] = $normalized;
    }
  }

  /**
   * The corrections the server itself needs.
   *
   * User is the only model with 'public' features, so it is the only one whose
   * responses can arrive attribute-filtered: a caller without 'permUserRead'
   * (directly or through an include) receives the public 'name' alone. Every
   * other attribute is therefore optional rather than guaranteed.
   */
  public static function defaults(): self {
    return new self([
      'User' => [
        self::OPTIONAL_ATTRIBUTES => [
          'email',
          'isValid',
          'isComputedPassword',
          'lastLoginDate',
          'registeredSince',
          'sessionLifetime',
          'globalPermissionGroupId',
          'yubikey',
          'otp1',
          'otp2',
          'otp3',
          'otp4',
        ],
      ],
    ]);
  }

  /**
   * Whether any correction is configured for the model.
   */
  public function has(string $model): bool {
    return array_key_exists($model, $this->byModel);
  }

  /**
   * Apply the corrections of one model to an attributes object schema.
   *
   * The schema is returned unchanged when nothing is configured for the model.
   *
   * @param string $model model name as the component schemas spell it
   * @param array $schema the "attributes" object, with "required" and "properties"
   */
  public function apply(string $model, array $schema): array {
    if (!$this->has($model)) {
      return $schema;
    }
    $entry = $this->byModel[$model];
    $properties = $schema['properties'] ?? [];

    foreach ($entry as $key => $aliases) {
      foreach ($aliases as $alias) {
        if (!array_key_exists($alias, $properties)) {
          throw new InvalidArgumentException(
            "Spec override '$key' of '$model' names attribute '$alias', which the model does not have"
          );
        }
      }
    }

    $optional = $entry[self::OPTIONAL_ATTRIBUTES] ?? [];
    if (count($optional) > 0 && array_key_exists('required', $schema)) {
      $schema['required'] = array_values(array_diff($schema['required'], $optional));
    }

    foreach ($entry[self::NULLABLE_ATTRIBUTES] ?? [] as $alias) {
      $schema['properties'][$alias] = $this->makeNullable($schema['properties'][$alias]);
    }

    return $schema;
  }

  /**
   * Widen a property schema by null, in both shapes FeatureTypeMapper emits:
   * a plain (or already widened) "type", and the "oneOf" of an enum.
   */
  private function makeNullable(array $property): array {
    if (array_key_exists('oneOf', $property)) {
      foreach ($property['oneOf'] as $branch) {
        if (($branch['type'] ?? null) === 'null') {
          return $property;
        }
      }
      $property['oneOf'][] = ["type" => "null"];
      return $property;
    }

    $type = $property['type'] ?? null;
    if (is_array($type)) {
      if (!in_array("null", $type, true)) {
        $property['type'][] = "null";
      }
      return $property;
    }
    if ($type !== null && $type !== "null") {
      $property['type'] = [$type, "null"];
    }
    return $property;
  }
}
