<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpForbiddenException;
use Slim\Routing\RouteContext;

use DBA\AccessGroup;
use DBA\AccessGroupAgent;
use DBA\AccessGroupUser;
use DBA\Agent;
use DBA\AgentBinary;
use DBA\AgentStat;
use DBA\Chunk;
use DBA\Config;
use DBA\CrackerBinary;
use DBA\CrackerBinaryType;
use DBA\File;
use DBA\FilePretask;
use DBA\Hash;
use DBA\Hashlist;
use DBA\HashlistHashlist;
use DBA\HealthCheck;
use DBA\NotificationSetting;
use DBA\Pretask;
use DBA\RightGroup;
use DBA\Speed;
use DBA\Supertask;
use DBA\SupertaskPretask;
use DBA\Task;
use DBA\TaskWrapper;
use DBA\User;

use DBA\Factory;
use DBA\HashType;
use DBA\HealthCheckAgent;
use DBA\JoinFilter;
use DBA\LogEntry;
use DBA\Preprocessor;
use DBA\QueryFilter;
use DBA\RegVoucher;
use Middlewares\Utils\HttpErrorException;
use Psr\Container\ContainerInterface;

require_once(dirname(__FILE__) . "/../load.php");

/**   
 * This class acts as the BaseAPI implementation of API model endpoints
 */
abstract class AbstractBaseAPI
{
  abstract static public function getDBAClass(): string;
  abstract protected function getFactory(): object;
  abstract public function getExpandables(): array;
  abstract protected function getFilterACL(): array;
  abstract public function getFormFields(): array;
  abstract public static function getBaseUri(): string;

  abstract protected function createObject($QUERY): int;
  abstract protected function deleteObject(object $object): void;
  abstract protected function checkPermission(object $object): bool;

  /** @var DBA\User|null $user is currently logged in user */
  private $user;

  /** @var \Slim\Interfaces\RouteParserInterface|null $routeParser contains routing information
   * which are for example used dynamic creation of _self references
   */
  private $routeParser;

  /** @var ContainerInterface|null $container dynamically generated model mappings 
   * which are for example used for retrival of objects based on string identity
   */
  protected $container;

  /** @var mixed|null $permissionErrors contained detailed results of last 
   * validatePermissions function call
  */
  private $permissionErrors;

