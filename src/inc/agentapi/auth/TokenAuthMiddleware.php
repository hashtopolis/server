<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\auth;

use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\Util;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

/**
 * Reads the agent token from the JSON body and loads the matching Agent.
 *
 * Auth is skipped for ``testConnection`` and ``register`` (no token needed).
 * For all other actions the token is looked up in the Agent table; if not
 * found, the standard error envelope ``{"action":<action>,"response":"ERROR",
 * "message":"Invalid token!"}`` is emitted and the request short-circuits.
 *
 * The loaded Agent is attached to the request via the ``agent`` attribute so
 * that Phase 2 controllers can access it without re-querying the database.
 *
 * In Phase 1 the existing ``API*.php`` handlers still call ``checkToken``
 * themselves; this middleware is the scaffolding that will replace those calls.
 */
final class TokenAuthMiddleware implements MiddlewareInterface {
    /**
     * Actions that do not require token authentication.
     */
    private const NO_AUTH_ACTIONS = [
        PActions::TEST_CONNECTION,
        PActions::REGISTER,
    ];

    public function process(Request $request, RequestHandler $handler): ResponseInterface {
        $body = $request->getParsedBody();
        $action = is_array($body) ? ($body[PQuery::ACTION] ?? null) : null;

        if (in_array($action, self::NO_AUTH_ACTIONS, true)) {
            return $handler->handle($request);
        }

        $token = is_array($body) ? ($body[PQuery::TOKEN] ?? null) : null;
        if ($token === null) {
            return $this->errorResponse($action ?? 'INV', 'Invalid token!');
        }

        $qF = new QueryFilter(Agent::TOKEN, $token, '=');
        $agent = Factory::getAgentFactory()->filter([Factory::FILTER => [$qF]], true);
        if ($agent === null) {
            DServerLog::log(DServerLog::WARNING, 'Agent from ' . Util::getIP() . ' sent invalid token!');
            return $this->errorResponse($action ?? 'INV', 'Invalid token!');
        }

        return $handler->handle($request->withAttribute('agent', $agent));
    }

    private function errorResponse(string $action, string $message): ResponseInterface {
        $response = new Response();
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode([
            'action'   => $action,
            'response' => 'ERROR',
            'message'  => $message,
        ]));
        return $response;
    }
}
