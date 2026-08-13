<?php

namespace Hashtopolis\inc\apiv2\common;

use DI\Container;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\inc\apiv2\model\HashTypeAPI;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;
use Slim\Factory\AppFactory;

/**
 * JSON:API requires the id of a resource object to be a string, while the
 * models keep their primary key as an int. This pins the cast in
 * AbstractBaseAPI::obj2Resource, which is what the generated spec describes.
 *
 * No database is needed: a model can be constructed in memory and obj2Resource
 * only reads its features.
 */
final class ResourceObjectIdTest extends TestCase {
  public function testResourceObjectIdIsSerializedAsString(): void {
    $container = new Container();
    $classMapper = new ClassMapper();
    $container->set('classMapper', $classMapper);

    /* register() adds the model to the class mapper and names the getOne route */
    $app = AppFactory::create(null, $container);
    HashTypeAPI::register($app);

    $api = new HashTypeAPI($container);
    $routeParser = new ReflectionProperty(AbstractBaseAPI::class, 'routeParser');
    $routeParser->setValue($api, $app->getRouteCollector()->getRouteParser());

    $obj2Resource = new ReflectionMethod(AbstractBaseAPI::class, 'obj2Resource');
    $expandResult = [];
    $resource = $obj2Resource->invokeArgs($api, [new HashType(1000, 'MD5', 0, 0), &$expandResult]);

    $this->assertSame('1000', $resource['id']);
    $this->assertSame('hashType', $resource['type']);

    /* The primary key is the id, it must not be repeated in the attributes */
    $this->assertArrayNotHasKey('hashTypeId', $resource['attributes']);

    /* Serializing must keep it a JSON string, not turn it back into a number */
    $this->assertStringContainsString('"id":"1000"', json_encode($resource, JSON_THROW_ON_ERROR));
  }
}
