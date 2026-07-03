<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<LogEntry>
 */
class LogEntryFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "LogEntry";
  }
  
  function getModelTable(): string {
    return "LogEntry";
  }

  function isMapping(): bool {
    return False;
  }
  
  function isCachable(): bool {
    return false;
  }
  
  function getCacheValidTime(): int {
    return -1;
  }
  
  /**
   * @return LogEntry
   */
  function getNullObject(): LogEntry {
    return new LogEntry(-1, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return LogEntry
   */
  function createObjectFromDict($pk, $dict): LogEntry {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new LogEntry($dict['logentryid'], $dict['issuer'], $dict['issuerid'], $dict['level'], $dict['message'], $dict['time']);
  }
}
