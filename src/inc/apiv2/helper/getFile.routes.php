<?php
use DBA\File;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DBA\Factory;
use Middlewares\Utils\HttpErrorException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpForbiddenException;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class getFileHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/getFile";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [File::PERM_READ];
  }

  public function actionPost(array $data): object|array|null
  {
    assert(False, "GetFile has no POST");
  }

  public function validateFile($request, $file_id) {
    if (!is_numeric($file_id)) {
      throw new HTException("Invalid file id given: " . $file_id);
    }
    $file = Factory::getFileFactory()->get($file_id);
    if (!$file) {
      throw new HttpNotFoundException($request, "No file with id: " . $file_id);
    }
    $filename = Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . "/" . $file->getFilename();
    //checks below should never trigger 
    if (!file_exists($filename)) {
      throw new HttpNotFoundException($request, "File not found at filesystem");
    }
    if (!is_readable($filename)) {
      throw new HttpForbiddenException($request, "Not allowed to read file");
    }

    return $filename;
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
  protected function handleRangeRequest(int &$start, int &$end,  int &$size, &$fp): bool {

    $c_start = $start;
    $c_end = $end;
    
    list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
    
    if (strpos($range, ',') !== false) {
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
   * Description of get params for swagger.
   */
  public function getParamsSwagger(): array {
    return [
      [
      "in" => "query",
      "name" => "file",
      "schema" => [
        "type" => "integer",
        "format" => "int32"
      ],
      "required" => true,
      "example" => 1,
      "description" => "The ID of the file to download."
    ]
    ];
  }

  /**
   * Endpoint to download files
   */
  public function handleGet(Request $request, Response $response): Response {
    $this->preCommon($request);
    $fileParam = $request->getQueryParams()['file'];
    if ($fileParam == null) {
      throw new HttpErrorException("No File query param has been provided");
    }
    $file_id = intval($fileParam); 

    $filename = $this->validateFile($request, $file_id);

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
    } else {
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
      if(!$this->handleRangeRequest($start, $end, $size, $fp)) {
        fclose($fp);
        return $response->withStatus(416)
          ->withHeader("Content-Range",  "bytes $start-$end/$size");
      } else {
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

  static public function register($app): void
  {
    $baseUri = getFileHelperAPI::getBaseUri();

    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, "getFileHelperAPI:handleGet");
  }
}

getFileHelperAPI::register($app); 