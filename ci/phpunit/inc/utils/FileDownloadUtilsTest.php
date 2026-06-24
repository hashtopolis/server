<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\File;
use Hashtopolis\dba\models\FileDownload;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\defines\DFileDownloadStatus;
use Hashtopolis\TestBase;
use Override;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class FileDownloadUtilsTest extends TestBase {
  private FileDownload $fileDownload;
  private File         $file;
  
  #[Override]
  protected function setUp(): void {
    parent::setUp();
    $group = $this->createAccessGroup('fdl_group');
    $this->file = $this->createFile($group);
    $this->fileDownload = $this->createFileDownload($this->file->getId(), DFileDownloadStatus::DONE);
  }
  
  public function testAddDownloadCreatesPendingDownload(): void {
    $group = $this->createAccessGroup('fdl_new');
    $newFile = $this->createFile($group);
    
    FileDownloadUtils::addDownload($newFile->getId());
    
    $qF1 = new QueryFilter(FileDownload::FILE_ID, $newFile->getId(), '=');
    $qF2 = new QueryFilter(FileDownload::STATUS, DFileDownloadStatus::PENDING, '=');
    $result = Factory::getFileDownloadFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
    
    $this->assertInstanceOf(FileDownload::class, $result);
    $this->assertSame($newFile->getId(), $result->getFileId());
    $this->assertSame(DFileDownloadStatus::PENDING, $result->getStatus());
    $this->registerDatabaseObject(Factory::getFileDownloadFactory(), $result);
  }
  
  public function testAddDownloadSkipsExistingPending(): void {
    $this->createFileDownload($this->file->getId());
    
    FileDownloadUtils::addDownload($this->file->getId());
    
    $qF1 = new QueryFilter(FileDownload::FILE_ID, $this->file->getId(), '=');
    $qF2 = new QueryFilter(FileDownload::STATUS, DFileDownloadStatus::PENDING, '=');
    $pending = Factory::getFileDownloadFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
    $this->assertCount(1, $pending);
  }
  
  public function testAddDownloadCreatesNewForCompletedFile(): void {
    FileDownloadUtils::addDownload($this->file->getId());
    
    $qF1 = new QueryFilter(FileDownload::FILE_ID, $this->file->getId(), '=');
    $qF2 = new QueryFilter(FileDownload::STATUS, DFileDownloadStatus::PENDING, '=');
    $pending = Factory::getFileDownloadFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
    
    $this->assertInstanceOf(FileDownload::class, $pending);
    $this->assertSame($this->file->getId(), $pending->getFileId());
    $this->assertSame(DFileDownloadStatus::PENDING, $pending->getStatus());
    $this->registerDatabaseObject(Factory::getFileDownloadFactory(), $pending);
  }
  
  public function testRemoveFileDeletesDownloads(): void {
    FileDownloadUtils::removeFile($this->fileDownload->getFileId());
    
    $qF = new QueryFilter(FileDownload::FILE_ID, $this->fileDownload->getFileId(), '=');
    $remaining = Factory::getFileDownloadFactory()->filter([Factory::FILTER => $qF]);
    $this->assertSame([], $remaining);
  }
  
  public function testRemoveFileIsNoopForNonExistent(): void {
    FileDownloadUtils::removeFile(-1);
  }
}
