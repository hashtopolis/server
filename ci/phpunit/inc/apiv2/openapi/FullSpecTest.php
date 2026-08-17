<?php

namespace Hashtopolis\inc\apiv2\openapi;

use Hashtopolis\inc\apiv2\common\ApiRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Structural invariants of the full spec generated from all registered API
 * classes (raw and sanitized variant). This is intentionally not a comparison
 * against a full-document fixture: it asserts the properties every valid
 * spec revision must have, so it stays green across legitimate API changes.
 */
final class FullSpecTest extends TestCase {
  private static array $raw;
  private static array $sanitized;

  public static function setUpBeforeClass(): void {
    self::$raw = (new SpecBuilder())->buildForApiClasses(ApiRegistry::allApiClasses());
    // Round-trip through JSON so stdClass values become arrays and the walk
    // below only has to deal with arrays.
    self::$sanitized = json_decode(
      json_encode((new SpecSanitizer())->sanitize(self::$raw), JSON_THROW_ON_ERROR),
      true
    );
  }

  public function testSpecsDeclareOpenApi310AndEncodeAsJson(): void {
    $this->assertSame('3.1.0', self::$raw['openapi']);
    $this->assertSame('3.1.0', self::$sanitized['openapi']);
    $this->assertJson(json_encode(self::$raw, JSON_THROW_ON_ERROR));
  }

  /**
   * The document identifies the API and the terms it is served under, which
   * the builder states once for both variants.
   */
  public function testInfoIdentifiesTheApiAndItsLicense(): void {
    foreach (['raw' => self::$raw, 'sanitized' => self::$sanitized] as $variant => $spec) {
      $this->assertSame('Hashtopolis API', $spec['info']['title'], $variant);
      $this->assertSame('Hashtopolis REST API', $spec['info']['description'], $variant);
      $this->assertSame('https://github.com/hashtopolis/server', $spec['info']['contact']['url'], $variant);
      $this->assertSame('GPL-3.0', $spec['info']['license']['name'], $variant);
      $this->assertSame(
        'https://github.com/hashtopolis/server/blob/master/LICENSE.txt',
        $spec['info']['license']['url'],
        $variant
      );
    }
  }

  /**
   * Paths are OpenAPI path templates from the moment they are built, so no
   * Slim regex constraint may survive into either variant.
   */
  public function testPathsCarryNoRouteConstraints(): void {
    foreach (['raw' => self::$raw, 'sanitized' => self::$sanitized] as $variant => $spec) {
      foreach (array_keys($spec['paths']) as $path) {
        $this->assertStringNotContainsString(':', $path, "Route constraint in $variant spec: $path");
      }
    }
  }

  public function testExpectedCoverage(): void {
    $this->assertGreaterThanOrEqual(160, count(self::$sanitized['paths']));
    $this->assertGreaterThanOrEqual(200, count(self::$sanitized['components']['schemas']));
  }

  public function testAllRefsResolve(): void {
    foreach (['raw' => self::$raw, 'sanitized' => self::$sanitized] as $variant => $spec) {
      $refs = [];
      $this->collectRefs($spec, $refs);
      $this->assertNotEmpty($refs);
      foreach ($refs as $ref) {
        $this->assertStringStartsWith('#/components/schemas/', $ref, "Unexpected ref format in $variant spec: $ref");
        $name = substr($ref, strlen('#/components/schemas/'));
        $this->assertArrayHasKey($name, $spec['components']['schemas'], "Dangling ref in $variant spec: $ref");
      }
    }
  }

  public function testSanitizedSchemasHaveNoBackslashNames(): void {
    foreach (array_keys(self::$sanitized['components']['schemas']) as $name) {
      $this->assertStringNotContainsString('\\', $name);
    }
  }

