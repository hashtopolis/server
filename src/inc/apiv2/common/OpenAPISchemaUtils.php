<?php

namespace Hashtopolis\inc\apiv2\common;

use Middlewares\Utils\HttpErrorException;
use ReflectionMethod;
use Slim\App;

class OpenAPISchemaUtils {
  /**
   * @throws HttpErrorException
   */
  static function typeLookup($feature): array {
    $type_format = null;
    $type_enum = null;
    $sub_type = null;
    if ($feature['type'] == 'int') {
      $type = "integer";
    }
    elseif ($feature['type'] == 'uint64') {
      /* TODO: Specify integer ranges */
      $type = "integer";
    }
    elseif ($feature['type'] == 'int64') {
      $type = "integer";
      $type_format = "int64";
    }
    elseif ($feature['type'] == 'dict') {
      $type = "object";
      if ($feature['subtype'] !== 'unset') {
        $sub_type = self::typeLookup(['type' => $feature['subtype'], 'choices' => 'unset'])['type'];
      }
    }
    elseif ($feature['type'] == 'array') {
      $type = "array";
      $sub_type = "integer"; //TODO: subtype is hardcoded because we only have int arrays
    }
    elseif ($feature['type'] == 'bool') {
      $type = "boolean";
    }
    elseif (str_starts_with($feature['type'], 'str(')) {
      $type = "string";
    }
    elseif ($feature['type'] == 'str') {
      $type = "string";
    }
    else {
      throw new HttpErrorException("Cast for type  '" . $feature['type'] . "' not implemented");
    }

    if (is_array($feature['choices'])) {
      $type_enum = array_keys($feature['choices']);
    }

    return [
      "type" => $type,
      "type_format" => $type_format,
      "type_enum" => $type_enum,
      "subtype" => $sub_type
    ];
  }

  static function parsePhpDoc($doc): array|string {
    $cleanedDoc = preg_replace([
      '/^\/\*\*/',   // Remove opening /**
      '/\*\/$/',      // Remove closing */
      '/^\s*\*\s?/m'  // Remove leading * on each line
    ], '', $doc);
    //markdown friendly line end
    return str_replace("\n", "<br />", $cleanedDoc);
  }

  // "jsonapi": {
  //   "version": "1.1",
  //   "ext": [
  //       "https://jsonapi.org/profiles/ethanresnick/cursor-pagination"
  //   ]
  // },
  static function makeJsonApiHeader(): array {
    return ["jsonapi" => [
      "type" => "object",
      "required" => ["version"],
      "properties" => [
        "version" => [
          "type" => "string",
          "default" => "1.1"
        ],
        "ext" => [
          "type" => "array",
          "items" => ["type" => "string"],
          "default" => ["https://jsonapi.org/profiles/ethanresnick/cursor-pagination"]
        ]
      ]
    ]
    ];
  }

  // "links": {
  //     "self": "/api/v2/ui/hashlists?page[size]=10000",
  //     "first": "/api/v2/ui/hashlists?page[size]=10000&page[after]=0",
  //     "last": "/api/v2/ui/hashlists?page[size]=10000&page[before]=345",
  //     "next": null,
  //     "prev": "/api/v2/ui/hashlists?page[size]=10000&page[before]=114"
  //   },
  static function makeLinks($uri): array {
    $self = $uri . "?page[size]=25";
    return ["links" => [
      "type" => "object",
      "required" => ["self"],
      "properties" => [
        "self" => [
          "type" => "string",
          "default" => $self
        ],
        "first" => [
          "type" => "string",
          "default" => $self . "&page[after]=0"
        ],
        "last" => [
          "type" => "string",
          "default" => $self . "&page[before]=500"
        ],
        "next" => [
          "type" => "string",
          "default" => $self . "&page[after]=25"
        ],
        "previous" => [
          "type" => "string",
          "default" => $self . "&page[before]=25"
        ]
      ]
    ]
    ];
  }

  static function makeRelationships($class, $uri, $container = null): array {
    $toOneRelationships = $class->getToOneRelationships();
    $toManyRelationships = $class->getToManyRelationships();

    // Legacy behavior when no container is provided
    if ($container === null) {
      $properties = [];
      $relationshipsNames = array_merge(array_keys($toOneRelationships), array_keys($toManyRelationships));
      sort($relationshipsNames);
      foreach ($relationshipsNames as $relationshipName) {
        $self = $uri . "/relationships/" . $relationshipName;
        $related = $uri . "/" . $relationshipName;
        $properties[] = [
          "properties" => [
            $relationshipName => [
              "type" => "object",
              "properties" => [
                "links" => [
                  "type" => "object",
                  "properties" => [
                    "self" => [
                      "type" => "string",
                      "default" => $self
                    ],
                    "related" => [
                      "type" => "string",
                      "default" => $related
                    ]
                  ]
                ]
              ]
            ]
          ]
        ];
      }
      return $properties;
    }

    // New behavior with container: resolve relationship types
    $properties = [];
    $classMapper = $container->get('classMapper');

    $allRelationships = array_merge($toOneRelationships, $toManyRelationships);
    ksort($allRelationships);

    foreach ($allRelationships as $relationshipName => $relationshipConfig) {
      $self = $uri . "/relationships/" . $relationshipName;
      $related = $uri . "/" . $relationshipName;
      $isToMany = array_key_exists($relationshipName, $toManyRelationships);

      $relationType = $relationshipConfig['relationType'];
      $apiClassName = $classMapper->get($relationType);
      $nameParts = explode('\\', $apiClassName);
      $typeName = lcfirst(substr(end($nameParts), 0, -3));

      $resourceIdentifier = [
        "type" => "object",
        "required" => ["type", "id"],
        "properties" => [
          "type" => [
            "type" => "string",
            "const" => $typeName
          ],
          "id" => [
            "type" => "integer"
          ]
        ]
      ];

      $dataSchema = $isToMany
        ? ["type" => "array", "items" => $resourceIdentifier]
        : ["oneOf" => [$resourceIdentifier, ["type" => "null"]]];

      $properties[$relationshipName] = [
        "type" => "object",
        "required" => ["links"],
        "properties" => [
          "links" => [
            "type" => "object",
            "required" => ["self", "related"],
            "properties" => [
              "self" => [
                "type" => "string",
                "default" => $self
              ],
              "related" => [
                "type" => "string",
                "default" => $related
              ]
            ]
          ],
          "data" => $dataSchema
        ]
      ];
    }
    return $properties;
  }

