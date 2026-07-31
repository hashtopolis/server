<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\common;

use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agentapi\model\LoginAction;
use Hashtopolis\inc\agentapi\model\RegisterAgentAction;
use Hashtopolis\inc\agentapi\model\TestConnectionAction;
use Hashtopolis\inc\api\APICheckClientVersion;
use Hashtopolis\inc\api\APIClientError;
use Hashtopolis\inc\api\APIDeRegisterAgent;
use Hashtopolis\inc\api\APIDownloadBinary;
use Hashtopolis\inc\api\APIGetChunk;
use Hashtopolis\inc\api\APIGetFile;
use Hashtopolis\inc\api\APIGetFileStatus;
use Hashtopolis\inc\api\APIGetFound;
use Hashtopolis\inc\api\APIGetHashlist;
use Hashtopolis\inc\api\APIGetHealthCheck;
use Hashtopolis\inc\api\APIGetTask;
use Hashtopolis\inc\api\APISendBenchmark;
use Hashtopolis\inc\api\APISendHealthCheck;
use Hashtopolis\inc\api\APISendKeyspace;
use Hashtopolis\inc\api\APISendProgress;
use Hashtopolis\inc\api\APIUpdateClientInformation;

/**
 * Maps agent API action strings to their handler class names.
 *
 * Replaces the switch statement in the legacy ``server.php``.  In Phase 1 each
 * entry points at an existing ``src/inc/api/API*.php`` handler that uses
 * ``echo`` + ``die()``.  In Phase 2 these will be replaced by PSR-7 controllers.
 */
final class ActionRegistry {
    /** @var array<string,class-string> */
    private static array $map = [
        PActions::TEST_CONNECTION           => TestConnectionAction::class,
        PActions::REGISTER                  => RegisterAgentAction::class,
        PActions::UPDATE_CLIENT_INFORMATION => APIUpdateClientInformation::class,
        PActions::LOGIN                     => LoginAction::class,
        PActions::CHECK_CLIENT_VERSION      => APICheckClientVersion::class,
        PActions::DOWNLOAD_BINARY           => APIDownloadBinary::class,
        PActions::CLIENT_ERROR              => APIClientError::class,
        PActions::GET_FILE                   => APIGetFile::class,
        PActions::GET_HASHLIST               => APIGetHashlist::class,
        PActions::GET_TASK                   => APIGetTask::class,
        PActions::GET_CHUNK                  => APIGetChunk::class,
        PActions::SEND_KEYSPACE              => APISendKeyspace::class,
        PActions::SEND_BENCHMARK             => APISendBenchmark::class,
        PActions::SEND_PROGRESS              => APISendProgress::class,
        PActions::GET_FILE_STATUS            => APIGetFileStatus::class,
        PActions::GET_HEALTH_CHECK           => APIGetHealthCheck::class,
        PActions::SEND_HEALTH_CHECK          => APISendHealthCheck::class,
        PActions::GET_FOUND                  => APIGetFound::class,
        PActions::DEREGISTER                 => APIDeRegisterAgent::class,
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
