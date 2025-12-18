<?php

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteContext;

use DBA\AccessGroup;
use DBA\AccessGroupAgent;
use DBA\AccessGroupUser;
use DBA\Agent;
use DBA\AgentBinary;
use DBA\AgentError;
use DBA\AgentStat;
use DBA\Assignment;
use DBA\Chunk;
use DBA\ComparisonFilter;
use DBA\Config;
use DBA\ConfigSection;
use DBA\CrackerBinary;
use DBA\CrackerBinaryType;
use DBA\File;
use DBA\FilePretask;
use DBA\FileTask;
use DBA\Hash;
use DBA\Hashlist;
use DBA\HashlistHashlist;
use DBA\HashType;
use DBA\HealthCheck;
use DBA\HealthCheckAgent;
use DBA\NotificationSetting;
use DBA\Pretask;
use DBA\RegVoucher;
use DBA\RightGroup;
use DBA\Speed;
use DBA\Supertask;
use DBA\Task;
use DBA\TaskWrapper;
use DBA\User;

use DBA\Factory;
use DBA\ContainFilter;
use DBA\LikeFilter;
use DBA\LikeFilterInsensitive;
use DBA\LogEntry;
use DBA\Preprocessor;
use DBA\QueryFilter;
use DBA\SupertaskPretask;
use Psr\Container\ContainerInterface;

require_once(dirname(__FILE__) . "/../../load.php");


/**
 * This class acts as the BaseAPI implementation of API model endpoints
 */
abstract class AbstractBaseAPI {
  abstract public static function getBaseUri(): string;
  
  abstract public function getRequiredPermissions(string $method): array;
  
  /** @var DBA\User|null $user is currently logged in user */
  private User|null $user;
  
  /** @var RouteParserInterface|null $routeParser contains routing information
   * which are for example used dynamic creation of _self references
   */
  protected RouteParserInterface|null $routeParser;
  
  /** @var ContainerInterface|null $container dynamically generated model mappings
   * which are for example used for retrival of objects based on string identity
   */
  protected ContainerInterface|null $container;
  
  /** @var mixed|null $permissionErrors contained detailed results of last
   * validatePermissions function call
   */
  protected mixed $permissionErrors = null;

  protected mixed $missing_permissions = [];
  
  /**
   * @var array|null list of model classes which will need to be filtered for public attributes because
   * no read access on the whole model exists
   */
  protected array|null $publicAttributeFilterClasses;
  
  /**
   * Constructor receives container instance
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
    $this->publicAttributeFilterClasses = [];
  }
  
  /**
   * Checks if a user has access to the given single element retrieved
   * @param User $user
   * @param object $object
   * @return bool true if the access is allowed
   */
  protected function getSingleACL(User $user, object $object): bool {
    return true;
  }
  
  /**
   * Returns an array containing all filters and joins to be added to the query to ensure
   * that only the elements are retrieved matching the access groups the user is in
   * @return array
   */
  protected function getFilterACL(): array {
    return [];
  }
  
  /**
   * Extra fields which are valid for creation of object
   */
  public function getFormFields(): array {
    return [];
  }
  
  /**
   * Get input field names valid for creation of object
   */
  final public function getCreateValidFeatures(): array {
    return $this->getAliasedFeatures();
  }
  
  /**
   * Create features from form fields
   */
  protected function getFeatures(): array {
    $features = [];
    foreach ($this->getFormFields() as $key => $feature) {
      /* Initiate default values */
      $features[$key] = $feature + ['null' => False, 'protected' => False, 'private' => False, 'choices' => "unset", 'pk' => False, 'read_only' => True];
      if (!array_key_exists('alias', $feature)) {
        $features[$key]['alias'] = $key;
      }
    }
    return $features;
  }

  /**
   * Get features based on DBA model features
   *
   * @param string $dbaClass is the dba class to get the features from
   */
  //TODO doesnt retrieve features based on form fields, could be done by adding api class in relationship objects
  final protected function getFeaturesOther(string $dbaClass): array {
    return call_user_func($dbaClass . '::getFeatures');
  }
  
  protected function getUpdateHandlers($id, $current_user): array {
    return [];
  }
  
  /**
   * Overridable function to aggregate data in the object. Used for Tasks and Agents.
   *
   * @param object $object The object to aggregate data from.
   * @param array &$includedData Array passed by reference; implementations can add related resources
   *   for inclusion in the response by appending to this array.
   * @return array Aggregated data as key-value pairs.
   *
   * Implementations should use $includedData to collect related resources that should be included
   * in the API response, such as related entities or additional data.
   */
  public static function aggregateData(object $object, array &$includedData = [], array $aggregateFieldsets = null): array
  {
    return [];
  }
  
  /**
   * Take all the dba features and converts them to a list.
   * It uses the data from the generator and replaces the keys with the aliases.
   * structure: hashlist: name: [dbname => hashlistId]
   */
  public function getAliasedFeatures(): array {
    $features = $this->getFeatures();
    return $this->mapFeatures($features);
  }

  public function getAliasedFeaturesOther($dbaClass): array {
    $features = $this->getFeaturesOther($dbaClass);
    return $this->mapFeatures($features);
  }
  
  final protected function mapFeatures($features): array {
    $mappedFeatures = [];
    foreach ($features as $key => $value) {
      $mappedFeatures[$value['alias']] = $value;
      $mappedFeatures[$value['alias']]['dbname'] = $key;
    }
    return $mappedFeatures;
  }

  public static function getToOneRelationships(): array {
    return [];
  }
  
  public static function getToManyRelationships(): array {
    return [];
  }

  public function getAllRelationships(): array {
    return array_merge($this->getToOneRelationships(), $this->getToManyRelationships());
  }
  
  /**
   * Retrieve currently logged-in user
   */
  final protected function getCurrentUser(): User {
    return $this->user;
  }

  public function setCurrentUser(User $user): void {
    $this->user = $user;
  }
  
  
  protected static function getModelFactory(string $model): object {
    switch ($model) {
      case AccessGroup::class:
        return Factory::getAccessGroupFactory();
      case AccessGroupAgent::class:
        return Factory::getAccessGroupAgentFactory();
      case AccessGroupUser::class:
        return Factory::getAccessGroupUserFactory();
      case Agent::class:
        return Factory::getAgentFactory();
      case AgentBinary::class:
        return Factory::getAgentBinaryFactory();
      case AgentError::class:
        return Factory::getAgentErrorFactory();
      case AgentStat::class:
        return Factory::getAgentStatFactory();
      case Assignment::class:
        return Factory::getAssignmentFactory();
      case Chunk::class:
        return Factory::getChunkFactory();
      case Config::class:
        return Factory::getConfigFactory();
      case ConfigSection::class:
        return Factory::getConfigSectionFactory();
      case CrackerBinary::class:
        return Factory::getCrackerBinaryFactory();
      case CrackerBinaryType::class:
        return Factory::getCrackerBinaryTypeFactory();
      case File::class:
        return Factory::getFileFactory();
      case FileTask::class:
        return Factory::getFileTaskFactory();
      case FilePretask::class:
        return Factory::getFilePretaskFactory();
      case Hash::class:
        return Factory::getHashFactory();
      case Hashlist::class:
        return Factory::getHashlistFactory();
      case HashlistHashlist::class:
        return Factory::getHashlistHashlistFactory();
      case HashType::class:
        return Factory::getHashTypeFactory();
      case HealthCheckAgent::class:
        return Factory::getHealthCheckAgentFactory();
      case HealthCheck::class:
        return Factory::getHealthCheckFactory();
      case LogEntry::class:
        return Factory::getLogEntryFactory();
      case NotificationSetting::class:
        return Factory::getNotificationSettingFactory();
      case Preprocessor::class:
        return Factory::getPreprocessorFactory();
      case Pretask::class:
        return Factory::getPretaskFactory();
      case RegVoucher::class:
        return Factory::getRegVoucherFactory();
      case RightGroup::class:
        return Factory::getRightGroupFactory();
      case Speed::class:
        return Factory::getSpeedFactory();
      case Supertask::class:
        return Factory::getSupertaskFactory();
      case SupertaskPretask::class:
        return Factory::getSupertaskPretaskFactory();
      case Task::class:
        return Factory::getTaskFactory();
      case TaskWrapper::class:
        return Factory::getTaskWrapperFactory();
      case User::class:
        return Factory::getUserFactory();
    }
    assert(False, "Model '$model' cannot be mapped to Factory");
  }
  
