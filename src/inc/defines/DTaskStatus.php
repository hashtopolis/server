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
   * Status value => what that value means, in the form expected by the
   * 'choices' entry of a feature definition. The descriptions are what the API
   * reference shows for each value.
   *
   * @return array<int, string>
   */
  public static function choices(): array {
    return [
      self::INIT      => 'The starting value, before any task of the wrapper has been looked at',
      self::RUNNING   => 'An agent reported on a chunk of the task within the chunk timeout',
      self::IDLE      => 'Work is left, but no agent has reported on it within the chunk timeout',
      self::COMPLETED => 'The whole keyspace was searched, or every hash of the hashlist is cracked and this task found one',
      self::SKIPPED   => 'Archived, or every hash of the hashlist was cracked without this task finding one',
    ];
  }

  /**
   * Status value => the name of the constant holding it, in the form expected
   * by the 'choice_names' entry of a feature definition. It names the values
   * in the generated OpenAPI spec, for a reader and for code generators.
   *
   * @return array<int, string>
   */
  public static function choiceNames(): array {
    return [
      self::INIT      => 'INIT',
      self::RUNNING   => 'RUNNING',
      self::IDLE      => 'IDLE',
      self::COMPLETED => 'COMPLETED',
      self::SKIPPED   => 'SKIPPED',
    ];
  }
}
