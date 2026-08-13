<?php

namespace Hashtopolis\inc\apiv2\openapi;

use Hashtopolis\inc\apiv2\common\AbstractModelAPI;

/**
 * Builders for the recurring JSON:API document fragments (jsonapi header,
 * pagination links, write envelopes, canned descriptions).
 */
class JsonApiFragments {
  /** Media type JSON:API mandates for its documents, used for every payload the APIv2 speaks. */
  public const MEDIA_TYPE = "application/vnd.api+json";

  /**
   * Media type of the atomic operations extension: the same JSON:API media type
   * with the extension named in its "ext" parameter, as the extension requires.
   * Taken from the endpoint implementing it, so spec and server cannot drift.
   */
  public const ATOMIC_MEDIA_TYPE = AbstractModelAPI::ATOMIC_MEDIA_TYPE;

  /** URI identifying the atomic operations extension. */
  public const ATOMIC_EXT_URI = AbstractModelAPI::ATOMIC_EXT_URI;

  /**
   * URI of the cursor pagination profile the APIv2 follows. It is a profile and
   * not an extension: it adds no document member, it only gives meaning to the
   * page[after]/page[before] query parameters JSON:API already allows.
   */
  public const CURSOR_PAGINATION_PROFILE = "https://jsonapi.org/profiles/ethanresnick/cursor-pagination";

  /**
   * The id of a resource object or resource identifier object. JSON:API requires
   * it to be a string, so AbstractBaseAPI::obj2Resource casts the integer
   * primary key of the model on the way out.
   */
  public function resourceIdSchema(): array {
    return [
      "type" => "string",
      "pattern" => '^[0-9]+$',
      "example" => "1"
    ];
  }

  /**
   * A response carrying the JSON:API document described by $schemaRef. Documents
   * of an extension are served under the media type of that extension.
   */
  public function jsonApiResponse(string $description, string $schemaRef, string $mediaType = self::MEDIA_TYPE): array {
    return [
      "description" => $description,
      "content" => [
        $mediaType => [
          "schema" => ['$ref' => $schemaRef]
        ]
      ]
    ];
  }

  /**
   * A required request body carrying the JSON:API document described by $schemaRef.
   */
  public function jsonApiRequestBody(string $schemaRef, string $mediaType = self::MEDIA_TYPE): array {
    return [
      "required" => true,
      "content" => [
        $mediaType => [
          "schema" => ['$ref' => $schemaRef]
        ]
      ]
    ];
  }

  /**
   * An error response: a JSON:API error document, as ErrorHandler::errorResponse
   * renders every error of the APIv2.
   */
  public function errorResponse(string $description): array {
    return [
      "description" => $description,
      "content" => [
        self::MEDIA_TYPE => [
          "schema" => ['$ref' => "#/components/schemas/ErrorResponse"]
        ]
      ]
    ];
  }

  /**
   * The errors every authenticated APIv2 route can answer with: 400 on a
   * malformed request, 401 without a usable token, 403 when the token lacks the
   * permission the route requires and 406 when the Accept header asks for the
   * JSON:API media type in a way the server cannot serve
   * (ContentNegotiationMiddleware).
   */
  public function commonErrorResponses(): array {
    return [
      "400" => $this->errorResponse("Invalid request"),
      "401" => $this->errorResponse("Authentication failed"),
      "403" => $this->errorResponse("Permission denied"),
      "406" => $this->errorResponse(
        "The Accept header only asks for instances of `" . self::MEDIA_TYPE . "` that cannot be served,"
        . " because they carry a media type parameter other than `ext` and `profile` or name an unsupported extension"
      )
    ];
  }

  /**
   * The answer to a request body sent as the JSON:API media type modified with a
   * media type parameter the specification does not define, or naming an
   * extension the server does not implement (ContentNegotiationMiddleware).
   */
  public function unsupportedMediaTypeResponse(): array {
    return $this->errorResponse(
      "The Content-Type is `" . self::MEDIA_TYPE . "` with a media type parameter other than `ext` and `profile`,"
      . " or names an unsupported extension"
    );
  }

