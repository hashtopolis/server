<?php

use phpDocumentor\Reflection\PseudoTypes\True_;

$CONF = array();

require_once(dirname(__FILE__) . "/../../inc/defines/userApi.php");

// Type: describes what kind of type the attribute is
// Subtype: in case type is dict, the first level of the dict will also be checked.
// Read_only: determines that the attribute can only be set during creation
// Protected: cannot be set at any point but can be returned. For example 'id'
// Private: cannot be set at any point, and can also not be returned. For example: passwordhash
// Pk: primary key
// Null: means that an attribute is optional.
// Alias: used for setting new names for attributes. This is for the transition between APIv1 and APIv2.
//        Alias determines the name for the attribute as it should be called by APIv2

// entities
// FIXME: Add correct read_only mapping to relevant fields
$CONF['AccessGroup'] = [
  ['name' => 'accessGroupId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'groupName', 'read_only' => False, 'type' => 'str(50)']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['Agent'] = [
  ['name' => 'agentId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'agentName', 'read_only' => False, 'type' => 'str(100)'],
  ['name' => 'uid', 'read_only' => False, 'type' => 'str(100)'],
  ['name' => 'os', 'read_only' => False, 'type' => 'int'],
  ['name' => 'devices', 'read_only' => False, 'type' => 'str(65535)'],
  ['name' => 'cmdPars', 'read_only' => False, 'type' => 'str(256)'],
  ['name' => 'hardwareGroupId', 'read_only' => True, 'type' => 'int'],
  ['name' => 'ignoreErrors', 'read_only' => False, 'type' => 'bool'],
  ['name' => 'isActive', 'read_only' => False, 'type' => 'bool'],
  ['name' => 'isTrusted', 'read_only' => False, 'type' => 'bool'],
  ['name' => 'token', 'read_only' => False, 'type' => 'str(30)'],
  ['name' => 'lastAct', 'read_only' => False, 'type' => 'str(50)'],
  ['name' => 'lastTime', 'read_only' => False, 'type' => 'int64'],
  ['name' => 'lastIp', 'read_only' => False, 'type' => 'str(50)'],
  ['name' => 'userId', 'read_only' => False, 'type' => 'int', 'null' => True],
  ['name' => 'cpuOnly', 'read_only' => False, 'type' => 'bool'],
  ['name' => 'clientSignature', 'read_only' => False, 'type' => 'str(50)']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['AgentBinary'] = [
  ['name' => 'agentBinaryId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'type', 'read_only' => False, 'type' => 'str(20)'],
  ['name' => 'version', 'read_only' => False, 'type' => 'str(20)'],
  ['name' => 'operatingSystems', 'read_only' => False, 'type' => 'str(50)'],
  ['name' => 'filename', 'read_only' => False, 'type' => 'str(50)'],
  ['name' => 'updateTrack', 'read_only' => False, 'type' => 'str(20)'],
  ['name' => 'updateAvailable', 'read_only' => True, 'type' => 'str(20)', 'protected' => True]
];
$CONF['AgentError'] = [
  ['name' => 'agentErrorId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'agentId', 'read_only' => True, 'type' => 'int'],
  ['name' => 'taskId', 'read_only' => True, 'type' => 'int'],
  ['name' => 'chunkId', 'read_only' => True, 'type' => 'int'],
  ['name' => 'time', 'read_only' => True, 'type' => 'int64'],
  ['name' => 'error', 'read_only' => True, 'type' => 'str(65535)']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['AgentStat'] = [
  ['name' => 'agentStatId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'agentId', 'read_only' => True, 'protected' => True, 'type' => 'int'],
  ['name' => 'statType', 'read_only' => True, 'protected' => True, 'type' => 'int'],
  ['name' => 'time', 'read_only' => True, 'protected' => True, 'type' => 'int64'],
  ['name' => 'value', 'read_only' => True, 'protected' => True, 'type' => 'str(128)']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['AgentZap'] = [
  ['name' => 'agentZapId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'agentId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'lastZapId', 'read_only' => False, 'type' => 'str(128)']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['Assignment'] = [
  ['name' => 'assignmentId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'taskId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'agentId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'benchmark', 'read_only' => False, 'type' => 'str(50)'],
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['Benchmark'] = [
  ['name' => 'benchmarkId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'benchmarkValue', 'read_only' => False, 'type' => 'str(256)'],
  ['name' => 'hardwareGroupId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'crackerBinaryId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'ttl', 'read_only' => False, 'type' => 'int'],
  ['name' => 'hashmode', 'read_only' => False, 'type' => 'int'],
  ['name' => 'benchmarkType', 'read_only' => False, 'type' => 'str(10)'],
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['Chunk'] = [
  ['name' => 'chunkId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'taskId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'skip', 'read_only' => False, 'type' => 'uint64'],
  ['name' => 'length', 'read_only' => False, 'type' => 'uint64'],
  ['name' => 'agentId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'dispatchTime', 'read_only' => False, 'type' => 'int64'],
  ['name' => 'solveTime', 'read_only' => False, 'type' => 'int64'],
  ['name' => 'checkpoint', 'read_only' => False, 'type' => 'int64'],
  ['name' => 'progress', 'read_only' => False, 'type' => 'int'],
  ['name' => 'state', 'read_only' => False, 'type' => 'int'],
  ['name' => 'cracked', 'read_only' => False, 'type' => 'int'],
  ['name' => 'speed', 'read_only' => False, 'type' => 'int64']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['Config'] = [
  ['name' => 'configId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'configSectionId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'item', 'read_only' => False, 'type' => 'str(128)'],
  ['name' => 'value', 'read_only' => False, 'type' => 'str(65535)']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['ConfigSection'] = [
  ['name' => 'configSectionId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'sectionName', 'read_only' => False, 'type' => 'str(100)']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['CrackerBinary'] = [
  ['name' => 'crackerBinaryId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'crackerBinaryTypeId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'version', 'read_only' => False, 'type' => 'str(20)'],
  ['name' => 'downloadUrl', 'read_only' => False, 'type' => 'str(150)'],
  ['name' => 'binaryName', 'read_only' => False, 'type' => 'str(50)']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['CrackerBinaryType'] = [
  ['name' => 'crackerBinaryTypeId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'typeName', 'read_only' => False, 'type' => 'str(30)'],
  ['name' => 'isChunkingAvailable', 'read_only' => False, 'type' => 'bool']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['File'] = [
  ['name' => 'fileId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'filename', 'read_only' => False, 'type' => 'str(100)'],
  ['name' => 'size', 'read_only' => True, 'type' => 'int64', 'protected' => True],
  ['name' => 'isSecret', 'read_only' => False, 'type' => 'bool'],
  ['name' => 'fileType', 'read_only' => False, 'type' => 'int'],
  ['name' => 'accessGroupId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'lineCount', 'read_only' => True, 'type' => 'int64', 'protected' => True]
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['Hash'] = [
  ['name' => 'hashId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'hashlistId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'hash', 'read_only' => False, 'type' => 'str(65535)'],
  ['name' => 'salt', 'read_only' => False, 'type' => 'str(256)'],
  ['name' => 'plaintext', 'read_only' => False, 'type' => 'str(256)'],
  ['name' => 'timeCracked', 'read_only' => False, 'type' => 'int64'],
  ['name' => 'chunkId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'isCracked', 'read_only' => False, 'type' => 'bool'],
  ['name' => 'crackPos', 'read_only' => False, 'type' => 'int64']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['HashBinary'] = [
  ['name' => 'hashBinaryId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'hashlistId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'essid', 'read_only' => False, 'type' => 'str(100)'],
  ['name' => 'hash', 'read_only' => False, 'type' => 'str(4294967295)'],
  ['name' => 'plaintext', 'read_only' => False, 'type' => 'str(1024)'],
  ['name' => 'timeCracked', 'read_only' => False, 'type' => 'int64'],
  ['name' => 'chunkId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'isCracked', 'read_only' => False, 'type' => 'bool'],
  ['name' => 'crackPos', 'read_only' => False, 'type' => 'int64']
];
$CONF['Hashlist'] = [
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
  ['name' => 'isArchived', 'read_only' => False, 'type' => 'bool']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['HashType'] = [
  ['name' => 'hashTypeId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'description', 'read_only' => False, 'type' => 'str(256)'],
  ['name' => 'isSalted', 'read_only' => False, 'type' => 'bool'],
  ['name' => 'isSlowHash', 'read_only' => False, 'type' => 'bool']
];
$CONF['HardwareGroup'] = [
  ['name' => 'hardwareGroupId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'devices', 'read_only' => False, 'type' => 'str(65000)'],
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['LogEntry'] = [
  ['name' => 'logEntryId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'issuer', 'read_only' => False, 'type' => 'str(50)'],
  ['name' => 'issuerId', 'read_only' => False, 'type' => 'str(50)'],
  ['name' => 'level', 'read_only' => False, 'type' => 'str(50)'],
  ['name' => 'message', 'read_only' => False, 'type' => 'str(65535)'],
  ['name' => 'time', 'read_only' => True, 'type' => 'int64', 'protected' => True]
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['NotificationSetting'] = [
  ['name' => 'notificationSettingId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'action', 'read_only' => False, 'type' => 'str(50)'],
  ['name' => 'objectId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'notification', 'read_only' => False, 'type' => 'str(50)'],
  ['name' => 'userId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'receiver', 'read_only' => False, 'type' => 'str(256)'],
  ['name' => 'isActive', 'read_only' => False, 'type' => 'bool']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['Pretask'] = [
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
  ['name' => 'crackerBinaryTypeId', 'read_only' => False, 'type' => 'int']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['RegVoucher'] = [
  ['name' => 'regVoucherId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'voucher', 'read_only' => False, 'type' => 'str(100)'],
  ['name' => 'time', 'read_only' => True, 'type' => 'int64', 'protected' => True]
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['RightGroup'] = [
  ['name' => 'rightGroupId', 'read_only' => True, 'type' => 'int', 'protected' => True, 'alias' => 'id'],
  ['name' => 'groupName', 'read_only' => False, 'type' => 'str(50)', 'alias' => 'name'],
  ['name' => 'permissions', 'read_only' => False, 'type' => 'dict', 'subtype' => 'bool', 'null' => True]
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['Session'] = [
  ['name' => 'sessionId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'userId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'sessionStartDate', 'read_only' => False, 'type' => 'int64'],
  ['name' => 'lastActionDate', 'read_only' => False, 'type' => 'int64'],
  ['name' => 'isOpen', 'read_only' => False, 'type' => 'bool'],
  ['name' => 'sessionLifetime', 'read_only' => False, 'type' => 'int'],
  ['name' => 'sessionKey', 'read_only' => False, 'type' => 'str(256)']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['StoredValue'] = [
  ['name' => 'storedValueId', 'read_only' => True, 'type' => 'str(50)', 'protected' => True],
  ['name' => 'val', 'read_only' => False, 'type' => 'str(256)']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['Supertask'] = [
  ['name' => 'supertaskId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'supertaskName', 'read_only' => False, 'type' => 'str(50)']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['Task'] = [
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
  ['name' => 'preprocessorCommand', 'read_only' => True, 'type' => 'str(256)']
];
$CONF['TaskDebugOutput'] = [
  ['name' => 'taskDebugOutputId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'taskId', 'read_only' => True, 'type' => 'int'],
  ['name' => 'output', 'read_only' => True, 'type' => 'str(256)']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['TaskWrapper'] = [
  ['name' => 'taskWrapperId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'priority', 'read_only' => False, 'type' => 'int'],
  ['name' => 'maxAgents', 'read_only' => False, 'type' => 'int'],
  ['name' => 'taskType', 'read_only' => False, 'type' => 'int'],
  ['name' => 'hashlistId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'accessGroupId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'taskWrapperName', 'read_only' => False, 'type' => 'str(100)'],
  ['name' => 'isArchived', 'read_only' => False, 'type' => 'bool'],
  ['name' => 'cracked', 'read_only' => False, 'type' => 'int']
];
$CONF['User'] = [
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
  ['name' => 'otp4', 'read_only' => True, 'type' => 'str(256)', 'protected' => True]
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['Zap'] = [
  ['name' => 'zapId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'hash', 'read_only' => False, 'type' => 'str(65535)'],
  ['name' => 'solveTime', 'read_only' => False, 'type' => 'int64'],
  ['name' => 'agentId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'hashlistId', 'read_only' => False, 'type' => 'int']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['ApiKey'] = [
  ['name' => 'apiKeyId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'startValid', 'read_only' => False, 'type' => 'int64'],
  ['name' => 'endValid', 'read_only' => False, 'type' => 'int64'],
  ['name' => 'accessKey', 'read_only' => False, 'type' => 'str(256)'],
  ['name' => 'accessCount', 'read_only' => False, 'type' => 'int'],
  ['name' => 'userId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'apiGroupId', 'read_only' => False, 'type' => 'int']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['ApiGroup'] = [
  ['name' => 'apiGroupId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'permissions', 'read_only' => False, 'type' => 'str(65535)'],
  ['name' => 'name', 'read_only' => False, 'type' => 'str(100)']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['FileDownload'] = [
  ['name' => 'fileDownloadId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'time', 'read_only' => False, 'type' => 'int64'],
  ['name' => 'fileId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'status', 'read_only' => False, 'type' => 'int']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['FileDelete'] = [
  ['name' => 'fileDeleteId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'filename', 'read_only' => False, 'type' => 'str(256)'],
  ['name' => 'time', 'read_only' => False, 'type' => 'int64']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['HealthCheck'] = [
  ['name' => 'healthCheckId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'time', 'read_only' => True, 'type' => 'int64', 'protected' => True],
  ['name' => 'status', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'checkType', 'read_only' => False, 'type' => 'int'],
  ['name' => 'hashtypeId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'crackerBinaryId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'expectedCracks', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'attackCmd', 'read_only' => True, 'type' => 'str(256)', 'protected' => True]
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['HealthCheckAgent'] = [
  ['name' => 'healthCheckAgentId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'healthCheckId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'agentId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'status', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'cracked', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'numGpus', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'start', 'read_only' => True, 'type' => 'int64', 'protected' => True],
  ['name' => 'end', 'read_only' => True, 'type' => 'int64', 'protected' => True],
  ['name' => 'errors', 'read_only' => True, 'type' => 'str(65535)', 'protected' => True]
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['Speed'] = [
  ['name' => 'speedId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'agentId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'taskId', 'read_only' => False, 'type' => 'int'],
  ['name' => 'speed', 'read_only' => False, 'type' => 'int64'],
  ['name' => 'time', 'read_only' => False, 'type' => 'int64']
];
// FIXME: Add correct read_only mapping to relevant fields
$CONF['Preprocessor'] = [
  ['name' => 'preprocessorId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'name', 'read_only' => False, 'type' => 'str(256)'],
  ['name' => 'url', 'read_only' => False, 'type' => 'str(512)'],
  ['name' => 'binaryName', 'read_only' => False, 'type' => 'str(256)'],
  ['name' => 'keyspaceCommand', 'read_only' => False, 'type' => 'str(256)'],
  ['name' => 'skipCommand', 'read_only' => False, 'type' => 'str(256)'],
  ['name' => 'limitCommand', 'read_only' => False, 'type' => 'str(256)']
];

// relations
$CONF['AccessGroupUser'] = [
  ['name' => 'accessGroupUserId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'accessGroupId', 'read_only' => True, 'type' => 'int'],
  ['name' => 'userId', 'read_only' => True, 'type' => 'int']
];
$CONF['AccessGroupAgent'] = [
  ['name' => 'accessGroupAgentId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'accessGroupId', 'read_only' => True, 'type' => 'int'],
  ['name' => 'agentId', 'read_only' => True, 'type' => 'int']
];
$CONF['FileTask'] = [
  ['name' => 'fileTaskId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'fileId', 'read_only' => True, 'type' => 'int'],
  ['name' => 'taskId', 'read_only' => True, 'type' => 'int']
];
$CONF['FilePretask'] = [
  ['name' => 'filePretaskId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'fileId', 'read_only' => True, 'type' => 'int'],
  ['name' => 'pretaskId', 'read_only' => True, 'type' => 'int']
];
$CONF['SupertaskPretask'] = [
  ['name' => 'supertaskPretaskId', 'read_only' => True, 'type' => 'int', 'protected' => True],
  ['name' => 'supertaskId', 'read_only' => True, 'type' => 'int'],
  ['name' => 'pretaskId', 'read_only' => True, 'type' => 'int']
];
$CONF['HashlistHashlist'] = [
  ['name' => 'hashlistHashlistId', 'read_only' => True, 'type' => 'int', 'protected' => True],    
  ['name' => 'parentHashlistId', 'read_only' => True, 'type' => 'int'],
  ['name' => 'hashlistId', 'read_only' => True, 'type' => 'int']
];

foreach ($CONF as $NAME => $COLUMNS) {
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
    $features[] = "\$dict['$col'] = ['read_only' => " . ($COLUMN['read_only'] ? 'True' : "False") . ', ' . 
                                     '"type" => "' . $COLUMN['type'] . '", ' .
                                     '"subtype" => "' . (array_key_exists("subtype", $COLUMN) ? $COLUMN['subtype'] : 'unset') . '", ' .
                                     '"null" => ' . (array_key_exists("null", $COLUMN) ? ($COLUMN['null'] ? 'True' : 'False') : 'False') . ', ' .
                                     '"pk" => ' . (($col == $COLUMNS[0]['name']) ? 'True' : 'False') . ', ' .
                                     '"protected" => ' . (array_key_exists("protected", $COLUMN) ? ($COLUMN['protected'] ? 'True' : 'False') : 'False') . ', ' .
                                     '"private" => ' . (array_key_exists("private", $COLUMN) ? ($COLUMN['private'] ? 'True' : 'False') : 'False') . ', ' .
                                     '"alias" => "' . (array_key_exists("alias", $COLUMN) ? $COLUMN['alias']  : $COLUMN['name']) . '"' . 
                                    '];';
    $keyVal[] = "\$dict['$col'] = \$this->$col;";
    $variables[] = "const " . makeConstant($col) . " = \"$col\";";
    
  }
  $class = str_replace("__MODEL_PARAMS__", implode(", ", $params), $class);
  $class = str_replace("__MODEL_VARS__", implode("\n  ", $vars), $class);
  $class = str_replace("__MODEL_PARAMS_INIT__", implode("\n    ", $init), $class);
  $class = str_replace("__MODEL_KEY_VAL__", implode("\n    ", $keyVal), $class);
  $class = str_replace("__MODEL_FEATURES__", implode("\n    ", $features), $class);
  $class = str_replace("__MODEL_GETTER_SETTER__", implode("\n  \n  ", $functions), $class);
  $class = str_replace("__MODEL_VARIABLE_NAMES__", implode("\n  ", $variables), $class);
  
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