  static function getTUSHeader(): array {
    return [
      "description" => "Indicates the TUS version the server supports.
        Must always be set to `1.0.0` in compliant servers.",
      "schema" => [
        "type" => "string",
        "enum" => "enum: ['1.0.0']"
      ]
    ];
  }

  //TODO expandables array is unnecessarily indexed in the swagger UI
  static function makeExpandables($class, $container): array {
    $properties = [];
    $expandables = array_merge($class->getToOneRelationships(), $class->getToManyRelationships());
    foreach ($expandables as $expand => $expandVal) {
      $expandClass = $expandVal["relationType"];
      $expandApiClass = new ($container->get('classMapper')->get($expandClass))($container);
      $properties[] = [
        "required" => ["id", "type", "attributes"],
        "properties" => [
          "id" => [
            "type" => "integer"
          ],
          "type" => [
            "type" => "string",
            "const" => $expand
          ],
          "attributes" => [
            "type" => "object",
            "properties" => self::makeProperties($expandApiClass->getAliasedFeatures())
          ]
        ]
      ];
    };
    return $properties;
  }

  static function mapToProperties($map): array {
    $properties = array_map(function ($value) {
      if (is_int($value)) {
        $type = "integer";
      } elseif (is_float($value)) {
        $type = "number";
      } elseif (is_bool($value)) {
        $type = "boolean";
      } elseif (is_array($value) || is_object($value)) {
        $type = "object";
      } else {
        $type = "string";
      }
      return [
        "type" => $type,
        "default" => $value,
      ];
    }, $map);
    return [
      "type" => "array",
      "items" => [
        "type" => "object",
        "properties" => $properties
      ]
    ];
  }

  /**
   * @throws HttpErrorException
   */
  static function makeProperties($features, $skipPK = false): array {
    $propertyVal = [];
    foreach ($features as $feature) {
      if ($skipPK && $feature['pk']) {
        continue;
      }
      $ret = self::typeLookup($feature);
      $propertyVal[$feature['alias']]["type"] = $ret["type"];
      if ($ret["type_format"] !== null) {
        $propertyVal[$feature['alias']]["format"] = $ret["type_format"];
      }
      if ($ret["type_enum"] !== null) {
        $propertyVal[$feature['alias']]["enum"] = $ret["type_enum"];
      }
      if ($ret["subtype"] !== null) {
        if ($ret["type"] === "object") {
          $propertyVal[$feature['alias']]["additionalProperties"]["type"] = $ret["subtype"];
        } else {
          $propertyVal[$feature['alias']]["items"]["type"] = $ret["subtype"];
        }
      }
    }
    return $propertyVal;
  }

  static function buildPatchPost($properties, $name, $id = null): array {
    $required = ["type", "attributes"];
    if ($id) {
      $required[] = "id";
    }
    $result = ["data" => [
      "type" => "object",
      "required" => $required,
      "properties" => [
        "type" => [
          "type" => "string",
          "const" => $name
        ],
        "attributes" => [
          "type" => "object",
          "properties" => $properties
        ]
      ]
    ]
    ];

    if ($id) {
      $result["data"]["properties"]["id"] = [
        "type" => "integer",
      ];
    }
    return $result;
  }

  /**
   * This function builds the post/patch attributes for a relationship. When $istomany is false,
   * it would build the attributes for a to one relationship. If it is true it will build it for a too many relationship.
   * */
  static function buildPostPatchRelation($name, $isToMany): array {
    $resourceRecord = [
      "type" => "object",
      "required" => ["type", "id"],
      "properties" => [
        "type" => [
          "type" => "string",
          "const" => $name
        ],
        "id" => [
          "type" => "integer",
          "default" => 1
        ]
      ]
    ];
    if ($isToMany) {
      return ["data" => [
        "type" => "array",
        "items" => $resourceRecord
      ]
      ];
    }
    else {
      return ["data" => $resourceRecord];
    }
  }

  static function makeDescription($isRelation, $method, $singleObject): string {
    $description = "";
    switch ($method) {
      case "get":
        if ($isRelation) {
          if ($singleObject) {
            $description = "GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.";
          }
          else {
            $description = "GET request for a to-many relationship link. Returns a list of resource records of objects that are part of the specified relation.";
          }
        }
        else {
          if ($singleObject) {
            $description = "GET request to retrieve a single object.";
          }
          else {
            $description = "GET many request to retrieve multiple objects.";
          }
        }
        break;
      case "post":
        if ($isRelation) {
          if ($singleObject) {
            $description = "POST request to create a to-one relationship link.";
          }
          else {
            $description = "POST request to create a to-many relationship link.";
          }
        }
        else {
          $description = "POST request to create a new object. The request must contain the resource record as data with the attributes of the new object."
            . "To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.";
        }
        break;
      case "patch":
        if ($isRelation) {
          if ($singleObject) {
            $description = "PATCH request to update a to one relationship.";
          }
          else {
            $description = "PATCH request to update a to-many relationship link.";
          }
        }
        else {
          $description = "PATCH request to update attributes of a single object.";
        }
    }
    return $description;
  }

