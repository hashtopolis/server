<?php
use DBA\QueryFilter;
use DBA\FileDownload;
use DBA\ContainFilter;

class FileDownloadUtils{
  /**
   * Adds a file to the download list if it's not already pending
   * @param int $fileId 
   * @return void
   */
  public static function addDownload($fileId){
    global $FACTORIES;

    $qF1 = new QueryFilter(FileDownload::FILE_ID, $fileId, "=");
    $qF2 = new ContainFilter(FileDownload::STATUS, [DFileDownloadStatus::FAILED, DFileDownloadStatus::PENDING]);
    $check = $FACTORIES::getFileDownloadFactory()->filter([$FACTORIES::FILTER => [$qF1, $qF2]]);
    if($check != null){
      return; // file is already in pending list
    }
    $fileDownload = new FileDownload(0, time(), $fileId, DFileDownloadStatus::PENDING);
    $FACTORIES::getFileDownloadFactory()->save($fileDownload);
  }

  /**
   * Removes a file from the download list
   * @param int $fileId 
   */
  public static function removeFile($fileId){
    global $FACTORIES;

    $qF = new QueryFilter(FileDownload::FILE_ID, $fileId, "=");
    $FACTORIES::getFileDownloadFactory()->massDeletion([$FACTORIES::FILTER => $qF]);
  }
}