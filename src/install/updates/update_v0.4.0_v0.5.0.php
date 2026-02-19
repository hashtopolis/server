<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\AccessGroupAgent;
use DBA\AccessGroupUser;
use DBA\Agent;
use DBA\AgentFactory;
use DBA\File;
use DBA\FilePretask;
use DBA\Hash;
use DBA\HashBinary;
use DBA\Hashlist;
use DBA\HashlistHashlist;
use DBA\HashType;
use DBA\Pretask;
use DBA\Supertask;
use DBA\SupertaskPretask;
use DBA\User;
use DBA\Factory;

ini_set("memory_limit", "2G");

/** @noinspection PhpIncludeInspection */
@include(dirname(__FILE__) . "/../../inc/db.php");
include(dirname(__FILE__) . "/../../dba/init.php");

echo "WARNING!!!!\n";
echo "This update contains some drastic changes and everything except users, agents, files, hashlists, pretasks and supertasks will not be kept!\n";
echo "Backup the database before applying this update, in case something does not run as expected!\n";
echo "Do this upgrade ONLY if you really don't want to do a clean installation instead!\n";
echo "NOTE: If you have a lot of hashes imported, make sure that this process gets enough ram assigned!\n";
echo "Enter 'AGREE' to continue... \n";
$confirm = trim(fgets(STDIN));
if ($confirm != 'AGREE') {
  die("Aborted!\n");
}

$aF = new AgentFactory();
$DB = $aF->getDB();
$DB->beginTransaction();

echo "Apply updates...\n";

echo "Disable checks... ";
$DB->exec("SET foreign_key_checks = 0;");
echo "OK\n";

echo "Read Config table... ";
$stmt = $DB->query("SELECT * FROM `Config` WHERE 1");
$configs = $stmt->fetchAll();
// read some important values
$saved = array();
foreach ($configs as $config) {
  $saved[$config['item']] = $config['value'];
}
echo "OK\n";

echo "Read users... ";
$stmt = $DB->query("SELECT * FROM `User` WHERE 1");
$users = $stmt->fetchAll();
echo "OK\n";

echo "Read agents... ";
$stmt = $DB->query("SELECT * FROM `Agent` WHERE 1");
$agents = $stmt->fetchAll();
echo "OK\n";

echo "Read files... ";
$stmt = $DB->query("SELECT * FROM `File` WHERE 1");
$files = $stmt->fetchAll();
echo "OK\n";

echo "Read taskFile... ";
$stmt = $DB->query("SELECT * FROM `TaskFile` WHERE 1");
$taskFiles = $stmt->fetchAll();
echo "OK\n";

echo "Read hashlists... ";
$stmt = $DB->query("SELECT * FROM `Hashlist` WHERE 1");
$hashlists = $stmt->fetchAll();
echo "OK\n";

echo "Read superhashlists... ";
$stmt = $DB->query("SELECT * FROM `SuperHashlistHashlist` WHERE 1");
$superhashlistsHashlists = $stmt->fetchAll();
echo "OK\n";

echo "Read hashes... ";
$stmt = $DB->query("SELECT * FROM `Hash` WHERE 1");
$hashes = $stmt->fetchAll();
echo "OK\n";

echo "Read binary hashes... ";
$stmt = $DB->query("SELECT * FROM `HashBinary` WHERE 1");
$binaryHashes = $stmt->fetchAll();
echo "OK\n";

echo "Read tasks... ";
$stmt = $DB->query("SELECT * FROM `Task` WHERE 1");
$tasks = $stmt->fetchAll();
echo "OK\n";

echo "Read supertasks... ";
$stmt = $DB->query("SELECT * FROM `Supertask` WHERE 1");
$supertasks = $stmt->fetchAll();
echo "OK\n";

echo "Read supertaskTasks... ";
$stmt = $DB->query("SELECT * FROM `SupertaskTask` WHERE 1");
$supertaskTasks = $stmt->fetchAll();
echo "OK\n";

