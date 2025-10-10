<?php

use DBA\Assignment;
use DBA\Factory;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Speed;
use DBA\Task;
use JetBrains\PhpStorm\NoReturn;
use Middlewares\Utils\HttpErrorException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class GetTaskSpeedHelper extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/getTaskSpeeds";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [Task::PERM_READ];
  }
  
  public static function getResponse(): null {
    return null;
  }
  
  
  #[NoReturn] public function actionPost(array $data): object|array|null {
    assert(False, "getTaskSpeedHelper has no POST");
  }
  
  /**
   * Description of get params for swagger.
   */
  public function getParamsSwagger(): array {
    return [
      [
        "in" => "query",
        "name" => "task",
        "schema" => [
          "type" => "integer",
          "format" => "int32"
        ],
        "required" => true,
        "example" => 1,
        "description" => "The ID of the task."
      ]
    ];
  }
  
  /**
   * Endpoint to get a limited number of the recent task speeds
   *
   * Sets a limit for each assigned agent and a max limit at all
   * The returned array is reversed to get the speeds sorted ascending by time
   *
   * $limitPerAgent: Roughly set to one hour per agent, assuming an agent is writing a speed entry every 5 seconds
   * $maxLimit: Set to 10 agents to be able to show an hour, if more agents are working on a task the time period gets shorter
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws HttpErrorException
   */
  public function handleGet(Request $request, Response $response): Response {
    $this->preCommon($request);
    
    $limitPerAgent = 720;
    $maxAgents = 10;
    $maxLimit = $limitPerAgent * $maxAgents;
    
    $taskId = $request->getQueryParams()['task'];
    
    $assignmentQueryFilter = new QueryFilter(Assignment::TASK_ID, $taskId, "=");
    $agentCount = Factory::getAssignmentFactory()->countFilter([Factory::FILTER => $assignmentQueryFilter]) + 1;
    $requestLimit = min($agentCount * $limitPerAgent, $maxLimit);
    
    $speedQueryFilter= new QueryFilter(Speed::TASK_ID, $taskId, "=");
    $speedOrderFilter = new OrderFilter(Speed::TIME, "DESC LIMIT $requestLimit");
    $speedEntries = Factory::getSpeedFactory()->filter([Factory::FILTER => $speedQueryFilter, Factory::ORDER => $speedOrderFilter]);
    
    $converted = [];
    foreach ($speedEntries as $speed) {
      $converted[] = self::obj2Resource($speed);
    }
    $ret = self::createJsonResponse(data: array_reverse($converted));
    
    $body = $response->getBody();
    $body->write($this->ret2json($ret));
    
    return $response->withStatus(200)
      ->withHeader("Content-Type", 'application/vnd.api+json;');
  }
  
  static public function register($app): void {
    $baseUri = GetTaskSpeedHelper::getBaseUri();
    
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, "getTaskSpeedHelper:handleGet");
  }
}

GetTaskSpeedHelper::register($app);
