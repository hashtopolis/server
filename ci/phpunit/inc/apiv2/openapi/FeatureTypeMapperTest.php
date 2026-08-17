<?php

namespace Hashtopolis\inc\apiv2\openapi;

use Middlewares\Utils\HttpErrorException;
use PHPUnit\Framework\TestCase;

final class FeatureTypeMapperTest extends TestCase {
  private FeatureTypeMapper $mapper;

  protected function setUp(): void {
    $this->mapper = new FeatureTypeMapper();
  }

  public function testTypeLookupInt(): void {
    $this->assertSame(
      ["type" => "integer", "type_format" => null, "type_enum" => null, "type_enum_labels" => null, "subtype" => null],
      $this->mapper->typeLookup(['type' => 'int', 'choices' => 'unset'])
    );
  }

  public function testTypeLookupUint64(): void {
    $this->assertSame("integer", $this->mapper->typeLookup(['type' => 'uint64', 'choices' => 'unset'])['type']);
  }

  public function testTypeLookupInt64HasFormat(): void {
    $result = $this->mapper->typeLookup(['type' => 'int64', 'choices' => 'unset']);
    $this->assertSame("integer", $result['type']);
    $this->assertSame("int64", $result['type_format']);
  }

  public function testTypeLookupBool(): void {
    $this->assertSame("boolean", $this->mapper->typeLookup(['type' => 'bool', 'choices' => 'unset'])['type']);
  }

  public function testTypeLookupStr(): void {
    $this->assertSame("string", $this->mapper->typeLookup(['type' => 'str', 'choices' => 'unset'])['type']);
    $this->assertSame("string", $this->mapper->typeLookup(['type' => 'str(256)', 'choices' => 'unset'])['type']);
  }

  public function testTypeLookupDictWithSubtype(): void {
    $result = $this->mapper->typeLookup(['type' => 'dict', 'subtype' => 'str', 'choices' => 'unset']);
    $this->assertSame("object", $result['type']);
    $this->assertSame("string", $result['subtype']);
  }

  public function testTypeLookupArray(): void {
    $result = $this->mapper->typeLookup(['type' => 'array', 'choices' => 'unset']);
    $this->assertSame("array", $result['type']);
    $this->assertSame("integer", $result['subtype']);
  }

  public function testTypeLookupChoices(): void {
    $result = $this->mapper->typeLookup(['type' => 'int', 'choices' => [0 => 'Linux', 1 => 'Windows', 2 => 'macOS']]);
    $this->assertSame([0, 1, 2], $result['type_enum']);
    $this->assertSame(['Linux', 'Windows', 'macOS'], $result['type_enum_labels']);
  }

  public function testTypeLookupUnknownTypeThrows(): void {
    $this->expectException(HttpErrorException::class);
    $this->mapper->typeLookup(['type' => 'blob', 'choices' => 'unset']);
  }

  public function testMakePropertiesSimpleString(): void {
    $features = [['type' => 'str', 'choices' => 'unset', 'null' => false, 'alias' => 'name', 'pk' => false]];
    $this->assertSame(['name' => ['type' => 'string']], $this->mapper->makeProperties($features));
  }

  public function testMakePropertiesNullableUsesTypeArray(): void {
    $features = [['type' => 'int', 'choices' => 'unset', 'null' => true, 'alias' => 'userId', 'pk' => false]];
    $this->assertSame(['userId' => ['type' => ['integer', 'null']]], $this->mapper->makeProperties($features));
  }

  public function testMakePropertiesInt64Format(): void {
    $features = [['type' => 'int64', 'choices' => 'unset', 'null' => false, 'alias' => 'lastTime', 'pk' => false]];
    $this->assertSame(['lastTime' => ['type' => 'integer', 'format' => 'int64']], $this->mapper->makeProperties($features));
  }