echo "Read hash types... ";
$stmt = $DB->query("SELECT * FROM `HashType` WHERE 1");
$hashTypes = $stmt->fetchAll();
echo "OK\n";

echo "All data loaded! Removing old tables... ";
$DB->exec("SET @tables = NULL;
SELECT GROUP_CONCAT(table_schema, '.', table_name) INTO @tables
  FROM information_schema.tables
  WHERE table_schema = '" . StartupConfig::getInstance()->getDatabaseDB() . "';

SET @tables = CONCAT('DROP TABLE ', @tables);
PREPARE stmt FROM @tables;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;"
);
echo "OK\n";

echo "Importing new scheme... ";
$DB->exec(file_get_contents(dirname(__FILE__) . "/../hashtopolis.sql"));
echo "OK\n";

echo "Reload full include... (Warning about sessions might show up, which can be ignored)";
require_once(dirname(__FILE__) . "/../../inc/startup/load.php");
echo "OK\n";

echo "Starting with refilling data...\n";

echo "Create default access group... ";
$DB->exec("INSERT INTO `AccessGroup` (`accessGroupId`, `groupName`) VALUES (1, 'Default Group');");
echo "OK\n";

echo "Add Hashcat to CrackerBinaryType table... ";
$DB->exec("INSERT INTO `CrackerBinaryType` (`crackerBinaryTypeId`, `typeName`, `isChunkingAvailable`) VALUES (1, 'Hashcat', 1);");
echo "OK\n";

echo "Save hash types... ";
$t = [];
foreach ($hashTypes as $hashType) {
  $ht = Factory::getHashTypeFactory()->get($hashType['hashTypeId']);
  if ($ht == null) {
    $t[] = new HashType($hashType['hashTypeId'], $hashType['description'], $hashType['isSalted']);
  }
}
if (sizeof($t) > 0) {
  Factory::getHashTypeFactory()->massSave($t);
}
echo "OK\n";

echo "Save users... ";
$u = [];
$ug = [];
foreach ($users as $user) {
  $u[] = new User($user['userId'], $user['username'], $user['email'], $user['passwordHash'], $user['passwordSalt'], $user['isValid'], $user['isComputedPassword'], $user['lastLoginDate'], $user['registeredSince'], $user['sessionLifetime'], $user['rightGroupId'], $user['yubikey'], $user['otp1'], $user['otp2'], $user['otp3'], $user['otp4']);
  $ug[] = new AccessGroupUser(0, 1, $user['userId']);
}
if (sizeof($u) > 0) {
  Factory::getUserFactory()->massSave($u);
  Factory::getAccessGroupUserFactory()->massSave($ug);
}
echo "OK\n";

echo "Save agents... ";
$a = [];
$ag = [];
foreach ($agents as $agent) {
  $a[] = new Agent($agent['agentId'], $agent['agentName'], $agent['uid'], $agent['os'], $agent['gpus'], $agent['cmdPars'], $agent['ignoreErrors'], $agent['isActive'], $agent['isTrusted'], $agent['token'], $agent['lastAct'], $agent['lastTime'], $agent['lastIp'], $agent['userId'], $agent['cpuOnly'], "");
  $ag[] = new AccessGroupAgent(0, 1, $agent['agentId']);
}
if (sizeof($a) > 0) {
  Factory::getAgentFactory()->massSave($a);
  Factory::getAccessGroupAgentFactory()->massSave($ag);
}
echo "OK\n";

echo "Save files... ";
$f = [];
$fileIds = [];
foreach ($files as $file) {
  $fileIds[] = $file['fileId'];
  $f[] = new File($file['fileId'], $file['filename'], $file['size'], $file['secret'], $file['fileType']);
}
if (sizeof($f) > 0) {
  Factory::getFileFactory()->massSave($f);
}
echo "OK\n";

echo "Save hashlists... ";
$h = [];
foreach ($hashlists as $hashlist) {
  $h[] = new Hashlist($hashlist['hashlistId'], $hashlist['hashlistName'], $hashlist['format'], $hashlist['hashTypeId'], $hashlist['hashCount'], $hashlist['saltSeparator'], $hashlist['cracked'], $hashlist['secret'], $hashlist['hexSalt'], $hashlist['isSalted'], 1);
}
if (sizeof($h) > 0) {
  Factory::getHashlistFactory()->massSave($h);
}
echo "OK\n";

