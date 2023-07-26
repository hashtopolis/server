<?php

use DBA\Agent;
use DBA\QueryFilter;
use DBA\ApiKey;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

ini_set("max_execution_time", 100000);

if (!isset($_GET['file'])) {
  die("ERR1 - no file set");
}

$FILEID = intval($_GET['file']);

if (!$FILEID) {
  die("ERR2 - no file provided");
}

$line = Factory::getFileFactory()->get($FILEID);

//no file found
if (!$line) {
  die("ERR5 - file not found");
}

$accessGroupIds = [];

//check user rights to download here:
//if the user is logged in, he need to have the rights to
//if agent provides his voucher, check it.
if (!Login::getInstance()->isLoggedin()) {
  if (isset($_GET['apiKey'])) {
    $qF = new QueryFilter(ApiKey::ACCESS_KEY, $_GET['apiKey'], "=");
    $apiKey = Factory::getApiKeyFactory()->filter([Factory::FILTER => $qF], true);
    $apiFile = new UserAPIFile();
    if ($apiKey == null) {
      die("Invalid access key!");
    }
    else if ($apiKey->getStartValid() > time() || $apiKey->getEndValid() < time()) {
      die("Expired access key!");
    }
    else if (!$apiFile->hasPermission(USection::FILE, USectionFile::GET_FILE, $apiKey)) {
      die("Permission denied!");
    }
    $accessGroupIds = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser(Factory::getUserFactory()->get($apiKey->getUserId())));
  }
  else {
    $token = @$_GET['token'];
    $qF = new QueryFilter(Agent::TOKEN, $token, "=");
    $agent = Factory::getAgentFactory()->filter([Factory::FILTER => $qF], true);
    if (!$agent) {
      die("No access!");
    }
    if ($agent->getIsTrusted() < $line->getIsSecret()) {
      die("No access!");
    }
    $accessGroupIds = Util::arrayOfIds(AccessUtils::getAccessGroupsOfAgent($agent));
  }
}
else if (!AccessControl::getInstance()->hasPermission(DAccessControl::VIEW_FILE_ACCESS)) {
  die("No access!");
}
else {
  $accessGroupIds = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser(Login::getInstance()->getUser()));
}

if (!in_array($line->getAccessGroupId(), $accessGroupIds)) {
  die("Access denied to file because of access groups!");
}

$filename = Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . "/" . $line->getFilename();

//file not found
if (!file_exists($filename)) {
  die("ERR3 - file not present");
}

$file = $filename;
$fp = @fopen($file, "rb");

$size = Util::filesize($file); // File size
$length = $size;           // Content length
$start = 0;               // Start byte
$end = $size - 1;       // End byte

header("Accept-Ranges: bytes");

$exp = explode(".", $filename);
if ($exp[sizeof($exp) - 1] == '7z') {
  header("Content-Type: application/x-7z-compressed");
}
else {
  //header("Content-Type: text/plain");
  header("Content-Type: application/force-download");
}

header("Content-Description: " . $line->getFilename());
header("Content-Disposition: attachment; filename=\"" . $line->getFilename() . "\"");

if (isset($_SERVER['HTTP_RANGE'])) {
  
  $c_start = $start;
  $c_end = $end;
  
  list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
  
  if (strpos($range, ',') !== false) {
    header('HTTP/1.1 416 Requested Range Not Satisfiable');
    header("Content-Range: bytes $start-$end/$size");
    exit;
  }
  if ($range == '-') {
    $c_start = $size - substr($range, 1);
  }
  else {
    $range = explode('-', $range);
    $c_start = $range[0];
    if ((isset($range[1]) && is_numeric($range[1]))) {
      $c_end = $range[1];
    }
    else {
      $c_end = $size;
    }
  }
  if ($c_end > $end) {
    $c_end = $end;
  }
  if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
    header('HTTP/1.1 416 Requested Range Not Satisfiable');
    header("Content-Range: bytes $start-$end/$size");
    exit;
  }
  $start = $c_start;
  $end = $c_end;
  $length = $end - $start + 1;
  fseek($fp, $start);
  header('HTTP/1.1 206 Partial Content');
}

header("Content-Range: bytes $start-$end/$size");
header("Content-Length: " . $length);

$buffer = 1024 * 100;
while (!feof($fp) && ($p = ftell($fp)) <= $end) {
  
  if ($p + $buffer > $end) {
    $buffer = $end - $p + 1;
  }
  echo fread($fp, $buffer);
  flush();
}


fclose($fp);