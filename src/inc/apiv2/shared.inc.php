<?php

use DBA\AccessGroup;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpForbiddenException;

use DBA\Agent;
use DBA\AccessGroupUser;
use DBA\AccessGroupAgent;
use DBA\CrackerBinary;
use DBA\Hash;
use DBA\Hashlist;
use DBA\User;

use DBA\ContainFilter;
use DBA\Factory;
use DBA\FilePretask;
use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\OrderFilter;
use DBA\Pretask;
use DBA\Supertask;
use DBA\SupertaskPretask;
use Middlewares\Utils\HttpErrorException;
use Slim\Exception\HttpNotImplementedException;

require_once(dirname(__FILE__) . "/../load.php");

abstract class AbstractBaseAPI {
  abstract protected function getPermission(): string;
  abstract protected function getFeatures(): array;
  abstract protected function getFactory(): object;
  abstract protected function getExpandables(): array;
  abstract protected function getFilterACL(): array;
  abstract protected function getFormFields(): array;

  abstract protected function createObject($QUERY): int;
  abstract protected function deleteObject(object $object): void;
  abstract protected function checkPermission(object $object): bool;


  private $user;
  final protected function getUser() {
    return $this->user;
  }

  /* Convert Database resturn value to JSON object value */
  private static function db2json(string $type, mixed $val): mixed {
    if ($type == 'bool') {
      $obj = ($val == "1") ? True : False;
    } else {
      // TODO: Check all objects, instead of wild cast to hopefully-JSON compatible object
      $obj = $val;
    }
    return $obj;
  }


  /* Convert JSON object value to DB insert value, supported by DBA */
  private static function json2db(string $type, mixed $obj): string {
    if ($type == 'bool') {
        $val = ($obj) ? "1" : "0";
    } elseif (str_starts_with($type, 'str')) {
        $val = htmlentities($obj, ENT_QUOTES, "UTF-8");
    } else {
        $val = strval($obj);
    }
    return $val;
  }


  protected function obj2Array(mixed $obj) {  
    // Convert values to JSON supported types
    $features = $obj->getFeatures();
    $kv = $obj->getKeyValueDict();

    $item = [];
    foreach ($features as $NAME => $FEATURE) {
      $test = $kv[$NAME];
      $item[$FEATURE['alias']] = self::db2json($FEATURE['type'], $kv[$NAME]);
    }
    return $item;
  }


  
  /* Quirck to resolve objects via ManyToMany relation table */
  private function joinQuery(mixed $objFactory, DBA\QueryFilter $qF, DBA\JoinFilter $jF): array {
    $joined = $objFactory->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    $objects = $joined[$objFactory->getModelName()];
    
    $ret = [];
    foreach ($objects as $object) {
      array_push($ret, $this->obj2Array($object));
    }

    return $ret;
  }


