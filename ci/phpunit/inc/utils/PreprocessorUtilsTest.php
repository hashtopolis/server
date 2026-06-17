<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Preprocessor;
use Hashtopolis\dba\models\Task;
use Hashtopolis\inc\apiv2\error\HttpConflict;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\HTException;
use Hashtopolis\TestBase;
use Override;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class PreprocessorUtilsTest extends TestBase {
  private Preprocessor $preprocessor;

  #[Override]
  protected function setUp(): void {
    parent::setUp();
    $this->preprocessor = PreprocessorUtils::addPreprocessor(
      'test_pp_' . uniqid(),
      'test_binary_' . uniqid(),
      'https://example.com/test.zip',
      '--keyspace',
      '--skip',
      '--limit'
    );
  }

  #[Override]
  protected function tearDown(): void {
    try {
      PreprocessorUtils::delete($this->preprocessor->getId());
    }
    catch (Exception) {
    }
    parent::tearDown();
  }

  private function createPreprocessor(string $suffix = ''): Preprocessor {
    $suffix = $suffix ?: uniqid();
    $pp = PreprocessorUtils::addPreprocessor(
      'tmp_pp_' . $suffix,
      'tmp_binary_' . $suffix,
      'https://example.com/' . $suffix . '.zip',
      '--ks',
      '--sk',
      '--lm'
    );
    $this->registerDatabaseObject(Factory::getPreprocessorFactory(), $pp);
    return $pp;
  }

  public function testAddPreprocessorCreatesWithValidData(): void {
    $name = 'add_create_' . uniqid();
    $binaryName = 'add_binary_' . uniqid();
    $url = 'https://example.com/add_create.zip';

    $pp = PreprocessorUtils::addPreprocessor($name, $binaryName, $url, '--keyspace', '--skip', '--limit');
    $this->registerDatabaseObject(Factory::getPreprocessorFactory(), $pp);

    $this->assertInstanceOf(Preprocessor::class, $pp);
    $this->assertSame($name, $pp->getName());
    $this->assertSame($binaryName, $pp->getBinaryName());
    $this->assertSame($url, $pp->getUrl());
    $this->assertSame('--keyspace', $pp->getKeyspaceCommand());
    $this->assertSame('--skip', $pp->getSkipCommand());
    $this->assertSame('--limit', $pp->getLimitCommand());
    $this->assertNotNull($pp->getId());
  }

  public function testAddPreprocessorConvertsEmptyCommandsToNull(): void {
    $pp = PreprocessorUtils::addPreprocessor(
      'add_null_cmds_' . uniqid(),
      'binary_null',
      'https://example.com/null.zip',
      '', '', ''
    );
    $this->registerDatabaseObject(Factory::getPreprocessorFactory(), $pp);

    $this->assertNull($pp->getKeyspaceCommand());
    $this->assertNull($pp->getSkipCommand());
    $this->assertNull($pp->getLimitCommand());
  }

  public function testAddPreprocessorThrowsForDuplicateName(): void {
    $this->expectException(HttpConflict::class);
    PreprocessorUtils::addPreprocessor(
      $this->preprocessor->getName(),
      'binary_dup',
      'https://example.com/dup.zip',
      '', '', ''
    );
  }

  public function testAddPreprocessorThrowsForEmptyName(): void {
    $this->expectException(HttpError::class);
    PreprocessorUtils::addPreprocessor('', 'binary', 'https://example.com/e.zip', '', '', '');
  }

  public function testAddPreprocessorThrowsForEmptyBinaryName(): void {
    $this->expectException(HttpError::class);
    PreprocessorUtils::addPreprocessor('name', '', 'https://example.com/e.zip', '', '', '');
  }

  public function testAddPreprocessorThrowsForEmptyUrl(): void {
    $this->expectException(HttpError::class);
    PreprocessorUtils::addPreprocessor('name', 'binary', '', '', '', '');
  }

  public function testAddPreprocessorThrowsForBlacklistedBinaryName(): void {
    $this->expectException(HttpError::class);
    PreprocessorUtils::addPreprocessor('name', 'bad|binary', 'https://example.com/b.zip', '', '', '');
  }

  public function testAddPreprocessorThrowsForBlacklistedKeyspace(): void {
    $this->expectException(HttpError::class);
    PreprocessorUtils::addPreprocessor(
      'name', 'binary', 'https://example.com/b.zip',
      '--keyspace;rm', '--skip', '--limit'
    );
  }

  public function testAddPreprocessorThrowsForBlacklistedSkip(): void {
    $this->expectException(HttpError::class);
    PreprocessorUtils::addPreprocessor(
      'name', 'binary', 'https://example.com/b.zip',
      '--keyspace', '--skip$test', '--limit'
    );
  }

  public function testAddPreprocessorThrowsForBlacklistedLimit(): void {
    $this->expectException(HttpError::class);
    PreprocessorUtils::addPreprocessor(
      'name', 'binary', 'https://example.com/b.zip',
      '--keyspace', '--skip', '--limit&test&'
    );
  }

  public function testGetPreprocessorReturnsPreprocessor(): void {
    $retrieved = PreprocessorUtils::getPreprocessor($this->preprocessor->getId());
    $this->assertInstanceOf(Preprocessor::class, $retrieved);
    $this->assertSame($this->preprocessor->getId(), $retrieved->getId());
  }

  public function testGetPreprocessorThrowsForInvalidId(): void {
    $this->expectException(HTException::class);
    PreprocessorUtils::getPreprocessor(-1);
  }

  public function testDeleteRemovesPreprocessor(): void {
    $pp = $this->createPreprocessor('del_test');
    $ppId = $pp->getId();

    PreprocessorUtils::delete($ppId);

    $this->assertNull(Factory::getPreprocessorFactory()->get($ppId));
  }

  public function testDeleteThrowsForNonExistentPreprocessor(): void {
    $this->expectException(HTException::class);
    PreprocessorUtils::delete(-1);
  }

  public function testDeleteThrowsWhenTaskUsesPreprocessor(): void {
    $pp = $this->createPreprocessor('del_task');
    $hashType = $this->createHashType();
    $hashlist = $this->createHashlist($this->createAccessGroup('del_pp'), $hashType);
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);
    $taskWrapper = $this->createTaskWrapper($this->createAccessGroup('del_pp'), $hashlist);
    $this->createDatabaseObject(
      Factory::getTaskFactory(),
      new Task(
        null, 'task_with_pp_' . uniqid(), '--attack-mode 0', 60, 30, 0, 0, 1, 1,
        '#ffffff', 0, 0, 0, 0, $crackerBinary->getId(), $crackerBinaryType->getId(),
        $taskWrapper->getId(), 0, '', 0, 0, 0, $pp->getId(), ''
      )
    );

    $this->expectException(HttpError::class);
    PreprocessorUtils::delete($pp->getId());
  }

  public function testEditNameUpdatesName(): void {
    $newName = 'rename_pp_' . uniqid();
    PreprocessorUtils::editName($this->preprocessor->getId(), $newName);

    $updated = Factory::getPreprocessorFactory()->get($this->preprocessor->getId());
    $this->assertSame($newName, $updated->getName());
  }

  public function testEditNameThrowsForDuplicateName(): void {
    $other = $this->createPreprocessor('rename_dup');

    $this->expectException(HTException::class);
    PreprocessorUtils::editName($this->preprocessor->getId(), $other->getName());
  }

  public function testEditBinaryNameUpdates(): void {
    $newBinary = 'new_binary_' . uniqid();
    PreprocessorUtils::editBinaryName($this->preprocessor->getId(), $newBinary);

    $updated = Factory::getPreprocessorFactory()->get($this->preprocessor->getId());
    $this->assertSame($newBinary, $updated->getBinaryName());
  }

  public function testEditBinaryNameThrowsForEmpty(): void {
    $this->expectException(HTException::class);
    PreprocessorUtils::editBinaryName($this->preprocessor->getId(), '');
  }

  public function testEditBinaryNameThrowsForBlacklistedChars(): void {
    $this->expectException(HTException::class);
    PreprocessorUtils::editBinaryName($this->preprocessor->getId(), 'bad|binary');
  }

  public function testEditKeyspaceCommandUpdates(): void {
    $newCmd = '--new-keyspace';
    PreprocessorUtils::editKeyspaceCommand($this->preprocessor->getId(), $newCmd);

    $updated = Factory::getPreprocessorFactory()->get($this->preprocessor->getId());
    $this->assertSame($newCmd, $updated->getKeyspaceCommand());
  }

  public function testEditKeyspaceCommandThrowsForBlacklistedChars(): void {
    $this->expectException(HTException::class);
    PreprocessorUtils::editKeyspaceCommand($this->preprocessor->getId(), 'keyspace;rm');
  }

  public function testEditSkipCommandUpdates(): void {
    $newCmd = '--new-skip';
    PreprocessorUtils::editSkipCommand($this->preprocessor->getId(), $newCmd);

    $updated = Factory::getPreprocessorFactory()->get($this->preprocessor->getId());
    $this->assertSame($newCmd, $updated->getSkipCommand());
  }

  public function testEditSkipCommandThrowsForBlacklistedChars(): void {
    $this->expectException(HTException::class);
    PreprocessorUtils::editSkipCommand($this->preprocessor->getId(), 'skip$test');
  }

  public function testEditLimitCommandUpdates(): void {
    $newCmd = '--new-limit';
    PreprocessorUtils::editLimitCommand($this->preprocessor->getId(), $newCmd);

    $updated = Factory::getPreprocessorFactory()->get($this->preprocessor->getId());
    $this->assertSame($newCmd, $updated->getLimitCommand());
  }

  public function testEditLimitCommandThrowsForBlacklistedChars(): void {
    $this->expectException(HTException::class);
    PreprocessorUtils::editLimitCommand($this->preprocessor->getId(), 'limit&test&');
  }

  public function testEditPreprocessorUpdatesAllFields(): void {
    $newName = 'full_edit_' . uniqid();
    $newBinary = 'full_bin_' . uniqid();
    $newUrl = 'https://example.com/full.zip';

    PreprocessorUtils::editPreprocessor(
      $this->preprocessor->getId(), $newName, $newBinary, $newUrl,
      '--ks', '--sk', '--lm'
    );

    $updated = Factory::getPreprocessorFactory()->get($this->preprocessor->getId());
    $this->assertSame($newName, $updated->getName());
    $this->assertSame($newBinary, $updated->getBinaryName());
    $this->assertSame($newUrl, $updated->getUrl());
    $this->assertSame('--ks', $updated->getKeyspaceCommand());
    $this->assertSame('--sk', $updated->getSkipCommand());
    $this->assertSame('--lm', $updated->getLimitCommand());
  }

  public function testEditPreprocessorThrowsForDuplicateName(): void {
    $other = $this->createPreprocessor('full_dup');

    $this->expectException(HTException::class);
    PreprocessorUtils::editPreprocessor(
      $this->preprocessor->getId(), $other->getName(),
      'binary', 'https://example.com/f.zip',
      '', '', ''
    );
  }

  public function testEditPreprocessorThrowsForEmptyName(): void {
    $this->expectException(HTException::class);
    PreprocessorUtils::editPreprocessor(
      $this->preprocessor->getId(), '', 'binary', 'https://example.com/f.zip',
      '', '', ''
    );
  }

  public function testEditPreprocessorThrowsForEmptyBinaryName(): void {
    $this->expectException(HTException::class);
    PreprocessorUtils::editPreprocessor(
      $this->preprocessor->getId(), 'name' . uniqid(), '', 'https://example.com/f.zip',
      '', '', ''
    );
  }

  public function testEditPreprocessorThrowsForEmptyUrl(): void {
    $this->expectException(HTException::class);
    PreprocessorUtils::editPreprocessor(
      $this->preprocessor->getId(), 'name' . uniqid(), 'binary', '',
      '', '', ''
    );
  }

  public function testEditPreprocessorThrowsForBlacklistedBinaryName(): void {
    $this->expectException(HTException::class);
    PreprocessorUtils::editPreprocessor(
      $this->preprocessor->getId(), 'name' . uniqid(), 'bad|binary', 'https://example.com/f.zip',
      '', '', ''
    );
  }

  public function testEditPreprocessorThrowsForBlacklistedKeyspace(): void {
    $this->expectException(HTException::class);
    PreprocessorUtils::editPreprocessor(
      $this->preprocessor->getId(), 'name' . uniqid(), 'binary', 'https://example.com/f.zip',
      'keyspace;rm', '', ''
    );
  }

  public function testEditPreprocessorThrowsForBlacklistedSkip(): void {
    $this->expectException(HTException::class);
    PreprocessorUtils::editPreprocessor(
      $this->preprocessor->getId(), 'name' . uniqid(), 'binary', 'https://example.com/f.zip',
      '', 'skip$test', ''
    );
  }

  public function testEditPreprocessorThrowsForBlacklistedLimit(): void {
    $this->expectException(HTException::class);
    PreprocessorUtils::editPreprocessor(
      $this->preprocessor->getId(), 'name' . uniqid(), 'binary', 'https://example.com/f.zip',
      '', '', 'limit`test&'
    );
  }

  public function testEditPreprocessorConvertsEmptyCommandsToNull(): void {
    PreprocessorUtils::editPreprocessor(
      $this->preprocessor->getId(), 'name' . uniqid(), 'binary', 'https://example.com/f.zip',
      '', '', ''
    );

    $updated = Factory::getPreprocessorFactory()->get($this->preprocessor->getId());
    $this->assertNull($updated->getKeyspaceCommand());
    $this->assertNull($updated->getSkipCommand());
    $this->assertNull($updated->getLimitCommand());
  }
}
