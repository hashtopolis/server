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
  public function __construct(
    private FeatureTypeMapper $typeMapper,
    private JsonApiFragments $jsonApiFragments
  ) {
  }

  public function addRoute(RouteTarget $target, AbstractHelperAPI $api, array &$paths, array &$components): void {
    $path = $target->pathTemplate;
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
    /**
     * Helpers run behind the same authentication and permission checks as the
     * model routes and resolve the objects they act on, so they answer the same
     * errors.
     */
    $paths[$path][$method]["responses"] = $this->jsonApiFragments->commonErrorResponses();
    $paths[$path][$method]["responses"]["404"] = $this->jsonApiFragments->errorResponse("Not Found");
    /* A request body under an unusable media type never reaches the route */
    if (in_array($method, ["post", "patch"], true)) {
      $paths[$path][$method]["responses"]["415"] = $this->jsonApiFragments->unsupportedMediaTypeResponse();
    }

    if ($method == "post") {
      $reflectionMethodFormFields = new ReflectionMethod($name, "getFormFields");
      $bodyDescription = $this->parsePhpDoc($reflectionMethodFormFields->getDocComment());
      /**
       * A helper takes a flat map of its form fields, not a JSON:API document,
       * so its request body is plain application/json.
       */
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
      $components[$name . "Response"] = $this->jsonApiFragments->buildMetaResponse(
        $this->typeMapper->mapToProperties($request_response)
      );
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
      $paths[$path][$method]["responses"]["200"] = $this->jsonApiFragments->jsonApiResponse("successful operation", $ref);
    }
    else {
      $paths[$path][$method]["responses"]["200"] = [
        "description" => "successful operation",
      ];
    }
  }

  private function parsePhpDoc($doc): string {
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
