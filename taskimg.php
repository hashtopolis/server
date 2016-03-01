<?php 
// draws a graph of chunk distribution inside task keyspace
include("dbconfig.php");

$imx=min(1920,intval($_GET["x"]));
$imy=min(1080,intval($_GET["y"]));
if ($imx==0 || $imy==0) die();

$task=intval($_GET["task"]);
if ($task>0) {
  $pik=imagecreatetruecolor($imx,$imy);
  imagesavealpha($pik,true);
  imagefill($pik, 0, 0, imagecolorallocatealpha($pik, 0,0,0,127));
  $kver=mysqli_query($dblink,"SELECT progress,keyspace FROM tasks WHERE id=$task");
  $erej=mysqli_fetch_array($kver,MYSQLI_ASSOC);
  $progress=$erej["progress"];
  $keyspace=max(1,$erej["keyspace"]);
  $kver=mysqli_query($dblink,"SELECT * FROM chunks WHERE task=$task ORDER BY state ASC");
  while ($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
    $zacatek=($imx-1)*$erej["skip"]/$keyspace;
    $konec=($imx-1)*($erej["skip"]+$erej["length"])/$keyspace;
    $real=($imx-1)*($erej["skip"]+($erej["length"]*$erej["rprogress"])/10000)/$keyspace;
    imagefilledrectangle($pik, $zacatek, 1, $real, ($imy-2), imagecolorallocate($pik, 255,255,0));
    if ($erej["state"]>=6) {
      // draw red rectangle for chunks with problem state
      imagerectangle($pik, $zacatek, 0, $konec, ($imy-1), imagecolorallocate($pik, 255,0,0));
    } else {
      // draw dark yellow for ok chunks
      imagerectangle($pik, $zacatek, 0, $konec, ($imy-1), imagecolorallocate($pik, 192,192,0));
    }
    if ($konec-$zacatek>=2) {
      $zacatek++;
    }
    if ($real-$zacatek>=1) {
      $real--;
    }
    if ($erej["cracked"]>0) {
      // the more cracked hashes, the greener color
      $gr=min(strlen(strval($erej["cracked"]))*16,128);
      imagefilledrectangle($pik, $zacatek, 1, $real, ($imy-2), imagecolorallocate($pik, 128-$gr,255,0));
    }

  }
  // simply return the header for png and output the picture
  header("Content-type: image/png");
  header("Cache-Control: no-cache");
  imagepng($pik);
}




?>