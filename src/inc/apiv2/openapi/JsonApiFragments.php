<?php

namespace Hashtopolis\inc\apiv2\openapi;

/**
 * Builders for the recurring JSON:API document fragments (jsonapi header,
 * pagination links, write envelopes, canned descriptions).
 */
class JsonApiFragments {
  /** Media type JSON:API mandates for its documents, used for every payload the APIv2 speaks. */
  public const MEDIA_TYPE = "application/vnd.api+json";

  /**
   * Media type of the error documents. Every APIv2 error is rendered by
   * ErrorHandler::errorResponse as an RFC 7807 problem document, not as a
   * JSON:API error document.
   */
  public const PROBLEM_MEDIA_TYPE = "application/problem+json";

  /**
   * The id of a resource object or resource identifier object. JSON:API requires
   * it to be a string, but AbstractBaseAPI::obj2Resource answers the integer
   * primary key of the model, so that is what the spec describes. The string
   * form is documented once the runtime serializes it as one.
   */
  public function resourceIdSchema(): array {
    return [
      "type" => "integer",
      "example" => 1
    ];
  }

  /**
   * A response carrying the JSON:API document described by $schemaRef.
   */
  public function jsonApiResponse(string $description, string $schemaRef): array {
    return [
      "description" => $description,
      "content" => [
        self::MEDIA_TYPE => [
          "schema" => ['$ref' => $schemaRef]
        ]
      ]
    ];
  }

  /**
   * A required request body carrying the JSON:API document described by $schemaRef.
   */
  public function jsonApiRequestBody(string $schemaRef): array {
    return [
      "required" => true,
      "content" => [
        self::MEDIA_TYPE => [
          "schema" => ['$ref' => $schemaRef]
        ]
      ]
    ];
  }

  /**
   * A single RFC 7807 problem response.
   */
  public function problemResponse(string $description): array {
    return [
      "description" => $description,
      "content" => [
        self::PROBLEM_MEDIA_TYPE => [
          "schema" => ['$ref' => "#/components/schemas/ErrorResponse"]
        ]
      ]
    ];
  }

  /**
   * The errors every authenticated APIv2 route can answer with: 400 on a
   * malformed request, 401 without a usable token and 403 when the token lacks
   * the permission the route requires.
   */
  public function commonErrorResponses(): array {
    return [
      "400" => $this->problemResponse("Invalid request"),
      "401" => $this->problemResponse("Authentication failed"),
      "403" => $this->problemResponse("Permission denied")
    ];
  }

