<?php

namespace Hashtopolis\inc\apiv2\common;

use DI\Container;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\apiv2\model\ConfigAPI;
use Hashtopolis\inc\apiv2\model\ConfigSectionAPI;
use Hashtopolis\inc\apiv2\model\HashTypeAPI;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Slim\Factory\AppFactory;

/**
 * Request validation of the JSON:API atomic operations endpoint
 * (AbstractModelAPI::atomicOperations). No database is needed: the parsing step
 * only reads the document and the API class it is addressed to.
 */
final class AtomicOperationsTest extends TestCase {
  private function api(string $apiClass, array $alsoRegister = []): AbstractModelAPI {
    $container = new Container();
    $container->set('classMapper', new ClassMapper());

    /* register() adds the model to the class mapper, which resolves the type name */
    $app = AppFactory::create(null, $container);
    foreach (array_merge([$apiClass], $alsoRegister) as $class) {
      $class::register($app);
    }

    return new $apiClass($container);
  }

  private function parse(AbstractModelAPI $api, mixed $body): array {
    $parse = new ReflectionMethod(AbstractModelAPI::class, 'parseAtomicOperations');
    return $parse->invoke($api, $body);
  }

  public function testReducesEveryOperationToWhatItModifies(): void {
    $operations = $this->parse($this->api(HashTypeAPI::class), [
      'atomic:operations' => [
        ['op' => 'add', 'data' => ['type' => 'hashType', 'attributes' => ['description' => 'MD5']]],
        ['op' => 'update', 'data' => ['type' => 'hashType', 'id' => '1000', 'attributes' => ['isSalted' => true]]],
        ['op' => 'remove', 'ref' => ['type' => 'hashType', 'id' => 1100]],
      ]
    ]);

    $this->assertSame(
      [
        ['op' => 'add', 'id' => null, 'attributes' => ['description' => 'MD5']],
        ['op' => 'update', 'id' => '1000', 'attributes' => ['isSalted' => true]],
        /* An id may arrive as a number, the resource identifier is a string */
        ['op' => 'remove', 'id' => '1100', 'attributes' => []],
      ],
      $operations
    );
  }

  public function testRejectsADocumentWithoutOperations(): void {
    $api = $this->api(HashTypeAPI::class);

    foreach ([null, [], ['data' => []], ['atomic:operations' => []], ['atomic:operations' => 'update']] as $body) {
      try {
        $this->parse($api, $body);
        $this->fail('Accepted a document without operations: ' . json_encode($body));
      }
      catch (HttpError $error) {
        $this->assertSame(400, $error->getCode());
      }
    }
  }

  public function testRejectsUnknownAndUntargetedOperations(): void {
    $api = $this->api(HashTypeAPI::class);

    $invalid = [
      /* no op */
      ['data' => ['type' => 'hashType', 'attributes' => []]],
      /* unknown op */
      ['op' => 'replace', 'data' => ['type' => 'hashType', 'attributes' => []]],
      /* href targeting is not supported */
      ['op' => 'remove', 'href' => '/api/v2/ui/hashtypes/1', 'ref' => ['type' => 'hashType', 'id' => '1']],
      /* an update must name the object it updates */
      ['op' => 'update', 'data' => ['type' => 'hashType', 'attributes' => []]],
      /* a removal must name the object it removes */
      ['op' => 'remove', 'data' => ['type' => 'hashType', 'id' => '1']],
      /* attributes are part of an add */
      ['op' => 'add', 'data' => ['type' => 'hashType']],
      /* every operation addresses the type of the collection */
      ['op' => 'add', 'data' => ['type' => 'agent', 'attributes' => []]],
      ['op' => 'remove', 'ref' => ['type' => 'agent', 'id' => '1']],
    ];

    foreach ($invalid as $operation) {
      try {
        $this->parse($api, ['atomic:operations' => [$operation]]);
        $this->fail('Accepted an invalid operation: ' . json_encode($operation));
      }
      catch (HttpError $error) {
        $this->assertSame(400, $error->getCode());
      }
    }
  }

  /**
   * A collection only accepts the operations matching the methods it enables:
   * a config can be updated, but not created or deleted.
   */
  public function testRejectsOperationsTheCollectionDoesNotSupport(): void {
    $api = $this->api(ConfigAPI::class, [ConfigSectionAPI::class]);

    $updated = $this->parse($api, ['atomic:operations' => [
      ['op' => 'update', 'data' => ['type' => 'config', 'id' => '1', 'attributes' => ['value' => '2']]]
    ]]);
    $this->assertSame('update', $updated[0]['op']);

    foreach ([
      ['op' => 'add', 'data' => ['type' => 'config', 'attributes' => ['value' => '2']]],
      ['op' => 'remove', 'ref' => ['type' => 'config', 'id' => '1']],
    ] as $operation) {
      try {
        $this->parse($api, ['atomic:operations' => [$operation]]);
        $this->fail('Accepted an operation the collection does not support: ' . json_encode($operation));
      }
      catch (HttpForbidden $error) {
        $this->assertSame(403, $error->getCode());
      }
    }
  }
}
