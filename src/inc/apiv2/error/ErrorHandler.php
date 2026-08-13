<?php

namespace Hashtopolis\inc\apiv2\error;

use Psr\Http\Message\ResponseInterface as Response;

class ErrorHandler {
  /**
   * Render an error as the JSON:API error document JSON:API requires
   * (https://jsonapi.org/format/1.1/#errors): the error objects live under
   * "errors" and the document is served as the JSON:API media type. The APIv2
   * reports one error per response, so the array always holds a single object.
   */
  static function errorResponse(Response $response, $message, $status = 401): Response {
    $document = [
      "jsonapi" => [
        "version" => "1.1"
      ],
      "errors" => [
        [
          /* JSON:API requires the status code of an error object as a string */
          "status" => (string)$status,
          "title" => (string)$message
        ]
      ]
    ];

    $body = $response->getBody();
    $body->write(json_encode($document, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));

    return $response
      ->withHeader("Content-type", "application/vnd.api+json")
      ->withStatus($status);
  }
}
