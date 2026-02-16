<?php

namespace Hashtopolis\inc\apiv2\common;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use ReflectionMethod;
use Slim\Routing\RouteCollectorProxy;

use Middlewares\Utils\HttpErrorException;

use Slim\App;
/** @var App $app */
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
      /* Quirk to receive className, since it is hidden in a protected variable */
      $reflectionOfRoute = new \ReflectionObject($route);
      $protectedCallable = $reflectionOfRoute->getProperty('callable');
      $protectedCallable->setAccessible(true);
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
        $apiMethod = ($apiMethod == "processPost" && $name !== "ImportFileHelperAPI") ? "actionPost" : $apiMethod;
        $reflectionApiMethod = new ReflectionMethod($name, $apiMethod);
        $paths[$path][$method]["description"] = OpenAPISchemaUtils::parsePhpDoc($reflectionApiMethod->getDocComment());
        $parameters = $class->getCreateValidFeatures();
        $properties = OpenAPISchemaUtils::makeProperties($parameters);
        $components[$name] =
          [
            "type" => "object",
            "properties" => $properties,
          ];
        if ($method == "post") {
          $reflectionMethodFormFields = new ReflectionMethod($name, "getFormFields");
          $bodyDescription = OpenAPISchemaUtils::parsePhpDoc($reflectionMethodFormFields->getDocComment());
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
          $responseProperties = OpenAPISchemaUtils::mapToProperties($request_response);
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
      $name = substr($class->getDBAClass(), 4);
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
                  "properties" => OpenAPISchemaUtils::makeProperties($class->getFeaturesWithoutFormfields(), true)
                ],
              ]
            ]
          ]
        ];
        
        $relationships = ["relationships" => [
          "type" => "object",
          "properties" => OpenAPISchemaUtils::makeRelationships($class, $uri)
        ]
        ];
        $included = ["included" => [
          "type" => "array",
          "items" => [
            "type" => "object",
            "properties" => OpenAPISchemaUtils::makeExpandables($class, $app->getContainer())
          ],
        ]
        ];
        
        $properties_get_single = array_merge($properties_return_post_patch, $relationships, $included);
        
        $json_api_header = OpenAPISchemaUtils::makeJsonApiHeader();
        $links = OpenAPISchemaUtils::makeLinks($uri);
        $properties_return_post_patch = array_merge($json_api_header, $properties_return_post_patch);
        $properties_create = OpenAPISchemaUtils::buildPatchPost(OpenAPISchemaUtils::makeProperties($class->getAllPostParameters($class->getCreateValidFeatures())), $name);
        $properties_get = array_merge($json_api_header, $links, $properties_get_single, $included);
        $properties_patch = OpenAPISchemaUtils::buildPatchPost(OpenAPISchemaUtils::makeProperties($class->getPatchValidFeatures(), true), $name);
        $properties_patch_post_relation = OpenAPISchemaUtils::buildPostPatchRelation($relation, ($isToMany && !$isToOne));
        $responseGetRelation = $properties_patch_post_relation;
        
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
        
        $components[$name . "Relation" . ucfirst($relation)] =
          [
            "type" => "object",
            "properties" => $properties_patch_post_relation,
          ];
        
        $components[$name . "Relation" . ucfirst($relation) . "GetResponse"] =
          [
            "type" => "object",
            "properties" => $responseGetRelation
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
      
      $paths[$path][$method]["description"] = OpenAPISchemaUtils::makeDescription($isRelation, $method, $singleObject);
      
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
        "description" => " The Upload-Offset header’s value MUST be equal to the current offset of the resource"
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
        "Tus-Resumable" => OpenAPISchemaUtils::getTUSHeader(),
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
        "Tus-Resumable" => OpenAPISchemaUtils::getTUSHeader(),
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
        "Tus-Resumable" => OpenAPISchemaUtils::getTUSHeader(),
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
