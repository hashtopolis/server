<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PResponseGetFileStatus;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentResponseTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``getFileStatus`` action.
 *
 * Returns the list of files that are pending deletion on the server, so the
 * agent can remove its local copies.  No field validation is needed beyond a
 * valid token, which is enforced by
 * {@see \Hashtopolis\inc\agentapi\auth\TokenAuthMiddleware}.
 */
final class GetFileStatusAction implements AgentAction {
    use AgentResponseTrait;

    public function __invoke(Request $request, Response $response): ResponseInterface {
        /** @var Agent $agent */
        $agent = $request->getAttribute(AgentAction::AGENT_ATTRIBUTE);

        $deleteRequests = Factory::getFileDeleteFactory()->filter([]);
        $files = [];
        foreach ($deleteRequests as $deleteRequest) {
            $files[] = $deleteRequest->getFilename();
        }

        $this->updateAgent($agent, PActions::GET_FILE_STATUS);

        return $this->success($response, PActions::GET_FILE_STATUS, [
            PResponseGetFileStatus::FILENAMES => $files,
        ]);
    }
}
