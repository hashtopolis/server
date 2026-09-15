<?php

namespace Hashtopolis\inc\apiv2\auth;

use Hashtopolis\inc\StartupConfig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Reads and writes the cookie carrying the refresh token.
 *
 * The token is deliberately never part of a response body: kept in an HttpOnly cookie it is out of
 * reach of JavaScript, so cross-site scripting in the frontend cannot walk off with a credential
 * that outlives the access token. The cookie is scoped to the single path that consumes it, which
 * keeps it off every other API request.
 */
class RefreshTokenCookie {
  const COOKIE_NAME = "refreshToken";
  const COOKIE_PATH = "/api/v2/auth/refresh";
  
  /**
   * @param Request $request
   * @return string|null the token string sent by the client, if any
   */
  public static function read(Request $request): ?string {
    $cookies = $request->getCookieParams();
    if (!isset($cookies[self::COOKIE_NAME]) || !is_string($cookies[self::COOKIE_NAME])) {
      return null;
    }
    $value = trim($cookies[self::COOKIE_NAME]);
    
    return $value === "" ? null : $value;
  }
  
  /**
   * Attaches a refresh token to the response.
   *
   * @param Request $request the request being answered, used to decide on the Secure flag
   * @param Response $response
   * @param string $token the token string to hand to the client
   * @return Response
   */
  public static function attach(Request $request, Response $response, string $token): Response {
    $maxAge = StartupConfig::getInstance()->getRefreshTokenLifetime();
    
    return $response->withAddedHeader("Set-Cookie", self::build($request, $token, $maxAge));
  }
  
  /**
   * Instructs the client to drop the refresh token. The attributes have to match those used when the
   * cookie was set, otherwise the browser keeps the original cookie around.
   *
   * @param Request $request the request being answered, used to decide on the Secure flag
   * @param Response $response
   * @return Response
   */
  public static function clear(Request $request, Response $response): Response {
    return $response->withAddedHeader("Set-Cookie", self::build($request, "", 0));
  }
  
  /**
   * @param Request $request
   * @param string $value
   * @param int $maxAge lifetime in seconds; 0 expires the cookie immediately
   * @return string a Set-Cookie header value
   */
  private static function build(Request $request, string $value, int $maxAge): string {
    $config = StartupConfig::getInstance();
    $sameSite = $config->getRefreshCookieSameSite();
    
    $parts = [
      self::COOKIE_NAME . "=" . $value,
      "Path=" . self::COOKIE_PATH,
      "Max-Age=" . $maxAge,
      "Expires=" . gmdate("D, d M Y H:i:s \G\M\T", time() + $maxAge),
      "HttpOnly",
      "SameSite=" . $sameSite,
    ];
    /* SameSite=None is only honoured on cookies which are also Secure, so the two cannot be
       configured against each other without the browser silently dropping the cookie. */
    if (self::isSecure($request) || $sameSite === "None") {
      $parts[] = "Secure";
    }
    
    return implode("; ", $parts);
  }
  
  /**
   * Whether the cookie should be marked Secure. Flagging it on a plain HTTP deployment would make the
   * client withhold the cookie from every subsequent request, so this follows the scheme the request
   * actually arrived over unless the deployment says otherwise.
   *
   * @param Request $request
   * @return bool
   */
  private static function isSecure(Request $request): bool {
    $configured = StartupConfig::getInstance()->getRefreshCookieSecure();
    if ($configured !== null) {
      return $configured;
    }
    
    if ($request->getUri()->getScheme() === "https") {
      return true;
    }
    /* A TLS terminating proxy forwards plain HTTP, and only this header says what the client used. */
    $forwarded = explode(",", $request->getHeaderLine("X-Forwarded-Proto"))[0];
    
    return strtolower(trim($forwarded)) === "https";
  }
}
