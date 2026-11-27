<?php

namespace Hashtopolis\inc\apiv2\openapi;

use Hashtopolis\inc\apiv2\helper\AbortChunkHelperAPI;
use Hashtopolis\inc\apiv2\helper\GetBestTasksAgent;
use Hashtopolis\inc\apiv2\helper\GetCracksPerDayHelperAPI;
use Hashtopolis\inc\apiv2\helper\GetNotificationActionsHelperAPI;
use Hashtopolis\inc\apiv2\helper\GetNotificationChannelsHelperAPI;
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
    // A helper takes a flat body, so its request body stays application/json.
    $this->assertSame(
      ['$ref' => '#/components/schemas/' . AbortChunkHelperAPI::class],
      $post['requestBody']['content']['application/json']['schema']
    );
    $this->assertSame(
      ['$ref' => '#/components/schemas/' . AbortChunkHelperAPI::class . 'Response'],
      $post['responses']['200']['content']['application/vnd.api+json']['schema']
    );

    // getMetaResponse puts the sample map under meta and leaves data empty
    $responseSchema = $spec['components']['schemas'][AbortChunkHelperAPI::class . 'Response'];
    $this->assertSame(['jsonapi', 'meta', 'data'], $responseSchema['required']);
    $this->assertSame(
      ['Abort' => ['type' => 'string', 'default' => 'Success']],
      $responseSchema['properties']['meta']['properties']
    );
    $this->assertSame(0, $responseSchema['properties']['data']['maxItems']);

    // Errors are RFC 7807 problem documents
    $this->assertSame(
      ['$ref' => '#/components/schemas/ErrorResponse'],
      $post['responses']['403']['content']['application/problem+json']['schema']
    );
  }

  public function testGetCracksPerDaySpec(): void {
    // GET helper with a custom register() (array callable to handleGet) whose
    // meta member carries dynamic names, so it states the meta schema itself
    // through getMetaResponseSchema().
    $spec = (new SpecBuilder())->buildForApiClasses([GetCracksPerDayHelperAPI::class]);

    $get = $spec['paths']['/api/v2/helper/getCracksPerDay']['get'];
    $this->assertStringStartsWith('Returns a map of date -> crack count', $get['description']);
    $this->assertSame([], $get['parameters']);
    $this->assertSame(
      ['$ref' => '#/components/schemas/' . GetCracksPerDayHelperAPI::class . 'Response'],
      $get['responses']['200']['content']['application/vnd.api+json']['schema']
    );

    $responseSchema = $spec['components']['schemas'][GetCracksPerDayHelperAPI::class . 'Response'];
    $this->assertSame(['jsonapi', 'meta', 'data'], $responseSchema['required']);
    $this->assertSame(['type' => 'integer'], $responseSchema['properties']['meta']['additionalProperties']);
    $this->assertSame(0, $responseSchema['properties']['data']['maxItems']);
  }

  public function testGetNotificationActionsSpec(): void {
    // GET helper answering meta only: the notification actions are defined by
    // the server, so they are described by a schema, not by a sample map.
    $spec = (new SpecBuilder())->buildForApiClasses([GetNotificationActionsHelperAPI::class]);

    $get = $spec['paths']['/api/v2/helper/getNotificationActions']['get'];
    $this->assertStringStartsWith('Lists the notification actions', $get['description']);
    $this->assertSame([], $get['parameters']);
    $this->assertSame(
      ['$ref' => '#/components/schemas/' . GetNotificationActionsHelperAPI::class . 'Response'],
      $get['responses']['200']['content']['application/vnd.api+json']['schema']
    );

    $responseSchema = $spec['components']['schemas'][GetNotificationActionsHelperAPI::class . 'Response'];
    $this->assertSame(['jsonapi', 'meta', 'data'], $responseSchema['required']);
    $actions = $responseSchema['properties']['meta']['properties']['actions'];
    $this->assertSame(
      ['action', 'objectType', 'requiredPermission', 'allowed'],
      $actions['items']['required']
    );
    $this->assertSame(['Hashlist', 'Agent', 'User', 'Task', 'NONE'], $actions['items']['properties']['objectType']['enum']);
    $this->assertSame(0, $responseSchema['properties']['data']['maxItems']);
  }

  public function testGetNotificationChannelsSpec(): void {
    // GET helper answering meta only: the channel names come from the
    // registered notification implementations, so they are described by a
    // schema, not by a sample map.
    $spec = (new SpecBuilder())->buildForApiClasses([GetNotificationChannelsHelperAPI::class]);

    $get = $spec['paths']['/api/v2/helper/getNotificationChannels']['get'];
    $this->assertStringStartsWith('Lists the notification channels', $get['description']);
    $this->assertSame([], $get['parameters']);
    $this->assertSame(
      ['$ref' => '#/components/schemas/' . GetNotificationChannelsHelperAPI::class . 'Response'],
      $get['responses']['200']['content']['application/vnd.api+json']['schema']
    );

    $responseSchema = $spec['components']['schemas'][GetNotificationChannelsHelperAPI::class . 'Response'];
    $this->assertSame(['jsonapi', 'meta', 'data'], $responseSchema['required']);
    $channels = $responseSchema['properties']['meta']['properties']['channels'];
    $this->assertSame(['name'], $channels['items']['required']);
    $this->assertSame(0, $responseSchema['properties']['data']['maxItems']);
  }

  public function testGetBestTasksAgentSpec(): void {
    // GET helper declared as "Task[]": it answers with resource objects under
    // data inside the slim helper envelope (jsonapi and data, no links and no
    // meta), referencing the resource object component of the model routes.
    $spec = (new SpecBuilder())->buildForApiClasses([GetBestTasksAgent::class]);

    $get = $spec['paths']['/api/v2/helper/getBestTasksAgent']['get'];
    $this->assertSame(
      ['$ref' => '#/components/schemas/' . GetBestTasksAgent::class . 'Response'],
      $get['responses']['200']['content']['application/vnd.api+json']['schema']
    );

    $responseSchema = $spec['components']['schemas'][GetBestTasksAgent::class . 'Response'];
    $this->assertSame(['jsonapi', 'data'], $responseSchema['required']);
    $this->assertSame('array', $responseSchema['properties']['data']['type']);
    $this->assertSame(
      ['$ref' => '#/components/schemas/TaskResourceObject'],
      $responseSchema['properties']['data']['items']
    );
  }
}
