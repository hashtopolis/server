<?php

use DBA\File;
use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DBA\Factory;
use Middlewares\Utils\HttpErrorException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpForbiddenException;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class GetAgentBinaryHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/getAgentBinary";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [];
  }
  
  /**
   * getAgentBinary is different because it returns actual binary data.
   */
  public static function getResponse(): null {
    return null;
  }
  
  
  #[NoReturn] public function actionPost(array $data): object|array|null {
    assert(False, "getAgentBinary has no POST");
  }
  
  /**
   * @throws HTException
   */
  public function validateAgent($request, int $agentBinaryId): string {
    $agentBinary = Factory::getAgentBinaryFactory()->get($agentBinaryId);
    if ($agentBinary == null) {
      throw new HttpNotFoundException($request, "No agent binary with id: " . $agentBinaryId);
    }
    $filename = dirname(__FILE__) . "/../../../bin/" . $agentBinary->getFilename();
    if (!file_exists($filename)) {
      throw new HTException("Agent Binary not present on server!");
    }
    if (!is_readable($filename)) {
      throw new HttpForbiddenException($request, "Not allowed to read file");
    }
    
    return $filename;
  }
  
  /**
   * Description of get params for swagger.
   */
  public function getParamsSwagger(): array {
    return [
      [
        "in" => "query",
        "name" => "agent",
        "schema" => [
          "type" => "integer",
          "format" => "int32"
        ],
        "required" => true,
        "example" => 1,
        "description" => "The ID of the agent zip to download."
      ]
    ];
  }
  
  /**
   * Endpoint to download files
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws HTException
   * @throws HttpErrorException
   * @throws HttpForbidden
   */
  public function handleGet(Request $request, Response $response): Response {
    $this->preCommon($request);
    $agentParam = $request->getQueryParams()['agent'];
    if ($agentParam == null) {
      throw new HttpErrorException("No AgentBinary query param has been provided");
    }
    $agentBinaryId = intval($agentParam);
    $filename = $this->validateAgent($request, $agentBinaryId);
    
    return $this->startDownload($request, $response, $filename);
  }
  
  static public function register($app): void {
    $baseUri = GetAgentBinaryHelperAPI::getBaseUri();
    
    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, "getAgentBinaryHelperAPI:handleGet");
  }
}

GetAgentBinaryHelperAPI::register($app);