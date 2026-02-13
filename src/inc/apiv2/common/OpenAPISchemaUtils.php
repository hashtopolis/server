<?php

namespace Hashtopolis\inc\apiv2\common;

use Middlewares\Utils\HttpErrorException;

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
      "properties" => [
        "version" => [
          "type" => "string",
          "default" => "1.1"
        ],
        "ext" => [
          "type" => "string",
          "default" => "https://jsonapi.org/profiles/ethanresnick/cursor-pagination"
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
  
  //TODO relationship array is unnecessarily indexed in the swagger UI
  static function makeRelationships($class, $uri): array {
    $properties = [];
    $relationshipsNames = array_merge(array_keys($class->getToOneRelationships()), array_keys($class->getToManyRelationships()));
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
        "properties" => [
          "id" => [
            "type" => "integer"
          ],
          "type" => [
            "type" => "string",
            "default" => $expand
          ],
          "attributes" => [
            "type" => "object",
            "properties" => makeProperties($expandApiClass->getAliasedFeatures())
          ]
        ]
      ];
    };
    return $properties;
  }
  
  static function mapToProperties($map): array {
    $properties = array_map(function ($value) {
      return [
        "type" => "string",
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
      $ret = typeLookup($feature);
      $propertyVal[$feature['alias']]["type"] = $ret["type"];
      if ($ret["type_format"] !== null) {
        $propertyVal[$feature['alias']]["format"] = $ret["type_format"];
      }
      if ($ret["type_enum"] !== null) {
        $propertyVal[$feature['alias']]["enum"] = $ret["type_enum"];
      }
      if ($ret["subtype"] !== null) {
        $propertyVal[$feature['alias']]["items"]["type"] = $ret["subtype"];
      }
    }
    return $propertyVal;
  }
  
  static function buildPatchPost($properties, $name, $id = null): array {
    $result = ["data" => [
      "type" => "object",
      "properties" => [
        "type" => [
          "type" => "string",
          "default" => $name
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
      "properties" => [
        "type" => [
          "type" => "string",
          "default" => $name
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
}