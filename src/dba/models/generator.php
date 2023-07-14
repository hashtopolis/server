<?php

$CONF = array();

// entities
$CONF['AccessGroup'] = [
  'accessGroupId',
  'groupName'
];
$CONF['Agent'] = [
  'agentId',
  'agentName',
  'uid',
  'os',
  'devices',
  'cmdPars',
  'ignoreErrors',
  'isActive',
  'isTrusted',
  'token',
  'lastAct',
  'lastTime',
  'lastIp',
  'userId',
  'cpuOnly',
  'clientSignature'
];
$CONF['AgentBinary'] = [
  'agentBinaryId',
  'type',
  'version',
  'operatingSystems',
  'filename',
  'updateTrack',
  'updateAvailable'
];
$CONF['AgentError'] = [
  'agentErrorId',
  'agentId',
  'taskId',
  'chunkId',
  'time',
  'error'
];
$CONF['AgentStat'] = [
  'agentStatId',
  'agentId',
  'statType',
  'time',
  'value'
];
$CONF['AgentZap'] = [
  'agentZapId',
  'agentId',
  'lastZapId'
];
$CONF['Assignment'] = [
  'assignmentId',
  'taskId',
  'agentId',
  'benchmark',
];
$CONF['Benchmark'] = [
  'benchmarkId',
  'benchmarkValue',
  'attackParameters',
  'hardwareGroupId',
  'crackerBinaryId',
  'ttl',
  'hashmode',
  'benchmarkType',
];
$CONF['Chunk'] = [
  'chunkId',
  'taskId',
  'skip',
  'length',
  'agentId',
  'dispatchTime',
  'solveTime',
  'checkpoint',
  'progress',
  'state',
  'cracked',
  'speed'
];
$CONF['Config'] = [
  'configId',
  'configSectionId',
  'item',
  'value'
];
$CONF['ConfigSection'] = [
  'configSectionId',
  'sectionName'
];
$CONF['CrackerBinary'] = [
  'crackerBinaryId',
  'crackerBinaryTypeId',
  'version',
  'downloadUrl',
  'binaryName'
];
$CONF['CrackerBinaryType'] = [
  'crackerBinaryTypeId',
  'typeName',
  'isChunkingAvailable'
];
$CONF['File'] = [
  'fileId',
  'filename',
  'size',
  'isSecret',
  'fileType',
  'accessGroupId',
  'lineCount'
];
$CONF['Hash'] = [
  'hashId',
  'hashlistId',
  'hash',
  'salt',
  'plaintext',
  'timeCracked',
  'chunkId',
  'isCracked',
  'crackPos'
];
$CONF['HardwareGroup'] = [
  'hardwareGroupId',
  'devices',
  'benchmarkId',
];
$CONF['HashBinary'] = [
  'hashBinaryId',
  'hashlistId',
  'essid',
  'hash',
  'plaintext',
  'timeCracked',
  'chunkId',
  'isCracked',
  'crackPos'
];
$CONF['Hashlist'] = [
  'hashlistId',
  'hashlistName',
  'format',
  'hashTypeId',
  'hashCount',
  'saltSeparator',
  'cracked',
  'isSecret',
  'hexSalt',
  'isSalted',
  'accessGroupId',
  'notes',
  'brainId',
  'brainFeatures',
  'isArchived'
];
$CONF['HashType'] = [
  'hashTypeId',
  'description',
  'isSalted',
  'isSlowHash'
];
$CONF['LogEntry'] = [
  'logEntryId',
  'issuer',
  'issuerId',
  'level',
  'message',
  'time'
];
$CONF['NotificationSetting'] = [
  'notificationSettingId',
  'action',
  'objectId',
  'notification',
  'userId',
  'receiver',
  'isActive'
];
$CONF['Pretask'] = [
  'pretaskId',
  'taskName',
  'attackCmd',
  'chunkTime',
  'statusTimer',
  'color',
  'isSmall',
  'isCpuTask',
  'useNewBench',
  'priority',
  'maxAgents',
  'isMaskImport',
  'crackerBinaryTypeId'
];
$CONF['RegVoucher'] = [
  'regVoucherId',
  'voucher',
  'time'
];
$CONF['RightGroup'] = [
  'rightGroupId',
  'groupName',
  'permissions'
];
$CONF['Session'] = [
  'sessionId',
  'userId',
  'sessionStartDate',
  'lastActionDate',
  'isOpen',
  'sessionLifetime',
  'sessionKey'
];
$CONF['StoredValue'] = [
  'storedValueId',
  'val'
];
$CONF['Supertask'] = [
  'supertaskId',
  'supertaskName'
];
$CONF['Task'] = [
  'taskId',
  'taskName',
  'attackCmd',
  'chunkTime',
  'statusTimer',
  'keyspace',
  'keyspaceProgress',
  'priority',
  'maxAgents',
  'color',
  'isSmall',
  'isCpuTask',
  'useNewBench',
  'skipKeyspace',
  'crackerBinaryId',
  'crackerBinaryTypeId',
  'taskWrapperId',
  'isArchived',
  'notes',
  'staticChunks',
  'chunkSize',
  'forcePipe',
  'usePreprocessor',
  'preprocessorCommand'
];
$CONF['TaskDebugOutput'] = [
  'taskDebugOutputId',
  'taskId',
  'output'
];
$CONF['TaskWrapper'] = [
  'taskWrapperId',
  'priority',
  'taskType',
  'hashlistId',
  'accessGroupId',
  'taskWrapperName',
  'isArchived',
  'cracked'
];
$CONF['User'] = [
  'userId',
  'username',
  'email',
  'passwordHash',
  'passwordSalt',
  'isValid',
  'isComputedPassword',
  'lastLoginDate',
  'registeredSince',
  'sessionLifetime',
  'rightGroupId',
  'yubikey',
  'otp1',
  'otp2',
  'otp3',
  'otp4'
];
$CONF['Zap'] = [
  'zapId',
  'hash',
  'solveTime',
  'agentId',
  'hashlistId'
];
$CONF['ApiKey'] = [
  'apiKeyId',
  'startValid',
  'endValid',
  'accessKey',
  'accessCount',
  'userId',
  'apiGroupId'
];
$CONF['ApiGroup'] = [
  'apiGroupId',
  'permissions',
  'name'
];
$CONF['FileDownload'] = [
  'fileDownloadId',
  'time',
  'fileId',
  'status'
];
$CONF['FileDelete'] = [
  'fileDeleteId',
  'filename',
  'time'
];
$CONF['HealthCheck'] = [
  'healthCheckId',
  'time',
  'status',
  'checkType',
  'hashtypeId',
  'crackerBinaryId',
  'expectedCracks',
  'attackCmd'
];
$CONF['HealthCheckAgent'] = [
  'healthCheckAgentId',
  'healthCheckId',
  'agentId',
  'status',
  'cracked',
  'numGpus',
  'start',
  'end',
  'errors'
];
$CONF['Speed'] = [
  'speedId',
  'agentId',
  'taskId',
  'speed',
  'time'
];
$CONF['Preprocessor'] = [
  'preprocessorId',
  'name',
  'url',
  'binaryName',
  'keyspaceCommand',
  'skipCommand',
  'limitCommand'
];

