<?php

namespace Hashtopolis\inc\apiv2\openapi;

use Hashtopolis\inc\apiv2\helper\AbortChunkHelperAPI;
use Hashtopolis\inc\apiv2\helper\GetCracksPerDayHelperAPI;
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/SpecFixtureTrait.php');

/**
 * Input model -> OpenAPI spec tests for helper API classes.
 */
final class SpecBuilderHelperApiTest extends TestCase {
  use SpecFixtureTrait;

  public function testAbortChunkSpec(): void {
    // POST helper: description from the actionPost PHPDoc, request body from
    // getFormFields, response schema inferred from the getResponse() sample.
    $spec = (new SpecBuilder())->buildForApiClasses([AbortChunkHelperAPI::class]);

    $this->assertMatchesJsonFixture($spec, 'abortchunk.spec.json');

    $post = $spec['paths']['/api/v2/helper/abortChunk']['post'];
    $this->assertStringStartsWith('Endpoint to stop a running chunk.', $post['description']);

    // Raw spec keys helper components by FQCN; the sanitizer renames them.
    $this->assertSame(
      ['$ref' => '#/components/schemas/' . AbortChunkHelperAPI::class],
      $post['requestBody']['content']['application/json']['schema']
    );
    $this->assertSame(
      ['$ref' => '#/components/schemas/' . AbortChunkHelperAPI::class . 'Response'],
      $post['responses']['200']['content']['application/json']['schema']
    );
    $this->assertSame([
      'type' => 'array',
      'items' => [
        'type' => 'object',
        'properties' => ['Abort' => ['type' => 'string', 'default' => 'Success']],
      ],
    ], $spec['components']['schemas'][AbortChunkHelperAPI::class . 'Response']);
  }

  public function testGetCracksPerDaySpec(): void {
    // GET helper with a custom register() (array callable to handleGet) and
    // getResponse(): null, which yields a contentless 200 response.
    $spec = (new SpecBuilder())->buildForApiClasses([GetCracksPerDayHelperAPI::class]);

    $get = $spec['paths']['/api/v2/helper/getCracksPerDay']['get'];
    $this->assertStringStartsWith('Returns a map of date -> crack count', $get['description']);
    $this->assertSame([], $get['parameters']);
    $this->assertSame(['description' => 'successful operation'], $get['responses']['200']);
  }
}
