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
   * Returns a map of date -> crack count for every day from January 1st of the
   * current year up to and including today.
   */
  public function handleGet(Request $request, Response $response): Response {
    $this->preCommon($request);

    $yearStart = mktime(0, 0, 0, 1, 1, (int) date('Y'));
    $now = time();

    $filters = [
      new QueryFilter(Hash::IS_CRACKED, 1, "="),
      new QueryFilter(Hash::TIME_CRACKED, $yearStart, ">="),
      new QueryFilter(Hash::TIME_CRACKED, $now, "<="),
    ];

    $counts = [];

    foreach (Factory::getHashFactory()->filter([Factory::FILTER => $filters]) as $hash) {
      $day = date('Y-m-d', $hash->getTimeCracked());
      $counts[$day] = ($counts[$day] ?? 0) + 1;
    }

    $binaryFilters = [
      new QueryFilter(HashBinary::IS_CRACKED, 1, "="),
      new QueryFilter(HashBinary::TIME_CRACKED, $yearStart, ">="),
      new QueryFilter(HashBinary::TIME_CRACKED, $now, "<="),
    ];

    foreach (Factory::getHashBinaryFactory()->filter([Factory::FILTER => $binaryFilters]) as $hash) {
      $day = date('Y-m-d', $hash->getTimeCracked());
      $counts[$day] = ($counts[$day] ?? 0) + 1;
    }

    ksort($counts);

    $body = $response->getBody();
    $body->write(json_encode($counts, JSON_THROW_ON_ERROR));

    return $response->withStatus(200)
      ->withHeader("Content-Type", 'application/json');
  }

  public static function register($app): void {
    $baseUri = self::getBaseUri();

    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, self::class . ":handleGet");
  }
}
