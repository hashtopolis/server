<?php

namespace Hashtopolis\inc\apiv2\openapi;

/**
 * Builders for the recurring JSON:API document fragments (jsonapi header,
 * pagination links, write envelopes, canned descriptions).
 */
class JsonApiFragments {
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
          "default" => $self
        ],
        "last" => [
          "type" => "string",
          "default" => $self . "&page[before]=eyJwcmltYXJ5Ijp7InNvbWVVbnFpdWVGaWVsZCI6MTIzfSwic2Vjb25kYXJ5Ijp7InNvbWVPdGhlck9wdGlvbmFsRmllbGQiOiJGb28ifX0="
        ],
        "next" => [
          "type" => ["string", "null"],
          "default" => $self . "&page[after]=eyJwcmltYXJ5Ijp7InNvbWVVbnFpdWVGaWVsZCI6MTIzfSwic2Vjb25kYXJ5Ijp7InNvbWVPdGhlck9wdGlvbmFsRmllbGQiOiJGb28ifX0"
        ],
        "previous" => [
          "type" => ["string", "null"],
          "default" => $self . "&page[before]=eyJwcmltYXJ5Ijp7InNvbWVVbnFpdWVGaWVsZCI6MTIzfSwic2Vjb25kYXJ5Ijp7InNvbWVPdGhlck9wdGlvbmFsRmllbGQiOiJGb28ifX0"
        ]
      ]
    ]
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
  public function buildPostPatchRelation($name, $isToMany): array {
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
