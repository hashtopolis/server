<?php

namespace Hashtopolis\inc\apiv2\helper;

use Exception;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Aggregation;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\TaskWrapperDisplay;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\defines\DTaskTypes;
use Hashtopolis\inc\HTException;
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\Task;
use Middlewares\Utils\HttpErrorException;

class GetCompletedCountHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/getCompletedCount";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [TaskWrapper::PERM_READ, Task::PERM_READ];
  }
  
  public static function getResponse(): string {
    return "Task";
  }
  
  /**
   * @param array $data
   * @return AbstractModel|array|null
   * @throws HttpErrorException
   */
  public function actionPost(array $data): AbstractModel|array|null {
    throw new HttpErrorException("getCompletedCount has no POST");
  }
  
  /**
   * Endpoint to get the cracked hashes of a certain task
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws HttpError
   * @throws HTException
   * @throws JsonException
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   * @throws HttpForbidden
   * @throws Exception
   */
  public function handleGet(Request $request, Response $response): Response {
    $this->preCommon($request);
    $data = [];
    
    // count of completed normal tasks
    $qF1 = new QueryFilter(TaskWrapperDisplay::TASK_TYPE, DTaskTypes::NORMAL, "=");
    $qF2 = new QueryFilter(TaskWrapperDisplay::TASK_IS_ARCHIVED, 0, "=");
    
    $jF = new JoinFilter(Factory::getChunkFactory(), TaskWrapperDisplay::TASK_ID, Chunk::TASK_ID);
    
    $agg1 = new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM, Factory::getChunkFactory());
    $agg2 = new Aggregation(Chunk::SKIP, Aggregation::SUM, Factory::getChunkFactory());
    $results = Factory::getTaskWrapperDisplayFactory()->joinAggregationFilter([Factory::FILTER => [$qF1, $qF2]], $jF, [$agg1, $agg2]);
    
    $completed = 0;
    for ($i = 0; $i < sizeof($results[Factory::getTaskWrapperDisplayFactory()->getModelName()]); $i++) {
      $taskWrapperDisplay = $results[Factory::getTaskWrapperDisplayFactory()->getModelName()][$i];
      $checkpointSum = $results[$agg1->getName()][$i];
      $skipSum = $results[$agg2->getName()][$i];
      if ($taskWrapperDisplay->getKeyspace() > 0 && $checkpointSum - $skipSum == $taskWrapperDisplay->getKeyspace()) {
        $completed++;
      }
    }
    $data["completedTasks"] = $completed;
    
    // count of completed supertasks
    $qF1 = new QueryFilter(TaskWrapper::TASK_TYPE, DTaskTypes::SUPERTASK, "=");
    $qF2 = new QueryFilter(TaskWrapper::IS_ARCHIVED, 0, "=");
    $taskWrapperIds = Factory::getTaskWrapperFactory()->columnFilter([Factory::FILTER => [$qF1, $qF2]], TaskWrapper::TASK_WRAPPER_ID);
    
    $qF = new ContainFilter(Task::TASK_WRAPPER_ID, $taskWrapperIds);
    $jF = new JoinFilter(Factory::getChunkFactory(), Task::TASK_ID, Chunk::TASK_ID, joinType: JoinFilter::LEFT);
    
    $agg1 = new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM, Factory::getChunkFactory());
    $agg2 = new Aggregation(Chunk::SKIP, Aggregation::SUM, Factory::getChunkFactory());
    $results = Factory::getTaskFactory()->joinAggregationFilter([Factory::FILTER => $qF], $jF, [$agg1, $agg2]);
    
    $completed = [];
    foreach ($taskWrapperIds as $taskWrapperId) {
      $completed[$taskWrapperId] = true;
    }
    
    for ($i = 0; $i < sizeof($results[Factory::getTaskFactory()->getModelName()]); $i++) {
      $task = $results[Factory::getTaskFactory()->getModelName()][$i];
      $checkpointSum = $results[$agg1->getName()][$i];
      $skipSum = $results[$agg2->getName()][$i];
      if ($task->getKeyspace() == 0 || $checkpointSum - $skipSum < $task->getKeyspace()) {
        $completed[$task->getTaskWrapperId()] = false;
      }
    }
    
    $data["completedSupertasks"] = array_sum($completed);
    
    $ret = self::createJsonResponse(data: $data);
    
    $body = $response->getBody();
    $body->write($this->ret2json($ret));
    
    return $response->withStatus(200)
      ->withHeader("Content-Type", 'application/vnd.api+json;');
  }
  
  static public function register($app): void {
    $baseUri = GetCompletedCountHelperAPI::getBaseUri();
    
    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, [self::class, 'handleGet']);
  }
}
