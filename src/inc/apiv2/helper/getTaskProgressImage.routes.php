<?php

use DBA\Chunk;
use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DBA\Factory;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Task;
use Middlewares\Utils\HttpErrorException;
use Slim\Exception\HttpNotFoundException;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class GetTaskProgressImageHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/getTaskProgressImage";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [Task::PERM_READ];
  }
  
  /**
   * getfile is different because it returns actual binary data.
   */
  public static function getResponse(): null {
    return null;
  }
  
  
  #[NoReturn] public function actionPost(array $data): object|array|null {
    assert(False, "GetTaskProgressImage has no POST");
  }
  
  /**
   * Description of get params for swagger.
   */
  public function getParamsSwagger(): array {
    return [
      [
        "in" => "query",
        "name" => "supertask",
        "schema" => [
          "type" => "integer",
          "format" => "int32"
        ],
        "required" => false,
        "example" => 1,
        "description" => "The ID of the supertask where you want to create the progress image of."
      ],
      [
        "in" => "query",
        "name" => "task",
        "schema" => [
          "type" => "integer",
          "format" => "int32"
        ],
        "required" => false,
        "example" => 1,
        "description" => "The ID of the task where you want to create the progress image of."
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
    $task_id = $request->getQueryParams()['task'];
    $supertask_id = $request->getQueryParams()['supertask'];

    //check if task exists and get information
    if ($task_id) {
      $task = Factory::getTaskFactory()->get($task_id);
      if ($task == null) {
        throw new HttpNotFoundException($request, "Invalid task");
      }
      $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
      if ($taskWrapper == null) {
        throw new HttpError("Inconsistency on task!");
      }
    }
    else if ($supertask_id) {
      $taskWrapper = Factory::getTaskWrapperFactory()->get($supertask_id);
      if ($taskWrapper == null) {
        throw new HttpError("Invalid task wrapper!");
      }
    } else {
      throw new HttpError("No task or super task has been provided");
    }
      
    $size = array(1500, 32);

    //create image
    $image = imagecreatetruecolor($size[0], $size[1]);
    imagesavealpha($image, true);

    //set colors
    $transparency = imagecolorallocatealpha($image, 0, 0, 0, 127);
    $yellow = imagecolorallocate($image, 255, 255, 0);
    $red = imagecolorallocate($image, 255, 0, 0);
    $grey = imagecolorallocate($image, 192, 192, 192);
    $green = imagecolorallocate($image, 0, 255, 0);
    $blue = imagecolorallocate($image, 60, 60, 245);

    //prepare image
    imagefill($image, 0, 0, $transparency);

    if ($taskWrapper->getTaskType() == DTaskTypes::SUPERTASK && isset($supertask_id)) {
      // handle supertask progress drawing here
      $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
      $oF = new OrderFilter(Task::PRIORITY, "DESC");
      $tasks = Factory::getTaskFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]);
      $numTasks = sizeof($tasks);
      for ($i = 0; $i < sizeof($tasks); $i++) {
        $qF = new QueryFilter(Chunk::TASK_ID, $tasks[$i]->getId(), "=");
        $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);
        $progress = 0;
        foreach ($chunks as $chunk) {
          $progress += $chunk->getCheckpoint();
        }
        $qF = new QueryFilter(Chunk::TASK_ID, $tasks[$i]->getId(), "=");
        $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);
        $cracked = 0;
        foreach ($chunks as $chunk) {
          $cracked += $chunk->getCracked();
        }
        if ($cracked > 0) {
          imagefilledrectangle($image, $i * $size[0] / $numTasks, 0, ($i + 1) * $size[0] / $numTasks, $size[1] - 1, $green);
        }
        else if ($tasks[$i]->getKeyspace() > 0 && $progress >= $tasks[$i]->getKeyspace()) {
          imagefilledrectangle($image, $i * $size[0] / $numTasks, 0, ($i + 1) * $size[0] / $numTasks, $size[1] - 1, $blue);
        }
        else if ($tasks[$i]->getKeyspace() > 0 && $progress > 0) {
          imagefilledrectangle($image, $i * $size[0] / $numTasks, 0, ($i + 1) * $size[0] / $numTasks, $size[1] - 1, $yellow);
        }
        else {
          imagefilledrectangle($image, $i * $size[0] / $numTasks, 0, ($i + 1) * $size[0] / $numTasks, $size[1] - 1, $grey);
        }
      }
    }
    else {
      $progress = $task->getKeyspaceProgress();
      $keyspace = max($task->getKeyspace(), 1);
      
      //load chunks
      $qF = new QueryFilter(Task::TASK_ID, $task->getId(), "=");
      $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);
      foreach ($chunks as $chunk) {
        if ($task->getUsePreprocessor() == 1 && $task->getKeyspace() <= 0) {
          continue;
        }
        $start = floor(($size[0] - 1) * $chunk->getSkip() / $keyspace);
        $end = floor(($size[0] - 1) * ($chunk->getSkip() + $chunk->getLength()) / $keyspace) - 1;
        //division by 10000 is required because rprogress is saved in percents with two decimals
        $current = floor(($size[0] - 1) * ($chunk->getSkip() + $chunk->getLength() * $chunk->getProgress() / 10000) / $keyspace) - 1;
        
        if ($current > $end) {
          $current = $end;
        }
        
        if ($end - $start < 3) {
          if ($chunk->getState() >= 6) {
            imagefilledrectangle($image, $start, 0, $end, $size[1] - 1, $red);
          }
          else if ($chunk->getCracked() > 0) {
            imagefilledrectangle($image, $start, 0, $end, $size[1] - 1, $green);
          }
          else {
            imagefilledrectangle($image, $start, 0, $end, $size[1] - 1, $yellow);
          }
        }
        else {
          if ($chunk->getState() >= 6) {
            imagerectangle($image, $start, 0, $end, ($size[1] - 1), $red);
          }
          else {
            imagerectangle($image, $start, 0, $end, ($size[1] - 1), $grey);
          }
          if ($chunk->getCracked() > 0) {
            imagefilledrectangle($image, $start + 1, 1, $current - 1, $size[1] - 2, $green);
          }
          else {
            imagefilledrectangle($image, $start + 1, 1, $current - 1, $size[1] - 2, $yellow);
          }
        }
      }
    }

    //send image data to output
    ob_start($image);
    imagepng($image);
    $imageData = ob_get_clean();
    imagedestroy($image);
    $response->getBody()->write($imageData);
    return $response->withStatus(200)
      ->withHeader("Content-Type", "image/png")
      ->withHeader("Cache-Control", "no-cache");
  }
  
  static public function register($app): void {
    $baseUri = GetTaskProgressImageHelperAPI::getBaseUri();
    
    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, "GetTaskProgressImageHelperAPI:handleGet");
  }
}

GetTaskProgressImageHelperAPI::register($app);