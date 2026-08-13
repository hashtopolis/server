<?php

namespace Hashtopolis\inc\apiv2\common;

use DI\Container;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\model\HashTypeAPI;
use Hashtopolis\inc\apiv2\openapi\FeatureTypeMapper;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Slim\Factory\AppFactory;

/**
 * Foreign-key columns are tagged with a "reference" marker in the model
 * features. JSON:API requires ids to be strings, so such fields are emitted as
 * string ids in the response, typed as strings in the generated spec and
 * accepted as numeric strings on input, regardless of their integer storage.
 */
final class ReferenceIdTest extends TestCase {
  /** @return array<string, mixed> */
  private static function referenceFeature(bool $nullable): array {
    return [
      'read_only' => false, 'type' => 'int', 'subtype' => 'unset', 'choices' => 'unset',
      'null' => $nullable, 'pk' => false, 'protected' => false, 'private' => false,
      'alias' => 'userId', 'public' => false, 'reference' => 'User', 'dba_mapping' => false,
    ];
  }

  public function testDb2jsonSerialisesReferenceAsString(): void {
    $db2json = new ReflectionMethod(AbstractBaseAPI::class, 'db2json');

    $this->assertSame('5', $db2json->invoke(null, self::referenceFeature(false), 5));
    // A nullable relation keeps null (not the string "null").
    $this->assertNull($db2json->invoke(null, self::referenceFeature(true), null));
    // A plain integer column is untouched and stays a number.
    $plain = ['type' => 'int', 'reference' => false, 'subtype' => 'unset', 'null' => false];
    $this->assertSame(7, $db2json->invoke(null, $plain, 7));
  }

  public function testSpecTypesReferenceAsString(): void {
    $mapper = new FeatureTypeMapper();

    $required = $mapper->makeProperties([self::referenceFeature(false)]);
    $this->assertSame('string', $required['userId']['type']);
    $this->assertArrayNotHasKey('format', $required['userId']);

    $nullable = $mapper->makeProperties([self::referenceFeature(true)]);
    $this->assertSame(['string', 'null'], $nullable['userId']['type']);
  }

  public function testValidateDataAcceptsStringAndIntIds(): void {
    $api = $this->makeApi();
    $validate = new ReflectionMethod(AbstractBaseAPI::class, 'validateData');
    $features = ['userId' => self::referenceFeature(false)];

    // The preferred JSON:API string form and the legacy integer form both pass.
    $validate->invokeArgs($api, [['userId' => '5'], $features]);
    $validate->invokeArgs($api, [['userId' => 5], $features]);
    $this->addToAssertionCount(1);
  }

  public function testValidateDataRejectsNonNumericId(): void {
    $api = $this->makeApi();
    $validate = new ReflectionMethod(AbstractBaseAPI::class, 'validateData');
    $features = ['userId' => self::referenceFeature(false)];

    $this->expectException(HttpError::class);
    $validate->invokeArgs($api, [['userId' => 'abc'], $features]);
  }

  private function makeApi(): HashTypeAPI {
    $container = new Container();
    $container->set('classMapper', new ClassMapper());
    $app = AppFactory::create(null, $container);
    HashTypeAPI::register($app);
    return new HashTypeAPI($container);
  }
}
