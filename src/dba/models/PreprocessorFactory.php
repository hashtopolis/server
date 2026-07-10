<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<Preprocessor>
 */
class PreprocessorFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Preprocessor";
  }
  
  function getModelTable(): string {
    return "Preprocessor";
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
   * @return Preprocessor
   */
  function getNullObject(): Preprocessor {
    return new Preprocessor(-1, null, null, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return Preprocessor
   */
  function createObjectFromDict(array $dict): Preprocessor {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Preprocessor($dict['preprocessorid'], $dict['name'], $dict['url'], $dict['binaryname'], $dict['keyspacecommand'], $dict['skipcommand'], $dict['limitcommand']);
  }
}
