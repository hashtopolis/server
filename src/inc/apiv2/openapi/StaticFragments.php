<?php

namespace Hashtopolis\inc\apiv2\openapi;

/**
 * Hardcoded spec fragments that are not derived from introspection: base error
 * components, the auth token endpoint, token/object request schemas and the
 * TUS importFile endpoint documentation.
 */
class StaticFragments {
  /**
   * The body of every error the APIv2 answers with: ErrorHandler::errorResponse
   * renders an RFC 7807 problem document for all of them.
   */
  public function errorComponents(): array {
    $components = [];
    $components["ErrorResponse"] = [
      "type" => "object",
      "required" => ["status"],
      "description" => "RFC 7807 problem document",
      "properties" => [
        "title" => [
          "type" => "string",
          "example" => "No access to this object!"
        ],
        "type" => [
          "type" => "string",
          "example" => "about:blank"
        ],
        "status" => [
          "type" => "integer",
          "example" => 400
        ]
      ]
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
          "400" => $this->problemResponse("Invalid request"),
          "401" => $this->problemResponse("Authentication failed")
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
   * Errors are rendered as RFC 7807 problem documents by
   * ErrorHandler::errorResponse, on every APIv2 route.
   */
  private function problemResponse(string $description): array {
    return [
      "description" => $description,
      "content" => [
        JsonApiFragments::PROBLEM_MEDIA_TYPE => [
          "schema" => [
            '$ref' => "#/components/schemas/ErrorResponse"
          ]
        ]
      ]
    ];
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

    $paths["/api/v2/helper/importFile/{id:[0-9]{14}-[0-9a-f]{32}}"]["patch"]["parameters"] = [
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
    $paths["/api/v2/helper/importFile/{id:[0-9]{14}-[0-9a-f]{32}}"]["patch"]["responses"]["204"] = [
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