// relations
$CONF['AccessGroupUser'] = [
  'accessGroupUserId',
  'accessGroupId',
  'userId'
];
$CONF['AccessGroupAgent'] = [
  'accessGroupAgentId',
  'accessGroupId',
  'agentId'
];
$CONF['FileTask'] = [
  'fileTaskId',
  'fileId',
  'taskId'
];
$CONF['FilePretask'] = [
  'filePretaskId',
  'fileId',
  'pretaskId'
];
$CONF['SupertaskPretask'] = [
  'supertaskPretaskId',
  'supertaskId',
  'pretaskId'
];
$CONF['HashlistHashlist'] = [
  'hashlistHashlistId',
  'parentHashlistId',
  'hashlistId'
];

foreach ($CONF as $NAME => $COLUMNS) {
  $class = file_get_contents(dirname(__FILE__) . "/AbstractModel.template.txt");
  $class = str_replace("__MODEL_NAME__", $NAME, $class);
  $vars = array();
  $init = array();
  $keyVal = array();
  $class = str_replace("__MODEL_PK__", $COLUMNS[0], $class);
  $functions = array();
  $params = array();
  $variables = array();
  foreach ($COLUMNS as $col) {
    if (sizeof($vars) > 0) {
      $getter = "function get" . strtoupper($col[0]) . substr($col, 1) . "() {\n    return \$this->$col;\n  }";
      $setter = "function set" . strtoupper($col[0]) . substr($col, 1) . "(\$$col) {\n    \$this->$col = \$$col;\n  }";
      $functions[] = $getter;
      $functions[] = $setter;
    }
    $params[] = "\$$col";
    $vars[] = "private \$$col;";
    $init[] = "\$this->$col = \$$col;";
    $keyVal[] = "\$dict['$col'] = \$this->$col;";
    $variables[] = "const " . makeConstant($col) . " = \"$col\";";
    
  }
  $class = str_replace("__MODEL_PARAMS__", implode(", ", $params), $class);
  $class = str_replace("__MODEL_VARS__", implode("\n  ", $vars), $class);
  $class = str_replace("__MODEL_PARAMS_INIT__", implode("\n    ", $init), $class);
  $class = str_replace("__MODEL_KEY_VAL__", implode("\n    ", $keyVal), $class);
  $class = str_replace("__MODEL_GETTER_SETTER__", implode("\n  \n  ", $functions), $class);
  $class = str_replace("__MODEL_VARIABLE_NAMES__", implode("\n  ", $variables), $class);
  
  if (true || !file_exists(dirname(__FILE__) . "/" . $NAME . ".class.php")) {
    file_put_contents(dirname(__FILE__) . "/" . $NAME . ".class.php", $class);
  }
  
  $class = file_get_contents(dirname(__FILE__) . "/AbstractModelFactory.template.txt");
  $class = str_replace("__MODEL_NAME__", $NAME, $class);
  $dict = array();
  $dict2 = array();
  foreach ($COLUMNS as $col) {
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

