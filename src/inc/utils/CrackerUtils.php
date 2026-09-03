<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Pretask;
use Hashtopolis\inc\defines\DDirectories;
use Hashtopolis\inc\apiv2\error\HttpConflict;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\Util;

class CrackerUtils {
  /**
   * @param CrackerBinaryType $cracker
   * @return CrackerBinary[]
   * @throws Exception
   */
  public static function getBinaries(CrackerBinaryType $cracker): array {
    $qF = new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $cracker->getId(), "=");
    return Factory::getCrackerBinaryFactory()->filter([Factory::FILTER => $qF]);
  }
  
  /**
   * @return CrackerBinaryType[]
   * @throws Exception
   */
  public static function getBinaryTypes(): array {
    return Factory::getCrackerBinaryTypeFactory()->filter([]);
  }
  
  /**
   * @param string $typeName
   * @return CrackerBinaryType
   * @throws HttpConflict
   * @throws HttpError
   * @throws Exception
   */
  public static function createBinaryType(string $typeName): CrackerBinaryType {
    $qF = new QueryFilter(CrackerBinaryType::TYPE_NAME, $typeName, "=");
    $check = Factory::getCrackerBinaryTypeFactory()->filter([Factory::FILTER => $qF], true);
    if ($check !== null) {
      throw new HttpConflict("This binary type already exists!");
    }
    else if (strlen($typeName) == 0) {
      throw new HttpError("Cracker name cannot be empty!");
    }
    $binaryType = new CrackerBinaryType(null, $typeName, 1);
    return Factory::getCrackerBinaryTypeFactory()->save($binaryType);
  }
  
  /**
   * @param string $version
   * @param string $name
   * @param string $url
   * @param int $binaryTypeId
   * @return CrackerBinary
   * @throws HttpError
   * @throws HTException
   * @throws Exception
   */
  public static function createBinary(string $version, string $name, string $url, int $binaryTypeId): CrackerBinary {
    $binaryType = CrackerUtils::getBinaryType($binaryTypeId);
    if (strlen($version) == 0 || strlen($name) == 0 || strlen($url) == 0) {
      throw new HttpError("Please provide all information!");
    }
    $binary = new CrackerBinary(null, $binaryType->getId(), $version, $url, $name, null);
    return Factory::getCrackerBinaryFactory()->save($binary);
  }
  
  /**
   * Creates a new cracker binary from an uploaded 7z archive. The archive is stored in
   * the crackers directory and the downloadUrl is set to the download endpoint of this
   * server, so it can directly be used by the agents to download the binary.
   *
   * @param string $version
   * @param string $name
   * @param int $binaryTypeId
   * @param string $sourceType choices inline, import, url
   * @param string $sourceData base64 data, filename in the import directory or download url
   * @return CrackerBinary
   * @throws HttpError
   * @throws HTException
   * @throws Exception
   */
  public static function createBinaryFromUpload(string $version, string $name, int $binaryTypeId, string $sourceType, string $sourceData): CrackerBinary {
    $binaryType = CrackerUtils::getBinaryType($binaryTypeId);
    if (strlen($version) == 0 || strlen($name) == 0 || strlen($sourceData) == 0) {
      throw new HttpError("Please provide all information!");
    }
    
    // determine the source of the archive and validate it
    switch ($sourceType) {
      case "inline":
        $archiveData = base64_decode($sourceData, true);
        if ($archiveData === false) {
          throw new HttpError("sourceData not valid base64 encoding");
        }
        $uploadType = "paste";
        $uploadData = $archiveData;
        break;
      case "import":
        $realname = str_replace(" ", "_", htmlentities(basename($sourceData), ENT_QUOTES, "UTF-8"));
        if ($sourceData != $realname) {
          throw new HttpError("sourceData is invalid filename suggestion '$realname'");
        }
        $uploadType = "import";
        $uploadData = $sourceData;
        break;
      case "url":
        $scheme = parse_url($sourceData, PHP_URL_SCHEME);
        if ($scheme != "http" && $scheme != "https") {
          throw new HttpError("Only http and https URLs are supported as sourceData!");
        }
        $uploadType = "url";
        $uploadData = $sourceData;
        break;
      default:
        throw new HttpError("sourceType value '" . $sourceType . "' is not supported (choices inline, import, url");
    }
    
    $filename = CrackerUtils::buildArchiveFilename($binaryType, $version);
    
    // create the entry first with a placeholder download url, the final one
    // contains the id and can only be set once it is known
    $binary = Factory::getCrackerBinaryFactory()->save(
      new CrackerBinary(null, $binaryType->getId(), $version, "", $name, null)
    );
    
    $target = CrackerUtils::getCrackersPath() . $binary->getId() . '_' . $filename;
    [$success, $msg] = Util::uploadFile($target, $uploadType, $uploadData);
    if (!$success) {
      Factory::getCrackerBinaryFactory()->delete($binary);
      throw new HttpError("Failed to store the archive: " . $msg);
    }
    
    if (!CrackerUtils::isSevenZipArchive($target)) {
      // in case the archive was imported, put the file back to the import directory
      if ($sourceType == "import") {
        rename($target, CrackerUtils::getImportPath() . $sourceData);
      }
      else {
        unlink($target);
      }
      Factory::getCrackerBinaryFactory()->delete($binary);
      throw new HttpError("The provided archive is not a valid 7z archive!");
    }
    
    return Factory::getCrackerBinaryFactory()->mset($binary, [
      CrackerBinary::DOWNLOAD_URL => Util::buildBackendBaseUrl() . '/api/download.php/crackerBinary/' . $binary->getId(),
      CrackerBinary::FILENAME => $filename
    ]);
  }
  
  /**
   * @throws Exception
   */
  public static function getCrackersPath(): string {
    return rtrim(Factory::getStoredValueFactory()->get(DDirectories::CRACKERS)->getVal(), '/') . '/';
  }
  
  /**
   * @throws Exception
   */
  private static function getImportPath(): string {
    return rtrim(Factory::getStoredValueFactory()->get(DDirectories::IMPORT)->getVal(), '/') . '/';
  }
  
  /**
   * Composed server-side archive filename for a locally stored cracker binary,
   * the '.7z' extension is enforced by construction.
   */
  private static function buildArchiveFilename(CrackerBinaryType $binaryType, string $version): string {
    $sanitized = preg_replace('/[^A-Za-z0-9._-]/', '-', $binaryType->getTypeName() . '-' . $version) ?? '';
    return $sanitized . '.7z';
  }
  
  private static function isSevenZipArchive(string $path): bool {
    $magic = "\x37\x7A\xBC\xAF\x27\x1C";
    $fp = @fopen($path, "rb");
    if ($fp === false) {
      return false;
    }
    $header = fread($fp, strlen($magic));
    fclose($fp);
    return $header === $magic;
  }
  
  /**
   * Removes the locally stored archive of a cracker binary if there is one.
   *
   * @throws Exception
   */
  private static function deleteLocalArchive(CrackerBinary $binary): void {
    if ($binary->getFilename() !== null) {
      $path = CrackerUtils::getCrackersPath() . $binary->getId() . '_' . $binary->getFilename();
      if (file_exists($path)) {
        unlink($path);
      }
    }
  }
  
  /**
   * @param int $binaryId
   * @throws HTException
   * @throws Exception
   */
  public static function deleteBinary(int $binaryId): void {
    $binary = CrackerUtils::getBinary($binaryId);
    $qF = new QueryFilter(Task::CRACKER_BINARY_ID, $binary->getId(), "=");
    $check = Factory::getTaskFactory()->filter([Factory::FILTER => $qF]);
    if (sizeof($check) > 0) {
      throw new HTException("There are tasks which use this binary!");
    }
    // remove a locally stored archive if there is one
    CrackerUtils::deleteLocalArchive($binary);
    Factory::getCrackerBinaryFactory()->delete($binary);
  }
  
  /**
   * @param int $binaryTypeId
   * @throws HTException
   * @throws Exception
   */
  public static function deleteBinaryType(int $binaryTypeId): void {
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
    
    // remove the archives of locally stored binaries
    foreach ($binaries as $binary) {
      CrackerUtils::deleteLocalArchive($binary);
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
   * @throws Exception
   */
  public static function updateBinary(string $version, string $name, string $url, int $binaryId): CrackerBinaryType {
    $binary = CrackerUtils::getBinary($binaryId);
    if (strlen($version) == 0 || strlen($name) == 0 || strlen($url) == 0) {
      throw new HTException("Please provide all information!");
    }
    // locally stored binaries are downloaded from this server, so the url is owned by the server
    if ($binary->getFilename() !== null && $url != $binary->getDownloadUrl()) {
      throw new HTException("The download url of a locally stored cracker binary cannot be changed!");
    }
    $binary = Factory::getCrackerBinaryFactory()->mset($binary, [
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
   * @throws Exception
   */
  public static function getBinaryType(int $binaryTypeId): CrackerBinaryType {
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
   * @throws Exception
   */
  public static function getBinary(int $binaryId): CrackerBinary {
    $binary = Factory::getCrackerBinaryFactory()->get($binaryId);
    if ($binary === null) {
      throw new HTException("Invalid cracker binary!");
    }
    return $binary;
  }
}
