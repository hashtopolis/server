<?php
require_once(dirname(__FILE__) . "/inc/load.php");
$DB = $FACTORIES::getagentsFactory()->getDB();

$pik = imagecreatefromgif("static/stats.gif");

$res = $DB->query("SELECT COUNT(DISTINCT agents.id) AS agents, COUNT(DISTINCT tasks.id) AS tasks, COUNT(DISTINCT tasks.hashlist) AS hashlists, SUM(DISTINCT hashlists.hashcount)-SUM(DISTINCT hashlists.cracked) AS hashes, SUM(assignments.speed) AS speed FROM assignments JOIN tasks ON assignments.task=tasks.id JOIN hashlists ON tasks.hashlist=hashlists.id JOIN agents ON assignments.agent=agents.id JOIN chunks ON chunks.task=tasks.id AND chunks.agent=agents.id AND GREATEST(chunks.dispatchtime,chunks.solvetime)>=UNIX_TIMESTAMP()-(tasks.statustimer*1.2)");
$line = $res->fetch();

imagestring($pik, 3, 173, 11, $line["agents"], 0);
imagestring($pik, 3, 173, 23, $line["tasks"], 0);
imagestring($pik, 3, 173, 35, $line["hashlists"], 0);
imagestring($pik, 3, 173, 47, $line["hashes"], 0);
imagestring($pik, 3, 173, 59, Util::nicenum($line["speed"], 100000, 1000) . "H/s", 0);

$res = $DB->query("SELECT (SELECT COUNT(id) FROM tasks WHERE progress=keyspace AND keyspace>0 AND hashlist IS NOT NULL) AS tasks, (SELECT COUNT(id) FROM hashlists WHERE cracked=hashcount) AS hashlists, (SELECT SUM(cracked) FROM hashlists) AS hashes");
$line = $res->fetch();

imagestring($pik, 3, 267, 11, "-", 0);
imagestring($pik, 3, 267, 23, $line["tasks"], 0);
imagestring($pik, 3, 267, 35, $line["hashlists"], 0);
imagestring($pik, 3, 267, 47, Util::nicenum($line["hashes"], 1000, 1000), 0);

$res = $DB->query("SELECT (SELECT COUNT(id) FROM agents) AS agents, (SELECT COUNT(id) FROM tasks WHERE hashlist IS NOT NULL) AS tasks, (SELECT COUNT(id) FROM hashlists) AS hashlists, (SELECT SUM(hashcount) FROM hashlists) AS hashes");
$line = $res->fetch();

imagestring($pik, 3, 355, 11, $line["agents"], 0);
imagestring($pik, 3, 355, 23, $line["tasks"], 0);
imagestring($pik, 3, 355, 35, $line["hashlists"], 0);
imagestring($pik, 3, 355, 47, Util::nicenum($line["hashes"], 1000, 1000), 0);

// simply return the header for png and output the picture
header("Content-type: image/gif");
header("Cache-Control: no-cache");
imagegif($pik);