  /**
   * @param string $model
   * @param int $pk
   * @return object
   * @throws ResourceNotFoundError
   */
  final protected static function fetchOne(string $model, int $pk): object {
    $factory = self::getModelFactory($model);
    $object = $factory->get($pk);
    if ($object === null) {
      throw new ResourceNotFoundError("$model '$pk' not found!", 400);
    }
    return $object;
  }
  
  final protected static function getChunk(int $pk): Chunk {
    return self::fetchOne(Chunk::class, $pk);
  }
  
  final protected static function getCrackerBinary(int $pk): CrackerBinary {
    return self::fetchOne(CrackerBinary::class, $pk);
  }
  
  final protected static function getHashlist(int $pk): Hashlist {
    return self::fetchOne(Hashlist::class, $pk);
  }
  
  final protected static function getPretask(int $pk): Pretask {
    return self::fetchOne(Pretask::class, $pk);
  }
  
  final protected static function getRightGroup(int $pk): RightGroup {
    return self::fetchOne(RightGroup::class, $pk);
  }
  
  final protected static function getSupertask(int $pk): Supertask {
    return self::fetchOne(Supertask::class, $pk);
  }
  
  final protected static function getTask(int $pk): Task {
    return self::fetchOne(Task::class, $pk);
  }
  
  final protected static function getTaskWrapper(int $pk): TaskWrapper {
    return self::fetchOne(TaskWrapper::class, $pk);
  }
  
  final protected static function getUser(int $pk): User {
    return self::fetchOne(User::class, $pk);
  }
  
  /**
   * Return Object Resource Type Identifier of API object.
   *
   * @param mixed $obj
   * @return string
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   */
  final protected function getObjectTypeName(mixed $obj): string {
    
    $container = $this->container->get('classMapper');
    
    if (is_string($obj)) {
      $apiClass = $this->container->get('classMapper')->get($obj);
    }
    else {
      $apiClass = $this->container->get('classMapper')->get(get_class($obj));
    }
    
    /* Use the API class Name as type identifier written in camelCase*/
    return lcfirst(substr($apiClass, 0, -3));
  }
  
  /**
   * Retrieve permissions based on expand section
   * @throws InternalError
   */
  protected static function getExpandPermissions(string $expand): array {
    $expand_to_perm_mapping = array(
      'assignedAgents' => [Agent::PERM_READ],
      'assignments' => [Assignment::PERM_READ],
      'agent' => [Agent::PERM_READ],
      'agents' => [AccessGroup::PERM_READ],
      'agentErrors' => [AgentError::PERM_READ],
      'agentStats' => [AgentStat::PERM_READ],
      'accessGroups' => [AccessGroup::PERM_READ],
      'accessGroup' => [AccessGroup::PERM_READ],
      'chunk' => [Chunk::PERM_READ],
      'chunks' => [Chunk::PERM_READ],
      'configSection' => [ConfigSection::PERM_READ],
      'crackerBinary' => [CrackerBinary::PERM_READ],
      'crackerBinaryType' => [CrackerBinaryType::PERM_READ],
      'crackerVersions' => [CrackerBinary::PERM_READ],
      'hashes' => [Hash::PERM_READ],
      'hashlist' => [Hashlist::PERM_READ],
      'hashlists' => [Hashlist::PERM_READ],
      'hashType' => [HashType::PERM_READ],
      'healthCheck' => [HealthCheck::PERM_READ],
      'healthCheckAgents' => [HealthCheckAgent::PERM_READ],
      'globalPermissionGroup' => [RightGroup::PERM_READ],
      'task' => [Task::PERM_READ],
      'tasks' => [Task::PERM_READ],
      'speeds' => [Speed::PERM_READ],
      'pretaskFiles' => [FilePretask::PERM_READ, File::PERM_READ],
      'files' => [FileTask::PERM_READ, File::PERM_READ],
      'pretasks' => [Supertask::PERM_READ, Pretask::PERM_READ],
      'user' => [User::PERM_READ],
      'users' => [User::PERM_READ],
      'userMembers' => [User::PERM_READ],
      'agentMembers' => [Agent::PERM_READ],
    );
    
    if (array_key_exists($expand, $expand_to_perm_mapping) === False) {
      throw new InternalError("Internal error: Expand type '$expand' has no permission mapping implemented in getExpandPermissions()!");
    }
    return $expand_to_perm_mapping[$expand];
  }
  
