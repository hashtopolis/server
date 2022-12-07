<?php

/* Implementation of TUS protocol 1.0.0
 *    https://tus.io/protocols/resumable-upload.html
 */ 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\QueryFilter;
use DBA\User;
use DBA\Factory;

require_once(dirname(__FILE__) . "/../load.php");

function getUploadPath(string $id): string {
  $filename = dirname(__file__) . "/../../tmp/" . $id . '.part';
  return $filename;
};

$app->group("/api/v2/ui/files/upload", function (RouteCollectorProxy $group) { 
  $group->options('', function (Request $request, Response $response, array $args): Response {
    return $response->withStatus(204)
      ->withHeader('Tus-Version', '1.0.0')
      ->withHeader('Tus-Resumable', '1.0.0')
      ->withHeader('Tus-Extension', 'checksum')
      ->withHeader('Tus-Checksum-Algorithm', 'md5,sha1,crc32')
      // TODO: Maybe add Upload-Expires support. Return in PATCH with RFC 7231
      ->withHeader('Tus-Extension', 'creation,creation-with-upload');
      //TODO: Option for Tus-Max-Size: 1073741824
  });




//  if ($request->hasHeader('Accept')) {
    // Do something
//}
//$headerValueArray = $request->getHeader('Accept');


  $group->post('', function (Request $request, Response $response, array $args): Response {
    // Check for
    // a) Upload-Length
    // b) Upload-Defer-Length: 1

    // TODO: Non-empty Content-Length should include first bit of files

    /*
    Content-Length: 0
    Upload-Length: 100
    Tus-Resumable: 1.0.0
    Upload-Metadata: filename d29ybGRfZG9taW5hdGlvbl9wbGFuLnBkZg==,is_confidential
    */
    // TODO: validate Upload-Metadata

    // TODO: Should we even check this and which error to return?
    $contentLength = intval($request->getHeader('Content-Length')[0]);
    $body = $request->getBody();
    if ($body->getSize() != $contentLength) {
      return $response->withStatus(400, 'Mismatch between Content-Length specified and sent');
    }

    // TODO: Hash of filename and/or check if similar named file already exists
    return $response->withStatus(201)
      ->withHeader("Location", "/api/v2/ui/files/upload/24e533e02ec3bc40c387f1a0e460e216")
      ->withHeader('Tus-Resumable', '1.0.0');
  });
});

$app->group("/api/v2/ui/files/upload/{id:[0-9a-f]{32}}", function (RouteCollectorProxy $group) { 
  /* Allow preflight requests */
  $group->options('', function (Request $request, Response $response, array $args): Response {
    return $response;
  });

  
  $group->map(['HEAD'], '', function (Request $request, Response $response, array $args): Response {
    // TODO return 404 or 410 if entry is not found
    $filename = getUploadPath($args['id']);
    $currentSize = filesize($filename);

    return $response->withStatus(200)
        ->withHeader("Cache-Control", "no-store")
        // TODO Actual size of upload header
        ->withHeader("Upload-Length", "100")
        ->withHeader("Upload-Offset", strval($currentSize));
  });




$group->patch('', function (Request $request, Response $response, array $args): Response {
  // Check for Content-Type: application/offset+octet-stream or return 415
  if (($request->hasHeader('Content-Type') == false) || 
      ($request->getHeader('Content-Type')[0] != "application/offset+octet-stream")) {
    return $response->withStatus(415, 'Unsupported Media Type');
  }

  // TODO return 404 or 410 if entry is not found
  $filename = getUploadPath($args['id']);
  $currentSize = filesize($filename);

  // Offset mismatch check and 409 Conflict
    if (($request->hasHeader('Upload-Offset') == false) ||
      (intval($request->getHeader('Upload-Offset')[0]) != $currentSize)) {
    return $response->withStatus(409, 'Conflict');
  }

  $body = $request->getBody();
  
  // TODO: Should we even check this and which error to return?
  $contentLength = intval($request->getHeader('Content-Length')[0]);
  $chunk = $body->getContents();
  if (strlen($chunk) != $contentLength) {
    return $response->withStatus(400, 'Mismatch between Content-Length specified and sent');
  }

  // TODO: Implement
  if ($request->hasHeader('Upload-Checksum')) {
    $uploadChecksum = $request->getHeader('Upload-Checksum');
    // 400 Bad Request
    assert(False);
    // 460 Checksum Mismatch
  }

  file_put_contents($filename, $chunk, FILE_APPEND);

  clearstatcache();
  $newSize = filesize($filename);

  // TODO: Process completed file
  
  return $response->withStatus(204)
    ->withHeader("Tus-Resumable", "1.0.0")
    ->withHeader("Upload-Offset", strval($newSize));
});

  $group->delete('', function (Request $request, Response $response, array $args): Response {
    // TODO delete file

    // TODO return 404 or 410 if entry is not found
    return $response->withStatus(204)
      ->withHeader("Tus-Resumable", "1.0.0");
  });
});