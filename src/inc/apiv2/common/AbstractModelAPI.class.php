<?php
require_once(dirname(__FILE__) . "/AbstractBaseAPI.class.php");

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Exception\HttpNotFoundException;


use DBA\AbstractModelFactory;
use DBA\AccessGroup;
use DBA\AccessGroupUser;
use DBA\AccessGroupUserFactory;
use DBA\JoinFilter;
use DBA\Factory;
use DBA\ContainFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use Middlewares\Utils\HttpErrorException;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use SebastianBergmann\FileIterator\Facade;
use Slim\Exception\HttpForbiddenException;

use function PHPUnit\Framework\assertCount;

abstract class AbstractModelAPI extends AbstractBaseAPI {
  abstract static public function getDBAClass(): string;
  abstract protected function createObject(array $data): int;
  abstract protected function deleteObject(object $object): void;

  static public function getToOneRelationships(): array { return []; }
  static public function getToManyRelationships(): array { return []; }


  protected function getFactory(): object {
    return self::getModelFactory($this->getDBAclass());
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
      foreach($objects as $object) {
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
      foreach($objects as $object) {
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
      foreach($objects as $object) {
        $kv = $object->getKeyValueDict();
        $objectIds[] = $kv[$objectField];
      }
      $qF = new ContainFilter($filterField, $objectIds, $intermediateFactory);        
      $jF = new JoinFilter($intermediateFactory, $joinField, $joinField);
      $hO = $targetFactory->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);

      /* Build mapping Parent -> Intermediate */
      $i2p = [];
      foreach($hO[$intermediateFactory->getModelName()] as $intermidiateObject) {
        $kv = $intermidiateObject->getKeyValueDict();
        $i2p[$kv[$joinField]] = $kv[$filterField];
      }

      /* Associate Target -> Parent (via Intermediate) */
      foreach($hO[$targetFactory->getModelName()] as $targetObject) {
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
    switch(strtoupper($method)) {
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
  protected function getFilterACL(): array {
    return [];
  }
 
   /**
   * API entry point for requesting multiple objects
   */
  public static function getManyResources(object $apiClass, Request $request, Response $response, array $relationFs = []): Response
  {
    $apiClass->preCommon($request);

    $aliasedfeatures = $apiClass->getAliasedFeatures();
    $factory = $apiClass->getFactory();

    $pageAfter = $apiClass->getQueryParameterFamilyMember($request, 'page', 'after') ?? 0;
    // TODO: Maximum and default should be configurable per server instance
    $pageSize = $apiClass->getQueryParameterFamilyMember($request, 'page', 'size') ?? 50000;

    $validExpandables = $apiClass->getExpandables();
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
     * LIMIT is not officially supported via Filters, instead hacked in via the 'commonly' abused 
     * ORDER BY x LIMIT y variants. In our case we need to modify the last order filter.
     * 
     * Since we need to append to the last order filter, make sure to redefine default as defined at
     * /src/dba/AbstractModelFactory.class.php:656 over here.
     * 
     * TODO: Deny pagination with un-stable sorting
     */
    $orderTemplates = $apiClass->makeOrderFilterTemplates($request, $aliasedfeatures);
    if (count($orderTemplates) == 0) {
      array_push($orderTemplates, ['by' => $apiClass->getPrimaryKey(), 'type' => 'ASC']);
    }
    // Alter last order filter to include LIMIT parameter.
    $orderTemplate = array_pop($orderTemplates);
    $orderTemplate['type'] .= ' LIMIT ' . $pageSize;
    array_push($orderTemplates, $orderTemplate);

    // Build actual order filters
    foreach ($orderTemplates as $orderTemplate) {
      $aFs[Factory::ORDER][] = new OrderFilter($orderTemplate['by'], $orderTemplate['type']);
    }

    // Add page[after] filter, note this depends on the ordering used
    if ($orderTemplate['type'] == 'ASC') {
      $aFs[Factory::FILTER][] = new QueryFilter($apiClass->getPrimaryKey(), $pageAfter, '<', $factory);
    } else {
      $aFs[Factory::FILTER][] = new QueryFilter($apiClass->getPrimaryKey(), $pageAfter, '>', $factory);
    }

    /* Include relation filters */
    $finalFs = array_merge($aFs, $relationFs);

    /* Request objects */
    $filterObjects = $factory->filter($finalFs);
    // TODO: Yield 'Max Page Size Exceeded Error' if pagination is not possible and more items are returned than the actual page[size] limit

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
            foreach($expandResultObject as $expandObject) {
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

    // Build self link
    $selfParams = $request->getQueryParams();
    $selfParams['page']['size'] = $pageSize;
    $linksSelf = $request->getUri()->getPath() . '?' .  urldecode(http_build_query($selfParams));

    // Build next link
    $nextParams = $selfParams;
    if (count($objects) == $pageSize) {
      $nextParams['page']['after'] = end($objects)->getId();
      $linksNext = $request->getUri()->getPath() . '?' .  urldecode(http_build_query($nextParams));
    } else {
      // We have no more entries pending
      $linksNext = null;
    }

    // Generate JSON:API GET output
    $ret = [
      "jsonapi" => [
        "version" => "1.1",
        "ext" => [
          "https://jsonapi.org/profiles/ethanresnick/cursor-pagination"
        ],
      ],
      "links" => [
        "self" => $linksSelf,
        "next" => $linksNext,
      ],
      "data" => $dataResources,
    ];

    if (count($expands) > 0) {
      $ret['included'] = $includedResources;
    }

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
   * Get single Resource
   */
  private static function getOneResource(object $apiClass, object $object, Request $request, Response $response, int $statusCode=200): Response
  {
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
      $newObject = $apiClass->obj2Resource($object, $expandResult);

      // For compound document, included resources
      foreach ($expands as $expand) {
        if (array_key_exists($object->getId(), $expandResult[$expand])) {
          $expandResultObject = $expandResult[$expand][$object->getId()];
          if (is_array($expandResultObject)) {
            foreach($expandResultObject as $expandObject) {
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
    
    $selfParams = $request->getQueryParams();
    $linksQuery = urldecode(http_build_query($selfParams));
    
    $linksSelf = $request->getUri()->getPath() . ((!empty($linksQuery)) ? '?' .  $linksQuery : '');

    // Generate JSON:API GET output
    $ret = [
      "jsonapi" => [
        "version" => "1.1",
        "ext" => [
          "https://jsonapi.org/profiles/ethanresnick/cursor-pagination"
        ],
      ],
      "links" => [
        "self" => $linksSelf,
      ],
      "data" => $dataResources[0],
    ];

    if (count($expands) > 0) {
      $ret['included'] = $includedResources;
    }
  
    $body = $response->getBody();
    $body->write($apiClass->ret2json($ret));

    return $response->withStatus($statusCode)
      ->withHeader("Content-Type", "application/vnd.api+json")
      ->withHeader("Location", $linksSelf);
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

    $data = $request->getParsedBody();
    $aliasedfeatures = $this->getAliasedFeatures();
  
    // Validate incoming data
    foreach (array_keys($data) as $key) {
      // Ensure key is a regular string
      if (is_string($key) == False) {
        throw new HttpErrorException("Key '$key' invalid");
      }
      // Ensure key exists in target array
      if (array_key_exists($key, $aliasedfeatures) == False) {
        throw new HttpErrorException("Key '$key' does not exists!");
      }

      // Ensure key can be updated 
      if ($aliasedfeatures[$key]['read_only'] == True) {
        throw new HttpErrorException("Key '$key' is immutable");
      }
      if ($aliasedfeatures[$key]['protected'] == True) {
        throw new HttpErrorException("Key '$key' is protected");
      }
      if ($aliasedfeatures[$key]['private'] == True) {
        throw new HttpErrorException("Key '$key' is private");
      }
    }
    // Validate input data if it matches the correct type or subtype
    $this->validateData($data, $aliasedfeatures);

    // This does the real things, patch the values that were sent in the data.
    $mappedData = $this->unaliasData($data, $aliasedfeatures);
    $this->updateObject($object, $mappedData);

    // Return updated object
    $newObject = $this->getFactory()->get($object->getId());

    $body = $response->getBody();
    $body->write($this->object2JSON($newObject));

    return $response->withStatus(201)
      ->withHeader("Content-Type", "application/json");
  }


  /**
   * API entry point creation of new object
   */
  public function post(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);

    $data = $request->getParsedBody();
    if ($data == null) {
      throw new HttpErrorException("POST request requires data to be present");
    }

    $allFeatures = $this->getAliasedFeatures();

    // Validate incoming parameters
    $this->validateParameters($data, $allFeatures);

    // Validate incoming data by value
    $this->validateData($data, $allFeatures);

    // Remove key aliases and sanitize to 'db values and request creation
    $mappedData = $this->unaliasData($data, $allFeatures);
    $pk = $this->createObject($mappedData);

    // TODO: Return 409 (conflict) if resource already exists or cannot be created

    // Request object again, since post-modified entries are not reflected into object.
    $object = $this->getFactory()->get($pk);
    return self::getOneResource($this, $object, $request, $response, 201);
  }


  /**
   * 
   */
  public function getToOneRelatedResource(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);

    // Base object
    $object = $this->doFetch($request, $args['id']);
   
    // Relation object
    $relationObjects = $this->fetchExpandObjects([$object], $args['relation']);  
    $relationObject = $relationObjects[$args['id']];

    $relationClass = array_column($this->getToOneRelationships(), 'relationType', 'name')[$args['relation']];
    $relationApiClass = new ($this->container->get('classMapper')->get($relationClass))($this->container);

    return self::getOneResource($relationApiClass, $relationObject, $request, $response);
  }


  /**
   * 
   */
  public function getToOneRelationshipLink(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);

    // Base object -> Relationship objects
    $object = $this->doFetch($request, $args['id']);

    $relation = $this->getToOneRelationships()[$args['relation']];
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


    // Generate JSON:API GET output
    $ret = [
      "jsonapi" => [
        "version" => "1.1",
      ],
      "links" => [
        "self" => $linksSelf,
        "related" => $linksRelated,
      ],
      "data" => $dataResource,
    ];


    $body = $response->getBody();
    $body->write($this->ret2json($ret));

    return $response->withStatus(200)
      ->withHeader("Content-Type", 'application/vnd.api+json');
  }

  /**
   * 
   */
  public function patchToOneRelationshipLink(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);
    $object = $this->doFetch($request, $args['id']);

    return $response->withStatus(201)
      ->withHeader("Content-Type", "application/vnd.api+json");
  }


  /**
   * 
   */
  public function getToManyRelatedResource(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);

    // Base object -> Relation objects
    $object = $this->doFetch($request, $args['id']);

    $toManyRelation = $this->getToManyRelationships()[$args['relation']];
    $relationClass = $toManyRelation['relationType'];
    $relationApiClass = new ($this->container->get('classMapper')->get($relationClass))($this->container);

    /* Prepare filter for to-many relations */

    // Example:
    // 'accessGroups' => [
    //   'intermidiate' => AccessGroupUser::class, 
    //   'filterField' => AccessGroupUser::USER_ID,
    //   'joinField' => AccessGroupUser::ACCESS_GROUP_ID,
    //   'joinFieldRelation' => AccessGroup::ACCESS_GROUP_ID,
    //   'relationType' => AccessGroup::class,
    // ],

    $aFs = [];
    if (array_key_exists('intermidiate', $toManyRelation)) {
      $aFs[Factory::FILTER][] = new QueryFilter(
          $toManyRelation['filterField'], 
          $args['id'], 
          '=', 
          self::getModelFactory($toManyRelation['intermidiate'])
        );
          
      $aFs[Factory::JOIN][] = new JoinFilter(
          self::getModelFactory($toManyRelation['intermidiate']),
          $toManyRelation['joinField'],
          $toManyRelation['joinFieldRelation'],
        );
    } else {
      $aFs[Factory::FILTER][] = new QueryFilter(
        $toManyRelation['filterField'], 
        $args['id'], 
        '=', 
      );
    };
    
    return self::getManyResources($relationApiClass, $request, $response, $aFs);
  }


  /**
   * 
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
    $ret = [
      "jsonapi" => [
        "version" => "1.1",
        "ext" => [
          "https://jsonapi.org/profiles/ethanresnick/cursor-pagination"
        ],
      ],
      "links" => [
        "self" => $linksSelf,
        "related" => $linksRelated,
        "next" => $linksNext,
      ],
      "data" => $dataResources,
    ];


    $body = $response->getBody();
    $body->write($this->ret2json($ret));

    return $response->withStatus(200)
      ->withHeader("Content-Type", 'application/vnd.api+json; ext="https://jsonapi.org/profiles/ethanresnick/cursor-pagination"');

  }

  /**
   * 
   */
  public function patchToManyRelationshipLink(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);
    $object = $this->doFetch($request, $args['id']);

    return $response->withStatus(201)
      ->withHeader("Content-Type", "application/vnd.api+json");
  }

  /**
   * 
   */
  public function postToManyRelationshipLink(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);
    $object = $this->doFetch($request, $args['id']);

    return $response->withStatus(201)
      ->withHeader("Content-Type", "application/vnd.api+json");
  }

  /**
   * 
   */
  public function deleteToManyRelationshipLink(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);
    $object = $this->doFetch($request, $args['id']);

    return $response->withStatus(201)
      ->withHeader("Content-Type", "application/vnd.api+json");
  }


  /**
   * Update object with provided values
   */
  public function updateObject(object $object, array $data, array $processed = []): void
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