  /**
   * Temporary mapping until src/inc/defines/accessControl.php permissions are no longer used
   */
  public static array $acl_mapping = array(
    DAccessControl::VIEW_HASHLIST_ACCESS[0] => array(Hashlist::PERM_READ),
    DAccessControl::MANAGE_HASHLIST_ACCESS => array(Hashlist::PERM_READ, Hashlist::PERM_UPDATE, Hashlist::PERM_DELETE,
                                                    Hash::PERM_READ, Hash::PERM_UPDATE, Hash::PERM_DELETE
    ),
    DAccessControl::CREATE_HASHLIST_ACCESS => array(Hashlist::PERM_CREATE, Hash::PERM_CREATE),
    
    DAccessControl::CREATE_SUPERHASHLIST_ACCESS => array(HashlistHashlist::PERM_CREATE, HashlistHashlist::PERM_READ),
    
    DAccessControl::VIEW_HASHES_ACCESS => array(Hash::PERM_READ),
    DAccessControl::VIEW_AGENT_ACCESS[0] => array(Agent::PERM_READ, Assignment::PERM_READ, AgentError::PERM_READ),
    
    DAccessControl::MANAGE_AGENT_ACCESS => array(Agent::PERM_READ, Agent::PERM_UPDATE, Agent::PERM_DELETE,
                                                 // src/inc/defines/agents.php
                                                 AgentStat::PERM_CREATE, AgentStat::PERM_READ, AgentStat::PERM_UPDATE, AgentStat::PERM_DELETE,
                                                 Assignment::PERM_CREATE, Assignment::PERM_READ, Assignment::PERM_UPDATE, Assignment::PERM_DELETE,
                                                 AgentError::PERM_DELETE
    
    ),
    
    DAccessControl::CREATE_AGENT_ACCESS => array(Agent::PERM_CREATE, Agent::PERM_READ,
                                                 // src/inc/defines/agents.php
                                                 RegVoucher::PERM_CREATE, RegVoucher::PERM_READ, RegVoucher::PERM_UPDATE, RegVoucher::PERM_DELETE
    ),
    
    DAccessControl::VIEW_TASK_ACCESS[0] => array(Task::PERM_READ, Speed::PERM_READ, Chunk::PERM_READ, FileTask::PERM_READ),
    DAccessControl::RUN_TASK_ACCESS[0] => array(Task::PERM_CREATE, FileTask::PERM_CREATE),
    DAccessControl::CREATE_TASK_ACCESS[0] => array(Task::PERM_CREATE, FileTask::PERM_CREATE,
                                                   Task::PERM_READ, Chunk::PERM_READ, FileTask::PERM_READ,
                                                   TaskWrapper::PERM_CREATE, TaskWrapper::PERM_READ
    ),
    DAccessControl::MANAGE_TASK_ACCESS => array(Task::PERM_READ, Task::PERM_UPDATE, Task::PERM_DELETE,
                                                Chunk::PERM_READ, Chunk::PERM_UPDATE, Chunk::PERM_DELETE,
                                                // src/inc/defines/tasks.php
                                                TaskWrapper::PERM_READ, TaskWrapper::PERM_UPDATE, TaskWrapper::PERM_DELETE,
                                                FileTask::PERM_READ, FileTask::PERM_UPDATE, FileTask::PERM_DELETE
    ),
    
    DAccessControl::VIEW_PRETASK_ACCESS[0] => array(Pretask::PERM_READ, FilePretask::PERM_READ),
    DAccessControl::CREATE_PRETASK_ACCESS => array(Pretask::PERM_READ, Pretask::PERM_CREATE, FilePretask::PERM_CREATE),
    DAccessControl::MANAGE_PRETASK_ACCESS => array(Pretask::PERM_READ, Pretask::PERM_UPDATE, Pretask::PERM_DELETE, FilePretask::PERM_UPDATE, FilePretask::PERM_DELETE),
    
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
                                                   AgentBinary::PERM_CREATE, AgentBinary::PERM_READ, AgentBinary::PERM_UPDATE, AgentBinary::PERM_DELETE
    ),
    
    DAccessControl::SERVER_CONFIG_ACCESS => array(Config::PERM_CREATE, Config::PERM_READ, Config::PERM_UPDATE, Config::PERM_DELETE,
                                                  ConfigSection::PERM_CREATE, ConfigSection::PERM_READ, ConfigSection::PERM_UPDATE, ConfigSection::PERM_DELETE,
                                                  // src/inc/defines/preprocessor.php
                                                  Preprocessor::PERM_CREATE, Preprocessor::PERM_READ, Preprocessor::PERM_UPDATE, Preprocessor::PERM_DELETE,
                                                  // src/inc/defines/health.php
                                                  HealthCheck::PERM_CREATE, HealthCheck::PERM_READ, HealthCheck::PERM_UPDATE, HealthCheck::PERM_DELETE,
                                                  HealthCheckAgent::PERM_CREATE, HealthCheckAgent::PERM_READ, HealthCheckAgent::PERM_UPDATE, HealthCheckAgent::PERM_DELETE,
                                                  // src/inc/defines/hashlists.php
                                                  HashType::PERM_CREATE, HashType::PERM_READ, HashType::PERM_UPDATE, HashType::PERM_DELETE
    ),
    
    DAccessControl::USER_CONFIG_ACCESS => array(User::PERM_CREATE, User::PERM_READ, User::PERM_UPDATE, User::PERM_DELETE, RightGroup::PERM_CREATE, RightGroup::PERM_READ, RightGroup::PERM_UPDATE, RightGroup::PERM_DELETE),
    
    DAccessControl::MANAGE_ACCESS_GROUP_ACCESS => array(AccessGroup::PERM_CREATE, AccessGroup::PERM_READ, AccessGroup::PERM_UPDATE, AccessGroup::PERM_DELETE),
    
    // src/inc/defines/accessControl.php
    DAccessControl::PUBLIC_ACCESS => array(LogEntry::PERM_READ),
    
