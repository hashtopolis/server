<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\auth;

use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agentapi\common\ActionRegistry;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\error\AgentErrorHandler;
use Hashtopolis\inc\api\APIBasic;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\Util;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Reads the agent token from the JSON body and loads the matching Agent.
 *
 * Auth is skipped for ``testConnection`` and ``register`` (no token needed)
 * and for unknown / missing actions (the route handler produces the ``INV``
 * envelope).
 *
 * For all other registered actions:
 * - **PSR-7 controllers** (implementing {@see AgentAction}): the middleware
 *   handles ALL auth failures — both missing and invalid tokens short-circuit
 *   with ``"Invalid token!"``.  Controllers can assume the agent is always
 *   non-null and never need to duplicate this check.
 * - **Legacy handlers** (extending {@see APIBasic}): the middleware only
 *   short-circuits on **present but invalid** tokens.  When the token is
 *   missing, the request passes through so the legacy handler's own
 *   ``isValid()`` can produce the field-validation error
 *   ``"Invalid X query!"``.
 *
 * In all cases where the token is present and valid, the Agent is attached to
 * the request via {@see AgentAction::AGENT_ATTRIBUTE}.
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

        $handlerClass = ActionRegistry::getHandler($action);
        if ($handlerClass === null) {
            return $handler->handle($request);
        }

        $isPSR7 = !is_subclass_of($handlerClass, APIBasic::class);

        $token = is_array($body) ? ($body[PQuery::TOKEN] ?? null) : null;
        if ($token === null) {
            if ($isPSR7) {
                return AgentErrorHandler::errorResponse($action, 'Invalid token!');
            }
            return $handler->handle($request);
        }

        $qF = new QueryFilter(Agent::TOKEN, $token, '=');
        $agent = Factory::getAgentFactory()->filter([Factory::FILTER => [$qF]], true);
        if ($agent === null) {
            DServerLog::log(DServerLog::WARNING, 'Agent from ' . Util::getIP() . ' sent invalid token!');
            return AgentErrorHandler::errorResponse($action, 'Invalid token!');
        }

        return $handler->handle($request->withAttribute(AgentAction::AGENT_ATTRIBUTE, $agent));
    }
}
