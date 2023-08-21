<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Factory;
use DBA\Config;
use DBA\QueryFilter;
use DBA\OrderFilter;
use Middlewares\Utils\HttpErrorException;
use Slim\Exception\HttpNotImplementedException;

use function PHPUnit\Framework\throwException;

require_once(dirname(__FILE__) . "/shared.inc.php");

function typeLookup($feature): array {
  $type_format = null;
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
    /* TODO: Specify item type */
  } elseif ($feature['type'] == 'bool') {
    $type = "boolean";
  } elseif (str_starts_with($feature['type'], 'str(')) {
    $type = "string";
  } elseif ($feature['type'] ==  'str') {
    $type = "string";
  } elseif ($feature['type'] ==  'list') {
    $type = "array";
  } else {
    throw new HttpErrorException("Cast for type  '" . $feature['type'] . "' not implemented");
  }
  $result = [
     "type" => $type,
      "type_format" => $type_format,
  ];

  return $result;
};

$app->group("/api/v2/ui/openapi.json", function (RouteCollectorProxy $group) use ($app) {
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
        "startsAt" => [
          "type" => "integer",
          "example" => 0
        ],
        "maxResults" => [
          "type" => "integer",
          "example" => 100
        ],
        "total" => [
          "type" => "integer",
          "example" => 200
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
      assert(sizeof($route->getMethods()) == 1);

      if (is_string($reflectionCallable) == false) {
        /* OPTIONS (CORS) have an function callable, ignore for now */
        continue;
      }

      /* Retrieve parameters */
      $apiClassName = explode(':', $reflectionCallable)[0];
      $class = new $apiClassName($app->getContainer());

      /* Path relative to basePath */
      $path = $route->getPattern();
      $method = strtolower($route->getMethods()[0]);
      /* Quick to find out if single parameter object is used */
      $singleObject = ((strstr($path, '/{id:')) !== false);
      $name = substr($class->getDBAClass(), 4);

      /**
       * Create component objects
       */
      if (array_key_exists($name, $components) == false) {
        $properties_get = [
          "_id" => [
            "type" => "integer",
          ],
          "_self" => [ 
            "type" => "string",
          ],
          "_expandables" => [ 
            "type" => "string",
            "default" => $class->getExpandables(),
          ]
          ];

        foreach ($class->getMappedFeatures() as $feature) {
          $ret = typeLookup($feature);
          $properties_get[$feature['alias']]["type"] = $ret["type"];
          if ($ret["type_format"] !== null) {
            $properties_get[$feature['alias']]["format"] = $ret["type_format"];
          }
        };

        foreach ($class->getCreateValidFeatures() as $feature) {
          $ret = typeLookup($feature);
          $properties_create[$feature['alias']]["type"] = $ret["type"];
          if ($ret["type_format"] !== null) {
            $properties_create[$feature['alias']]["format"] = $ret["type_format"];
          }
        }

        foreach ($class->getPatchValidFeatures() as $feature) {
          $ret = typeLookup($feature);
          $properties_patch[$feature['alias']]["type"] = $ret["type"];
          if ($ret["type_format"] !== null) {
            $properties_patch[$feature['alias']]["format"] = $ret["type_format"];
          }
        }

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
      $required_scopes = getRequiredPermissions($class->getDBAClass(), $method);
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
          $paths[$path][$method]["responses"]["201"] = [
            "description" => "successful operation",
            "content" => [
              "application/json" => [
                "schema" => [
                  '$ref' => "#/components/schemas/" . $name . "Response"
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
                  '$ref' => "#/components/schemas/" . $name . "Response"
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
      

      if ($singleObject) {
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
            "name" => "expand",
            "in" => "query",
            "schema" => [
              "type" => "string"
            ],
            "description" => "Items to expand"
          ]);
        };
      } else {
        if ($method == 'get') {
          $parameters = [
            [
              "name" => "startsAt",
              "in" => "query",
              "schema" => [
                "type" => "integer",
                "format" => "int32"
              ],
              "example" => 0,
              "description" => "The starting index of the values"
            ],
            [
              "name" => "maxResults",
              "in" => "query",
              "schema" => [
                "type" => "integer",
                "format" => "int32"
              ],
              "example" => 100,
              "description" => "The maximum number of issues to return per page."
            ],
            [
              "name" => "filter",
              "in" => "query",
              "schema" => [
                "type" => "string"
              ],
              "description" => "Filters results using a query."
            ],
            [
              "name" => "expand",
              "in" => "query",
              "schema" => [
                "type" => "string"
              ],
              "description" => "Items to expand"
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
