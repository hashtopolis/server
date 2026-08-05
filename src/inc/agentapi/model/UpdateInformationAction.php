<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agent\PQueryUpdateInformation;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentResponseTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``updateInformation`` action.
 *
 * Receives the agent's reported devices, UID and OS, infers whether the
 * agent is CPU-only, persists these details and updates the agent's
 * activity fields.
 */
final class UpdateInformationAction implements AgentAction {
    use AgentResponseTrait;

    public function __invoke(Request $request, Response $response): ResponseInterface {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        if (!isset($body[PQuery::TOKEN]) || !isset($body[PQueryUpdateInformation::DEVICES]) || !isset($body[PQueryUpdateInformation::UID]) || !isset($body[PQueryUpdateInformation::OPERATING_SYSTEM])) {
            return $this->error($response, PActions::UPDATE_CLIENT_INFORMATION, 'Invalid update query!');
        }

        /** @var Agent $agent */
        $agent = $request->getAttribute(AgentAction::AGENT_ATTRIBUTE);

        $devices = $body[PQueryUpdateInformation::DEVICES];
        $uid = htmlentities($body[PQueryUpdateInformation::UID], ENT_QUOTES, 'UTF-8');
        $os = intval($body[PQueryUpdateInformation::OPERATING_SYSTEM]);

        $cpuOnly = 1;
        foreach ($devices as $device) {
            $device = strtolower((string)$device);
            if (str_contains($device, 'amd') || str_contains($device, 'ati ') || str_contains($device, 'radeon') || str_contains($device, 'nvidia') || str_contains($device, 'gtx') || str_contains($device, 'ti') || str_contains($device, 'microsoft')) {
                $cpuOnly = 0;
            }
        }

        if (strlen($agent->getUid()) == 0 && $agent->getCpuOnly() == 0) {
            $agent = Factory::getAgentFactory()->set($agent, Agent::CPU_ONLY, $cpuOnly);
        }
        $agent = Factory::getAgentFactory()->mset($agent, [
            Agent::DEVICES => htmlentities(implode("\n", $devices), ENT_QUOTES, 'UTF-8'),
            Agent::UID     => $uid,
            Agent::OS      => $os,
        ]);

        $agent = $this->updateAgent($agent, PActions::UPDATE_CLIENT_INFORMATION);
        DServerLog::log(DServerLog::DEBUG, 'Agent sent updated client information', [$agent]);

        return $this->success($response, PActions::UPDATE_CLIENT_INFORMATION);
    }
}
