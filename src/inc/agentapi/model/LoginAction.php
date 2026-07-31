<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agent\PQueryLogin;
use Hashtopolis\inc\agent\PResponseLogin;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentEnvelope;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\StartupConfig;
use Hashtopolis\inc\Util;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``login`` action.
 *
 * Validates that ``token`` and ``clientSignature`` are present, then uses the
 * Agent loaded by {@see \Hashtopolis\inc\agentapi\auth\TokenAuthMiddleware}
 * (guaranteed non-null because the middleware short-circuits on invalid
 * tokens).  Stores the client signature, updates the agent's activity fields,
 * and returns server config (multicast, timeout, version).
 */
final class LoginAction implements AgentAction {
    public function __invoke(Request $request, Response $response): ResponseInterface {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        if (!isset($body[PQuery::TOKEN]) || !isset($body[PQueryLogin::CLIENT_SIGNATURE])) {
            return $this->error($response, PActions::LOGIN, 'Invalid login query!');
        }

        /** @var Agent $agent */
        $agent = $request->getAttribute(AgentAction::AGENT_ATTRIBUTE);

        $agent = Factory::getAgentFactory()->set(
            $agent,
            Agent::CLIENT_SIGNATURE,
            htmlentities($body[PQueryLogin::CLIENT_SIGNATURE], ENT_QUOTES, 'UTF-8'),
        );
        $agent = Factory::getAgentFactory()->mset($agent, [
            Agent::LAST_IP   => Util::getIP(),
            Agent::LAST_ACT  => PActions::LOGIN,
            Agent::LAST_TIME => time(),
        ]);

        DServerLog::log(DServerLog::DEBUG, 'Agent logged in', [$agent]);

        $envelope = AgentEnvelope::success(PActions::LOGIN, [
            PResponseLogin::MULTICAST => (bool)SConfig::getInstance()->getVal(DConfig::MULTICAST_ENABLE),
            PResponseLogin::TIMEOUT   => (int)SConfig::getInstance()->getVal(DConfig::AGENT_TIMEOUT),
            PResponseLogin::VERSION   => StartupConfig::getInstance()->getVersion() . ' (' . Util::getGitCommit() . ')',
        ]);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($envelope));
        return $response;
    }

    private function error(Response $response, string $action, string $message): ResponseInterface {
        $envelope = AgentEnvelope::error($action, $message);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($envelope));
        return $response;
    }
}
