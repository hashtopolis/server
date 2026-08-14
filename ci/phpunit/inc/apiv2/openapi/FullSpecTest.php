<?php

namespace Hashtopolis\inc\apiv2\openapi;

use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
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
   * Every payload is served as application/vnd.api+json, errors included:
   * ErrorHandler::errorResponse answers JSON:API error documents. The documents
   * of the atomic operations extension carry its "ext" parameter, as JSON:API
   * 1.1 requires (AbstractModelAPI::atomicOperations).
   */
  public function testMediaTypesMatchWhatTheApiSends(): void {
    foreach (self::$sanitized['paths'] as $path => $pathItem) {
      if (!str_starts_with($path, '/api/v2/ui/')) {
        continue;
      }
      $isOperations = str_ends_with($path, '/operations');
      $successMediaType = $isOperations ? AbstractModelAPI::ATOMIC_MEDIA_TYPE : 'application/vnd.api+json';
      foreach ($pathItem as $method => $operation) {
        foreach ($operation['responses'] ?? [] as $code => $response) {
          $mediaTypes = array_keys($response['content'] ?? []);
          if ($mediaTypes === []) {
            continue;
          }
          $expected = ((int)$code >= 400) ? 'application/vnd.api+json' : $successMediaType;
          $this->assertSame([$expected], $mediaTypes, "$method $path response $code");
        }
        if (isset($operation['requestBody']['content'])) {
          $this->assertSame(
            [$successMediaType],
            array_keys($operation['requestBody']['content']),
            "$method $path request body"
          );
        }
      }
    }
  }

  /**
   * The atomic operations endpoint of a collection describes exactly the
   * operations the API class enables, answers 200 with one result per operation
   * and 204 when none of them returns data.
   */
  public function testAtomicOperationsEndpoints(): void {
    /* Every collection that can be modified has one, read-only ones do not */
    $this->assertArrayHasKey('/api/v2/ui/agents/operations', self::$sanitized['paths']);
    $this->assertArrayNotHasKey('/api/v2/ui/chunks/operations', self::$sanitized['paths']);

    $post = self::$sanitized['paths']['/api/v2/ui/hashtypes/operations']['post'];
    $this->assertSame(['HashTypes'], $post['tags']);
    $this->assertSame(
      '#/components/schemas/HashTypeAtomicOperations',
      $post['requestBody']['content'][AbstractModelAPI::ATOMIC_MEDIA_TYPE]['schema']['$ref']
    );
    $this->assertSame(
      '#/components/schemas/HashTypeAtomicResults',
      $post['responses']['200']['content'][AbstractModelAPI::ATOMIC_MEDIA_TYPE]['schema']['$ref']
    );
    $this->assertArrayHasKey('204', $post['responses']);
    /* Without the ext parameter the endpoint answers 415 */
    $this->assertArrayHasKey('415', $post['responses']);

    /* HashTypes support all three operations */
    $operations = self::$sanitized['components']['schemas']['HashTypeAtomicOperations'];
    $this->assertSame(['atomic:operations'], $operations['required']);
    $items = $operations['properties']['atomic:operations']['items'];
    $this->assertSame(
      ['add', 'update', 'remove'],
      array_map(fn($op) => $op['properties']['op']['const'], $items['oneOf'])
    );

    /* Configs can only be updated, so that is the only operation offered */
    $configItems = self::$sanitized['components']['schemas']['ConfigAtomicOperations']['properties']['atomic:operations']['items'];
    $this->assertArrayNotHasKey('oneOf', $configItems);
    $this->assertSame('update', $configItems['properties']['op']['const']);
    $this->assertSame(['id', 'type', 'attributes'], $configItems['properties']['data']['required']);

    /* A result reports the written object, the extension is named in the header */
    $results = self::$sanitized['components']['schemas']['HashTypeAtomicResults'];
    $this->assertSame([AbstractModelAPI::ATOMIC_EXT_URI], $results['properties']['jsonapi']['properties']['ext']['default']);
    $this->assertSame(
      '#/components/schemas/HashTypeResourceObject',
      $results['properties']['atomic:results']['items']['properties']['data']['$ref']
    );
  }

  /**
   * Errors are JSON:API error documents: one error object under "errors", with
   * the status as a string (ErrorHandler::errorResponse).
   */
  public function testErrorsAreJsonApiErrorDocuments(): void {
    $error = self::$sanitized['components']['schemas']['ErrorResponse'];
    $this->assertSame(['jsonapi', 'errors'], $error['required']);
    $this->assertSame(1, $error['properties']['errors']['maxItems']);
    $errorObject = $error['properties']['errors']['items'];
    $this->assertSame(['status', 'title'], $errorObject['required']);
    $this->assertSame('string', $errorObject['properties']['status']['type']);
    /* An error document applies no extension and no profile */
    $this->assertSame(['version'], array_keys($error['properties']['jsonapi']['properties']));
  }

  /**
   * Cursor pagination is a profile, not an extension, so an ordinary document
   * reports it under jsonapi.profile (AbstractBaseAPI::createJsonResponse).
   */
  public function testOrdinaryDocumentsReportTheCursorPaginationProfile(): void {
    $header = self::$sanitized['components']['schemas']['AgentListResponse']['properties']['jsonapi']['properties'];
    $this->assertArrayNotHasKey('ext', $header);
    $this->assertSame(
      [JsonApiFragments::CURSOR_PAGINATION_PROFILE],
      $header['profile']['default']
    );
  }

  /**
   * A collection takes GET and POST only: several objects are modified through
   * the atomic operations endpoint, not through a PATCH or DELETE on the
   * collection, which JSON:API does not describe.
   */
  public function testCollectionsAreNotPatchedOrDeletedAsAWhole(): void {
    foreach (self::$sanitized['paths'] as $path => $pathItem) {
      if (!str_starts_with($path, '/api/v2/ui/') || preg_match('#/(\{id\}|count|operations)#', $path)) {
        continue;
      }
      $this->assertSame([], array_intersect(['patch', 'delete'], array_keys($pathItem)), $path);
    }
  }

  /**
   * ContentNegotiationMiddleware runs for every route, so every operation can
   * answer 406, and every operation taking a body can answer 415.
   */
  public function testEveryOperationDocumentsContentNegotiation(): void {
    foreach (self::$sanitized['paths'] as $path => $pathItem) {
      foreach ($pathItem as $method => $operation) {
        $this->assertArrayHasKey('406', $operation['responses'], "$method $path");
        if (in_array($method, ['post', 'patch'], true)) {
          $this->assertArrayHasKey('415', $operation['responses'], "$method $path");
        }
      }
    }
  }

  /**
   * A single object PATCH carries the id of the object it updates, which
   * AbstractModelAPI::patchSingleObject requires and JSON:API mandates.
   */
  public function testSingleObjectPatchRequiresTheResourceId(): void {
    $patch = self::$sanitized['components']['schemas']['HashTypePatch']['properties']['data'];
    $this->assertContains('id', $patch['required']);
    $this->assertSame('string', $patch['properties']['id']['type']);
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
   * JSON:API requires resource ids to be strings, and obj2Resource casts them,
   * so no schema may describe an id as an integer. Covers resource objects,
   * relationship linkage, included resources and the write envelopes at once.
   */
  public function testEveryResourceIdIsDeclaredAsString(): void {
    $offenders = [];
    $this->collectIdSchemas(self::$sanitized['components']['schemas'], '', $offenders);
    $this->assertSame([], $offenders, 'Resource ids must be declared as string');
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
        if ($value['id']['type'] !== 'string') {
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

    // Config attributes use the schema override (oneOf over config value types)
    $this->assertArrayHasKey(
      'oneOf',
      $schemas['ConfigResponse']['properties']['data']['properties']['attributes']
    );

    // Foreign-key attributes are JSON:API string ids; a nullable relation
    // (userId -> User) renders as a 3.1 ["string", "null"] type array.
    $agentAttributes = $schemas['AgentResponse']['properties']['data']['properties']['attributes']['properties'];
    $this->assertSame(['string', 'null'], $agentAttributes['userId']['type']);

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
