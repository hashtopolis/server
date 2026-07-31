<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agent\PQueryGetFound;
use Hashtopolis\inc\agent\PResponseGetFound;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentResponseTrait;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\Util;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``getFound`` action.
 *
 * Mirrors {@see \Hashtopolis\inc\agentapi\model\GetHashlistAction} but returns
 * the download URL for the list of already-cracked hashes (``getFound.php``)
 * rather than the full hashlist.
 */
final class GetFoundAction implements AgentAction {
    use AgentResponseTrait;

    public function __invoke(Request $request, Response $response): ResponseInterface {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        if (!isset($body[PQuery::TOKEN]) || !isset($body[PQueryGetFound::HASHLIST_ID])) {
            return $this->error($response, PActions::GET_FOUND, 'Invalid found query!');
        }

        /** @var Agent $agent */
        $agent = $request->getAttribute(AgentAction::AGENT_ATTRIBUTE);

        $hashlist = Factory::getHashlistFactory()->get($body[PQueryGetFound::HASHLIST_ID]);
        if ($hashlist === null) {
            return $this->error($response, PActions::GET_FOUND, 'Invalid hashlist!');
        }

        DServerLog::log(DServerLog::DEBUG, 'Requesting founds for hashlist...', [$agent, $hashlist]);

        $qF = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), '=');
        $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF], true);
        if ($assignment === null) {
            return $this->error($response, PActions::GET_FOUND, 'Agent is not assigned to a task!');
        }

        $task = Factory::getTaskFactory()->get($assignment->getTaskId());
        if ($task === null) {
            DServerLog::log(DServerLog::WARNING, 'Assignment contained invalid task!', [$agent, $assignment]);
            return $this->error($response, PActions::GET_FOUND, 'Assignment contains invalid task!');
        }

        $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
        if ($taskWrapper === null) {
            DServerLog::log(DServerLog::FATAL, 'Inconsistency between taskWrapper and tasks!', [$agent, $task]);
            return $this->error($response, PActions::GET_FOUND, 'Inconsistent taskWrapper for task!');
        }

        if ($taskWrapper->getHashlistId() != $hashlist->getId()) {
            DServerLog::log(DServerLog::WARNING, 'Agent requested hashlist not used for task!', [$agent, $taskWrapper, $task, $hashlist]);
            return $this->error($response, PActions::GET_FOUND, 'This hashlist is not used for the assigned task!');
        }
        elseif ($agent->getIsTrusted() < $hashlist->getIsSecret()) {
            return $this->error($response, PActions::GET_FOUND, 'You have not access to this hashlist!');
        }

        $hashlists = Util::checkSuperHashlist($hashlist);
        foreach ($hashlists as $subHashlist) {
            if ($subHashlist->getIsSecret() > $agent->getIsTrusted()) {
                return $this->error($response, PActions::GET_FOUND, 'Agent would require to download secret hashlist with insufficient level!');
            }
        }

        $agent = $this->updateAgent($agent, PActions::GET_FOUND);

        if (sizeof($hashlists) == 0) {
            return $this->error($response, PActions::GET_FOUND, 'No hashlists selected/available!');
        }
        return $this->success($response, PActions::GET_FOUND, [
            PResponseGetFound::URL => 'getFound.php?hashlists=' . implode(',', Util::arrayOfIds($hashlists)) . '&token=' . $agent->getToken(),
        ]);
    }
}
