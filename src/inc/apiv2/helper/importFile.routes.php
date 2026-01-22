<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Random\RandomException;
use Slim\Routing\RouteCollectorProxy;
use DBA\Factory;

/* Default timeout interval for considering an upload stale/incomplete */
const DEFAULT_UPLOAD_EXPIRES_TIMEOUT = 3600;
require_once(dirname(__FILE__) . "/../../startup/include.php");
require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

/*  File import API
 *    Based on TUS protocol: https://tus.io/protocols/resumable-upload.html
 * 
 *  1) Client 'Announce' file at ./api/v2/helper/importFile'
 *      - Ensure Upload-Metadata: filename= base64-encoded-filename is set
 *  2) Server checks filename does not exists yet:
 *     - Checked not part of ongoing transfer (<uuid>.part / <uuid>.metatadata in import directory)
 *     - Checked not uploaded yet (import/<filename>)
 *     If all conditions are met, upload is created and user informed about UUID to push to.
 *  3) Client pushes parts to ./api/v2/ui/files/<uuid>
 *     - Checked if upload timeout is not expired
 *  4) Server check if upload is completed
 *     - Checked if not present yet (import/<filename>)
 *     - Marks file and stores as import/<filename>
 */

class ImportFileHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/importFile";
  }
  
  public function getRequiredPermissions(string $method): array {
    return [];
  }
  
static function getUploadPath(string $id): string {
  return Factory::getStoredValueFactory()->get(DDirectories::TUS)->getVal() . DIRECTORY_SEPARATOR . 'uploads' . 
    DIRECTORY_SEPARATOR . basename($id) . ".part";
}

static function getMetaPath(string $id): string {
  return Factory::getStoredValueFactory()->get(DDirectories::TUS)->getVal() . DIRECTORY_SEPARATOR .  'meta' 
    . DIRECTORY_SEPARATOR . basename($id) . ".meta";
}

