<?php

namespace Hashtopolis\inc\downloadapi;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\apiv2\auth\JwtAuthenticationFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

/**
 * Authentication for the download endpoint. A download is allowed with either
 * an apiv2 JWT ('Authorization: Bearer' header, validated like in the apiv2)
 * or with an agent token ('?token=' query parameter, same as getFile.php).
 *
 * When the authentication was successful, the agent (if authenticated via
 * agent token) is attached to the request with the agent attribute.
 */
final class DownloadAuthMiddleware implements MiddlewareInterface {
  public function process(Request $request, RequestHandler $handler): ResponseInterface {
    if ($request->hasHeader('Authorization')) {
      // apiv2 JWT authentication, attaches the userId, scope and aud attributes
      return JwtAuthenticationFactory::create([])->process($request, $handler);
    }

    $token = $request->getQueryParams()['token'] ?? null;
    if (is_string($token) && strlen($token) > 0) {
      $qF = new QueryFilter(Agent::TOKEN, $token, '=');
      $agent = Factory::getAgentFactory()->filter([Factory::FILTER => $qF], true);
      if ($agent !== null) {
        return $handler->handle($request->withAttribute(AgentAction::AGENT_ATTRIBUTE, $agent));
      }
    }

    $response = new Response(401);
    $response->getBody()->write('No access!');
    return $response;
  }
}
