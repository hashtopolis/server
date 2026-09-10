<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\inc\Util;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpForbiddenException;

/**
 * Utilities to stream files as a download response, with support for ETag
 * based caching and partial content (HTTP range) requests.
 */
class DownloadUtils {
  /**
   * Handles a HTTP range request for a file download. Parses the range given
   * in the 'Range' header, ensures the range is valid and updates the file
   * pointer accordingly.
   *
   * @param int &$start A reference to the starting byte of the range. This value will be updated.
   * @param int &$end A reference to the ending byte of the range. This value will be updated.
   * @param int $size The total size of the content in bytes.
   * @param resource $fp A file pointer resource to seek to the correct position for the range.
   * @return bool Returns `true` if the range request is valid and successfully processed, or `false` otherwise.
   *
   * @throws InvalidArgumentException If the `Range` header is malformed.
   *
   * @note This function assumes the presence of the `HTTP_RANGE` header in the `$_SERVER` superglobal.
   */
  public static function handleRangeRequest(int &$start, int &$end, int $size, $fp): bool {
    $c_end = $end;

    list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);

    if (str_contains($range, ',')) {
      return false;
    }
    if ($range == '-') {
      $c_start = $size - (int)substr($range, 1);
    }
    else {
      $range = explode('-', $range);
      $c_start = (int)$range[0];
      if ((isset($range[1]) && is_numeric($range[1]))) {
        $c_end = (int)$range[1];
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
   * Streams the given file as a download response, handling ETag based
   * caching ('If-None-Match') and partial content ('Range') requests.
   *
   * @param Request $request
   * @param Response $response
   * @param string $path Absolute path of the file to stream
   * @param string|null $displayName Filename to announce to the client, defaults to the base name of the path
   * @return Response
   * @throws HttpForbiddenException
   */
  public static function startDownload(Request $request, Response $response, string $path, ?string $displayName = null): Response {
    if ($displayName === null) {
      $displayName = basename($path);
    }
    $size = Util::filesize($path);
    $lastModified = filemtime($path);

    $etag = md5($lastModified . $size);
    $ifNoneMatch = $request->getHeaderLine('If-None-Match');
    if ($ifNoneMatch === $etag) {
      return $response->withStatus(304);
    }

    $exp = explode(".", $path);
    if ($exp[sizeof($exp) - 1] == '7z') {
      $contentType = "application/x-7z-compressed";
    }
    else {
      $contentType = "application/force-download";
    }
    $fp = @fopen($path, "rb");

    if (!$fp) {
      throw new HttpForbiddenException($request, "Can't open the file");
    }

    $start = 0;          // Start byte
    $end = $size - 1;    // End byte

    $status = 200;
    if (isset($_SERVER['HTTP_RANGE'])) {
      if (!DownloadUtils::handleRangeRequest($start, $end, $size, $fp)) {
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
      ->withHeader("Content-Description", $displayName)
      ->withHeader("Content-Disposition", "attachment; filename=\"" . $displayName . "\"")
      ->withHeader("Accept-Ranges", "Byte")
      ->withHeader("Content-Range", "bytes $start-$end/$size")
      ->withHeader("Content-Length", $length)
      ->withHeader("ETag", $etag);
  }
}
