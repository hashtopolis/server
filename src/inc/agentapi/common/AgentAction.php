<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\common;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * Interface for PSR-7 agent API action controllers.
 *
 * Each controller is invokable: ``$controller($request, $response)`` returns a
 * PSR-7 ``ResponseInterface`` (no ``echo``, no ``die()``).
 */
interface AgentAction {
    public function __invoke(Request $request, Response $response): ResponseInterface;
}