echo "Save superhashlistsHashlists... ";
$h = [];
foreach ($superhashlistsHashlists as $superhashlistsHashlist) {
  $h[] = new HashlistHashlist($superhashlistsHashlist['superHashlistHashlistId'], $superhashlistsHashlist['superHashlistId'], $superhashlistsHashlist['hashlistId']);
}
if (sizeof($h) > 0) {
  Factory::getHashlistHashlistFactory()->massSave($h);
}
echo "OK\n";

echo "Save hashes... ";
$h = [];
foreach ($hashes as $hash) {
  $h[] = new Hash($hash['hashId'], $hash['hashlistId'], $hash['hash'], $hash['salt'], $hash['plaintext'], $hash['time'], null, $hash['isCracked'], 0);
  if (sizeof($h) >= 1000) {
    Factory::getHashFactory()->massSave($h);
    $h = [];
  }
}
if (sizeof($h) > 0) {
  Factory::getHashFactory()->massSave($h);
}
echo "OK\n";

echo "Save binary hashes... ";
$h = [];
foreach ($binaryHashes as $binaryHash) {
  $h[] = new HashBinary($binaryHash['hashBinaryId'], $binaryHash['hashlistId'], $binaryHash['essid'], $binaryHash['hash'], $binaryHash['plaintext'], $binaryHash['time'], null, $binaryHash['isCracked'], 0);
}
if (sizeof($h) > 0) {
  Factory::getHashBinaryFactory()->massSave($h);
}
echo "OK\n";

echo "Save pretasks... ";
$t = [];
$taskIds = [];
foreach ($tasks as $task) {
  if ($task['taskType'] != 0 || $task['hashlistId'] != null) {
    continue; // we only transfer pretasks
  }
  $taskIds[] = $task['taskId'];
  $isMask = (strpos($task['taskName'], "HIDDEN: ") === 0) ? 1 : 0;
  if ($isMask == 1) {
    $task['taskName'] = str_replace("HIDDEN: ", "", $task['taskName']);
  }
  $t[] = new Pretask($task['taskId'], $task['taskName'], $task['attackCmd'], $task['chunkTime'], $task['statusTimer'], $task['color'], $task['isSmall'], $task['isCpuTask'], $task['useNewBench'], $task['priority'], $isMask, 1);
}
if (sizeof($t) > 0) {
  Factory::getPretaskFactory()->massSave($t);
}
echo "OK\n";

echo "Save task files... ";
$f = [];
foreach ($taskFiles as $taskFile) {
  if (!in_array($taskFile['taskId'], $taskIds) || !in_array($taskFile['fileId'], $fileIds)) {
    continue; // file is not from a pretask
  }
  $f[] = new FilePretask($taskFile['taskFileId'], $taskFile['fileId'], $taskFile['taskId']);
}
if (sizeof($f) > 0) {
  Factory::getFilePretaskFactory()->massSave($f);
}
echo "OK\n";

echo "Save supertasks... ";
$s = [];
foreach ($supertasks as $supertask) {
  $s[] = new Supertask($supertask['supertaskId'], $supertask['supertaskName']);
}
if (sizeof($s) > 0) {
  Factory::getSupertaskFactory()->massSave($s);
}
echo "OK\n";

echo "Save supertasks tasks... ";
$s = [];
foreach ($supertaskTasks as $supertaskTask) {
  $s[] = new SupertaskPretask($supertaskTask['supertaskTaskId'], $supertaskTask['supertaskId'], $supertaskTask['taskId']);
}
if (sizeof($s) > 0) {
  Factory::getSupertaskPretaskFactory()->massSave($s);
}
echo "OK\n";

echo "Re-enable checks... ";
$DB->exec("SET foreign_key_checks = 1;");
echo "OK\n";

$DB->commit();

echo "Update complete!\n";
