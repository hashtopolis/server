<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Exception;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQueryRegister;
use Hashtopolis\inc\agent\PResponseRegister;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentEnvelope;
use Hashtopolis\inc\DataSet;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DNotificationType;
use Hashtopolis\inc\defines\DPayloadKeys;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\handlers\NotificationHandler;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\inc\Util;
use Hashtopolis\dba\models\AccessGroupAgent;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\RegVoucher;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``register`` action.
 *
 * Voucher-based agent registration: validates the voucher, creates an Agent
 * row with a random token, assigns it to the default access group, and
 * optionally consumes the voucher (depending on the ``voucherDeletion`` config).
 *
 * No token authentication is required for this action.
 */
final class RegisterAgentAction implements AgentAction {
    public function __invoke(Request $request, Response $response): ResponseInterface {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        if (!isset($body[PQueryRegister::VOUCHER]) || !isset($body[PQueryRegister::AGENT_NAME])) {
            return $this->error($response, PActions::REGISTER, 'Invalid registering query!');
        }

        $qF = new QueryFilter(RegVoucher::VOUCHER, $body[PQueryRegister::VOUCHER], '=');
        $voucher = Factory::getRegVoucherFactory()->filter([Factory::FILTER => $qF], true);
        if ($voucher === null) {
            return $this->error($response, PActions::REGISTER, 'Provided voucher does not exist.');
        }

        $name = htmlentities($body[PQueryRegister::AGENT_NAME], ENT_QUOTES, 'UTF-8');

        $cpuOnly = 0;
        if (isset($body[PQueryRegister::CPU_ONLY]) && $body[PQueryRegister::CPU_ONLY]) {
            $cpuOnly = 1;
        }

        $token = Util::randomString(10);
        $agent = new Agent(null, $name, '', -1, '', '', 0, 1, 0, $token, PActions::REGISTER, time(), Util::getIP(), null, $cpuOnly, '');

        if (SConfig::getInstance()->getVal(DConfig::VOUCHER_DELETION) == 0) {
            Factory::getRegVoucherFactory()->delete($voucher);
        }

        try {
            $agent = Factory::getAgentFactory()->save($agent);
        } catch (Exception $e) {
            DServerLog::log(DServerLog::ERROR, 'Saving of agent failed!', [$agent]);
            return $this->error($response, PActions::REGISTER, 'Could not register you to server: Saving failed!');
        }

        if ($agent === null) {
            DServerLog::log(DServerLog::ERROR, 'Saving of agent failed!', [$agent]);
            return $this->error($response, PActions::REGISTER, 'Could not register you to server: Saving failed!');
        }

        $payload = new DataSet([DPayloadKeys::AGENT => $agent]);
        NotificationHandler::checkNotifications(DNotificationType::NEW_AGENT, $payload);
        DServerLog::log(DServerLog::INFO, 'Registered new agent', [$agent]);

        $accessGroup = AccessUtils::getOrCreateDefaultAccessGroup();
        $accessGroupAgent = new AccessGroupAgent(null, $accessGroup->getId(), $agent->getId());
        Factory::getAccessGroupAgentFactory()->save($accessGroupAgent);
        DServerLog::log(DServerLog::INFO, 'Assigned agent to access group', [$agent, $accessGroup]);

        $envelope = AgentEnvelope::success(PActions::REGISTER, [
            PResponseRegister::TOKEN => $token,
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
