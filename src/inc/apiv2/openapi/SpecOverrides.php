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
 * Three independent corrections are available per model, the first two keyed
 * by the attribute alias as it appears in the response:
 *
 * - OPTIONAL_ATTRIBUTES: the attribute may be missing, so it is dropped from
 *   the "required" list of the attributes object. This is what permission
 *   filtering does: the key is not there at all.
 * - NULLABLE_ATTRIBUTES: the attribute may be null, so "null" joins its type,
 *   and its enum when it has one. The key stays required.
 * - ATTRIBUTE_DESCRIPTIONS: a map of attribute alias to description text.
 *   Purely additive documentation, applied to the response schemas and, via
 *   applyDescriptions(), to the create request schema. Properties the schema
 *   does not carry are skipped, so one description set can serve the
 *   different shapes a model's attributes take (responses do not contain
 *   creation-only form fields, for example). It is a side channel for what the
 *   features cannot say; an attribute the features can describe is described
 *   by the 'description' of its feature instead, which reaches every schema
 *   rather than these two, and an attribute limited to a set of values has to
 *   be, because its description carries the meaning of each value.
 *
 * Both the openapi.json route and ci/tools/generate-openapi.php build with
 * defaults(), so the served spec and the committed one agree.
 */
class SpecOverrides {
  /** Attributes a response may omit; they are dropped from "required". */
  public const OPTIONAL_ATTRIBUTES = 'optionalAttributes';

  /** Attributes a response may send as null; "null" joins their type. */
  public const NULLABLE_ATTRIBUTES = 'nullableAttributes';

  /** Attribute descriptions; a map of attribute alias to description text. */
  public const ATTRIBUTE_DESCRIPTIONS = 'attributeDescriptions';

  private const KEYS = [self::OPTIONAL_ATTRIBUTES, self::NULLABLE_ATTRIBUTES, self::ATTRIBUTE_DESCRIPTIONS];

  /** @var array<string, array<string, array<string>|array<string, string>>> */
  private array $byModel = [];

  /**
   * @param array<string, array<string, array<string>|array<string, string>>> $overrides model name
   *   (as the component schemas spell it, e.g. "User") to a map of
   *   OPTIONAL_ATTRIBUTES and/or NULLABLE_ATTRIBUTES to attribute aliases,
   *   or of ATTRIBUTE_DESCRIPTIONS to attribute alias => description
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
        if ($key === self::ATTRIBUTE_DESCRIPTIONS) {
          foreach ($aliases as $alias => $description) {
            if (!is_string($alias) || $alias === '' || !is_string($description) || $description === '') {
              throw new InvalidArgumentException(
                "Spec override '$key' of '$model' must map attribute names to non-empty descriptions"
              );
            }
          }
          $normalized[$key] = $aliases;
        }
        else {
          foreach ($aliases as $alias) {
            if (!is_string($alias) || $alias === '') {
              throw new InvalidArgumentException("Spec override '$key' of '$model' must only name attributes");
            }
          }
          $normalized[$key] = array_values(array_unique($aliases));
        }
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
      if ($key === self::ATTRIBUTE_DESCRIPTIONS) {
        // descriptions are additive documentation, properties which are not
        // part of this particular schema shape are simply skipped
        continue;
      }
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

    return $this->applyDescriptionEntries($entry[self::ATTRIBUTE_DESCRIPTIONS] ?? [], $schema, $model);
  }

  /**
   * Apply only the attribute descriptions of one model to a request attributes
   * schema (create), where the response oriented optional/nullable corrections
   * must not be applied. Properties the schema does not carry are skipped.
   *
   * The schema is returned unchanged when nothing is configured for the model.
   *
   * @param string $model model name as the component schemas spell it
   * @param array $schema the "attributes" object, with "required" and "properties"
   */
  public function applyDescriptions(string $model, array $schema): array {
    if (!$this->has($model)) {
      return $schema;
    }
    $descriptions = $this->byModel[$model][self::ATTRIBUTE_DESCRIPTIONS] ?? [];
    return $this->applyDescriptionEntries($descriptions, $schema, $model);
  }

  /**
   * @param array<string, string> $descriptions attribute alias to description text
   * @param string $model model name, for the error message of the enum guard
   */
  private function applyDescriptionEntries(array $descriptions, array $schema, string $model): array {
    foreach ($descriptions as $alias => $description) {
      if (!array_key_exists($alias, $schema['properties'] ?? [])) {
        continue;
      }
      /**
       * An attribute limited to a set of values carries the meaning of every
       * value in its description, which replacing the text would silently
       * drop. Such an attribute is documented where its values are declared,
       * by the 'description' of its feature, so that one source states both.
       */
      if (is_array($schema['properties'][$alias]['enum'] ?? null)) {
        throw new InvalidArgumentException(
          "Spec override '" . self::ATTRIBUTE_DESCRIPTIONS . "' of '$model' describes attribute " .
          "'$alias', which is limited to a set of values; describe it with the 'description' of " .
          "its feature instead, so that the meaning of each value is kept"
        );
      }
      $schema['properties'][$alias]['description'] = $description;
    }
    return $schema;
  }

  /**
   * Widen a property schema by null: "null" joins its type and, when the
   * property is limited to a set of values, null joins that set as well,
   * because a value the property accepts that its enum omits is a schema no
   * response can satisfy.
   */
  private function makeNullable(array $property): array {
    $type = $property['type'] ?? null;
    if (is_array($type)) {
      if (!in_array("null", $type, true)) {
        $property['type'][] = "null";
      }
    }
    elseif ($type !== null && $type !== "null") {
      $property['type'] = [$type, "null"];
    }

    if (is_array($property['enum'] ?? null) && !in_array(null, $property['enum'], true)) {
      $property['enum'][] = null;
    }
    return $property;
  }
}
