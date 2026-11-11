<?php

use Hashtopolis\inc\defines\DAgentIgnoreErrors;
use Hashtopolis\inc\defines\DAgentStatsType;
use Hashtopolis\inc\defines\DBackgroundJobStatus;
use Hashtopolis\inc\defines\DBackgroundJobType;
use Hashtopolis\inc\defines\DFileDownloadStatus;
use Hashtopolis\inc\defines\DFileType;
use Hashtopolis\inc\defines\DHashcatStatus;
use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\inc\defines\DHealthCheckAgentStatus;
use Hashtopolis\inc\defines\DHealthCheckMode;
use Hashtopolis\inc\defines\DHealthCheckStatus;
use Hashtopolis\inc\defines\DLogEntry;
use Hashtopolis\inc\defines\DLogEntryIssuer;
use Hashtopolis\inc\defines\DNotificationType;
use Hashtopolis\inc\defines\DOperatingSystem;
use Hashtopolis\inc\defines\DTaskStaticChunking;
use Hashtopolis\inc\defines\DTaskTypes;
use Hashtopolis\inc\defines\UQueryHashlist;

$CONF = array();

require_once(dirname(__FILE__) . "/../../inc/defines/DAgentIgnoreErrors.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DAgentStatsType.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DBackgroundJobStatus.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DBackgroundJobType.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DFileDownloadStatus.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DFileType.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DHashcatStatus.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DHashlistFormat.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DHealthCheckAgentStatus.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DHealthCheckMode.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DHealthCheckStatus.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DLogEntry.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DLogEntryIssuer.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DNotificationType.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DOperatingSystem.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DTaskStaticChunking.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DTaskTypes.php");
require_once(dirname(__FILE__) . "/../../inc/defines/UQuery.php");
require_once(dirname(__FILE__) . "/../../inc/defines/UQueryHashlist.php");

//
// Field choice declarations
//
// A choice list pairs every value a column accepts with a description of what
// that value means. Both reach APIv2: the values are what request validation
// accepts (AbstractBaseAPI::validateData) and the enum of the generated
// OpenAPI schema, the descriptions are what the API reference shows for each
// of them. They are therefore written for a reader of the API documentation,
// not as a restatement of the constant name.
//
// makeChoices() reads the name of the constant each value comes from out of
// the class declaring it, so the spec can name the values without a second
// hand-written list that could drift away from the first.
//

/**
 * Write a value as a PHP double quoted string literal for the generated model.
 *
 * Choice descriptions and column descriptions are prose, so they may well hold
 * a quote, a backslash or a dollar sign, none of which may reach the generated
 * file unescaped.
 */
function phpStringLiteral(string $value): string {
  if (str_contains($value, "\n") || str_contains($value, "\r")) {
    fwrite(STDERR, "Descriptions are written on one line, this one is not: '$value'\n");
    exit(1);
  }
  return '"' . addcslashes($value, "\"\\$") . '"';
}

/**
 * Build the choice declaration of a column from the class holding its values.
 *
 * @param string $class the D* class declaring the values as constants
 * @param array<int|string, string> $labels value => what that value means
 * @return array<int, array{key: int|string, label: string, name: string}>
 */
function makeChoices(string $class, array $labels): array {
  $names = [];
  foreach ((new ReflectionClass($class))->getConstants() as $name => $value) {
    if (is_int($value) || is_string($value)) {
      $names[$value] = $name;
    }
  }
  $choices = [];
  foreach ($labels as $key => $label) {
    if (!array_key_exists($key, $names)) {
      fwrite(STDERR, "No constant of $class holds the value '$key'\n");
      exit(1);
    }
    $choices[] = ['key' => $key, 'label' => $label, 'name' => $names[$key]];
  }
  return $choices;
}

$FieldIgnoreErrorsChoices = makeChoices(DAgentIgnoreErrors::class, [
  DAgentIgnoreErrors::NO => 'Deactivate the agent when it reports an error',
  DAgentIgnoreErrors::IGNORE_SAVE => 'Keep the agent running and record the errors it reports',
  DAgentIgnoreErrors::IGNORE_NOSAVE => 'Keep the agent running and discard the errors it reports',
]);

$FieldTaskTypeChoices = makeChoices(DTaskTypes::class, [
  DTaskTypes::NORMAL => 'A single task',
  DTaskTypes::SUPERTASK => 'A supertask, a sequence of tasks that run as one unit',
]);

$FieldHashlistFormatChoices = makeChoices(DHashlistFormat::class, [
  DHashlistFormat::PLAIN => 'Plaintext hashes, read as hash[:salt]:plaintext',
  DHashlistFormat::WPA => 'A WPA/WPA2 handshake, as an hccapx or PMKID capture, read as essid:plaintext',
  DHashlistFormat::BINARY => 'A single hash held in a binary file, cracked to plaintext',
  DHashlistFormat::SUPERHASHLIST => 'A superhashlist, whose hashes are those of the hashlists it is composed of',
]);

$FieldOperatingSystemChoices = makeChoices(DOperatingSystem::class, [
  DOperatingSystem::LINUX => 'Linux',
  DOperatingSystem::WINDOWS => 'Windows',
  DOperatingSystem::OSX => 'macOS',
]);

$FieldAgentStatTypeChoices = makeChoices(DAgentStatsType::class, [
  DAgentStatsType::GPU_TEMP => 'Device temperature in degrees Celsius, one value per device',
  DAgentStatsType::GPU_UTIL => 'Device utilisation in percent, one value per device',
  DAgentStatsType::CPU_UTIL => 'Utilisation of the host CPU in percent',
]);

$FieldHashcatStatusChoices = makeChoices(DHashcatStatus::class, [
  DHashcatStatus::INIT => 'Starting up',
  DHashcatStatus::AUTOTUNE => 'Tuning itself to the devices of the agent',
  DHashcatStatus::RUNNING => 'Working through its keyspace',
  DHashcatStatus::PAUSED => 'Paused',
  DHashcatStatus::EXHAUSTED => 'Finished its keyspace without cracking every hash',
  DHashcatStatus::CRACKED => 'Finished because every hash of the hashlist was cracked',
  DHashcatStatus::ABORTED => 'Aborted',
  DHashcatStatus::QUIT => 'Stopped on request',
  DHashcatStatus::BYPASS => 'Skipped because the task was bypassed',
  DHashcatStatus::ABORTED_CHECKPOINT => 'Aborted at a checkpoint, so it can resume from there',
  DHashcatStatus::STATUS_ABORTED_RUNTIME => 'Aborted after reaching its runtime limit',
]);

$FieldFileTypeChoices = makeChoices(DFileType::class, [
  DFileType::WORDLIST => 'A wordlist',
  DFileType::RULE => 'A rule file',
  DFileType::OTHER => 'Any other file an attack needs, such as a mask or charset file',
  DFileType::TEMPORARY => 'A temporary file, written by the server and removed again afterwards',
]);

$FieldFileDownloadStatusChoices = makeChoices(DFileDownloadStatus::class, [
  DFileDownloadStatus::DELETED => 'The file was deleted again',
  DFileDownloadStatus::FAILED => 'The download failed',
  DFileDownloadStatus::PENDING => 'The download has not finished yet',
  DFileDownloadStatus::DONE => 'The file was downloaded',
]);

$FieldHealthCheckStatusChoices = makeChoices(DHealthCheckStatus::class, [
  DHealthCheckStatus::ABORTED => 'Abandoned before every agent had finished it',
  DHealthCheckStatus::PENDING => 'Waiting for agents to finish it',
  DHealthCheckStatus::COMPLETED => 'Every assigned agent finished it',
]);

$FieldHealthCheckModeChoices = makeChoices(DHealthCheckMode::class, [
  DHealthCheckMode::MD5 => 'Crack MD5 hashes, a fast hash that exercises the whole device',
  DHealthCheckMode::BCRYPT => 'Crack bcrypt hashes, a slow hash that exercises the device under load',
]);

