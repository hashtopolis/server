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
      $aggregateFeatures = $class->getAggregateFeatures();
      $aggregateAttributeProperties = $this->typeMapper->makeProperties($aggregateFeatures, true);
      $allResponseProperties = array_merge($responseAttributeProperties, $aggregateAttributeProperties);
      $attributesOverride = $class->getOpenAPIAttributesSchemaOverride();
      if ($attributesOverride !== null) {
        $attributesSchema = $this->addAggregateProperties($attributesOverride, $aggregateAttributeProperties);
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
       * same single resource document (AbstractBaseAPI::getOneResource). The
       * document is described once and the other two names refer to it, so the
       * three stay identical by construction instead of by three copies that
       * have to be kept in step.
       */
      $components[$name . "Response"] = [
        "type" => "object",
        "required" => ["jsonapi", "links", "data"],
        "properties" => $properties_get_single
      ];
      $singleDocumentRef = ['$ref' => "#/components/schemas/" . $name . "Response"];

      $this->addRelationComponents($name, $relation, ($isToMany && !$isToOne), $components);

      $components[$name . "SingleResponse"] = $singleDocumentRef;

      $components[$name . "PostPatchResponse"] = $singleDocumentRef;

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
      /**
       * OpenAPI only allows scopes on an OAuth2 scheme, so the list of a bearer
       * requirement has to stay empty. The permissions the call needs are stated
       * next to it instead, where they document the endpoint without making the
       * security requirement invalid.
       */
      "security" => [
        [
          "bearerAuth" => []
        ]
      ],
      "x-required-permissions" => array_values(array_unique($required_scopes))
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

    /**
     * Sparse fieldsets and sorting address the attributes of the resource the
     * operation returns, so both are derived from the response features.
     *
     * A relationship route answers with the related resource rather than with
     * this one (AbstractModelAPI::getRelationship), so its attribute names are
     * not the ones below and it is left out, the same way "include" is.
     */
    $isRelationRoute = str_contains($pattern, "relation:");
    $publicFeatures = array_filter($class->getFeaturesWithoutFormfields(), fn($f) => !$f['private']);
    $attributeNames = array_values(array_map(
      fn($f) => $f['alias'],
      array_filter($publicFeatures, fn($f) => !$f['pk'])
    ));

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

      if (!$isRelationRoute) {
        $parameters[] = $this->makeIncludeParameter($class);
        $parameters = array_merge($parameters, $this->makeResourceShapeParameters($class, $typeName, $attributeNames, $container));
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
            "in" => "query",
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
            "in" => "query",
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
            "in" => "query",
            "schema" => [
              "type" => "integer",
              "format" => "int32"
            ],
            "example" => 100,
            "description" => "Amout of data to retrieve inside a single page"
          ],
          $this->makeFilterParameter($primaryKey),
          $this->makeIncludeParameter($class),
          $this->makeSortParameter($this->sortableAttributeNames($attributeNames))
        ];

        $parameters = array_merge($parameters, $this->makeResourceShapeParameters($class, $typeName, $attributeNames, $container));
      }
      elseif (($method == 'post' || ($method == 'patch' && $singleObject)) && !$isRelationRoute) {
        /**
         * Creating one object and updating one answer with the resource they
         * wrote, shaped like any other read. A collection PATCH updates several
         * objects and answers 204 (AbstractModelAPI::patchMultiple), so there is
         * no resource for the two parameters to shape.
         */
        $parameters = $this->makeResourceShapeParameters($class, $typeName, $attributeNames, $container);
      }
      else {
        $parameters = [];
      }
    }
    $paths[$path][$method]["parameters"] = $parameters;
  }

  /**
   * Add the aggregate properties to an attributes schema supplied by
   * getOpenAPIAttributesSchemaOverride(). The override replaces the
   * feature-derived attributes, but aggregateData() still appends its fields to
   * whatever the override describes, so they have to be part of it.
   *
   * An override that offers a choice of shapes carries the aggregates in every
   * branch, since any of them can be returned with an aggregate requested.
   *
   * @param array<string, mixed> $schema
   * @param array<string, mixed> $aggregateProperties
   * @return array<string, mixed>
   */
  private function addAggregateProperties(array $schema, array $aggregateProperties): array {
    if (count($aggregateProperties) === 0) {
      return $schema;
    }
    if (array_key_exists("oneOf", $schema)) {
      $schema["oneOf"] = array_map(
        fn(array $branch) => $this->addAggregateProperties($branch, $aggregateProperties),
        $schema["oneOf"]
      );
      return $schema;
    }
    /* Aggregates are only produced on request, so they are never required */
    $schema["properties"] = array_merge($schema["properties"] ?? [], $aggregateProperties);
    return $schema;
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
   * The query parameters that shape the resource an operation returns rather
   * than selecting which resources it returns. Every route answering with a
   * resource honours them, whether it reads, creates or updates one
   * (AbstractBaseAPI::getOneResource, AbstractModelAPI::getManyResources).
   *
   * @param list<string> $attributeNames
   * @return list<array<string, mixed>>
   */
  private function makeResourceShapeParameters($class, string $typeName, array $attributeNames, ContainerInterface $container): array {
    $parameters = [$this->makeSparseFieldsetsParameter(
      $typeName,
      $attributeNames,
      $this->includableTypeNames($class, $container)
    )];
    $aggregateParameter = $this->makeAggregateParameter($class);
    if ($aggregateParameter !== null) {
      $parameters[] = $aggregateParameter;
    }
    return $parameters;
  }

  /**
   * The resource types reachable through "include", which are the types a
   * sparse fieldset can address next to the primary one.
   *
   * @return list<string>
   */
  private function includableTypeNames($class, ContainerInterface $container): array {
    $typeNames = [];
    $relationships = array_merge($class->getToOneRelationships(), $class->getToManyRelationships());
    foreach ($relationships as $relationship) {
      $apiClass = $container->get('classMapper')->get($relationship["relationType"]);
      $nameParts = explode('\\', $apiClass);
      $typeNames[] = lcfirst(substr(end($nameParts), 0, -3));
    }
    return array_values(array_unique($typeNames));
  }

  /**
   * The attributes accepted as a sort key. The primary key is addressed as
   * "id", and an attribute carrying a digit is left out because the sort
   * parameter is parsed as [_a-zA-Z.]+ (AbstractBaseAPI::makeOrderFilterTemplates).
   *
   * @param list<string> $attributeNames
   * @return list<string>
   */
  private function sortableAttributeNames(array $attributeNames): array {
    return array_merge(
      ["id"],
      array_values(array_filter($attributeNames, fn(string $name) => preg_match('/\d/', $name) !== 1))
    );
  }

  /**
   * The "aggregate" query parameter, offering the computed fields of
   * getAggregateFieldsets() by resource key. Returns null for a model that
   * offers none.
   */
  private function makeAggregateParameter($class): ?array {
    $examples = [];
    $descriptionParts = [];
    foreach ($class->getAggregateFieldsets() as $fieldset => $options) {
      if (empty($options)) {
        continue;
      }
      $examples["aggregate[" . $fieldset . "]"] = implode(",", array_keys($options));
      $descriptionParts[] = $fieldset . ": " . implode(", ", array_keys($options));
    }
    if (empty($examples)) {
      return null;
    }
    return [
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
      "description" => "Aggregated fields to include by type (comma separated values). Possible options: " . implode(" | ", $descriptionParts),
      "example" => $examples
    ];
  }

  /**
   * The JSON:API "fields" query parameter (sparse fieldsets). Keys are resource
   * types, values the comma separated attributes to keep. The server applies it
   * to the primary data and to every included resource alike, keyed by the type
   * of the resource in question (AbstractBaseAPI::obj2Resource).
   *
   * @param list<string> $attributeNames attributes of the primary resource type
   * @param list<string> $includableTypes types reachable through "include"
   */
  private function makeSparseFieldsetsParameter(string $typeName, array $attributeNames, array $includableTypes): array {
    $example = implode(",", array_slice($attributeNames, 0, 2));
    $description = "Attributes to return per resource type, comma separated, e.g. `fields[$typeName]=$example`."
      . " Applies to the primary data and to included resources alike."
      . " A type that is not named is returned in full."
      . "\n\nAttributes of `$typeName`: " . implode(", ", $attributeNames) . "."
      . "\n\n**Note:** a resource whose attributes are narrowed this way no longer carries every attribute"
      . " listed as required in the response schema.";
    if (count($includableTypes) > 0) {
      $description .= "\n\nTypes reachable through `include`: " . implode(", ", $includableTypes) . ".";
    }
    return [
      "name" => "fields",
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
      "description" => $description,
      "example" => [$typeName => $example]
    ];
  }

  /**
   * The JSON:API "sort" query parameter. Read as a single comma separated value
   * like "include", each entry optionally prefixed with "-" for descending
   * order (AbstractBaseAPI::makeOrderFilterTemplates). The primary key is
   * appended as a tie-breaker, so the order stays stable for pagination.
   *
   * @param list<string> $sortable attribute names accepted as a sort key
   */
  private function makeSortParameter(array $sortable): array {
    /* Prefer an attribute over the primary key, which is the tie-breaker anyway */
    $exampleKey = $sortable[1] ?? $sortable[0];
    return [
      "name" => "sort",
      "in" => "query",
      "style" => "form",
      "explode" => false,
      "schema" => [
        "type" => "array",
        "items" => [
          "type" => "string"
        ]
      ],
      "required" => false,
      "description" => "Attributes to sort by, comma separated, each optionally prefixed with `-` to sort descending,"
        . " e.g. `sort=-" . $exampleKey . "`. `id` addresses the primary key of the resource, and an attribute of an"
        . " included resource can be addressed as `<relationship>.<attribute>`. The primary key is always appended as a"
        . " tie-breaker, so that pagination over the result stays stable."
        . "\n\nAccepted keys: " . implode(", ", $sortable) . ".",
      "example" => ["-" . $exampleKey]
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

  private function makeRelationships($class, $uri, $container): array {
    $toOneRelationships = $class->getToOneRelationships();
    $toManyRelationships = $class->getToManyRelationships();

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
