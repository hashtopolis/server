<?php

namespace Hashtopolis\inc\apiv2\openapi;

use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use ReflectionMethod;

/**
 * Builds the path and component entries for a helper API route by
 * introspecting the helper class (PHPDoc comments, form fields, hand-written
 * swagger params and sample responses).
 */
class HelperApiPathBuilder {
  public function __construct(private FeatureTypeMapper $typeMapper) {
  }

  public function addRoute(RouteTarget $target, AbstractHelperAPI $api, array &$paths, array &$components): void {
    $path = $target->pattern;
    $method = $target->httpMethod;
    $apiMethod = $target->methodName;
    $class = $api;

    $name = $class::class;
    $apiMethod = ($apiMethod == "processPost" && $name != "ImportFileHelperAPI") ? "actionPost" : $apiMethod;
    $reflectionApiMethod = new ReflectionMethod($name, $apiMethod);
    $paths[$path][$method]["description"] = $this->parsePhpDoc($reflectionApiMethod->getDocComment());
    $parameters = $class->getCreateValidFeatures();
    $properties = $this->typeMapper->makeProperties($parameters);
    $components[$name] =
      [
        "type" => "object",
        "properties" => $properties,
      ];
    if ($method == "post") {
      $reflectionMethodFormFields = new ReflectionMethod($name, "getFormFields");
      $bodyDescription = $this->parsePhpDoc($reflectionMethodFormFields->getDocComment());
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
      $responseProperties = $this->typeMapper->mapToProperties($request_response);
      $components[$name . "Response"] = $responseProperties;
      $ref = "#/components/schemas/" . $name . "Response";
    }
    else if (is_string($request_response)) {
      $ref = "#/components/schemas/" . $request_response . "SingleResponse";
    }
    else if ($name == "ImportFileHelperAPI") {
      //ImportFileHelperAPI is hardcoded, because its different than other helpers.
      return;
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
  }

  private function parsePhpDoc($doc): array|string {
    $cleanedDoc = preg_replace([
      '/^\/\*\*/',   // Remove opening /**
      '/\*\/$/',      // Remove closing */
      '/^\s*\*\s?/m'  // Remove leading * on each line
    ], '', $doc);
    //annotation lines (@param, @return, @throws, ...) document the PHP signature, not the endpoint
    $prose = array_filter(
      array_map('rtrim', explode("\n", $cleanedDoc)),
      fn($line) => !str_starts_with(ltrim($line), '@')
    );
    //markdown friendly line end
    return str_replace("\n", "<br />", trim(implode("\n", $prose)));
  }
}
