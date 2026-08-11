<?php

namespace Hashtopolis\inc\apiv2\openapi;

use Hashtopolis\inc\apiv2\common\ApiRegistry;
use Hashtopolis\inc\defines\DTaskStatus;
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
    $this->assertSame('#/components/schemas/Token', $post['responses']['200']['content']['application/json']['schema']['$ref']);
  }

  public function testKnownShapeSpotChecks(): void {
    $schemas = self::$sanitized['components']['schemas'];

    // Config attributes use the schema override (oneOf over config value types)
    $this->assertArrayHasKey(
      'oneOf',
      $schemas['ConfigResponse']['properties']['data']['properties']['attributes']
    );

    // Nullable integer field rendered as 3.1 type array
    $agentAttributes = $schemas['AgentResponse']['properties']['data']['properties']['attributes']['properties'];
    $this->assertSame(['integer', 'null'], $agentAttributes['userId']['type']);

    // Integer enum rendered as oneOf with const + title
    $this->assertSame(0, $agentAttributes['os']['oneOf'][0]['const']);
    $this->assertArrayHasKey('title', $agentAttributes['os']['oneOf'][0]);

    // Multi-expandable models get a discriminated union in "included"
    $this->assertSame(
      ['propertyName' => 'type'],
      $schemas['AgentResponse']['properties']['included']['items']['discriminator']
    );

    // Computed task status covers every value DTaskStatus can produce
    $statusChoices = $schemas['TaskResponse']['properties']['data']['properties']['attributes']['properties']['status']['oneOf'];
    $this->assertSame(
      DTaskStatus::choices(),
      array_column($statusChoices, 'title', 'const')
    );

    // A notification not tied to an object reports objectId as null, so the
    // attribute is present but nullable
    $notificationAttributes = $schemas['NotificationSettingResponse']['properties']['data']['properties']['attributes'];
    $this->assertSame(['integer', 'null'], $notificationAttributes['properties']['objectId']['type']);
    $this->assertContains('objectId', $notificationAttributes['required']);

    // Helper responses reference the schema of the model API they return
    $this->assertSame(
      '#/components/schemas/GlobalPermissionGroupSingleResponse',
      self::$sanitized['paths']['/api/v2/helper/getUserPermission']['get']['responses']['200']['content']['application/json']['schema']['$ref']
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
