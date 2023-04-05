<?php

use DBA\AccessGroup;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpForbiddenException;
use Slim\Routing\RouteContext;

use DBA\Agent;
use DBA\AgentStat;
use DBA\AccessGroupUser;
use DBA\AccessGroupAgent;
use DBA\AgentBinary;
use DBA\CrackerBinary;
use DBA\Hash;
use DBA\Hashlist;
use DBA\User;
use DBA\TaskWrapper;
use DBA\Task;

use DBA\ContainFilter;
use DBA\Factory;
use DBA\FilePretask;
use DBA\HealthCheck;
use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\OrderFilter;
use DBA\Pretask;
use DBA\File;
use DBA\HashlistFactory;
use DBA\Supertask;
use DBA\SupertaskPretask;
use Middlewares\Utils\HttpErrorException;
use Slim\Exception\HttpNotImplementedException;
use Psr\Container\ContainerInterface;

require_once(dirname(__FILE__) . "/../load.php");

abstract class AbstractBaseAPI {
  abstract public function getPermission(): string;
  abstract static public function getDBAClass(): string;
  abstract protected function getFactory(): object;
  abstract public function getExpandables(): array;
  abstract protected function getFilterACL(): array;
  abstract public function getFormFields(): array;
  abstract public static function getBaseUri(): string;

  abstract protected function createObject($QUERY): int;
  abstract protected function deleteObject(object $object): void;
  abstract protected function checkPermission(object $object): bool;


  private $user;
  private $routeParser;

  protected $container;

  /* constructor receives container instance */
  public function __construct(ContainerInterface $container) {
      $this->container = $container;
  }

  
  public function getFeatures(): array {
    return call_user_func($this->getDBAclass() .'::getFeatures');
  }

