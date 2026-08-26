<?php

namespace Hashtopolis\inc\apiv2\helper;

use Exception;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\models\Hash;
use Hashtopolis\dba\models\HashBinary;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\Util;
use Hashtopolis\inc\utils\AccessUtils;
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
   * @param array $data
   * @return AbstractModel|array|null
   * @throws HttpError
   */
  public function actionPost(array $data): AbstractModel|array|null {
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
    
    /*
     * Cracks are only reported for hashlists within the access groups of the requesting user,
     * the same restriction HashAPI applies to its listing.
     */
    $accessGroups = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getCurrentUser()));
    
    $start = time() - 3600 * 24 * 365;
    $qF1 = new QueryFilter(Hash::IS_CRACKED, 1, "=");
    $qF2 = new QueryFilter(Hash::TIME_CRACKED, $start, ">");
    $qF3 = new ContainFilter(Hashlist::ACCESS_GROUP_ID, $accessGroups, Factory::getHashlistFactory());
    
    $hashJF = new JoinFilter(Factory::getHashlistFactory(), Hash::HASHLIST_ID, Hashlist::HASHLIST_ID);
    $binaryJF = new JoinFilter(Factory::getHashlistFactory(), HashBinary::HASHLIST_ID, Hashlist::HASHLIST_ID);
    
    $counts = Factory::getHashFactory()->columnTimeseriesFilter([Factory::FILTER => [$qF1, $qF2, $qF3], Factory::JOIN => [$hashJF]], Hash::TIME_CRACKED);
    $counts2 = Factory::getHashBinaryFactory()->columnTimeseriesFilter([Factory::FILTER => [$qF1, $qF2, $qF3], Factory::JOIN => [$binaryJF]], Hash::TIME_CRACKED);
    foreach ($counts2 as $key => $value) {
      $counts[$key] = ($counts[$key] ?? 0) + $value;
    }
    
    $ret = self::createJsonResponse(data: $counts);
    if(empty($counts)) {
      $ret["data"] = new stdClass();
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
    $app->get($baseUri, [self::class, 'handleGet']);
  }
}
