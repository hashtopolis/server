<?php
require_once(dirname(__FILE__) . "/AbstractBaseAPI.class.php");

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpForbiddenException;
use Middlewares\Utils\HttpErrorException;

use DBA\AbstractModelFactory;
use DBA\JoinFilter;
use DBA\Factory;
use DBA\ContainFilter;
use DBA\LimitFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractModelAPI extends AbstractBaseAPI
{
  abstract static public function getDBAClass();
  abstract protected function createObject(array $data): int;
  abstract protected function deleteObject(object $object): void;

  public static function getToOneRelationships(): array
  {
    return [];
  }
  public static function getToManyRelationships(): array
  {
    return [];
  }


  /** 
   * Available 'expand' parameters on $object
   */
  public static function getExpandables(): array
  {
    $expandables = array_merge(array_keys(static::getToOneRelationships()), array_keys(static::getToManyRelationships()));
    return $expandables;
  }

  // /** 
  //  * Fetch objects for  $expand on $objects
  //  */
  protected static function fetchExpandObjects(array $objects, string $expand): mixed
  {
    //disabled the check because with intermediate objects its possible to fetch a different model
    /* Ensure we receive the proper type */
    // $baseModel = static::getDBAClass();
    // array_walk($objects, function ($obj) use ($baseModel) {
    //   assert($obj instanceof $baseModel);
    // });

    $toOneRelationships = static::getToOneRelationships();
    if (array_key_exists($expand, $toOneRelationships)) {
      $relationFactory = self::getModelFactory($toOneRelationships[$expand]['relationType']);
      return self::getForeignKeyRelation(
        $objects,
        $toOneRelationships[$expand]['key'],
        $relationFactory,
        $toOneRelationships[$expand]['relationKey'],
      );
    };

    $toManyRelationships = static::getToManyRelationships();
    if (array_key_exists($expand, $toManyRelationships)) {
      $relationFactory = self::getModelFactory($toManyRelationships[$expand]['relationType']);

      /* Associative entity */
      if (array_key_exists('junctionTableType', $toManyRelationships[$expand])) {
        $junctionTableFactory = self::getModelFactory($toManyRelationships[$expand]['junctionTableType']);
        return self::getManyToOneRelationViaIntermediate(
          $objects,
          $toManyRelationships[$expand]['key'],
          $junctionTableFactory,
          $toManyRelationships[$expand]['junctionTableFilterField'],
          $relationFactory,
          $toManyRelationships[$expand]['relationKey'],
        );
      };

      return self::getManyToOneRelation(
        $objects,
        $toManyRelationships[$expand]['key'],
        $relationFactory,
        $toManyRelationships[$expand]['relationKey'],
      );
    };

    throw new BadFunctionCallException("Internal error: Expansion '$expand' not implemented!");
  }


  final protected static function getFactory(): object
  {
    return self::getModelFactory(static::getDBAclass());
  }

  /** 
   * Get features based on Formfields and DBA model features
   */
  final protected function getFeatures(): array
  {
    return array_merge(
      parent::getFeatures(),
      call_user_func($this->getDBAclass() . '::getFeatures'),
    );
  }

  /** 
   * Get features based on DBA model features
   * 
   * @param string $dbaClass is the dba class to get the features from
   */
  //TODO doesnt retrieve features based on formfields, could be done by adding api class in relationship objects
  final protected function getFeaturesOther(string $dbaClass): array
  {
    return call_user_func($dbaClass . '::getFeatures');
  }

  /**
   * Find primary key for  another DBA object
   * A little bit hacky because the getPrimaryKey function in dbaClass is not static
   * 
   * @param string $dbaClass is the dba class to get the primarykey from
   */
  protected function getPrimaryKeyOther(string $dbaClass): string
  {
    $features = $this->getFeaturesOther($dbaClass);
    # Work-around required since getPrimaryKey is not static in dba/models/*.php
    foreach ($features as $key => $value) {
      if ($value['pk'] == True) {
        return $key;
      }
    }
  }

  /**
   * Retrieve ForeignKey Relation
   * 
   * @param array $objects Objects Fetch relation for selected Objects 
   * @param string $objectField Field to use as base for $objects
   * @param object $factory Factory used to retrieve objects
   * @param string $filterField Filter field of $field to filter against $objects field
   * 
   * @return array 
   */
  final protected static function getForeignKeyRelation(
    array $objects,
    string $objectField,
    object $factory,
    string $filterField
  ): array {
    assert($factory instanceof AbstractModelFactory);
    $retval = array();

    /* Fetch required objects */
    $objectIds = [];
    foreach ($objects as $object) {
      $kv = $object->getKeyValueDict();
      $objectIds[] = $kv[$objectField];
    }
    $qF = new ContainFilter($filterField, $objectIds, $factory);
    $hO = $factory->filter([Factory::FILTER => $qF]);

    /* Objects are uniquely identified by fields, create mapping to speed-up further processing */
    $f2o = [];
    foreach ($hO as $relationObject) {
      $f2o[$relationObject->getKeyValueDict()[$filterField]] = $relationObject;
    };

    /* Map objects */
    foreach ($objects as $object) {
      $fieldId = $object->getKeyValueDict()[$objectField];
      if (array_key_exists($fieldId, $f2o) == true) {
        $retval[$object->getId()] = $f2o[$fieldId];
      }
    }

    return $retval;
  }

  /**
   * Retrieve ManyToOneRelation (reverse ForeignKey)
   * 
   * @param array $objects Objects Fetch relation for selected Objects 
   * @param string $objectField Field to use as base for $objects
   * @param object $factory Factory used to retrieve objects
   * @param string $filterField Filter field of $field to filter against $objects field
   * 
   * @return array 
   */
  final protected static function getManyToOneRelation(
    array $objects,
    string $objectField,
    object $factory,
    string $filterField
  ): array {
    assert($factory instanceof AbstractModelFactory);
    $retval = array();

    /* Fetch required objects */
    $objectIds = [];
    foreach ($objects as $object) {
      $kv = $object->getKeyValueDict();
      $objectIds[] = $kv[$objectField];
    }
    $qF = new ContainFilter($filterField, $objectIds, $factory);
    $hO = $factory->filter([Factory::FILTER => $qF]);

    /* Map (multiple) objects to base objects */
    foreach ($hO as $relationObject) {
      $kv = $relationObject->getKeyValueDict();
      $retval[$kv[$filterField]][] = $relationObject;
    }

    return $retval;
  }


  /**
   * Retrieve ManyToOne relalation for $objects ('parents') of type $targetFactory via 'intermidate'
   * of $intermediateFactory joining on $joinField (between 'intermediate' and 'target'). Filtered by 
   * $filterField at $intermediateFactory.
   * 
   * @param array $objects Objects Fetch relation for selected Objects 
   * @param string $objectField Field to use as base for $objects
   * @param object $intermediateFactory Factory used as intermediate between parentObject and targetObject
   * @param string $filterField Filter field of intermadiateObject to filter against $objects field
   * @param object $targetFactory Object properties of objects returned
   * @param string $joinField Field to connect 'intermediate' to 'target'

   * @return array 
   */
  final protected static function getManyToOneRelationViaIntermediate(
    array $objects,
    string $objectField,
    object $intermediateFactory,
    string $filterField,
    object $targetFactory,
    string $joinField,
  ): array {
    assert($intermediateFactory instanceof AbstractModelFactory);
    assert($targetFactory instanceof AbstractModelFactory);
    $retval = array();


    /* Retrieve Parent -> Intermediate -> Target objects */
    $objectIds = [];
    foreach ($objects as $object) {
      $kv = $object->getKeyValueDict();
      $objectIds[] = $kv[$objectField];
    }
    $qF = new ContainFilter($filterField, $objectIds, $intermediateFactory);
    $jF = new JoinFilter($intermediateFactory, $joinField, $joinField);
    $hO = $targetFactory->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);

    /* Build mapping Parent -> Intermediate */
    $i2p = [];
    foreach ($hO[$intermediateFactory->getModelName()] as $intermidiateObject) {
      $kv = $intermidiateObject->getKeyValueDict();
      $i2p[$kv[$joinField]] = $kv[$filterField];
    }

    /* Associate Target -> Parent (via Intermediate) */
    foreach ($hO[$targetFactory->getModelName()] as $targetObject) {
      $parent = $i2p[$targetObject->getKeyValueDict()[$joinField]];
      $retval[$parent][] = $targetObject;
    }

    return $retval;
  }

  /** 
   * Retrieve  permissions based on class and method requested
   */
  public function getRequiredPermissions(string $method): array
  {
    $model = $this->getDBAclass();
    # Get required permission based on API method type
    switch (strtoupper($method)) {
      case "GET":
        $required_perm = $model::PERM_READ;
        break;
      case "POST":
        $required_perm = $model::PERM_CREATE;
        break;
      case "PATCH":
        $required_perm = $model::PERM_UPDATE;
        break;
      case "DELETE":
        $required_perm = $model::PERM_DELETE;
        break;
      default:
        throw new HTException("Method '" . $method . "' is not allowed ");
    }
    return array($required_perm);
  }

  /**
   * Validate the Permission of a foreignkey and check if foreign key may be altered
   * 
   * @param ServerRequestInterface $request Current request that is being handled
   * @param string $relationKey Field to use as base for $objects
   * @param array $features The features of the DBA object of the child
   * 
   * @throws HttpForbiddenException when it is not allowed to alter the foreignkey of the child object
   * 
   * @return void 
   */
  final protected function checkForeignkeyPermission(ServerRequestInterface $request, string $relationKey, array $features)
  {
    if ($features[$relationKey]['read_only'] == True) {
      throw new HttpForbiddenException($request, "Key '$relationKey' is immutable");
    }
    if ($features[$relationKey]['protected'] == True) {
      throw new HttpForbiddenException($request, "Key '$relationKey' is protected");
    }
    if ($features[$relationKey]['private'] == True) {
      throw new HttpForbiddenException($request, "Key '$relationKey' is private");
    }
  }

  /**
   * API entry point for deletion of single object
   */
  public function deleteOne(Request $request, Response $response, array $args): Response
  // TODO how to handle cascading deletes?
  // ex. Hash foreignkey to hashlist can't be null, but hashlist delete doesnt cascade to Hash
  // Which effectively means that we cant delete a hashlist because of foreingkey constraints 
  // Solution 1: make cascading rules in Database
  // Solution 2: implement delete logic in every api model 
  {
    $this->preCommon($request);
    $object = $this->doFetch($request, $args['id']);

    /* Actually delete object */
    $this->deleteObject($object);

    return $response->withStatus(204)
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

    return $object;
  }

  /**
   * Additional filtering required for limiting access to objects 
   */
  protected function getFilterACL(): array
  {
    return [];
  }

  /**
   * Helper function to determine if $resourceRecord is a valid resource record
   * returns true if it is a valid resource record and false if it is an invallid resource record
   */
  final protected function validateResourceRecord(mixed $resourceRecord): bool
  {
    return (isset($resourceRecord['type']) && is_numeric($resourceRecord['id']));
  }

  final protected function ResourceRecordArrayToUpdateArray($data, $parentId)
  {
    $updates = [];
    foreach ($data as $item) {
      if (!$this->validateResourceRecord($item)) {
        $encoded_item = json_encode($item);
        throw new HttpErrorException('Invallid resource record given in list! invalid resource record: ' . $encoded_item);
      }
      $updates[] = new MassUpdateSet($item["id"], $parentId);
    }
    return $updates;
  }

  /**
   * API entry point for requesting multiple objects
   */
  public static function getManyResources(object $apiClass, Request $request, Response $response, array $relationFs = []): Response
  {
    $apiClass->preCommon($request);

    $aliasedfeatures = $apiClass->getAliasedFeatures();
    $factory = $apiClass->getFactory();

    // TODO: Maximum and default should be configurable per server instance
    $defaultPageSize = 10000;
    $maxPageSize = 50000;

    $pageAfter = $apiClass->getQueryParameterFamilyMember($request, 'page', 'after') ?? 0;
    $pageSize = $apiClass->getQueryParameterFamilyMember($request, 'page', 'size') ?? $defaultPageSize;
    if ($pageSize < 0) {
      throw new HttpErrorException("Invallid parameter, page[size] must be a positive integer", 400);
    } elseif ($pageSize > $maxPageSize) {
      throw new HttpErrorException(sprintf("You requested a size of %d, but %d is the maximum.", $pageSize, $maxPageSize), 400);
    }

    $validExpandables = $apiClass::getExpandables();
    $expands = $apiClass->makeExpandables($request, $validExpandables);

    /* Object filter definition */
    $aFs = [];

    /* Generate filters */
    $qFs_Filter = $apiClass->makeFilter($request, $aliasedfeatures);
    $qFs_ACL = $apiClass->getFilterACL();
    $qFs = array_merge($qFs_ACL, $qFs_Filter);
    if (count($qFs) > 0) {
      $aFs[Factory::FILTER] = $qFs;
    }

    /**
     * Create pagination
     * 
     * TODO: Deny pagination with un-stable sorting
     */
    $defaultSort = $apiClass->getQueryParameterFamilyMember($request, 'page', 'after') == null &&
      $apiClass->getQueryParameterFamilyMember($request, 'page', 'before') != null ?  'DESC' : 'ASC';
    $orderTemplates = $apiClass->makeOrderFilterTemplates($request, $aliasedfeatures, $defaultSort);

    // Build actual order filters
    foreach ($orderTemplates as $orderTemplate) {
      $aFs[Factory::ORDER][] = new OrderFilter($orderTemplate['by'], $orderTemplate['type']);
    }

    /* Include relation filters */
    $finalFs = array_merge($aFs, $relationFs);

    //according to JSON API spec, first and last have to be calculated if inexpensive to compute 
    //(https://jsonapi.org/profiles/ethanresnick/cursor-pagination/#auto-id-links))
    //if this query is too expensive for big tables, it should be removed
    $max = $factory->minMaxFilter($finalFs, $apiClass->getPrimaryKey(), "MAX");

    //pagination filters need to be added after max has been calculated
    $finalFs[Factory::LIMIT] = new LimitFilter($pageSize);

    $finalFs[Factory::FILTER][] = new QueryFilter($apiClass->getPrimaryKey(), $pageAfter, '>', $factory);
    $pageBefore = $apiClass->getQueryParameterFamilyMember($request, 'page', 'before');
    if (isset($pageBefore)) {
      $finalFs[Factory::FILTER][] = new QueryFilter($apiClass->getPrimaryKey(), $pageBefore, '<', $factory);
    }

    /* Request objects */
    $filterObjects = $factory->filter($finalFs);

    /* JOIN statements will return related modules as well, discard for now */
    if (array_key_exists(Factory::JOIN, $finalFs)) {
      $objects = $filterObjects[$factory->getModelname()];
    } else {
      $objects = $filterObjects;
    }

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
      $newObject = $apiClass->obj2Resource($object, $expandResult);

      // For compound document, included resources
      foreach ($expands as $expand) {
        if (array_key_exists($object->getId(), $expandResult[$expand])) {
          $expandResultObject = $expandResult[$expand][$object->getId()];
          if (is_array($expandResultObject)) {
            foreach ($expandResultObject as $expandObject) {
              $includedResources[] = $apiClass->obj2Resource($expandObject);
            }
          } else {
            if ($expandResultObject === null) {
              // to-only relation which is nullable
              continue;
            }
            $includedResources[] = $apiClass->obj2Resource($expandResultObject);
          }
        }
      }

      // Add to result output
      $dataResources[] = $newObject;
    }

    //build last link
    $lastParams = $request->getQueryParams();
    unset($lastParams['page']['after']);
    $lastParams['page']['size'] = $pageSize;
    $lastParams['page']['before'] = $max + 1;
    $linksLast = $request->getUri()->getPath() . '?' .  urldecode(http_build_query($lastParams));

    // Build self link
    $selfParams = $request->getQueryParams();
    $selfParams['page']['size'] = $pageSize;
    $linksSelf = $request->getUri()->getPath() . '?' .  urldecode(http_build_query($selfParams));

    $linksNext = null;
    $linksPrev = null;

    // Build next link
    if (!empty($objects)) {
      $minId = $maxId = $objects[0]->getId() ?? null;
      foreach ($objects as $obj) {
        $cur_id = $obj->getId();
        if ($cur_id < $minId) {
          $minId = $cur_id;
        }
        if ($cur_id > $maxId) {
          $maxId = $cur_id;
        }
      }
      $nextId = $defaultSort == "ASC" ? $maxId : $minId;

      if ($nextId < $max) { //only set next page when its not the last page
        $nextParams = $selfParams;
        $nextParams['page']['after'] = $nextId;
        unset($nextParams['page']['before']);
        $linksNext = $request->getUri()->getPath() . '?' .  urldecode(http_build_query($nextParams));
      }
      // Build prev link 
      $prevId = $defaultSort == "DESC" ? $maxId : $minId;
      if ($prevId != 1) { //only set previous page when its not the first page
        $prevParams = $selfParams;
        //This scenario might return a link to an empty array if the elements with the lowest id are deleted, but this is allowed according
        //to the json API spec https://jsonapi.org/profiles/ethanresnick/cursor-pagination/#auto-id-links
        //We could also get the lowest id the same way we got the max, but this is probably unnecessary expensive.
        //But pull request: https://github.com/hashtopolis/server/pull/1069 would create a cheaper way of doing this in a single query
        $prevParams['page']['before'] = $prevId;
        unset($prevParams['page']['after']);
        $linksPrev = $request->getUri()->getPath() . '?' .  urldecode(http_build_query($prevParams));
      }
    }

    //build first link
    $firstParams = $request->getQueryParams();
    unset($firstParams['page']['before']);
    $firstParams['page']['size'] = $pageSize;
    $firstParams['page']['after'] = 0;
    $linksFirst = $request->getUri()->getPath() . '?' .  urldecode(http_build_query($firstParams));
    $links = [
        "self" => $linksSelf,
        "first" => $linksFirst,
        "last" => $linksLast,
        "next" => $linksNext,
        "prev" => $linksPrev,
      ];

    // Generate JSON:API GET output
    $ret = self::createJsonResponse($dataResources, $links, $includedResources);

    $body = $response->getBody();
    $body->write($apiClass->ret2json($ret));

    return $response->withStatus(200)
      ->withHeader("Content-Type", 'application/vnd.api+json; ext="https://jsonapi.org/profiles/ethanresnick/cursor-pagination"');
  }

  /**
   * API entry point for requesting multiple objects
   */
  public function get(Request $request, Response $response, array $args): Response
  {
    return self::getManyResources($this, $request, $response);
  }

  /**
   * Get input field names valid for creation of object
   */
  final public function getCreateValidFeatures(): array
  {
    return $this->getAliasedFeatures();
  }

  /**
   * API entry point for requests of single object
   */
  public function getOne(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);
    $object = $this->doFetch($request, $args['id']);

    $classMapper = $this->container->get('classMapper');

    return self::getOneResource($this, $object, $request, $response);
  }


  /**
   * API entry point for modification of single object
   */
  public function patchOne(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);
    $object = $this->doFetch($request, $args['id']);

    $data = $request->getParsedBody()['data'];
    if (!$this->validateResourceRecord($data)) {
      throw new HttpErrorException('No valid resource identifier object was given as data!', 403);
    }
    $aliasedfeatures = $this->getAliasedFeatures();
    $attributes = $data['attributes'];

    // Validate incoming data
    foreach (array_keys($attributes) as $key) {
      // Ensure key is a regular string
      if (is_string($key) == False) {
        throw new HttpErrorException("Key '$key' invalid", 403);
      }
      // Ensure key exists in target array
      if (array_key_exists($key, $aliasedfeatures) == False) {
        throw new HttpErrorException("Key '$key' does not exists!", 403);
      }

      // Ensure key can be updated 
      if ($aliasedfeatures[$key]['read_only'] == True) {
        throw new HttpForbiddenException($request, "Key '$key' is immutable");
      }
      if ($aliasedfeatures[$key]['protected'] == True) {
        throw new HttpForbiddenException($request, "Key '$key' is protected");
      }
      if ($aliasedfeatures[$key]['private'] == True) {
        throw new HttpForbiddenException($request, "Key '$key' is private");
      }
    }
    // Validate input data if it matches the correct type or subtype
    $this->validateData($attributes, $aliasedfeatures);

    // This does the real things, patch the values that were sent in the data.
    $mappedData = $this->unaliasData($attributes, $aliasedfeatures);
    $this->updateObject($object, $mappedData); //TODO updateObject not implemented in every route?

    // Return updated object
    $newObject = $this->getFactory()->get($object->getId());
    return self::getOneResource($this, $newObject, $request, $response, 201);
  }


  /**
   * API entry point creation of new object
   */
  public function post(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);

    $data = $request->getParsedBody()["data"];
    if ($data == null) {
      throw new HttpErrorException("POST request requires data to be present", 403);
    }
    //POST request RR only needs type, no ID
    if (!isset($data['type'])) {
      throw new HttpErrorException('No valid resource identifier object with type was given as data!', 403);
    }
    $attributes = $data["attributes"];

    $allFeatures = $this->getAliasedFeatures();

    // Validate incoming parameters
    $this->validateParameters($attributes, $allFeatures);

    // Validate incoming data by value
    $this->validateData($attributes, $allFeatures);

    // Remove key aliases and sanitize to 'db values and request creation
    $mappedData = $this->unaliasData($attributes, $allFeatures);
    $pk = $this->createObject($mappedData);

    // TODO: Return 409 (conflict) if resource already exists or cannot be created

    // Request object again, since post-modified entries are not reflected into object.
    $object = $this->getFactory()->get($pk);
    return self::getOneResource($this, $object, $request, $response, 201);
  }


  /**
   * API endpoint to get a to one related resource record 
   */
  public function getToOneRelatedResource(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);

    $relation = $args['relation'];

    $relationMapper = $this->getToOneRelationships()[$relation];
    $intermediate = $relationMapper["intermediateType"];
    //if there is an intermediate table join on that
    if ($intermediate !== null) {
      $intermediateFactory = self::getModelFactory($intermediate);
      $aFs[Factory::JOIN][] = new JoinFilter(
        $intermediateFactory,
        $relationMapper['joinField'],
        $relationMapper['joinFieldRelation'],
      );

      $factory = $this->getFactory();
      $object = $factory->filter($aFs)[$intermediateFactory->getModelName()][0];
    } else {
      // Base object
      $object = $this->doFetch($request, $args['id']);
    }

    // Relation object
    $relationObjects = $this->fetchExpandObjects([$object], $relation);
    $relationObject = $relationObjects[$args['id']];

    $relationClass = $relationMapper['relationType'];
    $relationApiClass = new ($this->container->get('classMapper')->get($relationClass))($this->container);

    return self::getOneResource($relationApiClass, $relationObject, $request, $response);
  }


  /**
   * API endpoint to get a to one relationship link
   */
  public function getToOneRelationshipLink(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);

    $relation = $this->getToOneRelationships()[$args['relation']];

    /* Prepare filter for to-one relations */

    // Example for Task:
    // 'Hashlist' => [
    //     'intermediateType' => TaskWrapper::class,
    //     'joinField' => Task::TASK_WRAPPER_ID,
    //     'joinFieldRelation' => TaskWrapper::TASK_WRAPPER_ID,
    // ],
    if (array_key_exists('intermediateType', $relation)) {
      $aFs = [];
      $intermediateFactory = self::getModelFactory($relation['intermediateType']);

      $aFs[Factory::FILTER][] = new QueryFilter(
        $relation['joinField'],
        $args['id'],
        '=',
        $intermediateFactory
      );

      $aFs[Factory::JOIN][] = new JoinFilter(
        $intermediateFactory,
        $relation['joinField'],
        $relation['joinFieldRelation'],
      );

      $factory = $this->getFactory();
      //retrieve the only element of the intermediate table, which contains the data for the relatedResource
      $object = $factory->filter($aFs)[$intermediateFactory->getModelName()][0];
    } else {
      $object = $this->doFetch($request, $args['id']);
    };

    $id = $object->getKeyValueDict()[$relation['key']];

    if (is_null($id)) {
      $dataResource = null;
    } else {
      $dataResource = [
        'type' => $this->getObjectTypeName($relation['relationType']),
        'id' => $id,
      ];
    }

    $selfParams = $request->getQueryParams();
    $linksQuery = urldecode(http_build_query($selfParams));
    $linksSelf = $request->getUri()->getPath() . ((!empty($linksQuery)) ? '?' .  $linksQuery : '');

    $apiClass = $this->container->get('classMapper')->get(get_class($object));
    $linksRelated = $this->routeParser->urlFor($apiClass . ':getToOneRelatedResource', $args);

      $links = [
        "self" => $linksSelf,
        "related" => $linksRelated,
      ];

    // Generate JSON:API GET output
    $ret = self::createJsonResponse($dataResource, $links);

    $body = $response->getBody();
    $body->write($this->ret2json($ret));

    return $response->withStatus(200)
      ->withHeader("Content-Type", 'application/vnd.api+json');
  }

  /*
  * API endpoint to patch a to one relationship link
  */
  //This works as intended but it can give weird behaviour. ex. it allows you to put an MD5 hash to a SHA1 hashlist 
  //by patching the foreingkey. Simple fix could be to make foreignkey immutable for cases like this.
  public function patchToOneRelationshipLink(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);
    $jsonBody = $request->getParsedBody();

    if ($jsonBody === null || !array_key_exists('data', $jsonBody)) {
      throw new HttpErrorException('No data was sent! Send the json data in the following format: {"data": {"type": "foo", "id": 1}}');
    }
    $data = $jsonBody['data'];

    $relationKey = $this->getToOneRelationships()[$args['relation']]['relationKey'];
    if ($relationKey == null) {
      throw new HttpErrorException("Relation does not exist!");
    }

    $feature = $this->getFeatures()[$relationKey];
    if ($feature['read_only'] == True) {
      throw new HttpForbiddenException($request, "Key '$relationKey' is immutable");
    }
    if ($feature['protected'] == True) {
      throw new HttpForbiddenException($request, "Key '$relationKey' is protected");
    }
    if ($feature['private'] == True) {
      throw new HttpForbiddenException($request, "Key '$relationKey' is private");
    }

    $factory = $this->getFactory();
    $object = $this->doFetch($request, intval($args['id']));
    if ($data == null) {
      $factory->set($object, $relationKey, null);
    } elseif (!$this->validateResourceRecord($data)) {
      throw new HttpErrorException('No valid resource identifier object was given as data!');
    } else {
      $factory->set($object, $relationKey, $data["id"]);
    }
    //TODO catch database exceptions like failed foreignkey constraint and return correct error response

    return $response->withStatus(201)
      ->withHeader("Content-Type", "application/vnd.api+json");
  }


  /**
   * API endpoint for retrieving to many relationship resource records
   */
  public function getToManyRelatedResource(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);

    // Base object -> Relation objects
    // $object = $this->doFetch($request, $args['id']);

    $toManyRelation = $this->getToManyRelationships()[$args['relation']];
    $relationClass = $toManyRelation['relationType'];
    $relationApiClass = new ($this->container->get('classMapper')->get($relationClass))($this->container);

    $aFs = [];
    $filterField = $toManyRelation['relationKey'];
    $filterFactory = null;

    if (array_key_exists('junctionTableType', $toManyRelation)) {
      $filterField = $toManyRelation['junctionTableFilterField'];
      $filterFactory = self::getModelFactory($toManyRelation['junctionTableType']);

      $aFs[Factory::JOIN][] = new JoinFilter(
        self::getModelFactory($toManyRelation['junctionTableType']),
        $toManyRelation['junctionTableJoinField'],
        $toManyRelation['key'],
      );
    }

    $aFs[Factory::FILTER][] = new QueryFilter(
      $filterField,
      $args['id'],
      '=',
      $filterFactory
    );

    return self::getManyResources($relationApiClass, $request, $response, $aFs);
  }


  /**
   * API get request to retrieve the to many relationship links 
   */
  public function getToManyRelationshipLink(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);

    // Base object -> Relationship objects
    $object = $this->doFetch($request, $args['id']);
    $expandObjects = $this->fetchExpandObjects([$object], $args['relation']);

    $dataResources = [];
    if (array_key_exists($object->getId(), $expandObjects)) {
      foreach ($expandObjects[$object->getId()] as $relationshipObject) {
        $dataResources[] = [
          'type' => $this->getObjectTypeName($relationshipObject),
          'id' => $relationshipObject->getId(),
        ];
      }
    }

    $selfParams = $request->getQueryParams();
    $linksQuery = urldecode(http_build_query($selfParams));
    $linksSelf = $request->getUri()->getPath() . ((!empty($linksQuery)) ? '?' .  $linksQuery : '');

    $apiClass = $this->container->get('classMapper')->get(get_class($object));
    $linksRelated = $this->routeParser->urlFor($apiClass . ':getToManyRelatedResource', $args);


    // TODO implement pagination support
    $linksNext = null;

    // Generate JSON:API GET output
    $links = [
      "self" => $linksSelf,
      "related" => $linksRelated,
      "next" => $linksNext,
    ];
    $ret = self::createJsonResponse($dataResources, $links);

    $body = $response->getBody();
    $body->write($this->ret2json($ret));

    return $response->withStatus(200)
      ->withHeader("Content-Type", 'application/vnd.api+json; ext="https://jsonapi.org/profiles/ethanresnick/cursor-pagination"');
  }

  /**
   * PATCH request to patch the to many relationship link TODO: handle intermediate tables
   */
  public function patchToManyRelationshipLink(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);
    $jsonBody = $request->getParsedBody();

    if ($jsonBody === null || !array_key_exists('data', $jsonBody) || !is_array($jsonBody['data'])) {
      throw new HttpErrorException('No data was sent! Send the json data in the following format: {"data":[{"type": "foo", "id": 1}}]');
    }
    $data = $jsonBody['data'];

    $relation = $this->getToManyRelationships()[$args['relation']];
    $primaryKey = $this->getPrimaryKeyOther($relation['relationType']);
    $relationKey = $relation['relationKey'];
    if ($relationKey == null) {
      throw new HttpErrorException("Relation does not exist!");
    }

    $relationType = $relation['relationType'];
    $features = $this->getFeaturesOther($relationType);
    if ($features[$relationKey]['read_only'] == True) {
      throw new HttpForbiddenException($request, "Key '$relationKey' is immutable");
    }
    if ($features[$relationKey]['protected'] == True) {
      throw new HttpForbiddenException($request, "Key '$relationKey' is protected");
    }
    if ($features[$relationKey]['private'] == True) {
      throw new HttpForbiddenException($request, "Key '$relationKey' is private");
    }

    $factory = self::getModelFactory($relationType);

    $qF = new QueryFilter($relationKey, $args['id'], "=");
    $models = $factory->filter([Factory::FILTER => $qF]);
    //TODO Would be nicer if filter/factory could return a dict based on primarykeys directly
    $modelsDict = array();
    foreach ($models as $item) {
      $modelsDict[$item->getPrimaryKeyValue()] = $item;
    }

    $updates = [];
    foreach ($data as $item) {
      if (!$this->validateResourceRecord($item)) {
        $encoded_item = json_encode($item);
        throw new HttpErrorException('Invallid resource record given in list! invalid resource record: ' . $encoded_item);
      }
      $updates[] = new MassUpdateSet($item["id"], $args["id"]);
      unset($modelsDict[$item["id"]]);
    }

    $leftover_primarykeys = array_keys($modelsDict);
    if ($features[$relationKey]["null"] == False && count($leftover_primarykeys) > 0) {
      throw new HttpErrorException("Not all current relationship objects have been included,
       but the foreignkey can't be set to null. Either add all objects or delete the not needed objects");
    }
    foreach ($leftover_primarykeys as $key) {
      //set all foreignkeys of current relationships to null that have not been included
      $updates[] = new MassUpdateSet($key, null);
    }
    $factory->getDB()->beginTransaction(); //start transaction to be able roll back
    $factory->massSingleUpdate($primaryKey, $relationKey, $updates);
    if (!$factory->getDB()->commit()) {
      throw new HttpErrorException("Was not able to update to many relationship");
    }

    return $response->withStatus(204)
      ->withHeader("Content-Type", "application/vnd.api+json");
  }

  /**
   * POST request for the to many relationship link TODO
   */
  public function postToManyRelationshipLink(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);

    $jsonBody = $request->getParsedBody();
    if ($jsonBody === null || !array_key_exists('data', $jsonBody) || !is_array($jsonBody['data'])) {
      throw new HttpErrorException('No data was sent! Send the json data in the following format: {"data":[{"type": "foo", "id": 1}}]');
    }
    $data = $jsonBody['data'];

    $relation = $this->getToManyRelationships()[$args['relation']];
    $relationKey = $relation['relationKey'];
    if ($relationKey == null) {
      throw new HttpErrorException("Relation does not exist!");
    }

    $relationType = $relation['relationType'];
    $primaryKey = $this->getPrimaryKeyOther($relationType);
    $features = $this->getFeaturesOther($relationType);

    $this->checkForeignkeyPermission($request, $relationKey, $features);

    $factory = self::getModelFactory($relationType);
    $updates = self::ResourceRecordArrayToUpdateArray($data, $args["id"]);
    $factory->massSingleUpdate($primaryKey, $relationKey, $updates);

    return $response->withStatus(201)
      ->withHeader("Content-Type", "application/vnd.api+json");
  }

  /**
   * DELETE request for the to many relationship link
   * currently there is no object that can be altered this way because of constraints
   */
  public function deleteToManyRelationshipLink(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);
    $jsonBody = $request->getParsedBody();

    if ($jsonBody === null || !array_key_exists('data', $jsonBody) && is_array($jsonBody['data'])) {
      throw new HttpErrorException('No data was sent! Send the json data in the following format: {"data":[{"type": "foo", "id": 1}}]');
    }

    $relation = $this->getToManyRelationships()[$args['relation']];
    $primaryKey = $relation['key'];
    $relationKey = $relation['relationKey'];
    if ($relationKey == null) {
      throw new HttpErrorException("Relation does not exist!");
    }

    $relationType = $relation['relationType'];
    $feature = $this->getFeaturesOther($relationType);
    if ($feature[$relationKey]['read_only'] == True) {
      throw new HttpForbiddenException($request, "Key '$relationKey' is immutable");
    }
    if ($feature[$relationKey]['protected'] == True) {
      throw new HttpForbiddenException($request, "Key '$relationKey' is protected");
    }
    if ($feature[$relationKey]['private'] == True) {
      throw new HttpForbiddenException($request, "Key '$relationKey' is private");
    }
    if ($feature[$relationKey]['null'] == False) {
      // In this scenario another solution could be to delete object TODO?
      throw new HttpForbiddenException($request, "Key '$relationKey' cant be set to null");
    }

    $data = $jsonBody['data'];

    foreach ($data as $item) {
      if (!$this->validateResourceRecord($item)) {
        $encoded_item = json_encode($item);
        throw new HttpErrorException('Invalid resource record given in list! invalid resource record: ' . $encoded_item);
      }
      $updates[] = new MassUpdateSet($item["id"], null);
    }
    $factory = self::getModelFactory($relationType);
    $factory->getDB()->beginTransaction(); //start transaction to be able roll back
    $factory->massSingleUpdate($primaryKey, $relationKey, $updates);
    if (!$factory->getDB()->commit()) {
      throw new HttpErrorException("Some resources failed updating");
    }

    return $response->withStatus(201)
      ->withHeader("Content-Type", "application/vnd.api+json");
  }


  /**
   * Update object with provided values
   */
  protected function updateObject(object $object, array $data, array $processed = []): void
  {
    // Apply changes 
    foreach ($data as $key => $value) {
      if (in_array($key, $processed)) {
        continue;
      }

      $this->getFactory()->set($object, $key, $value);
    }
  }

  /**
   * Get input field names valid for patching of object
   */
  final public function getPatchValidFeatures(): array
  {
    $aliasedfeatures = $this->getAliasedFeatures();
    $validFeatures = [];

    // Generate listing of validFeatures
    foreach ($aliasedfeatures as $name => $feature) {
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
   * Override-able registering of options
   */
  static public function register($app): void
  {
    $me = get_called_class();
    $foo = $me::getDBAClass();
    $baseUri = $me::getBaseUri();
    $baseUriOne = $baseUri . '/{id:[0-9]+}';

    $baseUriRelationships = $baseUri . '/{id:[0-9]+}/relationships';

    $classMapper = $app->getContainer()->get('classMapper');
    $classMapper->add($me::getDBAclass(), $me);

    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->options($baseUriOne, function (Request $request, Response $response): Response {
      return $response;
    });

    $available_methods = $me::getAvailableMethods();

    if (in_array("GET", $available_methods)) {
      $app->get($baseUri, $me . ':get')->setname($me . ':get');
    }

    foreach ($me::getToOneRelationships() as $name => $relationship) {
      $relationUri = '{relation:' . $name . '}';
      $app->get($baseUriOne . '/' . $relationUri, $me . ':getToOneRelatedResource')->setname($me . ':getToOneRelatedResource');
      $app->get($baseUriRelationships . '/' . $relationUri, $me . ':getToOneRelationshipLink')->setname($me . ':getToOneRelationshipLink');
      $app->patch($baseUriRelationships . '/' . $relationUri, $me . ':patchToOneRelationshipLink')->setname($me . ':patchToOneRelationshipLink');
    }

    foreach ($me::getToManyRelationships() as $name => $relationship) {
      $relationUri = '{relation:' . $name . '}';
      $app->get($baseUriOne . '/' . $relationUri, $me . ':getToManyRelatedResource')->setname($me . ':getToManyRelatedResource');
      $app->get($baseUriRelationships . '/' . $relationUri, $me . ':getToManyRelationshipLink')->setname($me . ':getToManyRelationshipLink');
      $app->patch($baseUriRelationships . '/' . $relationUri, $me . ':patchToManyRelationshipLink')->setname($me . ':patchToManyRelationshipLink');
      $app->post($baseUriRelationships . '/' . $relationUri, $me . ':postToManyRelationshipLink')->setname($me . ':postToManyRelationshipLink');
      $app->delete($baseUriRelationships . '/' . $relationUri, $me . ':deleteToManyRelationshipLink')->setname($me . ':deleteToManyRelationshipLink');
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
