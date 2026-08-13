<?php

namespace Hashtopolis\inc\apiv2\util;

use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\ErrorHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Psr7\Response;

/**
 * Content negotiation of the JSON:API media type, as JSON:API 1.1 requires from
 * a server (https://jsonapi.org/format/1.1/#content-negotiation-servers):
 *
 * - A request whose Content-Type is the JSON:API media type modified with a
 *   media type parameter other than "ext" or "profile", or naming an extension
 *   the server does not implement, is answered with 415.
 * - A request whose Accept header holds instances of the JSON:API media type of
 *   which none is usable (all modified by other parameters, or all requiring an
 *   extension the server does not implement) is answered with 406.
 *
 * Both leave every other media type alone: requests sending or accepting
 * application/json, application/offset+octet-stream (TUS uploads) or anything
 * else are not affected.
 */
class ContentNegotiationMiddleware implements MiddlewareInterface {
  private const JSON_API_MEDIA_TYPE = "application/vnd.api+json";

  /** The extensions this server implements. */
  private const SUPPORTED_EXTENSIONS = [AbstractModelAPI::ATOMIC_EXT_URI];

  /**
   * Parameters that do not modify the JSON:API media type: the two the
   * specification defines, plus the HTTP quality weight, which is a property of
   * the Accept header and not of the media type.
   */
  private const ALLOWED_PARAMETERS = ["ext", "profile", "q"];

  public function process(Request $request, RequestHandler $handler): ResponseInterface {
    $contentTypeError = $this->contentTypeError($request->getHeaderLine("Content-Type"));
    if ($contentTypeError !== null) {
      return ErrorHandler::errorResponse(new Response(), $contentTypeError, 415);
    }

    $acceptError = $this->acceptError($request->getHeaderLine("Accept"));
    if ($acceptError !== null) {
      return ErrorHandler::errorResponse(new Response(), $acceptError, 406);
    }

    return $handler->handle($request);
  }

  /**
   * The Content-Type names the media type of the request body, so it carries a
   * single media type instance.
   */
  private function contentTypeError(string $contentType): ?string {
    $instance = $this->jsonApiInstances($contentType)[0] ?? null;
    if ($instance === null) {
      return null;
    }

    foreach ($instance as $name => $value) {
      if (!in_array($name, self::ALLOWED_PARAMETERS, true)) {
        return "The media type '" . self::JSON_API_MEDIA_TYPE . "' does not accept the '" . $name . "' parameter";
      }
    }

    $unsupported = $this->unsupportedExtensions($instance);
    if (count($unsupported) > 0) {
      return "Unsupported extension(s) requested: " . implode(", ", $unsupported);
    }

    return null;
  }

  /**
   * The Accept header may hold several instances of the media type. Only when
   * none of them is usable can the request not be answered.
   */
  private function acceptError(string $accept): ?string {
    $instances = $this->jsonApiInstances($accept);
    if (count($instances) === 0) {
      return null;
    }

    foreach ($instances as $instance) {
      $modified = array_filter(
        array_keys($instance),
        fn($name) => !in_array($name, self::ALLOWED_PARAMETERS, true)
      );
      if (count($modified) === 0 && count($this->unsupportedExtensions($instance)) === 0) {
        return null;
      }
    }

    return "None of the accepted instances of '" . self::JSON_API_MEDIA_TYPE . "' can be served:"
      . " the media type only takes the 'ext' and 'profile' parameters, and the only extension"
      . " implemented is " . implode(", ", self::SUPPORTED_EXTENSIONS);
  }

  /**
   * The parameters of every instance of the JSON:API media type in a header
   * value, in the order they appear.
   *
   * @return list<array<string, string>>
   */
  private function jsonApiInstances(string $header): array {
    if (trim($header) === "") {
      return [];
    }

    $instances = [];
    foreach (explode(",", $header) as $entry) {
      $parts = array_map("trim", explode(";", $entry));
      if (strtolower((string)array_shift($parts)) !== self::JSON_API_MEDIA_TYPE) {
        continue;
      }

      $parameters = [];
      foreach ($parts as $part) {
        if ($part === "") {
          continue;
        }
        $name = strtolower(trim(strstr($part, "=", true) ?: $part));
        $value = str_contains($part, "=") ? substr($part, strpos($part, "=") + 1) : "";
        $parameters[$name] = trim($value, " \"'");
      }
      $instances[] = $parameters;
    }

    return $instances;
  }

  /**
   * The extension URIs of an instance that this server does not implement. The
   * ext parameter holds a space separated list of them.
   *
   * @param array<string, string> $parameters
   * @return list<string>
   */
  private function unsupportedExtensions(array $parameters): array {
    if (!isset($parameters["ext"]) || trim($parameters["ext"]) === "") {
      return [];
    }

    $requested = preg_split("/\s+/", trim($parameters["ext"])) ?: [];
    return array_values(array_diff($requested, self::SUPPORTED_EXTENSIONS));
  }
}
