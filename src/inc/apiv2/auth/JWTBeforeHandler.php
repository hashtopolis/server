<?php

namespace Hashtopolis\inc\apiv2\auth;

use JimTools\JwtAuth\Handlers\BeforeHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class JWTBeforeHandler implements BeforeHandlerInterface {
  /**
   * @param array{decoded: array<string, mixed>, token: string} $arguments
   */
  public function __invoke(ServerRequestInterface $request, array $arguments): ServerRequestInterface {
    // adds the decoded userId and scope to the request attributes
    return $request->withAttribute("userId", $arguments["decoded"]["userId"])->withAttribute("scope", $arguments["decoded"]["scope"]);
  }
}