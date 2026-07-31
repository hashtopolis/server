<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\common;

use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agentapi\model\CheckClientVersionAction;
use Hashtopolis\inc\agentapi\model\ClientErrorAction;
use Hashtopolis\inc\agentapi\model\DeregisterAction;
use Hashtopolis\inc\agentapi\model\DownloadBinaryAction;
use Hashtopolis\inc\agentapi\model\GetFileAction;
use Hashtopolis\inc\agentapi\model\GetFileStatusAction;
use Hashtopolis\inc\agentapi\model\GetFoundAction;
use Hashtopolis\inc\agentapi\model\GetHashlistAction;
use Hashtopolis\inc\agentapi\model\GetHealthCheckAction;
use Hashtopolis\inc\agentapi\model\LoginAction;
use Hashtopolis\inc\agentapi\model\RegisterAgentAction;
use Hashtopolis\inc\agentapi\model\SendBenchmarkAction;
use Hashtopolis\inc\agentapi\model\SendHealthCheckAction;
use Hashtopolis\inc\agentapi\model\SendKeyspaceAction;
use Hashtopolis\inc\agentapi\model\TestConnectionAction;
use Hashtopolis\inc\agentapi\model\UpdateInformationAction;
use Hashtopolis\inc\api\APIGetChunk;
use Hashtopolis\inc\api\APIGetTask;
use Hashtopolis\inc\api\APISendProgress;

/**
 * Maps agent API action strings to their handler class names.
 *
 * Migrated actions point to PSR-7 controllers (implementing {@see AgentAction})
 * that return ``ResponseInterface``.  Un-migrated actions still point to the
 * legacy ``src/inc/api/API*.php`` handlers (which use ``echo`` + ``die()``).
 */
final class ActionRegistry {
    /** @var array<string,class-string> */
    private static array $map = [
        PActions::TEST_CONNECTION           => TestConnectionAction::class,
        PActions::REGISTER                  => RegisterAgentAction::class,
        PActions::UPDATE_CLIENT_INFORMATION => UpdateInformationAction::class,
        PActions::LOGIN                     => LoginAction::class,
        PActions::CHECK_CLIENT_VERSION      => CheckClientVersionAction::class,
        PActions::DOWNLOAD_BINARY           => DownloadBinaryAction::class,
        PActions::CLIENT_ERROR              => ClientErrorAction::class,
        PActions::GET_FILE                   => GetFileAction::class,
        PActions::GET_HASHLIST               => GetHashlistAction::class,
        PActions::GET_TASK                   => APIGetTask::class,
        PActions::GET_CHUNK                  => APIGetChunk::class,
        PActions::SEND_KEYSPACE              => SendKeyspaceAction::class,
        PActions::SEND_BENCHMARK             => SendBenchmarkAction::class,
        PActions::SEND_PROGRESS              => APISendProgress::class,
        PActions::GET_FILE_STATUS            => GetFileStatusAction::class,
        PActions::GET_HEALTH_CHECK           => GetHealthCheckAction::class,
        PActions::SEND_HEALTH_CHECK          => SendHealthCheckAction::class,
        PActions::GET_FOUND                  => GetFoundAction::class,
        PActions::DEREGISTER                 => DeregisterAction::class,
    ];

    /**
     * @return class-string|null  The handler class name, or null if the action
     *                            is unknown / missing.
     */
    public static function getHandler(?string $action): ?string {
        if ($action === null) {
            return null;
        }
        return self::$map[$action] ?? null;
    }

    /** @return list<string>  All registered action strings. */
    public static function getActions(): array {
        return array_keys(self::$map);
    }
}
