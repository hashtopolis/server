<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agent\PQuerySendHealthCheck;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentResponseTrait;
use Hashtopolis\inc\defines\DAgentIgnoreErrors;
use Hashtopolis\inc\defines\DHealthCheckAgentStatus;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\utils\HealthUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``sendHealthCheck`` action.
 *
 * Receives the results of a previously-issued health check (number of cracked
 * hashes, gpu count, timing, errors), marks the ``HealthCheckAgent`` record as
 * COMPLETED or FAILED accordingly, and triggers completion handling for the
 * overall health check.
 */
final class SendHealthCheckAction implements AgentAction {
    use AgentResponseTrait;

    public function __invoke(Request $request, Response $response): ResponseInterface {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        if (!isset($body[PQuery::TOKEN]) || !isset($body[PQuerySendHealthCheck::CHECK_ID]) || !isset($body[PQuerySendHealthCheck::NUM_CRACKED]) || !isset($body[PQuerySendHealthCheck::START]) || !isset($body[PQuerySendHealthCheck::END]) || !isset($body[PQuerySendHealthCheck::NUM_GPUS]) || !isset($body[PQuerySendHealthCheck::ERRORS])) {
            return $this->error($response, PActions::SEND_HEALTH_CHECK, 'Invalid send health check query!');
        }

        /** @var Agent $agent */
        $agent = $request->getAttribute(AgentAction::AGENT_ATTRIBUTE);

        $agent = $this->updateAgent($agent, PActions::SEND_HEALTH_CHECK);

        $healthCheck = Factory::getHealthCheckFactory()->get($body[PQuerySendHealthCheck::CHECK_ID]);
        if ($healthCheck === null) {
            return $this->error($response, PActions::SEND_HEALTH_CHECK, 'Invalid health check id!');
        }
        $qF1 = new QueryFilter(HealthCheckAgent::HEALTH_CHECK_ID, $healthCheck->getId(), '=');
        $qF2 = new QueryFilter(HealthCheckAgent::AGENT_ID, $agent->getId(), '=');
        $healthCheckAgent = Factory::getHealthCheckAgentFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
        if ($healthCheckAgent === null) {
            return $this->error($response, PActions::SEND_HEALTH_CHECK, 'Invalid health check agent id!');
        }

        $numCracked = intval($body[PQuerySendHealthCheck::NUM_CRACKED]);
        $numGpus = intval($body[PQuerySendHealthCheck::NUM_GPUS]);
        $errors = $body[PQuerySendHealthCheck::ERRORS];
        $start = intval($body[PQuerySendHealthCheck::START]);
        $end = intval($body[PQuerySendHealthCheck::END]);

        if (!is_array($errors)) {
            $errors = [$errors];
        }

        $status = DHealthCheckAgentStatus::COMPLETED;
        if (sizeof($errors) > 0 && $agent->getIgnoreErrors() == DAgentIgnoreErrors::NO) {
            $status = DHealthCheckAgentStatus::FAILED;
        }
        elseif ($numCracked != $healthCheck->getExpectedCracks()) {
            $status = DHealthCheckAgentStatus::FAILED;
        }

        $healthCheckAgent->setCracked($numCracked);
        $healthCheckAgent->setNumGpus($numGpus);
        $healthCheckAgent->setErrors(json_encode($errors));
        $healthCheckAgent->setStart($start);
        $healthCheckAgent->setEnd($end);
        $healthCheckAgent->setStatus($status);
        Factory::getHealthCheckAgentFactory()->update($healthCheckAgent);

        DServerLog::log(DServerLog::DEBUG, 'Agent sent health check results', [$agent, $healthCheck, $healthCheckAgent]);

        HealthUtils::checkCompletion($healthCheck);
        return $this->success($response, PActions::SEND_HEALTH_CHECK);
    }
}