  /**
   * Build the OpenAPI spec from the application routes.
   */
  static function buildSpec(App $app): array {
    /* Hold collection of all scopes discovered */
    $all_scopes = [];

    $paths = [];
    $components["ErrorResponse"] = [
      "type" => "object",
      "required" => ["status"],
      "properties" => [
        "title" => [
          "type" => "string",
          "example" => "about=>blank"
        ],
        "type" => [
          "type" => "string",
          "example" => "Error details here"
        ],
        "status" => [
          "type" => "integer",
          "example" => 400
        ]
      ]
    ];
    $components["NotFoundResponse"] = [
      "type" => "object",
      "required" => ["message"],
      "properties" => [
        "message" => [
          "type" => "string",
          "example" => "404 Not Found"
        ],
        "exception" => [
          "type" => "object",
          "properties" => [
            "type" => [
              "type" => "string",
              "example" => "Slim\\Exception\\HttpNotFoundException"
            ],
            "code" => [
              "type" => "integer",
              "example" => 404
            ],
            "message" => [
              "type" => "string",
              "example" => "Not Found"
            ],
            "file" => [
              "type" => "string",
              "example" => "../hashtopolis/server/vendor/slim/slim/Slim/Middleware/RoutingMiddleware.php"
            ],
            "line" => [
              "type" => "integer",
              "example" => 91
            ]
          ]
        ]
      ]
    ];

    /* Iterate over routes */
    $routes = $app->getRouteCollector()->getRoutes();
    foreach ($routes as $route) {
      /* Quirk to receive className, since it is hidden in a protected variable */
      $reflectionOfRoute = new \ReflectionObject($route);
      $protectedCallable = $reflectionOfRoute->getProperty('callable');
      $reflectionCallable = ($protectedCallable->getValue($route));

      /* Assume only one method per route call */
      assert(sizeof($route->getMethods()) == 1, "More than 1 methods found for this route");
      /* Path relative to basePath */
      $path = $route->getPattern();
      $method = strtolower($route->getMethods()[0]);

      if (!is_string($reflectionCallable)) {
        /* OPTIONS (CORS) have an function callable, ignore for now */
        continue;
      }

      /* Retrieve parameters */
      $explodedCallable = explode(':', $reflectionCallable);
      $apiClassName = $explodedCallable[0];
      $apiMethod = $explodedCallable[1];
      $class = new $apiClassName($app->getContainer());

      if (!($class instanceof AbstractModelAPI)) {
        $name = $class::class;
        $apiMethod = ($apiMethod == "processPost" && $name != "ImportFileHelperAPI") ? "actionPost" : $apiMethod;
        $reflectionApiMethod = new ReflectionMethod($name, $apiMethod);
        $paths[$path][$method]["description"] = self::parsePhpDoc($reflectionApiMethod->getDocComment());
        $parameters = $class->getCreateValidFeatures();
        $properties = self::makeProperties($parameters);
        $components[$name] =
          [
            "type" => "object",
            "properties" => $properties,
          ];
        if ($method == "post") {
          $reflectionMethodFormFields = new ReflectionMethod($name, "getFormFields");
          $bodyDescription = self::parsePhpDoc($reflectionMethodFormFields->getDocComment());
          $paths[$path][$method]["requestBody"] = [
            "description" => $bodyDescription,
            "required" => true,
            "content" => [
              "application/json" => [
                "schema" => [
                  '$ref' => "#/components/schemas/" . $name
                ],
              ]
            ]
          ];
        }
        elseif ($method == "get") {
          $paths[$path][$method]["parameters"] = $class->getParamsSwagger();
        }
        $request_response = $class->getResponse();
        $ref = null;
        if (is_array($request_response)) {
          $responseProperties = self::mapToProperties($request_response);
          $components[$name . "Response"] = $responseProperties;
          $ref = "#/components/schemas/" . $name . "Response";
        }
        else if (is_string($request_response)) {
          $ref = "#/components/schemas/" . $request_response . "SingleResponse";
        }
        else if ($name == "ImportFileHelperAPI") {
          //ImportFileHelperAPI is hardcoded, because its different than other helpers.
          continue;
        }
        if (isset($ref)) {
          $paths[$path][$method]["responses"]["200"] = [
            "description" => "successful operation",
            "content" => [
              "application/json" => [
                "schema" => [
                  '$ref' => $ref
                ]
              ]
            ]
          ];
        }
        else {
          $paths[$path][$method]["responses"]["200"] = [
            "description" => "successful operation",
          ];
        }
        continue;
      };

      /* Quick to find out if single parameter object is used */
      $singleObject = ((strstr($path, '/{id:')) !== false);
      $api_name_parts = explode('\\', get_class($class));
      $name = substr(end($api_name_parts), 0, -3); // Remove "API" suffix
      $typeName = lcfirst($name);
      $uri = $class->getBaseUri();

      $isRelation = (strstr($path, "/relationships/")) !== false;
      if (str_contains($path, "relation:")) {
        $relation = rtrim(explode("relation:", $path)[1], "}");
        $isToMany = array_key_exists($relation, $class::getToManyRelationships());
        $isToOne = array_key_exists($relation, $class::getToOneRelationships());
        assert(!($isToMany && $isToOne), "An relationship cant be a to one and to many at the same time.");
      } else {
        $isToMany = $isToOne = false;
        $relation = null;
      }

      $expandables = implode(",", $class->getExpandables());
      /**
       * Create component objects
       */
      if (!array_key_exists($name, $components)) {
        $responseFeatures = array_filter($class->getFeaturesWithoutFormfields(), fn($f) => !$f['private']);
        $responseAttributeProperties = self::makeProperties($responseFeatures, true);
        $aggregateFeatures = $class->getAggregateFeatures();
        $aggregateAttributeProperties = self::makeProperties($aggregateFeatures, true);
        $allResponseProperties = array_merge($responseAttributeProperties, $aggregateAttributeProperties);
        $properties_return_post_patch = [
          "data" => [
            "type" => "object",
            "required" => ["id", "type", "attributes"],
            "properties" => [
              "id" => [
                "type" => "integer",
              ],
              "type" => [
                "type" => "string",
                "const" => $typeName
              ],
              "attributes" => [
                "type" => "object",
                "required" => array_keys($responseAttributeProperties),
                "properties" => $allResponseProperties
              ],
            ]
          ]
        ];

        $relationshipProperties = self::makeRelationships($class, $uri, $app->getContainer());
        $relationships = ["relationships" => [
          "type" => "object",
          "required" => array_keys($relationshipProperties),
          "properties" => $relationshipProperties
        ]
        ];
        $included = ["included" => [
          "type" => "array",
          "items" => [
            "type" => "object",
            "properties" => self::makeExpandables($class, $app->getContainer())
          ],
        ]
        ];

        $properties_return_list = [
          "data" => [
            "type" => "array",
            "items" => $properties_return_post_patch["data"]
          ]
        ];

        $properties_get_single = array_merge($properties_return_post_patch, $relationships, $included);

        $json_api_header = self::makeJsonApiHeader();
        $links = self::makeLinks($uri);
        $properties_return_post_patch = array_merge($json_api_header, $properties_return_post_patch);
        $properties_create = self::buildPatchPost(self::makeProperties($class->getAllPostParameters($class->getCreateValidFeatures())), $typeName);
        $properties_get = array_merge($json_api_header, $links, $properties_get_single, $included);
        $properties_get_list = array_merge($json_api_header, $links, $properties_return_list, $relationships, $included);
        $properties_patch = self::buildPatchPost(self::makeProperties($class->getPatchValidFeatures(), true), $typeName);
        $properties_patch_post_relation = self::buildPostPatchRelation($relation, ($isToMany && !$isToOne));
        $responseGetRelation = $properties_patch_post_relation;

        $components[$name . "Create"] =
          [
            "type" => "object",
            "required" => ["data"],
            "properties" => $properties_create,
          ];

        $components[$name . "Patch"] =
          [
            "type" => "object",
            "required" => ["data"],
            "properties" => $properties_patch,
          ];

        $components[$name . "Response"] =
          [
            "type" => "object",
            "required" => ["jsonapi", "data"],
            "properties" => $properties_get,
          ];

        $components[$name . "Relation" . ucfirst($relation)] =
          [
            "type" => "object",
            "required" => ["data"],
            "properties" => $properties_patch_post_relation,
          ];

        $components[$name . "Relation" . ucfirst($relation) . "GetResponse"] =
          [
            "type" => "object",
            "required" => ["data"],
            "properties" => $responseGetRelation
          ];

        $components[$name . "SingleResponse"] =
          [
            "type" => "object",
            "required" => ["data"],
            "properties" => $properties_get_single
          ];

        $components[$name . "PostPatchResponse"] =
          [
            "type" => "object",
            "required" => ["jsonapi", "data"],
            "properties" => $properties_return_post_patch
          ];

        $components[$name . "ListResponse"] =
          [
            "type" => "object",
            "required" => ["jsonapi", "data"],
            "properties" => $properties_get_list,
          ];
      }

      /**
       * Create path objects
       */

      /* Determine the scopes required for the call */
      $required_scopes = $class->getRequiredPermissions($method);
      array_push($all_scopes, ...$required_scopes);

      $paths[$path][$method] = [
        "tags" => [
          $name . 's'
        ],
        "responses" => [

          "400" => [
            "description" => "Invalid request",
            "content" => [
              "application/json" => [
                "schema" => [
                  '$ref' => "#/components/schemas/ErrorResponse"
                ]
              ]
            ]
          ],
          "401" => [
            "description" => "Authentication failed",
            "content" => [
              "application/json" => [
                "schema" => [
                  '$ref' => "#/components/schemas/ErrorResponse"
                ]
              ]
            ]
          ]
        ],
        "security" => [
          [
            "bearerAuth" => [
              $required_scopes
            ]
          ]
        ]
      ];

      $paths[$path][$method]["description"] = self::makeDescription($isRelation, $method, $singleObject);

      if ($isRelation && in_array($method, ["post", "patch", "delete"], true)) {
        $paths[$path][$method]["responses"]["204"] =
          [
            "description" => "Successfull operation"
          ];
      }
      if ($singleObject) {
        /* Single objects could not exists */
        $paths[$path][$method]["responses"]["404"] =
          [
            "description" => "Not Found",
            "content" => [
              "application/json" => [
                "schema" => [
                  '$ref' => "#/components/schemas/NotFoundResponse"
                ]
              ]
            ]
          ];

        /* Method specific responses and requests for single objects */
        if ($method == 'get') {
          if (!$isRelation && str_contains($path, "relation:")) {
            $paths[$path][$method]["responses"]["200"] = [
              "description" => "successful operation",
              "content" => [
                "application/json" => [
                  "schema" => [
                    '$ref' => "#/components/schemas/" . $name . "Relation" . ucfirst($relation) . "GetResponse"

                  ]
                ]
              ]
            ];
          }
          else {
            $paths[$path][$method]["responses"]["200"] = [
              "description" => "successful operation",
              "content" => [
                "application/json" => [
                  "schema" => [
                    '$ref' => "#/components/schemas/" . $name . "Response"
                  ]
                ]
              ]
            ];
          }

          /* Supported by client, not by browser, disabled for APIdocs */
          // /* JSON object required */
          // $paths[$path][$method]["requestBody"] = [
          //   "required" => true,
          //   "content" => [
          //     "application/json" => [
          //       "schema" => [
          //         '$ref' => "#/components/schemas/ObjectRequest"
          //       ],
          //     ],
          // ]];

        }
        elseif ($method == 'patch') {
          if ($isRelation) {
            $paths[$path][$method]["requestBody"] = [
              "required" => true,
              "content" => [
                "application/json" => [
                  "schema" => [
                    '$ref' => "#/components/schemas/" . $name . "Relation" . ucfirst($relation)
                  ],
                ],
              ]
            ];
          }
          else {
            $paths[$path][$method]["requestBody"] = [
              "required" => true,
              "content" => [
                "application/json" => [
                  "schema" => [
                    '$ref' => "#/components/schemas/" . $name . "Patch"
                  ],
                ],
              ]
            ];

            $paths[$path][$method]["responses"]["200"] = [
              "description" => "successful operation",
              "content" => [
                "application/json" => [
                  "schema" => [
                    '$ref' => "#/components/schemas/" . $name . "PostPatchResponse"
                  ]
                ]
              ]
            ];
          }
        }
        elseif ($method == 'delete') {
          $paths[$path][$method]["responses"]["204"] = [
            "description" => "successfully deleted",
          ];

          if ($isRelation) {
            $paths[$path][$method]["requestBody"] = [
              "required" => true,
              "content" => [
                "application/json" => [
                  "schema" => [
                    '$ref' => "#/components/schemas/" . $name . "Relation" . ucfirst($relation)
                  ],
                ],
              ]
            ];
          }
          else {
            /* Empty JSON object required */
            $paths[$path][$method]["requestBody"] = [
              "required" => true,
              "content" => [
                "application/json" => [],
              ]
            ];
          }
        }
        elseif ($method == 'post') {
          $paths[$path][$method]["responses"]["204"] = [
            "description" => "successfully created",
          ];

          /* Empty JSON object required */
          $paths[$path][$method]["requestBody"] = [
            "required" => true,
            "content" => [
              "application/json" => [],
            ]
          ];
        }
        else {
          throw new HttpErrorException("Method '$method' not implemented");
        }
      }
      else {
        /* Model API entry point */
        if ($method == 'get') {
          $paths[$path][$method]["responses"]["200"] = [
            "description" => "successful operation",
            "content" => [
              "application/json" => [
                "schema" => [
                  '$ref' => "#/components/schemas/" . $name . "ListResponse"
                ]
              ]
            ]
          ];

          /* Supported by client, not by browser, disabled for APIdocs */
          // $paths[$path][$method]["requestBody"] = [
          //   "content" => [
          //     "application/json" => [
          //       "schema" => [
          //         '$ref' => "#/components/schemas/ObjectListRequest"
          //       ],
          //     ]
          // ]];


        }
        elseif ($method == 'post') {
          $paths[$path][$method]["responses"]["201"] = [
            "description" => "successful operation",
            "content" => [
              "application/json" => [
                "schema" => [
                  '$ref' => "#/components/schemas/" . $name . "PostPatchResponse"
                ]
              ]
            ]
          ];

          if ($isRelation) {
            $paths[$path][$method]["requestBody"] = [
              "required" => true,
              "content" => [
                "application/json" => [
                  "schema" => [
                    '$ref' => "#/components/schemas/" . $name . "Relation" . ucfirst($relation)
                  ],
                ],
              ]
            ];
          }
          else {
            $paths[$path][$method]["requestBody"] = [
              "required" => true,
              "content" => [
                "application/json" => [
                  "schema" => [
                    '$ref' => "#/components/schemas/" . $name . "Create"
                  ],
                ]
              ]
            ];
          }

        }
        elseif ($method == 'patch') {
          // TODO add patch many here
        }
        elseif ($method == 'delete') {
          // TODO add delete many here
        }
        else {
          throw new HttpErrorException("Method '$method' not implemented");
        }
      }

      if ($singleObject && $method == 'get') {
        $parameters = [
          [
            "name" => "id",
            "in" => "path",
            "required" => true,
            "schema" => [
              "type" => "integer",
              "format" => "int32",
              "example" => 10,
            ]
          ]
        ];

        if (!str_contains($path, "relation:")) {
          $parameters[] = [
            "name" => "include",
            "in" => "query",
            "schema" => [
              "type" => "string"
            ],
            "description" => "Items to include. Comma seperated"
          ];
        };
      }
      else {
        if ($method == 'get') {
          $parameters = [
            [
              "name" => "page[after]",
              "in" => "path",
              "schema" => [
                "type" => "integer",
                "format" => "int32"
              ],
              "example" => 0,
              "description" => "Pointer to paginate to retrieve the data after the value provided"
            ],
            [
              "name" => "page[before]",
              "in" => "path",
              "schema" => [
                "type" => "integer",
                "format" => "int32"
              ],
              "example" => 0,
              "description" => "Pointer to paginate to retrieve the data before the value provided"
            ],
            [
              "name" => "page[size]",
              "in" => "path",
              "schema" => [
                "type" => "integer",
                "format" => "int32"
              ],
              "example" => 100,
              "description" => "Amout of data to retrieve inside a single page"
            ],
            [
              "name" => "filter",
              "in" => "path",
              "style" => "deepobject",
              "explode" => true,
              "schema" => [
                "type" => "object",
              ],
              "description" => "Filters results using a query",
              "example" => '"filter[hashlistId__gt]": 200'
            ],
            [
              "name" => "include",
              "in" => "path",
              "schema" => [
                "type" => "string"
              ],
              "description" => "Items to include, comma seperated. Possible options: " . $expandables
            ]
          ];
        }
        else {
          $parameters = [];
        }
      }
      $paths[$path][$method]["parameters"] = $parameters;
    };

    /**
     * Build static entries
     */
    $paths["/api/v2/auth/token"] = [
      "post" => [
        "tags" => [
          "Login"
        ],
        "requestBody" => [
          "required" => true,
          "content" => [
            "application/json" => [
              "schema" => [
                '$ref' => "#/components/schemas/TokenRequest"
              ]
            ]
          ]
        ],
        "responses" => [
          "200" => [
            "description" => "Success",
            "content" => [
              "application/json" => [
                "schema" => [
                  '$ref' => "#/components/schemas/Token"
                ]
              ]
            ]
          ],
          "401" => [
            "description" => "Authentication failed",
            "content" => [
              "application/json" => [
                "schema" => [
                  '$ref' => "#/components/schemas/ErrorResponse"
                ]
              ]
            ]
          ],
          "404" => [
            "description" => "Not Found",
            "content" => [
              "application/json" => [
                "schema" => [
                  '$ref' => "#/components/schemas/NotFoundResponse"
                ]
              ]
            ]
          ]
        ],
        "security" => [
          [
            "basicAuth" => []
          ]
        ]
      ]
    ];

    $components["Token"] = [
      "type" => "object",
      "required" => ["token", "expires"],
      "properties" => [
        "token" => [
          "type" => "string"
        ],
        "expires" => [
          "type" => "integer"
        ]
      ],
      "additionalProperties" => false
    ];
    $components["TokenRequest"] = [
      "type" => "array",
      "items" => [
        "type" => "string",
        "example" => "role.all"
      ]
    ];

    $components["ObjectRequest"] = [
      "type" => "object",
      "properties" => [
        "expand" => [
          "type" => "string",
        ],
        "expires" => [
          "type" => "integer"
        ]
      ],
      "additionalProperties" => false
    ];

    $components["ObjectListRequest"] = [
      "type" => "object",
      "properties" => [
        "expand" => [
          "type" => "string",
        ],
        "filter" => [
          "type" => "array",
          "items" => [
            "type" => "string",
            "example" => "",
          ]
        ]
      ],
      "additionalProperties" => false
    ];
    //Hard coded headers for the importfile endpoints.
    $paths["/api/v2/helper/importFile"]["post"]["parameters"] = [
      [
        "name" => "Upload-Metadata",
        "in" => "header",
        "required" => "true",
        "schema" => [
          "type" => "string",
          "pattern" => '^([a-zA-Z0-9]+ [A-Za-z0-9+/=]+)(,[a-zA-Z0-9]+ [A-Za-z0-9+/=]+)*$'
        ],
        "example" => "filename ZXhhbXBsZS50eHQ=",
        "description" => " The Upload-Metadata header contains one or more comma-separated key-value pairs.
            Each pair is formatted as `<key> <base64(value)>`, where:
              - `key` is a string without spaces.
              - `value` is base64-encoded"
      ],
      [
        "name" => "Upload-Length",
        "in" => "header",
        "schema" => [
          "type" => "integer",
          "minimum" => 1
        ],
        "example" => 10000,
        "description" => "The total size of the upload in bytes. Must be a positive integer.
          Required if `Upload-Defer-Length` is not set."
      ],
      [
        "name" => "Upload-Defer-Length",
        "in" => "header",
        "schema" => [
          "type" => "integer",
        ],
        "example" => 1,
        "description" => "Indicates that the upload length is not known at creation time.
          Value must be `1`. If present, `Upload-Length` must be omitted."
      ]
    ];

    $paths["/api/v2/helper/importFile/{id:[0-9]{14}-[0-9a-f]{32}}"]["patch"]["parameters"] = [
      [
        "name" => "Upload-Offset",
        "in" => "header",
        "required" => "true",
        "schema" => [
          "type" => "integer",
        ],
        "example" => "512",
        "description" => " The Upload-Offset header's value MUST be equal to the current offset of the resource"
      ],
      [
        "name" => "Content-Type",
        "in" => "header",
        "required" => "true",
        "schema" => [
          "type" => "string",
          "enum" => ["application/offset+octet-stream"]
        ],
      ],
    ];
    $paths["/api/v2/helper/importFile/{id:[0-9]{14}-[0-9a-f]{32}}"]["patch"]["requestBody"] = [
      [
        "required" => "true",
        "description" => "The binary data to push to the file",
        "content" => [
          "application/offset+octet-stream" => [
            "schema" => [
              "type" => "string",
              "format" => "binary"
            ]
          ]
        ]
      ]
    ];

    $paths["/api/v2/helper/importFile/{id:[0-9]{14}-[0-9a-f]{32}}"]["head"]["responses"]["200"] = [
      "description" => "successful request",
      "headers" => [
        "Tus-Resumable" => self::getTUSHeader(),
        "Upload-Offset" => [
          "description" => "Number of bytes already received",
          "schema" => [
            "type" => "integer"
          ]
        ],
        "Upload-Length" => [
          "description" => "Total upload length (if known)",
          "schema" => [
            "type" => "integer"
          ],
        ],
        "Upload-Defer-Length" => [
          "description" => "Indicates deferred upload length (if applicable)",
          "schema" => [
            "type" => "string"
          ],
        ],
        "Upload-Metadata" => [
          "description" => "Original metadata sent during creation",
          "schema" => [
            "type" => "string"
          ]
        ]
      ]
    ];

    $paths["/api/v2/helper/importFile"]["post"]["responses"]["201"] = [
      "description" => "successful operation",
      "headers" => [
        "Tus-Resumable" => self::getTUSHeader(),
        "Location" => [
          "description" => "Location of the file where the user can push to.",
          "schema" => [
            "type" => "string"
          ]
        ]
      ],
      "content" => [
        "application/pdf" => [
          "type" => "string",
          "format" => "binary"
        ]
      ]
    ];
    $paths["/api/v2/helper/importFile/{id:[0-9]{14}-[0-9a-f]{32}}"]["patch"]["responses"]["204"] = [
      "description" => "Chunk accepted",
      "headers" => [
        "Tus-Resumable" => self::getTUSHeader(),
        "Upload-Offset" => [
          "description" => "The new offset after the chunk is accepted. Indicates how many bytes were received so far.",
          "schema" => [
            "type" => "integer"
          ]
        ]
      ]
    ];
    /**
     * Build final result
     */
    $unique_all_scopes = array_unique($all_scopes);
    asort($unique_all_scopes);
    $result = [
      "openapi" => "3.1.0",
      "info" => [
        "title" => "Hashtopolis API",
        "version" => "v2"
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
   * Post-process the spec for strict OpenAPI 3.1.0 compliance.
   */
  static function sanitizeSpec(array $spec): array {
    // Fix: Add missing info fields
    if (!isset($spec['info']['description'])) {
      $spec['info']['description'] = 'Hashtopolis REST API';
    }
    if (!isset($spec['info']['contact'])) {
      $spec['info']['contact'] = [
        'name' => 'Hashtopolis',
        'url' => 'https://github.com/hashtopolis/server'
      ];
    }

    // Phase 1: Build rename map for schema names containing backslashes
    $renameMap = [];
    if (isset($spec['components']['schemas'])) {
      $usedShortNames = [];
      // Collect existing non-backslash names to avoid collisions
      foreach (array_keys($spec['components']['schemas']) as $key) {
        if (!str_contains($key, '\\')) {
          $usedShortNames[$key] = true;
        }
      }
      // Build rename map: extract short class name (last segment after \)
      foreach (array_keys($spec['components']['schemas']) as $key) {
        if (str_contains($key, '\\')) {
          $shortName = substr($key, strrpos($key, '\\') + 1);
          if (!isset($usedShortNames[$shortName])) {
            $renameMap[$key] = $shortName;
            $usedShortNames[$shortName] = true;
          }
        }
      }
      // Apply rename to component schema keys
      $newSchemas = [];
      foreach ($spec['components']['schemas'] as $key => $value) {
        $newKey = $renameMap[$key] ?? $key;
        $newSchemas[$newKey] = $value;
      }
      $spec['components']['schemas'] = $newSchemas;
    }

    // Phase 2: Remove scopes from bearerAuth (only valid on OAuth2)
    if (isset($spec['components']['securitySchemes']['bearerAuth']['scopes'])) {
      unset($spec['components']['securitySchemes']['bearerAuth']['scopes']);
    }

    // Phase 3: Clean path templates (strip Slim regex patterns)
    $newPaths = [];
    foreach ($spec['paths'] as $path => $pathItem) {
      $cleanPath = preg_replace('/\{([^:}]+):[^}]+\}/', '{$1}', $path);
      $newPaths[$cleanPath] = $pathItem;
    }
    $spec['paths'] = $newPaths;

    // Phase 4: Walk operations for fixes
    foreach ($spec['paths'] as $path => &$pathItem) {
      foreach ($pathItem as $method => &$operation) {
        if (!is_array($operation)) continue;

        // Fix: Security requirement - bearerAuth should have empty scopes array for HTTP bearer
        if (isset($operation['security'])) {
          foreach ($operation['security'] as &$secReq) {
            if (isset($secReq['bearerAuth'])) {
              $secReq['bearerAuth'] = [];
            }
          }
          unset($secReq);
        }

        // Fix: Query params incorrectly marked as path params + style casing
        if (isset($operation['parameters'])) {
          $queryParamNames = ['page[after]', 'page[before]', 'page[size]', 'filter', 'include'];
          foreach ($operation['parameters'] as &$param) {
            if (isset($param['in']) && $param['in'] === 'path' && in_array($param['name'], $queryParamNames)) {
              $param['in'] = 'query';
            }
            // Fix: style casing (deepobject -> deepObject)
            if (isset($param['style']) && $param['style'] === 'deepobject') {
              $param['style'] = 'deepObject';
            }
          }
          unset($param);
        }

        // Fix: requestBody as indexed array -- unwrap to first element
        if (isset($operation['requestBody'][0]) && is_array($operation['requestBody'][0])) {
          $operation['requestBody'] = $operation['requestBody'][0];
        }

        // Fix: Walk response content
        if (isset($operation['responses'])) {
          foreach ($operation['responses'] as &$responseObj) {
            if (!is_array($responseObj) || !isset($responseObj['content'])) continue;
            foreach ($responseObj['content'] as $mediaType => &$mediaObj) {
              // Fix: Empty media type object
              if (is_array($mediaObj) && empty($mediaObj)) {
                $mediaObj = ["schema" => ["type" => "object"]];
              }
              // Fix: Missing schema wrapper (has 'type' but no 'schema')
              elseif (is_array($mediaObj) && isset($mediaObj['type']) && !isset($mediaObj['schema'])) {
                $mediaObj = ["schema" => $mediaObj];
              }
            }
            unset($mediaObj);
          }
          unset($responseObj);
        }

        // Fix: Also for requestBody content
        if (isset($operation['requestBody']['content'])) {
          foreach ($operation['requestBody']['content'] as $mediaType => &$mediaObj) {
            if (is_array($mediaObj) && empty($mediaObj)) {
              $mediaObj = ["schema" => ["type" => "object"]];
            }
          }
          unset($mediaObj);
        }

        // Fix: Clean backslash-prefixed tag names
        if (isset($operation['tags'])) {
          $operation['tags'] = array_map(function($tag) {
            return str_contains($tag, '\\') ? substr($tag, strrpos($tag, '\\') + 1) : $tag;
          }, $operation['tags']);
        }
        // Fix: Add missing tags for helper/auth operations
        if (!isset($operation['tags']) || empty($operation['tags'])) {
          if (str_starts_with($path, '/api/v2/helper/')) {
            $operation['tags'] = ['Helpers'];
          } elseif (str_starts_with($path, '/api/v2/auth/')) {
            $operation['tags'] = ['Authentication'];
          }
        }

        // Fix: Add missing path parameter definitions
        preg_match_all('/\{(\w+)\}/', $path, $pathParamMatches);
        $expectedPathParams = $pathParamMatches[1] ?? [];
        if (!empty($expectedPathParams)) {
          $definedPathParams = [];
          if (isset($operation['parameters'])) {
            foreach ($operation['parameters'] as $existingParam) {
              if (isset($existingParam['in']) && $existingParam['in'] === 'path') {
                $definedPathParams[] = $existingParam['name'];
              }
            }
          }
          foreach ($expectedPathParams as $paramName) {
            if (!in_array($paramName, $definedPathParams)) {
              if (!isset($operation['parameters'])) {
                $operation['parameters'] = [];
              }
              $operation['parameters'][] = [
                "name" => $paramName,
                "in" => "path",
                "required" => true,
                "schema" => [
                  "type" => $paramName === 'id' ? "integer" : "string",
                ]
              ];
            }
          }
        }

        // Fix: Add missing operation summary
        if (!isset($operation['summary'])) {
          $tag = $operation['tags'][0] ?? '';
          $hasId = str_contains($path, '{id}');
          $isRelation = str_contains($path, '/relationships/');
          $isCount = str_ends_with($path, '/count');
          $summary = match($method) {
            'get' => $isCount ? "Count $tag" : ($hasId ? "Get $tag" : "List $tag"),
            'post' => $isRelation ? "Add $tag relationship" : "Create $tag",
            'patch' => "Update $tag",
            'delete' => $isRelation ? "Remove $tag relationship" : "Delete $tag",
            'head' => "Head $tag",
            default => ucfirst($method) . " $tag"
          };
          $operation['summary'] = $summary;
        }

        // Fix: Generate unique operationId
        if (!isset($operation['operationId'])) {
          $stripped = preg_replace('#^/api/v2/(ui|helper|auth)/#', '', $path);
          $parts = [];
          foreach (explode('/', $stripped) as $seg) {
            if ($seg === '') continue;
            if (str_starts_with($seg, '{')) {
              $parts[] = 'By' . ucfirst(trim($seg, '{}'));
            } else {
              $parts[] = ucfirst($seg);
            }
          }
          $operation['operationId'] = $method . implode('', $parts);
        }

        // Fix: Fill empty descriptions
        if (!isset($operation['description']) || $operation['description'] === '') {
          $operation['description'] = $operation['summary'] ?? '';
        }

        // Fix: Ensure operation has security defined
        if (!isset($operation['security'])) {
          $operation['security'] = [["bearerAuth" => []]];
        }

        // Fix: Ensure at least one 2xx response exists
        if (isset($operation['responses'])) {
          $has2xx = false;
          foreach (array_keys($operation['responses']) as $code) {
            if (str_starts_with((string)$code, '2')) {
              $has2xx = true;
              break;
            }
          }
          if (!$has2xx) {
            $operation['responses']['200'] = [
              "description" => "successful operation"
            ];
          }
        }
      }
      unset($operation);
    }
    unset($pathItem);

    // Phase 5: Recursive walk for $ref renaming, enum, required, description fixes
    $spec = self::recursiveFixValues($spec, $renameMap);

    // Phase 6: Build global tags array from all operations
    $allTags = [];
    foreach ($spec['paths'] as $pathItem) {
      foreach ($pathItem as $op) {
        if (is_array($op) && isset($op['tags'])) {
          foreach ($op['tags'] as $tag) { $allTags[$tag] = true; }
        }
      }
    }
    ksort($allTags);
    $spec['tags'] = array_map(fn($name) => ['name' => $name], array_keys($allTags));

    // Phase 7: Remove unreferenced component schemas (iterative until stable)
    if (isset($spec['components']['schemas'])) {
      $changed = true;
      while ($changed) {
        $changed = false;
        $refs = [];
        self::collectSchemaRefs($spec['paths'], $refs);
        self::collectSchemaRefs($spec['components']['schemas'], $refs);
        foreach (array_keys($spec['components']['schemas']) as $name) {
          if (!isset($refs[$name])) {
            unset($spec['components']['schemas'][$name]);
            $changed = true;
          }
        }
      }
    }

    return $spec;
  }

  private static function collectSchemaRefs(mixed $data, array &$refs): void {
    if (!is_array($data) && !is_object($data)) return;
    if (is_object($data)) $data = (array)$data;
    foreach ($data as $key => $value) {
      if ($key === '$ref' && is_string($value) && str_starts_with($value, '#/components/schemas/')) {
        $refs[substr($value, strlen('#/components/schemas/'))] = true;
      } elseif (is_array($value) || is_object($value)) {
        self::collectSchemaRefs($value, $refs);
      }
    }
  }

  private static function recursiveFixValues(array $data, array $renameMap): array {
    foreach ($data as $key => &$value) {
      // Fix: description as array -> string (skip schema objects named "description")
      if ($key === 'description' && is_array($value) && !isset($value['type'])) {
        $value = implode("\n", $value);
        continue;
      }

      // Fix: enum as string -> proper array
      if ($key === 'enum' && is_string($value)) {
        if (preg_match_all("/'([^']+)'/", $value, $matches)) {
          $value = $matches[1];
        }
        continue;
      }

      // Fix: properties must be a JSON object, not array
      if ($key === 'properties' && is_array($value)) {
        if (empty($value)) {
          // Empty array -> stdClass so json_encode outputs {} not []
          $value = new \stdClass();
          continue;
        }
        if (isset($value[0])) {
          // Indexed array -- try merging elements that have 'properties' sub-keys
          $canMerge = true;
          foreach ($value as $item) {
            if (!is_array($item) || !isset($item['properties'])) {
              $canMerge = false;
              break;
            }
          }
          if ($canMerge) {
            $merged = [];
            foreach ($value as $item) {
              $merged = array_merge($merged, $item['properties']);
            }
            $value = $merged;
          } else {
            // Non-mergeable indexed array -> force to object
            $value = (object)$value;
            continue;
          }
        }
      }

      if (is_array($value)) {
        $value = self::recursiveFixValues($value, $renameMap);
      } else {
        // Fix: $ref renaming (update references to renamed schemas)
        if ($key === '$ref' && is_string($value) && str_starts_with($value, '#/components/schemas/')) {
          $schemaName = substr($value, strlen('#/components/schemas/'));
          if (isset($renameMap[$schemaName])) {
            $value = '#/components/schemas/' . $renameMap[$schemaName];
          }
        }
        // Fix: required as string "true" -> boolean true
        if ($key === 'required' && $value === "true") {
          $value = true;
        }
      }
    }
    unset($value);
    return $data;
  }
}
