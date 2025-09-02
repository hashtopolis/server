<?php

use DBA\Chunk;
use DBA\Factory;
use DBA\QueryFilter;
use JetBrains\PhpStorm\NoReturn;
use Middlewares\Utils\HttpErrorException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class TaskExtraDetailsHelper extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/taskExtraDetails";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [];
  }
  
  public function getFormFields(): array {
    return [];
  }
  
  /**
   * @throws HttpErrorException
   */
  public function handleGet(Request $request, Response $response): Response {
    $this->preCommon($request);

    $taskId = $request->getQueryParams()['task'];
    if ($taskId == null) {
      throw new HttpErrorException("No task query param has been provided");
    }
    $taskId = intval($taskId);
    if ($taskId === 0) {
      throw new HttpErrorException("No valid integer provided as task");
    }

    $qF = new QueryFilter(Chunk::TASK_ID, $taskId, "=");
    $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);
    $currentSpeed = 0;
    $cProgress = 0;
    foreach ($chunks as $chunk) {
      $cProgress += $chunk->getCheckpoint() - $chunk->getSkip();
      if (time() - max($chunk->getSolveTime(), $chunk->getDispatchTime()) < SConfig::getInstance()->getVal(DConfig::CHUNK_TIMEOUT) && $chunk->getProgress() < 10000) {
        $currentSpeed += $chunk->getSpeed();
    }
      
    $timeChunks = $chunks;
    usort($timeChunks, "Util::compareChunksTime");
    $timeSpent = 0;
    $current = 0;
    foreach ($timeChunks as $c) {
      if ($c->getDispatchTime() > $current) {
        $timeSpent += $c->getSolveTime() - $c->getDispatchTime();
        $current = $c->getSolveTime();
      }
      else if ($c->getSolveTime() > $current) {
        $timeSpent += $c->getSolveTime() - $current;
        $current = $c->getSolveTime();
      }
    }
    $task = Factory::getTaskFactory()->get($taskId);
    $estimatedTime = round($timeSpent / ($cProgress / $task->getKeyspace()) - $timeSpent);
    $currentSpeed = ($currentSpeed > 0) ? Util::nicenum($currentSpeed, 10000, 1000) . "H/s" : 0;
    $responseObject = [
      "estimatedTime" => $estimatedTime,
      "timeSpent" => $timeSpent,
      "currentSpeed" => $currentSpeed,
    ];
      
    return self::getMetaResponse($responseObject, $request, $response);
    }
  }
  
  #[NoReturn] public function actionPost($data): object|array|null {
    assert(False, "TaskExtraDetails has no POST");
  }
  
  static public function register($app): void {
    $baseUri = TaskExtraDetailsHelper::getBaseUri();
    
    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, "TaskExtraDetailsHelper:handleGet");
  }
  
  /**
   * getAccessGroups is different because it returns via another function
   */
  public static function getResponse(): array|string|null {
    return null;
  }
}

TaskExtraDetailsHelper::register($app);
