<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\error;

use Hashtopolis\inc\agent\PResponse;
use Hashtopolis\inc\agent\PResponseErrorMessage;
use Hashtopolis\inc\agent\PValues;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Slim\Psr7\Response;

/**
 * Produces the agent API error envelope on unhandled exceptions.
 *
 * Every error is emitted as ``{"action":"INV","response":"ERROR","message":...}``
 * with ``Content-Type: application/json``.
 */
final class AgentErrorHandler {
    /**
     * Build the canonical INV error envelope and PSR-7 response.
     */
    public static function invResponse(string $message = 'Invalid query!'): ResponseInterface {
        $response = new Response();
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode([
            PResponse::ACTION             => 'INV',
            PResponse::RESPONSE           => PValues::ERROR,
            PResponseErrorMessage::MESSAGE => $message,
        ]));
        return $response;
    }

    /**
     * Build an error envelope for a specific action string.
     */
    public static function errorResponse(string $action, string $message): ResponseInterface {
        $response = new Response();
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode([
            PResponse::ACTION             => $action,
            PResponse::RESPONSE           => PValues::ERROR,
            PResponseErrorMessage::MESSAGE => $message,
        ]));
        return $response;
    }
}