  public function testSanitizedOperationsAreComplete(): void {
    $operationIds = [];
    foreach (self::$sanitized['paths'] as $path => $pathItem) {
      foreach ($pathItem as $method => $operation) {
        $this->assertIsArray($operation, "$method $path");
        $this->assertArrayHasKey('operationId', $operation, "$method $path");
        $this->assertArrayHasKey('summary', $operation, "$method $path");
        $this->assertArrayHasKey('security', $operation, "$method $path");
        $this->assertNotEmpty($operation['tags'] ?? [], "$method $path");
        $operationIds[] = $operation['operationId'];

        $has2xx = false;
        foreach (array_keys($operation['responses'] ?? []) as $code) {
          if (str_starts_with((string)$code, '2')) {
            $has2xx = true;
          }
        }
        $this->assertTrue($has2xx, "No 2xx response on $method $path");
      }
    }
    $this->assertSame($operationIds, array_unique($operationIds), 'operationIds are not unique');
  }

  public function testGlobalTagsAreBuilt(): void {
    $this->assertNotEmpty(self::$sanitized['tags']);
    $tagNames = array_column(self::$sanitized['tags'], 'name');
    $this->assertContains('Helpers', $tagNames);
    $this->assertContains('Login', $tagNames);
  }

  public function testAuthTokenEndpointUsesBasicAuth(): void {
    $post = self::$sanitized['paths']['/api/v2/auth/token']['post'];
    $this->assertSame([['basicAuth' => []]], $post['security']);
    /* token.routes.php answers 201 with a plain JSON body, it is not a JSON:API route */
    $this->assertSame('#/components/schemas/Token', $post['responses']['201']['content']['application/json']['schema']['$ref']);
    $this->assertArrayNotHasKey('200', $post['responses']);
  }

  /**
   * Every JSON:API payload is served as application/vnd.api+json and every
   * error as an RFC 7807 problem document, matching what the API sends.
   */
  public function testMediaTypesMatchWhatTheApiSends(): void {
    foreach (self::$sanitized['paths'] as $path => $pathItem) {
      if (!str_starts_with($path, '/api/v2/ui/')) {
        continue;
      }
      foreach ($pathItem as $method => $operation) {
        foreach ($operation['responses'] ?? [] as $code => $response) {
          $mediaTypes = array_keys($response['content'] ?? []);
          if ($mediaTypes === []) {
            continue;
          }
          $expected = ((int)$code >= 400) ? 'application/problem+json' : 'application/vnd.api+json';
          $this->assertSame([$expected], $mediaTypes, "$method $path response $code");
        }
        if (isset($operation['requestBody']['content'])) {
          $this->assertSame(
            ['application/vnd.api+json'],
            array_keys($operation['requestBody']['content']),
            "$method $path request body"
          );
        }
      }
    }
  }

  /**
   * obj2Resource puts the self link and the relationships inside the resource
   * object, not next to it in the document.
   */
  public function testResourceObjectsCarryLinksAndRelationships(): void {
    $data = self::$sanitized['components']['schemas']['AccessGroupResponse']['properties']['data'];
    $this->assertArrayHasKey('self', $data['properties']['links']['properties']);
    $this->assertArrayHasKey('userMembers', $data['properties']['relationships']['properties']);
    $this->assertArrayNotHasKey(
      'relationships',
      self::$sanitized['components']['schemas']['AccessGroupResponse']['properties'],
      'relationships belong to the resource object, not to the document'
    );
  }

  /**
   * obj2Resource answers the primary key of the model as it comes from the
   * database, so every id is described as an integer. Covers resource objects,
   * relationship linkage, included resources and the write envelopes at once.
   */
  public function testEveryResourceIdIsDeclaredAsTheRuntimeAnswersIt(): void {
    $offenders = [];
    $this->collectIdSchemas(self::$sanitized['components']['schemas'], '', $offenders);
    $this->assertSame([], $offenders, 'Resource ids must be declared as integer');
  }

