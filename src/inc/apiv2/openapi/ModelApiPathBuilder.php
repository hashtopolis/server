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
    /* The spec is keyed by the path template, while the placeholder
       constraints of the raw pattern still say what kind of route this is */
    $path = $target->pathTemplate;
    $pattern = $target->pattern;
    $method = $target->httpMethod;
    $class = $api;

    /* Quick to find out if single parameter object is used */
    $singleObject = ((strstr($pattern, '/{id:')) !== false);
    $isCount = str_ends_with($path, '/count');
    $api_name_parts = explode('\\', get_class($class));
    $name = substr(end($api_name_parts), 0, -3); // Remove "API" suffix
    $typeName = lcfirst($name);
    $uri = $class->getBaseUri();

    $isRelation = (strstr($path, "/relationships/")) !== false;
    if (str_contains($pattern, "relation:")) {
      $relation = rtrim(explode("relation:", $pattern)[1], "}");
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
      $attributesSchema = [
        "type" => "object",
        "required" => array_values(array_map(
          fn($f) => $f['alias'],
          array_filter($responseFeatures, fn($f) => !$f['pk'])
        )),
        "properties" => $responseAttributeProperties
      ];
      /**
       * The resource object as AbstractBaseAPI::obj2Resource builds it: the
       * primary key becomes the id, the remaining features the attributes, and
       * both the self link and the relationships are part of the resource
       * object itself rather than of the document around it.
       */
      $resourceObjectRequired = ["id", "type", "attributes", "links"];
      $resourceObjectProperties = [
        "id" => $this->jsonApiFragments->resourceIdSchema(),
        "type" => [
          "type" => "string",
          "const" => $typeName
        ],
        "attributes" => $attributesSchema,
        "links" => [
          "type" => "object",
          "required" => ["self"],
          "properties" => [
            "self" => [
              "type" => "string",
              "default" => $uri . "/1"
            ]
          ]
        ]
      ];

      $relationshipProperties = $this->makeRelationships($class, $uri, $container);
      if (count($relationshipProperties) > 0) {
        $resourceObjectRequired[] = "relationships";
        $resourceObjectProperties["relationships"] = [
          "type" => "object",
          "required" => array_keys($relationshipProperties),
          "properties" => $relationshipProperties
        ];
      }

      $resourceObject = [
        "type" => "object",
        "required" => $resourceObjectRequired,
        "properties" => $resourceObjectProperties
      ];

      $expandables = $this->makeExpandables($class, $container);
      /**
       * A model without relationships has nothing to include, so it must not
       * carry an "included" member: an empty oneOf is not a valid schema.
       */
      $included = [];
      if (count($expandables) > 0) {
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
      }

      $json_api_header = $this->jsonApiFragments->makeJsonApiHeader();

      /**
       * A single resource document carries only the self link, a collection
       * document the full set of cursor pagination links plus the element count
       * (see AbstractBaseAPI::getOneResource and AbstractModelAPI::get).
       */
      $properties_get_single = array_merge(
        $json_api_header,
        $this->jsonApiFragments->makeSelfLink($uri),
        ["data" => $resourceObject],
        $included
      );
      $properties_get_list = array_merge(
        $json_api_header,
        $this->jsonApiFragments->makeLinks($uri),
        $this->jsonApiFragments->makeListMeta(),
        ["data" => [
          "type" => "array",
          "items" => $resourceObject
        ]
        ],
        $included
      );

      $createFeatures = $class->getAllPostParameters($class->getCreateValidFeatures());
      $requiredCreateAttributes = array_values(array_map(
        fn($f) => $f['alias'],
        array_filter($createFeatures, fn($f) => !$f['null'])
      ));
      $properties_create = $this->jsonApiFragments->buildPatchPost($this->typeMapper->makeProperties($createFeatures), $typeName, null, $requiredCreateAttributes);
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

      $components[$name . "PatchMultiple"] =
        [
          "type" => "object",
          "required" => ["data"],
          "properties" => $this->jsonApiFragments->buildMultipleWriteEnvelope(
            $typeName,
            $this->typeMapper->makeProperties($class->getPatchValidFeatures(), true)
          ),
        ];

      $components[$name . "DeleteMultiple"] =
        [
          "type" => "object",
          "required" => ["data"],
          "properties" => $this->jsonApiFragments->buildMultipleWriteEnvelope($typeName),
        ];

      /**
       * Reading one object, creating one and updating one all answer with the
       * same single resource document (AbstractBaseAPI::getOneResource), so all
       * three schemas share one shape.
       */
      $singleDocument = [
        "type" => "object",
        "required" => ["jsonapi", "links", "data"],
        "properties" => $properties_get_single
      ];

      $components[$name . "Response"] = $singleDocument;

      $this->addRelationComponents($name, $relation, ($isToMany && !$isToOne), $components);

      $components[$name . "SingleResponse"] = $singleDocument;

      $components[$name . "PostPatchResponse"] = $singleDocument;

      $components[$name . "ListResponse"] =
        [
          "type" => "object",
          "required" => ["jsonapi", "links", "meta", "data"],
          "properties" => $properties_get_list,
        ];

      $components[$name . "CountResponse"] = $this->jsonApiFragments->buildCountResponse();
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
      "responses" => $this->jsonApiFragments->commonErrorResponses(),
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
      $paths[$path][$method]["responses"]["404"] = $this->jsonApiFragments->problemResponse("Not Found");

      /* Method specific responses and requests for single objects */
      if ($method == 'get') {
        if (!$isRelation && str_contains($pattern, "relation:")) {
          $paths[$path][$method]["responses"]["200"] = $this->jsonApiFragments->jsonApiResponse(
            "successful operation",
            "#/components/schemas/" . $name . "Relation" . ucfirst($relation) . "GetResponse"
          );
        }
        else {
          $paths[$path][$method]["responses"]["200"] = $this->jsonApiFragments->jsonApiResponse(
            "successful operation",
            "#/components/schemas/" . $name . "Response"
          );
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
        /* A rename can collide with an existing object */
        $paths[$path][$method]["responses"]["409"] = $this->jsonApiFragments->problemResponse("Resource already exists");

        if ($isRelation) {
          $paths[$path][$method]["requestBody"] = $this->jsonApiFragments->jsonApiRequestBody(
            "#/components/schemas/" . $name . "Relation" . ucfirst($relation)
          );
        }
        else {
          $paths[$path][$method]["requestBody"] = $this->jsonApiFragments->jsonApiRequestBody(
            "#/components/schemas/" . $name . "Patch"
          );

          $paths[$path][$method]["responses"]["200"] = $this->jsonApiFragments->jsonApiResponse(
            "successful operation",
            "#/components/schemas/" . $name . "PostPatchResponse"
          );
        }
      }
      elseif ($method == 'delete') {
        $paths[$path][$method]["responses"]["204"] = [
          "description" => "successfully deleted",
        ];

        if ($isRelation) {
          $paths[$path][$method]["requestBody"] = $this->jsonApiFragments->jsonApiRequestBody(
            "#/components/schemas/" . $name . "Relation" . ucfirst($relation)
          );
        }
        /* deleteOne identifies the object by its path id and reads no body */
      }
      elseif ($method == 'post') {
        $paths[$path][$method]["responses"]["204"] = [
          "description" => "successfully created",
        ];
        /* Linking a relation that already exists is a conflict */
        $paths[$path][$method]["responses"]["409"] = $this->jsonApiFragments->problemResponse("Resource already exists");

        /* The resource identifiers to link are sent as data */
        $paths[$path][$method]["requestBody"] = $this->jsonApiFragments->jsonApiRequestBody(
          "#/components/schemas/" . $name . "Relation" . ucfirst($relation)
        );
      }
      else {
        throw new HttpErrorException("Method '$method' not implemented");
      }
    }
    else {
      /* Model API entry point */
      if ($method == 'get') {
        /* The /count route reports the number of matches under meta, it returns no objects */
        $paths[$path][$method]["responses"]["200"] = $isCount
          ? $this->jsonApiFragments->jsonApiResponse(
              "successful operation",
              "#/components/schemas/" . $name . "CountResponse"
            )
          : $this->jsonApiFragments->jsonApiResponse(
              "successful operation",
              "#/components/schemas/" . $name . "ListResponse"
            );

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
        $paths[$path][$method]["responses"]["201"] = $this->jsonApiFragments->jsonApiResponse(
          "successful operation",
          "#/components/schemas/" . $name . "PostPatchResponse"
        );
        /* Creating an object whose unique attributes are taken is a conflict */
        $paths[$path][$method]["responses"]["409"] = $this->jsonApiFragments->problemResponse("Resource already exists");

        if ($isRelation) {
          $paths[$path][$method]["requestBody"] = $this->jsonApiFragments->jsonApiRequestBody(
            "#/components/schemas/" . $name . "Relation" . ucfirst($relation)
          );
        }
        else {
          $paths[$path][$method]["requestBody"] = $this->jsonApiFragments->jsonApiRequestBody(
            "#/components/schemas/" . $name . "Create"
          );
        }

      }
      elseif ($method == 'patch') {
        /**
         * patchMultiple: the resource records to update are sent as data, the
         * updated objects are not returned (see AbstractModelAPI::patchMultiple).
         */
        $paths[$path][$method]["responses"]["204"] = [
          "description" => "successfully updated",
        ];
        $paths[$path][$method]["responses"]["404"] = $this->jsonApiFragments->problemResponse("Not Found");
        $paths[$path][$method]["responses"]["409"] = $this->jsonApiFragments->problemResponse("Resource already exists");
        $paths[$path][$method]["requestBody"] = $this->jsonApiFragments->jsonApiRequestBody(
          "#/components/schemas/" . $name . "PatchMultiple"
        );
      }
      elseif ($method == 'delete') {
        /**
         * deleteMultiple: the resource identifiers to delete are sent as data
         * (see AbstractModelAPI::deleteMultiple).
         */
        $paths[$path][$method]["responses"]["204"] = [
          "description" => "successfully deleted",
        ];
        $paths[$path][$method]["responses"]["404"] = $this->jsonApiFragments->problemResponse("Not Found");
        $paths[$path][$method]["requestBody"] = $this->jsonApiFragments->jsonApiRequestBody(
          "#/components/schemas/" . $name . "DeleteMultiple"
        );
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

      if (!str_contains($pattern, "relation:")) {
        $parameters[] = $this->makeIncludeParameter($class);
      };
    }
    else {
      if ($method == 'get') {
        $primaryKey = array_find(call_user_func($class->getDBAclass() . '::getFeatures'), function (array $feature) { return $feature["pk"] === true; });
        $exampleCursor = "{\"primary\":{\"" . ($primaryKey['alias'] ?? "id") . "\": 123}}";
        /**
         * The /count route counts the objects matching the filters, so it takes
         * the filters but neither pagination nor include (AbstractModelAPI::count).
         */
        if ($isCount) {
          $paths[$path][$method]["parameters"] = [
            $this->makeFilterParameter($primaryKey),
            [
              "name" => "include_total",
              "in" => "query",
              "schema" => [
                "type" => "boolean"
              ],
              "example" => true,
              "description" => "Also report the number of objects without any filter applied, as `meta.total_count`"
            ]
          ];
          return;
        }
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
          $this->makeFilterParameter($primaryKey),
          $this->makeIncludeParameter($class)
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
   * The "filter" query parameter, a deep object whose keys are attribute names
   * optionally suffixed with a comparison operator.
   */
  private function makeFilterParameter(?array $primaryKey): array {
    $exampleKey = ($primaryKey['alias'] ?? "id") . "__gt";
    return [
      "name" => "filter",
      "in" => "query",
      "style" => "deepObject",
      "explode" => true,
      "schema" => [
        "type" => "object",
        "additionalProperties" => [
          "type" => "string"
        ]
      ],
      "description" => "Filters results using a query. Every key is an attribute name optionally suffixed with a comparison operator, e.g. `filter[" . $exampleKey . "]=200`.",
      "example" => [$exampleKey => "200"]
    ];
  }

  /**
   * The JSON:API "include" query parameter. The API reads it as a single comma
   * separated value, which is exactly how "style: form, explode: false"
   * serializes a string array: `?include=a,b`.
   */
  private function makeIncludeParameter($class): array {
    $expandables = $class->getExpandables();
    $parameter = [
      "name" => "include",
      "in" => "query",
      "style" => "form",
      "explode" => false,
      "schema" => [
        "type" => "array",
        "items" => [
          "type" => "string"
        ]
      ],
      "description" => "Relationships to include in the response, comma seperated. Possible options: " . implode(", ", $expandables)
    ];
    if (count($expandables) > 0) {
      $parameter["schema"]["items"]["enum"] = array_values($expandables);
      $parameter["example"] = array_slice(array_values($expandables), 0, 2);
    }
    return $parameter;
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
          "id" => $this->jsonApiFragments->resourceIdSchema()
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
          "id" => $this->jsonApiFragments->resourceIdSchema(),
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
