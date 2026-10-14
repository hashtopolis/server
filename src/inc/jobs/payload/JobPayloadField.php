<?php

namespace Hashtopolis\inc\jobs\payload;

final class JobPayloadField {
  public const TYPE_INT = 'int';
  public const TYPE_STRING = 'string';
  public const TYPE_BOOL = 'bool';
  public const TYPE_FLOAT = 'float';
  public const TYPE_ARRAY = 'array';

  public function __construct(
    public readonly string $type,
    public readonly bool   $required = true,
  ) {}

  public function checkType(mixed $value): bool {
    return match ($this->type) {
      self::TYPE_INT => is_int($value),
      self::TYPE_STRING => is_string($value),
      self::TYPE_BOOL => is_bool($value),
      self::TYPE_FLOAT => is_float($value),
      self::TYPE_ARRAY => is_array($value),
      default => false,
    };
  }
}
