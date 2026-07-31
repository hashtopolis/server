<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agent\PQueryGetHashlist;
use Hashtopolis\inc\agent\PResponseGetHashlist;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentResponseTrait;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\Util;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``getHashlist`` action.
 *
 * Validates that the requested hashlist is the one used by the agent's
 * current assignment (and that the agent's trust level is sufficient), expands
 * any super-hashlist, then returns the download URL for ``getHashlist.php``.
 */
final class GetHashlistAction implements AgentAction {
    use AgentResponseTrait;

    public function __invoke(Request $request, Response $response): ResponseInterface {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        if (!isset($body[PQuery::TOKEN]) || !isset($body[PQueryGetHashlist::HASHLIST_ID])) {
            return $this->error($response, PActions::GET_HASHLIST, 'Invalid hashlist query!');
        }

        /** @var Agent $agent */
        $agent = $request->getAttribute(AgentAction::AGENT_ATTRIBUTE);

        $hashlist = Factory::getHashlistFactory()->get($body[PQueryGetHashlist::HASHLIST_ID]);
        if ($hashlist === null) {
            return $this->error($response, PActions::GET_HASHLIST, 'Invalid hashlist!');
        }

        DServerLog::log(DServerLog::DEBUG, 'Requesting a hashlist...', [$agent, $hashlist]);

        $qF = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), '=');
        $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF], true);
        if ($assignment === null) {
            return $this->error($response, PActions::GET_HASHLIST, 'Agent is not assigned to a task!');
        }

        $task = Factory::getTaskFactory()->get($assignment->getTaskId());
        if ($task === null) {
            DServerLog::log(DServerLog::WARNING, 'Assignment contained invalid task!', [$agent, $assignment]);
            return $this->error($response, PActions::GET_HASHLIST, 'Assignment contains invalid task!');
        }

        $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
        if ($taskWrapper === null) {
            DServerLog::log(DServerLog::FATAL, 'Inconsistency between taskWrapper and tasks!', [$agent, $task]);
            return $this->error($response, PActions::GET_HASHLIST, 'Inconsistent taskWrapper for task!');
        }

        if ($taskWrapper->getHashlistId() != $hashlist->getId()) {
            DServerLog::log(DServerLog::WARNING, 'Agent requested hashlist not used for task!', [$agent, $taskWrapper, $task, $hashlist]);
            return $this->error($response, PActions::GET_HASHLIST, 'This hashlist is not used for the assigned task!');
        }
        elseif ($agent->getIsTrusted() < $hashlist->getIsSecret()) {
            return $this->error($response, PActions::GET_HASHLIST, 'You have not access to this hashlist!');
        }

        $hashlists = Util::checkSuperHashlist($hashlist);
        foreach ($hashlists as $subHashlist) {
            if ($subHashlist->getIsSecret() > $agent->getIsTrusted()) {
                return $this->error($response, PActions::GET_HASHLIST, 'Agent would require to download secret hashlist with insufficient level!');
            }
        }

        $agent = $this->updateAgent($agent, PActions::GET_HASHLIST);

        if (sizeof($hashlists) == 0) {
            return $this->error($response, PActions::GET_HASHLIST, 'No hashlists selected/available!');
        }
        return $this->success($response, PActions::GET_HASHLIST, [
            PResponseGetHashlist::URL => 'getHashlist.php?hashlists=' . implode(',', Util::arrayOfIds($hashlists)) . '&token=' . $agent->getToken(),
        ]);
    }
}
