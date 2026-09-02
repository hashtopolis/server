<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\common;

use Exception;
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
    /**
     * Name of the request attribute under which the authenticated Agent
     * (loaded by {@see \Hashtopolis\inc\agentapi\auth\TokenAuthMiddleware})
     * is stored.  Controllers read it via
     * ``$request->getAttribute(AgentAction::AGENT_ATTRIBUTE)``.
     */
    public const AGENT_ATTRIBUTE = 'agent';
  
  /**
   * @param Request $request
   * @param Response $response
   * @return ResponseInterface
   * @throws Exception
   */
    public function __invoke(Request $request, Response $response): ResponseInterface;
}