  public function testMakePropertiesChoicesBecomeOneOfWithConstAndTitle(): void {
    $features = [['type' => 'int', 'choices' => [0 => 'Linux', 1 => 'Windows'], 'null' => false, 'alias' => 'os', 'pk' => false]];
    $this->assertSame([
      'os' => ['oneOf' => [
        ['const' => 0, 'title' => 'Linux', 'type' => 'integer'],
        ['const' => 1, 'title' => 'Windows', 'type' => 'integer'],
      ]],
    ], $this->mapper->makeProperties($features));
  }

  public function testMakePropertiesNullableChoicesAppendNullBranch(): void {
    $features = [['type' => 'int', 'choices' => [0 => 'Off', 1 => 'On'], 'null' => true, 'alias' => 'state', 'pk' => false]];
    $properties = $this->mapper->makeProperties($features);
    $this->assertSame(['type' => 'null'], end($properties['state']['oneOf']));
  }

  public function testMakePropertiesSkipsPrimaryKeyWhenRequested(): void {
    $features = [
      ['type' => 'int', 'choices' => 'unset', 'null' => false, 'alias' => 'id', 'pk' => true],
      ['type' => 'str', 'choices' => 'unset', 'null' => false, 'alias' => 'name', 'pk' => false],
    ];
    $this->assertSame(['name'], array_keys($this->mapper->makeProperties($features, true)));
    $this->assertSame(['id', 'name'], array_keys($this->mapper->makeProperties($features)));
  }

  public function testMakePropertiesDictUsesAdditionalProperties(): void {
    $features = [['type' => 'dict', 'subtype' => 'int', 'choices' => 'unset', 'null' => false, 'alias' => 'stats', 'pk' => false]];
    $this->assertSame(
      ['stats' => ['type' => 'object', 'additionalProperties' => ['type' => 'integer']]],
      $this->mapper->makeProperties($features)
    );
  }

  public function testMakePropertiesArrayUsesItems(): void {
    $features = [['type' => 'array', 'choices' => 'unset', 'null' => false, 'alias' => 'fileIds', 'pk' => false]];
    $this->assertSame(
      ['fileIds' => ['type' => 'array', 'items' => ['type' => 'integer']]],
      $this->mapper->makeProperties($features)
    );
  }

  public function testMapToSchemaInfersTypesFromSampleValues(): void {
    $this->assertSame([
      'type' => 'object',
      'properties' => [
        'file' => ['type' => 'string', 'example' => 'abc.txt'],
        'size' => ['type' => 'integer', 'example' => 123],
        'ratio' => ['type' => 'number', 'example' => 0.5],
        'ok' => ['type' => 'boolean', 'example' => true],
        'absent' => ['type' => ['string', 'null']],
        'meta' => ['type' => 'array'],
      ],
    ], $this->mapper->mapToSchema([
      'file' => 'abc.txt', 'size' => 123, 'ratio' => 0.5, 'ok' => true, 'absent' => null, 'meta' => []
    ]));
  }

  /** A nested sample keeps its structure instead of collapsing to type: object. */
  public function testMapToSchemaWalksNestedMaps(): void {
    $this->assertSame([
      'type' => 'object',
      'properties' => [
        'outer' => [
          'type' => 'object',
          'properties' => [
            'inner' => ['type' => 'integer', 'example' => 7],
          ],
        ],
      ],
    ], $this->mapper->mapToSchema(['outer' => ['inner' => 7]]));
  }

  /** A sample list becomes an array schema, and the item schema merges the keys of all entries. */
  public function testMapToSchemaMergesThePropertiesOfListEntries(): void {
    $this->assertSame([
      'type' => 'array',
      'items' => [
        'type' => 'object',
        'properties' => [
          'found' => ['type' => 'boolean', 'example' => true],
          'query' => ['type' => 'string', 'example' => '54321'],
          'matches' => ['type' => 'array'],
        ],
      ],
    ], $this->mapper->mapToSchema([
      ['found' => false, 'query' => '12345678'],
      ['found' => true, 'query' => '54321', 'matches' => []],
    ]));
  }
}
