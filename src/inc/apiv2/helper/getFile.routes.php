<?php

use DBA\File;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DBA\Factory;
use Middlewares\Utils\HttpErrorException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpForbiddenException;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class GetFileHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/getFile";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [File::PERM_READ];
  }
  
  /**
   * getfile is different because it returns actual binary data.
   */
  public static function getResponse(): null {
    return null;
  }
  
  
  public function actionPost(array $data): object|array|null {
    throw new HttpErrorException("GetFile has no POST");
  }
  
  /**
   * @throws HTException
   */
  public function validateFile($request, $file_id): string {
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
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws HTException
   * @throws HttpErrorException
   * @throws HttpForbidden
   */
  public function handleGet(Request $request, Response $response): Response {
    $this->preCommon($request);
    $fileParam = $request->getQueryParams()['file'];
    if ($fileParam == null) {
      throw new HttpErrorException("No File query param has been provided");
    }
    $file_id = intval($fileParam);
    
    $filename = $this->validateFile($request, $file_id);
    
    return $this->startDownload($request, $response, $filename);
  }
  
  static public function register($app): void {
    $baseUri = GetFileHelperAPI::getBaseUri();
    
    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, "getFileHelperAPI:handleGet");
  }
}

use Slim\App;
/** @var App $app */
GetFileHelperAPI::register($app);