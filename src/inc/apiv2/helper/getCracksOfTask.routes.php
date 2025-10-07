<?php

use DBA\Chunk;
use DBA\ContainFilter;
use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DBA\Factory;
use DBA\Hash;
use DBA\QueryFilter;
use DBA\Task;
use Middlewares\Utils\HttpErrorException;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class getCracksOfTaskHelper extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/getCracksOfTask";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [Hash::PERM_READ, Task::PERM_READ];
  }
  
  public static function getResponse(): null {
    return null;
  }
  
  
  #[NoReturn] public function actionPost(array $data): object|array|null {
    assert(False, "getCracksOfTask has no POST");
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
   * Endpoint to get the cracked hashes of a certain task
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws HttpErrorException
   */
  public function handleGet(Request $request, Response $response): Response {
    $this->preCommon($request);
    $task = Factory::getTaskFactory()->get($_GET['task']);
    if ($task == null) {
      throw new HttpError("No task has been found with provided task id");
    }
    $hashlists = Util::checkSuperHashlist(Factory::getHashlistFactory()->get(Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId())->getHashlistId()));
    if ($hashlists[0]->getFormat() == DHashlistFormat::PLAIN) {
      $hashFactory = Factory::getHashFactory();
    }
    else {
      $hashFactory = Factory::getHashBinaryFactory();
    }
    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);
    $chunkIds = array();
    foreach ($chunks as $chunk) {
      $chunkIds[] = $chunk->getId();
    }
    $queryFilters[] = new ContainFilter(Hash::CHUNK_ID, $chunkIds);
    $queryFilters[] = new QueryFilter(Hash::IS_CRACKED, 1, "=");
    $hashes = $hashFactory->filter([Factory::FILTER => $queryFilters]);
    $converted = [];
    
    foreach ($hashes as $hash) {
      $converted[] = self::obj2Resource($hash);
    }
    $ret = self::createJsonResponse(data: $converted);
    
    $body = $response->getBody();
    $body->write($this->ret2json($ret));
    
    return $response->withStatus(200)
      ->withHeader("Content-Type", 'application/vnd.api+json;');
  }
  
  static public function register($app): void {
    $baseUri = getCracksOfTaskHelper::getBaseUri();
    
    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, "getCracksOfTaskHelper:handleGet");
  }
}

getCracksOfTaskHelper::register($app);