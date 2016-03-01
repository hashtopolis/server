<?php 
  // draws a forum activity signature
  include("dbconfig.php");
  $pik=imagecreatefromgif("img/stats.gif");

  $erej=mysqli_fetch_array(mysqli_query($dblink,"SELECT COUNT(DISTINCT agents.id) AS agents, COUNT(DISTINCT tasks.id) AS tasks, COUNT(DISTINCT tasks.hashlist) AS hashlists, SUM(DISTINCT hashlists.hashcount)-SUM(DISTINCT hashlists.cracked) AS hashes, SUM(assignments.speed) AS speed FROM assignments JOIN tasks ON assignments.task=tasks.id JOIN hashlists ON tasks.hashlist=hashlists.id JOIN agents ON assignments.agent=agents.id JOIN chunks ON chunks.task=tasks.id AND chunks.agent=agents.id AND GREATEST(chunks.dispatchtime,chunks.solvetime)>=UNIX_TIMESTAMP()-(tasks.statustimer*1.2)"),MYSQLI_ASSOC);
  imagestring($pik,3,173,11,$erej["agents"],0);
  imagestring($pik,3,173,23,$erej["tasks"],0);
  imagestring($pik,3,173,35,$erej["hashlists"],0);
  imagestring($pik,3,173,47,$erej["hashes"],0);
  imagestring($pik,3,173,59,nicenum($erej["speed"],100000,1000)."H/s",0);
  
  $erej=mysqli_fetch_array(mysqli_query($dblink,"SELECT (SELECT COUNT(id) FROM tasks WHERE progress=keyspace AND keyspace>0 AND hashlist IS NOT NULL) AS tasks, (SELECT COUNT(id) FROM hashlists WHERE cracked=hashcount) AS hashlists, (SELECT SUM(cracked) FROM hashlists) AS hashes"),MYSQLI_ASSOC);
  imagestring($pik,3,267,11,"-",0);
  imagestring($pik,3,267,23,$erej["tasks"],0);
  imagestring($pik,3,267,35,$erej["hashlists"],0);
  imagestring($pik,3,267,47,nicenum($erej["hashes"],1000,1000),0);

  $erej=mysqli_fetch_array(mysqli_query($dblink,"SELECT (SELECT COUNT(id) FROM agents) AS agents, (SELECT COUNT(id) FROM tasks WHERE hashlist IS NOT NULL) AS tasks, (SELECT COUNT(id) FROM hashlists) AS hashlists, (SELECT SUM(hashcount) FROM hashlists) AS hashes"),MYSQLI_ASSOC);
  imagestring($pik,3,355,11,$erej["agents"],0);
  imagestring($pik,3,355,23,$erej["tasks"],0);
  imagestring($pik,3,355,35,$erej["hashlists"],0);
  imagestring($pik,3,355,47,nicenum($erej["hashes"],1000,1000),0);
  
  // simply return the header for png and output the picture
  header("Content-type: image/gif");
  header("Cache-Control: no-cache");
  imagegif($pik);

function nicenum($num,$treshold=1024,$divider=1024) {
  // display nicely formated number divided into correct units
  $r=0;
  while ($num>$treshold) {
    $num/=$divider;
    $r++;
  }
  $rs=array("","k","M","G");
  $vysnew=niceround($num,2);
  return $vysnew." ".$rs[$r];

}

function niceround($num,$dec) {
  $stri=strval(round($num,$dec));
  if ($dec>0) {
    $pozice=strpos($stri,".");
    if ($pozice===false) {
      $stri.=".00";
    } else {
      while (strlen($stri)-$pozice<=$dec) $stri.="0";
    }
  }
  return $stri;
  
}



?>