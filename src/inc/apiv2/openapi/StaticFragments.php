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
   * The refresh endpoint trades the refresh token cookie for a new access token
   * without asking for credentials again, and its DELETE ends the session the
   * cookie belongs to (see token.routes.php).
   */
  public function authRefreshPath(): array {
    return [
      "post" => [
        "tags" => [
          "Login"
        ],
        "summary" => "Exchange the refresh token cookie for a new access token",
        "description" => "Reads the refreshToken cookie set by /api/v2/auth/token, rotates it and answers
          with a new access token. Needs no Authorization header, so it keeps working once the previous
          access token has expired. The rotated cookie is returned in Set-Cookie; the token itself is
          never part of the body.",
        "responses" => [
          "201" => [
            "description" => "Success",
            "headers" => [
              "Set-Cookie" => $this->rotatedCookieHeader()
            ],
            "content" => [
              "application/json" => [
                "schema" => [
                  '$ref' => "#/components/schemas/Token"
                ]
              ]
            ]
          ],
          "401" => $this->problemResponse("The refresh token is missing, expired, revoked or already used"),
          "403" => $this->problemResponse("The user has been deactivated")
        ],
        "security" => [
          [
            "refreshCookie" => []
          ]
        ]
      ],
      "delete" => [
        "tags" => [
          "Login"
        ],
        "summary" => "Log out",
        "description" => "Revokes the session the refreshToken cookie belongs to and clears the cookie.
          Sessions on other devices are left alone. Logging out without a cookie is not an error.",
        "responses" => [
          "204" => [
            "description" => "Success",
            "headers" => [
              "Set-Cookie" => $this->clearedCookieHeader()
            ]
          ],
          "403" => $this->problemResponse("The request origin is not allowed to send credentials")
        ],
        /* Logging out without a cookie is a successful no-op, so the cookie cannot be a hard
           requirement here: the empty alternative is how OpenAPI spells "optional", and without it a
           generated client would refuse to make a call the server answers with 204. */
        "security" => [
          [
            "refreshCookie" => []
          ],
          new \stdClass()
        ]
      ]
    ];
  }

  /**
   * The cookie handed out on a successful exchange. Its lifetime follows the deployment's configured
   * refresh token lifetime, so the Max-Age in the example is the default rather than a fixed value.
   */
  private function rotatedCookieHeader(): array {
    return [
      "description" => "The rotated refresh token, scoped to /api/v2/auth/refresh and marked HttpOnly.
        Replaces the cookie sent with the request, which is consumed by this call.",
      "schema" => [
        "type" => "string",
        "example" => "refreshToken=4fa1371293a112224bc930cf9fecd0fd; Path=/api/v2/auth/refresh; Max-Age=1209600; Expires=Wed, 30 Sep 2026 07:02:31 GMT; HttpOnly; SameSite=Strict"
      ]
    ];
  }

  /**
   * Logging out sends the same cookie back empty and already expired, which is how a client is told
   * to drop it. Describing it as the rotated cookie would document the opposite of what happens.
   */
  private function clearedCookieHeader(): array {
    return [
      "description" => "The refresh token cookie, emptied and expired so the client drops it. Carries
        the same attributes it was set with, which is what makes a browser replace rather than keep it.",
      "schema" => [
        "type" => "string",
        "example" => "refreshToken=; Path=/api/v2/auth/refresh; Max-Age=0; Expires=Wed, 16 Sep 2026 07:02:31 GMT; HttpOnly; SameSite=Strict"
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
