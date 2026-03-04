<?php

namespace Hashtopolis\inc\apiv2\helper;

use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Middlewares\Utils\HttpErrorException;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\utils\TaskUtils;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class getBestTasksAgent extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/getBestTasksAgent";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [Agent::PERM_READ, Task::PERM_READ];
  }
  
  public static function getResponse(): null {
    return null;
  }
  
  
  public function actionPost(array $data): object|array|null {
    throw new HttpErrorException("getBestTasksAgents has no POST");
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
         "description" => "The ID of the agent."
      ]
    ];
  }
  
  /**
   * Endpoint to get the best task 
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws HttpErrorException
   */
  public function handleGet(Request $request, Response $response): Response {
    $this->preCommon($request);
    $agent = Factory::getAgentFactory()->get($request->getQueryParams()['agent']);
    if ($agent == null) {
      throw new HttpError("No agent has been found with provided agent id");
    }
    $tasks = TaskUtils::getBestTask($agent, true);

    $converted = [];
    foreach ($tasks as $task) {
      $converted[] = self::obj2Resource($task);
    }
    $ret = self::createJsonResponse(data: $converted);
    
    $body = $response->getBody();
    $body->write($this->ret2json($ret));
    
    return $response->withStatus(200)
      ->withHeader("Content-Type", 'application/vnd.api+json;');
  }
  
  static public function register($app): void {
    $baseUri = getBestTasksAgent::getBaseUri();
    
    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, "Hashtopolis\\inc\\apiv2\\helper\\getBestTasksAgent:handleGet");
  }
}