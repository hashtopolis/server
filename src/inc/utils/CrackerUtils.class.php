<?php

use DBA\CrackerBinary;
use DBA\CrackerBinaryType;
use DBA\QueryFilter;
use DBA\Task;
use DBA\ContainFilter;
use DBA\Factory;
use DBA\Pretask;

require_once __DIR__ . '/../apiv2/common/ErrorHandler.class.php';
class CrackerUtils {
  /**
   * @param CrackerBinaryType $cracker
   * @return CrackerBinary[]
   */
  public static function getBinaries($cracker) {
    $qF = new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $cracker->getId(), "=");
    return Factory::getCrackerBinaryFactory()->filter([Factory::FILTER => $qF]);
  }
  
  /**
   * @return CrackerBinaryType[]
   */
  public static function getBinaryTypes() {
    return Factory::getCrackerBinaryTypeFactory()->filter([]);
  }
  
  /**
   * @param string $typeName
   * @throws HTException
   */
  public static function createBinaryType($typeName) {
    $qF = new QueryFilter(CrackerBinaryType::TYPE_NAME, $typeName, "=");
    $check = Factory::getCrackerBinaryTypeFactory()->filter([Factory::FILTER => $qF], true);
    if ($check !== null) {
      throw new HttpConflict("This binary type already exists!");
    }
    else if (strlen($typeName) == 0) {
      throw new HttpError("Cracker name cannot be empty!");
    }
    $binaryType = new CrackerBinaryType(null, $typeName, 1);
    Factory::getCrackerBinaryTypeFactory()->save($binaryType);
  }
  
  /**
   * @param string $version
   * @param string $name
   * @param string $url
   * @param int $binaryTypeId
   * @return CrackerBinaryType
   * @throws HttpError
   */
  public static function createBinary($version, $name, $url, $binaryTypeId) {
    $binaryType = CrackerUtils::getBinaryType($binaryTypeId);
    if (strlen($version) == 0 || strlen($name) == 0 || strlen($url) == 0) {
      throw new HttpError("Please provide all information!");
    }
    $binary = new CrackerBinary(null, $binaryType->getId(), $version, $url, $name);
    Factory::getCrackerBinaryFactory()->save($binary);
    return $binaryType;
  }
  
  /**
   * @param int $binaryId
   * @throws HTException
   */
  public static function deleteBinary($binaryId) {
    $binary = CrackerUtils::getBinary($binaryId);
    $qF = new QueryFilter(Task::CRACKER_BINARY_ID, $binary->getId(), "=");
    $check = Factory::getTaskFactory()->filter([Factory::FILTER => $qF]);
    if (sizeof($check) > 0) {
      throw new HTException("There are tasks which use this binary!");
    }
    Factory::getCrackerBinaryFactory()->delete($binary);
  }
  
  /**
   * @param int $binaryTypeId
   * @throws HTException
   */
  public static function deleteBinaryType($binaryTypeId) {
    $binaryType = CrackerUtils::getBinaryType($binaryTypeId);
    
    $qF = new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $binaryType->getId(), "=");
    $binaries = Factory::getCrackerBinaryFactory()->filter([Factory::FILTER => $qF]);
    $versionIds = Util::arrayOfIds($binaries);
    
    // check if there are tasks which use a binary of this type
    $qF = new ContainFilter(Task::CRACKER_BINARY_ID, $versionIds);
    $check = Factory::getTaskFactory()->filter([Factory::FILTER => $qF]);
    if (sizeof($check) > 0) {
      throw new HTException("There are tasks which use binaries of this cracker!");
    }
    
    // check if there are pretasks using this type
    $qF2 = new QueryFilter(Pretask::CRACKER_BINARY_TYPE_ID, $binaryTypeId, "=");
    $check = Factory::getPretaskFactory()->filter([Factory::FILTER => $qF2]);
    if (sizeof($check) > 0) {
      throw new HTException("There are pretasks which use this cracker type!");
    }
    
    // delete
    Factory::getCrackerBinaryFactory()->massDeletion([Factory::FILTER => $qF]);
    Factory::getCrackerBinaryTypeFactory()->delete($binaryType);
  }
  
  /**
   * @param string $version
   * @param string $name
   * @param string $url
   * @param int $binaryId
   * @return CrackerBinaryType
   * @throws HTException
   */
  public static function updateBinary($version, $name, $url, $binaryId) {
    $binary = CrackerUtils::getBinary($binaryId);
    if (strlen($version) == 0 || strlen($name) == 0 || strlen($url) == 0) {
      throw new HTException("Please provide all information!");
    }
    Factory::getCrackerBinaryFactory()->mset($binary, [
        CrackerBinary::BINARY_NAME => htmlentities($name, ENT_QUOTES, "UTF-8"),
        CrackerBinary::DOWNLOAD_URL => $url,
        CrackerBinary::VERSION => $version
      ]
    );
    return Factory::getCrackerBinaryTypeFactory()->get($binary->getCrackerBinaryTypeId());
  }
  
  /**
   * @param int $binaryTypeId
   * @return CrackerBinaryType
   * @throws HTException
   */
  public static function getBinaryType($binaryTypeId) {
    $binaryType = Factory::getCrackerBinaryTypeFactory()->get($binaryTypeId);
    if ($binaryType === null) {
      throw new HTException("Invalid binary type!");
    }
    return $binaryType;
  }
  
  /**
   * @param int $binaryId
   * @return CrackerBinary
   * @throws HTException
   */
  public static function getBinary($binaryId) {
    $binary = Factory::getCrackerBinaryFactory()->get($binaryId);
    if ($binary === null) {
      throw new HTException("Invalid cracker binary!");
    }
    return $binary;
  }
}