<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Hashtopolis\dba\models\Agent;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentResponseTrait;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\utils\AgentUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``deregister`` action.
 *
 * When the ``ALLOW_DEREGISTER`` config option is enabled, deletes the calling
 * agent (and its associated data) via {@see AgentUtils::delete}.
 * Note that, unlike the other auth actions, this one intentionally does **not**
 * call ``updateAgent()`` — the agent row no longer exists afterwards.
 */
final class DeregisterAction implements AgentAction {
    use AgentResponseTrait;

    public function __invoke(Request $request, Response $response): ResponseInterface {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        if (!isset($body[PQuery::TOKEN])) {
            return $this->error($response, PActions::DEREGISTER, 'Invalid de-registering query!');
        }

        /** @var Agent $agent */
        $agent = $request->getAttribute(AgentAction::AGENT_ATTRIBUTE);

        if (!SConfig::getInstance()->getVal(DConfig::ALLOW_DEREGISTER)) {
            return $this->error($response, PActions::DEREGISTER, 'De-registration is not allowed on this server!');
        }
        try {
            AgentUtils::delete($agent->getId(), null);
        }
        catch (HTException $e) {
            return $this->error($response, PActions::DEREGISTER, 'Error occured during de-registration: ' . $e->getMessage());
        }
        return $this->success($response, PActions::DEREGISTER);
    }
}
