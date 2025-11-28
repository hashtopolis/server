<?php

use DBA\Factory;
use DBA\Preprocessor;
use DBA\QueryFilter;
use DBA\Task;

class PreprocessorUtils {
  
  /**
   * @param string $name
   * @param string $binaryName
   * @param string $url
   * @param string $keyspaceCommand
   * @param string $skipCommand
   * @param string $limitCommand
   * @return Preprocessor
   * @throws HttpConflict
   * @throws HttpError
   */
  public static function addPreprocessor(string $name, string $binaryName, string $url, string $keyspaceCommand, string $skipCommand, string $limitCommand): Preprocessor {
    $qF = new QueryFilter(Preprocessor::NAME, $name, "=");
    $check = Factory::getPreprocessorFactory()->filter([Factory::FILTER => $qF], true);
    if ($check !== null) {
      throw new HttpConflict("This preprocessor name already exists!");
    }
    else if (strlen($name) == 0) {
      throw new HttpError("Preprocessor name cannot be empty!");
    }
    else if (strlen($binaryName) == 0) {
      throw new HttpError("Binary basename cannot be empty!");
    }
    else if (Util::containsBlacklistedChars($binaryName)) {
      throw new HttpError("The binary name must contain no blacklisted characters!");
    }
    else if (Util::containsBlacklistedChars($keyspaceCommand)) {
      throw new HttpError("The keyspace command must contain no blacklisted characters!");
    }
    else if (Util::containsBlacklistedChars($skipCommand)) {
      throw new HttpError("The skip command must contain no blacklisted characters!");
    }
    else if (Util::containsBlacklistedChars($limitCommand)) {
      throw new HttpError("The limit command must contain no blacklisted characters!");
    }
    else if (strlen($url) == 0) {
      throw new HttpError("URL cannot be empty!");
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
    return Factory::getPreprocessorFactory()->save($preprocessor);
  }
  
  /**
   * @param $preprocessorId
   * @throws HttpError
   */
  public static function delete($preprocessorId) {
    $preprocessor = PreprocessorUtils::getPreprocessor($preprocessorId);
    $qF = new QueryFilter(Task::USE_PREPROCESSOR, $preprocessor->getId(), "=");
    $check = Factory::getTaskFactory()->filter([Factory::FILTER => [$qF]]);
    if (sizeof($check) > 0) {
      throw new HttpError("There are tasks which use this preprocessor!");
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
   * Edits the name of the preprocessor
   * @throws HTException when name already exists
   */
  public static function editName($preprocessorId, $name) {
    $qF1 = new QueryFilter(Preprocessor::NAME, $name, "=");
    $qF2 = new QueryFilter(Preprocessor::PREPROCESSOR_ID, $preprocessorId, "<>");
    $check = Factory::getPreprocessorFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
    if ($check !== null) {
      throw new HTException("This preprocessor name already exists!");
    }

    $preprocessor = PreprocessorUtils::getPreprocessor($preprocessorId);
    Factory::getPreprocessorFactory()->set($preprocessor, Preprocessor::NAME, $name);
  }

  /**
   * @param $preprocessorId
   * @param $binaryName
   * Edits the binaryName of the preprocessor
   * @throws HTException when BinaryName is empty or contains blacklisted characters
   */
  public static function editBinaryName($preprocessorId, $binaryName) {

    if (strlen($binaryName) == 0) {
      throw new HTException("Binary basename cannot be empty!");
    } else if (Util::containsBlacklistedChars($binaryName)) {
      throw new HTException("The binary name must contain no blacklisted characters!");
    }
    $preprocessor = PreprocessorUtils::getPreprocessor($preprocessorId);
    Factory::getPreprocessorFactory()->set($preprocessor, Preprocessor::BINARY_NAME, $binaryName);
  }
  
  /**
   * @param $preprocessorId
   * @param $keyspaceCommand
   * Edits the keyspaceCommand of the preprocessor
   * @throws HTException when keyspaceCommand is empty or contains blacklisted characters
   */
  public static function editKeyspaceCommand($preprocessorId, $keyspaceCommand) {

    if (strlen($keyspaceCommand) == 0) {
      $keyspaceCommand == null;
    } else if (Util::containsBlacklistedChars($keyspaceCommand)) {
      throw new HTException("The keyspace command must contain no blacklisted characters!");
    }
    $preprocessor = PreprocessorUtils::getPreprocessor($preprocessorId);
    Factory::getPreprocessorFactory()->set($preprocessor, Preprocessor::KEYSPACE_COMMAND, $keyspaceCommand);
  }
  
  /**
   * @param $preprocessorId
   * @param $skipCommand
   * Edits the skipCommand of the preprocessor
   * @throws HTException when skipCommand is empty or contains blacklisted characters
   */
  public static function editSkipCommand($preprocessorId, $skipCommand) {

    if (strlen($skipCommand) == 0) {
      $skipCommand == null;
    } else if (Util::containsBlacklistedChars($skipCommand)) {
      throw new HTException("The skip command must contain no blacklisted characters!");
    }
    $preprocessor = PreprocessorUtils::getPreprocessor($preprocessorId);
    Factory::getPreprocessorFactory()->set($preprocessor, Preprocessor::SKIP_COMMAND, $skipCommand);
  }

  /**
   * @param $preprocessorId
   * @param $limitCommand
   * Edits the limitCommand of the preprocessor
   * @throws HTException when limitCommand is empty or contains blacklisted characters
   */
  public static function editLimitCommand($preprocessorId, $limitCommand) {

    if (strlen($limitCommand) == 0) {
      $limitCommand == null;
    } else if (Util::containsBlacklistedChars($limitCommand)) {
      throw new HTException("The limit command must contain no blacklisted characters!");
    }
    $preprocessor = PreprocessorUtils::getPreprocessor($preprocessorId);
    Factory::getPreprocessorFactory()->set($preprocessor, Preprocessor::LIMIT_COMMAND, $limitCommand);
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
