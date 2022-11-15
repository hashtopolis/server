<?php


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpForbiddenException;

use DBA\Hashlist;
use DBA\Factory;
use Middlewares\Utils\HttpErrorException;

require_once(dirname(__FILE__) . "/../load.php");


function obj2Array(mixed $obj) {  
  // Convert values to JSON supported types
  $features = $obj->getFeatures();
  $kv = $obj->getKeyValueDict();

  $item = [];
  foreach ($features as $NAME => $FEATURE) {
    if ($FEATURE['type'] == 'bool') {
      $item[$NAME] = ($kv[$NAME] == 1) ? True : False;
    } else {
      $item[$NAME] = $kv[$NAME];
    }
  }
  return $item;
}


function hashlist2Array(Hashlist $hashlist, array $expand) {
  $item = obj2Array($hashlist);

  if (in_array('accessGroup', $expand, true)) {
    $obj = Factory::getAccessGroupFactory()->get($item['accessGroupId']);
    $item['accessGroup'] = obj2Array($obj);
  }
  if (in_array('hashType', $expand, true)) {
    $obj = Factory::getHashTypeFactory()->get($item['hashTypeId']);
    $item['hashType'] = obj2Array($obj);
  }

  return $item;
}


function hashlist2JSON(Hashlist $hashlist) {
  $item = hashlist2Array($hashlist, []);
  return json_encode($item, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

$app->group("/api/v2/ui/hashlists", function (RouteCollectorProxy $group) { 
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });


    $group->get('', function (Request $request, Response $response, array $args): Response {
        $userId = $request->getAttribute(('userId'));
        $user = UserUtils::getUser($userId);

        $expandables = ["accessGroup", "hashType"];

        $startAt = intval($request->getQueryParams()['startsAt'] ?? 0);
        $maxResults = intval($request->getQueryParams()['maxResults'] ?? 5);

        $foo = $request->getQueryParams();

        // Check for valid expand parameters
        $expandable = $expandables;
        if (array_key_exists('expand', $request->getQueryParams())) {
          $expands = preg_split("/[,\ ]+/", $request->getQueryParams()['expand']);

          foreach($expands as $expand) {
            if (($key = array_search($expand, $expandable)) !== false){
              unset($expandable[$key]);
            }
            else {
              throw new HTException("Parameter '" . $expand . "' is not valid expand key (valid keys are: " . join(", ", array_values($expandables)) . ")");
            }
          }
        }

        // TODO: Implement filtering support
        $filter = preg_split("/[,\ ]+/", ($request->getQueryParams()['filter'] ?? ""));


        // TODO: Optimize code, should only fetch subsection of database
        $hashlists = HashlistUtils::getHashlists($user);
        $lists = [];
        foreach ($hashlists as $hashlist) {
            $lists[] = hashlist2Array($hashlist, $expands);
        }

        // TODO: Implement actual expanding
        $total = count($hashlists);
        $ret = [
            "_expandable" => join(",", $expandable),
            "startAt" => $startAt,
            "maxResults" => $maxResults,
            "total" => $total,
            "isLast" => ($total <= ($startAt + $maxResults)),
            "values" => array_slice($lists, $startAt, $maxResults)
        ];

        $body = $response->getBody();
        $body->write(json_encode($ret, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        
        return $response->withStatus(201)
        ->withHeader("Content-Type", "application/json");
    });

    $group->post('', function (Request $request, Response $response, array $args): Response {
        $userId = $request->getAttribute(('userId'));
        $user = UserUtils::getUser($userId);

        if(!AccessControl::getInstance($user)->hasPermission(DAccessControl::CREATE_HASHLIST_ACCESS)) {
            throw new HttpForbiddenException($request, "No '" . DAccessControl::getDescription(DAccessControl::CREATE_HASHLIST_ACCESS) . "' permission");
        }

        $QUERY = $request->getParsedBody();
        $features = Hashlist::getFeatures();
        // Ensure debugging response lists are in sorted order
        ksort($features);

        // Find keys which are invalid
        foreach($QUERY as $NAME => $VALUE)  {
          if (!array_key_exists($NAME, $features)) {
            throw new HTException("Parameter '" . $NAME . "' is not valid input key (valid keys are: " . join(", ", array_keys($features)) . ")");

          }
        }

        // Find out about mandatory keys which are not provided
        $missingKeys = [];
        foreach ($features as $NAME => $FEATURE) {
          // Optional keys are not required entities
          if ($FEATURE['null'] == True) {
            continue;
          }
          if (!array_key_exists($NAME, $QUERY)) {
            $missingKeys[] = $NAME;
          }
        };
        if (count($missingKeys) > 0) {
          throw new HTException("Required parameter(s) '" .  join(", ", $missingKeys) . "' not specified");
        };


        // Validate incoming data
        foreach($QUERY as $KEY => $VALUE) {
          // Ensure type is correct
          if ($features[$KEY]['type'] == 'bool') {
            if (is_bool($VALUE) == False) {
              throw new HttpErrorException("Key '$KEY' is not of type boolean");            
            }
          } elseif (str_starts_with($features[$KEY]['type'], 'int')) {
            // TODO: int32, int64 range validation
            if (is_integer($VALUE) == False) {
              throw new HttpErrorException("Key '$KEY' is not of type integer");
            }
          } elseif (str_starts_with($features[$KEY]['type'], 'str')) {
            if (is_string($VALUE) == False) {
              throw new HttpErrorException("Key '$KEY' is not of type string");
            }
            // TODO: Length validation
          }
        }

        $hashlist = HashlistUtils::createHashlist(
            $QUERY["hashlistName"],
            $QUERY[UQueryHashlist::HASHLIST_IS_SALTED],
            $QUERY[UQueryHashlist::HASHLIST_IS_SECRET],
            $QUERY[UQueryHashlist::HASHLIST_HEX_SALTED],
            $QUERY[UQueryHashlist::HASHLIST_SEPARATOR],
            $QUERY[UQueryHashlist::HASHLIST_FORMAT],
            $QUERY[UQueryHashlist::HASHLIST_HASHTYPE_ID],
            $QUERY[UQueryHashlist::HASHLIST_SEPARATOR],
            $QUERY[UQueryHashlist::HASHLIST_ACCESS_GROUP_ID],
            "paste",
            ['hashfield' => base64_decode($QUERY[UQueryHashlist::HASHLIST_DATA])],
            [],
            $user,
            $QUERY[UQueryHashlist::HASHLIST_USE_BRAIN],
            $QUERY[UQueryHashlist::HASHLIST_BRAIN_FEATURES]
          );


        $body = $response->getBody();
        $body->write(hashlist2JSON($hashlist));

        return $response->withStatus(201)
            ->withHeader("Content-Type", "application/json");
    });
});


$app->group("/api/v2/ui/hashlists/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', function (Request $request, Response $response, array $args): Response {
        $userId = $request->getAttribute(('userId'));
        $user = UserUtils::getUser($userId);

        $hashlistId = $args['id'];
        try {
            $hashlist = HashlistUtils::getHashlist($hashlistId);
        } catch (HTException $ex) {
            throw new HttpNotFoundException($request, $ex->getMessage());
        }
        if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
          throw new HttpForbiddenException($request, "No access to hashlist!");
        }

        $body = $response->getBody();
        $body->write(hashlist2JSON($hashlist));

        return $response->withStatus(201)
            ->withHeader("Content-Type", "application/json");
    });


    $group->patch('', function (Request $request, Response $response, array $args): Response {
        $userId = $request->getAttribute(('userId'));
        $user = UserUtils::getUser($userId);

        if(!AccessControl::getInstance($user)->hasPermission(DAccessControl::MANAGE_HASHLIST_ACCESS)) {
            throw new HttpForbiddenException($request, "No '" . DAccessControl::getDescription(DAccessControl::MANAGE_HASHLIST_ACCESS) . "' permission");
        }

        $hashlistId = $args['id'];
        try {
            $hashlist = HashlistUtils::getHashlist($hashlistId);
        } catch (HTException $ex) {
            throw new HttpNotFoundException($request, $ex->getMessage());
        }
        if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
          throw new HttpForbiddenException($request, "No access to hashlist!");
        }

        $features = $hashlist->getFeatures();

        $data = $request->getParsedBody();
        // Validate incoming data
        foreach($data as $KEY => $VALUE) {
          // Ensure key is a regular string
          if (is_string($KEY) == False) {
            throw new HttpErrorException("Key '$KEY' invalid");
          }
          // Ensure key exists in target array
          if (array_key_exists($KEY, $features) == False) {
            throw new HttpErrorException("Key '$KEY' does not exists!");
          }

          // Ensure key can be updated 
          if ($features[$KEY]['read_only'] == True) {
            throw new HttpErrorException("Key '$KEY' is immutable");
          }

          // Ensure type is correct
          if ($features[$KEY]['type'] == 'bool') {
            if (is_bool($VALUE) == False) {
              throw new HttpErrorException("Key '$KEY' is not of type boolean");            
            }
          } elseif (str_starts_with($features[$KEY]['type'], 'int')) {
            // TODO: int32, int64 range validation
            if (is_integer($VALUE) == False) {
              throw new HttpErrorException("Key '$KEY' is not of type integer");
            }
          } elseif (str_starts_with($features[$KEY]['type'], 'str')) {
            if (is_string($VALUE) == False) {
              throw new HttpErrorException("Key '$KEY' is not of type string");
            }
            // TODO: Length validation
          }
        }

        // Apply changes 
        foreach($data as $KEY => $VALUE) {
          // Sanity values
          if (str_starts_with($features[$KEY]['type'], 'str')) {
            $val = htmlentities($data[$KEY], ENT_QUOTES, "UTF-8");
          } else {
            $val = $VALUE;
          }
          Factory::getHashlistFactory()->set($hashlist, $KEY, $val);
        }

        // Return updated object
        $hashlist = HashlistUtils::getHashlist($hashlistId);

        $body = $response->getBody();
        $body->write(hashlist2JSON($hashlist));

        return $response->withStatus(201)
            ->withHeader("Content-Type", "application/json");
    });

    $group->delete('', function (Request $request, Response $response, array $args): Response {
        $userId = $request->getAttribute(('userId'));
        $user = UserUtils::getUser($userId);

        if(!AccessControl::getInstance($user)->hasPermission(DAccessControl::MANAGE_HASHLIST_ACCESS)) {
            throw new HttpForbiddenException($request, "No '" . DAccessControl::getDescription(DAccessControl::MANAGE_HASHLIST_ACCESS) . "' permission");
        }

        $hashlistId = $args['id'];
        try {
            $hashlist = HashlistUtils::getHashlist($hashlistId);
        } catch (HTException $ex) {
            throw new HttpNotFoundException($request, $ex->getMessage());
        }
        if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
          throw new HttpForbiddenException($request, "No access to hashlist!");
        }

        return $response->withStatus(204)
        ->withHeader("Content-Type", "application/json");
    });
});