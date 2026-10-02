<?php

namespace Hashtopolis\inc\downloadapi;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\utils\CrackerUtils;
use Hashtopolis\inc\utils\DownloadUtils;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Streams the locally stored 7z archive of a cracker binary. Any
 * authenticated principal (agent token or apiv2 JWT) is allowed to download.
 */
final class CrackerBinaryDownloadHandler {
  public function __invoke(Request $request, Response $response, array $args): Response {
    $binary = Factory::getCrackerBinaryFactory()->get((int)$args['id']);
    if ($binary === null || $binary->getFilename() === null) {
      return CrackerBinaryDownloadHandler::notFound($response, 'No such cracker binary archive!');
    }
    $path = CrackerUtils::getCrackersPath() . $binary->getId() . '_' . $binary->getFilename();
    if (!file_exists($path)) {
      return CrackerBinaryDownloadHandler::notFound($response, 'The archive of this cracker binary is not present on the server!');
    }

    $agent = $request->getAttribute(AgentAction::AGENT_ATTRIBUTE);
    if ($agent instanceof Agent) {
      DServerLog::log(DServerLog::TRACE, 'Agent ' . $agent->getId() . ' downloaded the archive of cracker binary ' . $binary->getId());
    }
    else {
      DServerLog::log(DServerLog::TRACE, 'User ' . ($request->getAttribute('userId') ?? 'unknown') .
        ' downloaded the archive of cracker binary ' . $binary->getId());
    }
    return DownloadUtils::startDownload($request, $response, $path, $binary->getFilename());
  }

  private static function notFound(Response $response, string $message): Response {
    $response->getBody()->write($message);
    return $response->withStatus(404)->withHeader('Content-Type', 'text/plain');
  }
}
