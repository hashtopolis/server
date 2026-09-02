<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\common;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Always parses the request body as JSON, regardless of Content-Type.
 *
 * Agent API clients (e.g. DummyAgent, the Python agent) send JSON payloads
 * without a ``Content-Type: application/json`` header, so Slim's built-in body
 * parser (which checks the content type) cannot be used.  This middleware
 * reads ``php://input``, JSON-decodes it, and stores the result as the parsed
 * body on the request so that downstream middleware (e.g.
 * {@see \Hashtopolis\inc\agentapi\auth\TokenAuthMiddleware}) and the route
 * handler can access it via ``$request->getParsedBody()``.
 */
final class AgentBodyParserMiddleware implements MiddlewareInterface {
    public function process(Request $request, RequestHandler $handler): ResponseInterface {
        if ($request->getParsedBody() === null) {
            $raw = file_get_contents('php://input');
            $parsed = json_decode($raw, true);
            if (!is_array($parsed)) {
                $parsed = [];
            }
            $request = $request->withParsedBody($parsed);
        }
        return $handler->handle($request);
    }
}
