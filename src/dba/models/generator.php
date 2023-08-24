<?php

$CONF = array();

require_once(dirname(__FILE__) . "/../../inc/defines/agents.php");
require_once(dirname(__FILE__) . "/../../inc/defines/userApi.php");
require_once(dirname(__FILE__) . "/../../inc/defines/tasks.php");

//
// Field choice declarations
// 
$FieldIgnoreErrorsChoices = [
  [ 'key' => DAgentIgnoreErrors::NO, 'label' => 'Deactivate agent on error'],
  [ 'key' => DAgentIgnoreErrors::IGNORE_SAVE, 'label' => 'Keep agent running, but save errors'],
  [ 'key' => DAgentIgnoreErrors::IGNORE_NOSAVE, 'label' => 'Keep agent running and discard errors'],
];

$FieldTaskTypeChoices = [
  [ 'key' => DTaskTypes::NORMAL, 'label' => 'TaskType is Task'],
  [ 'key' => DTaskTypes::SUPERTASK, 'label' => 'TaskType is Supertask'],
];



// Type: describes what kind of type the attribute is
// Subtype: in case type is dict, the first level of the dict will also be checked.
// Read_only: determines that the attribute can only be set during creation
// Protected: cannot be set at any point but can be returned. For example 'id'
// Private: cannot be set at any point, and can also not be returned. For example: passwordhash
// Pk: primary key
// Null: means that an attribute is optional.
// Alias: used for setting new names for attributes. This is for the transition between APIv1 and APIv2.
//        Alias determines the name for the attribute as it should be called by APIv2

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
    ['name' => 'uid', 'read_only' => False, 'type' => 'str(100)'],
    ['name' => 'os', 'read_only' => False, 'type' => 'int'],
    ['name' => 'devices', 'read_only' => False, 'type' => 'str(65535)'],
    ['name' => 'cmdPars', 'read_only' => False, 'type' => 'str(256)'],
    ['name' => 'ignoreErrors', 'read_only' => False, 'type' => 'int', 'choices' => $FieldIgnoreErrorsChoices],
    ['name' => 'isActive', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'isTrusted', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'token', 'read_only' => False, 'type' => 'str(30)'],
    ['name' => 'lastAct', 'read_only' => True, 'type' => 'str(50)', 'protected' => True],
    ['name' => 'lastTime', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'lastIp', 'read_only' => True, 'type' => 'str(50)', 'protected' => True],
    ['name' => 'userId', 'read_only' => False, 'type' => 'int', 'null' => True],
    ['name' => 'cpuOnly', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'clientSignature', 'read_only' => False, 'type' => 'str(50)'],
  ],
];
$CONF['AgentBinary'] = [
  'columns' => [
    ['name' => 'agentBinaryId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'type', 'read_only' => False, 'type' => 'str(20)'],
    ['name' => 'version', 'read_only' => False, 'type' => 'str(20)'],
    ['name' => 'operatingSystems', 'read_only' => False, 'type' => 'str(50)'],
    ['name' => 'filename', 'read_only' => False, 'type' => 'str(50)'],
    ['name' => 'updateTrack', 'read_only' => False, 'type' => 'str(20)'],
    ['name' => 'updateAvailable', 'read_only' => True, 'type' => 'str(20)', 'protected' => True],
  ],
];
$CONF['AgentError'] = [
  'columns' => [
    ['name' => 'agentErrorId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'agentId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'taskId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'chunkId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'time', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'error', 'read_only' => True, 'type' => 'str(65535)', 'protected' => True],
  ],
];
$CONF['AgentStat'] = [
  'columns' => [
    ['name' => 'agentStatId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'agentId', 'read_only' => True, 'protected' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'statType', 'read_only' => True, 'protected' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'time', 'read_only' => True, 'protected' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'value', 'read_only' => True, 'protected' => True, 'type' => 'array', 'subtype' => 'int', 'protected' => True],
  ],
];
$CONF['AgentZap'] = [
  'columns' => [
    ['name' => 'agentZapId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'agentId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'lastZapId', 'read_only' => True, 'type' => 'str(128)', 'protected' => True],
  ],
];
$CONF['ApiKey'] = [
  'columns' => [
    ['name' => 'apiKeyId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'startValid', 'read_only' => False, 'type' => 'int64'],
    ['name' => 'endValid', 'read_only' => False, 'type' => 'int64'],
    ['name' => 'accessKey', 'read_only' => True, 'type' => 'str(256)', 'protected' => True],
    ['name' => 'accessCount', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'userId', 'read_only' => False, 'type' => 'int'],
    ['name' => 'apiGroupId', 'read_only' => False, 'type' => 'int'],
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
    ['name' => 'taskId', 'read_only' => False, 'type' => 'int'],
    ['name' => 'agentId', 'read_only' => False, 'type' => 'int'],
    ['name' => 'benchmark', 'read_only' => True, 'type' => 'str(50)', 'protected' => True],
  ],
];
$CONF['Chunk'] = [
  'columns' => [
    ['name' => 'chunkId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'taskId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'skip', 'read_only' => True, 'type' => 'uint64', 'protected' => True],
    ['name' => 'length', 'read_only' => True, 'type' => 'uint64', 'protected' => True],
    ['name' => 'agentId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'dispatchTime', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'solveTime', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'checkpoint', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'progress', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'state', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'cracked', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'speed', 'read_only' => True, 'type' => 'int64', 'protected' => True],
  ],
];
$CONF['Config'] = [
  'columns' => [
    ['name' => 'configId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'configSectionId', 'read_only' => False, 'type' => 'int'],
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
    ['name' => 'crackerBinaryTypeId', 'read_only' => False, 'type' => 'int'],
    ['name' => 'version', 'read_only' => False, 'type' => 'str(20)'],
    ['name' => 'downloadUrl', 'read_only' => False, 'type' => 'str(150)'],
    ['name' => 'binaryName', 'read_only' => False, 'type' => 'str(50)'],
  ],
];
$CONF['CrackerBinaryType'] = [
  'columns' => [
    ['name' => 'crackerBinaryTypeId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'typeName', 'read_only' => False, 'type' => 'str(30)'],
    ['name' => 'isChunkingAvailable', 'read_only' => False, 'type' => 'bool'],
  ],
];
$CONF['File'] = [
  'columns' => [
    ['name' => 'fileId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'filename', 'read_only' => False, 'type' => 'str(100)'],
    ['name' => 'size', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'isSecret', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'fileType', 'read_only' => False, 'type' => 'int'],
    ['name' => 'accessGroupId', 'read_only' => False, 'type' => 'int'],
    ['name' => 'lineCount', 'read_only' => True, 'type' => 'int64', 'protected' => True],
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
    ['name' => 'fileId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'status', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ],
];
$CONF['Hash'] = [
  'columns' => [
    ['name' => 'hashId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'hashlistId', 'read_only' => False, 'type' => 'int'],
    ['name' => 'hash', 'read_only' => False, 'type' => 'str(65535)'],
    ['name' => 'salt', 'read_only' => False, 'type' => 'str(256)'],
    ['name' => 'plaintext', 'read_only' => False, 'type' => 'str(256)'],
    ['name' => 'timeCracked', 'read_only' => False, 'type' => 'int64'],
    ['name' => 'chunkId', 'read_only' => False, 'type' => 'int'],
    ['name' => 'isCracked', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'crackPos', 'read_only' => False, 'type' => 'int64'],
  ],
];
$CONF['HashBinary'] = [
  'columns' => [
    ['name' => 'hashBinaryId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'hashlistId', 'read_only' => False, 'type' => 'int'],
    ['name' => 'essid', 'read_only' => False, 'type' => 'str(100)'],
    ['name' => 'hash', 'read_only' => False, 'type' => 'str(4294967295)'],
    ['name' => 'plaintext', 'read_only' => False, 'type' => 'str(1024)'],
    ['name' => 'timeCracked', 'read_only' => False, 'type' => 'int64'],
    ['name' => 'chunkId', 'read_only' => False, 'type' => 'int'],
    ['name' => 'isCracked', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'crackPos', 'read_only' => False, 'type' => 'int64'],
  ],
];
$CONF['Hashlist'] = [
  'columns' => [
    ['name' => 'hashlistId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'hashlistName', 'read_only' => False, 'type' => 'str(100)', 'alias' => UQueryHashlist::HASHLIST_NAME],
    ['name' => 'format', 'read_only' => False, 'type' => 'int'],
    ['name' => 'hashTypeId', 'read_only' => True, 'type' => 'int'],
    ['name' => 'hashCount', 'read_only' => True, 'type' => 'int'],
    ['name' => 'saltSeparator', 'read_only' => True, 'type' => 'str(10)', 'null' => True, 'alias' => UQueryHashlist::HASHLIST_SEPARATOR],
    ['name' => 'cracked', 'read_only' => true, 'type' => 'int', 'protected' => True],
    ['name' => 'isSecret', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'hexSalt', 'read_only' => True, 'type' => 'bool', 'alias' => UQueryHashlist::HASHLIST_HEX_SALTED],
    ['name' => 'isSalted', 'read_only' => True, 'type' => 'bool'],
    ['name' => 'accessGroupId', 'read_only' => False, 'type' => 'int'],
    ['name' => 'notes', 'read_only' => False, 'type' => 'str(65535)'],
    ['name' => 'brainId', 'read_only' => True, 'type' => 'bool', 'alias' => UQueryHashlist::HASHLIST_USE_BRAIN],
    ['name' => 'brainFeatures', 'read_only' => True, 'type' => 'int'],
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
    ['name' => 'status', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'checkType', 'read_only' => False, 'type' => 'int'],
    ['name' => 'hashtypeId', 'read_only' => False, 'type' => 'int'],
    ['name' => 'crackerBinaryId', 'read_only' => False, 'type' => 'int'],
    ['name' => 'expectedCracks', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'attackCmd', 'read_only' => True, 'type' => 'str(256)', 'protected' => True],
  ],
];
$CONF['HealthCheckAgent'] = [
  'columns' => [
    ['name' => 'healthCheckAgentId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'healthCheckId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'agentId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'status', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'cracked', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'numGpus', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'start', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'end', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'errors', 'read_only' => True, 'type' => 'str(65535)', 'protected' => True],
  ],
];
$CONF['LogEntry'] = [
  'columns' => [
    ['name' => 'logEntryId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'issuer', 'read_only' => True, 'type' => 'str(50)', 'protected' => True],
    ['name' => 'issuerId', 'read_only' => True, 'type' => 'str(50)', 'protected' => True],
    ['name' => 'level', 'read_only' => True, 'type' => 'str(50)', 'protected' => True],
    ['name' => 'message', 'read_only' => True, 'type' => 'str(65535)', 'protected' => True],
    ['name' => 'time', 'read_only' => True, 'type' => 'int64', 'protected' => True],
  ],
];
$CONF['NotificationSetting'] = [
  'columns' => [
    ['name' => 'notificationSettingId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'action', 'read_only' => False, 'type' => 'str(50)'],
    ['name' => 'objectId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'notification', 'read_only' => False, 'type' => 'str(50)'],
    ['name' => 'userId', 'read_only' => True, 'type' => 'int', 'protected' => True],
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
    ['name' => 'attackCmd', 'read_only' => False, 'type' => 'str(256)'],
    ['name' => 'chunkTime', 'read_only' => False, 'type' => 'int'],
    ['name' => 'statusTimer', 'read_only' => False, 'type' => 'int'],
    ['name' => 'color', 'read_only' => False, 'type' => 'str(20)'],
    ['name' => 'isSmall', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'isCpuTask', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'useNewBench', 'read_only' => True, 'type' => 'bool'],
    ['name' => 'priority', 'read_only' => False, 'type' => 'int'],
    ['name' => 'maxAgents', 'read_only' => False, 'type' => 'int'],
    ['name' => 'isMaskImport', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'crackerBinaryTypeId', 'read_only' => False, 'type' => 'int'],
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
    ['name' => 'rightGroupId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'alias' => 'id'],
    ['name' => 'groupName', 'read_only' => False, 'type' => 'str(50)', 'alias' => 'name'],
    ['name' => 'permissions', 'read_only' => False, 'type' => 'dict', 'subtype' => 'bool', 'null' => True],
  ],
];
$CONF['Session'] = [
  'columns' => [
    ['name' => 'sessionId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'userId', 'read_only' => True, 'type' => 'int', 'protected' => True],
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
    ['name' => 'agentId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'taskId', 'read_only' => True, 'type' => 'int', 'protected' => True],
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
  ],
];
$CONF['Task'] = [
  'columns' => [
    ['name' => 'taskId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'taskName', 'read_only' => False, 'type' => 'str(256)'],
    ['name' => 'attackCmd', 'read_only' => False, 'type' => 'str(256)'],
    ['name' => 'chunkTime', 'read_only' => False, 'type' => 'int'],
    ['name' => 'statusTimer', 'read_only' => False, 'type' => 'int'],
    ['name' => 'keyspace', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'keyspaceProgress', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'priority', 'read_only' => False, 'type' => 'int'],
    ['name' => 'maxAgents', 'read_only' => False, 'type' => 'int'],
    ['name' => 'color', 'read_only' => False, 'type' => 'str(50)', 'null' => True],
    ['name' => 'isSmall', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'isCpuTask', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'useNewBench', 'read_only' => True, 'type' => 'bool'],
    ['name' => 'skipKeyspace', 'read_only' => True, 'type' => 'int64'],
    ['name' => 'crackerBinaryId', 'read_only' => True, 'type' => 'int'],
    ['name' => 'crackerBinaryTypeId', 'read_only' => True, 'type' => 'int'],
    ['name' => 'taskWrapperId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'isArchived', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'notes', 'read_only' => False, 'type' => 'str(65535)'],
    ['name' => 'staticChunks', 'read_only' => True, 'type' => 'int'],
    ['name' => 'chunkSize', 'read_only' => True, 'type' => 'int64'],
    ['name' => 'forcePipe', 'read_only' => True, 'type' => 'bool'],
    ['name' => 'usePreprocessor', 'read_only' => True, 'type' => 'int', 'alias' => 'preprocessorId'],
    ['name' => 'preprocessorCommand', 'read_only' => True, 'type' => 'str(256)'],
  ],
];
$CONF['TaskDebugOutput'] = [
  'columns' => [
    ['name' => 'taskDebugOutputId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'taskId', 'read_only' => True, 'type' => 'int'],
    ['name' => 'output', 'read_only' => True, 'type' => 'str(256)'],
  ],
];
$CONF['TaskWrapper'] = [
  'columns' => [
    ['name' => 'taskWrapperId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'priority', 'read_only' => False, 'type' => 'int'],
    ['name' => 'taskType', 'read_only' => True, 'type' => 'int', 'protected' => True, 'choices' => $FieldTaskTypeChoices],
    ['name' => 'hashlistId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'accessGroupId', 'read_only' => False, 'type' => 'int'],
    ['name' => 'taskWrapperName', 'read_only' => False, 'type' => 'str(100)'],
    ['name' => 'isArchived', 'read_only' => False, 'type' => 'bool'],
    ['name' => 'cracked', 'read_only' => False, 'type' => 'int', 'protected' => True],
  ],
];
$CONF['User'] = [
  'columns' => [
    ['name' => 'userId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'alias' => 'id'],
    ['name' => 'username', 'read_only' => False, 'type' => 'str(100)', 'alias' => 'name'],
    ['name' => 'email', 'read_only' => False, 'type' => 'str(150)'],
    ['name' => 'passwordHash', 'read_only' => True, 'type' => 'str(256)', 'protected' => True, 'private' => True],
    ['name' => 'passwordSalt', 'read_only' => True, 'protected' => True, 'type' => 'str(256)', 'private' => True],
    ['name' => 'isValid', 'read_only' => False, 'type' => 'bool', 'null' => true],
    ['name' => 'isComputedPassword', 'read_only' => True, 'type' => 'bool', 'protected' => True,],
    ['name' => 'lastLoginDate', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'registeredSince', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'sessionLifetime', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'rightGroupId', 'read_only' => False, 'type' => 'int', 'alias' => 'globalPermissionGroupId'],
    ['name' => 'yubikey', 'read_only' => True, 'type' => 'str(256)', 'protected' => True],
    ['name' => 'otp1', 'read_only' => True, 'type' => 'str(256)', 'protected' => True],
    ['name' => 'otp2', 'read_only' => True, 'type' => 'str(256)', 'protected' => True],
    ['name' => 'otp3', 'read_only' => True, 'type' => 'str(256)', 'protected' => True],
    ['name' => 'otp4', 'read_only' => True, 'type' => 'str(256)', 'protected' => True],
  ],
];
$CONF['Zap'] = [
  'columns' => [
    ['name' => 'zapId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'hash', 'read_only' => True, 'type' => 'str(65535)', 'protected' => True],
    ['name' => 'solveTime', 'read_only' => True, 'type' => 'int64', 'protected' => True],
    ['name' => 'agentId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'hashlistId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ],
];
//
// Relations
//
$CONF['AccessGroupUser'] = [
  'columns' => [
    ['name' => 'accessGroupUserId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'accessGroupId', 'read_only' => True, 'type' => 'int'],
    ['name' => 'userId', 'read_only' => True, 'type' => 'int'],
  ],
];
$CONF['AccessGroupAgent'] = [
  'columns' => [
    ['name' => 'accessGroupAgentId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'accessGroupId', 'read_only' => True, 'type' => 'int'],
    ['name' => 'agentId', 'read_only' => True, 'type' => 'int'],
  ],
];
$CONF['FileTask'] = [
  'columns' => [
    ['name' => 'fileTaskId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'fileId', 'read_only' => True, 'type' => 'int'],
    ['name' => 'taskId', 'read_only' => True, 'type' => 'int'],
  ],
];
$CONF['FilePretask'] = [
  'columns' => [
    ['name' => 'filePretaskId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'fileId', 'read_only' => True, 'type' => 'int'],
    ['name' => 'pretaskId', 'read_only' => True, 'type' => 'int'],
  ],
];
$CONF['SupertaskPretask'] = [
  'columns' => [
    ['name' => 'supertaskPretaskId', 'read_only' => True, 'type' => 'int', 'protected' => True],
    ['name' => 'supertaskId', 'read_only' => True, 'type' => 'int'],
    ['name' => 'pretaskId', 'read_only' => True, 'type' => 'int'],
  ],
];
$CONF['HashlistHashlist'] = [
  'columns' => [
    ['name' => 'hashlistHashlistId', 'read_only' => True, 'type' => 'int', 'protected' => True],    
    ['name' => 'parentHashlistId', 'read_only' => True, 'type' => 'int'],
    ['name' => 'hashlistId', 'read_only' => True, 'type' => 'int'],
  ],
];

foreach ($CONF as $NAME => $MODEL_CONF) {
  $COLUMNS = $MODEL_CONF['columns'];
  $class = file_get_contents(dirname(__FILE__) . "/AbstractModel.template.txt");
  $class = str_replace("__MODEL_NAME__", $NAME, $class);
  $vars = array();
  $init = array();
  $keyVal = array();
  $class = str_replace("__MODEL_PK__", $COLUMNS[0]['name'], $class);
  $features = array();
  $functions = array();
  $params = array();
  $variables = array();
  $crud_defines = array();
  foreach ($COLUMNS as $COLUMN) {
    $col = $COLUMN['name'];
    if (sizeof($vars) > 0) {
      $getter = "function get" . strtoupper($col[0]) . substr($col, 1) . "() {\n    return \$this->$col;\n  }";
      $setter = "function set" . strtoupper($col[0]) . substr($col, 1) . "(\$$col) {\n    \$this->$col = \$$col;\n  }";
      $functions[] = $getter;
      $functions[] = $setter;
    }
    $params[] = "\$$col";
    $vars[] = "private \$$col;";
    $init[] = "\$this->$col = \$$col;";

    if (array_key_exists("choices", $COLUMN)) { 
      $choicesVal = '[';
      foreach ($COLUMN['choices'] as $CHOICE) {
        $choicesVal .= $CHOICE['key'] . ' => "' . $CHOICE['label'] . '", ';
      }
      $choicesVal .= ']';
      
    } else {
      $choicesVal = '"unset"';
    }

    $features[] = "\$dict['$col'] = ['read_only' => " . ($COLUMN['read_only'] ? 'True' : "False") . ', ' . 
                                     '"type" => "' . $COLUMN['type'] . '", ' .
                                     '"subtype" => "' . (array_key_exists("subtype", $COLUMN) ? $COLUMN['subtype'] : 'unset') . '", ' .
                                     '"choices" => ' . $choicesVal . ', ' .
                                     '"null" => ' . (array_key_exists("null", $COLUMN) ? ($COLUMN['null'] ? 'True' : 'False') : 'False') . ', ' .
                                     '"pk" => ' . (($col == $COLUMNS[0]['name']) ? 'True' : 'False') . ', ' .
                                     '"protected" => ' . (array_key_exists("protected", $COLUMN) ? ($COLUMN['protected'] ? 'True' : 'False') : 'False') . ', ' .
                                     '"private" => ' . (array_key_exists("private", $COLUMN) ? ($COLUMN['private'] ? 'True' : 'False') : 'False') . ', ' .
                                     '"alias" => "' . (array_key_exists("alias", $COLUMN) ? $COLUMN['alias']  : $COLUMN['name']) . '"' . 
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
  
  if (true || !file_exists(dirname(__FILE__) . "/" . $NAME . ".class.php")) {
    file_put_contents(dirname(__FILE__) . "/" . $NAME . ".class.php", $class);
  }
  
  $class = file_get_contents(dirname(__FILE__) . "/AbstractModelFactory.template.txt");
  $class = str_replace("__MODEL_NAME__", $NAME, $class);
  $dict = array();
  $dict2 = array();
  foreach ($COLUMNS as $COLUMN) {
    $col = $COLUMN['name'];
    if (sizeof($dict) == 0) {
      $dict[] = "-1";
      $dict2[] = "\$dict['$col']";
    }
    else {
      $dict[] = "null";
      $dict2[] = "\$dict['$col']";
    }
  }
  $class = str_replace("__MODEL_DICT__", implode(", ", $dict), $class);
  $class = str_replace("__MODEL__DICT2__", implode(", ", $dict2), $class);
  
  if (true || !file_exists(dirname(__FILE__) . "/" . $NAME . "Factory.class.php")) {
    file_put_contents(dirname(__FILE__) . "/" . $NAME . "Factory.class.php", $class);
  }
}

$class = file_get_contents(dirname(__FILE__) . "/Factory.template.txt");
$static = array();
$functions = array();
foreach ($CONF as $NAME => $COLUMNS) {
  $lowerName = strtolower($NAME[0]) . substr($NAME, 1);
  $static[] = "private static \$" . $lowerName . "Factory = null;";
  $functions[] = "public static function get" . $NAME . "Factory() {\n    if (self::\$" . $lowerName . "Factory == null) {\n      \$f = new " . $NAME . "Factory();\n      self::\$" . $lowerName . "Factory = \$f;\n      return \$f;\n    } else {\n      return self::\$" . $lowerName . "Factory;\n    }\n  }";
}
$class = str_replace("__MODEL_STATIC__", implode("\n  ", $static), $class);
$class = str_replace("__MODEL_FUNCTIONS__", implode("\n  \n  ", $functions), $class);

file_put_contents(dirname(__FILE__) . "/../Factory.class.php", $class);


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
