<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agent\PQueryDownloadBinary;
use Hashtopolis\inc\agent\PResponseBinaryDownload;
use Hashtopolis\inc\agent\PValuesDownloadBinaryType;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentResponseTrait;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\Util;
use Hashtopolis\inc\utils\AccessUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``downloadBinary`` action.
 *
 * Serves download URLs for the various binaries an agent may need
 * (7zr, uftpd, cracker, prince/preprocessor) depending on the requested
 * type.
 */
final class DownloadBinaryAction implements AgentAction {
    use AgentResponseTrait;

    public function __invoke(Request $request, Response $response): ResponseInterface {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        if (!isset($body[PQuery::TOKEN]) || !isset($body[PQueryDownloadBinary::BINARY_TYPE])) {
            return $this->error($response, PActions::DOWNLOAD_BINARY, 'Invalid download query!');
        }

        /** @var Agent $agent */
        $agent = $request->getAttribute(AgentAction::AGENT_ATTRIBUTE);

        $agent = $this->updateAgent($agent, PActions::DOWNLOAD_BINARY);

        switch ($body[PQueryDownloadBinary::BINARY_TYPE]) {
            case PValuesDownloadBinaryType::EXTRACTOR:
                DServerLog::log(DServerLog::TRACE, 'Agent ' . $agent->getId() . ' downloaded 7zr binary');
                $filename = '7zr' . Util::getFileExtension($agent->getOs());
                $path = Util::buildServerUrl() . SConfig::getInstance()->getVal(DConfig::BASE_URL) . '/static/' . $filename;
                return $this->success($response, PActions::DOWNLOAD_BINARY, [
                    PResponseBinaryDownload::EXECUTABLE => $path,
                ]);
            case PValuesDownloadBinaryType::UFTPD:
                DServerLog::log(DServerLog::TRACE, 'Agent ' . $agent->getId() . ' downloaded uftpd binary');
                $filename = 'uftpd' . Util::getFileExtension($agent->getOs());
                $path = Util::buildServerUrl() . SConfig::getInstance()->getVal(DConfig::BASE_URL) . '/static/' . $filename;
                return $this->success($response, PActions::DOWNLOAD_BINARY, [
                    PResponseBinaryDownload::EXECUTABLE => $path,
                ]);
            case PValuesDownloadBinaryType::CRACKER:
                $crackerBinary = Factory::getCrackerBinaryFactory()->get($body[PQueryDownloadBinary::BINARY_VERSION_ID]);
                if ($crackerBinary === null) {
                    return $this->error($response, PActions::DOWNLOAD_BINARY, 'Invalid cracker binary type id!');
                }
                if (!in_array(
                    $crackerBinary->getAccessGroupId(),
                    Util::arrayOfIds(AccessUtils::getAccessGroupsOfAgent($agent))
                )) {
                    return $this->error($response, PActions::DOWNLOAD_BINARY, 'No access to this cracker binary!');
                }
                $crackerBinaryType = Factory::getCrackerBinaryTypeFactory()->get($crackerBinary->getCrackerBinaryTypeId());
                DServerLog::log(DServerLog::TRACE, 'Agent ' . $agent->getId() . ' downloaded cracker binary ' . $crackerBinary->getId());
                $ext = Util::getFileExtension($agent->getOs());
                $url = $crackerBinary->getDownloadUrl();
                // locally stored binaries are downloaded from this server, the download
                // endpoint requires the token of the requesting agent as authentication
                if ($crackerBinary->getFilename() !== null) {
                    $url .= '?token=' . $agent->getToken();
                }
                return $this->success($response, PActions::DOWNLOAD_BINARY, [
                    PResponseBinaryDownload::URL         => $url,
                    PResponseBinaryDownload::NAME        => $crackerBinaryType->getTypeName(),
                    PResponseBinaryDownload::EXECUTABLE  => $crackerBinary->getBinaryName() . $ext,
                ]);
            case PValuesDownloadBinaryType::PRINCE:
            case PValuesDownloadBinaryType::PREPROCESSOR:
                $preprocessor = Factory::getPreprocessorFactory()->get($body[PQueryDownloadBinary::PREPROCESSOR_ID]);
                if ($preprocessor === null) {
                    return $this->error($response, PActions::DOWNLOAD_BINARY, 'Invalid preprocessor id!');
                }
                DServerLog::log(DServerLog::TRACE, 'Agent ' . $agent->getId() . ' downloaded preprocessor ' . $preprocessor->getId());
                $ext = Util::getFileExtension($agent->getOs());
                return $this->success($response, PActions::DOWNLOAD_BINARY, [
                    PResponseBinaryDownload::URL          => $preprocessor->getUrl(),
                    PResponseBinaryDownload::NAME         => $preprocessor->getName(),
                    PResponseBinaryDownload::EXECUTABLE   => $preprocessor->getBinaryName() . $ext,
                    PResponseBinaryDownload::KEYSPACE_CMD => $preprocessor->getKeyspaceCommand(),
                    PResponseBinaryDownload::SKIP_CMD     => $preprocessor->getSkipCommand(),
                    PResponseBinaryDownload::LIMIT_CMD    => $preprocessor->getLimitCommand(),
                ]);
            default:
                DServerLog::log(DServerLog::WARNING, 'Agent ' . $agent->getId() . ' requested invalid binary download: ' . $body[PQueryDownloadBinary::BINARY_TYPE]);
                return $this->error($response, PActions::DOWNLOAD_BINARY, 'Unknown download type!');
        }
    }
}