  /**
   * The jsonapi member of a document, as AbstractBaseAPI::createJsonResponse
   * builds it:
   *
   * "jsonapi": {
   *   "version": "1.1",
   *   "profile": ["https://jsonapi.org/profiles/ethanresnick/cursor-pagination"]
   * }
   *
   * JSON:API 1.1 keeps the URIs of applied extensions in "ext" and those of
   * applied profiles in "profile", so a document of the atomic operations
   * extension reports that extension while an ordinary one reports the cursor
   * pagination profile.
   *
   * @param list<string> $profile URIs of the profiles the document applies
   * @param list<string> $ext URIs of the extensions the document applies
   */
  public function makeJsonApiHeader(array $profile = [self::CURSOR_PAGINATION_PROFILE], array $ext = []): array {
    $properties = [
      "version" => [
        "type" => "string",
        "default" => "1.1"
      ]
    ];
    foreach (["ext" => $ext, "profile" => $profile] as $member => $uris) {
      if (count($uris) === 0) {
        continue;
      }
      $properties[$member] = [
        "type" => "array",
        "items" => ["type" => "string"],
        "default" => $uris
      ];
    }

    return ["jsonapi" => [
      "type" => "object",
      "required" => ["version"],
      "properties" => $properties
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
   * The document a helper answers with when its action returns a map instead of
   * an object: AbstractBaseAPI::getMetaResponse puts that map under "meta" and
   * leaves data empty.
   */
  public function buildMetaResponse(array $metaProperties): array {
    return [
      "type" => "object",
      "required" => ["jsonapi", "meta", "data"],
      "properties" => array_merge(
        $this->makeJsonApiHeader(),
        [
          "meta" => [
            "type" => "object",
            "properties" => $metaProperties
          ],
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
   * The request document of the atomic operations extension
   * (https://jsonapi.org/ext/atomic/), as AbstractModelAPI::atomicOperations
   * reads it: a non-empty list of operations, each addressing one object of this
   * collection. Only the operations the collection supports are described, an
   * unsupported one is rejected with 403.
   *
   * @param string $typeName resource type every operation must address
   * @param ?array $createProperties attributes an "add" accepts, null when the
   *   collection cannot be created in
   * @param array $requiredCreateAttributes attributes an "add" must carry
   * @param ?array $patchProperties attributes an "update" accepts, null when the
   *   collection cannot be updated
   * @param bool $allowRemove whether objects of the collection can be removed
   */
  public function buildAtomicOperationsRequest(
    string $typeName,
    ?array $createProperties,
    array $requiredCreateAttributes,
    ?array $patchProperties,
    bool $allowRemove
  ): array {
    $operations = [];
    if ($createProperties !== null) {
      $operations[] = $this->buildAtomicWriteOperation("add", $typeName, $createProperties, $requiredCreateAttributes, false);
    }
    if ($patchProperties !== null) {
      $operations[] = $this->buildAtomicWriteOperation("update", $typeName, $patchProperties, [], true);
    }
    if ($allowRemove) {
      $operations[] = [
        "type" => "object",
        "description" => "Deletes the object named by `ref`, the same modification as a `DELETE` on that object.",
        "required" => ["op", "ref"],
        "properties" => [
          "op" => [
            "type" => "string",
            "const" => "remove"
          ],
          "ref" => [
            "type" => "object",
            "required" => ["type", "id"],
            "properties" => [
              "type" => [
                "type" => "string",
                "const" => $typeName
              ],
              "id" => $this->resourceIdSchema()
            ]
          ]
        ]
      ];
    }

    return [
      "type" => "object",
      "required" => ["atomic:operations"],
      "properties" => [
        "atomic:operations" => [
          "type" => "array",
          "minItems" => 1,
          "description" => "The operations to apply, in the order they are applied. Either all of them take effect or none does.",
          "items" => (count($operations) === 1) ? $operations[0] : ["oneOf" => $operations]
        ]
      ]
    ];
  }

  /**
   * An "add" or "update" operation: both carry the object they write as a
   * resource object, an "update" identifies it by its id.
   */
  private function buildAtomicWriteOperation(string $op, string $typeName, array $properties, array $requiredAttributes, bool $needsId): array {
    $attributesSchema = [
      "type" => "object",
      "properties" => $properties
    ];
    if (count($requiredAttributes) > 0) {
      $attributesSchema["required"] = $requiredAttributes;
    }

    $dataRequired = ["type", "attributes"];
    $dataProperties = [
      "type" => [
        "type" => "string",
        "const" => $typeName
      ],
      "attributes" => $attributesSchema
    ];
    if ($needsId) {
      $dataRequired = ["id", "type", "attributes"];
      $dataProperties = ["id" => $this->resourceIdSchema()] + $dataProperties;
    }

    $description = ($op === "add")
      ? "Creates one object from the attributes given, the same modification as a `POST` to this collection."
      : "Updates the attributes of the object named by `data.id`, the same modification as a `PATCH` on that object.";

    return [
      "type" => "object",
      "description" => $description,
      "required" => ["op", "data"],
      "properties" => [
        "op" => [
          "type" => "string",
          "const" => $op
        ],
        "data" => [
          "type" => "object",
          "required" => $dataRequired,
          "properties" => $dataProperties
        ]
      ]
    ];
  }

  /**
   * The response document of the atomic operations extension: one result per
   * operation, in the order the operations were sent. A result carries the
   * object the operation wrote, and is empty for a removal.
   */
  public function buildAtomicResults(string $resourceObjectRef): array {
    return [
      "type" => "object",
      "required" => ["jsonapi", "atomic:results"],
      "properties" => array_merge(
        $this->makeJsonApiHeader([], [self::ATOMIC_EXT_URI]),
        [
          "atomic:results" => [
            "type" => "array",
            "description" => "One result per operation, in request order.",
            "items" => [
              "type" => "object",
              "description" => "The object written by the operation, empty (`{}`) for a removal.",
              "properties" => [
                "data" => ['$ref' => $resourceObjectRef]
              ]
            ]
          ]
        ]
      )
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
          $description = "PATCH request to update attributes of a single object."
            . " Several objects are updated in one request with the atomic operations endpoint of this collection"
            . " (`POST .../operations`).";
        }
        break;
      case "delete":
        if ($isRelation) {
          $description = "DELETE request to remove a to-many relationship link.";
        }
        else {
          $description = "DELETE request to remove a single object."
            . " Several objects are removed in one request with the atomic operations endpoint of this collection"
            . " (`POST .../operations`).";
        }
    }
    return $description;
  }

  /**
   * The endpoint of the atomic operations extension. It is documented per
   * collection because that is where it is served and because the permissions
   * of the collection apply to it (see AbstractModelAPI::atomicOperations).
   */
  public function makeAtomicOperationsDescription(string $typeName): string {
    return "Applies a list of JSON:API atomic operations to the `" . $typeName . "` collection"
      . " ([atomic operations extension](https://jsonapi.org/ext/atomic/))."
      . " Every operation addresses one `" . $typeName . "` object: `add` creates it, `update` updates its attributes and"
      . " `remove` deletes it. Only the operations this collection supports are accepted."
      . "<br />The operations are applied in the order they are sent and inside one transaction, so a failing operation"
      . " invalidates the effects of the ones before it."
      . "<br />Request and response carry the `ext=\"" . self::ATOMIC_EXT_URI . "\"` media type parameter; a request"
      . " without it is answered with 415. The answer reports one result per operation, and is 204 when no operation"
      . " returns data (a body of `remove` operations only)."
      . "<br />An operation requires the permission of the modification it describes: `add` the create, `update` the"
      . " update and `remove` the delete permission of this collection.";
  }
}
