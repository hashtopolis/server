<?php

namespace Hashtopolis\inc\apiv2\openapi;

use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Middlewares\Utils\HttpErrorException;
use Psr\Container\ContainerInterface;

/**
 * Builds the path and component entries for a model API route by
 * introspecting the API class and its DBA model features (attributes,
 * relationships, expandables, permissions).
 */
class ModelApiPathBuilder {
  public function __construct(
    private FeatureTypeMapper $typeMapper,
    private JsonApiFragments $jsonApiFragments
  ) {
  }

  /**
   * @throws HttpErrorException
   */
  public function addRoute(RouteTarget $target, AbstractModelAPI $api, ContainerInterface $container, array &$paths, array &$components, array &$all_scopes): void {
    $path = $target->pattern;
    $method = $target->httpMethod;
    $class = $api;

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
      $responseAttributeProperties = $this->typeMapper->makeProperties($responseFeatures, true);
      $aggregateFeatures = $class->getAggregateFeatures();
      $aggregateAttributeProperties = $this->typeMapper->makeProperties($aggregateFeatures, true);
      $allResponseProperties = array_merge($responseAttributeProperties, $aggregateAttributeProperties);
      $attributesOverride = $class->getOpenAPIAttributesSchemaOverride();
      if ($attributesOverride !== null) {
        $attributesSchema = $attributesOverride;
      } else {
        $attributesSchema = [
          "type" => "object",
          "required" => array_values(array_map(
            fn($f) => $f['alias'],
            array_filter($responseFeatures, fn($f) => !$f['pk'])
          )),
          "properties" => $allResponseProperties
        ];
      }
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
            "attributes" => $attributesSchema,
          ]
        ]
      ];

      $relationshipProperties = $this->makeRelationships($class, $uri, $container);
      $relationships = ["relationships" => [
        "type" => "object",
        "required" => array_keys($relationshipProperties),
        "properties" => $relationshipProperties
      ]
      ];
      $expandables = $this->makeExpandables($class, $container);
      $includedItems = count($expandables) === 1
        ? array_merge(["type" => "object"], $expandables[0])
        : [
            "oneOf" => array_map(
              fn($e) => array_merge(["type" => "object"], $e),
              $expandables
            ),
            "discriminator" => ["propertyName" => "type"]
          ];
      $included = ["included" => [
        "type" => "array",
        "items" => $includedItems,
      ]
      ];

      $properties_return_list = [
        "data" => [
          "type" => "array",
          "items" => $properties_return_post_patch["data"]
        ]
      ];

      $properties_get_single = array_merge($properties_return_post_patch, $relationships, $included);

      $json_api_header = $this->jsonApiFragments->makeJsonApiHeader();
      $links = $this->jsonApiFragments->makeLinks($uri);
      $properties_return_post_patch = array_merge($json_api_header, $properties_return_post_patch);
      $createFeatures = $class->getAllPostParameters($class->getCreateValidFeatures());
      $requiredCreateAttributes = array_values(array_map(
        fn($f) => $f['alias'],
        array_filter($createFeatures, fn($f) => !$f['null'])
      ));
      $properties_create = $this->jsonApiFragments->buildPatchPost($this->typeMapper->makeProperties($createFeatures), $typeName, null, $requiredCreateAttributes);
      $properties_get = array_merge($json_api_header, $links, $properties_get_single, $included);
      $properties_get_list = array_merge($json_api_header, $links, $properties_return_list, $relationships, $included);
      $properties_patch = $this->jsonApiFragments->buildPatchPost($this->typeMapper->makeProperties($class->getPatchValidFeatures(), true), $typeName);

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

      $this->addRelationComponents($name, $relation, ($isToMany && !$isToOne), $components);

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

    $paths[$path][$method]["description"] = $this->jsonApiFragments->makeDescription($isRelation, $method, $singleObject);

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
        $primaryKey = array_find(call_user_func($class->getDBAclass() . '::getFeatures'), function (array $feature) { return $feature["pk"] === true; });
        $exampleCursor = "{\"primary\":{\"" . ($primaryKey['alias'] ?? "id") . "\": 123}}";
        $parameters = [
          [
            "name" => "page[after]",
            "in" => "path",
            "schema" => [
              "type" => "string",
              "format" => "byte",
            ],
            "example" => base64_encode($exampleCursor),
            "description" => "Pointer to paginate to retrieve the data after the object provided. Specify the `base64` encoded JSON string in a **uniquely identifiable** manner (e.g. object IDs), i.e. by using one (primary) or two (primary and secondary) fields that allow for **stable** sorting.
            \n\nFormat: `{\"primary\":{\"someField\": 123},\"secondary\":{\"someOtherOptionalField\": \"Foo\"}}`
            \n\nExample: `$exampleCursor` -> `" . (base64_encode($exampleCursor)) . "`"
          ],
          [
            "name" => "page[before]",
            "in" => "path",
            "schema" => [
              "type" => "string",
              "format" => "byte",
            ],
            "example" => base64_encode($exampleCursor),
            "description" => "Pointer to paginate to retrieve the data before the object provided. Specify the `base64` encoded JSON string in a **uniquely identifiable** manner (e.g. object IDs), i.e. by using one (primary) or two (primary and secondary) fields that allow for **stable** sorting.
            \n\nFormat: `{\"primary\":{\"someField\": 123},\"secondary\":{\"someOtherOptionalField\": \"Foo\"}}`
            \n\nExample: `$exampleCursor` -> `" . (base64_encode($exampleCursor)) . "`"
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
            "description" => "Items to include, comma seperated. Possible options: " . implode(", ", $class->getExpandables())
          ]
        ];

        $aggregateFieldsets = $class->getAggregateFieldsets();
        if (!empty($aggregateFieldsets)) {
          $aggregateExamples = [];
          $aggregateDescriptionParts = [];
          foreach ($aggregateFieldsets as $fieldset => $options) {
            if (empty($options)) {
              continue;
            }
            $aggregateExamples["aggregate[" . $fieldset . "]"] = implode(",", array_keys($options));
            $aggregateDescriptionParts[] = $fieldset . ": " . implode(", ", array_keys($options));
          }

          if (!empty($aggregateExamples)) {
            $parameters[] = [
              "name" => "aggregate",
              "in" => "query",
              "style" => "deepObject",
              "explode" => true,
              "schema" => [
                "type" => "object",
                "additionalProperties" => [
                  "type" => "string"
                ]
              ],
              "required" => false,
              "description" => "Aggregated fields to include by type (comma separated values). Possible options: " . implode(" | ", $aggregateDescriptionParts),
              "example" => $aggregateExamples
            ];
          }
        }
      }
      else {
        $parameters = [];
      }
    }
    $paths[$path][$method]["parameters"] = $parameters;
  }

  /**
   * Relation schemas only exist for relationship routes. Other routes of the
   * same model carry no relation name, so nothing must be emitted for them:
   * that produced a nameless "<Model>Relation" schema with a null type const.
   */
  private function addRelationComponents(string $name, ?string $relation, bool $isToMany, array &$components): void {
    if ($relation === null) {
      return;
    }
    $properties = $this->jsonApiFragments->buildPostPatchRelation($relation, $isToMany);

    $components[$name . "Relation" . ucfirst($relation)] =
      [
        "type" => "object",
        "required" => ["data"],
        "properties" => $properties,
      ];

    $components[$name . "Relation" . ucfirst($relation) . "GetResponse"] =
      [
        "type" => "object",
        "required" => ["data"],
        "properties" => $properties
      ];
  }

  private function makeRelationships($class, $uri, $container = null): array {
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

  //TODO expandables array is unnecessarily indexed in the swagger UI
  private function makeExpandables($class, $container): array {
    $properties = [];
    $expandables = array_merge($class->getToOneRelationships(), $class->getToManyRelationships());
    foreach ($expandables as $expand => $expandVal) {
      $expandClass = $expandVal["relationType"];
      $expandApiClass = new ($container->get('classMapper')->get($expandClass))($container);
      $nameParts = explode('\\', get_class($expandApiClass));
      $typeName = lcfirst(substr(end($nameParts), 0, -3));
      $features = array_filter($expandApiClass->getFeaturesWithoutFormfields(), fn($f) => !$f['private']);
      $attrProperties = $this->typeMapper->makeProperties($features, true);
      $requiredAttributes = array_values(array_map(
        fn($f) => $f['alias'],
        array_filter($features, fn($f) => !$f['pk'])
      ));
      $properties[$typeName] = [
        "required" => ["id", "type", "attributes"],
        "properties" => [
          "id" => [
            "type" => "integer"
          ],
          "type" => [
            "type" => "string",
            "const" => $typeName
          ],
          "attributes" => [
            "type" => "object",
            "required" => $requiredAttributes,
            "properties" => $attrProperties
          ]
        ]
      ];
    };
    return array_values($properties);
  }
}
