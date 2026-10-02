<?php

namespace Hashtopolis\inc\jobs;

use Hashtopolis\inc\HTException;
use Hashtopolis\inc\jobs\payload\JobPayloadField;

final class BackgroundJobPayloadValidator {
  /**
   * Checks that the given payload satisfies the field definition of a job. Only declared
   * fields are checked, additional keys in the payload are allowed and ignored.
   *
   * @param array $payload decoded JSON payload of the job
   * @param array<string, JobPayloadField> $definition payload key to field definition
   * @throws HTException if the payload violates the definition
   */
  public static function validate(array $payload, array $definition): void {
    foreach ($definition as $key => $field) {
      if (!array_key_exists($key, $payload)) {
        if ($field->required) {
          throw new HTException("Missing required payload key '$key'!");
        }
        continue;
      }
      if (!$field->checkType($payload[$key])) {
        throw new HTException("Invalid type for payload key '$key', expected " . $field->type . "!");
      }
    }
  }
}
