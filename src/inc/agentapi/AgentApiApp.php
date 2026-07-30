<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi;

use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agentapi\common\ActionRegistry;
use Hashtopolis\inc\agentapi\error\AgentErrorHandler;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\Util;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response;

/**
 * Creates and configures the Slim 4 application for the agent API.
 *
 * A single ``POST`` route dispatches on the ``action`` field of the JSON body
 * via {@see ActionRegistry}.  In Phase 1 the route handler delegates to the
 * existing ``src/inc/api/API*.php`` handlers, which use ``echo`` + ``die()``.
 * Unknown / missing actions and malformed bodies produce the canonical ``INV``
 * error envelope as a proper PSR-7 response.
 */
final class AgentApiApp {
    /**
     * Create the configured Slim application.
     */
    public static function create(): \Slim\App {
        $app = AppFactory::create();

        $errorMiddleware = $app->addErrorMiddleware(true, true, true);
        $customErrorHandler = function (
            Request $request,
            \Throwable $exception,
            bool $displayErrorDetails,
            bool $logErrors,
            bool $logErrorDetails,
        ): ResponseInterface {
            return AgentErrorHandler::invResponse();
        };
        $errorMiddleware->setDefaultErrorHandler($customErrorHandler);
        $app->addRoutingMiddleware();

        $app->post('/api/server.php', function (Request $request, Response $response): ResponseInterface {
            $body = json_decode(file_get_contents('php://input'), true);
            if (!is_array($body)) {
                $body = [];
            }

            $action = $body[PQuery::ACTION] ?? null;

            DServerLog::log(DServerLog::TRACE, 'Received from ' . Util::getIP() . ': ' . json_encode($body));

            $handlerClass = ActionRegistry::getHandler($action);
            if ($handlerClass === null) {
                return AgentErrorHandler::invResponse();
            }

            /** @var \Hashtopolis\inc\api\APIBasic $handler */
            $handler = new $handlerClass();
            $handler->execute($body);

            return $response;
        });

        return $app;
    }
}
