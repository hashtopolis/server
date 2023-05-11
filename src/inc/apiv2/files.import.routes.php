<?php

/*  File import API
 *    Based on TUS protocol: https://tus.io/protocols/resumable-upload.html
 * 
 *  1) Client 'Announce' file at ./api/v2/ui/files/import'
 *      - Ensure Upload-Metadata: filename= base64-encoded-filename is set
 *  2) Server checks filename does not exists yet:
 *     - Checked not part of ongoing transfer (<uuid>.part / <uuid>.metatadata in import directory)
 *     - Checked not uploaded yet (import/<filename>)
 *     - Checked not present yet (files/<filename>)
 *     If all conditions are met, upload is created and user informed about UUID to push to.
 *  3) Client pushes parts to ./api/v2/ui/files/<uuid>
 *  4) Server check if upload is completed
 *     - Marks file and stores as import/<filename>
 */ 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\QueryFilter;
use DBA\User;
use DBA\Factory;

require_once(dirname(__FILE__) . "/../load.php");

function getUploadPath(string $id): string {
  $filename = "/tmp/" . $id . '.part';
  return $filename;
};

function getMetaPath(string $id): string {
  $filename = "/tmp/" . $id . '.meta';
  return $filename;
};

function getImportPath(string $id): string {
  $filename = Factory::getStoredValueFactory()->get(DDirectories::IMPORT)->getVal() . "/" . $id;
  return $filename;
};

function getChecksumAlgorithm(): array {
  return ['md5', 'sha1' ,'crc32'];
}


/* Database quick for temponary storage during upload */
function getMetaStorage(string $id): array {
  $metaPath = getMetaPath($id);
  $ds = file_exists($metaPath) ? (array)json_decode(file_get_contents($metaPath), true) : array();

  return $ds;
}

function updateStorage(string $id, array $update): void {
  $ds = getMetaStorage($id);

  $newDs = $update + $ds;
  $metaPath = getMetaPath($id);
  file_put_contents($metaPath, json_encode($newDs));
}


$app->group("/api/v2/ui/files/import", function (RouteCollectorProxy $group) { 
  $group->options('', function (Request $request, Response $response, array $args): Response {
    return $response->withStatus(204)
      ->withHeader('Tus-Version', '1.0.0')
      ->withHeader('Tus-Resumable', '1.0.0')
      ->withHeader('Tus-Checksum-Algorithm', join(',', getChecksumAlgorithm()))
      //TODO: Maybe add Upload-Expires support. Return in PATCH with RFC 7231
      ->withHeader('Tus-Extension', 'checksum,creation,creation-defer-length,termination')
      ->withHeader('Access-Control-Expose-Headers', 'Tus-Version, Tus-Resumable, Tus-Checksum-Algorithm, Tus-Extension');
      //TODO: Option for Tus-Max-Size: 1073741824
  });


  $group->post('', function (Request $request, Response $response, array $args): Response {
    $update = [];
    if ($request->hasHeader('Upload-Metadata')) {
      $update["upload_metadata_raw"] = $request->getHeader('Upload-Metadata')[0];
      if (preg_match('/^[a-zA-Z0-9=, ]+$/', $update["upload_metadata_raw"], $match) === false) {
        return $response->withStatus(400, 'Error Upload-Metadata contains non-ASCII characters');
      }
     
      $update_metadata = [];
      $list = explode(",", $update["upload_metadata_raw"]);
      foreach ($list as $item) {
        list($key, $b64val) = explode(" ", $item);
        if (($val = base64_decode($b64val, true)) === false) {
          return $response->withStatus(400, "Error Upload-Metadata '$key' invalid base64 encoding");
        }
        $update_metadata[$key] = $val;
      }
    }
    // TODO: Should filename be mandatory?
    if (array_key_exists('filename', $update_metadata)) {
      $filename = $update_metadata['filename'];
      $id = md5($filename);
      if ((file_exists(getImportPath($filename))) || 
          (file_exists(getUploadPath($id)))) {
            return $response->withStatus(400, "Error filename '$filename' already exists!");
          }
    } else {
      $id = bin2hex(random_bytes(16));
    }
    $update["upload_metadata"] = $update_metadata;

    if ($request->hasHeader('Upload-Defer-Length')) {
      if ($request->getHeader('Upload-Defer-Length')[0] == "1") {
        $update["upload_defer_length"] = true;
      } else {
        return $response->withStatus(400, 'Invalid Upload-Defer-Length value (choices: 1)');
      }
    }
    if ($request->hasHeader('Upload-Length')) {
      $update["upload_length"] = intval($request->getHeader('Upload-Length')[0]);
      $update["upload_defer_length"] = false;
    }

    updateStorage($id, $update);
    file_put_contents(getUploadPath($id), '');

    // TODO: Hash of filename and/or check if similar named file already exists
    return $response->withStatus(201)
      ->withHeader("Location", "/api/v2/ui/files/import/$id")
      ->withHeader('Tus-Resumable', '1.0.0')
      ->withHeader('Access-Control-Expose-Headers', 'Location, Tus-Resumable');
    });
});

