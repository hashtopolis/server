<?php
require_once(dirname(__FILE__) . "/AbstractBaseAPI.class.php");

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class AbstractHelperAPI extends AbstractBaseAPI {
  abstract public function actionPost($mappedFeatures, $QUERY): array|null;
  
  
  protected function validateParameters($QUERY, $validFeatures) 
  {
    // Find keys which are invalid
    $invalidKeys = [];
    foreach (array_keys($QUERY) as $NAME) {
      if (!array_key_exists($NAME, $validFeatures)) {
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
    foreach ($validFeatures as $NAME => $FEATURE) {
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
    if (count($missingKeys) > 0) {
      throw new HTException("Required parameter(s) '" .  join(", ", $missingKeys) . "' not specified");
    }
  }


  /* Chunk API endpoint specific call to abort chunk */
  public function processPost(Request $request, Response $response, array $args): Response 
  {
    /* Required calls for all custom requests */
    $this->preCommon($request);
    $QUERY = $request->getParsedBody();

    $features = $this->getFeatures();

    // Validate if correct parameters are sent
    $this->validateParameters($QUERY, $features);

    /* Validate type of parameters */
    $this->validateData($QUERY, $features);

    // Convert incoming (JSON) data to DB values
    $mappedQuery = [];
    foreach ($QUERY as $KEY => $VALUE) {
      $mappedQuery[$KEY] = self::json2db($features[$KEY], $VALUE);
    }

    /* All creation of object */
    try {
      // TODO: Validate data is compliant with https://jsonapi.org/format/#document-top-level 'Primary data'
      $data = $this->actionPost($mappedQuery, $QUERY);
      $status = ($data) ? 200 : 204;
      $retval['data'] = $data;
    } catch (Exception $e) {
      // https://jsonapi.org/format/#error-objects
      $status = $e->getCode();
      $retval['errors'] = [
        'status' => $e->getCode(),
        'source' => $e->getFile() . ':' . $e->getLine(),
        'title' => $e->getMessage(),
      ];
    } finally {
      if ($status == 204) {
        return $response->withStatus($status);        
      } else {
        $response->getBody()->write(json_encode($retval, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);     
        return $response->withStatus($status)
        ->withHeader("Content-Type", "application/json");
      }
    } 
  }  

  /**
   * Override-able registering of options
   */
  static public function register($app): void
  {
    $me = get_called_class();
    $baseUri = $me::getBaseUri();

    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });

    $available_methods = $me::getAvailableMethods();

    if (in_array("GET", $available_methods)) {
      $app->get($baseUri, $me . ':actionGet')->setname($me . ':actionGet');
    }

    if (in_array("POST", $available_methods)) {
      $app->post($baseUri, $me . ':processPost')->setname($me . ':processPost');
    }

    if (in_array("PATCH", $available_methods)) {
      $app->patch($baseUri, $me . ':actionPatch')->setName($me . ':actionPatch');
    }

    if (in_array("DELETE", $available_methods)) {
      $app->delete($baseUri, $me . ':actionDelete')->setName($me . ':actionDelete');
    }
  }
}
