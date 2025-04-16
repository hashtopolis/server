<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use Middlewares\Utils\HttpErrorException;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");

function typeLookup($feature): array {
  $type_format = null;
  $type_enum = null;
  $sub_type = null;
  if ($feature['type'] == 'int') {
    $type = "integer";
  } elseif ($feature['type'] == 'uint64') {
    /* TODO: Specify integer ranges */
    $type = "integer";
  } elseif ($feature['type'] == 'int64') {
    $type = "integer";
    $type_format = "int64";
  } elseif ($feature['type'] == 'dict') {
    $type = "object";
  } elseif ($feature['type'] == 'array') {
    $type = "array";
    $sub_type = "integer"; //TODO: subtype is hardcoded because we only have int arrays
  } elseif ($feature['type'] == 'bool') {
    $type = "boolean";
  } elseif (str_starts_with($feature['type'], 'str(')) {
    $type = "string";
  } elseif ($feature['type'] ==  'str') {
    $type = "string";
  } else {
    throw new HttpErrorException("Cast for type  '" . $feature['type'] . "' not implemented");
  }

  if (is_array($feature['choices'])) {
    $type_enum = array_keys($feature['choices']);
  }

  $result = [
     "type" => $type,
      "type_format" => $type_format,
      "type_enum" => $type_enum,
      "subtype" => $sub_type
  ];

  return $result;
};

function parsePhpDoc($doc) {
  $cleanedDoc = preg_replace([
    '/^\/\*\*/',   // Remove opening /**
    '/\*\/$/',      // Remove closing */
    '/^\s*\*\s?/m'  // Remove leading * on each line
  ], '', $doc);
  $cleanedDoc = str_replace("\n", "<br />", $cleanedDoc); //markdown friendly line end
  return $cleanedDoc;
}


// "jsonapi": {
//   "version": "1.1",
//   "ext": [
//       "https://jsonapi.org/profiles/ethanresnick/cursor-pagination"
//   ]
// },
function makeJsonApiHeader(): array {
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
  ]];
}

// "links": {
//     "self": "/api/v2/ui/hashlists?page[size]=10000",
//     "first": "/api/v2/ui/hashlists?page[size]=10000&page[after]=0",
//     "last": "/api/v2/ui/hashlists?page[size]=10000&page[before]=345",
//     "next": null,
//     "prev": "/api/v2/ui/hashlists?page[size]=10000&page[before]=114"
//   },
function makeLinks($uri): array {
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
  ]];
}

//TODO relationship array is unnecessarily indexed in the swagger UI 
function makeRelationships($class, $uri): array {
  $properties = [];
  $relationshipsNames = array_merge(array_keys($class->getToOneRelationships()), array_keys($class->getToManyRelationships()));
  sort($relationshipsNames);
  foreach ($relationshipsNames as $relationshipName) {
    $self = $uri . "/relationships/" . $relationshipName;
    $related = $uri . "/" . $relationshipName;
    array_push($properties,
    [
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
    ]);
  }
  return $properties;
}

