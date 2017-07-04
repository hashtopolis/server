<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

$CONF = array();

// Configure the required models here
$CONF['Agent'] = array(
  'agentId',
  'agentName',
  'uid',
  'os',
  'gpus',
  'hcVersion',
  'cmdPars',
  'ignoreErrors',
  'isActive',
  'isTrusted',
  'token',
  'lastAct',
  'lastTime',
  'lastIp',
  'userId',
  'cpuOnly'
);
$CONF['AgentBinary'] = array(
  'agentBinaryId',
  'type',
  'version',
  'operatingSystems',
  'filename'
);
$CONF['Assignment'] = array(
  'assignmentId',
  'taskId',
  'agentId',
  'benchmark',
);
$CONF['Chunk'] = array(
  'chunkId',
  'taskId',
  'skip',
  'length',
  'agentId',
  'dispatchTime',
  'progress',
  'rprogress',
  'state',
  'cracked',
  'solveTime',
  'speed'
);
$CONF['Config'] = array(
  'configId',
  'item',
  'value'
);
$CONF['AgentError'] = array(
  'agentErrorId',
  'agentId',
  'taskId',
  'time',
  'error'
);
$CONF['File'] = array(
  'fileId',
  'filename',
  'size',
  'secret',
  'fileType'
);
$CONF['HashcatRelease'] = array(
  'hashcatReleaseId',
  'version',
  'time',
  'url',
  'rootdir'
);
$CONF['Hash'] = array(
  'hashId',
  'hashlistId',
  'hash',
  'salt',
  'plaintext',
  'time',
  'chunkId',
  'isCracked'
);
$CONF['HashBinary'] = array(
  'hashBinaryId',
  'hashlistId',
  'essid',
  'hash',
  'plaintext',
  'time',
  'chunkId',
  'isCracked'
);
$CONF['Hashlist'] = array(
  'hashlistId',
  'hashlistName',
  'format',
  'hashTypeId',
  'hashCount',
  'saltSeparator',
  'cracked',
  'secret',
  'hexSalt',
  'isSalted'
);
$CONF['HashlistAgent'] = array(
  'hashlistAgentId',
  'hashlistId',
  'agentId'
);
$CONF['HashType'] = array(
  'hashTypeId',
  'description',
  'isSalted'
);
$CONF['RegVoucher'] = array(
  'regVoucherId',
  'voucher',
  'time'
);
$CONF['SuperHashlistHashlist'] = array(
  'superHashlistHashlistId',
  'superHashlistId',
  'hashlistId'
);
$CONF['TaskFile'] = array(
  'taskFileId',
  'taskId',
  'fileId'
);
$CONF['Task'] = array(
  'taskId',
  'taskName',
  'attackCmd',
  'hashlistId',
  'chunkTime',
  'statusTimer',
  'keyspace',
  'progress',
  'priority',
  'color',
  'isSmall',
  'isCpuTask',
  'useNewBench',
  'skipKeyspace'
);
$CONF['Supertask'] = array(
  'supertaskId',
  'supertaskName'
);
$CONF['SupertaskTask'] = array(
  'supertaskTaskId',
  'taskId',
  'supertaskId'
);

$CONF['User'] = array(
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
);
$CONF['Session'] = array(
  'sessionId',
  'userId',
  'sessionStartDate',
  'lastActionDate',
  'isOpen',
  'sessionLifetime',
  'sessionKey'
);
$CONF['RightGroup'] = array(
  'rightGroupId',
  'groupName',
  'level'
);
$CONF['Zap'] = array(
  'zapId',
  'hash',
  'solveTime',
  'agentId',
  'hashlistId'
);
$CONF['AgentZap'] = array(
  'agentId',
  'lastZapId'
);
$CONF['StoredValue'] = array(
  'storedValueId',
  'val'
);
$CONF['LogEntry'] = array(
  'logEntryId',
  'issuer',
  'issuerId',
  'level',
  'message',
  'time'
);
$CONF['NotificationSetting'] = array(
  'notificationSettingId',
  'action',
  'objectId',
  'notification',
  'userId',
  'receiver',
  'isActive'
);

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
      $getter = "function get" . strtoupper($col[0]) . substr($col, 1) . "(){\n    return \$this->$col;\n  }";
      $setter = "function set" . strtoupper($col[0]) . substr($col, 1) . "(\$$col){\n    \$this->$col = \$$col;\n  }";
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
  
  if (!file_exists(dirname(__FILE__) . "/" . $NAME . ".class.php")) {
    file_put_contents(dirname(__FILE__) . "/" . $NAME . ".class.php", $class);
  }
  
  $class = file_get_contents(dirname(__FILE__) . "/AbstractModelFactory.template.txt");
  $class = str_replace("__MODEL_NAME__", $NAME, $class);
  $dict = array();
  $dict2 = array();
  foreach ($COLUMNS as $col) {
    if (sizeof($dict) == 0) {
      $dict[] = "-1";
      $dict2[] = "\$pk";
    }
    else {
      $dict[] = "null";
      $dict2[] = "\$dict['$col']";
    }
  }
  $class = str_replace("__MODEL_DICT__", implode(", ", $dict), $class);
  $class = str_replace("__MODEL__DICT2__", implode(", ", $dict2), $class);
  
  if (!file_exists(dirname(__FILE__) . "/" . $NAME . "Factory.class.php")) {
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

