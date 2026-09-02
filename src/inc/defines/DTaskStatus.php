<?php

namespace Hashtopolis\inc\defines;

/**
 * Status values reported for a task by the aggregate field 'status'.
 *
 * These are computed values (see TaskUtils::getStatus() and
 * TaskWrapperDisplayAPI::getAggregateStatus()), not a stored column. They are
 * defined here so the generated OpenAPI spec and the code producing them share
 * a single source of truth.
 */
class DTaskStatus {
  const INIT      = 0;
  const RUNNING   = 1;
  const IDLE      = 2;
  const COMPLETED = 3;
  const SKIPPED   = 4;

  /**
   * Status value => label, in the form expected by the 'choices' entry of a
   * feature definition.
   *
   * @return array<int, string>
   */
  public static function choices(): array {
    return [
      self::INIT      => 'init',
      self::RUNNING   => 'running',
      self::IDLE      => 'idle',
      self::COMPLETED => 'completed',
      self::SKIPPED   => 'skipped',
    ];
  }
}
