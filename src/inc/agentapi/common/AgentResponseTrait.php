<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\common;

use Exception;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\Util;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

/**
 * Shared helpers for PSR-7 agent API controllers.
 *
 * Provides the common ``success()`` / ``error()`` response builders and the
 * ``updateAgent()`` activity-tracker, so each controller doesn't need to
 * duplicate this boilerplate.
 */
trait AgentResponseTrait {
    /**
     * Build and write a success envelope to the response.
     *
     * @param array<string,mixed> $fields
     */
    private function success(Response $response, string $action, array $fields = []): ResponseInterface {
        $envelope = AgentEnvelope::success($action, $fields);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($envelope));
        return $response;
    }

    /**
     * Build and write an error envelope to the response.
     */
    private function error(Response $response, string $action, string $message): ResponseInterface {
        $envelope = AgentEnvelope::error($action, $message);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($envelope));
        return $response;
    }
  
  /**
   * Update the agent's last-IP / last-action / last-time fields.
   * @throws Exception
   */
    private function updateAgent(Agent $agent, string $action): Agent {
        return Factory::getAgentFactory()->mset($agent, [
            Agent::LAST_IP   => Util::getIP(),
            Agent::LAST_ACT  => $action,
            Agent::LAST_TIME => time(),
        ]);
    }
}