    // src/inc/defines/notifications.php
    DAccessControl::LOGIN_ACCESS => array(NotificationSetting::PERM_CREATE, NotificationSetting::PERM_READ, NotificationSetting::PERM_UPDATE, NotificationSetting::PERM_DELETE, LogEntry::PERM_CREATE, LogEntry::PERM_DELETE, LogEntry::PERM_UPDATE),
  );
  
  /**
   * Convert Database value to JSON object value
   */
  protected static function db2json(array $feature, mixed $val): mixed {
    if ($feature['type'] == 'bool') {
      $obj = $val == "1";
    }
    elseif ($feature['type'] == 'dict') {
      $obj = json_decode($val, true, 512, JSON_OBJECT_AS_ARRAY);
      // During encoding of the data, the data is saved as an empty array
      // An empty array is something different in json and in python.
      // The following code casts the empty array to an empty 'object'
      // which will be interpreted by python and json correctly as dict or object.
      if (empty($obj)) {
        $obj = (object)[];
      }
    }
    elseif ($feature['type'] == 'array' && $feature['subtype'] == 'int') {
      $obj = array_map('intval', preg_split("/,/", $val, -1, PREG_SPLIT_NO_EMPTY));
    }
    elseif (str_starts_with($feature['type'], 'str') && $val !== null) {
      $obj = html_entity_decode($val, ENT_COMPAT, "UTF-8");
    }
    else {
      // TODO: Check all objects, instead of wild cast to hopefully-JSON compatible object
      $obj = $val;
    }
    return $obj;
  }
  
  /**
   * Convert JSON object value to DB insert value, supported by DBA
   */
  protected static function json2db(array $feature, mixed $obj): ?string {
    if ($feature['null'] && is_null($obj)) {
      return null;
    }
    elseif ($feature['type'] == 'bool') {
      $val = ($obj) ? "1" : "0";
    }
    elseif ($feature['type'] == 'int' && is_null($obj)) {
      $val = $obj;
    }
    elseif (str_starts_with($feature['type'], 'str')) {
      $val = htmlentities($obj, ENT_QUOTES, "UTF-8");
    }
    elseif ($feature['type'] == 'array' && $feature['subtype'] == 'int') {
      $val = implode(",", $obj);
    }
    elseif ($feature['type'] == 'dict' && $feature['subtype'] == 'bool') {
      $val = serialize($obj);
    }
    else {
      $val = strval($obj);
    }
    return $val;
  }
  
  /**
   * Convert JSON object value to DB insert value, supported by DBA
   * @throws NotFoundExceptionInterface
   * @throws ContainerExceptionInterface,
   */
  protected function obj2Array(object $obj): array {
    // Convert values to JSON supported types
    $features = $obj->getFeatures();
    $kv = $obj->getKeyValueDict();
    
    $item = [];
    
    $apiClass = $this->container->get('classMapper')->get(get_class($obj));
    $item['_id'] = $obj->getId();
    $item['_self'] = $this->routeParser->urlFor($apiClass . ':getOne', ['id' => $item['_id']]);
    
    foreach ($features as $name => $feature) {
      // If a attribute is set to private, it should be hidden and not returned.
      // Example of this is the password hash.
      if ($feature['private'] === true) {
        continue;
      }
      $item[$feature['alias']] = $apiClass::db2json($feature, $kv[$name]);
    }
    return $item;
  }
  
  /**
   * Convert DB object JSON:API Resource Object
   * @throws NotFoundExceptionInterface
   * @throws ContainerExceptionInterface
   */
  protected function obj2Resource(object $obj, array &$expandResult = [], array $sparseFieldsets = null, array $aggregateFieldsets = null): array {
    // Convert values to JSON supported types
    $features = $obj->getFeatures();
    $kv = $obj->getKeyValueDict();
    
    $apiClass = $this->container->get('classMapper')->get(get_class($obj));
    $linkSelf = $this->routeParser->urlFor($apiClass . ':getOne', ['id' => $obj->getId()]);
    
    $attributes = [];
    $relationships = [];

    $sparseFieldsetsForObj = null;
    if (is_array($sparseFieldsets) && array_key_exists($this->getObjectTypeName($obj), $sparseFieldsets)) {
      $sparseFieldsetsForObj = explode(",", $sparseFieldsets[$this->getObjectTypeName($obj)]);
    }
    
    /* Collect attributes */
    foreach ($features as $name => $feature) {
      // If a attribute is set to private, it should be hidden and not returned.
      // Example of this is the password hash.
      if ($feature['private'] === true) {
        continue;
      }

      // If sparse fieldsets (https://jsonapi.org/format/#fetching-sparse-fieldsets) is used, return only the requested data
      if (is_array($sparseFieldsetsForObj) && !in_array($feature['alias'], $sparseFieldsetsForObj)) {
        continue;
      }

      // Hide the primaryKey from the attributes since this is used as indentifier (id) in response
      if ($feature['pk'] === true) {
        continue;
      }
      
      if (is_array($this->publicAttributeFilterClasses) && in_array($obj::class, $this->publicAttributeFilterClasses) && $feature['public'] !== true) {
        continue;
      }
      
      $attributes[$feature['alias']] = $apiClass::db2json($feature, $kv[$name]);
    }

    $aggregatedData = $apiClass::aggregateData($obj, $expandResult, $aggregateFieldsets);
    $attributes = array_merge($attributes, $aggregatedData);
    
    /* Build JSON::API relationship resource */
    $toManyRelationships = $apiClass::getToManyRelationships();
    $toOneRelationships = $apiClass::getToOneRelationships();
    
    $relationshipsNames = array_merge(array_keys($toOneRelationships), array_keys($toManyRelationships));
    sort($relationshipsNames);
    foreach ($relationshipsNames as $relationshipName) {
      $relationships[$relationshipName] = [
        "links" => [
          "self" => $linkSelf . "/relationships/" . $relationshipName,
          "related" => $linkSelf . "/" . $relationshipName,
        ]
      ];
    }
    
    /* Generate to-many relationships entries */
    foreach ($toManyRelationships as $relationshipName => $toManyRelationship) {
      // Build (optional) compound document resource linkage
      if (array_key_exists($relationshipName, $expandResult)) {
        $relationships[$relationshipName]["data"] = [];
        
        // Empty to-many relationship
        if (array_key_exists($obj->getId(), $expandResult[$relationshipName]) === false) {
          continue;
        }
        
        // Fetch to-many-objects
        $expandObjects = $expandResult[$relationshipName][$obj->getId()];
        foreach ($expandObjects as $relationObject) {
          $relationships[$relationshipName]["data"][] = [
            "type" => $this->getObjectTypeName($relationObject),
            "id" => $relationObject->getId()
          ];
        }
      }
    }
    
    /* Generate to-one relationships entries */
    foreach ($toOneRelationships as $relationshipName => $toOneRelationship) {
      // Build (optional) compound document resource linkage
      if (array_key_exists($relationshipName, $expandResult)) {
        // Empty to-one relationship
        if (array_key_exists($obj->getId(), $expandResult[$relationshipName]) === false) {
          $relationships[$relationshipName]["data"] = null;
          continue;
        }
        
        // Fetch to-one-objects
        $expandObject = $expandResult[$relationshipName][$obj->getId()];
        
        $relationships[$relationshipName]["data"] = [
          "type" => $this->getObjectTypeName($expandObject),
          "id" => $expandObject->getId()
        ];
      }
    }
    
    
    $newObject = [
      "type" => $this->getObjectTypeName($obj),
      "id" => $obj->getId(),
      "attributes" => $attributes,
      "links" => [
        "self" => $linkSelf,
      ],
    ];
    
    if (sizeof($relationships) > 0) {
      $newObject['relationships'] = $relationships;
    }
    
    return $newObject;
  }
  
  /**
   * Quick to resolve objects via ManyToMany relation table
   * @throws NotFoundExceptionInterface
   * @throws ContainerExceptionInterface
   */
  protected function joinQuery(mixed $objFactory, DBA\QueryFilter $qF, DBA\JoinFilter $jF): array {
    $joined = $objFactory->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    $objects = $joined[$objFactory->getModelName()];
    
    $ret = [];
    foreach ($objects as $object) {
      $ret[] = $this->obj2Array($object);
    }
    return $ret;
  }
  
  /**
   * Quick to resolve objects via ForeignKey relation table
   * @throws NotFoundExceptionInterface
   * @throws ContainerExceptionInterface
   */
  protected function filterQuery(mixed $objFactory, DBA\QueryFilter $qF): array {
    $objects = $objFactory->filter([Factory::FILTER => $qF]);
    
    $ret = [];
    foreach ($objects as $object) {
      $ret[] = $this->obj2Array($object);
    }
    return $ret;
  }
  
  /**
   * @param object $object
   * @param array $expands
   * @param array $expandResult
   * @return array
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   */
  protected function applyExpansions(object $object, array $expands, array $expandResult): array {
    $newObject = $this->obj2Array($object);
    foreach ($expands as $expand) {
      if (!array_key_exists($object->getId(), $expandResult[$expand])) {
        $newObject[$expand] = [];
        continue;
      }
      
      $expandObject = $expandResult[$expand][$object->getId()];
      if (is_array($expandObject)) {
        $newObject[$expand] = array_map(function ($object) {
          return $this->obj2Array($object);
        }, $expandObject);
      }
      else {
        $newObject[$expand] = $this->obj2Array($expandObject);
      }
    }
    
    /* Ensure sorted, for easy debugging of fields */
    ksort($newObject);
    
    return $newObject;
  }
  
  /**
   * Expands object items
   * @throws NotFoundExceptionInterface
   * @throws ContainerExceptionInterface
   */
  protected function object2Array(object $object, array $expands = []): array {
    $expandResult = [];
    foreach ($expands as $expand) {
      $apiClass = $this->container->get('classMapper')->get(get_class($object));
      $expandResult[$expand] = $apiClass::fetchExpandObjects([$object], $expand);
    }
    
    return $this->applyExpansions($object, $expands, $expandResult);
  }
  
  /**
   * Uniform conversion of php array to JSON output
   * @throws JsonException
   */
  protected static function ret2json(array $result): string {
    return json_encode($result, JSON_UNESCAPED_SLASHES |  JSON_THROW_ON_ERROR) . PHP_EOL;
  }
  
  /**
   * Helper conversion of single object to JSON string
   * @param object $object
   * @return string
   * @throws ContainerExceptionInterface
   * @throws JsonException
   * @throws NotFoundExceptionInterface
   */
  protected function object2JSON(object $object): string {
    $item = $this->object2Array($object, []);
    return $this->ret2json($item);
  }
  
  /**
   * Convert incoming (JSON) data to DB values
   */
  protected function unaliasData(array $data, array $features): array {
    $mappedData = [];
    foreach ($data as $key => $value) {
      $mappedData[$features[$key]['dbname']] = self::json2db($features[$key], $value);
    }
    return $mappedData;
  }
  
  /**
   * Validate the Permission of a DBA column and check if it key may be altered
   *
   * @param array $features The features of the DBA object of the child
   * @param string $key Field to use as base for $objects
   * @param bool $toNull set this to true if it should be checked if the value can be set to null
   * @return void
   * @throws HttpError
   * @throws HttpForbidden when it is not allowed to alter the key
   */
  protected function isAllowedToMutate(array $features, string $key, bool $toNull = false): void {
    // Ensure key exists in target array
    if (!array_key_exists($key, $features)) {
      throw new HttpError("Key '$key' does not exists!");
    }
    
    if ($features[$key]['read_only']) {
      throw new HttpForbidden("Key '$key' is immutable");
    }
    if ($features[$key]['protected']) {
      throw new HttpForbidden("Key '$key' is protected");
    }
    if ($features[$key]['private']) {
      throw new HttpForbidden("Key '$key' is private");
    }
    if ($toNull && $features[$key]['null'] == false){
      throw new HttpForbidden("Key '$key' can not be set to NULL!");
    }
  }
  
  /**
   * Validate incoming data
   * @throws HttpError
   */
  protected function validateData(array $data, array $features): void {
    foreach ($data as $key => $value) {
      // Validate if field can be left empty or not
      if (!($features[$key]['null'] ?? True)) {
        if (is_null($value)) {
          throw new HttpError("Key '$key' cannot be null.");
        }
      }
      else {
        if (is_null($value)) {
          // Key can be null and is null, so skip type checking.
          continue;
        }
      }
      
      // Perform type mapping
      if ($features[$key]['type'] == 'bool') {
        if (!is_bool($value)) {
          throw new HttpError("Key '$key' is not of type boolean");
        }
        // Int
      }
      elseif (str_starts_with($features[$key]['type'], 'int')) {
        if (!is_integer($value)) {
          throw new HttpError("Key '$key' is not of type integer");
        }
        $maxValue = ($features[$key]['type'] === 'int64') ? 9223372036854775807 : 2147483647;
        if ($value > $maxValue || $value < -$maxValue) {
          throw new HttpError("The value exceeds the limit for a {$features[$key]['type']} integer.");
        }
        // Str
      }
      elseif (str_starts_with($features[$key]['type'], 'str')) {
        if (!is_string($value)) {
          throw new HttpError("Key '$key' is not of type string");
        }
        if (preg_match('/str\((\d+)\)/', $features[$key]['type'], $matches)) {
          $max_string_len = (int)$matches[1];
          if (strlen($value) > $max_string_len) {
            throw new HttpError("The string value: '$value' is too long. The max size is '$max_string_len'");
          }
        }
        // TODO: Length validation
        // Array
      }
      elseif (str_starts_with($features[$key]['type'], 'array')) {
        if (!is_array($value)) {
          throw new HttpError("Key '$key' is not of type array");
        }
        // Array[Int]
        if ($features[$key]['subtype'] == 'int') {
          if (in_array(false, array_map('is_integer', $value))) {
            throw new HttpError("Key '$key' array contains non-integer values");
          }
        }
        // Dict
      }
      elseif (str_starts_with($features[$key]['type'], 'dict')) {
        if (!is_array($value)) {
          throw new HttpError("Key '$key' is not of type dict");
        }
        // Dict[Bool]
        if ($features[$key]['subtype'] == 'bool') {
          if (in_array(false, array_map('is_bool', $value))) {
            throw new HttpError("Key '$key' dict contains non-boolean values");
          }
        }
      }
      else {
        throw new HttpError("Typemapping error for key '$key' ");
      }
      
      // Validate values limited by choices
      if (is_array($features[$key]['choices'])) {
        if (!array_key_exists($value, $features[$key]['choices'])) {
          throw new HttpError("Key '$key' value is not valid, choices=[" .
            join(",", array_keys($features[$key]['choices'])) .
            "], choices_details=['" .
            join("', '", array_values($features[$key]['choices'])) . "']"
          );
        }
      }
    }
  }
  
  //function for automatic swagger doc generation
  function getAllPostParameters(array $features): array {
    return array_filter($features, function ($value) {
      return $value['protected'] == False;
    });
  }
  
  /**
   * Validate incoming parameter keys
   * @throws HttpError
   */
  protected function validateParameters(array $data, array $allFeatures): void {
    // Features which MAY be present
    $validFeatures = [];
    // Features which MUST be present
    $requiredFeatures = [];
    foreach ($allFeatures as $key => $value) {
      if (!$value['protected'] and !$value['private']) {
        $validFeatures[] = $key;
      }
      if (!$value['protected'] and !$value['null']) {
        $requiredFeatures[] = $key;
      }
    }
    
    // Find keys which are invalid
    $invalidKeys = array_diff(array_keys($data), $validFeatures);
    if (sizeof($invalidKeys) > 0) {
      // Ensure debugging response lists are in sorted order
      ksort($invalidKeys);
      ksort($validFeatures);
      throw new HttpError("Parameter(s) '" . join(", ", $invalidKeys) . "' not valid input " .
        "(valid key(s) : '" . join(", ", $validFeatures) . ")'", 403
      );
    }
    
    // Find out about mandatory parameters which are not provided
    $missingKeys = array_diff($requiredFeatures, array_keys($data));
    if (count($missingKeys) > 0) {
      // Ensure debugging response lists are in sorted order
      ksort($missingKeys);
      throw new HttpError("Required parameter(s) '" . join(", ", $missingKeys) . "' not specified");
    }
  }
  
  /**
   * Check for valid expand parameters.
   * @throws HttpError
   * @throws InternalError
   */
  //TODO: nice to have would be to be able to include objects that are further away in the relationship
  //ex. from Hash include=hashlist.task to include all tasks from a hash (section 8.3 JSON API) 
  protected function makeExpandables(Request $request, array $validExpandables): array {
    $data = $request->getParsedBody();
    $queryExpands = (array_key_exists('include', $request->getQueryParams())) ? preg_split("/[,\ ]+/", $request->getQueryParams()['include']) : [];
    
    foreach ($queryExpands as $expand) {
      if (!in_array($expand, $validExpandables)) {
        throw new HttpError("Parameter '" . $expand . "' is not valid expand key (valid keys are: " . join(", ", array_values($validExpandables)) . ")");
      }
    }
    
    /* Validate expand parameters for required permissions */
    $required_perms = [];
    $permsExpandMatching = [];
    foreach ($queryExpands as $expand) {
      $expandedPerms = self::getExpandPermissions($expand);
      foreach ($expandedPerms as $expandedPerm) {
        if (!isset($permsExpandMatching[$expandedPerm])) {
          $permsExpandMatching[$expandedPerm] = [$expand];
        }
        else {
          $permsExpandMatching[$expandedPerm][] = $expand;
        }
      }
      array_push($required_perms, ...$expandedPerms);
    }
    $permissionResponse = $this->validatePermissions($required_perms, $permsExpandMatching);
    $expands_to_remove = [];

    // remove expands with missing permissions
    foreach($this->missing_permissions as $missing_permission) {
      $expands_to_remove = array_merge($expands_to_remove, $permsExpandMatching[$missing_permission]);
    }
    $queryExpands = array_diff($queryExpands, $expands_to_remove);

    // if ($permissionResponse === FALSE) {
    //   throw new HttpError('Permissions missing on expand parameter objects! || ' . join('||', $this->permissionErrors));
    // }
    return $queryExpands;
  }
  
  /**
   * Find primary key for DBA object
   * @throws InternalError
   */
  protected function getPrimaryKey(): string {
    $features = $this->getFeatures();
    # Work-around required since getPrimaryKey is not static in dba/models/*.php
    foreach ($features as $key => $value) {
      if ($value['pk']) {
        return $key;
      }
    }
    throw new InternalError("Internal error: no primary key found");
  }
  
  function getFilters(Request $request): array {
    return $this->getQueryParameterFamily($request, 'filter');
  }
  
  /**
   * Check for valid filter parameters and build QueryFilter
   * @throws HttpForbidden
   * @throws InternalError
   */
  protected function makeFilter(array $filters, object $apiClass): array {
    $qFs = [];
    $features = $apiClass->getAliasedFeatures();
    $factory = $apiClass->getFactory();
    foreach ($filters as $filter => $value) {
      
      if (preg_match('/^(?P<key>[_a-zA-Z0-9]+?)(?<operator>|__eq|__ne|__lt|__lte|__gt|__gte|__contains|__startswith|__endswith|__icontains|__istartswith|__iendswith|__in|__nin)$/', $filter, $matches) == 0) {
        throw new HttpForbidden("Filter parameter '" . $filter . "' is not valid");
      }
      
      // Special filtering of _id to use for uniform access to model primary key
      $cast_key = $matches['key'] == 'id' ? array_column($features, 'alias', 'dbname')[$this->getPrimaryKey()] : $matches['key'];
      
      if (!array_key_exists($cast_key, $features)) {
        throw new HttpForbidden("Filter parameter '" . $filter . "' is not valid (key not valid field)");
      };
      
      $valueList = explode(",", $value);
      
      // TODO Merge/Combine with validate parameters 
      foreach ($valueList as &$value) {
        switch ($features[$cast_key]['type']) {
          case 'bool':
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (is_null($value)) {
              throw new HttpForbidden("Filter parameter '" . $filter . "' is not valid boolean value");
            }
            $value = (int)$value;
            break;
          case 'int':
            $value = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
            if (is_null($value)) {
              throw new HttpForbidden("Filter parameter '" . $filter . "' is not valid integer value");
            }
            $value = (int)$value;
            break;
        }
      }
      unset($value);
      
      // We need to remap any aliased key to the key as it appears in the database.
      $remappedKey = $features[$cast_key]['dbname'];
      
      $amount_values = count($valueList);
      $single_val = $valueList[0];
      $operator = $matches['operator'];
      $query_operator = "";
      switch (true) {
        case (($operator == '__eq' | $operator == '') && $amount_values == 1):
          $query_operator = '=';
          break;
        case ($operator == '__ne' && $amount_values == 1):
          $query_operator = '!=';
          break;
        case ($operator == '__lt' && $amount_values == 1):
          $query_operator = '<';
          break;
        case ($operator == '__lte' && $amount_values == 1):
          $query_operator = '<=';
          break;
        case ($operator == '__gt' && $amount_values == 1):
          $query_operator = '>';
          break;
        case ($operator == '__gte' && $amount_values == 1):
          $query_operator = '>=';
          break;
        case ($operator == '__contains' && $amount_values == 1):
          $qFs[] = new LikeFilter($remappedKey, "%" . $single_val . "%", $factory);
          break;
        case ($operator == '__startswith' && $amount_values == 1):
          $qFs[] = new LikeFilter($remappedKey, $single_val . "%", $factory);
          break;
        case ($operator == '__endswith' && $amount_values == 1):
          $qFs[] = new LikeFilter($remappedKey, "%" . $single_val, $factory);
          break;
        case ($operator == '__icontains' && $amount_values == 1):
          $qFs[] = new LikeFilterInsensitive($remappedKey, "%" . $single_val . "%", $factory);
          break;
        case ($operator == '__istartswith' && $amount_values == 1):
          $qFs[] = new LikeFilterInsensitive($remappedKey, $single_val . "%", $factory);
          break;
        case ($operator == '__iendswith' && $amount_values == 1):
          $qFs[] = new LikeFilterInsensitive($remappedKey, "%" . $single_val, $factory);
          break;
        //Filters bellow operate on lists
        case ($operator == '__in'):
          $qFs[] = new ContainFilter($remappedKey, $valueList, $factory);
          break;
        case ($operator == '__nin'):
          $qFs[] = new ContainFilter($remappedKey, $valueList, $factory, true);
          break;
        default:
          assert(False, "Operator '" . $operator . "' not implemented");
      }
      
      if ($query_operator) {
        if (array_key_exists($single_val, $features)) {
          $qFs[] = new ComparisonFilter($remappedKey, $single_val, $query_operator, $factory);
        }
        else {
          $qFs[] = new QueryFilter($remappedKey, $single_val, $query_operator, $factory);
        }
      }
    }
    return $qFs;
  }
  
  
  /**
   * Check for valid ordering parameters and build QueryFilter
   * @throws InternalError
   * @throws HttpForbidden
   */
  protected function makeOrderFilterTemplates(Request $request, array $features, string $defaultSort = 'ASC',
     bool $reverseSort = false): array {
    $orderTemplates = [];
    
    $orderings = $this->getQueryParameterAsList($request, 'sort');
    $contains_primary_key = false;
    foreach ($orderings as $order) {
      $factory = null;
      $joinKey = null;
      $features_sort = $features;
      if (preg_match('/^(?P<operator>[-])?(?P<key>[_a-zA-Z.]+)$/', $order, $matches)) {
        // Special filtering of _id to use for uniform access to model primary key
        $cast_key = $matches['key'] == 'id' ? $this->getPrimaryKey() : $matches['key'];
        if ($cast_key == $this->getPrimaryKey()) {
          $contains_primary_key = true;
        }
        if (strpos($cast_key, ".")) {
          $parts = explode(".", $cast_key);
            if (count($parts) == 2) { // Only relations of 1 deep allowed ex. task.keyspace
              $relationString = $parts[0];
              //currently getting all relationships, but its probably only possible to sort on  1 to 1 relations
              $relations = $this->getAllRelationships();
              if (array_key_exists($relationString, $relations)) {
                $relationClass = $relations[$relationString]['relationType'];
                $relationFeatures = $this->getAliasedFeaturesOther($relationClass);
                $factory = $this->getModelFactory($relationClass);
                $joinKey = $relations[$relationString]['relationKey'];
                $key = $relations[$relationString]['key'];
                $features_sort = $relationFeatures;
                $cast_key = $parts[1];
              }
            }
          }
        if (array_key_exists($cast_key, $features_sort)) {
          $remappedKey = $features_sort[$cast_key]['dbname'];
          $type = ($matches['operator'] == '-') ? "DESC" : "ASC";
          if ($reverseSort) {
            $type = ($type == "ASC") ? "DESC" : "ASC";
          }
          $orderTemplates[] = ['by' => $remappedKey, 'type' => $type, 'factory' => $factory, 'joinKey' => $joinKey, 'key' => $key];
        }
        else {
          throw new HttpForbidden("Ordering parameter '" . $order . "' is not valid");
        }
      }
      else {
        throw new HttpForbidden("Ordering parameter '" . $order . "' is not valid");
      }
    }
    
    //when no primary key has been added in the sort parameter, add the default case of sorting on primary key as last sort
    if (!$contains_primary_key) {
      $orderTemplates[] = ['by' => $this->getPrimaryKey(), 'type' => $defaultSort, 'factory' => null, 'joinKey' => null];
    }
    
    return $orderTemplates;
  }

  protected static function addToRelatedResources(array $relatedResources, array $relatedResource): array {
    $alreadyExists = false;
    $searchType = $relatedResource["type"];
    $searchId = $relatedResource["id"];
    foreach ($relatedResources as $resource) {
      if ($resource["id"] == $searchId && $resource["type"] == $searchType) {
        $alreadyExists = true;
        break;
      }
    }
    if (!$alreadyExists) {
      $relatedResources[] = $relatedResource;
    }
    return $relatedResources;
  }

  protected function processExpands(
    object $apiClass,
    array $expands,
    object $object,
    array $expandResult,
    array $includedResources,
    array $sparseFieldsets = null,
    array $aggregateFieldsets = null
): array {
    
    // Add missing expands to expands in case they have been added in aggregateData()
    $expandKeys = array_keys($expandResult);
    $diffs = array_diff($expandKeys, $expands);
    $expands = array_merge($expands, $diffs);

    foreach ($expands as $expand) {
      if (!array_key_exists($object->getId(), $expandResult[$expand])) {
          continue;
      }

      $expandResultObject = $expandResult[$expand][$object->getId()];

      if (is_array($expandResultObject)) {
        foreach ($expandResultObject as $expandObject) {
          $noFurtherExpands = [];
          $includedResources = self::addToRelatedResources($includedResources, $apiClass->obj2Resource($expandObject, $noFurtherExpands, $sparseFieldsets, $aggregateFieldsets));
        }
      } else {
          if ($expandResultObject === null) {
            // to-only relation which is nullable
            continue;
          }
        $noFurtherExpands = [];
        $includedResources = self::addToRelatedResources($includedResources, $apiClass->obj2Resource($expandResultObject, $noFurtherExpands, $sparseFieldsets, $aggregateFieldsets));
      }
    }

    return $includedResources;
  }

  /**
   * Validate if user is allowed to access hashlist
   * @throws HttpForbidden
   * @throws ResourceNotFoundError
   */
  protected function validateHashlistAccess(Request $request, User $user, string $hashlistId): Hashlist {
    // TODO: Fix permissions
    if (!AccessControl::getInstance($user)->hasPermission(DAccessControl::MANAGE_HASHLIST_ACCESS)) {
      throw new HttpForbidden("No '" . DAccessControl::getDescription(DAccessControl::MANAGE_HASHLIST_ACCESS) . "' permission");
    }
    
    try {
      $hashlist = HashlistUtils::getHashlist($hashlistId);
    }
    catch (HTException $ex) {
      throw new ResourceNotFoundError($ex->getMessage());
    }
    if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
      throw new HttpForbidden("No access to hashlist!");
    }
    
    return $hashlist;
  }
  
  /**
   * Validate permissions
   */
  protected function validatePermissions(array $required_perms, array $permsExpandMatching = []): bool|array {
    // Retrieve permissions from RightGroup part of the User
    $group = Factory::getRightGroupFactory()->get($this->user->getRightGroupId());
    
    if ($group->getPermissions() == 'ALL') {
      // Special (legacy) case for administrative access, enable all available permissions
      $all_perms = array_keys(self::$acl_mapping);
      $rightgroup_perms = array_combine($all_perms, array_fill(0, count($all_perms), true));
    }
    else {
      $rightgroup_perms = json_decode($group->getPermissions(), true);
    }
    
    // Validate if no undefined permissions are set in $acl_mapping
    assert(count(array_diff(array_keys($rightgroup_perms), array_keys(self::$acl_mapping))) == 0);
    
    // Create listing of available permissions for user
    $user_available_perms = array();
    foreach ($rightgroup_perms as $rightgroup_perm => $permission_set) {
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
      if ($this instanceof AbstractModelAPI) {
        $features = $this->getFeatures();
        foreach ($features as $key => $arr) {
          if ($arr['public']) {
            $this->addPublicAttributeClass($this->getDBAClass());
          }
        }
        
        $missingPermissionMatching = true;
        // if we also have permissions from expanded entries we need to check them as well
        if (count($permsExpandMatching) && $this instanceof AbstractModelAPI) {
          foreach ($missing_permissions as $missing_permission) {
            $expands = $permsExpandMatching[$missing_permission];
            foreach ($expands as $expand) {
              $classType = null;
              if (isset($this->getToManyRelationships()[$expand])) {
                $classType = $this->getToManyRelationships()[$expand]['relationType'];
              }
              elseif (isset($this->getToOneRelationships()[$expand])) {
                $classType = $this->getToOneRelationships()[$expand]['relationType'];
              }
              
              $expandPublicAttributes = [];
              if ($classType != null) {
                $features = $this->getFeaturesOther($classType);
                foreach ($features as $key => $arr) {
                  if ($arr['public']) {
                    $expandPublicAttributes[] = $key;
                  }
                }
              }
              if (count($expandPublicAttributes) == 0) {
                $missingPermissionMatching = false;
                break;
              }
              else {
                $this->addPublicAttributeClass($classType);
              }
            }
          }
        }
        if (!$missingPermissionMatching) {
          $this->publicAttributeFilterClasses = [];
        }
        
        if (count($this->publicAttributeFilterClasses) > 0) {
          // if there are public attributes we don't return false, but the list of classes which needs to be filtered is saved in the attribteFilterClasses list
          return TRUE;
        }
      }
      $this->permissionErrors = array("No '" . join(",", $missing_permissions) . "' permission(s). [required_permissions='" . join(", ", $required_perms) . "', user_permissions='" . join(", ", $user_available_perms) . "']");
      $this->missing_permissions = $missing_permissions;
      return FALSE;
    }
    else {
      $this->permissionErrors = array();
      return TRUE;
    }
  }
  
  protected function addPublicAttributeClass($class): void {
    if (!is_array($this->publicAttributeFilterClasses)) {
      $this->publicAttributeFilterClasses = [];
    }
    if (!in_array($class, $this->publicAttributeFilterClasses)) {
      $this->publicAttributeFilterClasses[] = $class;
    }
  }
  
  /**
   * Common features for all requests, like setting user and checking basic permissions
   * @throws HTException
   * @throws HttpForbidden
   */
  protected function preCommon(Request $request): void {
    $userId = $request->getAttribute(('userId'));
    $this->user = UserUtils::getUser($userId);
    
    # 'Initiate' AccessControl class, by requesting instance with parameter of logged-in user.
    # This will cause the AccessControl class to initiate it's static 'instance' parameter,
    # which is in turn used at later stages (e.g. src/inc/utils/NotificationUtils.class.php) to
    # request an object on which authentication takes place.
    #
    # At some point we might want to remove this strange behaviour always pass the $user object
    # to the AccessControl class when requested. 
    AccessControl::getInstance($this->user);
    
    $routeContext = RouteContext::fromRequest($request);
    $this->routeParser = $routeContext->getRouteParser();
    
    try {
      $required_perms = $this->getRequiredPermissions($request->getMethod());
    }
    catch (HTException $e) {
      # Annotate error message, with suitable candidates
      throw new HttpForbidden($e->getMessage() .
        "(valid methods are for model are: " . join(",", $this->getAvailableMethods()) . ")"
      );
    }
    
    if ($this->validatePermissions($required_perms) === FALSE) {
      throw new HttpForbidden(join('||', $this->permissionErrors));
    }
  }
  
  /* 
   * Return requested parameter, prioritize query parameter over inline payload parameter 
   */
  protected function getParam(Request $request, string $param, int $default): int {
    $queryParams = $request->getQueryParams();
    $bodyParams = $request->getParsedBody();
    
    // Check query parameters and make sure it is an array
    if (array_key_exists($param, $queryParams)) {
      return intval($queryParams[$param]);
    }
    // Check body parameters and make sure it is an array
    elseif (is_array($bodyParams) && array_key_exists($param, $bodyParams)) {
      return intval($bodyParams[$param]);
      // Return default value if parameter not found
    }
    else {
      return $default;
    }
  }
  
  protected function getQueryParameterAsList(Request $request, string $name): array {
    $queryParams = $request->getQueryParams();
    if (array_key_exists($name, $queryParams)) {
      return preg_split("/[,\ ]+/", $queryParams[$name]);
    }
    else {
      return [];
    }
  }
  
  
  /* 
   * Return requested parameter, prioritize query parameter over inline payload parameter 
   */
  protected function getQueryParameterFamilyMember(Request $request, string $family, string $member): string|null {
    $queryParams = $request->getQueryParams();
    // Check query parameters and make sure it is an array
    if (array_key_exists($family, $queryParams) && array_key_exists($member, $queryParams[$family])) {
      return $queryParams[$family][$member];
    }
    
    return null;
  }
  
  
  /* 
   * Return requested parameter, prioritize query parameter over inline payload parameter 
   */
  protected function getQueryParameterFamily(Request $request, string $family): array {
    $retval = [];
    $queryParams = $request->getQueryParams();
    if (array_key_exists($family, $queryParams) and is_array($queryParams[$family])) {
      // TODO: Enhance validation
      return $queryParams[$family];
    }
    
    return $retval;
  }
  
  static function createJsonResponse(array $data = [], array $links = [], array $included = [], array $meta = []): array {
    $response = [
      "jsonapi" => [
        "version" => "1.1",
        "ext" => [
          "https://jsonapi.org/profiles/ethanresnick/cursor-pagination"
        ],
      ],
    ];
    
    if (!empty($links)) {
      $response["links"] = $links;
    }
    
    if (!empty($meta)) {
      $response["meta"] = $meta;
    }
    
    $response["data"] = $data;
    
    if (!empty($included)) {
      $response["included"] = $included;
    }
    
    return $response;
  }
  
  /**
   * Get single Resource
   */
  protected static function getOneResource(object $apiClass, object $object, Request $request, Response $response, int $statusCode = 200): Response {
    $apiClass->preCommon($request);
    
    $validExpandables = $apiClass->getExpandables();
    $expands = $apiClass->makeExpandables($request, $validExpandables);
    
    $objects = [$object];
    
    /* Resolve all expandables */
    $expandResult = [];
    foreach ($expands as $expand) {
      // mapping from $objectId -> result objects in
      $expandResult[$expand] = $apiClass->fetchExpandObjects($objects, $expand);
    }
    
    /* Convert objects to JSON:API */
    $dataResources = [];
    $includedResources = [];
    
    // Convert objects to data resources 
    foreach ($objects as $object) {
      // Create object
      $newObject = $apiClass->obj2Resource($object, $expandResult, $request->getQueryParams()['fields'] ?? null, $request->getQueryParams()['aggregate'] ?? null);
      $includedResources = $apiClass->processExpands($apiClass, $expands, $object, $expandResult, $includedResources, $request->getQueryParams()['fields'] ?? null, $request->getQueryParams()['aggregate'] ?? null);
      
      // Add to result output
      $dataResources[] = $newObject;
    }
    
    $selfParams = $request->getQueryParams();
    $linksQuery = urldecode(http_build_query($selfParams));
    
    $linksSelf = $request->getUri()->getPath() . ((!empty($linksQuery)) ? '?' . $linksQuery : '');
    $links = ["self" => $linksSelf];
    
    $metaData = [];
    if ($apiClass->permissionErrors !== null) {
      $metaData["Include errors"] = $apiClass->permissionErrors;
    }
    // Generate JSON:API GET output
    $ret = self::createJsonResponse($dataResources[0], $links, $includedResources, $metaData);
    
    $body = $response->getBody();
    $body->write($apiClass->ret2json($ret));
    
    return $response->withStatus($statusCode)
      ->withHeader("Content-Type", "application/vnd.api+json")
      ->withHeader("Location", $dataResources[0]["links"]["self"]);
    //for location we use links value from $dataresources because if we use $linksSelf, the wrong location gets returned in
    //case of a POST request
  }
  
  //Meta response for helper functions that do not respond with resource records
  
  /**
   * @throws JsonException
   */
  protected static function getMetaResponse(array $meta, Request $request, Response $response, int $statusCode = 200): MessageInterface|Response {
    $ret = self::createJsonResponse(meta: $meta);
    $body = $response->getBody();
    $body->write(self::ret2json($ret));
    
    return $response->withStatus($statusCode)->withHeader("Content-Type", "application/vnd.api+json");
  }
  
  /**
   * Override-able activated methods
   */
  static public function getAvailableMethods(): array {
    return ["GET", "POST", "PATCH", "DELETE"];
  }
}
