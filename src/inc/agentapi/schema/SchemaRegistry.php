<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\schema;

use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agent\PQueryCheckClientVersion;
use Hashtopolis\inc\agent\PQueryClientError;
use Hashtopolis\inc\agent\PQueryDownloadBinary;
use Hashtopolis\inc\agent\PQueryGetChunk;
use Hashtopolis\inc\agent\PQueryGetFile;
use Hashtopolis\inc\agent\PQueryGetFound;
use Hashtopolis\inc\agent\PQueryGetHashlist;
use Hashtopolis\inc\agent\PQueryLogin;
use Hashtopolis\inc\agent\PQueryRegister;
use Hashtopolis\inc\agent\PQuerySendBenchmark;
use Hashtopolis\inc\agent\PQuerySendHealthCheck;
use Hashtopolis\inc\agent\PQuerySendKeyspace;
use Hashtopolis\inc\agent\PQuerySendProgress;
use Hashtopolis\inc\agent\PQueryUpdateInformation;
use Hashtopolis\inc\agent\PResponseBinaryDownload;
use Hashtopolis\inc\agent\PResponseClientUpdate;
use Hashtopolis\inc\agent\PResponseGetChunk;
use Hashtopolis\inc\agent\PResponseGetFile;
use Hashtopolis\inc\agent\PResponseGetFileStatus;
use Hashtopolis\inc\agent\PResponseGetFound;
use Hashtopolis\inc\agent\PResponseGetHashlist;
use Hashtopolis\inc\agent\PResponseGetHealthCheck;
use Hashtopolis\inc\agent\PResponseGetTask;
use Hashtopolis\inc\agent\PResponseLogin;
use Hashtopolis\inc\agent\PResponseRegister;
use Hashtopolis\inc\agent\PResponseSendBenchmark;
use Hashtopolis\inc\agent\PResponseSendKeyspace;
use Hashtopolis\inc\agent\PResponseSendProgress;
use Hashtopolis\inc\agent\PValuesBenchmarkType;
use Hashtopolis\inc\agent\PValuesChunkType;
use Hashtopolis\inc\agent\PValuesDownloadBinaryType;
use Hashtopolis\inc\agent\PValuesUpdateVersion;

/**
 * Static field definitions for every agent API action.
 *
 * Each entry describes the request fields (name, type, required, description)
 * and the success-response fields (name, type, description) for one action.
 * The error response is always the same 3-key envelope
 * ``{action, response:"ERROR", message}`` and is defined by the generator.
 *
 * All field names reference the existing ``PQuery*`` / ``PResponse*``
 * constants so that a rename in the wire-constant classes is automatically
 * reflected here.  The {@see OpenApiSchema} generator reads this registry
 * together with {@see \Hashtopolis\inc\agentapi\common\ActionRegistry} to
 * produce the OpenAPI 3.1 document.
 */