static function getImportPath(string $id): string {
  return Factory::getStoredValueFactory()->get(DDirectories::IMPORT)->getVal() . DIRECTORY_SEPARATOR . basename($id);
}

  /**
   * Import file has no POST parameters
   */
  public function getFormFields(): array {
    return [];
  }
  
  static function getChecksumAlgorithm(): array {
    return ['md5', 'sha1', 'crc32'];
  }
  
  
  /* Database quick for temporary storage during upload */
  static function getMetaStorage(string $id): array {
    $metaPath = self::getMetaPath($id);
    return file_exists($metaPath) ? (array)json_decode(file_get_contents($metaPath), true) : array();
  }
  
  static function updateStorage(string $id, array $update): void {
    $ds = self::getMetaStorage($id);
    
    $newDs = $update + $ds;
    $metaPath = self::getMetaPath($id);
    file_put_contents($metaPath, json_encode($newDs));
  }
  
  //register is overridden so no actionPost needed
  function actionPost(array $data): object|array|null {
    return null;
  }
  
  /**
   * A HEAD request is used in the TUS protocol to determine the offset at which the upload should be continued.
   * And to retrieve the upload status.
   */
  function processHead(Request $request, Response $response, array $args): Response {
    $filename = self::getUploadPath($args['id']);
    if (!is_file($filename)) {
      return $response->withStatus(404);
    }
    $currentSize = filesize($filename);
    $ds = self::getMetaStorage($args['id']);
    
    $newResponse = $response->withStatus(200)
      ->withHeader("Cache-Control", "no-store")
      ->withHeader("Upload-Offset", strval($currentSize))
      ->withHeader("Access-Control-Expose-Headers", "Cache-Control, Upload-Offset");
    
    if (array_key_exists("upload_metadata_raw", $ds)) {
      $cors_headers = $newResponse->getHeaderLine("Access-Control-Expose-Headers");
      $newResponse2 = $newResponse
        ->withHeader("Upload-Metadata", $ds["upload_metadata_raw"])
        ->withHeader("Access-Control-Expose-Headers", $cors_headers . ", Upload-Metadata");
    }
    else {
      $newResponse2 = $newResponse;
    }
    
    $cors_headers = $newResponse->getHeaderLine("Access-Control-Expose-Headers");
    if ($ds["upload_defer_length"] === true) {
      return $newResponse2
        ->withHeader("Upload-Defer-Length", "1")
        ->withHeader("Access-Control-Expose-Headers", $cors_headers . ", Upload-Defer-Length");
    }
    else {
      return $newResponse2
        ->withHeader("Upload-Length", strval($ds["upload_length"]))
        ->withHeader("Access-Control-Expose-Headers", $cors_headers . ", Upload-Length");
    }
  }
  
  /**
   * getfile is different because it returns actual binary data.
   */
  public static function getResponse(): null {
    return null;
  }
  
  /**  File import API
   *    Based on TUS protocol: https://tus.io/protocols/resumable-upload.html
   *
   *  1) Client 'Announce' file at ./api/v2/helper/importFile'
   *      - Ensure Upload-Metadata: filename= base64-encoded-filename is set
   *  2) Server checks filename does not exists yet:
   *     - Checked not part of ongoing transfer (<uuid>.part / <uuid>.metatadata in import directory)
   *     - Checked not uploaded yet (import/<filename>)
   *     If all conditions are met, upload is created and user informed about UUID to push to.
   *  3) Client pushes parts to ./api/v2/ui/files/<uuid>
   *     - Checked if upload timeout is not expired
   *  4) Server check if upload is completed
   *     - Checked if not present yet (import/<filename>)
   *     - Marks file and stores as import/<filename>
   * @throws RandomException
   */
  function processPost(Request $request, Response $response, array $args): Response {
    $update = [];
    if ($request->hasHeader('Upload-Metadata')) {
      $update["upload_metadata_raw"] = $request->getHeader('Upload-Metadata')[0];
      if (preg_match('/^[a-zA-Z0-9=, ]+$/', $update["upload_metadata_raw"], $match) === false) {
        $response->getBody()->write('Error Upload-Metadata contains non-ASCII characters');
        return $response->withStatus(400);
      }
      
      $update_metadata = [];
      $list = explode(",", $update["upload_metadata_raw"]);
      foreach ($list as $item) {
        list($key, $b64val) = explode(" ", $item);
        if (!isset($b64val)) {
          $response->getBody()->write("Error Upload-Metadata, should be a key value pair that is separated by a space, no value has been provided");
          return $response->withStatus(400);
        }
        if (($val = base64_decode($b64val, true)) === false) {
          $response->getBody()->write("Error Upload-Metadata '$key' invalid base64 encoding");
          return $response->withStatus(400);
        }
        $update_metadata[$key] = $val;
      }
    }
    // TODO: Should filename be mandatory?
    if (isset($update_metadata) && array_key_exists('filename', $update_metadata)) {
      $filename = $update_metadata['filename'];
      /* Generate unique upload identifier */
      $id = date("YmdHis") . "-" . md5($filename);
      if ((file_exists(self::getImportPath($filename))) ||
        (file_exists(self::getUploadPath($id)))) {
        $response->getBody()->write("Error filename '$filename' already exists!");
        return $response->withStatus(400);
      }
    }
    else {
      $id = bin2hex(random_bytes(16));
    }
    $update["upload_metadata"] = $update_metadata;
    
    if ($request->hasHeader('Upload-Defer-Length') && $request->hasHeader('Upload-Length')) {
      $response->getBody()->write('Error: Cannot provide both Upload-Length and Upload-Defer-Length');
      return $response->withStatus(400);
    }
    if ($request->hasHeader('Upload-Defer-Length')) {
      if ($request->getHeader('Upload-Defer-Length')[0] == "1") {
        $update["upload_defer_length"] = true;
      }
      else {
        $response->getBody()->write('Invalid Upload-Defer-Length value (choices: 1)');
        return $response->withStatus(400);
      }
    }
    if ($request->hasHeader('Upload-Length')) {
      $update["upload_length"] = intval($request->getHeader('Upload-Length')[0]);
      $update["upload_defer_length"] = false;
    }
    
    /* Give user fix amount of time to upload file, before temporary files are removed */
    $update["upload_expires"] = (new DateTime())->getTimestamp() + DEFAULT_UPLOAD_EXPIRES_TIMEOUT;
    
    self::updateStorage($id, $update);
    file_put_contents(self::getUploadPath($id), '');
    
    // TODO: Hash of filename and/or check if similar named file already exists
    return $response->withStatus(201)
      ->withHeader("Location", "/api/v2/helper/importFile/$id")
      ->withHeader('Tus-Resumable', '1.0.0')
      ->withHeader('Access-Control-Expose-Headers', 'Location, Tus-Resumable');
  }
  
  /**
   * Given the offset in the 'Upload Offset' header, the user can use this PATCH endpoint in order to resume the upload.
   */
  function processPatch(Request $request, Response $response, array $args): Response {
    // Check for Content-Type: application/offset+octet-stream or return 415
    if (!$request->hasHeader('Content-Type') ||
      ($request->getHeader('Content-Type')[0] != "application/offset+octet-stream")) {
      $response->getBody()->write('Unsupported Media Type');
      return $response->withStatus(415);
    }
    
    /* Return 404 if entry is not found */
    $filename = self::getUploadPath($args['id']);
    if (file_exists($filename) === false) {
      // TODO: Maybe 410 if actual file still exists and meta file also exists?
      $response->getBody()->write('Upload ID does not exists');
      return $response->withStatus(404);
    }
    
    /* Offset mismatch check and 409 Conflict */
    $currentSize = filesize($filename);
    if (!$request->hasHeader('Upload-Offset')) {
      $response->getBody()->write('Conflict (Upload-Offset header missing)');
      return $response->withStatus(409);
    }
    else {
      $uploadOffset = intval($request->getHeader('Upload-Offset')[0]);
      if ($uploadOffset != $currentSize) {
        $response->getBody()->write("Conflict (currentSize=$currentSize uploadOffset=$uploadOffset)");
        return $response->withStatus(409);
      }
    }
    
    $body = $request->getBody();
    
    // TODO: Should we even check this and which error to return?
    $contentLength = intval($request->getHeader('Content-Length')[0]);
    $chunk = $body->getContents();
    if (strlen($chunk) != $contentLength) {
      $response->getBody()->write('Mismatch between Content-Length specified and sent');
      return $response->withStatus(400);
    }
    
    $ds = self::getMetaStorage($args['id']);
    
    /* Validate if upload time is still valid */
    $now = new DateTimeImmutable();
    if (!isset($ds['upload_expires'])) {
      throw new HttpError("The meta file of this upload is incorrect");
    }
    $dt = (new DateTime())->setTimeStamp($ds['upload_expires']);
    if (($dt->getTimestamp() - $now->getTimestamp()) <= 0) {
      Util::tusFileCleaning();
      $response->getBody()->write('Upload token expired');
      return $response->withStatus(410);
    }
    
    /* Validate checksum */
    if ($request->hasHeader('Upload-Checksum')) {
      $uploadChecksum = $request->getHeader('Upload-Checksum')[0];
      /* algo base64_checksum */
      $regex = "/^(" . join("|", self::getChecksumAlgorithm()) . ")" .
        "[ ]+((?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=))?$/";
      
      if (preg_match($regex, $uploadChecksum, $matches) === false) {
        $response->getBody()->write('Syntax of Upload-Checksum header incorrect');
        return $response->withStatus(400);
      }
      else {
        $algo = $matches[1];
        $incomingHash = $matches[2];
        switch ($algo) {
          case "md5":
            $chunkHash = base64_encode(md5($chunk, true));
            break;
          case "sha1":
            $chunkHash = base64_encode(sha1($chunk, true));
            break;
          case "crc32":
            $chunkHash = base64_encode(crc32($chunk, true));
            break;
          default:
            /* Since algorithms are checked in regex, this should never happen */
            assert(False);
        }
        
        if ($chunkHash != $incomingHash) {
          $response->getBody()->write('Checksum Mismatch');
          return $response->withStatus(460);
        }
      }
    }
    
    if ($ds["upload_defer_length"] === true) {
      if ($request->hasHeader('Upload-Length')) {
        $update["upload_length"] = intval($request->getHeader('Upload-Length')[0]);
        $update["upload_defer_length"] = false;
        self::updateStorage($args['id'], $update);
      }
    }

    if (file_put_contents($filename, $chunk, FILE_APPEND) === false) {
      $response->getBody()->write('Failed to write to file');
      return $response->withStatus(400);
    }

    clearstatcache();
    $newSize = filesize($filename);
    
    if ($ds["upload_length"] == $newSize) {
      /* Process completed file */
      $statusMsg = "All chunks received";
      if (array_key_exists("upload_metadata", $ds) &&
        array_key_exists("filename", $ds["upload_metadata"])) {
        $targetFile = $ds["upload_metadata"]["filename"];
      }
      else {
        $targetFile = $args['id'];
      }
      
      /* Check if completed file is not created meanwhile */
      $importPath = self::getImportPath($targetFile);
      if (file_exists($importPath)) {
        $response->getBody()->write("Error filename '$targetFile' already exists!");
        return $response->withStatus(400);
      };
      
      /* Migrate completed file to import folder */
      rename($filename, $importPath);
      unlink(self::getMetaPath($args['id']));
    }
    else {
      $statusMsg = "Next chunk please";
    }

    $dt = (new DateTime())->setTimeStamp($ds['upload_expires']);
    $response->getBody()->write($statusMsg);
    return $response->withStatus(204)
      ->withHeader("Tus-Resumable", "1.0.0")
      ->withHeader("Upload-Length", strval($ds["upload_length"]))
      ->withHeader("Upload-Offset", strval($newSize))
      ->withHeader('Upload-Expires', $dt->format(DateTimeInterface::RFC7231))
      ->WithHeader("Access-Control-Expose-Headers", "Tus-Resumable, Upload-Length, Upload-Offset");
  }

  function processDelete(Request $request, Response $response, array $args): Response {
      /* Return 404 if entry is not found */
      $filename_upload = self::getUploadPath($args['id']);
      $filename_meta = self::getMetaPath($args['id']);
      $uploadExists = file_exists($filename_upload);
      $metaExists = file_exists($filename_meta);
      if (!$uploadExists && !$metaExists) {
        throw new HttpError("Upload ID doesnt exists");
      }
      if ($uploadExists) {
        $isDeletedUpload = unlink($filename_upload);
      }
      if ($metaExists) {
        $isDeletedMeta = unlink($filename_meta);
      }

      if (!$isDeletedMeta || !$isDeletedUpload) {
      throw new HttpError("Something went wrong while deleting the files");
      }

      return $response->withStatus(204)
        ->withHeader("Tus-Resumable", "1.0.0")
        ->WithHeader("Access-Control-Expose-Headers", "Tus-Resumable");
  }

  /**
   * Scans the import-directory for files. Directories are ignored.
   * @return array of all files in the top-level directory /../import
   */
  function scanImportDirectory() {
    $directory = Factory::getStoredValueFactory()->get(DDirectories::IMPORT)->getVal() . "/";
    if (file_exists($directory) && is_dir($directory)) {
      $importDirectory = opendir($directory);
      $importFiles = array();
      while ($file = readdir($importDirectory)) {
        if ($file[0] != '.' && $file != "." && $file != ".." && !is_dir($file)) {
          $importFiles[] = array("file" => $file, "size" => Util::filesize($directory . "/" . $file));
        }
      }
      sort($importFiles);
      return $importFiles;
    }
    return array();
  }

  function processGet(Request $request, Response $response, array $args): Response {
    $importFiles = $this->scanImportDirectory();
    return self::getMetaResponse($importFiles, $request, $response);
  }

  
  static public function register($app): void {
    $me = get_called_class();
    $baseUri = $me::getBaseUri();
    
    $app->group($baseUri, function (RouteCollectorProxy $group) use ($me) {
      $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response->withStatus(204)
          ->withHeader('Tus-Version', '1.0.0')
          ->withHeader('Tus-Resumable', '1.0.0')
          ->withHeader('Tus-Checksum-Algorithm', join(',', self::getChecksumAlgorithm()))
          //TODO: Maybe add Upload-Expires support. Return in PATCH with RFC 7231
          ->withHeader('Tus-Extension', 'checksum,creation,creation-defer-length,expiration,termination')
          ->withHeader('Access-Control-Expose-Headers', 'Tus-Version, Tus-Resumable, Tus-Checksum-Algorithm, Tus-Extension');
        //TODO: Option for Tus-Max-Size: 1073741824
      });
      
      $group->post('', $me . ":processPost")->setName($me . ":processPost");
      $group->get('', $me . ":processGet")->setName($me . ":processGet");
    });

    $app->group($baseUri . "/{id:[0-9]{14}-[0-9a-f]{32}}", function (RouteCollectorProxy $group) use ($me) {
      /* Allow preflight requests */
      $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
      });
      
      $group->map(['HEAD'], '', $me . ":processHead")->setName($me . ":processHead");
      $group->patch('', $me . ":processPatch")->setName($me . ":processPatch");
      $group->delete('', $me . ":processDelete")->setName($me . ":processDelete");
    });
  }
}

ImportFileHelperAPI::register($app);
