<?php

namespace Hashtopolis\inc\apiv2\helper;

use Exception;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\inc\HTException;
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Hash;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\Task;
use Middlewares\Utils\HttpErrorException;
use Hashtopolis\inc\Util;

class GetCracksOfTaskHelper extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/getCracksOfTask";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [Hashlist::PERM_READ, Hash::PERM_READ, Task::PERM_READ];
  }
  
  public static function getResponse(): string {
    return "Hash";
  }
  
  
  /**
   * @throws HttpErrorException
   */
  public function actionPost(array $data): object|array|null {
    throw new HttpErrorException("getCracksOfTask has no POST");
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
    $task = Factory::getTaskFactory()->get($request->getQueryParams()['task']);
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
    $queryFilters[] = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=", Factory::getChunkFactory());
    $queryFilters[] = new QueryFilter(Hash::IS_CRACKED, 1, "=");
    $jF = new JoinFilter(Factory::getChunkFactory(), Hash::CHUNK_ID, Chunk::CHUNK_ID);
    $joined = $hashFactory->filter([Factory::FILTER => $queryFilters, Factory::JOIN => $jF]);
    $converted = [];
    foreach ($joined[$hashFactory->getModelName()] as $hash) {
      $converted[] = self::obj2Resource($hash);
    }
    $ret = self::createJsonResponse(data: $converted);
    
    $body = $response->getBody();
    $body->write($this->ret2json($ret));
    
    return $response->withStatus(200)
      ->withHeader("Content-Type", 'application/vnd.api+json;');
  }
  
  static public function register($app): void {
    $baseUri = GetCracksOfTaskHelper::getBaseUri();
    
    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, "Hashtopolis\\inc\\apiv2\\helper\\GetCracksOfTaskHelper:handleGet");
  }
}
