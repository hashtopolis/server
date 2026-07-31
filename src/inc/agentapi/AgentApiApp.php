<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi;

use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agentapi\common\ActionRegistry;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentBodyParserMiddleware;
use Hashtopolis\inc\agentapi\auth\TokenAuthMiddleware;
use Hashtopolis\inc\agentapi\error\AgentErrorHandler;
use Hashtopolis\inc\api\APIBasic;
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
 * via {@see ActionRegistry}.  Migrated actions use PSR-7 controllers that
 * return ``ResponseInterface``; un-migrated legacy actions delegate to the
 * existing ``src/inc/api/API*.php`` handlers (which use ``echo`` + ``die()``).
 *
 * Middleware stack (outermost → innermost on the request path):
 *  1. Routing middleware
 *  2. Error middleware → produces ``INV`` envelope on exceptions
 *  3. Body parser → always parses JSON regardless of Content-Type
 *  4. Token auth → loads Agent by token (skips ``testConnection`` / ``register``)
 */
final class AgentApiApp {
    /**
     * Create the configured Slim application.
     */
    public static function create(): \Slim\App {
        $app = AppFactory::create();

        $app->add(new TokenAuthMiddleware());
        $app->add(new AgentBodyParserMiddleware());

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
            $body = $request->getParsedBody();
            if (!is_array($body)) {
                $body = [];
            }

            $action = $body[PQuery::ACTION] ?? null;

            DServerLog::log(DServerLog::TRACE, 'Received from ' . Util::getIP() . ': ' . json_encode($body));

            $handlerClass = ActionRegistry::getHandler($action);
            if ($handlerClass === null) {
                return AgentErrorHandler::invResponse();
            }

            if (is_subclass_of($handlerClass, APIBasic::class)) {
                /** @var APIBasic $handler Legacy handler using echo+die() */
                $handler = new $handlerClass();
                $handler->execute($body);
                return $response;
            }

            /** @var AgentAction $controller */
            $controller = new $handlerClass();
            return $controller($request, $response);
        });

        return $app;
    }
}