final class SchemaRegistry {
    /** @return array<string, array<string, mixed>> */
    public static function getSchema(): array {
        return [
            PActions::TEST_CONNECTION => [
                'summary' => 'Test the connection between agent and server (no auth required)',
                'request' => [],
                'response' => [],
            ],

            PActions::REGISTER => [
                'summary' => 'Register a new agent using a voucher (no auth required)',
                'request' => [
                    PQueryRegister::VOUCHER    => ['type' => 'string',  'required' => true,  'description' => 'Registration voucher'],
                    PQueryRegister::AGENT_NAME => ['type' => 'string',  'required' => true,  'description' => 'Agent name'],
                    PQueryRegister::CPU_ONLY   => ['type' => 'boolean', 'required' => false, 'description' => 'Whether the agent is CPU-only (no GPU)'],
                ],
                'response' => [
                    PResponseRegister::TOKEN => ['type' => 'string', 'description' => 'Authentication token for subsequent requests'],
                ],
            ],

            PActions::LOGIN => [
                'summary' => 'Validate the token and return server configuration',
                'request' => [
                    PQuery::TOKEN                         => ['type' => 'string', 'required' => true, 'description' => 'Agent authentication token'],
                    PQueryLogin::CLIENT_SIGNATURE         => ['type' => 'string', 'required' => true, 'description' => 'Client signature / version string'],
                ],
                'response' => [
                    PResponseLogin::MULTICAST => ['type' => 'boolean', 'description' => 'Whether multicast is enabled'],
                    PResponseLogin::TIMEOUT   => ['type' => 'integer', 'description' => 'Agent timeout in seconds'],
                    PResponseLogin::VERSION   => ['type' => 'string',  'description' => 'Server version with git commit'],
                ],
            ],

            PActions::UPDATE_CLIENT_INFORMATION => [
                'summary' => 'Update agent hardware information (UID, OS, devices)',
                'request' => [
                    PQuery::TOKEN                           => ['type' => 'string',        'required' => true, 'description' => 'Agent authentication token'],
                    PQueryUpdateInformation::UID             => ['type' => 'string',        'required' => true, 'description' => 'Unique hardware identifier'],
                    PQueryUpdateInformation::OPERATING_SYSTEM => ['type' => 'integer',       'required' => true, 'description' => 'Operating system (0=Linux, 1=Windows, 2=Mac)'],
                    PQueryUpdateInformation::DEVICES         => ['type' => 'array<string>', 'required' => true, 'description' => 'List of GPU/device names'],
                ],
                'response' => [],
            ],

            PActions::CHECK_CLIENT_VERSION => [
                'summary' => 'Check if a newer client binary is available',
                'request' => [
                    PQuery::TOKEN                          => ['type' => 'string', 'required' => true, 'description' => 'Agent authentication token'],
                    PQueryCheckClientVersion::VERSION      => ['type' => 'string', 'required' => true, 'description' => 'Current client version'],
                    PQueryCheckClientVersion::TYPE          => ['type' => 'string', 'required' => true, 'description' => 'Binary type to check'],
                ],
                'response' => [
                    PResponseClientUpdate::VERSION => ['type' => 'string', 'description' => 'Update status', 'enum' => [PValuesUpdateVersion::UP_TO_DATE, PValuesUpdateVersion::NEW_VERSION]],
                    PResponseClientUpdate::URL     => ['type' => 'string', 'description' => 'Download URL (only when version="NEW")'],
                ],
            ],

            PActions::DOWNLOAD_BINARY => [
                'summary' => 'Download a binary (7zr, uftpd, cracker, or preprocessor)',
                'request' => [
                    PQuery::TOKEN                              => ['type' => 'string',  'required' => true,  'description' => 'Agent authentication token'],
                    PQueryDownloadBinary::BINARY_TYPE          => ['type' => 'string',  'required' => true,  'description' => 'Binary type', 'enum' => [PValuesDownloadBinaryType::EXTRACTOR, PValuesDownloadBinaryType::UFTPD, PValuesDownloadBinaryType::CRACKER, PValuesDownloadBinaryType::PRINCE, PValuesDownloadBinaryType::PREPROCESSOR]],
                    PQueryDownloadBinary::BINARY_VERSION_ID    => ['type' => 'integer', 'required' => false, 'description' => 'Cracker binary version ID (for type="cracker")'],
                    PQueryDownloadBinary::PREPROCESSOR_ID      => ['type' => 'integer', 'required' => false, 'description' => 'Preprocessor ID (for type="prince" or "preprocessor")'],
                ],
                'response' => [
                    PResponseBinaryDownload::EXECUTABLE   => ['type' => 'string', 'description' => 'Executable file path/URL'],
                    PResponseBinaryDownload::URL           => ['type' => 'string', 'description' => 'Download URL (cracker/preprocessor types)'],
                    PResponseBinaryDownload::NAME          => ['type' => 'string', 'description' => 'Binary name (cracker/preprocessor types)'],
                    PResponseBinaryDownload::KEYSPACE_CMD  => ['type' => 'string', 'description' => 'Keyspace command (preprocessor types)'],
                    PResponseBinaryDownload::SKIP_CMD      => ['type' => 'string', 'description' => 'Skip command (preprocessor types)'],
                    PResponseBinaryDownload::LIMIT_CMD     => ['type' => 'string', 'description' => 'Limit command (preprocessor types)'],
                ],
            ],

            PActions::CLIENT_ERROR => [
                'summary' => 'Report a hashcat error from the agent',
                'request' => [
                    PQuery::TOKEN               => ['type' => 'string',  'required' => true,  'description' => 'Agent authentication token'],
                    PQueryClientError::TASK_ID  => ['type' => 'integer', 'required' => true,  'description' => 'Task ID the error occurred on'],
                    PQueryClientError::MESSAGE  => ['type' => 'string',  'required' => true,  'description' => 'Error message from hashcat'],
                    PQueryClientError::CHUNK_ID => ['type' => 'integer', 'required' => false, 'description' => 'Chunk ID the error occurred on'],
                ],
                'response' => [],
            ],

            PActions::GET_FILE => [
                'summary' => 'Get the download URL and metadata for a task file',
                'request' => [
                    PQuery::TOKEN          => ['type' => 'string',  'required' => true, 'description' => 'Agent authentication token'],
                    PQueryGetFile::TASK_ID  => ['type' => 'integer', 'required' => true, 'description' => 'Task ID'],
                    PQueryGetFile::FILENAME => ['type' => 'string',  'required' => true, 'description' => 'Filename to retrieve'],
                ],
                'response' => [
                    PResponseGetFile::FILENAME  => ['type' => 'string',  'description' => 'File name'],
                    PResponseGetFile::EXTENSION => ['type' => 'string',  'description' => 'File extension'],
                    PResponseGetFile::URL       => ['type' => 'string',  'description' => 'Download URL'],
                    PResponseGetFile::FILESIZE  => ['type' => 'integer', 'description' => 'File size in bytes'],
                ],
            ],

            PActions::GET_HASHLIST => [
                'summary' => 'Get the download URL for the assigned task\'s hashlist',
                'request' => [
                    PQuery::TOKEN                  => ['type' => 'string',  'required' => true, 'description' => 'Agent authentication token'],
                    PQueryGetHashlist::HASHLIST_ID => ['type' => 'integer', 'required' => true, 'description' => 'Hashlist ID'],
                ],
                'response' => [
                    PResponseGetHashlist::URL => ['type' => 'string', 'description' => 'Hashlist download URL'],
                ],
            ],

            PActions::GET_TASK => [
                'summary' => 'Get the task the agent should work on',
                'request' => [
                    PQuery::TOKEN => ['type' => 'string', 'required' => true, 'description' => 'Agent authentication token'],
                ],
                'response' => [
                    PResponseGetTask::TASK_ID              => ['type' => 'integer', 'description' => 'Task ID, or -1 for health check, or null if no task'],
                    PResponseGetTask::REASON              => ['type' => 'string',  'description' => 'Reason when no task is available (only when taskId is null)'],
                    PResponseGetTask::ATTACK_COMMAND      => ['type' => 'string',  'description' => 'Hashcat attack command'],
                    PResponseGetTask::CMD_PARAMETERS      => ['type' => 'string',  'description' => 'Additional command-line parameters'],
                    PResponseGetTask::HASHLIST_ID         => ['type' => 'integer', 'description' => 'Hashlist ID'],
                    PResponseGetTask::BENCHMARK           => ['type' => 'integer', 'description' => 'Benchmark time in seconds'],
                    PResponseGetTask::STATUS_TIMER        => ['type' => 'integer', 'description' => 'Status report interval in seconds'],
                    PResponseGetTask::FILES              => ['type' => 'array<string>', 'description' => 'List of task file names'],
                    PResponseGetTask::CRACKER_ID          => ['type' => 'integer', 'description' => 'Cracker binary ID'],
                    PResponseGetTask::BENCHTYPE          => ['type' => 'string',  'description' => 'Benchmark type', 'enum' => [PValuesBenchmarkType::SPEED_TEST, PValuesBenchmarkType::RUN_TIME]],
                    PResponseGetTask::HASHLIST_ALIAS      => ['type' => 'string',  'description' => 'Hashlist alias placeholder'],
                    PResponseGetTask::KEYSPACE            => ['type' => 'integer', 'description' => 'Task keyspace'],
                    PResponseGetTask::USE_PREPROCESSOR    => ['type' => 'boolean', 'description' => 'Whether a preprocessor is used'],
                    PResponseGetTask::PREPROCESSOR        => ['type' => 'integer', 'description' => 'Preprocessor ID'],
                    PResponseGetTask::PREPROCESSOR_COMMAND => ['type' => 'string',  'description' => 'Preprocessor command'],
                    PResponseGetTask::ENFORCE_PIPE        => ['type' => 'boolean', 'description' => 'Whether piping is enforced'],
                    PResponseGetTask::SLOW_HASH           => ['type' => 'boolean', 'description' => 'Whether the hash type is slow'],
                    PResponseGetTask::USE_BRAIN           => ['type' => 'boolean', 'description' => 'Whether hashcat brain is used'],
                ],
                'response_variants' => [
                    [
                        'condition' => 'When useBrain is true',
                        'response' => [
                            PResponseGetTask::BRAIN_HOST     => ['type' => 'string',  'description' => 'Hashcat brain host'],
                            PResponseGetTask::BRAIN_PORT     => ['type' => 'integer', 'description' => 'Hashcat brain port'],
                            PResponseGetTask::BRAIN_PASS     => ['type' => 'string',  'description' => 'Hashcat brain password'],
                            PResponseGetTask::BRAIN_FEATURES => ['type' => 'integer', 'description' => 'Hashcat brain features bitmask'],
                        ],
                    ],
                ],
            ],

            PActions::GET_CHUNK => [
                'summary' => 'Get a chunk to work on for an assigned task',
                'request' => [
                    PQuery::TOKEN         => ['type' => 'string',  'required' => true, 'description' => 'Agent authentication token'],
                    PQueryGetChunk::TASK_ID => ['type' => 'integer', 'required' => true, 'description' => 'Task ID'],
                ],
                'response' => [
                    PResponseGetChunk::CHUNK_STATUS    => ['type' => 'string',  'description' => 'Chunk status', 'enum' => [PValuesChunkType::OK, PValuesChunkType::KEYSPACE_REQUIRED, PValuesChunkType::BENCHMARK_REQUIRED, PValuesChunkType::FULLY_DISPATCHED, PValuesChunkType::CRACKER_UPDATE, PValuesChunkType::HEALTH_CHECK]],
                    PResponseGetChunk::CHUNK_ID        => ['type' => 'integer', 'description' => 'Chunk ID (only when status="OK")'],
                    PResponseGetChunk::KEYSPACE_SKIP   => ['type' => 'integer', 'description' => 'Keyspace skip offset (only when status="OK")'],
                    PResponseGetChunk::KEYSPACE_LENGTH => ['type' => 'integer', 'description' => 'Keyspace length (only when status="OK")'],
                ],
            ],

            PActions::SEND_KEYSPACE => [
                'summary' => 'Report the measured keyspace for a task',
                'request' => [
                    PQuery::TOKEN                  => ['type' => 'string',  'required' => true, 'description' => 'Agent authentication token'],
                    PQuerySendKeyspace::TASK_ID    => ['type' => 'integer', 'required' => true, 'description' => 'Task ID'],
                    PQuerySendKeyspace::KEYSPACE   => ['type' => 'integer', 'required' => true, 'description' => 'Measured keyspace size'],
                ],
                'response' => [
                    PResponseSendKeyspace::KEYSPACE => ['type' => 'string', 'description' => 'Confirmation', 'enum' => ['OK']],
                ],
            ],

            PActions::SEND_BENCHMARK => [
                'summary' => 'Report benchmark results for a task',
                'request' => [
                    PQuery::TOKEN                      => ['type' => 'string',  'required' => true, 'description' => 'Agent authentication token'],
                    PQuerySendBenchmark::TASK_ID       => ['type' => 'integer', 'required' => true, 'description' => 'Task ID'],
                    PQuerySendBenchmark::TYPE          => ['type' => 'string',  'required' => true, 'description' => 'Benchmark type', 'enum' => [PValuesBenchmarkType::SPEED_TEST, PValuesBenchmarkType::RUN_TIME]],
                    PQuerySendBenchmark::RESULT        => ['type' => 'string',  'required' => true, 'description' => 'Benchmark result (int:float for speed, int for run)'],
                ],
                'response' => [
                    PResponseSendBenchmark::BENCHMARK => ['type' => 'string', 'description' => 'Confirmation', 'enum' => ['OK']],
                ],
            ],

            PActions::SEND_PROGRESS => [
                'summary' => 'Report chunk progress, cracked hashes, and receive zaps',
                'request' => [
                    PQuery::TOKEN                              => ['type' => 'string',        'required' => true,  'description' => 'Agent authentication token'],
                    PQuerySendProgress::CHUNK_ID               => ['type' => 'integer',       'required' => true,  'description' => 'Chunk ID'],
                    PQuerySendProgress::KEYSPACE_PROGRESS      => ['type' => 'integer',       'required' => true,  'description' => 'Keyspace progress (current position)'],
                    PQuerySendProgress::RELATIVE_PROGRESS      => ['type' => 'integer',      'required' => true,  'description' => 'Relative progress (0-10000)'],
                    PQuerySendProgress::SPEED                  => ['type' => 'integer',       'required' => true,  'description' => 'Cracking speed (H/s)'],
                    PQuerySendProgress::HASHCAT_STATE          => ['type' => 'integer',       'required' => true,  'description' => 'Hashcat state (0=INIT, 2=RUNNING, 4=EXHAUSTED, 5=CRACKED, 6=ABORTED, 7=QUIT, 10=STATUS_ABORTED_RUNTIME)'],
                    PQuerySendProgress::CRACKED_HASHES         => ['type' => 'array<array>', 'required' => true,  'description' => 'Cracked hashes: [[hash, plain, hex_plain, crack_pos], ...]'],
                    PQuerySendProgress::DEBUG_OUTPUT           => ['type' => 'array<string>', 'required' => false, 'description' => 'Debug output lines'],
                    PQuerySendProgress::GPU_TEMP               => ['type' => 'array<integer>', 'required' => false, 'description' => 'GPU temperatures'],
                    PQuerySendProgress::GPU_UTIL               => ['type' => 'array<integer>', 'required' => false, 'description' => 'GPU utilization percentages'],
                    PQuerySendProgress::CPU_UTIL               => ['type' => 'array<integer>', 'required' => false, 'description' => 'CPU utilization percentages'],
                ],
                'response' => [
                    PResponseSendProgress::NUM_CRACKED   => ['type' => 'integer',       'description' => 'Number of hashes cracked in this update'],
                    PResponseSendProgress::NUM_SKIPPED   => ['type' => 'integer',       'description' => 'Number of cracks skipped (not found in DB)'],
                    PResponseSendProgress::HASH_ZAPS     => ['type' => 'array<string>',  'description' => 'Hashes cracked by other agents (for local removal)'],
                    PResponseSendProgress::AGENT_COMMAND => ['type' => 'string',        'description' => 'Agent command (only present when agent should stop)', 'enum' => ['stop']],
                ],
            ],

            PActions::GET_FILE_STATUS => [
                'summary' => 'Get the list of files that should be deleted on the agent',
                'request' => [
                    PQuery::TOKEN => ['type' => 'string', 'required' => true, 'description' => 'Agent authentication token'],
                ],
                'response' => [
                    PResponseGetFileStatus::FILENAMES => ['type' => 'array<string>', 'description' => 'List of filenames to delete'],
                ],
            ],

            PActions::GET_HEALTH_CHECK => [
                'summary' => 'Get a pending health check for the agent',
                'request' => [
                    PQuery::TOKEN => ['type' => 'string', 'required' => true, 'description' => 'Agent authentication token'],
                ],
                'response' => [
                    PResponseGetHealthCheck::ATTACK            => ['type' => 'string',  'description' => 'Attack command for the health check'],
                    PResponseGetHealthCheck::CRACKER_BINARY_ID  => ['type' => 'integer', 'description' => 'Cracker binary ID to use'],
                    PResponseGetHealthCheck::HASHES             => ['type' => 'array<string>', 'description' => 'Hashes for the health check'],
                    PResponseGetHealthCheck::CHECK_ID           => ['type' => 'integer', 'description' => 'Health check ID'],
                    PResponseGetHealthCheck::HASHLIST_ALIAS    => ['type' => 'string',  'description' => 'Hashlist alias placeholder'],
                ],
            ],

            PActions::SEND_HEALTH_CHECK => [
                'summary' => 'Report health check results',
                'request' => [
                    PQuery::TOKEN                        => ['type' => 'string',       'required' => true, 'description' => 'Agent authentication token'],
                    PQuerySendHealthCheck::CHECK_ID       => ['type' => 'integer',      'required' => true, 'description' => 'Health check ID'],
                    PQuerySendHealthCheck::NUM_CRACKED    => ['type' => 'integer',      'required' => true, 'description' => 'Number of hashes cracked'],
                    PQuerySendHealthCheck::NUM_GPUS       => ['type' => 'integer',      'required' => true, 'description' => 'Number of GPUs used'],
                    PQuerySendHealthCheck::ERRORS         => ['type' => 'array<string>', 'required' => true, 'description' => 'Error messages (empty if none)'],
                    PQuerySendHealthCheck::START           => ['type' => 'integer',      'required' => true, 'description' => 'Start timestamp'],
                    PQuerySendHealthCheck::END             => ['type' => 'integer',      'required' => true, 'description' => 'End timestamp'],
                ],
                'response' => [],
            ],

            PActions::GET_FOUND => [
                'summary' => 'Get the download URL for already-cracked hashes',
                'request' => [
                    PQuery::TOKEN               => ['type' => 'string',  'required' => true, 'description' => 'Agent authentication token'],
                    PQueryGetFound::HASHLIST_ID => ['type' => 'integer', 'required' => true, 'description' => 'Hashlist ID'],
                ],
                'response' => [
                    PResponseGetFound::URL => ['type' => 'string', 'description' => 'Found hashes download URL'],
                ],
            ],

            PActions::DEREGISTER => [
                'summary' => 'De-register the agent from the server (gated by allowDeregister config)',
                'request' => [
                    PQuery::TOKEN => ['type' => 'string', 'required' => true, 'description' => 'Agent authentication token'],
                ],
                'response' => [],
            ],
        ];
    }
}
