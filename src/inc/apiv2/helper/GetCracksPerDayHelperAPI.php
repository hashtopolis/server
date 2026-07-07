<?php

namespace Hashtopolis\inc\apiv2\helper;

use Exception;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Hash;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\QueryFilter;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use stdClass;

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
  
  /**
   * @throws HttpError
   */
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
   * @throws Exception
   */
  public function handleGet(Request $request, Response $response): Response {
    $this->preCommon($request);

    $start = time() - 3600 * 24 * 365;
    $qF1 = new QueryFilter(Hash::IS_CRACKED, 1, "=");
    $qF2 = new QueryFilter(Hash::TIME_CRACKED, $start, ">");
    $counts = Factory::getHashFactory()->columnTimeseriesFilter([Factory::FILTER => [$qF1, $qF2]], Hash::TIME_CRACKED);
    $counts2 = Factory::getHashBinaryFactory()->columnTimeseriesFilter([Factory::FILTER => [$qF1, $qF2]], Hash::TIME_CRACKED);
    foreach ($counts2 as $key => $value) {
      $counts[$key] = ($counts[$key] ?? 0) + $value;
    }
    
    $ret = self::createJsonResponse(meta: $counts);
    if(empty($counts)) {
      $ret["meta"] = new stdClass();
    }
    
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
