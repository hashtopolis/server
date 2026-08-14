<?php

namespace Hashtopolis\inc\apiv2\openapi;

/**
 * Hardcoded spec fragments that are not derived from introspection: base error
 * components, the auth token endpoint, token/object request schemas and the
 * TUS importFile endpoint documentation.
 */
class StaticFragments {
  public function __construct(private JsonApiFragments $jsonApiFragments) {
  }

  /**
   * The body of every error the APIv2 answers with: ErrorHandler::errorResponse
   * renders a JSON:API error document for all of them, holding exactly one error
   * object.
   */
  public function errorComponents(): array {
    $components = [];
    $components["ErrorResponse"] = [
      "type" => "object",
      "required" => ["jsonapi", "errors"],
      "description" => "JSON:API error document",
      "properties" => array_merge(
        $this->jsonApiFragments->makeJsonApiHeader([]),
        [
          "errors" => [
            "type" => "array",
            /* The APIv2 reports one error per response */
            "maxItems" => 1,
            "items" => [
              "type" => "object",
              "required" => ["status", "title"],
              "properties" => [
                "status" => [
                  "type" => "string",
                  "description" => "The HTTP status code of the response, as a string",
                  "example" => "400"
                ],
                "title" => [
                  "type" => "string",
                  "example" => "No access to this object!"
                ]
              ]
            ]
          ]
        ]
      )
    ];
    return $components;
  }

  /**
   * The token endpoint exchanges basic auth credentials for a JWT. It is not a
   * JSON:API resource endpoint: it answers a plain application/json body with
   * status 201 (see token.routes.php).
   */
  public function authTokenPath(): array {
    return [
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
          "201" => [
            "description" => "Success",
            "content" => [
              "application/json" => [
                "schema" => [
                  '$ref' => "#/components/schemas/Token"
                ]
              ]
            ]
          ],
          "400" => $this->errorResponse("Invalid request"),
          "401" => $this->errorResponse("Authentication failed"),
          /* generateTokenForUser refuses a user that is set to invalid */
          "403" => $this->errorResponse("The user is set to invalid"),
          /* Content negotiation happens for every route, this one included */
          "406" => $this->errorResponse(
            "The Accept header only asks for instances of `" . JsonApiFragments::MEDIA_TYPE . "` that cannot be served"
          ),
          "415" => $this->errorResponse(
            "The Content-Type is `" . JsonApiFragments::MEDIA_TYPE . "` with a media type parameter other than"
            . " `ext` and `profile`, or names an unsupported extension"
          )
        ],
        "security" => [
          [
            "basicAuth" => []
          ]
        ]
      ]
    ];
  }

  /**
   * Errors are rendered as JSON:API error documents by
   * ErrorHandler::errorResponse, on every APIv2 route.
   */
  private function errorResponse(string $description): array {
    return $this->jsonApiFragments->errorResponse($description);
  }

  public function tokenComponents(): array {
    $components = [];
    $components["Token"] = [
      "type" => "object",
      "required" => ["token", "expires"],
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
    return $components;
  }

  public function tusHeader(): array {
    return [
      "description" => "Indicates the TUS version the server supports.
        Must always be set to `1.0.0` in compliant servers.",
      "schema" => [
        "type" => "string",
        "enum" => "enum: ['1.0.0']"
      ]
    ];
  }

  /**
   * The keys used here are OpenAPI path templates, matching what
   * RouteIntrospector derives from the Slim patterns of the same routes, so
   * that these fragments land on the existing path items instead of creating
   * a second entry for the same endpoint.
   */
  public function applyImportFileTusPaths(array &$paths): void {
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

    $paths["/api/v2/helper/importFile/{id}"]["patch"]["parameters"] = [
      [
        "name" => "Upload-Offset",
        "in" => "header",
        "required" => "true",
        "schema" => [
          "type" => "integer",
        ],
        "example" => 512,
        "description" => " The Upload-Offset header's value MUST be equal to the current offset of the resource"
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
    $paths["/api/v2/helper/importFile/{id}"]["patch"]["requestBody"] = [
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

    $paths["/api/v2/helper/importFile/{id}"]["head"]["responses"]["200"] = [
      "description" => "successful request",
      "headers" => [
        "Tus-Resumable" => $this->tusHeader(),
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

    /* TUS creation answers with headers only, the upload itself follows as PATCH */
    $paths["/api/v2/helper/importFile"]["post"]["responses"]["201"] = [
      "description" => "Upload created",
      "headers" => [
        "Tus-Resumable" => $this->tusHeader(),
        "Location" => [
          "description" => "Location of the file where the user can push to.",
          "schema" => [
            "type" => "string"
          ]
        ]
      ]
    ];
    $paths["/api/v2/helper/importFile/{id}"]["patch"]["responses"]["204"] = [
      "description" => "Chunk accepted",
      "headers" => [
        "Tus-Resumable" => $this->tusHeader(),
        "Upload-Offset" => [
          "description" => "The new offset after the chunk is accepted. Indicates how many bytes were received so far.",
          "schema" => [
            "type" => "integer"
          ]
        ]
      ]
    ];
  }
}
