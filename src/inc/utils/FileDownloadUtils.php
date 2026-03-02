<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\FileDownload;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\defines\DFileDownloadStatus;

class FileDownloadUtils {
  /**
   * Adds a file to the download list if it's not already pending
   * @param int $fileId
   * @return void
   */
  public static function addDownload($fileId) {
    $qF1 = new QueryFilter(FileDownload::FILE_ID, $fileId, "=");
    $qF2 = new ContainFilter(FileDownload::STATUS, [DFileDownloadStatus::FAILED, DFileDownloadStatus::PENDING]);
    $check = Factory::getFileDownloadFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
    if ($check != null) {
      return; // file is already in pending list
    }
    $fileDownload = new FileDownload(null, time(), $fileId, DFileDownloadStatus::PENDING);
    Factory::getFileDownloadFactory()->save($fileDownload);
  }
  
  /**
   * Removes a file from the download list
   * @param int $fileId
   */
  public static function removeFile($fileId) {
    $qF = new QueryFilter(FileDownload::FILE_ID, $fileId, "=");
    Factory::getFileDownloadFactory()->massDeletion([Factory::FILTER => $qF]);
  }
}