  private function collectIdSchemas(array $node, string $path, array &$offenders): void {
    foreach ($node as $key => $value) {
      if (!is_array($value)) {
        continue;
      }
      /**
       * An "id" sibling of a "type" holding a const is a resource identifier;
       * that pairing is what distinguishes it from an "id" attribute of a model.
       */
      if ($key === 'properties' && isset($value['id']['type'], $value['type']['const'])) {
        if ($value['id']['type'] !== 'integer') {
          $offenders[] = "$path.id declares {$value['id']['type']}";
        }
      }
      $this->collectIdSchemas($value, $path === '' ? (string)$key : "$path.$key", $offenders);
    }
  }

  public function testCountRouteReportsTheCountUnderMeta(): void {
    $get = self::$sanitized['paths']['/api/v2/ui/accessgroups/count']['get'];
    $this->assertSame(
      '#/components/schemas/AccessGroupCountResponse',
      $get['responses']['200']['content']['application/vnd.api+json']['schema']['$ref']
    );
    $countSchema = self::$sanitized['components']['schemas']['AccessGroupCountResponse'];
    $this->assertArrayHasKey('count', $countSchema['properties']['meta']['properties']);
    /* Counting takes filters, not pagination */
    $parameterNames = array_column($get['parameters'], 'name');
    $this->assertSame(['filter', 'include_total'], $parameterNames);
  }

  public function testKnownShapeSpotChecks(): void {
    $schemas = self::$sanitized['components']['schemas'];

    // Nullable integer field rendered as 3.1 type array
    $agentAttributes = $schemas['AgentResponse']['properties']['data']['properties']['attributes']['properties'];
    $this->assertSame(['integer', 'null'], $agentAttributes['userId']['type']);

    // Integer enum rendered as oneOf with const + title
    $this->assertSame(0, $agentAttributes['ignoreErrors']['oneOf'][0]['const']);
    $this->assertArrayHasKey('title', $agentAttributes['ignoreErrors']['oneOf'][0]);
    $this->assertSame(
      [0 => 'Linux', 1 => 'Windows', 2 => 'macOS'],
      array_column($agentAttributes['os']['oneOf'], 'title', 'const')
    );

    // A string enum names its values the same way
    $notificationSetting = $schemas['NotificationSettingResponse']['properties']['data']['properties']['attributes'];
    $this->assertContains(
      'taskComplete',
      array_column($notificationSetting['properties']['action']['oneOf'], 'const')
    );

    // A notification not tied to an object reports objectId as null, so the
    // attribute is present but nullable
    $this->assertSame(['integer', 'null'], $notificationSetting['properties']['objectId']['type']);
    $this->assertContains('objectId', $notificationSetting['required']);

    // Multi-expandable models get a discriminated union in "included"
    $this->assertSame(
      ['propertyName' => 'type'],
      $schemas['AgentResponse']['properties']['included']['items']['discriminator']
    );

    // Helper responses reference the schema of the model API they return
    $this->assertSame(
      '#/components/schemas/GlobalPermissionGroupSingleResponse',
      self::$sanitized['paths']['/api/v2/helper/getUserPermission']['get']['responses']['200']['content']['application/vnd.api+json']['schema']['$ref']
    );
  }

  public function testNoSchemaIsNamedAfterAMissingRelation(): void {
    foreach (['raw' => self::$raw, 'sanitized' => self::$sanitized] as $variant => $spec) {
      foreach (array_keys($spec['components']['schemas']) as $name) {
        $this->assertDoesNotMatchRegularExpression(
          '/Relation(GetResponse)?$/',
          $name,
          "Schema without a relation name in $variant spec: $name"
        );
      }
    }
  }

  private function collectRefs(array $data, array &$refs): void {
    foreach ($data as $key => $value) {
      if ($key === '$ref' && is_string($value)) {
        $refs[] = $value;
      } elseif (is_array($value)) {
        $this->collectRefs($value, $refs);
      }
    }
  }
}
