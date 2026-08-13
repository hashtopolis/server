<?php

namespace Hashtopolis\inc\apiv2\openapi;

use Hashtopolis\inc\apiv2\model\ConfigAPI;
use Hashtopolis\inc\apiv2\model\ConfigSectionAPI;
use Hashtopolis\inc\apiv2\model\CrackerBinaryAPI;
use Hashtopolis\inc\apiv2\model\CrackerBinaryTypeAPI;
use Hashtopolis\inc\apiv2\model\HashTypeAPI;
use Hashtopolis\inc\apiv2\model\TaskAPI;
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/SpecFixtureTrait.php');

/**
 * Input model -> OpenAPI spec tests for model API classes. No database, no
 * HTTP server: the spec is generated purely from class introspection.
 */
final class SpecBuilderModelApiTest extends TestCase {
  use SpecFixtureTrait;

  public function testHashTypeSpec(): void {
    // Simplest case: full CRUD model API without any relationships.
    $spec = (new SpecBuilder())->buildForApiClasses([HashTypeAPI::class]);

    $this->assertMatchesJsonFixture($spec, 'hashtype.spec.json');

    $this->assertSame('3.1.0', $spec['openapi']);
    $this->assertArrayHasKey('/api/v2/ui/hashtypes', $spec['paths']);
    $this->assertArrayHasKey('/api/v2/ui/hashtypes/count', $spec['paths']);
    $this->assertArrayHasKey('/api/v2/ui/hashtypes/{id:[0-9]+}', $spec['paths']);

    $response = $spec['components']['schemas']['HashTypeResponse'];
    $this->assertSame(['jsonapi', 'links', 'data'], $response['required']);
    $attributes = $response['properties']['data']['properties']['attributes'];
    $this->assertSame(['description', 'isSalted', 'isSlowHash'], $attributes['required']);
    $this->assertSame(['type' => 'boolean'], $attributes['properties']['isSalted']);
    $this->assertSame(
      ['type' => 'string', 'const' => 'hashType'],
      $response['properties']['data']['properties']['type']
    );
  }

  public function testConfigSpecWithToOneRelationshipAndSchemaOverride(): void {
    // Closed pair: Config has a toOne relationship to ConfigSection. ConfigAPI
    // also overrides the attributes schema (oneOf over config value types).
    $spec = (new SpecBuilder())->buildForApiClasses([ConfigAPI::class, ConfigSectionAPI::class]);

    $this->assertMatchesJsonFixture($spec, 'config.spec.json');

    $this->assertArrayHasKey('/api/v2/ui/configs/{id:[0-9]+}/{relation:configSection}', $spec['paths']);
    $this->assertArrayHasKey('/api/v2/ui/configs/{id:[0-9]+}/relationships/{relation:configSection}', $spec['paths']);

    $response = $spec['components']['schemas']['ConfigResponse'];
    // getOpenAPIAttributesSchemaOverride() replaces the default attributes object
    $this->assertArrayHasKey('oneOf', $response['properties']['data']['properties']['attributes']);

    // toOne relationship linkage: resource identifier with const type, nullable
    $configSection = $response['properties']['data']['properties']['relationships']['properties']['configSection'];
    $this->assertSame(
      ['type' => 'string', 'const' => 'configSection'],
      $configSection['properties']['data']['oneOf'][0]['properties']['type']
    );
    $this->assertSame(['type' => 'null'], $configSection['properties']['data']['oneOf'][1]);
  }

  public function testCrackerBinaryTypeSpecWithMapperOnlySeeding(): void {
    // CrackerBinaryType has toMany relationships to CrackerBinary and Task.
    // The related API classes are seeded on the class mapper only, so their
    // own routes are not part of the spec but relationship resolution works.
    $spec = (new SpecBuilder())->buildForApiClasses(
      [CrackerBinaryTypeAPI::class],
      [CrackerBinaryAPI::class, TaskAPI::class]
    );

    $this->assertMatchesJsonFixture($spec, 'crackerbinarytype.spec.json');

    // Routes of mapper-only classes must not appear
    $this->assertArrayNotHasKey('/api/v2/ui/crackers', $spec['paths']);
    $this->assertArrayNotHasKey('/api/v2/ui/tasks', $spec['paths']);
    $this->assertArrayHasKey('/api/v2/ui/crackertypes/{id:[0-9]+}/{relation:crackerVersions}', $spec['paths']);

    $response = $spec['components']['schemas']['CrackerBinaryTypeResponse'];
    // toMany relationship linkage is an array of resource identifiers
    $this->assertSame('array', $response['properties']['data']['properties']['relationships']['properties']['tasks']['properties']['data']['type']);

    // Multiple expandables produce a discriminated oneOf union in "included"
    $included = $response['properties']['included']['items'];
    $this->assertSame(['propertyName' => 'type'], $included['discriminator']);
    $this->assertSame(
      ['crackerBinary', 'task'],
      array_map(fn($branch) => $branch['properties']['type']['const'], $included['oneOf'])
    );
  }
}