$FieldHealthCheckAgentStatusChoices = makeChoices(DHealthCheckAgentStatus::class, [
  DHealthCheckAgentStatus::FAILED => 'The agent did not return the expected cracks',
  DHealthCheckAgentStatus::PENDING => 'The agent has not reported a result yet',
  DHealthCheckAgentStatus::COMPLETED => 'The agent returned the expected cracks',
]);

$FieldBackgroundJobStatusChoices = makeChoices(DBackgroundJobStatus::class, [
  DBackgroundJobStatus::FAILED => 'The job failed; resultMessage says why',
  DBackgroundJobStatus::PENDING => 'Queued, not picked up yet',
  DBackgroundJobStatus::RUNNING => 'Being worked on',
  DBackgroundJobStatus::DONE => 'Finished successfully',
]);

$FieldBackgroundJobTypeChoices = makeChoices(DBackgroundJobType::class, [
  DBackgroundJobType::RECOUNT_FILE => 'Count the lines of a wordlist or rule file again',
  DBackgroundJobType::SCAN_CRACKER => 'Scan a cracker binary for the hash types it supports',
  DBackgroundJobType::CHECKSUM_FILE => 'Compute the checksum of a file',
]);

$FieldTaskStaticChunkingChoices = makeChoices(DTaskStaticChunking::class, [
  DTaskStaticChunking::NORMAL => 'Size every chunk from the benchmark of the agent and the chunk time of the task',
  DTaskStaticChunking::CHUNK_SIZE => 'Give every chunk the fixed size held in chunkSize',
  DTaskStaticChunking::NUM_CHUNKS => 'Split the keyspace into the number of equally sized chunks held in chunkSize',
]);

$FieldLogEntryIssuerChoices = makeChoices(DLogEntryIssuer::class, [
  DLogEntryIssuer::API => 'Written while serving an API request',
  DLogEntryIssuer::USER => 'Written while serving a user of the web interface',
]);

$FieldLogEntryLevelChoices = makeChoices(DLogEntry::class, [
  DLogEntry::INFO => 'Something worth recording happened',
  DLogEntry::WARN => 'Something unexpected happened that the server could carry on from',
  DLogEntry::ERROR => 'An operation failed',
  DLogEntry::FATAL => 'An operation failed in a way that needs an administrator to look at it',
]);

$FieldNotificationTypeChoices = makeChoices(DNotificationType::class, [
  DNotificationType::TASK_COMPLETE => 'A task finished',
  DNotificationType::AGENT_ERROR => 'Any agent reported an error',
  DNotificationType::OWN_AGENT_ERROR => 'An agent owned by the receiving user reported an error',
  DNotificationType::LOG_ERROR => 'An error was written to the server log',
  DNotificationType::NEW_TASK => 'A task was created',
  DNotificationType::NEW_HASHLIST => 'A hashlist was created',
  DNotificationType::HASHLIST_ALL_CRACKED => 'Every hash of a hashlist was cracked',
  DNotificationType::HASHLIST_CRACKED_HASH => 'A hash of a hashlist was cracked',
  DNotificationType::USER_CREATED => 'A user was created',
  DNotificationType::USER_DELETED => 'A user was deleted',
  DNotificationType::USER_LOGIN_FAILED => 'A login attempt failed',
  DNotificationType::LOG_WARN => 'A warning was written to the server log',
  DNotificationType::LOG_FATAL => 'A fatal error was written to the server log',
  DNotificationType::NEW_AGENT => 'An agent registered itself',
  DNotificationType::DELETE_TASK => 'A task was deleted',
  DNotificationType::DELETE_HASHLIST => 'A hashlist was deleted',
  DNotificationType::DELETE_AGENT => 'An agent was deleted',
]);

// Type: describes what kind of type the attribute is
// Subtype: in case type is dict, the first level of the dict will also be checked.
// Read_only: determines that the attribute can only be set during creation
// Protected: cannot be set at any point but can be returned. For example 'id'
// Private: cannot be set at any point, and can also not be returned. For example: passwordhash
// Pk: primary key
// Null: means that an attribute is optional.
// Alias: used for setting new names for attributes. This is for the transition between APIv1 and APIv2.
//        Alias determines the name for the attribute as it should be called by APIv2
// Sort_expression: optional SQL expression used instead of the raw column when sorting/paginating on it, '%s' is
//        replaced with the column. Must be supported by all database types.
// Choices: optional list of the values the attribute accepts, each with a description of what it means, built
//        with makeChoices() above. APIv2 rejects every other value and documents the set as the enum of the
//        attribute, the descriptions becoming the documentation of the individual values.
// Description: optional prose explaining the attribute, shown by the API reference. Written only where the
//        meaning is not already evident from the alias, and kept to a single line.