$app->group("/api/v2/ui/files/import/{id:[0-9a-f]{32}}", function (RouteCollectorProxy $group) { 
  /* Allow preflight requests */
  $group->options('', function (Request $request, Response $response, array $args): Response {
    return $response;
  });

  
  $group->map(['HEAD'], '', function (Request $request, Response $response, array $args): Response {
    // TODO return 404 or 410 if entry is not found
    $filename = getUploadPath($args['id']);
    $currentSize = filesize($filename);
    $ds = getMetaStorage($args['id']);

    $newResponse = $response->withStatus(200)
      ->withHeader("Cache-Control", "no-store")
      ->withHeader("Upload-Offset",   strval($currentSize))
      ->withHeader("Access-Control-Expose-Headers", "Cache-Control, Upload-Offset")
    ;
    
    if (array_key_exists("upload_metadata_raw", $ds)) {
      $cors_headers = $newResponse->getHeaderLine("Access-Control-Expose-Headers");
      $newResponse2 = $newResponse
        ->withHeader("Upload-Metadata", $ds["upload_metadata_raw"])
        ->withHeader("Access-Control-Expose-Headers", $cors_headers . ", Upload-Metadata")
      ;
    } else {
      $newResponse2 = $newResponse;
    }

    if ($ds["upload_defer_length"] === true) {
      $cors_headers = $newResponse->getHeaderLine("Access-Control-Expose-Headers");
      return $newResponse2
        ->withHeader("Upload-Defer-Length", "1")
        ->withHeader("Access-Control-Expose-Headers", $cors_headers . ", Upload-Defer-Length")
      ;
    } else {
      $cors_headers = $newResponse->getHeaderLine("Access-Control-Expose-Headers");
      return $newResponse2
        ->withHeader("Upload-Length", strval($ds["upload_length"]))
        ->withHeader("Access-Control-Expose-Headers", $cors_headers . ", Upload-Length")
      ;
    }
  });




  $group->patch('', function (Request $request, Response $response, array $args): Response {
    // Check for Content-Type: application/offset+octet-stream or return 415
    if (($request->hasHeader('Content-Type') == false) || 
        ($request->getHeader('Content-Type')[0] != "application/offset+octet-stream")) {
      return $response->withStatus(415, 'Unsupported Media Type');
    }

    /* Return 404 if entry is not found */
    $filename = getUploadPath($args['id']);
    if (file_exists($filename) === false) {
      // TODO: Maybe 410 if actual file still exists and meta file also exists?
      return $response->withStatus(404, "Upload ID does not exists"); 
    }

    /* Offset mismatch check and 409 Conflict */
    $currentSize = filesize($filename);
    if ($request->hasHeader('Upload-Offset') == false) {
      return $response->withStatus(409, "Conflict (Upload-Offset header missing)");
    } else {
      $uploadOffset = intval($request->getHeader('Upload-Offset')[0]);
      if ($uploadOffset != $currentSize) {
        return $response->withStatus(409, "Conflict (currentSize=$currentSize uploadOffset=$uploadOffset)");
      }
    }

    $body = $request->getBody();

    // TODO: Should we even check this and which error to return?
    $contentLength = intval($request->getHeader('Content-Length')[0]);
    $chunk = $body->getContents();
    if (strlen($chunk) != $contentLength) {
      return $response->withStatus(400, 'Mismatch between Content-Length specified and sent');
    }

    $ds = getMetaStorage($args['id']);

    /* Validate checksum */
    if ($request->hasHeader('Upload-Checksum')) {
      $uploadChecksum = $request->getHeader('Upload-Checksum')[0];
      /* algo base64_checksum */
      $regex = "/^(" . join("|", getChecksumAlgorithm()) . ")" . 
        "[ ]+((?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=))?$/";
      
      if(preg_match($regex, $uploadChecksum, $matches) === false) {
        return $response->withStatus(400, 'Syntax of Upload-Checksum header incorrect');
      } else {
        $algo = $matches[1];
        $incomingHash = $matches[2];
        switch($algo) {
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
            /* Since algoritms are checked in regex, this should never happen */
            assert(False);
        }

        if ($chunkHash != $incomingHash) {
          return $response->withStatus(460, 'Checksum Mismatch');
        }
      }
    }

    if ($ds["upload_defer_length"] === true) {
      if ($request->hasHeader('Upload-Length')) {
        $update["upload_length"] = intval($request->getHeader('Upload-Length')[0]);
        $update["upload_defer_length"] = false;
        updateStorage($args['id'], $update);
      }
    }

    file_put_contents($filename, $chunk, FILE_APPEND);

    clearstatcache();
    $newSize = filesize($filename);

    if ($ds["upload_length"] == $newSize) {
      /* Process completed file */
      $statusMsg = "All chunks received";
      if (array_key_exists("upload_metadata", $ds) &&
          array_key_exists("filename", $ds["upload_metadata"])) {
            $targetFile = $ds["upload_metadata"]["filename"];
      } else {
        $targetFile = $args['id'];
      }

      $importPath = getImportPath($targetFile);
      if (file_exists($importPath) === false) {
        rename($filename, $importPath);
        unlink(getMetaPath($args['id']));
      }
    } else {
      $statusMsg = "Next chunk please";
    }

    return $response->withStatus(204, $statusMsg)
      ->withHeader("Tus-Resumable", "1.0.0")
      ->withHeader("Upload-Length", strval($ds["upload_length"]))
      ->withHeader("Upload-Offset", strval($newSize))
      ->WithHeader("Access-Control-Expose-Headers", "Tus-Resumable, Upload-Length, Upload-Offset");
  });

  $group->delete('', function (Request $request, Response $response, array $args): Response {
    // TODO delete file

    // TODO return 404 or 410 if entry is not found
    return $response->withStatus(204)
      ->withHeader("Tus-Resumable", "1.0.0")
      ->WithHeader("Access-Control-Expose-Headers", "Tus-Resumable");
  });
});