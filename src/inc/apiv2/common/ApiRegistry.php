<?php

namespace Hashtopolis\inc\apiv2\common;

use Hashtopolis\inc\apiv2\helper\AbortChunkHelperAPI;
use Hashtopolis\inc\apiv2\helper\AssignAgentHelperAPI;
use Hashtopolis\inc\apiv2\helper\BulkSupertaskBuilderHelperAPI;
use Hashtopolis\inc\apiv2\helper\ChangeOwnPasswordHelperAPI;
use Hashtopolis\inc\apiv2\helper\CreateSuperHashlistHelperAPI;
use Hashtopolis\inc\apiv2\helper\CreateSupertaskHelperAPI;
use Hashtopolis\inc\apiv2\helper\CurrentUserHelperAPI;
use Hashtopolis\inc\apiv2\helper\ExportCrackedHashesHelperAPI;
use Hashtopolis\inc\apiv2\helper\ExportLeftHashesHelperAPI;
use Hashtopolis\inc\apiv2\helper\ExportWordlistHelperAPI;
use Hashtopolis\inc\apiv2\helper\GetAccessGroupsHelperAPI;
use Hashtopolis\inc\apiv2\helper\GetAgentBinaryHelperAPI;
use Hashtopolis\inc\apiv2\helper\GetBestTasksAgent;
use Hashtopolis\inc\apiv2\helper\GetCompletedCountHelperAPI;
use Hashtopolis\inc\apiv2\helper\GetCracksOfTaskHelper;
use Hashtopolis\inc\apiv2\helper\GetCracksPerDayHelperAPI;
use Hashtopolis\inc\apiv2\helper\GetFileHelperAPI;
use Hashtopolis\inc\apiv2\helper\GetGlobalConfigHelperAPI;
use Hashtopolis\inc\apiv2\helper\GetTaskProgressImageHelperAPI;
use Hashtopolis\inc\apiv2\helper\GetUserPermissionHelperAPI;
use Hashtopolis\inc\apiv2\helper\ImportCrackedHashesHelperAPI;
use Hashtopolis\inc\apiv2\helper\ImportFileHelperAPI;
use Hashtopolis\inc\apiv2\helper\MaskSupertaskBuilderHelperAPI;
use Hashtopolis\inc\apiv2\helper\PurgeTaskHelperAPI;
use Hashtopolis\inc\apiv2\helper\RebuildChunkCacheHelperAPI;
use Hashtopolis\inc\apiv2\helper\RecountFileLinesHelperAPI;
use Hashtopolis\inc\apiv2\helper\RescanGlobalFilesHelperAPI;
use Hashtopolis\inc\apiv2\helper\ResetChunkHelperAPI;
use Hashtopolis\inc\apiv2\helper\ResetUserPasswordHelperAPI;
use Hashtopolis\inc\apiv2\helper\SearchHashesHelperAPI;
use Hashtopolis\inc\apiv2\helper\SetUserPasswordHelperAPI;
use Hashtopolis\inc\apiv2\helper\UnassignAgentHelperAPI;
use Hashtopolis\inc\apiv2\model\AccessGroupAPI;
use Hashtopolis\inc\apiv2\model\AgentAPI;
use Hashtopolis\inc\apiv2\model\AgentAssignmentAPI;
use Hashtopolis\inc\apiv2\model\AgentBinaryAPI;
use Hashtopolis\inc\apiv2\model\AgentErrorAPI;
use Hashtopolis\inc\apiv2\model\AgentStatAPI;
use Hashtopolis\inc\apiv2\model\ApiTokenAPI;
use Hashtopolis\inc\apiv2\model\ChunkAPI;
use Hashtopolis\inc\apiv2\model\ConfigAPI;
use Hashtopolis\inc\apiv2\model\ConfigSectionAPI;
use Hashtopolis\inc\apiv2\model\CrackerBinaryAPI;
use Hashtopolis\inc\apiv2\model\CrackerBinaryTypeAPI;
use Hashtopolis\inc\apiv2\model\FileAPI;
use Hashtopolis\inc\apiv2\model\GlobalPermissionGroupAPI;
use Hashtopolis\inc\apiv2\model\HashAPI;
use Hashtopolis\inc\apiv2\model\HashlistAPI;
use Hashtopolis\inc\apiv2\model\HashTypeAPI;
use Hashtopolis\inc\apiv2\model\HealthCheckAgentAPI;
use Hashtopolis\inc\apiv2\model\HealthCheckAPI;
use Hashtopolis\inc\apiv2\model\LogEntryAPI;
use Hashtopolis\inc\apiv2\model\NotificationSettingAPI;
use Hashtopolis\inc\apiv2\model\PreprocessorAPI;
use Hashtopolis\inc\apiv2\model\PreTaskAPI;
use Hashtopolis\inc\apiv2\model\SpeedAPI;
use Hashtopolis\inc\apiv2\model\SupertaskAPI;
use Hashtopolis\inc\apiv2\model\TaskAPI;
use Hashtopolis\inc\apiv2\model\TaskWrapperAPI;
use Hashtopolis\inc\apiv2\model\TaskWrapperDisplayAPI;
use Hashtopolis\inc\apiv2\model\UserAPI;
use Hashtopolis\inc\apiv2\model\VoucherAPI;