  // "jsonapi": {
  //   "version": "1.1",
  //   "ext": [
  //       "https://jsonapi.org/profiles/ethanresnick/cursor-pagination"
  //   ]
  // },
  public function makeJsonApiHeader(): array {
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
  public function makeLinks($uri): array {
    $self = $uri . "?page[size]=25";
    $cursor = "eyJwcmltYXJ5Ijp7InNvbWVVbnFpdWVGaWVsZCI6MTIzfSwic2Vjb25kYXJ5Ijp7InNvbWVPdGhlck9wdGlvbmFsRmllbGQiOiJGb28ifX0=";
    return ["links" => [
      "type" => "object",
      "required" => ["self", "first", "last", "next", "prev"],
      "properties" => [
        "self" => [
          "type" => "string",
          "default" => $self
        ],
        "first" => [
          "type" => "string",
          "default" => $self
        ],
        "last" => [
          "type" => ["string", "null"],
          "default" => $self . "&page[before]=" . $cursor
        ],
        "next" => [
          "type" => ["string", "null"],
          "default" => $self . "&page[after]=" . $cursor
        ],
        "prev" => [
          "type" => ["string", "null"],
          "default" => $self . "&page[before]=" . $cursor
        ]
      ]
    ]
    ];
  }

  /**
   * The links member of a single resource document, which carries the self link
   * of the request and nothing else (see AbstractBaseAPI::getOneResource).
   */
  public function makeSelfLink(string $uri): array {
    return ["links" => [
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
  }

  /**
   * The meta member of a collection document: the total number of elements the
   * filtered collection holds.
   */
  public function makeListMeta(): array {
    return ["meta" => [
      "type" => "object",
      "required" => ["page"],
      "properties" => [
        "page" => [
          "type" => "object",
          "required" => ["total_elements"],
          "properties" => [
            "total_elements" => [
              "type" => "integer"
            ]
          ]
        ]
      ]
    ]
    ];
  }

  /**
   * The document the /count route answers with: the number of matching objects
   * under meta, with an empty data member (see AbstractModelAPI::count).
   */
  public function buildCountResponse(): array {
    return [
      "type" => "object",
      "required" => ["jsonapi", "meta", "data"],
      "properties" => array_merge(
        $this->makeJsonApiHeader(),
        [
          "meta" => [
            "type" => "object",
            "required" => ["count"],
            "properties" => [
              "count" => [
                "type" => "integer",
                "description" => "Number of objects matching the given filters"
              ],
              "total_count" => [
                "type" => "integer",
                "description" => "Number of objects without any filter applied, only present when `include_total=true` was requested"
              ]
            ]
          ],
          "data" => [
            "type" => "array",
            "items" => [
              "type" => "object"
            ],
            "maxItems" => 0,
            "description" => "Always empty: the count is reported under meta."
          ]
        ]
      )
    ];
  }

  public function buildPatchPost($properties, $name, $id = null, $requiredAttributes = null): array {
    $required = ["type", "attributes"];
    if ($id) {
      $required[] = "id";
    }
    $attributesSchema = [
      "type" => "object",
      "properties" => $properties
    ];
    if ($requiredAttributes !== null && count($requiredAttributes) > 0) {
      $attributesSchema["required"] = $requiredAttributes;
    }
    $result = ["data" => [
      "type" => "object",
      "required" => $required,
      "properties" => [
        "type" => [
          "type" => "string",
          "const" => $name
        ],
        "attributes" => $attributesSchema
      ]
    ]
    ];

    if ($id) {
      $result["data"]["properties"]["id"] = $this->resourceIdSchema();
    }
    return $result;
  }

  /**
   * The document a helper answers with when its action returns an array instead
   * of an object: AbstractBaseAPI::getMetaResponse puts that array under "meta"
   * verbatim and leaves data empty. The schema of the array is taken as given,
   * because a helper may answer with a map as well as with a list.
   */
  public function buildMetaResponse(array $metaSchema): array {
    return [
      "type" => "object",
      "required" => ["jsonapi", "meta", "data"],
      "properties" => array_merge(
        $this->makeJsonApiHeader(),
        [
          "meta" => $metaSchema,
          "data" => [
            "type" => "array",
            "items" => [
              "type" => "object"
            ],
            "maxItems" => 0,
            "description" => "Always empty: a helper answers with meta only."
          ]
        ]
      )
    ];
  }

  /**
   * The write envelope of the collection level patch and delete routes. Unlike
   * the single object routes these carry a list of resource records as data,
   * each identified by its own id. Attributes are only part of a patch, a
   * delete identifies the objects to remove and nothing else.
   */
  public function buildMultipleWriteEnvelope(string $name, ?array $properties = null): array {
    $required = ["id", "type"];
    $recordProperties = [
      "id" => $this->resourceIdSchema(),
      "type" => [
        "type" => "string",
        "const" => $name
      ]
    ];
    if ($properties !== null) {
      $required[] = "attributes";
      $recordProperties["attributes"] = [
        "type" => "object",
        "properties" => $properties
      ];
    }

    return ["data" => [
      "type" => "array",
      "items" => [
        "type" => "object",
        "required" => $required,
        "properties" => $recordProperties
      ]
    ]
    ];
  }

  /**
   * This function builds the post/patch attributes for a relationship. When $istomany is false,
   * it would build the attributes for a to one relationship. If it is true it will build it for a too many relationship.
   * */
  public function buildPostPatchRelation($name, $isToMany): array {
    $resourceRecord = [
      "type" => "object",
      "required" => ["type", "id"],
      "properties" => [
        "type" => [
          "type" => "string",
          "const" => $name
        ],
        "id" => $this->resourceIdSchema()
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

  public function makeDescription($isRelation, $method, $singleObject): string {
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
}
