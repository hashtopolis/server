<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Composer\Semver\Comparator;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\AgentBinary;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agent\PQueryCheckClientVersion;
use Hashtopolis\inc\agent\PResponseClientUpdate;
use Hashtopolis\inc\agent\PValuesUpdateVersion;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentResponseTrait;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\Util;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``checkClientVersion`` action.
 *
 * Looks up the ``AgentBinary`` matching the client's binary type and compares
 * its version with the one reported by the agent.  When the server's version
 * is newer the agent is told to download the new binary; otherwise it is told
 * it is up to date.
 */
final class CheckClientVersionAction implements AgentAction {
    use AgentResponseTrait;

    public function __invoke(Request $request, Response $response): ResponseInterface {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        if (!isset($body[PQuery::TOKEN]) || !isset($body[PQueryCheckClientVersion::VERSION]) || !isset($body[PQueryCheckClientVersion::TYPE])) {
            return $this->error($response, PActions::CHECK_CLIENT_VERSION, 'Invalid version check query!');
        }

        /** @var Agent $agent */
        $agent = $request->getAttribute(AgentAction::AGENT_ATTRIBUTE);

        $version = $body[PQueryCheckClientVersion::VERSION];
        $type = $body[PQueryCheckClientVersion::TYPE];

        $qF = new QueryFilter(AgentBinary::BINARY_TYPE, $type, '=');
        $result = Factory::getAgentBinaryFactory()->filter([Factory::FILTER => $qF], true);
        if ($result === null) {
            DServerLog::log(DServerLog::WARNING, 'Agent ' . $agent->getId() . ' sent unknown client type: ' . $type);
            return $this->error($response, PActions::CHECK_CLIENT_VERSION, 'Type not found!');
        }

        $agent = $this->updateAgent($agent, PActions::CHECK_CLIENT_VERSION);
        if (Comparator::greaterThan($result->getVersion(), $version)) {
            DServerLog::log(DServerLog::DEBUG, 'Agent ' . $agent->getId() . ' got notified about client update');
            return $this->success($response, PActions::CHECK_CLIENT_VERSION, [
                PResponseClientUpdate::VERSION => PValuesUpdateVersion::NEW_VERSION,
                PResponseClientUpdate::URL     => Util::buildServerUrl() . SConfig::getInstance()->getVal(DConfig::BASE_URL) . '/agents.php?download=' . $result->getId(),
            ]);
        }
        return $this->success($response, PActions::CHECK_CLIENT_VERSION, [
            PResponseClientUpdate::VERSION => PValuesUpdateVersion::UP_TO_DATE,
        ]);
    }
}
