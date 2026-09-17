<?php

namespace Hashtopolis\inc\apiv2\auth;

use Hashtopolis\inc\StartupConfig;
use JimTools\JwtAuth\Decoder\FirebaseDecoder;
use JimTools\JwtAuth\Middleware\JwtAuthentication;
use JimTools\JwtAuth\Options;
use JimTools\JwtAuth\Rules\RequestMethodRule;
use JimTools\JwtAuth\Rules\RequestPathRule;
use JimTools\JwtAuth\Secret;

/**
 * Creates the JWT authentication middleware with the hashtopolis specific
 * token configuration, so it can be shared between the apiv2 and other
 * applications like the download endpoint.
 */
class JwtAuthenticationFactory {
  /**
   * @param string[] $ignorePaths Request paths which should not be authenticated
   */
  public static function create(array $ignorePaths): JwtAuthentication {
    $decoder = new FirebaseDecoder(
      new Secret(StartupConfig::getInstance()->getPepper(0), 'HS256', hash("sha256", StartupConfig::getInstance()->getPepper(0)))
    );

    $options = new Options(
      isSecure: false,
      attribute: null,
      before: new JWTBeforeHandler
    );

    $rules = [
      new RequestPathRule(ignore: $ignorePaths),
      new RequestMethodRule(ignore: ["OPTIONS"])
    ];
    return new JwtAuthentication($options, $decoder, $rules);
  }
}
