<?php

use DBA\ContainFilter;
use DBA\Factory;
use DBA\Hash;
use DBA\Hashlist;
use DBA\JoinFilter;
use DBA\LikeFilterInsensitive;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class SearchHashesHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/searchHashes";
  }
  
  public static function getAvailableMethods(): array {
    return ['POST'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [Hashlist::PERM_READ, Hash::PERM_READ];
  }
  
  public function getFormFields(): array {
    return [
      "searchData" => ["type" => "str"],  # base64 encoded search input
      "separator" => ['type' => 'str'],
      "isSalted" => ['type' => 'bool'],
    ];
  }
  
  public static function getResponse(): array {
    return [
      ["found" => false,
       "query" => "12345678",
      ],
      ["found" => true,
       "query" => "54321",
       "matches" => [[
                       "type" => "hash",
                       "id" => 552,
                       "attributes" => [
                         "hashlistId" => 5,
                         "hash" => "7682543218768",
                         "salt" => "",
                         "plaintext" => "",
                         "timeCracked" => 0,
                         "chunkId" => null,
                         "isCracked" => false,
                         "crackPos" => 0
                       ],
                       "links" => [
                         "self" => "/api/v2/ui/hashes/552"
                       ],
                       "relationships" => [
                         "chunk" => [
                           "links" => [
                             "self" => "/api/v2/ui/hashes/552/relationships/chunk",
                             "related" => "/api/v2/ui/hashes/552/chunk"
                           ]
                         ],
                         "hashlist" => [
                           "links" => [
                             "self" => "/api/v2/ui/hashes/552/relationships/hashlist",
                             "related" => "/api/v2/ui/hashes/552/hashlist"
                           ]
                         ]
                       ]
                     ],
                     [
                       "type" => "hash",
                       "id" => 1,
                       "attributes" => [
                         "hashlistId" => 5,
                         "hash" => "54321768671",
                         "salt" => "",
                         "plaintext" => "",
                         "timeCracked" => 0,
                         "chunkId" => null,
                         "isCracked" => false,
                         "crackPos" => 0
                       ],
                       "links" => [
                         "self" => "/api/v2/ui/hashes/1"
                       ],
                       "relationships" => [
                         "chunk" => [
                           "links" => [
                             "self" => "/api/v2/ui/hashes/1/relationships/chunk",
                             "related" => "/api/v2/ui/hashes/1/chunk"
                           ]
                         ],
                         "hashlist" => [
                           "links" => [
                             "self" => "/api/v2/ui/hashes/1/relationships/hashlist",
                             "related" => "/api/v2/ui/hashes/1/hashlist"
                           ]
                         ]
                       ]
                     ],
       ],
      ]
    ];
  }
  
  /**
   * Endpoint to search for hashes in accessible hashlists.
   * @throws HttpError
   */
  public function actionPost($data): object|array|null {
    $search = base64_decode($data['searchData'], true);
    $isSalted = $data['isSalted'];
    $separator = $data['separator'];
    
    if (strlen($search) == 0) {
      throw new HttpError("Search query cannot be empty!");
    }
    else if ($search === false || mb_check_encoding($search, "UTF-8") == false) {
      throw new HttpError("Search query is not valid base64!");
    }
    else if ($isSalted && strlen($separator) == 0) {
      throw new HttpError("Salt separator cannot be empty!");
    }
    
    $search = str_replace("\r\n", "\n", $search);
    $search = explode("\n", $search);
    $resultEntries = array();
    $userHashlists = HashlistUtils::getHashlists(self::getCurrentUser(), false);
    $userHashlists += HashlistUtils::getHashlists(self::getCurrentUser(), true);
    foreach ($search as $searchEntry) {
      if (strlen($searchEntry) == 0) {
        continue;
      }
      
      // test if hash contains salt
      if ($isSalted) {
        $split = explode($separator, $searchEntry);
        $hash = $split[0];
        unset($split[0]);
        $salt = implode($separator, $split);
      }
      else {
        $hash = $searchEntry;
        $salt = "";
      }
      
      // TODO: add option to select if exact match or like match
      
      $filters = array();
      $filters[] = new LikeFilterInsensitive(Hash::HASH, "%" . $hash . "%");
      $filters[] = new ContainFilter(Hash::HASHLIST_ID, Util::arrayOfIds($userHashlists), Factory::getHashFactory());
      if (strlen($salt) > 0) {
        $filters[] = new QueryFilter(Hash::SALT, $salt, "=");
      }
      $jF = new JoinFilter(Factory::getHashlistFactory(), Hash::HASHLIST_ID, Hashlist::HASHLIST_ID);
      $joined = Factory::getHashFactory()->filter([Factory::FILTER => $filters, Factory::JOIN => $jF]);
      
      $qF1 = new LikeFilterInsensitive(Hash::PLAINTEXT, "%" . $searchEntry . "%");
      $qF2 = new ContainFilter(Hash::HASHLIST_ID, Util::arrayOfIds($userHashlists), Factory::getHashFactory());
      $joined2 = Factory::getHashFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::JOIN => $jF]);
      /** @var Hash[] $hashes */
      $hashes = $joined2[Factory::getHashFactory()->getModelName()];
      for ($i = 0; $i < sizeof($hashes); $i++) {
        $joined[Factory::getHashFactory()->getModelName()][] = $joined2[Factory::getHashFactory()->getModelName()][$i];
        $joined[Factory::getHashlistFactory()->getModelName()][] = $joined2[Factory::getHashlistFactory()->getModelName()][$i];
      }
      
      $resultEntry = [];
      /** @var Hash[] $hashes */
      $hashes = $joined[Factory::getHashFactory()->getModelName()];
      if (empty($hashes)) {
        $resultEntry["found"] = false;
        $resultEntry["query"] = $searchEntry;
      }
      else {
        $resultEntry["found"] = true;
        $resultEntry["query"] = $searchEntry;
        $matches = [];
        for ($i = 0; $i < sizeof($hashes); $i++) {
          /** @var Hash $hash */
          $hash = $joined[Factory::getHashFactory()->getModelName()][$i];
          $hashlist = $joined[Factory::getHashlistFactory()->getModelName()][$i];
          $hashResource = self::obj2Resource($hash);
          $hashlistResource = self::obj2Resource($hashlist);
          $hashResource["attributes"]["hashlist"] = $hashlistResource["attributes"];

          $matches[] = $hashResource;
        }
        $resultEntry["matches"] = $matches;
      }
      $resultEntries[] = $resultEntry;
    }
    return $resultEntries;
  }
}

use Slim\App;
/** @var App $app */
SearchHashesHelperAPI::register($app);