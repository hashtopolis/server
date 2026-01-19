<?php
require_once(dirname(__FILE__) . "/AbstractBaseAPI.class.php");

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpForbiddenException;

abstract class AbstractHelperAPI extends AbstractBaseAPI {
  abstract public function actionPost(array $data): object|array|null;
  
  /**
   * Function in order to create swagger documentation. SHould return either a map of strings that
   * describes the output ex: ["assign" => "succes"] or if the endpoint returns an object it should return
   * the string representation of that object ex: File.
   */
  abstract public static function getResponse(): array|string|null;
  
  public function getParamsSwagger(): array {
    return [];
  }
  
  /**
   * Chunk API endpoint specific call to abort chunk
   * @param Request $request
   * @param Response $response
   * @param array $args
   * @return Response
   * @throws HTException
   * @throws HttpError
   * @throws HttpForbidden
   * @throws JsonException
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   */
  public function processPost(Request $request, Response $response, array $args): Response {
    /* Required calls for all custom requests */
    $this->preCommon($request);
    
    $data = $request->getParsedBody();
    $allFeatures = $this->getAliasedFeatures();
    
    // Validate if correct parameters are sent
    $this->validateParameters($data, $allFeatures);
    
    /* Validate type of parameters */
    $this->validateData($data, $allFeatures);
    
    /* All creation of object */
    $newObject = $this->actionPost($data);
    
    /* Successfully executed action of type update/delete */
    if ($newObject == null) {
      return $response->withStatus(204);
    }
    
    
    /* Successful executed action of create */
    if (is_object($newObject)) {
      $apiClass = new ($this->container->get('classMapper')->get($newObject::class))($this->container);
      return self::getOneResource($apiClass, $newObject, $request, $response);
      /* A meta response of a helper function */
    }
    elseif (is_array($newObject)) {
      return self::getMetaResponse($newObject, $request, $response);
    }
    throw new HttpError("Unable to process request!");
  }
  
  /**
   * Override-able registering of options
   */
  static public function register($app): void {
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
  
  /**
   * Handles HTTP range requests for partial content delivery
   *
   * This method processes the `Range` header from the HTTP request
   * to determine the start and end byte positions for the response,
   * ensuring the range is valid and updates the file pointer accordingly.
   *
   * @param int &$start A reference to the starting byte of the range. This value will be updated.
   * @param int &$end A reference to the ending byte of the range. This value will be updated.
   * @param int &$size The total size of the content in bytes.
   * @param resource &$fp A file pointer resource to seek to the correct position for the range.
   * @return bool Returns `true` if the range request is valid and successfully processed, or `false` otherwise.
   *
   * @throws InvalidArgumentException If the `Range` header is malformed.
   *
   * @note This function assumes the presence of the `HTTP_RANGE` header in the `$_SERVER` superglobal.
   */
  protected function handleRangeRequest(int &$start, int &$end, int &$size, &$fp): bool {
    $c_start = $start;
    $c_end = $end;
    
    list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
    
    if (str_contains($range, ',')) {
      return false;
    }
    if ($range == '-') {
      $c_start = $size - substr($range, 1);
    }
    else {
      $range = explode('-', $range);
      $c_start = $range[0];
      if ((isset($range[1]) && is_numeric($range[1]))) {
        $c_end = $range[1];
      }
      else {
        $c_end = $size;
      }
    }
    if ($c_end > $end) {
      $c_end = $end;
    }
    if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
      return false;
    }
    $start = $c_start;
    $end = $c_end;
    fseek($fp, $start);
    return true;
  }
  
  /**
   * @param Request $request
   * @param Response $response
   * @param string $filename
   * @return Response
   * @throws HttpForbiddenException
   */
  protected function startDownload(Request $request, Response $response, string $filename): Response {
    $size = Util::filesize($filename);
    $lastModified = filemtime($filename);
    
    $etag = md5($lastModified . $size);
    $ifNoneMatch = $request->getHeaderLine('If-None-Match');
    if ($ifNoneMatch === $etag) {
      return $response->withStatus(304);
    }
    
    $exp = explode(".", $filename);
    if ($exp[sizeof($exp) - 1] == '7z') {
      $contentType = "application/x-7z-compressed";
    }
    else {
      $contentType = "application/force-download";
    }
    $fp = @fopen($filename, "rb");
    
    if (!$fp) {
      throw new HttpForbiddenException($request, "Can't open the file");
    }
    
    $start = 0;          // Start byte
    $end = $size - 1;    // End byte
    
    $status = 200;
    if (isset($_SERVER['HTTP_RANGE'])) {
      if (!$this->handleRangeRequest($start, $end, $size, $fp)) {
        fclose($fp);
        return $response->withStatus(416)
          ->withHeader("Content-Range", "bytes $start-$end/$size");
      }
      else {
        $status = 206;
      }
    }
    
    $length = $end - $start + 1; //content-length
    $buffer = 1024 * 100;
    $stream = $response->getBody();
    while (!feof($fp) && ($p = ftell($fp)) <= $end) {
      if ($p + $buffer > $end) {
        $buffer = $end - $p + 1;
      }
      $stream->write(fread($fp, $buffer));
    }
    fclose($fp);
    
    return $response->withStatus($status)
      ->withHeader("Content-Type", $contentType)
      ->withHeader("Content-Description", $filename)
      ->withHeader("Content-Disposition", "attachment; filename=\"" . $filename . "\"")
      ->withHeader("Accept-Ranges", "Byte")
      ->withHeader("Content-Range", "bytes $start-$end/$size")
      ->withHeader("Content-Length", $length)
      ->withHeader("ETag", $etag);
  }
}
