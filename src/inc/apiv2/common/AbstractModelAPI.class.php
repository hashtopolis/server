<?php
require_once(dirname(__FILE__) . "/AbstractBaseAPI.class.php");

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Exception\HttpNotFoundException;

use DBA\Factory;

use Middlewares\Utils\HttpErrorException;


abstract class AbstractModelAPI extends AbstractBaseAPI {
  abstract static public function getDBAClass(): string;
  abstract protected function createObject(array $data): int;
  abstract protected function deleteObject(object $object): void;

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
  public function get(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);

    $aliasedfeatures = $this->getAliasedFeatures();
    $factory = $this->getFactory();

    $startAt = $this->getParam($request, 'startsAt', 0);
    $maxResults = $this->getParam($request, 'maxResults', 5);

    $validExpandables = $this->getExpandables();
    $expands = $this->makeExpandables($request, $validExpandables);
    $expandable = array_diff($validExpandables, $expands);

    /* Generate filters */
    $qFs_Filter = $this->makeFilter($request, $aliasedfeatures);
    $qFs_ACL = $this->getFilterACL();
    $qFs = array_merge($qFs_ACL, $qFs_Filter);

    $oFs = $this->makeOrderFilter($request, $aliasedfeatures);

    /* Generate query */
    $allFilters = [];
    if (count($qFs) > 0) {
      $allFilters[Factory::FILTER] = $qFs;
    }
    if (count($oFs) > 0) {
      $allFilters[Factory::ORDER] = $oFs;
    }

    // TODO: Optimize code, should only fetch subsection of database, when pagination is in play
    $objects = $factory->filter($allFilters);

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
    return $this->getAliasedFeatures();
  }


  /**
   * API entry point for requests of single object
   */
  public function getOne(Request $request, Response $response, array $args): Response
  {
    $this->preCommon($request);

    $validExpandables = $this->getExpandables();
    $expands = $this->makeExpandables($request, $validExpandables);
    $expandable = array_diff($validExpandables, $expands);

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
    $allFeatures = $this->getAliasedFeatures();

    // Validate incoming parameters
    $this->validateParameters($data, $allFeatures);

    // Validate incoming data by value
    $this->validateData($data, $allFeatures);

    // Remove key aliases and sanitize to 'db values and request creation
    $mappedData = $this->unaliasData($data, $allFeatures);
    $pk = $this->createObject($mappedData);

    // Request object again, since post-modified entries are not reflected into object.
    $body = $response->getBody();
    $body->write($this->object2JSON($this->getFactory()->get($pk)));

    return $response->withStatus(201)
      ->withHeader("Content-Type", "application/json");
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