  /**
   * Constructor receives container instance
   */
  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
  }

  /**
   * Constructor receives features
   */
  public function getFeatures(): array
  {
    return call_user_func($this->getDBAclass() . '::getFeatures');
  }

  /**
   * Take all the dba features and converts them to a list.
   * It uses the data from the generator and replaces the keys with the aliasses.
   * structure: hashlist: name: [dbname => hashlistId]
   */
  public function getMappedFeatures(): array
  {
    $features = $this->getFeatures();
    $mappedFeatures = [];
    foreach ($features as $KEY => $VALUE) {
      $mappedFeatures[$VALUE['alias']] = $VALUE;
      $mappedFeatures[$VALUE['alias']]['dbname'] = $KEY;
    }
    return $mappedFeatures;
  }

  /** 
   * Retrieve currently logged-in user
   */
  final protected function getUser()
  {
    return $this->user;
  }

  /**
   * Temponary mapping until src/inc/defines/accessControl.php permissions are no longer used
   */
  protected static $acl_mapping = array(
    DAccessControl::VIEW_HASHLIST_ACCESS[0] => array(Hashlist::PERM_READ),
    DAccessControl::MANAGE_HASHLIST_ACCESS => array(Hashlist::PERM_READ, Hashlist::PERM_UPDATE, Hashlist::PERM_DELETE),
    DAccessControl::CREATE_HASHLIST_ACCESS => array(Hashlist::PERM_CREATE, Hashlist::PERM_READ),

    DAccessControl::CREATE_SUPERHASHLIST_ACCESS => array(HashlistHashlist::PERM_CREATE, HashlistHashlist::PERM_READ),

    DAccessControl::VIEW_HASHES_ACCESS => array(Hash::PERM_READ),
    DAccessControl::VIEW_AGENT_ACCESS[0] => array(Agent::PERM_READ),

    DAccessControl::MANAGE_AGENT_ACCESS => array(Agent::PERM_READ, Agent::PERM_UPDATE, Agent::PERM_DELETE,
                                                // src/inc/defines/agents.php
                                                AgentStat::PERM_CREATE, AgentStat::PERM_READ, AgentStat::PERM_UPDATE, AgentStat::PERM_DELETE),

    DAccessControl::CREATE_AGENT_ACCESS => array(Agent::PERM_CREATE, Agent::PERM_READ,
                                                // src/inc/defines/agents.php
                                                RegVoucher::PERM_CREATE, RegVoucher::PERM_READ, RegVoucher::PERM_UPDATE, RegVoucher::PERM_DELETE),

    DAccessControl::VIEW_TASK_ACCESS[0] => array(Task::PERM_READ, Speed::PERM_READ, Chunk::PERM_READ),
    DAccessControl::RUN_TASK_ACCESS[0] => array("TODO_RUN_TASK_ACCESS"),
    DAccessControl::CREATE_TASK_ACCESS[0] => array(Task::PERM_CREATE, Task::PERM_READ, Chunk::PERM_READ),
    DAccessControl::MANAGE_TASK_ACCESS => array(Task::PERM_READ, Task::PERM_UPDATE, Task::PERM_DELETE,
                                                Chunk::PERM_READ, Chunk::PERM_UPDATE, Chunk::PERM_DELETE,
                                                // src/inc/defines/tasks.php
                                                TaskWrapper::PERM_READ, TaskWrapper::PERM_UPDATE),

    DAccessControl::VIEW_PRETASK_ACCESS[0] => array(Pretask::PERM_READ),
    DAccessControl::CREATE_PRETASK_ACCESS => array(Pretask::PERM_READ, Pretask::PERM_CREATE),
    DAccessControl::MANAGE_PRETASK_ACCESS => array(Pretask::PERM_READ, Pretask::PERM_UPDATE, Pretask::PERM_DELETE),

    DAccessControl::VIEW_SUPERTASK_ACCESS[0] => array(Supertask::PERM_READ),
    DAccessControl::CREATE_SUPERTASK_ACCESS => array(Supertask::PERM_CREATE, Supertask::PERM_READ),
    DAccessControl::MANAGE_SUPERTASK_ACCESS => array(Supertask::PERM_READ, Supertask::PERM_UPDATE, Supertask::PERM_DELETE),

    DAccessControl::VIEW_FILE_ACCESS[0] => array(File::PERM_READ),
    DAccessControl::MANAGE_FILE_ACCESS => array(File::PERM_READ, File::PERM_UPDATE, File::PERM_DELETE),
    DAccessControl::ADD_FILE_ACCESS => array(File::PERM_CREATE, File::PERM_READ),

     // src/inc/defines/cracker.php
    DAccessControl::CRACKER_BINARY_ACCESS => array(CrackerBinary::PERM_CREATE, CrackerBinary::PERM_READ, CrackerBinary::PERM_UPDATE, CrackerBinary::PERM_DELETE,
                                                   CrackerBinaryType::PERM_CREATE, CrackerBinaryType::PERM_READ, CrackerBinaryType::PERM_UPDATE, CrackerBinaryType::PERM_DELETE,
                                                   // src/inc/defines/agents.php
                                                   AgentBinary::PERM_CREATE, AgentBinary::PERM_READ, AgentBinary::PERM_UPDATE, AgentBinary::PERM_DELETE),

    DAccessControl::SERVER_CONFIG_ACCESS => array(Config::PERM_CREATE, Config::PERM_READ, Config::PERM_UPDATE, Config::PERM_DELETE, 
                                                  // src/inc/defines/preprocessor.php
                                                  Preprocessor::PERM_CREATE, Preprocessor::PERM_READ, Preprocessor::PERM_UPDATE, Preprocessor::PERM_DELETE,
                                                  // src/inc/defines/health.php
                                                  HealthCheck::PERM_CREATE, HealthCheck::PERM_READ, HealthCheck::PERM_UPDATE, HealthCheck::PERM_DELETE,
                                                  HealthCheckAgent::PERM_CREATE, HealthCheckAgent::PERM_READ, HealthCheckAgent::PERM_UPDATE, HealthCheckAgent::PERM_DELETE,
                                                  // src/inc/defines/hashlists.php
                                                  HashType::PERM_CREATE, HashType::PERM_READ, HashType::PERM_UPDATE, HashType::PERM_DELETE),

    DAccessControl::USER_CONFIG_ACCESS => array(User::PERM_CREATE, User::PERM_READ, User::PERM_UPDATE, User::PERM_DELETE, RightGroup::PERM_CREATE, RightGroup::PERM_READ, RightGroup::PERM_UPDATE, RightGroup::PERM_DELETE),

    DAccessControl::MANAGE_ACCESS_GROUP_ACCESS => array(AccessGroup::PERM_CREATE, AccessGroup::PERM_READ, AccessGroup::PERM_UPDATE, AccessGroup::PERM_DELETE),

    // src/inc/defines/accessControl.php
    DAccessControl::PUBLIC_ACCESS => array(LogEntry::PERM_READ),

    // src/inc/defines/notifications.php
    DAccessControl::LOGIN_ACCESS => array(NotificationSetting::PERM_CREATE, NotificationSetting::PERM_READ, NotificationSetting::PERM_UPDATE, NotificationSetting::PERM_DELETE),
  );

  /** 
   * Retrieve default permissions based on method requested
   */
   public function getRequiredPermissions(string $method): array
   {
      # Get required permission based on API method type
      switch($method) {
      case "GET":
        $required_perm = $this->getDBAclass()::PERM_READ;
        break;
      case "POST":
        $required_perm = $this->getDBAclass()::PERM_CREATE;
        break;
      case "PATCH":
        $required_perm = $this->getDBAclass()::PERM_UPDATE;
        break;
      case "DELETE":
        $required_perm = $this->getDBAclass()::PERM_DELETE;
        break;
      default:
        throw new HTException("Method '" . $method . "' is not allowed " . 
                              "(valid methods are for model are: " . join(",", $this->getAvailableMethods()) . ")");  
    }
    return array($required_perm);
  }

  /** 
   * Convert Database resturn value to JSON object value 
   */
  private static function db2json(string $type, mixed $val): mixed
  {
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

  /** 
   * Convert JSON object value to DB insert value, supported by DBA
   */
  private static function json2db(string $type, mixed $obj): mixed
  {
    if ($type == 'bool') {
      $val = ($obj) ? "1" : "0";
    } elseif ($type == 'int' && is_null($obj)){
      $val = $obj;
    } elseif (str_starts_with($type, 'str')) {
      $val = htmlentities($obj, ENT_QUOTES, "UTF-8");
    } else {
      $val = strval($obj);
    }
    return $val;
  }

  /** 
   * Convert JSON object value to DB insert value, supported by DBA
   */
  protected function obj2Array(mixed $obj)
  {
    // Convert values to JSON supported types
    $features = $obj->getFeatures();
    $kv = $obj->getKeyValueDict();

    $item = [];

    $apiClass = $this->container->get('classMapper')->get(get_class($obj));
    $item['_id'] = $obj->getId();
    $item['_self'] = $this->routeParser->urlFor($apiClass . ':getOne', ['id' => $item['_id']]);

    foreach ($features as $NAME => $FEATURE) {
      $test = $kv[$NAME];
      // If a attribute is set to private, it should be hidden and not returned.
      // Example of this is the password hash.
      if ($FEATURE['private'] === true) {
        continue;
      } else {
        $item[$FEATURE['alias']] = self::db2json($FEATURE['type'], $kv[$NAME]);
      }
    }
    return $item;
  }

  /**
   * Quirck to resolve objects via ManyToMany relation table 
   */
  private function joinQuery(mixed $objFactory, DBA\QueryFilter $qF, DBA\JoinFilter $jF): array
  {
    $joined = $objFactory->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    $objects = $joined[$objFactory->getModelName()];

    $ret = [];
    foreach ($objects as $object) {
      array_push($ret, $this->obj2Array($object));
    }

    return $ret;
  }

  /** 
   * Expands object items
   */
  protected function object2Array(mixed $object, array $expand)
  {
    $item = $this->obj2Array($object);
    $features = $this->getFeatures();

    /* TODO Refactor expansions logic to class objects */
    foreach ($expand as $NAME) {
      switch ($NAME) {
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
        case 'globalPermissionGroup':
          $obj = Factory::getRightGroupFactory()->get($item['globalPermissionGroupId']);
          $item[$NAME] = $this->obj2Array($obj);
          break;
        case 'task':
          $obj = Factory::getTaskFactory()->get($item['taskId']);
          $item[$NAME] = $this->obj2Array($obj);
          break;
        case 'speeds':
          $qFs = [];
          $qFs[] = new QueryFilter(Speed::TASK_ID, $item['taskId'], "=");
          $objs = Factory::getSpeedFactory()->filter([Factory::FILTER => $qFs]);
          $item[$NAME] = array_map(array($this, 'obj2Array'), $objs);
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
          if (get_class($object) == RightGroup::class) {
            $mapped_id = $features[RightGroup::RIGHT_GROUP_ID]['alias'];
            $qF = new QueryFilter(User::RIGHT_GROUP_ID, $item[$mapped_id], "=");
            $objs = Factory::getUserFactory()->filter([Factory::FILTER => $qF]);
            $item[$NAME] = array_map(array($this, 'obj2Array'), $objs);
          } elseif (get_class($object) == NotificationSetting::class) {
            $obj = Factory::getUserFactory()->get($item['userId']);
            $item[$NAME] = $this->obj2Array($obj);
          }
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


  /**
   * Uniform conversion of php array to JSON output 
   */
  protected function ret2json(array $result): string
  {
    return json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
  }

  /**
   * Helper conversion of single object to JSON string
   */
  protected function object2JSON(object $object): string
  {
    $item = $this->object2Array($object, []);
    return $this->ret2json($item);
  }

  /**
   * Validate incoming data
   */
  protected function validateData(array $data, array $mappedFeatures)
  {
    foreach ($data as $KEY => $VALUE) {
      // Validate if field can be left empty or not
      if ($mappedFeatures[$KEY]['null'] == False) {
        if (is_null($VALUE) == True) {
          throw new HttpErrorException("Key '$KEY' is cannot be null.");
        }
      } else {
        if (is_null($VALUE) == True) {
          // Key can be null and is null, so skip type checking.
          continue;
        }
      }

      // Perform type mapping
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

  /**
   * Check for valid expand parameters
   */
  protected function makeExpandables(Request $request, array $expandables): array
  {
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

    foreach ($mergedExpands as $expand) {
      if (($key = array_search($expand, $expandable)) !== false) {
        unset($expandable[$key]);
      } else {
        throw new HTException("Parameter '" . $expand . "' is not valid expand key (valid keys are: " . join(", ", array_values($expandables)) . ")");
      }
    }

    return [$expandable, $mergedExpands];
  }

  /**
   * Find primary key for DBA object
   */
  private function getPrimaryKey(): string
  {
    $features = $this->getFeatures();
    # Word-around required since getPrimaryKey is not static in dba/models/*.php
    foreach($features as $key => $value) {
      if ($value['pk'] == True) {
        return $key;
      }
    }
  }

  /**
   * Check for valid filter parameters and build QueryFilter
   */
  protected function makeFilter(Request $request, array $features): array
  {
    $qFs = [];

    $data = $request->getParsedBody();
    if (!is_null(($data))) {
      $bodyFilter = (array_key_exists('filter', $data)) ? $data['filter'] : [];
    } else {
      $bodyFilter = [];
    }

    $queryFilter = (array_key_exists('filter', $request->getQueryParams())) ? preg_split("/[,\ ]+/", $request->getQueryParams()['filter']) : [];
    $mergedFilters = array_merge($bodyFilter, $queryFilter);

    foreach ($mergedFilters as $filter) {
      // TODO: Add sanity checking
      if (preg_match('/^(?P<key>[_a-zA-Z]+)(?<operator>=|!=|<|<=|>|>=)(?P<value>[^=]+)$/', $filter, $matches)) {
        // Special filtering of _id to use for uniform access to model primary key
        $cast_key = $matches['key'] == '_id' ? $this->getPrimaryKey() : $matches['key'];

        if (array_key_exists($cast_key, $features)) {
          // TODO: cast value
          if ($features[$cast_key]['type'] == 'bool') {
            $val = filter_var($matches['value'], FILTER_NULL_ON_FAILURE);
            if (is_null($val)) {
              throw new HTException("Filter parameter '" . $filter . "' is not valid boolean value");
            }
          } else {
            $val = $matches['value'];
          }
          // We need to remap any aliased key to the key as it appears in the database.
          $remappedKey = $features[$cast_key]['dbname'];
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

  /**
   * Validate if user is allowed to access hashlist
   */
  protected function validateHashlistAccess(Request $request, User $user, String $hashlistId): Hashlist
  {
    // TODO: Fix permissions
    if (!AccessControl::getInstance($user)->hasPermission(DAccessControl::MANAGE_HASHLIST_ACCESS)) {
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

  /** 
   * Validate permissions
   */
  protected function validatePermissions(array $required_perms): bool {
    // Retrieve permissions from RightGroup part of the User
    $group = Factory::getRightGroupFactory()->get($this->user->getId());
  
    if ($group->getPermissions() == 'ALL') {
      // Special (legacy) case for administative access, enable all available permissions
      $all_perms = array_keys(self::$acl_mapping);
      $rightgroup_perms = array_combine($all_perms, array_fill(0,count($all_perms), true));
    } else {
      $rightgroup_perms = json_decode($group->getPermissions(), true);
    }

    // Validate if no undefined permissions are set in $acl_mapping
    assert(count(array_diff(array_keys($rightgroup_perms), array_keys(self::$acl_mapping))) == 0);

    // Create listing of available permissions for user
    $user_available_perms = array();
    foreach($rightgroup_perms as $rightgroup_perm => $permission_set) {
      if ($permission_set) {
        $user_available_perms = array_unique(array_merge($user_available_perms, self::$acl_mapping[$rightgroup_perm]));
      }
    };

    // Sort to display values in a unified format for user and debugging
    sort($required_perms);
    sort($user_available_perms);

    // Find if all permissions are matched
    $missing_permissions = array_diff($required_perms, $user_available_perms);
    if (count($missing_permissions) > 0) {
      $this->permissionErrors = array("No '" . join(",", $missing_permissions) . "' permission(s). [required_permissions='" .join(", ", $required_perms). "', user_permissions='" . join(", ", $user_available_perms) . "']");
      return FALSE;
    } else {
      $this->permissionErrors = array();
      return TRUE;
    }
  }

  /**
   *  Common features for all requests, like setting user and checking basic permissions
   */
  protected function preCommon(Request $request): void
  {
    $userId = $request->getAttribute(('userId'));
    $this->user = UserUtils::getUser($userId);

    $routeContext = RouteContext::fromRequest($request);
    $this->routeParser = $routeContext->getRouteParser();
    
    $required_perms = $this->getRequiredPermissions($request->getMethod());

    if ($this->validatePermissions($required_perms) === FALSE) {
      throw new HttpForbiddenException($request, join('||', $this->permissionErrors));
    }
  }

  /* 
   * Return requested parameter, prioritize query parameter over inline payload parameter 
   */
  private function getParam(Request $request, string $param, int $default): int
  {
    $queryParams = $request->getQueryParams();
    $bodyParams = $request->getParsedBody();

    if (array_key_exists($param, $queryParams)) {
      return intval($queryParams[$param]);
    } elseif (array_key_exists($param, $bodyParams)) {
      return intval($bodyParams[$param]);
    } else {
      return $default;
    }
  }

  /**
   * API entry point for requesting multiple objects
   */
  public function get(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);

    $mappedFeatures = $this->getMappedFeatures();
    $factory = $this->getFactory();
    $expandables = $this->getExpandables();

    $startAt = $this->getParam($request, 'startsAt', 0);
    $maxResults = $this->getParam($request, 'maxResults', 5);

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
    $body->write($this->ret2json($ret));

    return $response->withStatus(200)
      ->withHeader("Content-Type", "application/json");
  }

  /**
   * Get input field names valid for creation of object
   */
  final public function getCreateValidFeatures(): array
  {
    $features = $this->getMappedFeatures();
    $formFields = $this->getFormfields();

    // Generate listing of validFeatures
    $featureFields = [];
    foreach ($features as $NAME => $FEATURE) {
      /* Protected and private features cannot be specified */
      if ($FEATURE['protected'] == True) {
        continue;
      } elseif ($FEATURE['private'] == True) {
        continue;
      }
      /* Use API aliased naming */
      array_push($featureFields, $FEATURE);
    }
    $validFeatures = array_merge($featureFields, $formFields);

    // Ensure debugging response lists are in sorted order
    ksort($validFeatures);

    return $validFeatures;
  }

  /**
   * API entry point creation of new object
   */
  public function post(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);

    $QUERY = $request->getParsedBody();
    /* TODO: Rework with $this->getMappedFeatures() and remove all alias references below */
    $features = $this->getFeatures();
    $formFields = $this->getFormfields();

    // Generate listing of validFeatures
    $featureFields = [];
    foreach ($features as $NAME => $FEATURE) {
      /* Protected and private features cannot be specified */
      if ($FEATURE['protected'] == True) {
        continue;
      } elseif ($FEATURE['private'] == True) {
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
    foreach ($QUERY as $NAME => $VALUE) {
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
    foreach ($formFields as $NAME => $FEATURE) {
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

  /**
   * Request single object from database & validate permissons
   */
  protected function doFetch(Request $request, string $pk): mixed
  {
    $object = $this->getFactory()->get($pk);
    if ($object === null) {
      throw new HttpNotFoundException($request, "Object not found!");
    }

    if (!$this->checkPermission($object)) {
      throw new HttpForbiddenException($request, "No access to object!");
    }
    return $object;
  }

  /**
   * API entry point for requests of single object
   */
  public function getOne(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);

    $expandables = $this->getExpandables();
    list($expandable, $expands) = $this->makeExpandables($request, $expandables);

    $object = $this->doFetch($request, $args['id']);

    $ret = $this->object2Array($object, $expands);
    $ret["_expandable"] = join(",", $expandable);
    ksort($ret);

    $body = $response->getBody();
    $body->write($this->ret2json($ret));

    return $response->withStatus(200)
      ->withHeader("Content-Type", "application/json");
  }

  /**
   * Update object with provided values
   */
  public function updateObject(object $object, array $data, array $mappedFeatures, array $processed = []): void
  {
    // Apply changes 
    foreach ($data as $KEY => $VALUE) {
      if (in_array($KEY, $processed)) {
        continue;
      }

      // Sanity values
      $val = self::json2db($mappedFeatures[$KEY]['type'], $data[$KEY]);
      // Use the original attribute name to update the object.
      $this->getFactory()->set($object, $mappedFeatures[$KEY]['dbname'], $val);
    }
  }

  /**
   * Get input field names valid for patching of object
   */
  final public function getPatchValidFeatures(): array
  {
    $mappedFeatures = $this->getMappedFeatures();
    $validFeatures = [];

    // Generate listing of validFeatures
    foreach ($mappedFeatures as $name => $feature) {
      // Ensure key can be updated 
      if ($feature['read_only'] == True) {
        continue;
      }
      if ($feature['protected'] == True) {
        continue;
      }
      if ($feature['private'] == True) {
        continue;
      }
    
      $validFeatures[$name] = $feature;
    };

    // Ensure debugging response lists are in sorted order
    ksort($validFeatures);

    return $validFeatures;
    }

  /**
   * API entry point for modification of single object
   */
  public function patchOne(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);
    $object = $this->doFetch($request, $args['id']);

    $data = $request->getParsedBody();
    $mappedFeatures = $this->getMappedFeatures();

    // Validate incoming data
    foreach ($data as $KEY => $VALUE) {
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
      if ($mappedFeatures[$KEY]['protected'] == True) {
        throw new HttpErrorException("Key '$KEY' is protected");
      }
      if ($mappedFeatures[$KEY]['private'] == True) {
        throw new HttpErrorException("Key '$KEY' is private");
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

  /**
   * API entry point for deletion of single object
   */
  public function deleteOne(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);
    $object = $this->doFetch($request, $args['id']);

    /* Actually delete object */
    $this->deleteObject($object);

    return $response->withStatus(204)
      ->withHeader("Content-Type", "application/json");
  }


  /**
   * Override-able activated methods 
   */
  static public function getAvailableMethods(): array
  {
    return ["GET", "POST", "PATCH", "DELETE"];
  }

  /**
   * Override-able registering of options
   */
  static public function register($app): void
  {
    $me = get_called_class();
    $baseUri = $me::getBaseUri();
    $baseUriOne = $baseUri . '/{id:[0-9]+}';

    $classMapper = $app->getContainer()->get('classMapper');
    $classMapper->add($me::getDBAclass(), $me);

    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->options($baseUriOne, function (Request $request, Response $response): Response {
      return $response;
    });

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
