<?php

namespace Hashtopolis\inc\apiv2\openapi;

use Hashtopolis\inc\apiv2\model\AccessGroupAPI;
use Hashtopolis\inc\apiv2\model\ApiTokenAPI;
use Hashtopolis\inc\apiv2\model\ConfigAPI;
use Hashtopolis\inc\apiv2\model\ConfigSectionAPI;
use Hashtopolis\inc\apiv2\model\CrackerBinaryAPI;
use Hashtopolis\inc\apiv2\model\CrackerBinaryTypeAPI;
use Hashtopolis\inc\apiv2\model\GlobalPermissionGroupAPI;
use Hashtopolis\inc\apiv2\model\HashTypeAPI;
use Hashtopolis\inc\apiv2\model\TaskAPI;
use Hashtopolis\inc\apiv2\model\UserAPI;
use Middlewares\Utils\HttpErrorException;
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
    $this->assertArrayHasKey('/api/v2/ui/hashtypes/{id}', $spec['paths']);

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

    $this->assertArrayHasKey('/api/v2/ui/configs/{id}/{relation}', $spec['paths']);
    $this->assertArrayHasKey('/api/v2/ui/configs/{id}/relationships/{relation}', $spec['paths']);

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
    $this->assertArrayHasKey('/api/v2/ui/crackertypes/{id}/{relation}', $spec['paths']);

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

  /**
   * User is the model the permission filter strips, so the configured
   * corrections have to reach both places its attributes are described: the
   * resource object of its own routes and the resource included in the
   * response of a model expanding it.
   */
  public function testUserSpecAppliesTheConfiguredAttributeCorrections(): void {
    $spec = (new SpecBuilder(SpecOverrides::defaults()))->buildForApiClasses(
      [UserAPI::class, ApiTokenAPI::class],
      [GlobalPermissionGroupAPI::class, AccessGroupAPI::class]
    );

    $attributes = $spec['components']['schemas']['UserResponse']['properties']['data']['properties']['attributes'];
    /* Only the public attribute survives a caller without 'permUserRead' */
    $this->assertSame(['name'], $attributes['required']);
    /* The optional attributes stay described, and stay non-nullable */
    $this->assertSame(['type' => 'string'], $attributes['properties']['email']);
    $this->assertSame(['type' => 'boolean'], $attributes['properties']['isValid']);

    $includedUser = $spec['components']['schemas']['ApiTokenResponse']['properties']['included']['items'];
    $this->assertSame('user', $includedUser['properties']['type']['const']);
    $this->assertSame(['name'], $includedUser['properties']['attributes']['required']);
  }

  /**
   * Without the corrections the features speak for themselves, so the
   * shortened list above is the configuration at work and not a rule baked
   * into the generator.
   */
  public function testAttributeCorrectionsAreOptIn(): void {
    $spec = (new SpecBuilder(new SpecOverrides()))->buildForApiClasses(
      [UserAPI::class, ApiTokenAPI::class],
      [GlobalPermissionGroupAPI::class, AccessGroupAPI::class]
    );

    $attributes = $spec['components']['schemas']['UserResponse']['properties']['data']['properties']['attributes'];
    $this->assertContains('email', $attributes['required']);
    $this->assertContains('sessionLifetime', $attributes['required']);
  }

  /**
   * A class stating its whole attributes schema leaves the per-attribute
   * corrections nothing to apply to, which must be said rather than ignored.
   */
  public function testCorrectingAModelThatStatesItsOwnAttributesSchemaIsRejected(): void {
    $builder = new SpecBuilder(new SpecOverrides([
      'Config' => [SpecOverrides::OPTIONAL_ATTRIBUTES => ['value']],
    ]));

    $this->expectException(HttpErrorException::class);
    $builder->buildForApiClasses([ConfigAPI::class, ConfigSectionAPI::class]);
  }
}
