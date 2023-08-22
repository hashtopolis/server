<?php
require_once(dirname(__FILE__) . "/AbstractBaseAPI.class.php");

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Exception\HttpNotFoundException;

use DBA\Factory;

use Middlewares\Utils\HttpErrorException;


abstract class AbstractModelAPI extends AbstractBaseAPI {
  abstract static public function getDBAClass(): string;
  abstract protected function getFactory(): object;
  abstract protected function getFilterACL(): array;
  abstract protected function createObject($mappedQuery, $QUERY): int;
  abstract protected function deleteObject(object $object): void;
  /**
   * Constructor receives features
   */
  public function getFeatures(): array
  {
    return call_user_func($this->getDBAclass() . '::getFeatures');
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

    /* Generate filters */
    $qFs_Filter = $this->makeFilter($request, $mappedFeatures);
    $qFs_ACL = $this->getFilterACL();
    $qFs = array_merge($qFs_ACL, $qFs_Filter);

    $oFs = $this->makeOrderFilter($request, $mappedFeatures);

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

    // Convert incoming (JSON) data to DB values
    $mappedQuery = [];
    foreach ($QUERY as $KEY => $VALUE) {
      $mappedQuery[$KEY] = self::json2db($mappedFeatures[$KEY], $VALUE);
    }
    $pk = $this->createObject($mappedQuery, $QUERY);

    // Request object again, since post-modified entries are not reflected into object.
    $body = $response->getBody();
    $body->write($this->object2JSON($this->getFactory()->get($pk)));

    return $response->withStatus(201)
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
      $val = self::json2db($mappedFeatures[$KEY], $data[$KEY]);
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


