<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentEnvelope;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``testConnection`` action.
 *
 * This is the simplest agent API action: no auth, no DB, always returns
 * ``{"action":"testConnection","response":"SUCCESS"}``.
 *
 * It is the first handler migrated from the legacy ``APITestConnection`` class
 * as part of Phase 2.  Unlike the legacy handler it does NOT use ``echo`` +
 * ``die()``; it returns a proper PSR-7 ``ResponseInterface``.
 */
final class TestConnectionAction implements AgentAction {
    public function __invoke(Request $request, Response $response): ResponseInterface {
        $envelope = AgentEnvelope::success(PActions::TEST_CONNECTION);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($envelope));
        return $response;
    }
}