  public function getMappedFeatures(): array {
    // This function takes all the dba features and converts them to a list.
    // It uses the data from the generator and replaces the keys with the aliasses.
    // structure: hashlist: name: [dbname => hashlistId]
    $features = $this->getFeatures();
    $mappedFeatures = [];
    foreach($features as $KEY => $VALUE) {
      $mappedFeatures[$VALUE['alias']] = $VALUE;
      $mappedFeatures[$VALUE['alias']]['dbname'] = $KEY;
    }
    return $mappedFeatures;
  }

  
  final protected function getUser() {
    return $this->user;
  }

  
  /* Convert Database resturn value to JSON object value */
  private static function db2json(string $type, mixed $val): mixed {
    if ($type == 'bool') {
      $obj = ($val == "1") ? True : False;
    } elseif ($type == 'dict') {
      $obj = json_decode($val, true, 512, JSON_OBJECT_AS_ARRAY);
      // During encoding of the data, the data is saved as an empty array
      // An empty array is something different in json and in python.
      // The following code casts the empty array to an empty 'object'
      // which will be intepreted by python and json correctly as dict or object.
      if (empty($obj)) {
        $obj = (object)[];
      }
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

    $apiClass = $this->container->get('classMapper')->get(get_class($obj));
    $item['_id'] = $obj->getId();
    $item['_self'] = $this->routeParser->urlFor($apiClass . ':getOne', ['id' => $item['_id']]);

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


  protected function object2Array(mixed $object, array $expand) {
    $item = $this->obj2Array($object);
    
    /* TODO Refactor expansions logic to class objects */
    foreach ($expand as $NAME) {
      switch($NAME) {
        case 'agent':
          $obj = Factory::getAgentFactory()->get($item['agentId']);
          $item[$NAME] = $this->obj2Array($obj);
          break;
        case 'agents':
          $obj = Factory::getAccessGroupFactory()->get($item['accessGroupId']);
          $item[$NAME] = $this->obj2Array($obj);
          break;
        case 'agentstats':
          $qFs = [];
          $qFs[] = new QueryFilter(AgentStat::AGENT_ID, $item['agentId'], "=");
          $agentstats = Factory::getAgentStatFactory()->filter([Factory::FILTER => $qFs]);
          $item[$NAME] = array_map(array($this, 'obj2Array'), $agentstats);
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
          $obj = Factory::getCrackerBinaryFactory()->get($item['crackerBinaryTypeId']);
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
          if (get_class($object) == Task::class) {
            // Tasks are bit of a specialcase, as in the task the hashlist is not directly available.
            // To get this information we need to join the task with the Hashlist and the TaskWrapper to get the Hashlist.
            $qF = new QueryFilter(TaskWrapper::TASK_WRAPPER_ID, $item['taskWrapperId'], "=", Factory::getTaskWrapperFactory());
            $jF = new JoinFilter(Factory::getTaskWrapperFactory(), Hashlist::HASHLIST_ID, TaskWrapper::HASHLIST_ID);
            $joined = Factory::getHashlistFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
            // Now cast the database data to an object.
            $obj = reset($joined[Factory::getHashlistFactory()->getModelName()]);
          } else {
            // Used in expanding hashes.
            $obj = Factory::getHashListFactory()->get($item['hashlistId']);
          }
          $item[$NAME] = $this->obj2Array($obj);
          break;
        case 'hashType':
          $obj = Factory::getHashTypeFactory()->get($item['hashTypeId']);
          $item[$NAME] = $this->obj2Array($obj);
          break;
        case 'healthCheck':
          $obj = Factory::getHealthCheckFactory()->get($item['healthCheckId']);
          $item[$NAME] = $this->obj2Array($obj);
          break;
        case 'healthCheckAgents':
          $qFs = [];
          $qFs[] = new QueryFilter(HealthCheck::HEALTH_CHECK_ID, $item['healthCheckId'], "=");
          $objs = Factory::getHealthCheckAgentFactory()->filter([Factory::FILTER => $qFs]);
          $item[$NAME] = array_map(array($this, 'obj2Array'), $objs);
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
          $jF = new JoinFilter(Factory::getFilePretaskFactory(), File::FILE_ID, FilePretask::FILE_ID);
          $item[$NAME] = $this->joinQuery(Factory::getFileFactory(), $qF, $jF);
          break;     
        case 'pretasks':
          /* M2M via SupertaskPretask */
          $qF = new QueryFilter(SupertaskPretask::SUPERTASK_ID, $item[Supertask::SUPERTASK_ID], "=", Factory::getSupertaskPretaskFactory());
          $jF = new JoinFilter(Factory::getSupertaskPretaskFactory(), Pretask::PRETASK_ID, SupertaskPretask::PRETASK_ID);
          $item[$NAME] = $this->joinQuery(Factory::getPretaskFactory(), $qF, $jF);
          break;
        case 'user':
          $obj = Factory::getUserFactory()->get($item['userId']);
          $item[$NAME] = $this->obj2Array($obj);
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

    /* Ensure sorted, for easy debugging of fields */
    ksort($item);

    return $item;
  }


  protected function object2JSON(object $hashlist) : string {
    $item = $this->object2Array($hashlist, []);
    return json_encode($item, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
  }

  protected function validateData(array $data, array $mappedFeatures) {
    // Validate incoming data
    foreach($data as $KEY => $VALUE) {
      // Bool
      if ($mappedFeatures[$KEY]['type'] == 'bool') {
        if (is_bool($VALUE) == False) {
          throw new HttpErrorException("Key '$KEY' is not of type boolean");            
        }
      // Int
      } elseif (str_starts_with($mappedFeatures[$KEY]['type'], 'int')) {
        // TODO: int32, int64 range validation
        if (is_integer($VALUE) == False) {
          throw new HttpErrorException("Key '$KEY' is not of type integer");
        }
      // Str
      } elseif (str_starts_with($mappedFeatures[$KEY]['type'], 'str')) {
        if (is_string($VALUE) == False) {
          throw new HttpErrorException("Key '$KEY' is not of type string");
        }
        // TODO: Length validation
      // Array
      } elseif (str_starts_with($mappedFeatures[$KEY]['type'], 'array')) {
        if (is_array($VALUE) == False) {
          throw new HttpErrorException("Key '$KEY' is not of type array");
        }
        // Array[Int]
        if ($mappedFeatures[$KEY]['subtype'] == 'int') {
          if (in_array(false, array_map('is_integer', $VALUE)) == true) {
            throw new HttpErrorException("Key '$KEY' array contains non-integer values");
          }
        }
      // Dict
      } elseif (str_starts_with($mappedFeatures[$KEY]['type'], 'dict')) {
        if (is_array($VALUE) == False) {
          throw new HttpErrorException("Key '$KEY' is not of type dict");
        }
        // Dict[Bool]
        if ($mappedFeatures[$KEY]['subtype'] == 'bool') {
          if (in_array(false, array_map('is_bool', $VALUE)) == true) {
            throw new HttpErrorException("Key '$KEY' dict contains non-boolean values");
          }
        }
      } else {
        throw new HttpErrorException("Typemapping error for key '$KEY' ");
      }
    }
  }



  protected function makeExpandables(Request $request, array $expandables): array {
    // Check for valid expand parameters
    $expandable = $expandables;
    $expands = [];

    $data = $request->getParsedBody();
    if (!is_null($data)) {
      $bodyExpand_raw = (array_key_exists('expand', $data)) ? $data['expand'] : [];
    } else {
      $bodyExpand_raw = [];
    }

    $queryExpand = (array_key_exists('expand', $request->getQueryParams())) ? preg_split("/[,\ ]+/", $request->getQueryParams()['expand']) : [];
    
    if (is_string($bodyExpand_raw)) {
      $bodyExpand = [$bodyExpand_raw];
    } elseif (is_null($bodyExpand_raw)) {
      $bodyExpand = [];
    } else {
      $bodyExpand = $bodyExpand_raw;
    }
    $mergedExpands = array_merge($bodyExpand, $queryExpand);
    
    foreach($mergedExpands as $expand) {
      if (($key = array_search($expand, $expandable)) !== false) {
        unset($expandable[$key]);
      }
      else {
        throw new HTException("Parameter '" . $expand . "' is not valid expand key (valid keys are: " . join(", ", array_values($expandables)) . ")");
      }
    }
  
    return [$expandable, $mergedExpands];
  }


  protected function makeFilter(Request $request, array $features): array {
    // Check for valid filter parameters

    $qFs = [];
    
    $data = $request->getParsedBody();
    if (!is_null(($data))) {
      $bodyFilter = (array_key_exists('filter', $data)) ? $data['filter'] : [];
    } else {
      $bodyFilter = [];
    }

    $queryFilter = (array_key_exists('filter', $request->getQueryParams())) ? preg_split("/[,\ ]+/", $request->getQueryParams()['filter']) : [];
    $mergedFilters = array_merge($bodyFilter, $queryFilter);

    foreach($mergedFilters as $filter) {
      // TODO: Add sanity checking
      if (preg_match('/^(?P<key>[a-zA-Z]+)(?<operator>=|!=|<|<=|>|>=)(?P<value>[^=]+)$/', $filter, $matches)) {
        if (array_key_exists($matches['key'], $features)) {
          // TODO: cast value
          if ($features[$matches['key']]['type'] == 'bool') {
            $val = filter_var($matches['value'], FILTER_NULL_ON_FAILURE);
            if (is_null($val)) {
              throw new HTException("Filter parameter '" . $filter . "' is not valid boolean value");  
            }
          } else {
            $val = $matches['value'];
          }
          // We need to remap any aliased key to the key as it appears in the database.
          $remappedKey = $features[$matches['key']]['dbname'];
          $qFs[] = new QueryFilter($remappedKey, $val, $matches['operator']);
        } else {
          throw new HTException("Filter parameter '" . $filter . "' is not valid");  
        }
      } else {
        throw new HTException("Filter parameter '" . $filter . "' is not valid");
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

  /*
  *  Common features for all requests, like setting user and checking basic permissions
  */
  protected function preCommon(Request $request): void {
    $userId = $request->getAttribute(('userId'));
    $this->user = UserUtils::getUser($userId);

    $routeContext = RouteContext::fromRequest($request);
    $this->routeParser = $routeContext->getRouteParser();

    if(!AccessControl::getInstance($this->getUser())->hasPermission($this->getPermission())) {
        throw new HttpForbiddenException($request, "No '" . DAccessControl::getDescription($this->getPermission()) . "' permission");
    }
  }

  public function get(Request $request, Response $response, array $args): Response {
    $this->preCommon($request);

    $mappedFeatures = $this->getMappedFeatures();
    $factory = $this->getFactory();
    $expandables = $this->getExpandables();

    $startAt = intval($request->getQueryParams()['startsAt'] ?? 0);
    $maxResults = intval($request->getQueryParams()['maxResults'] ?? 5);

    list($expandable, $expands) = $this->makeExpandables($request, $expandables);

    $qFs_Filter = $this->makeFilter($request, $mappedFeatures);
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
    $invalidKeys = [];
    foreach($QUERY as $NAME => $VALUE)  {
      if (!in_array($NAME, $validFeatures)) {
        array_push($invalidKeys, $NAME);
      }
    }
    if (sizeof($invalidKeys) == 1) {
      throw new HTException("Parameter '" . $invalidKeys[0] . "' is not valid input key (valid keys are: " . join(", ", $validFeatures) . ")");
    } elseif (sizeof($invalidKeys) > 1) {
      ksort($invalidKeys);
      throw new HTException("Parameters '" . join(", ", $invalidKeys) . "' are not valid input keys (valid keys are: " . join(", ", $validFeatures) . ")");
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

    // Map to alias and extend with fields which are only present at the webui
    // aka formFields.
    $mappedFeatures = array_merge($this->getMappedFeatures(), $formFields);

    // Validate incoming data
    $this->validateData($QUERY, $mappedFeatures);

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


  public function updateObject(object $object, array $data, array $mappedFeatures, array $processed = []): void {
    // Apply changes 
    foreach($data as $KEY => $VALUE) {
      if (in_array($KEY, $processed)) {
        continue;
      }

      // Sanity values
      $val = self::json2db($mappedFeatures[$KEY]['type'], $data[$KEY]);
      // Use the original attribute name to update the object.
      $this->getFactory()->set($object, $mappedFeatures[$KEY]['dbname'], $val);
    }
  }


  public function patchOne(Request $request, Response $response, array $args): Response {
    $this->preCommon($request);
    $object = $this->doFetch($request, $args['id']);


    
    $data = $request->getParsedBody();
    $mappedFeatures = $this->getMappedFeatures();

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
    }

    // Validate input data if it matches the correct type or subtype
    $this->validateData($data, $mappedFeatures);

    // This does the real things, patch the values that were sent in the data.
    $this->updateObject($object, $data, $mappedFeatures);

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


  /* Override-able activated methods */
  static public function getAvailableMethods(): array {
    return ["GET", "POST", "PATCH", "DELETE"];
  }
  

  static public function register($app): void {
    $me = get_called_class();
    $baseUri = $me::getBaseUri();
    $baseUriOne = $baseUri . '/{id:[0-9]+}';

    $classMapper = $app->getContainer()->get('classMapper');
    $classMapper->add($me::getDBAclass(), $me);

    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response { return $response; });
    $app->options($baseUriOne, function (Request $request, Response $response): Response { return $response; });

    $available_methods = self::getAvailableMethods();

    if (in_array("GET", $available_methods)) {     
      $app->get($baseUri, $me . ':get')->setname($me . ':get');
    }

    if (in_array("POST", $available_methods)) {
      $app->post($baseUri, $me . ':post')->setname($me . ':post');
    }

    if (in_array("GET", $available_methods)) {
      $app->get($baseUriOne, $me . ':getOne')->setName($me . ':getOne');
    }

    if (in_array("PATCH", $available_methods)) {
      $app->patch($baseUriOne, $me . ':patchOne')->setName($me . ':patchOne');
    }

    if (in_array("DELETE", $available_methods)) {
      $app->delete($baseUriOne, $me . ':deleteOne')->setName($me . ':deleteOne');
    }
  }
} 