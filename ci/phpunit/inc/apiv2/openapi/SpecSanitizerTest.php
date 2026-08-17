<?php

namespace Hashtopolis\inc\apiv2\openapi;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the individual fixes SpecSanitizer applies to a raw spec,
 * each exercised with a minimal handcrafted input.
 */
final class SpecSanitizerTest extends TestCase {
  private function sanitize(array $spec): array {
    return (new SpecSanitizer())->sanitize($spec);
  }

  private function minimalSpec(array $paths = [], array $schemas = []): array {
    return [
      'openapi' => '3.1.0',
      'info' => ['title' => 'Test', 'version' => 'v2'],
      'paths' => $paths,
      'components' => ['schemas' => $schemas],
    ];
  }

  public function testRenamesBackslashSchemaNamesAndRewritesRefs(): void {
    $fqcn = 'Hashtopolis\\inc\\apiv2\\helper\\ThingHelperAPI';
    $result = $this->sanitize($this->minimalSpec(
      ['/api/v2/helper/thing' => ['post' => [
        'responses' => ['200' => [
          'description' => 'ok',
          'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/' . $fqcn]]],
        ]],
      ]]],
      [$fqcn => ['type' => 'object']]
    ));

    $this->assertArrayHasKey('ThingHelperAPI', $result['components']['schemas']);
    $this->assertArrayNotHasKey($fqcn, $result['components']['schemas']);
    $this->assertSame(
      '#/components/schemas/ThingHelperAPI',
      $result['paths']['/api/v2/helper/thing']['post']['responses']['200']['content']['application/json']['schema']['$ref']
    );
  }

  /**
   * Paths arrive as OpenAPI path templates (RouteIntrospector), so a
   * placeholder in one names a path parameter the operation has to declare.
   */
  public function testAddsMissingPathParams(): void {
    $result = $this->sanitize($this->minimalSpec(
      ['/api/v2/ui/things/{id}' => ['delete' => ['responses' => ['204' => ['description' => 'gone']]]]]
    ));

    $operation = $result['paths']['/api/v2/ui/things/{id}']['delete'];
    $this->assertContains([
      'name' => 'id',
      'in' => 'path',
      'required' => true,
      'schema' => ['type' => 'integer'],
    ], $operation['parameters']);
    $this->assertSame(['id'], array_column($operation['parameters'], 'name'));
    $this->assertSame('deleteThingsById', $operation['operationId']);
  }

  public function testMovesPaginationParamsToQueryAndFixesStyleCasing(): void {
    $result = $this->sanitize($this->minimalSpec(
      ['/api/v2/ui/things' => ['get' => [
        'parameters' => [
          ['name' => 'page[after]', 'in' => 'path', 'schema' => ['type' => 'integer']],
          ['name' => 'filter', 'in' => 'path', 'style' => 'deepobject', 'schema' => ['type' => 'object']],
        ],
        'responses' => ['200' => ['description' => 'ok']],
      ]]]
    ));

    $parameters = $result['paths']['/api/v2/ui/things']['get']['parameters'];
    $this->assertSame('query', $parameters[0]['in']);
    $this->assertSame('query', $parameters[1]['in']);
    $this->assertSame('deepObject', $parameters[1]['style']);
  }

  public function testUnwrapsIndexedRequestBodyAndFixesRequiredString(): void {
    $result = $this->sanitize($this->minimalSpec(
      ['/api/v2/helper/upload' => ['patch' => [
        'requestBody' => [[
          'required' => 'true',
          'description' => 'binary data',
          'content' => ['application/offset+octet-stream' => ['schema' => ['type' => 'string']]],
        ]],
        'responses' => ['204' => ['description' => 'accepted']],
      ]]]
    ));

    $requestBody = $result['paths']['/api/v2/helper/upload']['patch']['requestBody'];
    $this->assertTrue($requestBody['required']);
    $this->assertSame('binary data', $requestBody['description']);
  }

  public function testFillsEmptyMediaTypeObjects(): void {
    $result = $this->sanitize($this->minimalSpec(
      ['/api/v2/ui/things/{id}' => ['delete' => [
        'requestBody' => ['required' => true, 'content' => ['application/json' => []]],
        'responses' => ['200' => ['description' => 'ok', 'content' => ['application/json' => []]]],
      ]]]
    ));

    $operation = $result['paths']['/api/v2/ui/things/{id}']['delete'];
    $this->assertSame(['schema' => ['type' => 'object']], $operation['requestBody']['content']['application/json']);
    $this->assertSame(['schema' => ['type' => 'object']], $operation['responses']['200']['content']['application/json']);
  }

  public function testWrapsSchemalessMediaTypeContent(): void {
    $result = $this->sanitize($this->minimalSpec(
      ['/api/v2/helper/importFile' => ['post' => [
        'responses' => ['201' => [
          'description' => 'created',
          'content' => ['application/pdf' => ['type' => 'string', 'format' => 'binary']],
        ]],
      ]]]
    ));

    $this->assertSame(
      ['schema' => ['type' => 'string', 'format' => 'binary']],
      $result['paths']['/api/v2/helper/importFile']['post']['responses']['201']['content']['application/pdf']
    );
  }

  public function testParsesEnumStringsIntoArrays(): void {
    $result = $this->sanitize($this->minimalSpec(
      ['/api/v2/helper/importFile' => ['head' => [
        'responses' => ['200' => [
          'description' => 'ok',
          'headers' => ['Tus-Resumable' => ['schema' => ['type' => 'string', 'enum' => "enum: ['1.0.0']"]]],
        ]],
      ]]]
    ));

    $this->assertSame(
      ['1.0.0'],
      $result['paths']['/api/v2/helper/importFile']['head']['responses']['200']['headers']['Tus-Resumable']['schema']['enum']
    );
  }

  /**
   * The security requirement a builder states is passed through untouched: the
   * builders emit the empty scope list a bearer scheme requires, so there is
   * nothing left for the sanitizer to correct.
   */
  public function testLeavesSecurityRequirementsAlone(): void {
    $result = $this->sanitize($this->minimalSpec(
      ['/api/v2/ui/things' => ['get' => [
        'security' => [['bearerAuth' => []]],
        'x-required-permissions' => ['permission1', 'permission2'],
        'responses' => ['200' => ['description' => 'ok']],
      ]]]
    ));

    $operation = $result['paths']['/api/v2/ui/things']['get'];
    $this->assertSame([['bearerAuth' => []]], $operation['security']);
    $this->assertSame(['permission1', 'permission2'], $operation['x-required-permissions']);
  }

  public function testSynthesizesTagsSummaryOperationIdAndSecurity(): void {
    $result = $this->sanitize($this->minimalSpec(
      ['/api/v2/helper/abortChunk' => ['post' => [
        'responses' => ['200' => ['description' => 'ok']],
      ]]]
    ));

    $operation = $result['paths']['/api/v2/helper/abortChunk']['post'];
    $this->assertSame(['Helpers'], $operation['tags']);
    $this->assertSame('Create Helpers', $operation['summary']);
    $this->assertSame('postAbortChunk', $operation['operationId']);
    $this->assertSame('Create Helpers', $operation['description']);
    $this->assertSame([['bearerAuth' => []]], $operation['security']);
    $this->assertSame(
      [[
        'name' => 'Helpers',
        'description' => 'Helper endpoints under `/api/v2/helper/`: actions and file transfers that do not map onto a resource collection.'
      ]],
      $result['tags']
    );
  }

  /**
   * Every tag needs a description. For a resource collection it names the
   * collection and whether it can be modified, which follows from the methods
   * its operations use.
   */
  public function testDescribesTagsOfResourceCollections(): void {
    $result = $this->sanitize($this->minimalSpec([
      '/api/v2/ui/things' => [
        'get' => ['tags' => ['Things'], 'responses' => ['200' => ['description' => 'ok']]],
        'patch' => ['tags' => ['Things'], 'responses' => ['204' => ['description' => 'ok']]],
      ],
      '/api/v2/ui/things/{id}' => [
        'get' => ['tags' => ['Things'], 'responses' => ['200' => ['description' => 'ok']]],
      ],
      '/api/v2/ui/readonlys' => [
        'get' => ['tags' => ['Readonlys'], 'responses' => ['200' => ['description' => 'ok']]],
      ],
    ]));

    $descriptions = array_column($result['tags'], 'description', 'name');
    $this->assertSame(
      'Reading the resources served under `/api/v2/ui/readonlys`.',
      $descriptions['Readonlys']
    );
    $this->assertSame(
      'Reading and writing the resources served under `/api/v2/ui/things`.',
      $descriptions['Things']
    );
  }

  public function testAddsMissing2xxResponse(): void {
    $result = $this->sanitize($this->minimalSpec(
      ['/api/v2/ui/things' => ['get' => [
        'responses' => ['400' => ['description' => 'bad']],
      ]]]
    ));

    $this->assertSame(
      ['description' => 'successful operation'],
      $result['paths']['/api/v2/ui/things']['get']['responses']['200']
    );
  }

  public function testPrunesUnreferencedSchemasIteratively(): void {
    $result = $this->sanitize($this->minimalSpec(
      ['/api/v2/ui/things' => ['get' => [
        'responses' => ['200' => [
          'description' => 'ok',
          'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/KeptRoot']]],
        ]],
      ]]],
      [
        'KeptRoot' => ['type' => 'object', 'properties' => ['child' => ['$ref' => '#/components/schemas/KeptChild']]],
        'KeptChild' => ['type' => 'string'],
        'OrphanParent' => ['type' => 'object', 'properties' => ['child' => ['$ref' => '#/components/schemas/OrphanChild']]],
        'OrphanChild' => ['type' => 'string'],
      ]
    ));

    $this->assertSame(['KeptRoot', 'KeptChild'], array_keys($result['components']['schemas']));
  }

  public function testEmptyPropertiesBecomeJsonObject(): void {
    $result = $this->sanitize($this->minimalSpec(
      ['/api/v2/ui/things' => ['get' => [
        'responses' => ['200' => [
          'description' => 'ok',
          'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/Empty']]],
        ]],
      ]]],
      ['Empty' => ['type' => 'object', 'properties' => []]]
    ));

    $properties = $result['components']['schemas']['Empty']['properties'];
    $this->assertInstanceOf(\stdClass::class, $properties);
    $this->assertSame('{}', json_encode($properties));
  }
}
