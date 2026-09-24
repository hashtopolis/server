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
    // Simple case: full CRUD model API with one readonly toMany relationship
    // (crackerBinaries), whose target is seeded on the class mapper only, so
    // its own routes are not part of the spec. The runtime rejects every
    // mutation of the readonly relationship, so the spec documents only its
    // reading operations.
    $spec = (new SpecBuilder())->buildForApiClasses([HashTypeAPI::class], [CrackerBinaryAPI::class]);

    $this->assertMatchesJsonFixture($spec, 'hashtype.spec.json');

    // the readonly relationship documents no mutation operations
    $this->assertSame(
      ['get'],
      array_keys($spec['paths']['/api/v2/ui/hashtypes/{id}/relationships/crackerBinaries'])
    );
    $this->assertSame(
      ['get'],
      array_keys($spec['paths']['/api/v2/ui/hashtypes/{id}/crackerBinaries'])
    );

    // the resource identifiers carry the resource type of the related API
    // class, not the plural name of the relationship
    $identifier = $spec['components']['schemas']['HashTypeRelationCrackerBinariesGetResponse']['properties']['data']['items']['properties']['type'];
    $this->assertSame(['type' => 'string', 'const' => 'crackerBinary'], $identifier);

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

    $this->assertArrayHasKey('/api/v2/ui/configs/{id}/configSection', $spec['paths']);
    $this->assertArrayHasKey('/api/v2/ui/configs/{id}/relationships/configSection', $spec['paths']);

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
    $this->assertArrayHasKey('/api/v2/ui/crackertypes/{id}/crackerVersions', $spec['paths']);
    $this->assertArrayHasKey('/api/v2/ui/crackertypes/{id}/tasks', $spec['paths']);
    $this->assertArrayHasKey('/api/v2/ui/crackertypes/{id}/relationships/crackerVersions', $spec['paths']);
    $this->assertArrayHasKey('/api/v2/ui/crackertypes/{id}/relationships/tasks', $spec['paths']);

    // each relation is documented with its own schemas, none overwrites another
    $mediaType = 'application/vnd.api+json';
    $crackerVersionsGet = $spec['paths']['/api/v2/ui/crackertypes/{id}/crackerVersions']['get']['responses']['200'];
    $this->assertSame(
      '#/components/schemas/CrackerBinaryTypeRelationCrackerVersionsGetResponse',
      $crackerVersionsGet['content'][$mediaType]['schema']['$ref']
    );
    $tasksGet = $spec['paths']['/api/v2/ui/crackertypes/{id}/tasks']['get']['responses']['200'];
    $this->assertSame(
      '#/components/schemas/CrackerBinaryTypeRelationTasksGetResponse',
      $tasksGet['content'][$mediaType]['schema']['$ref']
    );

    // the resource identifiers carry the resource type of the related API
    // class, not the plural name of the relationship
    $crackerVersionsIdentifier = $spec['components']['schemas']['CrackerBinaryTypeRelationCrackerVersionsGetResponse']['properties']['data']['items']['properties']['type'];
    $this->assertSame(['type' => 'string', 'const' => 'crackerBinary'], $crackerVersionsIdentifier);

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
   * CrackerBinary has two toMany relationships (tasks, hashtypes) and two
   * toOne relationships (crackerBinaryType, accessGroup). Every relation is
   * documented as its own concrete path with its own request and response
   * schemas: keying them to a shared "{relation}" path would let the later
   * relations overwrite the earlier ones in the spec.
   */
  public function testCrackerBinarySpec(): void {
    $spec = (new SpecBuilder())->buildForApiClasses(
      [CrackerBinaryAPI::class],
      [TaskAPI::class, HashTypeAPI::class, CrackerBinaryTypeAPI::class, AccessGroupAPI::class]
    );

    $this->assertMatchesJsonFixture($spec, 'crackerbinary.spec.json');

    // every relation has its own paths, none overwrites another
    $expectedPaths = [
      '/api/v2/ui/crackers/{id}/crackerBinaryType',
      '/api/v2/ui/crackers/{id}/accessGroup',
      '/api/v2/ui/crackers/{id}/tasks',
      '/api/v2/ui/crackers/{id}/hashtypes',
      '/api/v2/ui/crackers/{id}/relationships/crackerBinaryType',
      '/api/v2/ui/crackers/{id}/relationships/accessGroup',
      '/api/v2/ui/crackers/{id}/relationships/tasks',
      '/api/v2/ui/crackers/{id}/relationships/hashtypes',
    ];
    $actualPaths = array_keys($spec['paths']);
    foreach ($expectedPaths as $expectedPath) {
      $this->assertContains($expectedPath, $actualPaths);
    }

    // the related-resource routes answer with the per-relation document
    $mediaType = 'application/vnd.api+json';
    $tasksGet = $spec['paths']['/api/v2/ui/crackers/{id}/tasks']['get']['responses']['200'];
    $this->assertSame(
      '#/components/schemas/CrackerBinaryRelationTasksGetResponse',
      $tasksGet['content'][$mediaType]['schema']['$ref']
    );
    $hashtypesGet = $spec['paths']['/api/v2/ui/crackers/{id}/hashtypes']['get']['responses']['200'];
    $this->assertSame(
      '#/components/schemas/CrackerBinaryRelationHashtypesGetResponse',
      $hashtypesGet['content'][$mediaType]['schema']['$ref']
    );

    // the write requests carry the per-relation resource identifiers
    $tasksPost = $spec['paths']['/api/v2/ui/crackers/{id}/relationships/tasks']['post']['requestBody'];
    $this->assertSame(
      '#/components/schemas/CrackerBinaryRelationTasks',
      $tasksPost['content'][$mediaType]['schema']['$ref']
    );
    $hashtypesPost = $spec['paths']['/api/v2/ui/crackers/{id}/relationships/hashtypes']['post']['requestBody'];
    $this->assertSame(
      '#/components/schemas/CrackerBinaryRelationHashtypes',
      $hashtypesPost['content'][$mediaType]['schema']['$ref']
    );

    // the identifiers carry the resource type of the related API class, not
    // the plural name of the relationship
    $hashtypesIdentifier = $spec['components']['schemas']['CrackerBinaryRelationHashtypes']['properties']['data']['items']['properties']['type'];
    $this->assertSame(['type' => 'string', 'const' => 'hashType'], $hashtypesIdentifier);
    $tasksIdentifier = $spec['components']['schemas']['CrackerBinaryRelationTasks']['properties']['data']['items']['properties']['type'];
    $this->assertSame(['type' => 'string', 'const' => 'task'], $tasksIdentifier);
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
