<?php

namespace Hashtopolis\inc\apiv2\openapi;

use DI\Container;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\common\ClassMapper;
use Slim\App;
use Slim\Factory\AppFactory;

/**
 * Generates the OpenAPI 3.1.0 document for the APIv2 by introspecting the
 * registered API classes. No database connection or HTTP server is required:
 * the only inputs are the API classes (and, through them, the static feature
 * definitions of the DBA models) plus the SpecOverrides handed to the
 * constructor.
 *
 * Entry points:
 * - buildFromApp():        used by the HTTP routes with the fully built app.
 * - buildForApiClasses():  used by tests and CLI tooling with an explicit
 *                          list of API classes.
 */
class SpecBuilder {
  private RouteIntrospector $routeIntrospector;
  private HelperApiPathBuilder $helperApiPathBuilder;
  private ModelApiPathBuilder $modelApiPathBuilder;
  private StaticFragments $staticFragments;

  /**
   * @param SpecOverrides|null $overrides corrections to the attribute schemas
   *   derived from the model features; the server's own corrections
   *   (SpecOverrides::defaults()) are used when none are given
   */
  public function __construct(?SpecOverrides $overrides = null) {
    $typeMapper = new FeatureTypeMapper();
    $jsonApiFragments = new JsonApiFragments();
    $this->routeIntrospector = new RouteIntrospector();
    $this->helperApiPathBuilder = new HelperApiPathBuilder($typeMapper, $jsonApiFragments);
    $this->modelApiPathBuilder = new ModelApiPathBuilder($typeMapper, $jsonApiFragments, $overrides ?? SpecOverrides::defaults());
    $this->staticFragments = new StaticFragments();
  }

  /**
   * Build the OpenAPI spec from the application routes.
   */
  public function buildFromApp(App $app): array {
    /* Hold collection of all scopes discovered */
    $all_scopes = [];

    $paths = [];
    $components = $this->staticFragments->errorComponents();

    /* Iterate over routes */
    foreach ($this->routeIntrospector->introspect($app) as $target) {
      $apiClassName = $target->className;
      $class = new $apiClassName($app->getContainer());

      if (!($class instanceof AbstractModelAPI)) {
        $this->helperApiPathBuilder->addRoute($target, $class, $paths, $components);
        continue;
      }

      $this->modelApiPathBuilder->addRoute($target, $class, $app->getContainer(), $paths, $components, $all_scopes);
    }

    /**
     * Build static entries
     */
    $paths["/api/v2/auth/token"] = $this->staticFragments->authTokenPath();

    foreach ($this->staticFragments->tokenComponents() as $key => $schema) {
      $components[$key] = $schema;
    }

    $this->staticFragments->applyImportFileTusPaths($paths);

    /**
     * Build final result
     */
    $unique_all_scopes = array_unique($all_scopes);
    asort($unique_all_scopes);
    $result = [
      "openapi" => "3.1.0",
      "info" => [
        "title" => "Hashtopolis API",
        "version" => "v2",
        "description" => "Hashtopolis REST API",
        "contact" => [
          "name" => "Hashtopolis",
          "url" => "https://github.com/hashtopolis/server"
        ],
        /* The license of the server itself, as stated by LICENSE.txt in its repository */
        "license" => [
          "name" => "GPL-3.0",
          "url" => "https://github.com/hashtopolis/server/blob/master/LICENSE.txt"
        ]
      ],
      "servers" => [
        [
          "url" => "/"
        ],
      ],
      "paths" => $paths,
      "components" => [
        "schemas" => $components,
        "securitySchemes" => [
          "bearerAuth" => [
            "type" => "http",
            "description" => "JWT Authorization header using the Bearer scheme.",
            "scheme" => "bearer",
            "bearerFormat" => "JWT",
            "scopes" => array_values($unique_all_scopes),
          ],
          "basicAuth" => [
            "type" => "http",
            "description" => "Basic Authorization header.",
            "scheme" => "basic"
          ]
        ]
      ],
    ];

    return $result;
  }

  /**
   * Build the OpenAPI spec for an explicit list of API classes, without a
   * pre-built Slim app. A bare app and DI container are assembled internally,
   * the given classes are registered on it and the spec is generated from the
   * resulting routes.
   *
   * The input must be closed under relationships: every relationship target
   * of a registered model API class must be resolvable through the class
   * mapper, otherwise relationship resolution fails. Related API classes
   * whose routes should not be part of the spec can be passed via
   * $classMapperOnlyClasses; they are then only registered on the class
   * mapper (no routes).
   *
   * @param list<class-string> $apiClasses classes whose routes are registered
   * @param list<class-string<AbstractModelAPI>> $classMapperOnlyClasses
   *   model API classes registered on the class mapper only
   */
  public function buildForApiClasses(array $apiClasses, array $classMapperOnlyClasses = []): array {
    $container = new Container();
    $classMapper = new ClassMapper();
    $container->set('classMapper', $classMapper);
    $app = AppFactory::create(null, $container);

    foreach ($classMapperOnlyClasses as $apiClass) {
      $classMapper->add($apiClass::getDBAclass(), $apiClass);
    }
    foreach ($apiClasses as $apiClass) {
      $apiClass::register($app);
    }

    return $this->buildFromApp($app);
  }
}
