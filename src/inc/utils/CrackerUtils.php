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
   * @param int|null $accessGroupId falls back to the default access group if not given
   * @return CrackerBinaryType
   * @throws HttpConflict
   * @throws HttpError
   * @throws Exception
   */
  public static function createBinaryType(string $typeName, ?int $accessGroupId = null): CrackerBinaryType {
    $qF = new QueryFilter(CrackerBinaryType::TYPE_NAME, $typeName, "=");
    $check = Factory::getCrackerBinaryTypeFactory()->filter([Factory::FILTER => $qF], true);
    if ($check !== null) {
      throw new HttpConflict("This binary type already exists!");
    }
    else if (strlen($typeName) == 0) {
      throw new HttpError("Cracker name cannot be empty!");
    }
    if ($accessGroupId === null) {
      $accessGroupId = AccessUtils::getOrCreateDefaultAccessGroup()->getId();
    }
    else if (Factory::getAccessGroupFactory()->get($accessGroupId) === null) {
      throw new HttpError("Invalid access group selected!");
    }
    $binaryType = new CrackerBinaryType(null, $typeName, 1, $accessGroupId);
    return Factory::getCrackerBinaryTypeFactory()->save($binaryType);
  }
  
  /**
   * Creates a new cracker binary which is referenced by an external download url.
   * The server downloads a local copy of the archive into the crackers directory,
   * so it has it available for later analysis. The agents still download the
   * archive from the external download url. If the download fails or the archive
   * is not a valid 7z archive, nothing is added.
   *
   * @param string $version
   * @param string $name
   * @param string $url
   * @param int $binaryTypeId
   * @param int|null $accessGroupId must match the access group of the cracker binary type,
   *   falls back to the group of the type if not given
   * @return CrackerBinary
   * @throws HttpError
   * @throws HTException
   * @throws Exception
   */
  public static function createBinary(string $version, string $name, string $url, int $binaryTypeId, ?int $accessGroupId = null): CrackerBinary {
    $binaryType = CrackerUtils::getBinaryType($binaryTypeId);
    if (strlen($version) == 0 || strlen($name) == 0 || strlen($url) == 0) {
      throw new HttpError("Please provide all information!");
    }
    CrackerUtils::validateDownloadUrl($url);
    // create the entry first, the id is needed for the filename of the local copy
    CrackerUtils::checkAccessGroupMatchesType($binaryType, $accessGroupId);
    $binary = Factory::getCrackerBinaryFactory()->save(
      new CrackerBinary(null, $binaryType->getId(), $version, $url, $name, null, $binaryType->getAccessGroupId())
    );
    try {
      CrackerUtils::storeLocalCopy($binary);
    }
    catch (HttpError $e) {
      Factory::getCrackerBinaryFactory()->delete($binary);
      throw $e;
    }
    return $binary;
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
   * @param int|null $accessGroupId must match the access group of the cracker binary type,
   *   falls back to the group of the type if not given
   * @return CrackerBinary
   * @throws HttpError
   * @throws HTException
   * @throws Exception
   */
  public static function createBinaryFromUpload(string $version, string $name, int $binaryTypeId, string $sourceType, string $sourceData, ?int $accessGroupId = null): CrackerBinary {
    $binaryType = CrackerUtils::getBinaryType($binaryTypeId);
    if (strlen($version) == 0 || strlen($name) == 0 || strlen($sourceData) == 0) {
      throw new HttpError("Please provide all information!");
    }
    CrackerUtils::checkAccessGroupMatchesType($binaryType, $accessGroupId);
    
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
      new CrackerBinary(null, $binaryType->getId(), $version, "", $name, null, $binaryType->getAccessGroupId())
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
   * Validates that the server is allowed to fetch the given url as the
   * download url of a cracker binary archive.
   *
   * @param string $url
   * @throws HttpError
   */
  public static function validateDownloadUrl(string $url): void {
    $scheme = parse_url($url, PHP_URL_SCHEME);
    if ($scheme != "http" && $scheme != "https") {
      throw new HttpError("Only http and https download urls are supported!");
    }
  }
  
  /**
   * Downloads a local copy of the archive of a url-referenced cracker binary
   * from its download url into the crackers directory. The archive is
   * downloaded to a temporary file first, so a failed download cannot destroy
   * a previously stored local copy, and only moved into place after it was
   * validated as a 7z archive. Local copies of previous versions or urls of
   * the binary are removed.
   *
   * @param CrackerBinary $binary the binary to download the archive for
   * @throws HttpError
   * @throws HTException
   * @throws Exception
   */
  private static function storeLocalCopy(CrackerBinary $binary): void {
    $binaryType = CrackerUtils::getBinaryType($binary->getCrackerBinaryTypeId());
    $crackersPath = CrackerUtils::getCrackersPath();
    $filename = CrackerUtils::buildArchiveFilename($binaryType, $binary->getVersion());
    $target = $crackersPath . $binary->getId() . '_' . $filename;
    $temporary = $target . '.part';
    if (file_exists($temporary)) {
      unlink($temporary);
    }
    [$success, $msg] = Util::uploadFile($temporary, "url", $binary->getDownloadUrl());
    if (!$success) {
      if (file_exists($temporary)) {
        unlink($temporary);
      }
      throw new HttpError("Failed to download the archive from the download url: " . $msg);
    }
    if (!CrackerUtils::isSevenZipArchive($temporary)) {
      unlink($temporary);
      throw new HttpError("The archive at the download url is not a valid 7z archive!");
    }
    rename($temporary, $target);
    // remove local copies of previous versions or urls of this binary
    foreach (glob($crackersPath . $binary->getId() . '_*') ?: [] as $path) {
      if ($path != $target) {
        unlink($path);
      }
    }
  }
  
  /**
   * Refreshes the local copy of the archive of a url-referenced cracker binary
   * from its download url, to be called after the binary was updated in the
   * database. If the download fails, the update is rolled back by restoring
   * the given previous values before the error is rethrown, so nothing of
   * the update remains.
   *
   * @param int $binaryId
   * @param array $previousValues previous values of the updated fields, keyed by the CrackerBinary feature constants
   * @throws HttpError
   * @throws HTException
   * @throws Exception
   */
  public static function refreshLocalCopy(int $binaryId, array $previousValues): void {
    $binary = CrackerUtils::getBinary($binaryId);
    try {
      CrackerUtils::storeLocalCopy($binary);
    }
    catch (HttpError $e) {
      Factory::getCrackerBinaryFactory()->mset($binary, $previousValues);
      throw $e;
    }
  }
  
  /**
   * Removes the locally stored archive and any downloaded local copy of a
   * cracker binary, all of them are prefixed with the id of the binary.
   *
   * @throws Exception
   */
  private static function deleteLocalArchive(CrackerBinary $binary): void {
    foreach (glob(CrackerUtils::getCrackersPath() . $binary->getId() . '_*') ?: [] as $path) {
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
   * Updates a cracker binary. When the download url of a binary which is
   * referenced by an external url is changed, the server downloads a new local
   * copy of the archive from it; if that download fails, the update is rolled
   * back so nothing is changed.
   *
   * @param string $version
   * @param string $name
   * @param string $url
   * @param int $binaryId
   * @return CrackerBinaryType
   * @throws HttpError
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
    // a changed download url of a url-referenced binary requires the server to
    // download a new local copy of the archive from it
    $refreshLocalCopy = $binary->getFilename() === null && $url != $binary->getDownloadUrl();
    if ($refreshLocalCopy) {
      CrackerUtils::validateDownloadUrl($url);
    }
    $previousValues = [
      CrackerBinary::VERSION => $binary->getVersion(),
      CrackerBinary::DOWNLOAD_URL => $binary->getDownloadUrl(),
      CrackerBinary::BINARY_NAME => $binary->getBinaryName()
    ];
    $binary = Factory::getCrackerBinaryFactory()->mset($binary, [
        CrackerBinary::BINARY_NAME => htmlentities($name, ENT_QUOTES, "UTF-8"),
        CrackerBinary::DOWNLOAD_URL => $url,
        CrackerBinary::VERSION => $version
      ]
    );
    if ($refreshLocalCopy) {
      CrackerUtils::refreshLocalCopy($binary->getId(), $previousValues);
    }
    return Factory::getCrackerBinaryTypeFactory()->get($binary->getCrackerBinaryTypeId());
  }
  
  /**
   * Ensures a cracker binary is created in the access group of its type, all binaries
   * of a type are always in the same group.
   *
   * @throws HttpError
   */
  private static function checkAccessGroupMatchesType(CrackerBinaryType $binaryType, ?int $accessGroupId): void {
    if ($accessGroupId !== null && $accessGroupId != $binaryType->getAccessGroupId()) {
      throw new HttpError("The access group must match the access group of the cracker binary type!");
    }
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
