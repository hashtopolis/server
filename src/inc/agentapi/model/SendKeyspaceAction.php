<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agent\PQuerySendKeyspace;
use Hashtopolis\inc\agent\PResponseSendKeyspace;
use Hashtopolis\inc\agent\PValues;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentResponseTrait;
use Hashtopolis\inc\defines\DLogEntry;
use Hashtopolis\inc\defines\DLogEntryIssuer;
use Hashtopolis\inc\defines\DPrince;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\Util;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``sendKeyspace`` action.
 *
 * The agent reports the measured keyspace for a task.  If the task uses a
 * preprocessor and the keyspace overflows (−1), it is clamped to
 * ``PRINCE_KEYSPACE``.  Negative keyspaces from 32-bit servers are rejected.
 * If the task's skip value exceeds the keyspace, the task is deprioritized and
 * all assignments are deleted.
 */
final class SendKeyspaceAction implements AgentAction {
    use AgentResponseTrait;

    public function __invoke(Request $request, Response $response): ResponseInterface {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        if (!isset($body[PQuery::TOKEN]) || !isset($body[PQuerySendKeyspace::KEYSPACE]) || !isset($body[PQuerySendKeyspace::TASK_ID])) {
            return $this->error($response, PActions::SEND_KEYSPACE, 'Invalid keyspace query!');
        }

        /** @var Agent $agent */
        $agent = $request->getAttribute(AgentAction::AGENT_ATTRIBUTE);
        $agent = $this->updateAgent($agent, PActions::SEND_KEYSPACE);

        $keyspace = intval($body[PQuerySendKeyspace::KEYSPACE]);

        $task = Factory::getTaskFactory()->get($body[PQuerySendKeyspace::TASK_ID]);
        if ($task === null) {
            return $this->error($response, PActions::SEND_KEYSPACE, 'Invalid task ID!');
        }

        DServerLog::log(DServerLog::TRACE, 'Agent sending keyspace...', [$agent, $task]);

        $qF1 = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), '=');
        $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), '=');
        $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
        if ($assignment === null) {
            DServerLog::log(DServerLog::TRACE, 'Agent not assigned to task to send keyspace', [$agent]);
            return $this->error($response, PActions::SEND_KEYSPACE, 'You are not assigned to this task!');
        }

        if ($task->getKeyspace() == 0) {
            if ($task->getUsePreprocessor() && $keyspace == -1) {
                DServerLog::log(DServerLog::TRACE, 'Keyspace is too large to save, we set it to a specific number', [$agent]);
                $keyspace = DPrince::PRINCE_KEYSPACE;
            }
            else if ($keyspace < 0) {
                DServerLog::log(DServerLog::WARNING, 'Keyspace is negative, most likely due to 32bit server', [$agent, $keyspace]);
                return $this->error($response, PActions::SEND_KEYSPACE, "Server parsed a negative keyspace, it's very likely that the number was too big to be handled by the server system!");
            }

            $task = Factory::getTaskFactory()->set($task, Task::KEYSPACE, $keyspace);
            DServerLog::log(DServerLog::TRACE, 'Keyspace saved', [$agent, $task]);
        }

        if ($task->getSkipKeyspace() > $task->getKeyspace() && $task->getKeyspace() != DPrince::PRINCE_KEYSPACE) {
            DServerLog::log(DServerLog::ERROR, 'Task skip value is too high, putting task inactive!', [$agent, $task]);
            $task = Factory::getTaskFactory()->set($task, Task::PRIORITY, 0);
            $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), '=');
            Factory::getAssignmentFactory()->massDeletion([Factory::FILTER => $qF]);
            Util::createLogEntry(DLogEntryIssuer::API, $agent->getToken(), DLogEntry::ERROR, 'Task with ID ' . $task->getId() . ' has set a skip value which is too high for its keyspace!');
        }

        return $this->success($response, PActions::SEND_KEYSPACE, [
            PResponseSendKeyspace::KEYSPACE => PValues::OK,
        ]);
    }
}