  protected function object2Array(mixed $hashlist, array $expand) {
    $item = $this->obj2Array($hashlist);

    /* TODO Refactor expansions logic to class objects */
    foreach ($expand as $NAME) {
      switch($NAME) {
        case 'agents':
          $obj = Factory::getAccessGroupFactory()->get($item['accessGroupId']);
          $item[$NAME] = $this->obj2Array($obj);
          break;
        case 'accessGroup':
          $obj = Factory::getAccessGroupFactory()->get($item['accessGroupId']);
          $item[$NAME] = $this->obj2Array($obj);
          break;
        case 'chunk':
          if ($item['chunkId'] === null) {
            /* Chunk expansions are optional, hence the chunk object could be null */
            $item[$NAME] = null;
          } else {
            $obj = Factory::getChunkFactory()->get($item['chunkId']);
            $item[$NAME] = $this->obj2Array($obj);
          }
          break;
        case 'configSection':
          $obj = Factory::getConfigSectionFactory()->get($item['configSectionId']);
          $item[$NAME] = $this->obj2Array($obj);
          break;
        case 'crackerBinary':
          $obj = Factory::getCrackerBinaryFactory()->get($item['crackerBinaryId']);
          $item[$NAME] = $this->obj2Array($obj);
          break;
        case 'crackerBinaryType':
          $obj = Factory::getCrackerBinaryTypeFactory()->get($item['crackerBinaryTypeId']);
          $item[$NAME] = $this->obj2Array($obj);
          break;
        case 'crackerVersions':
          $qFs = [];
          $qFs[] = new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $item['crackerBinaryTypeId'], "=");
          $hashes = Factory::getCrackerBinaryFactory()->filter([Factory::FILTER => $qFs]);
          $item[$NAME] = array_map(array($this, 'obj2Array'), $hashes);
          break;
        case 'hashes':
          $qFs = [];
          $qFs[] = new QueryFilter(Hash::HASHLIST_ID, $item['hashlistId'], "=");
          $hashes = Factory::getHashFactory()->filter([Factory::FILTER => $qFs]);
          $item[$NAME] = array_map(array($this, 'obj2Array'), $hashes);
          break;
        case 'hashlist':
          $obj = Factory::getHashListFactory()->get($item['hashlistId']);
          $item[$NAME] = $this->obj2Array($obj);
          break;
        case 'hashType':
          $obj = Factory::getHashTypeFactory()->get($item['hashTypeId']);
          $item[$NAME] = $this->obj2Array($obj);
          break;
        case 'rightGroup':
        $obj = Factory::getRightGroupFactory()->get($item['rightGroupId']);
        $item[$NAME] = $this->obj2Array($obj);
        break;
        case 'task':
          $obj = Factory::getTaskFactory()->get($item['taskId']);
          $item[$NAME] = $this->obj2Array($obj);
          break;
        case 'pretaskFiles':
          /* M2M via FilePretask */
          $qF = new QueryFilter(FilePretask::PRETASK_ID, $item[Pretask::PRETASK_ID], "=", Factory::getFilePretaskFactory());
          $jF = new JoinFilter(Factory::getFilePretaskFactory(), Pretask::PRETASK_ID, FilePretask::PRETASK_ID);
          $item[$NAME] = $this->joinQuery(Factory::getPretaskFactory(), $qF, $jF);
          break;     
        case 'pretasks':
          /* M2M via SupertaskPretask */
          $qF = new QueryFilter(SupertaskPretask::SUPERTASK_ID, $item[Supertask::SUPERTASK_ID], "=", Factory::getSupertaskPretaskFactory());
          $jF = new JoinFilter(Factory::getSupertaskPretaskFactory(), Pretask::PRETASK_ID, SupertaskPretask::PRETASK_ID);
          $item[$NAME] = $this->joinQuery(Factory::getPretaskFactory(), $qF, $jF);
          break;                           
        case 'userMembers':
          /* M2M via AccessGroupUser */
          $qF = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $item[AccessGroup::ACCESS_GROUP_ID], "=", Factory::getAccessGroupUserFactory());
          $jF = new JoinFilter(Factory::getAccessGroupUserFactory(), User::USER_ID, AccessGroupUser::USER_ID);
          $item[$NAME] = $this->joinQuery(Factory::getUserFactory(), $qF, $jF);
          break;     
        case 'agentMembers':
          /* M2M via AccessGroupAgent */
          $qF = new QueryFilter(AccessGroupAgent::ACCESS_GROUP_ID, $item[AccessGroupAgent::ACCESS_GROUP_ID], "=", Factory::getAccessGroupAgentFactory());
          $jF = new JoinFilter(Factory::getAccessGroupAgentFactory(), Agent::AGENT_ID, AccessGroupAgent::AGENT_ID);
          $item[$NAME] = $this->joinQuery(Factory::getAgentFactory(), $qF, $jF);
          break;     
        default:
          throw new BadFunctionCallException("Internal error: Expansion '$NAME' not implemented!");
        }
    }

    $expandLeft = array_diff($expand, array_keys($item));
    if (sizeof($expandLeft) > 0) {
      /* This should never happen, since valid parameter checking is done pre-flight 
       * in makeExpandables and assignment should be done for every expansion 
       */
      throw new BadFunctionCallException("Internal error: Expansion(s) '" .  join(',', $expandLeft) . "' not implemented!");
    }

    return $item;
  }


  protected function object2JSON(object $hashlist) : string {
    $item = $this->object2Array($hashlist, []);
    return json_encode($item, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
  }



  protected function makeExpandables(Request $request, array $expandables): array {
    // Check for valid expand parameters
    $expandable = $expandables;
    $expands = [];
    if (array_key_exists('expand', $request->getQueryParams())) {
      $expands = preg_split("/[,\ ]+/", $request->getQueryParams()['expand']);

      foreach($expands as $expand) {
        if (($key = array_search($expand, $expandable)) !== false) {
          unset($expandable[$key]);
        }
        else {
          throw new HTException("Parameter '" . $expand . "' is not valid expand key (valid keys are: " . join(", ", array_values($expandables)) . ")");
        }
      }
    }
    return [$expandable, $expands];
  }


  protected function makeFilter(Request $request, array $features): array {
    // Check for valid filter parameters
    $filters = [];
    $qFs = [];
    if (array_key_exists('filter', $request->getQueryParams())) {
      $filters = preg_split("/[,\ ]+/", $request->getQueryParams()['filter']);

      foreach($filters as $filter) {
        // TODO: Add sanity checking
        if (preg_match('/^(?P<key>[a-zA-Z]+)(?<operator>=|!=|<|<=|>|>=)(?P<value>[^=]+)$/', $filter, $matches)) {
          if (array_key_exists($matches['key'], $features)) {
            // TODO: cast value
            if ($features[$matches['key']]['type'] == 'bool') {
              $val = (bool) filter_var($matches['value'], FILTER_VALIDATE_BOOLEAN);
            } else {
              $val = $matches['value'];
            }
            $qFs[] = new QueryFilter($matches['key'], $val, $matches['operator']);
          } else {
            throw new HTException("Filter parameter '" . $filter . "' is not valid");  
          }
        } else {
          throw new HTException("Filter parameter '" . $filter . "' is not valid");
        }
      }
    }

    return $qFs;
  }



  protected function validateHashlistAccess(Request $request, User $user, String $hashlistId): Hashlist {
    // TODO: Fix permissions
    if(!AccessControl::getInstance($user)->hasPermission(DAccessControl::MANAGE_HASHLIST_ACCESS)) {
      throw new HttpForbiddenException($request, "No '" . DAccessControl::getDescription(DAccessControl::MANAGE_HASHLIST_ACCESS) . "' permission");
    }

    try {
        $hashlist = HashlistUtils::getHashlist($hashlistId);
    } catch (HTException $ex) {
        throw new HttpNotFoundException($request, $ex->getMessage());
    }
    if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
      throw new HttpForbiddenException($request, "No access to hashlist!");
    }

    return $hashlist;
  }


  protected function validatePost($QUERY, array $features, array $formFields) {
    // Generate listing of validFeatures
    $featureFields = [];
    foreach($features as $NAME => $FEATURE) {
      /* Protected features cannot be specified */
      if ($FEATURE['protected'] == true) {
        continue;
      }
      /* Use API aliased naming */
      array_push($featureFields, $FEATURE['alias']);
    }
    $validFeatures = array_merge($featureFields, array_keys($formFields));

    // Ensure debugging response lists are in sorted order
    ksort($validFeatures);

    // Find keys which are invalid
    foreach($QUERY as $NAME => $VALUE)  {
      if (!in_array($NAME, $validFeatures)) {
        throw new HTException("Parameter '" . $NAME . "' is not valid input key (valid keys are: " . join(", ", $validFeatures) . ")");
      }
    }

    // Find out about mandatory keys which are not provided
    $missingKeys = [];
    foreach ($features as $NAME => $FEATURE) {
      // Optional keys are not required entities
      if ($FEATURE['null'] == True) {
        continue;
      }
      // Protected fields are not required on creation
      if ($FEATURE['protected'] == True) {
        continue;
      }
      if (!array_key_exists($FEATURE['alias'], $QUERY)) {
        $missingKeys[] = $FEATURE['alias'];
      }
    }
    // Consider all formFields mandatory input
    foreach($formFields as $NAME => $FEATURE) {
      if (!array_key_exists($NAME, $QUERY)) {
        $missingKeys[] = $NAME;
      }
    }
    if (count($missingKeys) > 0) {
      throw new HTException("Required parameter(s) '" .  join(", ", $missingKeys) . "' not specified");
    }

    // Build combined formField and Feature type mapping
    $allFeatures = $features + $formFields;

    // Validate incoming data
    foreach($QUERY as $KEY => $VALUE) {
      // Ensure type is correct
      if ($allFeatures[$KEY]['type'] == 'bool') {
        if (is_bool($VALUE) == False) {
          throw new HttpErrorException("Key '$KEY' is not of type boolean");            
        }
      } elseif (str_starts_with($allFeatures[$KEY]['type'], 'int')) {
        // TODO: int32, int64 range validation
        if (is_integer($VALUE) == False) {
          throw new HttpErrorException("Key '$KEY' is not of type integer");
        }
      } elseif (str_starts_with($allFeatures[$KEY]['type'], 'str')) {
        if (is_string($VALUE) == False) {
          throw new HttpErrorException("Key '$KEY' is not of type string");
        }
        // TODO: Length validation
      } elseif (str_starts_with($allFeatures[$KEY]['type'], 'array')) {
        if (is_array($VALUE) == False) {
          throw new HttpErrorException("Key '$KEY' is not of type array");
        }
        if ($allFeatures[$KEY]['subtype'] == 'int') {
          if (in_array(false, array_map('is_integer', $VALUE)) == true) {
            throw new HttpErrorException("Key '$KEY' array contains non-integer values");
          }
        }
      }
    }
  }

  /*
  *  Common features for all requests, like setting user and checking basic permissions
  */
  protected function preCommon(Request $request): void {
    $userId = $request->getAttribute(('userId'));
    $this->user = UserUtils::getUser($userId);

    if(!AccessControl::getInstance($this->getUser())->hasPermission($this->getPermission())) {
        throw new HttpForbiddenException($request, "No '" . DAccessControl::getDescription($this->getPermission()) . "' permission");
    }
  }

          
  public function get(Request $request, Response $response, array $args): Response {
    $this->preCommon($request);

    $features = $this->getFeatures();
    $factory = $this->getFactory();
    $expandables = $this->getExpandables();
    
    $startAt = intval($request->getQueryParams()['startsAt'] ?? 0);
    $maxResults = intval($request->getQueryParams()['maxResults'] ?? 5);

    list($expandable, $expands) = $this->makeExpandables($request, $expandables);

    $qFs_Filter = $this->makeFilter($request, $features);
    $qFs_ACL = $this->getFilterACL();
    $qFs = array_merge($qFs_ACL, $qFs_Filter);

    // TODO: Optimize code, should only fetch subsection of database, when pagination is in play       
    $objects = $factory->filter((count($qFs) > 0) ? [Factory::FILTER => $qFs] : []);

    $lists = [];
    foreach ($objects as $object) {
        $lists[] = $this->object2Array($object, $expands);
    }

    // TODO: Implement actual expanding
    $total = count($objects);
    $ret = [
        "_expandable" => join(",", $expandable),
        "startAt" => $startAt,
        "maxResults" => $maxResults,
        "total" => $total,
        "isLast" => ($total <= ($startAt + $maxResults)),
        "values" => array_slice($lists, $startAt, $maxResults)
    ];

    $body = $response->getBody();
    $body->write(json_encode($ret, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    
    return $response->withStatus(201)
    ->withHeader("Content-Type", "application/json");
  }


  public function post(Request $request, Response $response, array $args): Response {
    $this->preCommon($request);

    $QUERY = $request->getParsedBody();
    $features = $this->getFeatures();
    $formFields = $this->getFormfields();
    $this->validatePost($QUERY, $features, $formFields);

    $pk = $this->createObject($QUERY);

    // Request object again, since post-modified entries are not reflected into object.
    $body = $response->getBody();
    $body->write($this->object2JSON($this->getFactory()->get($pk)));

    return $response->withStatus(201)
        ->withHeader("Content-Type", "application/json");
  }

  
  protected function doFetch(Request $request, string $pk): mixed {
    $object = $this->getFactory()->get($pk);
    if ($object === null) {
        throw new HttpNotFoundException($request, "Object not found!");
    }

    if (!$this->checkPermission($object)) {
      throw new HttpForbiddenException($request, "No access to object!");
    }
    return $object;
  }


  public function getOne(Request $request, Response $response, array $args): Response {
    $this->preCommon($request);
    $object = $this->doFetch($request, $args['id']);

    $body = $response->getBody();
    $body->write($this->object2JSON($object));

    return $response->withStatus(201)
        ->withHeader("Content-Type", "application/json");
  }




  public function patchOne(Request $request, Response $response, array $args): Response {
    $this->preCommon($request);
    $object = $this->doFetch($request, $args['id']);


    
    $data = $request->getParsedBody();
    $features = $this->getFeatures();

    $mappedFeatures = [];
    foreach($features as $KEY => $VALUE) {
      $mappedFeatures[$VALUE['alias']] = $VALUE;
      $mappedFeatures[$VALUE['alias']]['dbname'] = $KEY;
    }


    // Validate incoming data
    foreach($data as $KEY => $VALUE) {
      // Ensure key is a regular string
      if (is_string($KEY) == False) {
        throw new HttpErrorException("Key '$KEY' invalid");
      }
      // Ensure key exists in target array
      if (array_key_exists($KEY, $mappedFeatures) == False) {
        throw new HttpErrorException("Key '$KEY' does not exists!");
      }

      // Ensure key can be updated 
      if ($mappedFeatures[$KEY]['read_only'] == True) {
        throw new HttpErrorException("Key '$KEY' is immutable");
      }

      // Ensure type is correct
      if ($mappedFeatures[$KEY]['type'] == 'bool') {
        if (is_bool($VALUE) == False) {
          throw new HttpErrorException("Key '$KEY' is not of type boolean");            
        }
      } elseif (str_starts_with($mappedFeatures[$KEY]['type'], 'int')) {
        // TODO: int32, int64 range validation
        if (is_integer($VALUE) == False) {
          throw new HttpErrorException("Key '$KEY' is not of type integer");
        }
      } elseif (str_starts_with($mappedFeatures[$KEY]['type'], 'str')) {
        if (is_string($VALUE) == False) {
          throw new HttpErrorException("Key '$KEY' is not of type string");
        }
        // TODO: Length validation
      }
    }

    // Apply changes 
    foreach($data as $KEY => $VALUE) {
      // Sanity values
      $val = self::json2db($mappedFeatures[$KEY]['type'], $data[$KEY]);
      $this->getFactory()->set($object, $mappedFeatures[$KEY]['dbname'], $val);
    }

    // Return updated object
    $newObject = $this->getFactory()->get($object->getId());

    $body = $response->getBody();
    $body->write($this->object2JSON($newObject));

    return $response->withStatus(201)
        ->withHeader("Content-Type", "application/json");
  }


  public function deleteOne(Request $request, Response $response, array $args): Response {
    $this->preCommon($request);
    $object = $this->doFetch($request, $args['id']);

    /* Actually delete object */
     $this->deleteObject($object);

    return $response->withStatus(204)
    ->withHeader("Content-Type", "application/json");
  }
} 