/**
 * Single source of truth for all APIv2 classes.
 *
 * The order of the class lists is significant: it is the registration order of
 * the Slim routes and therefore determines the order of paths and component
 * schemas in the generated OpenAPI document.
 */
final class ApiRegistry {
  /** @var list<class-string<AbstractModelAPI>> */
  public const MODEL_API_CLASSES = [
    AccessGroupAPI::class,
    AgentAPI::class,
    AgentAssignmentAPI::class,
    AgentBinaryAPI::class,
    AgentErrorAPI::class,
    AgentStatAPI::class,
    ApiTokenAPI::class,
    ChunkAPI::class,
    ConfigAPI::class,
    ConfigSectionAPI::class,
    CrackerBinaryAPI::class,
    CrackerBinaryTypeAPI::class,
    FileAPI::class,
    GlobalPermissionGroupAPI::class,
    HashAPI::class,
    HashlistAPI::class,
    HashTypeAPI::class,
    HealthCheckAgentAPI::class,
    HealthCheckAPI::class,
    LogEntryAPI::class,
    NotificationSettingAPI::class,
    PreprocessorAPI::class,
    PreTaskAPI::class,
    SpeedAPI::class,
    SupertaskAPI::class,
    TaskAPI::class,
    TaskWrapperAPI::class,
    TaskWrapperDisplayAPI::class,
    UserAPI::class,
    VoucherAPI::class,
  ];

  /** @var list<class-string> */
  public const HELPER_API_CLASSES = [
    AbortChunkHelperAPI::class,
    AssignAgentHelperAPI::class,
    BulkSupertaskBuilderHelperAPI::class,
    ChangeOwnPasswordHelperAPI::class,
    CreateSuperHashlistHelperAPI::class,
    CreateSupertaskHelperAPI::class,
    CurrentUserHelperAPI::class,
    ExportCrackedHashesHelperAPI::class,
    ExportLeftHashesHelperAPI::class,
    ExportWordlistHelperAPI::class,
    GetAccessGroupsHelperAPI::class,
    GetAgentBinaryHelperAPI::class,
    GetBestTasksAgent::class,
    GetCompletedCountHelperAPI::class,
    GetCracksOfTaskHelper::class,
    GetCracksPerDayHelperAPI::class,
    GetFileHelperAPI::class,
    GetGlobalConfigHelperAPI::class,
    GetTaskProgressImageHelperAPI::class,
    GetUserPermissionHelperAPI::class,
    ImportCrackedHashesHelperAPI::class,
    ImportFileHelperAPI::class,
    MaskSupertaskBuilderHelperAPI::class,
    PurgeTaskHelperAPI::class,
    RebuildChunkCacheHelperAPI::class,
    RecountFileLinesHelperAPI::class,
    RescanGlobalFilesHelperAPI::class,
    ResetChunkHelperAPI::class,
    ResetUserPasswordHelperAPI::class,
    SearchHashesHelperAPI::class,
    SetUserPasswordHelperAPI::class,
    UnassignAgentHelperAPI::class,
  ];

  /** @return list<class-string> */
  public static function allApiClasses(): array {
    return [...self::MODEL_API_CLASSES, ...self::HELPER_API_CLASSES];
  }
}
