<?php

namespace Hashtopolis\inc\apiv2\helper;

use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Hash;
use Hashtopolis\dba\models\HashBinary;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\QueryFilter;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetCracksPerDayHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/getCracksPerDay";
  }

  public static function getAvailableMethods(): array {
    return ['GET'];
  }

  public function getRequiredPermissions(string $method): array {
    return [Hashlist::PERM_READ, Hash::PERM_READ];
  }

  public static function getResponse(): null {
    return null;
  }

  public function actionPost(array $data): object|array|null {
    throw new HttpError("getCracksPerDay has no POST");
  }

  public function getParamsSwagger(): array {
    return [];
  }

  /**
    * Returns a map of date -> crack count for days with at least one crack from
    * January 1st of the current year up to and including today. Days with no
    * cracks are omitted from the response.
   */
  public function handleGet(Request $request, Response $response): Response {
    $this->preCommon($request);

    $yearStart = mktime(0, 0, 0, 1, 1, (int) date('Y'));
    $counts = Factory::getHashFactory()->filterCracksOnTimestamp($yearStart);

    $ret = self::createJsonResponse(meta: $counts);
    
    $body = $response->getBody();
    $body->write($this->ret2json($ret));

    return $response->withStatus(200)
      ->withHeader("Content-Type", 'application/vnd.api+json;');
  }

  public static function register($app): void {
    $baseUri = self::getBaseUri();

    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, self::class . ":handleGet");
  }
}
