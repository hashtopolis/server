<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\models\File;
use Hashtopolis\dba\models\FileTask;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agent\PQueryGetFile;
use Hashtopolis\inc\agent\PResponseGetFile;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentResponseTrait;
use Hashtopolis\inc\defines\DServerLog;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``getFile`` action.
 *
 * Validates that the requested file is attached to the agent's current task
 * and that the agent's trust level is sufficient, then returns the download
 * URL, filename, extension and size for ``getFile.php``.
 */
final class GetFileAction implements AgentAction {
    use AgentResponseTrait;

    public function __invoke(Request $request, Response $response): ResponseInterface {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        if (!isset($body[PQuery::TOKEN]) || !isset($body[PQueryGetFile::TASK_ID]) || !isset($body[PQueryGetFile::FILENAME])) {
            return $this->error($response, PActions::GET_FILE, 'Invalid file query!');
        }

        /** @var Agent $agent */
        $agent = $request->getAttribute(AgentAction::AGENT_ATTRIBUTE);

        DServerLog::log(DServerLog::DEBUG, 'Requesting file ' . $body[PQueryGetFile::FILENAME], [$agent]);

        $task = Factory::getTaskFactory()->get($body[PQueryGetFile::TASK_ID]);
        if ($task === null) {
            return $this->error($response, PActions::GET_FILE, 'Invalid task!');
        }

        $qF1 = new QueryFilter(Assignment::TASK_ID, $task->getId(), '=');
        $qF2 = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), '=');
        $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
        if ($assignment === null) {
            return $this->error($response, PActions::GET_FILE, 'Client is not assigned to this task!');
        }

        $file = $body[PQueryGetFile::FILENAME];
        $qF = new QueryFilter(File::FILENAME, $file, '=');
        $file = Factory::getFileFactory()->filter([Factory::FILTER => $qF], true);
        if ($file === null) {
            return $this->error($response, PActions::GET_FILE, 'Invalid file!');
        }

        $qF1 = new QueryFilter(FileTask::TASK_ID, $task->getId(), '=');
        $qF2 = new QueryFilter(FileTask::FILE_ID, $file->getId(), '=');
        $taskFile = Factory::getFileTaskFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
        if ($taskFile === null) {
            DServerLog::log(DServerLog::WARNING, 'Agent requested file not used in the task!', [$agent, $file, $task]);
            return $this->error($response, PActions::GET_FILE, 'This file is not used for the specified task!');
        }

        if ($agent->getIsTrusted() < $file->getIsSecret()) {
            return $this->error($response, PActions::GET_FILE, 'You have no access to get this file!');
        }
        $filename = $file->getFilename();
        $extension = explode('.', $filename)[sizeof(explode('.', $filename)) - 1];

        $agent = $this->updateAgent($agent, PActions::GET_FILE);

        return $this->success($response, PActions::GET_FILE, [
            PResponseGetFile::FILENAME  => $filename,
            PResponseGetFile::EXTENSION => $extension,
            PResponseGetFile::URL       => 'getFile.php?file=' . $file->getId() . '&token=' . $agent->getToken(),
            PResponseGetFile::FILESIZE  => (int)$file->getSize(),
        ]);
    }
}