function getTUSheader(): array {
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
function makeExpandables($class, $container): array {
  $properties = [];
  $expandables = array_merge($class->getToOneRelationships(), $class->getToManyRelationships());
  foreach ($expandables as $expand => $expandVal) {
      $expandClass = $expandVal["relationType"];
      $expandApiClass = new ($container->get('classMapper')->get($expandClass))($container);
      array_push($properties,
        [ 
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
        ]
      );
  };
  return $properties;
}

function mapToProperties($map): array {
  $properties = [];
  foreach ($map as $key => $value) {
    $properties[$key] = [
        "type" => "string",
        "default" => $value,
    ];
  }
  return [
    "type" => "array",
    "items" => [
      "type" => "object",
      "properties" => $properties
    ]
  ];
}

function makeProperties($features, $skipPK=false): array {
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
};

function buildPatchPost($properties, $name, $id=null): array {
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

function makeDescription($isRelation, $method, $singleObject): string {
  $description = "";
  switch ($method) {
    case "get":
      if ($isRelation) {
        if($singleObject) {
          $description = "GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.";
        } else {
          $description = "GET request for a to-many relationship link. Returns a list of resource records of objects that are part of the specified relation.";
        }
      } else {
        if ($singleObject) {
          $description = "GET request to retrieve a single object.";
        } else {
          $description = "GET many request to retrieve multiple objects.";
        }
      }
      break;
    case "post":
      if ($isRelation) {
        if ($singleObject) {
          "POST request to create a to-one relationship link.";
        } else {
          "POST request to create a to-many relationship link.";
        }
      } else {
        $description = "POST request to create a new object. The request must contain the resource record as data with the attributes of the new object." 
          . "To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.";
        }
      break;
    case "patch":
      if ($isRelation) {
        if ($singleObject) {
          "PATCH request to update a to one relationship.";
        } else {
          "PATCH request to update a to-many relationship link.";
        }
      } else {
        $description = "PATCH request to update attributes of a single object." ;
        }        
  }
  return $description;
}

$app->group("/api/v2/openapi.json", function (RouteCollectorProxy $group) use ($app) {
  /* Allow CORS preflight requests */
  $group->options('', function (Request $request, Response $response): Response {
    return $response;
  });

  $group->get('', function (Request $request, Response $response) use ($app): Response {
    /* Hold collection of all scopes discovered */
    $all_scopes = [];

    $paths = [];
    $components["ListResponse"] = [
      "type" => "object",
      "properties" => [
        "expand" => [
          "type" => "string",
          "example" => "hashlist",
        ],
        "page[after]" => [
          "type" => "integer",
          "example" => 0
        ],
        "page[before]" => [
          "type" => "integer",
          "example" => 0
        ],
        "page[size]" => [
          "type" => "integer",
          "example" => 100
        ]
      ]
    ];
    $components["ErrorResponse"] = [
      "type" => "object",
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
      /* Quirck to receive className, since it is hidden in a protected variable */
      $reflectionOfRoute = new \ReflectionObject($route);
      $protectedCallable =  $reflectionOfRoute->getProperty('callable');
      $protectedCallable->setAccessible(true);
      $reflectionCallable = ($protectedCallable->getValue($route));

      /* Assume only one method per route call */
      assert(sizeof($route->getMethods()) == 1, "More than 1 methods found for this route");
      /* Path relative to basePath */
      $path = $route->getPattern();
      $method = strtolower($route->getMethods()[0]);

      if (is_string($reflectionCallable) == false) {
        /* OPTIONS (CORS) have an function callable, ignore for now */
        continue;
      }

      /* Retrieve parameters */
      $explodedCallable = explode(':', $reflectionCallable);
      $apiClassName = $explodedCallable[0];
      $apiMethod = $explodedCallable[1];
      $class = new $apiClassName($app->getContainer());

      if (!($class instanceof AbstractModelAPI)){
        $name = $class::class;
        $apiMethod = ($apiMethod == "processPost" && $name !== "ImportFileHelperAPI") ? "actionPost" : $apiMethod;
        $reflectionApiMethod = new ReflectionMethod($name, $apiMethod);
        $paths[$path][$method]["description"] = parsePhpDoc($reflectionApiMethod->getDocComment());
        $parameters = $class->getCreateValidFeatures();
        $properties = makeProperties($parameters);
        $components[$name] =
          [
            "type" => "object",
            "properties" => $properties,
          ];
        if($method == "post") {
          $reflectionMethodFormFields = new ReflectionMethod($name, "getFormFields");
          $bodyDescription = parsePhpDoc($reflectionMethodFormFields->getDocComment());
          $paths[$path][$method]["requestBody"] = [
            "description" => $bodyDescription,
            "required" => true,
            "content" => [
              "application/json" => [
                "schema" => [
                  '$ref' => "#/components/schemas/" . $name 
                ],
              ]        
          ]];
        } elseif($method == "get") {
          $paths[$path][$method]["parameters"] = $class->getParamsSwagger();
        }
        $request_response = $class->getResponse();
        $ref = null;
        if (is_array($request_response)) {
          $responseProperties = mapToProperties($request_response);
          $components[$name . "response"] = $responseProperties;
          $ref = "#/components/schemas/" . $name . "Response";
        } else if (is_string($request_response)) {
          $ref = "#/components/schemas/" . $request_response . "SingleResponse";
        } else if ($name == "ImportFileHelperAPI"){
          //TODO: probably have to hardcode all responses and just continue here, since every endpoint has a different status code
          $paths[$path][$method]["responses"]["201"] = [
            "description" => "succesful operation",
            "headers" => [
              "Tus-Resumable" => getTUSheader(),
            ],
            "content" => [
              "application/pdf" => [
                "type" => "string",
                "format" => "binary"
              ]
            ]
          ];
          continue;
        }
        // $paths[$path][$method]["responses"]["200"] = [
        //   "description" => "successful operation",
        //   // "content" => [
        //   //   "application/json" => [
        //   //     "schema" => [
        //   //       '$ref' => $ref
        //   //     ]
        //   //   ]
        //   // ]
        // ];
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
      } else {
        $paths[$path][$method]["responses"]["200"] = [
          "description" => "successful operation",
          // "content" => [
          //   "application/json" => [
          //     "schema" => [
          //       '$ref' => $ref
          //     ]
          //   ]
          // ]
        ];
      }
        continue;
      };

      /* Quick to find out if single parameter object is used */
      $singleObject = ((strstr($path, '/{id:')) !== false);
      $name = substr($class->getDBAClass(), 4);
      $uri = $class->getBaseUri();

      $isRelation = (strstr($path , "{relation:")) !== false;

      $expandables = implode(",", $class->getExpandables());
      /**
       * Create component objects
       */
      if (array_key_exists($name, $components) == false) {
        $properties_return_post_patch = [
          "data" => [
            "type" => "array",
            "items" => [
              "type" => "object",
              "properties" => [
                "id" => [
                  "type" => "integer",
                ],
                "type" => [ 
                  "type" => "string",
                  "default" => $name
                ],
                "attributes" => [
                  "type" => "object",
                  "properties" => makeProperties($class->getFeaturesWithoutFormfields(), true)
                ],
              ]
            ]
          ]
        ];

        $relationships = ["relationships" =>[
          "type" => "object",
          "properties" => makeRelationships($class, $uri)
        ]
        ];
        $included = ["included" => [ 
          "type" => "array",
          "items" => [
            "type" => "object",
            "properties" => makeExpandables($class, $app->getContainer())
          ],
          ]
        ];

        $properties_get_single = array_merge($properties_return_post_patch, $relationships, $included);

        $json_api_header = makeJsonApiHeader();
        $links = makeLinks($uri);
        $properties_return_post_patch = array_merge($json_api_header, $properties_return_post_patch);
        $properties_create = buildPatchPost(makeProperties($class->getAllPostParameters($class->getCreateValidFeatures(), true)), $name);
        $properties_get = array_merge($json_api_header, $links, $properties_get_single, $included);
        $properties_patch = buildPatchPost(makeProperties($class->getPatchValidFeatures(), true), $name);

        $components[$name . "Create"] =
          [
            "type" => "object",
            "properties" => $properties_create,
          ];

        $components[$name . "Patch"] =
          [
            "type" => "object",
            "properties" => $properties_patch,
          ];
        
        $components[$name . "Response"] =
          [
            "type" => "object",
            "properties" => $properties_get,
          ];

        $components[$name . "SingleResponse"] =
          [
            "type" => "object",
            "properties" => $properties_get_single
          ];

        $components[$name . "PostPatchResponse"] =
          [
            "type" => "object",
            "properties" => $properties_return_post_patch
          ];

        $components[$name . "ListResponse"] =
          [
            "allOf" => [
              [
                '$ref' => "#/components/schemas/ListResponse"
              ],
              [
                "type" => "object",
                "properties" => [
                  "values" => [
                    "type" => "array",
                    "items" => [
                      '$ref' => "#/components/schemas/" . $name . "Response"
                    ]
                  ]
                ]
              ]
            ]
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

      $paths[$path][$method]["description"] = makeDescription($isRelation, $method, $singleObject);

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
         
        } elseif ($method == 'patch') {
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

          $paths[$path][$method]["requestBody"] = [
            "required" => true,
            "content" => [
              "application/json" => [
                "schema" => [
                  '$ref' => "#/components/schemas/" . $name . "Patch"
                ],
              ],     
          ]];

        } elseif ($method == 'delete') {
          $paths[$path][$method]["responses"]["204"] = [
            "description" => "successfully deleted",
          ];

                    /* Empty JSON object required */
                    $paths[$path][$method]["requestBody"] = [
                      "required" => true,
                      "content" => [
                        "application/json" => [],     
                    ]];
        } elseif ($method == 'post') {
          $paths[$path][$method]["responses"]["204"] = [
            "description" => "successfully created",
          ];

                    /* Empty JSON object required */
                    $paths[$path][$method]["requestBody"] = [
                      "required" => true,
                      "content" => [
                        "application/json" => [],     
                    ]];
        } else {
          throw new HttpErrorException("Method '$method' not implemented");
        }
      } else {
        /* Model API entry point */
        if ($method == 'get') {
          $paths[$path][$method]["responses"]["200"] = [
            "description" => "successful operation",
            "content" => [
              "application/json" => [
                "schema" => [
                  "type" => "array",
                  "items" => [
                    '$ref' => "#/components/schemas/" . $name . "Response"
                  ]
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


        } elseif ($method == 'post') {
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

          $paths[$path][$method]["requestBody"] = [
            "required" => true,
            "content" => [
              "application/json" => [
                "schema" => [
                  '$ref' => "#/components/schemas/" . $name . "Create"
                ],
              ]        
          ]];

        } else { 
          throw new HttpErrorException("Method '$method' not implemented");
        }
      }

      if ($singleObject && $method == 'get') {
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
          ]];

        if ($method == 'get') {
          array_push($parameters, 
          [
            "name" => "include",
            "in" => "query",
            "schema" => [
              "type" => "string"
            ],
            "description" => "Items to include. Comma seperated"
          ]);
        };
      } else {
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
        } else {
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
              "application/json" =>[
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

    /**
     * Build final result
     */
    $unique_all_scopes = array_unique($all_scopes);
    asort($unique_all_scopes);
    $result = [
      "openapi" => "3.0.1",
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

    $body = $response->getBody();
    $body->write(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

    return $response->withStatus(200)
      ->withHeader("Content-Type", "application/json");
  });
});
