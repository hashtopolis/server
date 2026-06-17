<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\File;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\defines\DFileType;
use Hashtopolis\inc\HTException;
use Hashtopolis\TestBase;
use Override;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class FileUtilsTest extends TestBase {
  private User $user;
  private AccessGroup $group;
  private File $file;
  private File $ruleFile;
  private File $wordlistFile;
  private File $otherFile;

  #[Override]
  protected function setUp(): void {
    parent::setUp();

    $this->user = $this->createUser('fu_user');
    $this->group = $this->createAccessGroup('fu_group');
    $this->createAccessGroupUser($this->user, $this->group);

    $this->file = $this->createFile($this->group);
    $this->ruleFile = $this->createFile($this->group, 0, 'test_rule_' . uniqid() . '.rule', 512, DFileType::RULE);
    $this->wordlistFile = $this->createFile($this->group);
    $this->otherFile = $this->createFile($this->group, 0, 'test_other_' . uniqid() . '.bin', 256, DFileType::OTHER);
  }

  public function testGetFileReturnsFileForAuthorizedUser(): void {
    $result = FileUtils::getFile($this->file->getId(), $this->user);
    $this->assertInstanceOf(File::class, $result);
    $this->assertSame($this->file->getId(), $result->getId());
  }

  public function testGetFileThrowsForInvalidId(): void {
    $this->expectException(HTException::class);
    FileUtils::getFile(-1, $this->user);
  }

  public function testGetFileThrowsForUnauthorizedUser(): void {
    $otherGroup = $this->createAccessGroup('fu_other');
    $otherFile = $this->createFile($otherGroup);

    $this->expectException(HTException::class);
    FileUtils::getFile($otherFile->getId(), $this->user);
  }

  public function testSetFileTypeUpdatesType(): void {
    FileUtils::setFileType($this->file->getId(), DFileType::RULE, $this->user);

    $updated = Factory::getFileFactory()->get($this->file->getId());
    $this->assertSame(DFileType::RULE, $updated->getFileType());
  }

  public function testSetFileTypeThrowsForInvalidType(): void {
    $this->expectException(HTException::class);
    FileUtils::setFileType($this->file->getId(), 999, $this->user);
  }

  public function testSwitchSecretTogglesSecret(): void {
    FileUtils::switchSecret($this->file->getId(), 1, $this->user);

    $updated = Factory::getFileFactory()->get($this->file->getId());
    $this->assertSame(1, $updated->getIsSecret());

    FileUtils::switchSecret($this->file->getId(), 0, $this->user);

    $updated = Factory::getFileFactory()->get($this->file->getId());
    $this->assertSame(0, $updated->getIsSecret());
  }

  public function testGetFilesReturnsFilesInUserAccessGroups(): void {
    $files = FileUtils::getFiles($this->user);
    $fileIds = array_map(fn(File $f) => $f->getId(), $files);

    $this->assertContains($this->file->getId(), $fileIds);
    $this->assertContains($this->ruleFile->getId(), $fileIds);
    $this->assertContains($this->wordlistFile->getId(), $fileIds);
    $this->assertContains($this->otherFile->getId(), $fileIds);
  }

  public function testGetFilesExcludesTemporaryFiles(): void {
    $tempFile = $this->createFile($this->group, 0, 'temp_' . uniqid() . '.tmp', 0, DFileType::TEMPORARY);

    $files = FileUtils::getFiles($this->user);
    $fileIds = array_map(fn(File $f) => $f->getId(), $files);

    $this->assertNotContains($tempFile->getId(), $fileIds);
  }

  public function testLoadFilesByCategoryCategorizesFiles(): void {
    [$rules, $wordlists, $other] = FileUtils::loadFilesByCategory($this->user, []);

    $ruleIds = array_map(fn($set) => $set->getAllValues()['file']->getId(), $rules);
    $wlIds = array_map(fn($set) => $set->getAllValues()['file']->getId(), $wordlists);
    $otherIds = array_map(fn($set) => $set->getAllValues()['file']->getId(), $other);

    $this->assertContains($this->ruleFile->getId(), $ruleIds);
    $this->assertContains($this->file->getId(), $wlIds);
    $this->assertContains($this->wordlistFile->getId(), $wlIds);
    $this->assertContains($this->otherFile->getId(), $otherIds);
  }

  public function testLoadFilesByCategoryMarksCheckedFiles(): void {
    [$rules, $wordlists, $other] = FileUtils::loadFilesByCategory($this->user, [$this->file->getId()]);

    $checkedIds = [];
    foreach (array_merge($rules, $wordlists, $other) as $set) {
      $data = $set->getAllValues();
      if ($data['checked'] === '1') {
        $checkedIds[] = $data['file']->getId();
      }
    }

    $this->assertContains($this->file->getId(), $checkedIds);
  }

  public function testDeleteThrowsForInvalidId(): void {
    $this->expectException(HTException::class);
    FileUtils::delete(-1, $this->user);
  }

  public function testDeleteThrowsWhenFileInUseByTask(): void {
    $this->expectException(HTException::class);

    $hashType = $this->createHashType();
    $hashlist = $this->createHashlist($this->group, $hashType);
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);
    $taskWrapper = $this->createTaskWrapper($this->group, $hashlist);
    $task = $this->createTask($taskWrapper, $crackerBinary, $crackerBinaryType);
    $this->createFileTask($this->file, $task);

    FileUtils::delete($this->file->getId(), $this->user);
  }
}
