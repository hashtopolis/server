<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\AgentError;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agent\PQueryClientError;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentResponseTrait;
use Hashtopolis\inc\DataSet;
use Hashtopolis\inc\defines\DAgentIgnoreErrors;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DNotificationType;
use Hashtopolis\inc\defines\DPayloadKeys;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\handlers\NotificationHandler;
use Hashtopolis\inc\SConfig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``clientError`` action.
 *
 * Records an error message sent by an agent for the task it is assigned to.
 * Errors matching the ``HC_ERROR_IGNORE`` whitelist are acknowledged but not
 * stored.  Depending on the agent's ``ignoreErrors`` setting the error is
 * persisted (and notifications fired) and/or the agent is deactivated.
 */
final class ClientErrorAction implements AgentAction {
    use AgentResponseTrait;

    public function __invoke(Request $request, Response $response): ResponseInterface {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        if (!isset($body[PQuery::TOKEN]) || !isset($body[PQueryClientError::TASK_ID]) || !isset($body[PQueryClientError::MESSAGE])) {
            return $this->error($response, PActions::CLIENT_ERROR, 'Invalid error query!');
        }

        /** @var Agent $agent */
        $agent = $request->getAttribute(AgentAction::AGENT_ATTRIBUTE);

        $task = Factory::getTaskFactory()->get($body[PQueryClientError::TASK_ID]);
        if ($task === null) {
            DServerLog::log(DServerLog::WARNING, 'Agent ' . $agent->getId() . ' tried to send error for invalid task!');
            return $this->error($response, PActions::CLIENT_ERROR, 'Invalid task!');
        }

        $qF1 = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), '=');
        $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), '=');
        $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
        if ($assignment === null) {
            DServerLog::log(DServerLog::WARNING, 'Agent ' . $agent->getId() . ' tried to send error for task he is not assigned to!');
            return $this->error($response, PActions::CLIENT_ERROR, 'Agent is not assigned to this task!');
        }

        DServerLog::log(DServerLog::INFO, 'Agent ' . $agent->getId() . ' sent error: ' . $body[PQueryClientError::MESSAGE]);
        $whitelist = explode(',', SConfig::getInstance()->getVal(DConfig::HC_ERROR_IGNORE));
        foreach ($whitelist as $w) {
            $w = trim($w);
            if (str_contains($body[PQueryClientError::MESSAGE], $w)) {
                return $this->success($response, PActions::CLIENT_ERROR);
            }
        }

        if ($agent->getIgnoreErrors() <= DAgentIgnoreErrors::IGNORE_SAVE) {
            $chunkId = null;
            if (isset($body[PQueryClientError::CHUNK_ID])) {
                $chunkId = intval($body[PQueryClientError::CHUNK_ID]);
            }
            $error = new AgentError(null, $agent->getId(), $task->getId(), $chunkId, time(), $body[PQueryClientError::MESSAGE]);
            Factory::getAgentErrorFactory()->save($error);

            $payload = new DataSet([DPayloadKeys::AGENT => $agent, DPayloadKeys::AGENT_ERROR => $body[PQueryClientError::MESSAGE]]);
            NotificationHandler::checkNotifications(DNotificationType::AGENT_ERROR, $payload);
            NotificationHandler::checkNotifications(DNotificationType::OWN_AGENT_ERROR, $payload);
        }

        if ($agent->getIgnoreErrors() == DAgentIgnoreErrors::NO) {
            $agent = Factory::getAgentFactory()->set($agent, Agent::IS_ACTIVE, 0);
        }

        $this->updateAgent($agent, PActions::CLIENT_ERROR);
        return $this->success($response, PActions::CLIENT_ERROR);
    }
}
