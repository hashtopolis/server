<?php

use DBA\Factory;
use DBA\Preprocessor;
use DBA\QueryFilter;
use DBA\Task;

class PreprocessorUtils {
  
  /**
   * @param $name
   * @param $binaryName
   * @param $url
   * @param $keyspaceCommand
   * @param $skipCommand
   * @param $limitCommand
   * @throws HTException
   */
  public static function addPreprocessor($name, $binaryName, $url, $keyspaceCommand, $skipCommand, $limitCommand) {
    $qF = new QueryFilter(Preprocessor::NAME, $name, "=");
    $check = Factory::getPreprocessorFactory()->filter([Factory::FILTER => $qF], true);
    if ($check !== null) {
      throw new HTException("This preprocessor name already exists!");
    }
    else if (strlen($name) == 0) {
      throw new HTException("Preprocessor name cannot be empty!");
    }
    else if (strlen($binaryName) == 0) {
      throw new HTException("Binary basename cannot be empty!");
    }
    else if (Util::containsBlacklistedChars($binaryName)) {
      throw new HTException("The binary name must contain no blacklisted characters!");
    }
    else if (Util::containsBlacklistedChars($keyspaceCommand)) {
      throw new HTException("The keyspace command must contain no blacklisted characters!");
    }
    else if (Util::containsBlacklistedChars($skipCommand)) {
      throw new HTException("The skip command must contain no blacklisted characters!");
    }
    else if (Util::containsBlacklistedChars($limitCommand)) {
      throw new HTException("The limit command must contain no blacklisted characters!");
    }
    else if (strlen($url) == 0) {
      throw new HTException("URL cannot be empty!");
    }
    
    if (strlen($keyspaceCommand) == 0) {
      $keyspaceCommand = null;
    }
    if (strlen($skipCommand) == 0) {
      $skipCommand = null;
    }
    if (strlen($limitCommand) == 0) {
      $limitCommand = null;
    }
    
    $preprocessor = new Preprocessor(null, $name, $url, $binaryName, $keyspaceCommand, $skipCommand, $limitCommand);
    Factory::getPreprocessorFactory()->save($preprocessor);
  }
  
  /**
   * @param $preprocessorId
   * @throws HTException
   */
  public static function delete($preprocessorId) {
    $preprocessor = PreprocessorUtils::getPreprocessor($preprocessorId);
    $qF = new QueryFilter(Task::USE_PREPROCESSOR, $preprocessor->getId(), "=");
    $check = Factory::getTaskFactory()->filter([Factory::FILTER => [$qF]]);
    if (sizeof($check) > 0) {
      throw new HTException("There are tasks which use this preprocessor!");
    }
    Factory::getPreprocessorFactory()->delete($preprocessor);
  }
  
  /**
   * @param $preprocessorId
   * @return Preprocessor
   * @throws HTException
   */
  public static function getPreprocessor($preprocessorId) {
    $preprocessor = Factory::getPreprocessorFactory()->get($preprocessorId);
    if ($preprocessor === null) {
      throw new HTException("Invalid preprocessor!");
    }
    return $preprocessor;
  }
  
  /**
   * @param $preprocessorId
   * @param $name
   * @param $binaryName
   * @param $url
   * @param $keyspaceCommand
   * @param $skipCommand
   * @param $limitCommand
   * @throws HTException
   */
  public static function editPreprocessor($preprocessorId, $name, $binaryName, $url, $keyspaceCommand, $skipCommand, $limitCommand) {
    $preprocessor = PreprocessorUtils::getPreprocessor($preprocessorId);
    
    $qF1 = new QueryFilter(Preprocessor::NAME, $name, "=");
    $qF2 = new QueryFilter(Preprocessor::PREPROCESSOR_ID, $preprocessor->getId(), "<>");
    $check = Factory::getPreprocessorFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
    if ($check !== null) {
      throw new HTException("This preprocessor name already exists!");
    }
    else if (strlen($name) == 0) {
      throw new HTException("Preprocessor name cannot be empty!");
    }
    else if (strlen($binaryName) == 0) {
      throw new HTException("Binary basename cannot be empty!");
    }
    else if (Util::containsBlacklistedChars($binaryName)) {
      throw new HTException("The binary name must contain no blacklisted characters!");
    }
    else if (Util::containsBlacklistedChars($keyspaceCommand)) {
      throw new HTException("The keyspace command must contain no blacklisted characters!");
    }
    else if (Util::containsBlacklistedChars($skipCommand)) {
      throw new HTException("The skip command must contain no blacklisted characters!");
    }
    else if (Util::containsBlacklistedChars($limitCommand)) {
      throw new HTException("The limit command must contain no blacklisted characters!");
    }
    else if (strlen($url) == 0) {
      throw new HTException("URL cannot be empty!");
    }
    
    if (strlen($keyspaceCommand) == 0) {
      $keyspaceCommand = null;
    }
    if (strlen($skipCommand) == 0) {
      $skipCommand = null;
    }
    if (strlen($limitCommand) == 0) {
      $limitCommand = null;
    }
    
    Factory::getPreprocessorFactory()->mset($preprocessor, [
        Preprocessor::NAME => $name,
        Preprocessor::BINARY_NAME => $binaryName,
        Preprocessor::URL => $url,
        Preprocessor::KEYSPACE_COMMAND => $keyspaceCommand,
        Preprocessor::SKIP_COMMAND => $skipCommand,
        Preprocessor::LIMIT_COMMAND => $limitCommand
      ]
    );
  }
}