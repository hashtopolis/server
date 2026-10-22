<?php

namespace Hashtopolis\inc\jobs;

use Hashtopolis\inc\HTException;
use Hashtopolis\inc\jobs\payload\JobPayloadField;
use Hashtopolis\TestBase;
use PHPUnit\Framework\Attributes\DataProvider;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class BackgroundJobPayloadValidatorTest extends TestBase {
  public static function provideTypes(): array {
    return [
      [JobPayloadField::TYPE_INT, 123],
      [JobPayloadField::TYPE_STRING, 'abc'],
      [JobPayloadField::TYPE_BOOL, true],
      [JobPayloadField::TYPE_FLOAT, 1.5],
      [JobPayloadField::TYPE_ARRAY, ['a', 'b']],
    ];
  }

  public static function provideTypeMismatches(): array {
    return [
      [JobPayloadField::TYPE_INT, '123'],
      [JobPayloadField::TYPE_INT, 1.5],
      [JobPayloadField::TYPE_STRING, 123],
      [JobPayloadField::TYPE_BOOL, 1],
      [JobPayloadField::TYPE_FLOAT, 1],
      [JobPayloadField::TYPE_ARRAY, 'abc'],
    ];
  }

  /**
   * @throws HTException
   */
  public function testValidPayloadPasses(): void {
    BackgroundJobPayloadValidator::validate(
      ['fileId' => 123, 'note' => 'abc'],
      ['fileId' => new JobPayloadField(JobPayloadField::TYPE_INT)]
    );
    $this->expectNotToPerformAssertions();
  }

  /**
   * @throws HTException
   */
  public function testMissingRequiredKeyThrows(): void {
    $this->expectException(HTException::class);
    $this->expectExceptionMessage("Missing required payload key 'fileId'!");
    BackgroundJobPayloadValidator::validate([], ['fileId' => new JobPayloadField(JobPayloadField::TYPE_INT)]);
  }

  /**
   * @throws HTException
   */
  public function testMissingOptionalKeyPasses(): void {
    BackgroundJobPayloadValidator::validate(
      ['fileId' => 123],
      [
        'fileId' => new JobPayloadField(JobPayloadField::TYPE_INT),
        'note' => new JobPayloadField(JobPayloadField::TYPE_STRING, false),
      ]
    );
    $this->expectNotToPerformAssertions();
  }

  /**
   * @throws HTException
   */
  public function testNullValueThrowsEvenWhenOptional(): void {
    $this->expectException(HTException::class);
    $this->expectExceptionMessage("Invalid type for payload key 'note', expected string!");
    BackgroundJobPayloadValidator::validate(
      ['note' => null],
      ['note' => new JobPayloadField(JobPayloadField::TYPE_STRING, false)]
    );
  }

  /**
   * @throws HTException
   */
  public function testUndeclaredKeysAreIgnored(): void {
    BackgroundJobPayloadValidator::validate(
      ['fileId' => 123, 'unknown' => 'whatever'],
      ['fileId' => new JobPayloadField(JobPayloadField::TYPE_INT)]
    );
    $this->expectNotToPerformAssertions();
  }

  /**
   * @param string $type one of the JobPayloadField type constants
   * @param mixed $value value matching the type
   * @throws HTException
   */
  #[DataProvider('provideTypes')]
  public function testMatchingTypesPass(string $type, mixed $value): void {
    BackgroundJobPayloadValidator::validate(['key' => $value], ['key' => new JobPayloadField($type)]);
    $this->expectNotToPerformAssertions();
  }

  /**
   * @param string $type one of the JobPayloadField type constants
   * @param mixed $value value violating the type
   * @throws HTException
   */
  #[DataProvider('provideTypeMismatches')]
  public function testTypeMismatchThrows(string $type, mixed $value): void {
    $this->expectException(HTException::class);
    $this->expectExceptionMessage("Invalid type for payload key 'key', expected $type!");
    BackgroundJobPayloadValidator::validate(['key' => $value], ['key' => new JobPayloadField($type)]);
  }
}
