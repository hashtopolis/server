<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agent\PResponseGetHealthCheck;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentResponseTrait;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\utils\HealthUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``getHealthCheck`` action.
 *
 * Determines whether the agent is due for a health check (via
 * {@see HealthUtils::checkNeeded}), then returns the
 * check's attack command, cracker binary id, hash list, check id and the
 * configured hashlist alias.
 */
final class GetHealthCheckAction implements AgentAction {
    use AgentResponseTrait;

    public function __invoke(Request $request, Response $response): ResponseInterface {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        if (!isset($body[PQuery::TOKEN])) {
            return $this->error($response, PActions::GET_HEALTH_CHECK, 'Invalid get health check query!');
        }

        /** @var Agent $agent */
        $agent = $request->getAttribute(AgentAction::AGENT_ATTRIBUTE);

        $agent = $this->updateAgent($agent, PActions::GET_HEALTH_CHECK);

        $healthCheckAgent = HealthUtils::checkNeeded($agent);
        if ($healthCheckAgent === false) {
            return $this->error($response, PActions::GET_HEALTH_CHECK, 'No health check available for this agent!');
        }
        $healthCheck = Factory::getHealthCheckFactory()->get($healthCheckAgent->getHealthCheckId());

        DServerLog::log(DServerLog::INFO, 'Received health check task', [$agent, $healthCheck]);

        $hashes = file_get_contents('/tmp/health-check-' . $healthCheck->getId() . '.txt');
        $hashes = explode("\n", $hashes);

        return $this->success($response, PActions::GET_HEALTH_CHECK, [
            PResponseGetHealthCheck::ATTACK            => ' --hash-type=' . $healthCheck->getHashtypeId() . ' ' . $healthCheck->getAttackCmd() . ' ' . $agent->getCmdPars(),
            PResponseGetHealthCheck::CRACKER_BINARY_ID => (int)$healthCheck->getCrackerBinaryId(),
            PResponseGetHealthCheck::HASHES            => $hashes,
            PResponseGetHealthCheck::CHECK_ID          => (int)$healthCheck->getId(),
            PResponseGetHealthCheck::HASHLIST_ALIAS    => SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS),
        ]);
    }
}