//
// Entities
//
$CONF['AccessGroup'] = [
  'columns' => [
    ['name' => 'accessGroupId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'groupName', 'read_only' => False, 'type' => 'str(50)'],
  ],
];
$CONF['Agent'] = [
  'columns' => [
    ['name' => 'agentId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'agentName', 'read_only' => False, 'type' => 'str(100)'],
    ['name' => 'uid', 'read_only' => False, 'type' => 'str(100)',
      'description' => 'Identifier the agent reports for the machine it runs on, which the server uses to recognise it again.'],
    ['name' => 'os', 'read_only' => False, 'type' => 'int', 'choices' => $FieldOperatingSystemChoices,
      'description' => 'Operating system the agent reported for the machine it runs on.'],
    ['name' => 'devices', 'read_only' => True, 'type' => 'str(65535)',
      'description' => 'The compute devices the agent last reported, one per line.'],
    ['name' => 'cmdPars', 'read_only' => False, 'type' => 'str(65535)',
      'description' => 'Extra cracker arguments added to every task this agent runs.'],
    ['name' => 'ignoreErrors', 'read_only' => False, 'type' => 'int', 'choices' => $FieldIgnoreErrorsChoices,
      'description' => 'What the server does when this agent reports an error.'],
    ['name' => 'isActive', 'read_only' => False, 'type' => 'bool',
      'description' => 'Whether the agent is allowed to pick up work.'],
    ['name' => 'isTrusted', 'read_only' => False, 'type' => 'bool',
      'description' => 'Whether the agent may receive secret hashlists and secret files.'],
    ['name' => 'token', 'read_only' => True, 'type' => 'str(30)',
      'description' => 'The credential the agent authenticates its protocol requests with.'],
    ['name' => 'lastAct', 'read_only' => True, 'type' => 'str(50)', 'protected' => True,
      'description' => 'The protocol action the agent performed last.'],
    ['name' => 'lastTime', 'read_only' => True, 'type' => 'int64', 'protected' => True,
      'description' => 'When the agent was last heard from, as a unix timestamp.'],
    ['name' => 'lastIp', 'read_only' => True, 'type' => 'str(50)', 'protected' => True,
      'description' => 'Address the agent was last seen from.'],
    ['name' => 'userId', 'read_only' => False, 'type' => 'int', 'null' => True, 'relation' => 'User'],
    ['name' => 'cpuOnly', 'read_only' => False, 'type' => 'bool',
      'description' => 'Whether the agent reported no usable GPU, so that it only takes CPU tasks.'],
    ['name' => 'clientSignature', 'read_only' => True, 'type' => 'str(50)',
      'description' => 'Name and version the agent software identifies itself with, for example generic-python.'],
  ],
];
$CONF['AgentBinary'] = [
  'columns' => [
    ['name' => 'agentBinaryId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'binaryType', 'read_only' => False, 'type' => 'str(20)',
      'description' => 'Which agent implementation this binary is, for example python. Also the first path segment the update is fetched from.'],
    ['name' => 'version', 'read_only' => False, 'type' => 'str(20)',
      'description' => 'Version of the agent this binary holds, compared against the upstream archive to find updates.'],
    ['name' => 'operatingSystems', 'read_only' => False, 'type' => 'str(50)',
      'description' => 'Free text listing the operating systems the binary runs on, shown to whoever downloads it.'],
    ['name' => 'filename', 'read_only' => False, 'type' => 'str(50)',
      'description' => 'Name of the archive under the bin directory of the server that agents download.'],
    // no choice list: the track is a directory of the upstream agent archive (see
    // AgentBinaryUtils::executeUpgrade), so the set of valid values belongs to that server rather than to
    // this one, and it has changed before - the 'dev' track was retired in v0.13.0
    ['name' => 'updateTrack', 'read_only' => False, 'type' => 'str(20)',
      'description' => 'Which upstream release track to take updates from, as a directory of the agent archive. The web interface offers release and stable.'],
    ['name' => 'updateAvailable', 'read_only' => True, 'type' => 'str(20)', 'protected' => True,
      'description' => 'Version the update check last found upstream, or empty when the binary is up to date.'],
  ],
];
$CONF['AgentError'] = [
  'columns' => [
    ['name' => 'agentErrorId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'agentId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'relation' => 'Agent'],
    ['name' => 'taskId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'relation' => 'Task'],
    // an agent error is not necessarily tied to a chunk
    ['name' => 'chunkId', 'read_only' => True, 'null' => True, 'type' => 'int', 'protected' => True, 'relation' => 'Chunk'],
    ['name' => 'time', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'error', 'read_only' => True, 'type' => 'str(65535)', 'protected' => True],
  ],
];
$CONF['AgentStat'] = [
  'columns' => [
    ['name' => 'agentStatId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'agentId', 'read_only' => True, 'protected' => True, 'type' => 'int', 'relation' => 'Agent'],
    ['name' => 'statType', 'read_only' => True, 'protected' => True, 'type' => 'int', 'choices' => $FieldAgentStatTypeChoices,
      'description' => 'What the values of this measurement are.'],
    ['name' => 'time', 'read_only' => True, 'protected' => True, 'type' => 'int64'],
    ['name' => 'value', 'read_only' => True, 'protected' => True, 'type' => 'array', 'subtype' => 'int'],
  ],
];
$CONF['AgentZap'] = [
  'columns' => [
    ['name' => 'agentZapId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'agentId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'relation' => 'Agent'],
    ['name' => 'lastZapId', 'read_only' => True, 'type' => 'int', 'null' => True, 'protected' => True],
  ],
];
$CONF['ApiKey'] = [
  'columns' => [
    ['name' => 'apiKeyId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'startValid', 'read_only' => False, 'type' => 'int64'],
    ['name' => 'endValid', 'read_only' => False, 'type' => 'int64'],
    ['name' => 'accessKey', 'read_only' => True, 'type' => 'str(256)', 'protected' => True],
    ['name' => 'accessCount', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'userId', 'read_only' => False, 'type' => 'int', 'relation' => 'User'],
    ['name' => 'apiGroupId', 'read_only' => False, 'type' => 'int', 'relation' => 'ApiGroup'],
  ],
];
$CONF['ApiGroup'] = [
  'columns' => [
    ['name' => 'apiGroupId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'permissions', 'read_only' => False, 'type' => 'str(65535)'],
    ['name' => 'name', 'read_only' => False, 'type' => 'str(100)'],
  ],
];
$CONF['Assignment'] = [
  'permission_alias' => 'AgentAssignment',
  'columns' => [
    ['name' => 'assignmentId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'taskId', 'read_only' => True, 'type' => 'int', 'relation' => 'Task'],
    ['name' => 'agentId', 'read_only' => True, 'type' => 'int', 'relation' => 'Agent'],
    ['name' => 'benchmark', 'read_only' => False, 'type' => 'str(50)'],
  ],
];
$CONF['Chunk'] = [
  'columns' => [
    ['name' => 'chunkId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'taskId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'relation' => 'Task'],
    ['name' => 'skip', 'read_only' => True, 'type' => 'uint64', 'protected' => True,
      'description' => 'Offset into the keyspace of the task at which this chunk starts.'],
    ['name' => 'length', 'read_only' => True, 'type' => 'uint64', 'protected' => True,
      'description' => 'How much of the keyspace this chunk covers.'],
    ['name' => 'agentId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'relation' => 'Agent', 'null' => True],
    ['name' => 'dispatchTime', 'read_only' => True, 'type' => 'int64', 'protected' => True,
      'description' => 'When the chunk was handed to an agent, as a unix timestamp.'],
    ['name' => 'solveTime', 'read_only' => True, 'type' => 'int64', 'protected' => True,
      'description' => 'When the agent last reported on this chunk, as a unix timestamp.'],
    ['name' => 'checkpoint', 'read_only' => True, 'type' => 'int64', 'protected' => True,
      'description' => 'Keyspace offset of the last checkpoint, from which the chunk can be resumed.'],
    ['name' => 'progress', 'read_only' => True, 'type' => 'int', 'protected' => True,
      'description' => 'How far the chunk has got, in hundredths of a percent, so that 10000 means finished.'],
    ['name' => 'state', 'read_only' => True, 'type' => 'int', 'protected' => True, 'choices' => $FieldHashcatStatusChoices,
      'description' => 'The state the cracker reported for this chunk, as a hashcat status value.'],
    ['name' => 'cracked', 'read_only' => True, 'type' => 'int', 'protected' => True,
      'description' => 'Number of hashes cracked while working on this chunk.'],
    ['name' => 'speed', 'read_only' => True, 'type' => 'int64', 'protected' => True,
      'description' => 'Candidates per second the agent last reported for this chunk.'],
  ],
];
$CONF['Config'] = [
  'columns' => [
    ['name' => 'configId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'configSectionId', 'read_only' => True, 'type' => 'int', 'relation' => 'ConfigSection'],
    ['name' => 'item', 'read_only' => False, 'type' => 'str(128)'],
    ['name' => 'value', 'read_only' => False, 'type' => 'str(65535)'],
  ],
];
$CONF['ConfigSection'] = [
  'columns' => [
    ['name' => 'configSectionId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'sectionName', 'read_only' => False, 'type' => 'str(100)'],
  ],
];
$CONF['CrackerBinary'] = [
  'columns' => [
    ['name' => 'crackerBinaryId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'crackerBinaryTypeId', 'read_only' => True, 'type' => 'int', 'relation' => 'CrackerBinaryType'],
    ['name' => 'version', 'read_only' => False, 'type' => 'str(20)',
      'description' => 'Version of the cracker this binary holds.'],
    ['name' => 'downloadUrl', 'read_only' => False, 'null' => True, 'type' => 'str(255)',
      'description' => 'External http or https url the agent downloads the binary archive from. The server keeps a local copy for later analysis: it is fetched on creation, and changing the url fetches it again, the change being rejected when the download fails or the archive is not a valid 7z file. Mutually exclusive with sourceType, which points this url at the download endpoint of this server and then makes it unchangeable.'],
    ['name' => 'binaryName', 'read_only' => False, 'type' => 'str(50)',
      'description' => 'Name of the executable inside the archive that the agent runs.'],
    // archive filename of a server-hosted binary; NULL means the binary is downloaded from downloadUrl
    ['name' => 'filename', 'read_only' => True, 'null' => True, 'type' => 'str(100)', 'protected' => True,
      'description' => 'Name of the locally stored 7z archive, null while the binary is downloaded from downloadUrl. Cannot be set.'],
    // access group the binary belongs to, required on creation and patchable with
    // membership validation of the current and the new group
    ['name' => 'accessGroupId', 'read_only' => False, 'type' => 'int', 'relation' => 'AccessGroup',
      'description' => 'Access group this cracker binary belongs to. Required on creation, where the requesting user must be a member, and changeable only by a user who belongs to both the current and the new group.'],
  ],
];
$CONF['CrackerBinaryType'] = [
  'columns' => [
    ['name' => 'crackerBinaryTypeId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'typeName', 'read_only' => False, 'type' => 'str(30)'],
    ['name' => 'isChunkingAvailable', 'read_only' => False, 'null' => True, 'type' => 'bool'],
  ],
];
// n-m association between cracker binaries and hashtypes: a binary only
// supports the hashtypes it is linked to
$CONF['CrackerBinaryHashtype'] = [
  'columns' => [
    ['name' => 'crackerBinaryHashtypeId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'crackerBinaryId', 'read_only' => True, 'type' => 'int', 'relation' => 'CrackerBinary'],
    ['name' => 'hashTypeId', 'read_only' => True, 'type' => 'int', 'relation' => 'HashType'],
  ],
];
$CONF['File'] = [
  'columns' => [
    ['name' => 'fileId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'filename', 'read_only' => False, 'type' => 'str(100)',
      'description' => 'Name of the file as it is stored on the server and handed to the agents.'],
    ['name' => 'size', 'read_only' => True, 'type' => 'int64', 'protected' => True,
      'description' => 'Size of the file in bytes.'],
    ['name' => 'isSecret', 'read_only' => False, 'type' => 'bool',
      'description' => 'Whether the file is secret, which keeps it from agents that are not marked trusted.'],
    ['name' => 'fileType', 'read_only' => False, 'type' => 'int', 'choices' => $FieldFileTypeChoices,
      'description' => 'What the file is used for by an attack.'],
    ['name' => 'accessGroupId', 'read_only' => False, 'type' => 'int', 'relation' => 'AccessGroup'],
    ['name' => 'lineCount', 'read_only' => True, 'type' => 'int64', 'protected' => True,
      'description' => 'Number of lines the file holds, which is the keyspace a wordlist or rule file contributes.'],
    ['name' => 'checksum', 'read_only' => True, 'type' => 'str(64)', 'null' => True, 'protected' => True,
      'description' => 'Checksum of the file contents, computed by a background job; null while that job has not run yet.'],
  ],
];
$CONF['FileDelete'] = [
  'columns' => [
    ['name' => 'fileDeleteId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'filename', 'read_only' => True, 'type' => 'str(256)', 'protected' => True],
    ['name' => 'time', 'read_only' => True, 'type' => 'int64', 'protected' => True],
  ],
];
$CONF['FileDownload'] = [
  'columns' => [
    ['name' => 'fileDownloadId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'time', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'fileId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'relation' => 'File'],
    ['name' => 'status', 'read_only' => True, 'type' => 'int', 'protected' => True, 'choices' => $FieldFileDownloadStatusChoices,
      'description' => 'How far the server has got with fetching the file.'],
  ],
];
$CONF['Hash'] = [
  'columns' => [
    ['name' => 'hashId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'hashlistId', 'read_only' => True, 'type' => 'int', 'relation' => 'Hashlist'],
    // sorting on full, potentially very long hashes fails on MySQL with "Out of sort memory" (see #2481)
    ['name' => 'hash', 'read_only' => False, 'type' => 'str(65535)', 'sort_expression' => 'LEFT(%s, 1024)'],
    ['name' => 'salt', 'read_only' => False, 'type' => 'str(256)'],
    ['name' => 'plaintext', 'read_only' => False, 'type' => 'str(256)'],
    ['name' => 'timeCracked', 'read_only' => False, 'type' => 'int64'],
    ['name' => 'chunkId', 'read_only' => False, 'type' => 'int', 'null' => True, 'relation' => 'Chunk'],
    ['name' => 'isCracked', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'crackPos', 'read_only' => False, 'type' => 'int64'],
  ],
];
$CONF['HashBinary'] = [
  'columns' => [
    ['name' => 'hashBinaryId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'hashlistId', 'read_only' => True, 'type' => 'int', 'relation' => 'Hashlist'],
    ['name' => 'essid', 'read_only' => False, 'type' => 'str(100)'],
    ['name' => 'hash', 'read_only' => False, 'type' => 'str(4294967295)'],
    ['name' => 'plaintext', 'read_only' => False, 'type' => 'str(1024)'],
    ['name' => 'timeCracked', 'read_only' => False, 'type' => 'int64'],
    ['name' => 'chunkId', 'read_only' => False, 'type' => 'int', 'null' => True, 'relation' => 'Chunk'],
    ['name' => 'isCracked', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'crackPos', 'read_only' => False, 'type' => 'int64'],
  ],
];
$CONF['Hashlist'] = [
  'columns' => [
    ['name' => 'hashlistId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'hashlistName', 'read_only' => False, 'type' => 'str(100)', 'alias' => UQueryHashlist::HASHLIST_NAME],
    ['name' => 'format', 'read_only' => True, 'type' => 'int', 'choices' => $FieldHashlistFormatChoices,
      'description' => 'What the hashlist holds, which decides how its hashes are read and how they are cracked.'],
    ['name' => 'hashTypeId', 'read_only' => True, 'type' => 'int', 'relation' => 'HashType'],
    ['name' => 'hashCount', 'read_only' => True, 'type' => 'int',
      'description' => 'Number of hashes the hashlist holds. Counted by the server while the hashes are imported.'],
    ['name' => 'saltSeparator', 'read_only' => True, 'type' => 'str(10)', 'null' => True, 'alias' => UQueryHashlist::HASHLIST_SEPARATOR,
      'description' => 'Character separating the fields of an imported line, used to split a salted hash and to split a hash from its plaintext.'],
    ['name' => 'cracked', 'read_only' => True, 'type' => 'int', 'protected' => True,
      'description' => 'Number of hashes of this hashlist that have been cracked so far.'],
    ['name' => 'isSecret', 'read_only' => False, 'type' => 'bool',
      'description' => 'Whether the hashes are secret, which keeps them from agents that are not marked trusted.'],
    ['name' => 'hexSalt', 'read_only' => True, 'type' => 'bool', 'alias' => UQueryHashlist::HASHLIST_HEX_SALTED,
      'description' => 'Whether the salts are hex encoded and have to be decoded before use.'],
    ['name' => 'isSalted', 'read_only' => True, 'type' => 'bool'],
    ['name' => 'accessGroupId', 'read_only' => False, 'type' => 'int', 'relation' => 'AccessGroup'],
    ['name' => 'notes', 'read_only' => False, 'type' => 'str(65535)'],
    ['name' => 'brainId', 'read_only' => True, 'type' => 'bool', 'alias' => UQueryHashlist::HASHLIST_USE_BRAIN,
      'description' => 'Whether tasks on this hashlist use the hashcat brain, which skips candidates an earlier task already tried.'],
    // a bitmask rather than a choice list: 0 is what a hashlist without brain carries, and only an enabled
    // brain constrains it to 1, 2 or 3 (see HashlistUtils::createHashlist)
    ['name' => 'brainFeatures', 'read_only' => True, 'type' => 'int',
      'description' => 'Which candidates the hashcat brain is told about, as a bitmask: 1 sends hashed passwords, 2 sends attack positions, 3 sends both. Only meaningful while useBrain is set.'],
    ['name' => 'isArchived', 'read_only' => False, 'type' => 'bool'],
  ],
];
$CONF['HashType'] = [
  'columns' => [
    ['name' => 'hashTypeId', 'read_only' => True, 'type' => 'int'],
    ['name' => 'description', 'read_only' => False, 'type' => 'str(256)'],
    ['name' => 'isSalted', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'isSlowHash', 'read_only' => False, 'type' => 'bool'],
  ],
];
$CONF['HealthCheck'] = [
  'columns' => [
    ['name' => 'healthCheckId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'time', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'status', 'read_only' => True, 'type' => 'int', 'protected' => True, 'choices' => $FieldHealthCheckStatusChoices,
      'description' => 'How far this health check has got overall.'],
    ['name' => 'checkType', 'read_only' => False, 'type' => 'int', 'choices' => $FieldHealthCheckModeChoices,
      'description' => 'Which hash the agents are asked to crack for this check.'],
    ['name' => 'hashtypeId', 'read_only' => True, 'type' => 'int', 'relation' => 'HashType'],
    ['name' => 'crackerBinaryId', 'read_only' => True, 'type' => 'int', 'relation' => 'CrackerBinary'],
    ['name' => 'expectedCracks', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'attackCmd', 'read_only' => True, 'type' => 'str(65535)', 'protected' => True],
  ],
];
$CONF['HealthCheckAgent'] = [
  'columns' => [
    ['name' => 'healthCheckAgentId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'healthCheckId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'relation' => 'HealthCheck'],
    ['name' => 'agentId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'relation' => 'Agent'],
    ['name' => 'status', 'read_only' => True, 'type' => 'int', 'protected' => True, 'choices' => $FieldHealthCheckAgentStatusChoices,
      'description' => 'How this agent fared in the health check.'],
    ['name' => 'cracked', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'numGpus', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'start', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'end', 'read_only' => True, 'type' => 'int64', 'protected' => True, 'dba_mapping' => True],
    ['name' => 'errors', 'read_only' => True, 'type' => 'str(65535)', 'protected' => True],
  ],
];
$CONF['JwtApiKey'] = [
  'columns' => [
    ['name' => 'jwtApiKeyId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'startValid', 'read_only' => True, 'type' => 'int64'],
    ['name' => 'endValid', 'read_only' => True, 'type' => 'int64'],
    ['name' => 'userId', 'read_only' => True, 'null' => True, 'type' => 'int', 'relation' => 'User'],
    ['name' => 'tokenName', 'read_only' => False, 'type' => 'str(100)'],
    ['name' => 'isRevoked', 'read_only' => False, 'type' => 'bool'],
  ],
];
$CONF['LogEntry'] = [
  'columns' => [
    ['name' => 'logEntryId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'issuer', 'read_only' => True, 'type' => 'str(50)', 'protected' => True, 'choices' => $FieldLogEntryIssuerChoices,
      'description' => 'Which entry point wrote the entry.'],
    ['name' => 'issuerId', 'read_only' => True, 'type' => 'str(50)', 'protected' => True],
    ['name' => 'level', 'read_only' => True, 'type' => 'str(50)', 'protected' => True, 'choices' => $FieldLogEntryLevelChoices,
      'description' => 'How serious the entry is.'],
    ['name' => 'message', 'read_only' => True, 'type' => 'str(65535)', 'protected' => True],
    ['name' => 'time', 'read_only' => True, 'type' => 'int64', 'protected' => True],
  ],
];
$CONF['NotificationSetting'] = [
  'columns' => [
    ['name' => 'notificationSettingId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    // 'action' names the event that triggers the notification, a DNotificationType value
    ['name' => 'action', 'read_only' => False, 'type' => 'str(50)', 'choices' => $FieldNotificationTypeChoices,
      'description' => 'The event that triggers this notification.'],
    // notifications not tied to a specific object (e.g. agent-wide events) have no objectId
    ['name' => 'objectId', 'read_only' => True, 'null' => True, 'type' => 'int', 'protected' => True],
    // 'notification' names the delivery method, the name of a registered HashtopolisNotification.
    // That set is open - an installation can register notification classes of its own - so the
    // column names no choices and NotificationUtils validates the value against the registry of
    // the running server instead
    ['name' => 'notification', 'read_only' => False, 'type' => 'str(50)'],
    ['name' => 'userId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'relation' => 'User'],
    ['name' => 'receiver', 'read_only' => False, 'type' => 'str(256)'],
    ['name' => 'isActive', 'read_only' => False, 'type' => 'bool'],
  ],
];
$CONF['Preprocessor'] = [
  'columns' => [
    ['name' => 'preprocessorId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'name', 'read_only' => False, 'type' => 'str(256)'],
    ['name' => 'url', 'read_only' => False, 'type' => 'str(512)'],
    ['name' => 'binaryName', 'read_only' => False, 'type' => 'str(256)'],
    ['name' => 'keyspaceCommand', 'read_only' => False, 'type' => 'str(256)'],
    ['name' => 'skipCommand', 'read_only' => False, 'type' => 'str(256)'],
    ['name' => 'limitCommand', 'read_only' => False, 'type' => 'str(256)'],
  ],
];
$CONF['Pretask'] = [
  'columns' => [
    ['name' => 'pretaskId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'taskName', 'read_only' => False, 'type' => 'str(100)'],
    ['name' => 'attackCmd', 'read_only' => False, 'type' => 'str(65535)',
      'description' => 'The cracker arguments the agents run, with #HL# standing in for the hashlist file.'],
    ['name' => 'chunkTime', 'read_only' => False, 'type' => 'int',
      'description' => 'How long one chunk of this task should keep an agent busy, in seconds. The chunk size is derived from it and the benchmark of the agent.'],
    ['name' => 'statusTimer', 'read_only' => False, 'type' => 'int',
      'description' => 'How often an agent working on this task reports its progress, in seconds.'],
    ['name' => 'color', 'read_only' => False, 'type' => 'str(20)',
      'description' => 'Colour the web interface tints tasks created from this pretask with, as an RGB hex triplet without the leading hash sign.'],
    ['name' => 'isSmall', 'read_only' => False, 'type' => 'bool',
      'description' => 'Whether the task is small, in which case only ever one agent is assigned to it.'],
    ['name' => 'isCpuTask', 'read_only' => False, 'type' => 'bool',
      'description' => 'Whether the task is meant for CPU agents rather than for agents with a GPU.'],
    ['name' => 'useNewBench', 'read_only' => False, 'type' => 'bool',
      'description' => 'Whether agents benchmark this task by speed rather than by runtime. Sent to the agent as benchType.'],
    ['name' => 'priority', 'read_only' => False, 'type' => 'int',
      'description' => 'Dispatch order: of the tasks an agent may take, the one with the highest priority is handed out first.'],
    ['name' => 'maxAgents', 'read_only' => False, 'type' => 'int',
      'description' => 'How many agents may work on this task at the same time. Zero leaves it unlimited.'],
    ['name' => 'isMaskImport', 'read_only' => False, 'type' => 'bool',
      'description' => 'Whether the attack command came from an imported mask file.'],
    ['name' => 'crackerBinaryTypeId', 'read_only' => False, 'type' => 'int'],
  ],
];
$CONF['RefreshToken'] = [
  'columns' => [
    ['name' => 'refreshTokenId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'userId', 'read_only' => True, 'type' => 'int', 'relation' => 'User'],
    ['name' => 'tokenHash', 'read_only' => True, 'type' => 'str(64)', 'protected' => True, 'private' => True],
    ['name' => 'familyId', 'read_only' => True, 'type' => 'str(32)', 'protected' => True, 'private' => True],
    ['name' => 'issuedAt', 'read_only' => True, 'type' => 'int64'],
    ['name' => 'endValid', 'read_only' => True, 'type' => 'int64'],
    ['name' => 'usedAt', 'read_only' => True, 'null' => True, 'type' => 'int64'],
    ['name' => 'isRevoked', 'read_only' => True, 'type' => 'bool'],
  ],
];
$CONF['RegVoucher'] = [
  'columns' => [
    ['name' => 'regVoucherId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'voucher', 'read_only' => False, 'type' => 'str(100)'],
    ['name' => 'time', 'read_only' => True, 'type' => 'int64', 'protected' => True],
  ],
];
$CONF['RightGroup'] = [
  'columns' => [
    ['name' => 'rightGroupId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'alias' => 'rightGroupId'],
    ['name' => 'groupName', 'read_only' => False, 'type' => 'str(50)', 'alias' => 'name'],
    ['name' => 'permissions', 'read_only' => False, 'type' => 'dict', 'subtype' => 'bool'],
  ],
];
$CONF['Session'] = [
  'columns' => [
    ['name' => 'sessionId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'userId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'relation' => 'User'],
    ['name' => 'sessionStartDate', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'lastActionDate', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'isOpen', 'read_only' => True, 'type' => 'bool', 'protected' => True],
    ['name' => 'sessionLifetime', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'sessionKey', 'read_only' => True, 'type' => 'str(256)', 'protected' => True],
  ],
];
$CONF['Speed'] = [
  'columns' => [
    ['name' => 'speedId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'agentId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'relation' => 'Agent'],
    ['name' => 'taskId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'relation' => 'Task'],
    ['name' => 'speed', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'time', 'read_only' => True, 'type' => 'int64', 'protected' => True],
  ],
];
$CONF['StoredValue'] = [
  'columns' => [
    ['name' => 'storedValueId', 'read_only' => True, 'type' => 'str(50)', 'protected' => True],
    ['name' => 'val', 'read_only' => False, 'type' => 'str(256)'],
  ],
];
$CONF['Supertask'] = [
  'columns' => [
    ['name' => 'supertaskId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'supertaskName', 'read_only' => False, 'type' => 'str(50)'],
    ['name' => 'crackerBinaryTypeId', 'read_only' => True, 'type' => 'int', 'relation' => 'CrackerBinaryType'],
  ],
];
$CONF['Task'] = [
  'columns' => [
    ['name' => 'taskId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'taskName', 'read_only' => False, 'type' => 'str(256)'],
    ['name' => 'attackCmd', 'read_only' => False, 'type' => 'str(65535)',
      'description' => 'The cracker arguments the agents run, with #HL# standing in for the hashlist file.'],
    ['name' => 'chunkTime', 'read_only' => False, 'type' => 'int',
      'description' => 'How long one chunk of this task should keep an agent busy, in seconds. The chunk size is derived from it and the benchmark of the agent.'],
    ['name' => 'statusTimer', 'read_only' => False, 'type' => 'int',
      'description' => 'How often an agent working on this task reports its progress, in seconds.'],
    ['name' => 'keyspace', 'read_only' => True, 'type' => 'int64', 'protected' => True,
      'description' => 'Size of the keyspace of the task, as the cracker measured it. Zero until an agent has reported it.'],
    ['name' => 'keyspaceProgress', 'read_only' => True, 'type' => 'int64', 'protected' => True,
      'description' => 'How far into the keyspace the task has been handed out, which is where the next chunk begins.'],
    ['name' => 'priority', 'read_only' => False, 'type' => 'int',
      'description' => 'Dispatch order: of the tasks an agent may take, the one with the highest priority is handed out first.'],
    ['name' => 'maxAgents', 'read_only' => False, 'type' => 'int',
      'description' => 'How many agents may work on this task at the same time. Zero leaves it unlimited.'],
    ['name' => 'color', 'read_only' => False, 'type' => 'str(50)', 'null' => True,
      'description' => 'Colour the web interface tints the task with, as an RGB hex triplet without the leading hash sign.'],
    ['name' => 'isSmall', 'read_only' => False, 'type' => 'bool',
      'description' => 'Whether the task is small, in which case only ever one agent is assigned to it.'],
    ['name' => 'isCpuTask', 'read_only' => False, 'type' => 'bool',
      'description' => 'Whether the task is meant for CPU agents rather than for agents with a GPU.'],
    ['name' => 'useNewBench', 'read_only' => True, 'type' => 'bool',
      'description' => 'Whether agents benchmark this task by speed rather than by runtime. Sent to the agent as benchType.'],
    ['name' => 'skipKeyspace', 'read_only' => True, 'type' => 'int64',
      'description' => 'How much of the keyspace to leave out at the beginning of the task.'],
    ['name' => 'crackerBinaryId', 'read_only' => True, 'type' => 'int', 'relation' => 'CrackerBinary'],
    ['name' => 'crackerBinaryTypeId', 'read_only' => True, 'null' => True, 'type' => 'int', 'relation' => 'CrackerBinaryType'], // TODO: this will be removed in the future as it's redundant
    ['name' => 'taskWrapperId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'relation' => 'TaskWrapper'],
    ['name' => 'isArchived', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'notes', 'read_only' => False, 'type' => 'str(65535)'],
    ['name' => 'staticChunks', 'read_only' => True, 'type' => 'int', 'choices' => $FieldTaskStaticChunkingChoices,
      'description' => 'How the keyspace of the task is cut into chunks. Anything but the default reads the chunk size from chunkSize instead of benchmarking the agent.'],
    ['name' => 'chunkSize', 'read_only' => True, 'type' => 'int64',
      'description' => 'The fixed chunk size, or the number of chunks, that staticChunks asks for. Ignored while staticChunks is at its default.'],
    ['name' => 'forcePipe', 'read_only' => True, 'type' => 'bool',
      'description' => 'Whether the candidates are always piped into the cracker, which applies the rules before the rejects.'],
    ['name' => 'usePreprocessor', 'read_only' => True, 'type' => 'int', 'alias' => 'preprocessorId',
      'description' => 'The preprocessor generating the candidates of this task, or 0 for none.'],
    ['name' => 'preprocessorCommand', 'read_only' => False, 'type' => 'str(256)',
      'description' => 'Arguments handed to the preprocessor, without its binary name and without the range options the server adds itself.'],
  ],
];
$CONF['TaskDebugOutput'] = [
  'columns' => [
    ['name' => 'taskDebugOutputId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'taskId', 'read_only' => True, 'type' => 'int', 'relation' => 'Task'],
    ['name' => 'output', 'read_only' => True, 'type' => 'str(256)'],
  ],
];
$CONF['TaskWrapper'] = [
  'columns' => [
    ['name' => 'taskWrapperId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'priority', 'read_only' => False, 'type' => 'int'],
    ['name' => 'maxAgents', 'read_only' => False, 'type' => 'int'],
    ['name' => 'taskType', 'read_only' => True, 'type' => 'int', 'protected' => True, 'choices' => $FieldTaskTypeChoices,
      'description' => 'Whether the wrapper holds a single task or a supertask.'],
    ['name' => 'hashlistId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'relation' => 'Hashlist'],
    ['name' => 'accessGroupId', 'read_only' => False, 'type' => 'int', 'relation' => 'AccessGroup'],
    ['name' => 'taskWrapperName', 'read_only' => False, 'type' => 'str(100)'],
    ['name' => 'isArchived', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'cracked', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ],
];
$CONF['TaskWrapperDisplay'] = [
  'columns' => [
    ['name' => 'taskWrapperId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'taskWrapperPriority', 'read_only' => False, 'type' => 'int'],
    ['name' => 'taskWrapperMaxAgents', 'read_only' => False, 'type' => 'int'],
    ['name' => 'taskType', 'read_only' => True, 'type' => 'int', 'protected' => True, 'choices' => $FieldTaskTypeChoices,
      'description' => 'Whether the wrapper holds a single task or a supertask.'],
    ['name' => 'hashlistId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'relation' => 'Hashlist'],
    ['name' => 'accessGroupId', 'read_only' => False, 'type' => 'int', 'relation' => 'AccessGroup'],
    ['name' => 'taskWrapperName', 'read_only' => False, 'type' => 'str(100)'],
    ['name' => 'displayName', 'read_only' => False, 'type' => 'str(100)'],
    ['name' => 'taskWrapperIsArchived', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'cracked', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'taskId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'null' => True],
    ['name' => 'taskName', 'read_only' => False, 'type' => 'str(256)', 'null' => True],
    ['name' => 'color', 'read_only' => False, 'type' => 'str(50)', 'null' => True],
    ['name' => 'attackCmd', 'read_only' => False, 'type' => 'str(65535)', 'null' => True],
    ['name' => 'chunkTime', 'read_only' => False, 'type' => 'int', 'null' => True],
    ['name' => 'statusTimer', 'read_only' => False, 'type' => 'int', 'null' => True],
    ['name' => 'keyspace', 'read_only' => True, 'type' => 'int64', 'protected' => True, 'null' => True],
    ['name' => 'keyspaceProgress', 'read_only' => True, 'type' => 'int64', 'protected' => True, 'null' => True],
    ['name' => 'taskPriority', 'read_only' => False, 'type' => 'int', 'null' => True],
    ['name' => 'taskMaxAgents', 'read_only' => False, 'type' => 'int', 'null' => True],
    ['name' => 'isSmall', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'isCpuTask', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'taskIsArchived', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'taskUsePreprocessor', 'read_only' => True, 'type' => 'int', 'alias' => 'preprocessorId', 'null' => True],
    ['name' => 'hashlistName', 'read_only' => True, 'type' => 'str(100)', 'protected' => True],
    ['name' => 'hashCount', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'hashlistCracked', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'hashlistIsSecret', 'read_only' => True, 'type' => 'bool'],
    ['name' => 'hashTypeId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'hashTypeDescription', 'read_only' => True, 'type' => 'str(256)', 'protected' => True],
    ['name' => 'groupName', 'read_only' => True, 'type' => 'str(50)', 'protected' => True],
  ],
];
$CONF['User'] = [
  'columns' => [
    ['name' => 'userId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'alias' => 'userId', 'public' => True],
    ['name' => 'username', 'read_only' => True, 'type' => 'str(100)', 'alias' => 'name', 'public' => True],
    ['name' => 'email', 'read_only' => False, 'type' => 'str(150)'],
    ['name' => 'passwordHash', 'read_only' => True, 'type' => 'str(256)', 'protected' => True, 'private' => True],
    ['name' => 'passwordSalt', 'read_only' => True, 'protected' => True, 'type' => 'str(256)', 'private' => True],
    ['name' => 'isValid', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'isComputedPassword', 'read_only' => True, 'type' => 'bool', 'protected' => True,],
    ['name' => 'lastLoginDate', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'registeredSince', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'sessionLifetime', 'read_only' => False, 'type' => 'int', 'protected' => False],
    ['name' => 'rightGroupId', 'read_only' => False, 'type' => 'int', 'alias' => 'globalPermissionGroupId', 'relation' => 'RightGroup'],
  ],
  "dba_mapping" => True,
];
$CONF['Zap'] = [
  'columns' => [
    ['name' => 'zapId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'hash', 'read_only' => True, 'type' => 'str(65535)', 'protected' => True],
    ['name' => 'solveTime', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'agentId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'relation' => 'Agent', 'null' => True],
    ['name' => 'hashlistId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'relation' => 'Hashlist'],
  ],
];
//
// Relations
//
$CONF['AccessGroupUser'] = [
  'columns' => [
    ['name' => 'accessGroupUserId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'accessGroupId', 'read_only' => True, 'type' => 'int', 'relation' => 'AccessGroup'],
    ['name' => 'userId', 'read_only' => True, 'type' => 'int', 'relation' => 'User'],
  ],
];
$CONF['AccessGroupAgent'] = [
  'columns' => [
    ['name' => 'accessGroupAgentId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'accessGroupId', 'read_only' => True, 'type' => 'int', 'relation' => 'accessGroup'],
    ['name' => 'agentId', 'read_only' => True, 'type' => 'int', 'relation' => 'Agent'],
  ],
];
$CONF['FileTask'] = [
  'columns' => [
    ['name' => 'fileTaskId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'fileId', 'read_only' => True, 'type' => 'int', 'relation' => 'File'],
    ['name' => 'taskId', 'read_only' => True, 'type' => 'int', 'relation' => 'Task'],
  ],
];
$CONF['FilePretask'] = [
  'columns' => [
    ['name' => 'filePretaskId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'fileId', 'read_only' => True, 'type' => 'int', 'relation' => 'File'],
    ['name' => 'pretaskId', 'read_only' => True, 'type' => 'int', 'relation' => 'PreTask'],
  ],
];
$CONF['SupertaskPretask'] = [
  'columns' => [
    ['name' => 'supertaskPretaskId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'supertaskId', 'read_only' => True, 'type' => 'int', 'relation' => 'Supertask'],
    ['name' => 'pretaskId', 'read_only' => True, 'type' => 'int', 'relation' => 'Pretask'],
  ],
];
$CONF['HashlistHashlist'] = [
  'columns' => [
    ['name' => 'hashlistHashlistId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'parentHashlistId', 'read_only' => True, 'type' => 'int', 'relation' => 'Hashlist'],
    ['name' => 'hashlistId', 'read_only' => True, 'type' => 'int', 'relation' => 'Hashlist'],
  ],
];

$CONF['BackgroundJob'] = [
  'columns' => [
    ['name' => 'backgroundJobId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'jobType', 'read_only' => True, 'type' => 'str(100)', 'protected' => True, 'choices' => $FieldBackgroundJobTypeChoices,
      'description' => 'The work the job performs, which decides how its payload is read.'],
    ['name' => 'payload', 'read_only' => True, 'type' => 'json', 'protected' => True],
    ['name' => 'status', 'read_only' => True, 'type' => 'int', 'protected' => True, 'choices' => $FieldBackgroundJobStatusChoices,
      'description' => 'How far the job has got.'],
    ['name' => 'userId', 'read_only' => True, 'protected' => True, 'type' => 'int', 'relation' => 'User', 'null' => True],
    ['name' => 'createdAt', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'startedAt', 'read_only' => True, 'type' => 'int64', 'protected' => True, 'null' => True],
    ['name' => 'finishedAt', 'read_only' => True, 'type' => 'int64', 'protected' => True, 'null' => True],
    ['name' => 'exitCode', 'read_only' => True, 'type' => 'int', 'protected' => True, 'null' => True],
    ['name' => 'resultMessage', 'read_only' => True, 'type' => 'str(1024)', 'protected' => True, 'null' => True],
  ],
];

$CONF['_sqlx_migrations'] = [
  'columns' => [
    ['name' => 'version', 'read_only' => True, 'type' => 'str(256)', 'protected' => True],
    ['name' => 'description', 'read_only' => True, 'type' => 'str(65535)', 'protected' => True],
    ['name' => 'installed_on', 'read_only' => True, 'type' => 'datetime', 'protected' => True],
    ['name' => 'success', 'read_only' => True, 'type' => 'bool', 'protected' => True],
    ['name' => 'checksum', 'read_only' => True, 'type' => 'binary', 'protected' => True],
    ['name' => 'execution_time', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ]
];

/**
 * @throws Exception
 */
function getTypingType($str, $nullable = false): string {
  if ($str == 'int' || $str == 'int64' || $str == 'uint64') {
    return ($nullable ? '?' : '') . 'int';
  }
  if (str_starts_with($str, "str(")) {
    return ($nullable ? '?' : '') . 'string';
  }
  if ($str == 'bool') {
    return ($nullable ? '?' : '') . 'int';
  }
  if ($str == 'array' || $str == 'dict') {
    return ($nullable ? '?' : '') . 'string';
  }
  if ($str == 'datetime') {
    return ($nullable ? '?' : '') . 'string';
  }
  if ($str == 'binary') {
    return ($nullable ? '?' : '') . 'string';
  }
  if ($str == 'json') {
    return ($nullable ? '?' : '') . 'string';
  }
  throw new Exception("Cannot convert type " . $str);
}

foreach ($CONF as $NAME => $MODEL_CONF) {
  $MODEL_CONF = array_merge(["dba_mapping" => False], $MODEL_CONF);
  $COLUMNS = array_map(fn(array $column): array => array_merge([
    "null" => False,
    "protected" => False,
    "private" => False,
    "public" => False,
    "dba_mapping" => False,
    "subtype" => "unset",
    "choices" => null,
    "description" => null,
    "alias" => $column["name"],
    // an absent "null" key means the column is nullable, while "null" => False makes it non-nullable
    "nullable" => !(array_key_exists("null", $column) && !$column["null"]),
  ], $column), $MODEL_CONF['columns']);
  $class = file_get_contents(dirname(__FILE__) . "/AbstractModel.template.txt");
  $class = str_replace("__MODEL_NAME__", $NAME, $class);
  $vars = array();
  $init = array();
  $keyVal = array();
  $class = str_replace("__MODEL_PK__", $COLUMNS[0]['name'], $class);
  $class = str_replace("__MODEL_PK_TYPE__", getTypingType($COLUMNS[0]['type'], false), $class);
  $features = array();
  $functions = array();
  $params = array();
  $variables = array();
  $crud_defines = array();
  foreach ($COLUMNS as $COLUMN) {
    $col = $COLUMN['name'];
    $type = getTypingType($COLUMN['type'], $COLUMN['nullable']);
    if (sizeof($vars) > 0) {
      $getter = "function get" . strtoupper($col[0]) . substr($col, 1) . "(): $type {\n    return \$this->$col;\n  }";
      $setter = "function set" . strtoupper($col[0]) . substr($col, 1) . "($type \$$col): void {\n    \$this->$col = \$$col;\n  }";
      $functions[] = $getter;
      $functions[] = $setter;
    }
    $params[] = "$type \$$col";
    $vars[] = "private $type \$$col;";
    $init[] = "\$this->$col = \$$col;";
    
    if ($COLUMN['choices'] !== null) {
      $choicesVal = '[';
      $namesVal = '[';
      foreach ($COLUMN['choices'] as $CHOICE) {
        $key = is_string($CHOICE['key']) ? '"' . $CHOICE['key'] . '"' : $CHOICE['key'];
        $choicesVal .= $key . ' => ' . phpStringLiteral($CHOICE['label']) . ', ';
        $namesVal .= $key . ' => ' . phpStringLiteral($CHOICE['name']) . ', ';
      }
      $choicesVal .= ']';
      $namesVal .= ']';
    }
    else {
      $choicesVal = '"unset"';
      $namesVal = null;
    }
    
    $features[] = "\$dict['$col'] = ['read_only' => " . ($COLUMN['read_only'] ? 'True' : "False") . ', ' .
      '"type" => "' . $COLUMN['type'] . '", ' .
      '"subtype" => "' . $COLUMN['subtype'] . '", ' .
      '"choices" => ' . $choicesVal . ', ' .
      '"null" => ' . ($COLUMN['null'] ? 'True' : 'False') . ', ' .
      '"pk" => ' . (($col == $COLUMNS[0]['name']) ? 'True' : 'False') . ', ' .
      '"protected" => ' . ($COLUMN['protected'] ? 'True' : 'False') . ', ' .
      '"private" => ' . ($COLUMN['private'] ? 'True' : 'False') . ', ' .
      '"alias" => "' . $COLUMN['alias'] . '", ' .
      '"public" => ' . ($COLUMN['public'] ? 'True' : 'False') . ', ' .
      '"dba_mapping" => ' . ($COLUMN['dba_mapping'] ? 'True' : 'False') .
      ($namesVal !== null ? ', "choice_names" => ' . $namesVal : '') .
      ($COLUMN['description'] !== null ? ', "description" => ' . phpStringLiteral($COLUMN['description']) : '') .
      (isset($COLUMN['sort_expression']) ? ', "sort_expression" => "' . $COLUMN['sort_expression'] . '"' : '') .
      '];';
    $keyVal[] = "\$dict['$col'] = \$this->$col;";
    $variables[] = "const " . makeConstant($col) . " = \"$col\";";
    
  }
  $crud_prefix = $MODEL_CONF['permission_alias'] ?? $NAME;
  $crud_defines[] = "const PERM_CREATE = \"perm" . $crud_prefix . "Create\";";
  $crud_defines[] = "const PERM_READ = \"perm" . $crud_prefix . "Read\";";
  $crud_defines[] = "const PERM_UPDATE = \"perm" . $crud_prefix . "Update\";";
  $crud_defines[] = "const PERM_DELETE = \"perm" . $crud_prefix . "Delete\";";
  
  $class = str_replace("__MODEL_PARAMS__", implode(", ", $params), $class);
  $class = str_replace("__MODEL_VARS__", implode("\n  ", $vars), $class);
  $class = str_replace("__MODEL_PARAMS_INIT__", implode("\n    ", $init), $class);
  $class = str_replace("__MODEL_KEY_VAL__", implode("\n    ", $keyVal), $class);
  $class = str_replace("__MODEL_FEATURES__", implode("\n    ", $features), $class);
  $class = str_replace("__MODEL_GETTER_SETTER__", implode("\n  \n  ", $functions), $class);
  $class = str_replace("__MODEL_VARIABLE_NAMES__", implode("\n  ", $variables), $class);
  $class = str_replace("__MODEL_PERMISSION_DEFINES__", implode("\n  ", $crud_defines), $class);
  
  file_put_contents(dirname(__FILE__) . "/" . $NAME . ".php", $class);
  
  $class = file_get_contents(dirname(__FILE__) . "/AbstractModelFactory.template.txt");
  $class = str_replace("__MODEL_NAME__", $NAME, $class);
  $class = str_replace("__MODEL_DBA_MAPPING__", ($MODEL_CONF['dba_mapping'] ? 'True' : 'False'), $class);
  $dict = [];
  $dict2 = [];
  $mapping = [];
  $streaming = [];
  foreach ($COLUMNS as $COLUMN) {
    $col = strtolower($COLUMN['name']);
    if (sizeof($dict) == 0) {
      $dict[] = "-1";
      $dict2[] = "\$dict['$col']";
    }
    else {
      $dict[] = "null";
      $dict2[] = "\$dict['$col']";
      if ($COLUMN['dba_mapping']) {
        $mapping[] = "\$dict['$col'] = \$dict['htp_$col'];";
      }
      if ($COLUMN['type'] == 'binary') {
        $streaming[] = "if (is_resource(\$dict['$col'])) {\n      \$t = stream_get_contents(\$dict['$col']);\n      fclose(\$dict['$col']);\n      \$dict['$col'] = bin2hex(\$t);\n    }";
      }
    }
  }
  $class = str_replace("__MODEL_DICT__", implode(", ", $dict), $class);
  $class = str_replace("__MODEL__DICT2__", implode(", ", $dict2), $class);
  
  if (count($mapping) > 0) {
    $class = str_replace("__MODEL_MAPPING_DICT__", "\n    " . implode("\n    ", $mapping), $class);
  }
  else {
    $class = str_replace("__MODEL_MAPPING_DICT__", "", $class);
  }
  
  if (count($streaming) > 0) {
    $class = str_replace("__MODEL_STREAMING_DICT__", "\n    " . implode("\n    ", $streaming), $class);
  }
  else {
    $class = str_replace("__MODEL_STREAMING_DICT__", "", $class);
  }
  
  file_put_contents(dirname(__FILE__) . "/" . $NAME . "Factory.php", $class);
}

$class = file_get_contents(dirname(__FILE__) . "/Factory.template.txt");
$static = array();
$functions = array();
$include_models = [];
foreach ($CONF as $NAME => $COLUMNS) {
  $include_models[] = "use Hashtopolis\\dba\\models\\{$NAME}Factory;";
  $lowerName = strtolower($NAME[0]) . substr($NAME, 1);
  $static[] = "private static ?" . $NAME . "Factory \$" . $lowerName . "Factory = null;";
  $functions[] = "public static function get" . $NAME . "Factory(): " . $NAME . "Factory {\n    if (self::\$" . $lowerName . "Factory == null) {\n      \$f = new " . $NAME . "Factory();\n      self::\$" . $lowerName . "Factory = \$f;\n      return \$f;\n    } else {\n      return self::\$" . $lowerName . "Factory;\n    }\n  }";
}
$class = str_replace("__MODEL_INCLUDE__", implode("\n", $include_models), $class);
$class = str_replace("__MODEL_STATIC__", implode("\n  ", $static), $class);
$class = str_replace("__MODEL_FUNCTIONS__", implode("\n  \n  ", $functions), $class);

file_put_contents(dirname(__FILE__) . "/../Factory.php", $class);


function makeConstant($name) {
  $output = "";
  for ($i = 0; $i < strlen($name); $i++) {
    if ($name[$i] == strtoupper($name[$i]) && $i < strlen($name) - 1) {
      $output .= "_";
    }
    $output .= strtoupper($name[$i]);
  }
  return $output;
}
