<?php

use DBA\CrackerBinary;
use DBA\CrackerBinaryType;
use DBA\QueryFilter;
use DBA\Task;
use DBA\ContainFilter;

class CrackerUtils {
  /**
   * @param CrackerBinaryType $cracker
   * @return CrackerBinary[]
   */
  public static function getBinaries($cracker) {
    global $FACTORIES;
    
    $qF = new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $cracker->getId(), "=");
    return $FACTORIES::getCrackerBinaryFactory()->filter(array($FACTORIES::FILTER => $qF));
  }
  
  /**
   * @return CrackerBinaryType[]
   */
  public static function getBinaryTypes() {
    global $FACTORIES;
    
    return $FACTORIES::getCrackerBinaryTypeFactory()->filter([]);
  }
  
  /**
   * @param string $typeName
   * @throws HTException
   */
  public static function createBinaryType($typeName) {
    global $FACTORIES;
    
    $qF = new QueryFilter(CrackerBinaryType::TYPE_NAME, $typeName, "=");
    $check = $FACTORIES::getCrackerBinaryTypeFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if ($check !== null) {
      throw new HTException("This binary type already exists!");
    }
    $binaryType = new CrackerBinaryType(0, $typeName, 1);
    $FACTORIES::getCrackerBinaryTypeFactory()->save($binaryType);
  }
  
  /**
   * @param string $version
   * @param string $name
   * @param string $url
   * @param int $binaryTypeId
   * @throws HTException
   * @return CrackerBinaryType
   */
  public static function createBinary($version, $name, $url, $binaryTypeId) {
    global $FACTORIES;
    
    $binaryType = CrackerUtils::getBinaryType($binaryTypeId);
    if (strlen($version) == 0 || strlen($name) == 0 || strlen($url) == 0) {
      throw new HTException("Please provide all information!");
    }
    $binary = new CrackerBinary(0, $binaryType->getId(), $version, $url, $name);
    $FACTORIES::getCrackerBinaryFactory()->save($binary);
    return $binaryType;
  }
  
  /**
   * @param int $binaryId
   * @throws HTException
   */
  public static function deleteBinary($binaryId) {
    global $FACTORIES;
    
    $binary = CrackerUtils::getBinary($binaryId);
    $qF = new QueryFilter(Task::CRACKER_BINARY_ID, $binary->getId(), "=");
    $check = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF));
    if (sizeof($check) > 0) {
      throw new HTException("There are tasks which use this binary!");
    }
    $FACTORIES::getCrackerBinaryFactory()->delete($binary);
  }
  
  /**
   * @param int $binaryTypeId
   * @throws HTException
   */
  public static function deleteBinaryType($binaryTypeId) {
    global $FACTORIES;
    
    $binaryType = CrackerUtils::getBinaryType($binaryTypeId);
    
    $qF = new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $binaryType->getId(), "=");
    $binaries = $FACTORIES::getCrackerBinaryFactory()->filter(array($FACTORIES::FILTER => $qF));
    $versionIds = Util::arrayOfIds($binaries);
    
    $qF = new ContainFilter(Task::CRACKER_BINARY_ID, $versionIds);
    $check = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF));
    if (sizeof($check) > 0) {
      throw new HTException("There are tasks which use binaries of this cracker!");
    }
    
    // delete
    $FACTORIES::getCrackerBinaryFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $FACTORIES::getCrackerBinaryTypeFactory()->delete($binaryType);
  }
  
  /**
   * @param string $version
   * @param string $name
   * @param string $url
   * @param int $binaryId
   * @throws HTException
   * @return CrackerBinaryType
   */
  public static function updateBinary($version, $name, $url, $binaryId) {
    global $FACTORIES;
    
    $binary = CrackerUtils::getBinary($binaryId);
    if (strlen($version) == 0 || strlen($name) == 0 || strlen($url) == 0) {
      throw new HTException("Please provide all information!");
    }
    $binary->setBinaryName(htmlentities($name, ENT_QUOTES, "UTF-8"));
    $binary->setDownloadUrl($url);
    $binary->setVersion($version);
    $FACTORIES::getCrackerBinaryFactory()->update($binary);
    $binaryType = $FACTORIES::getCrackerBinaryTypeFactory()->get($binary->getCrackerBinaryTypeId());
    return $binaryType;
  }
  
  /**
   * @param int $binaryTypeId
   * @throws HTException
   * @return CrackerBinaryType
   */
  public static function getBinaryType($binaryTypeId) {
    global $FACTORIES;
    
    $binaryType = $FACTORIES::getCrackerBinaryTypeFactory()->get($binaryTypeId);
    if ($binaryType === null) {
      throw new HTException("Invalid binary type!");
    }
    return $binaryType;
  }
  
  /**
   * @param int $binaryId
   * @throws HTException
   * @return CrackerBinary
   */
  public static function getBinary($binaryId) {
    global $FACTORIES;
    
    $binary = $FACTORIES::getCrackerBinaryFactory()->get($binaryId);
    if ($binary === null) {
      throw new HTException("Invalid cracker binary!");
    }
    return $binary;
  }
}