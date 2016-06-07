<?php 
// draws a graph of chunk distribution inside task keyspace
require_once(dirname(__FILE__)."/inc/load.php");

if(!$LOGIN->isLoggedin()){
	header("Location: index.php?err=4".time()."&fw=".urlencode($_SERVER['PHP_SELF']));
	die();
}

$imx = min(1920, intval($_GET["x"]));
$imy = min(1080, intval($_GET["y"]));
if ($imx==0 || $imy==0){
	die();
}

$DB = $FACTORIES::getagentsFactory()->getDB();

$task = intval($_GET["task"]);
if($task > 0){
	$pik = imagecreatetruecolor($imx, $imy);
	imagesavealpha($pik, true);
	imagefill($pik, 0, 0, imagecolorallocatealpha($pik, 0, 0, 0, 127));
	$res = $DB->query("SELECT progress,keyspace FROM tasks WHERE id=$task");
	$line = $res->fetch();
	$progress = $line["progress"];
	$keyspace = max(1, $line["keyspace"]);
	$res = $DB->query("SELECT * FROM chunks WHERE task=$task ORDER BY state ASC");
	$end = 0;
	while($line = $res->fetch()){
		$zacatek = ($imx - 1) * $line["skip"] / $keyspace;
		$konec = ($imx - 1) * ($line["skip"] + $line["length"]) / $keyspace;
		$real = ($imx - 1) * ($line["skip"] + ($line["length"] * $line["rprogress"]) / 10000) / $keyspace;
		imagefilledrectangle($pik, $zacatek, 1, $real, ($imy - 2), imagecolorallocate($pik, 255, 255, 0));
		if($line["state"] >= 6){
			// draw red rectangle for chunks with problem state
			imagerectangle($pik, $zacatek, 0, $konec, ($imy - 1), imagecolorallocate($pik, 255, 0, 0));
		}
		else{
			// draw dark yellow for ok chunks
			//imagerectangle($pik, $zacatek, 0, $konec, ($imy - 1), imagecolorallocate($pik, 192, 192, 0));
		}
		$end = max($end, max($konec, $real));
		if($konec - $zacatek >= 2){
			$zacatek++;
		}
		if($real - $zacatek >= 1){
			$real--;
		}
		if($line["cracked"] > 0){
			// the more cracked hashes, the greener color
			$gr = min(strlen(strval($line["cracked"])) * 16, 128);
			imagefilledrectangle($pik, $zacatek, 1, $real, ($imy - 2), imagecolorallocate($pik, 128 - $gr, 255, 0));
		}
	}
	if($end > 0){
		imagerectangle($pik, 0, 0, $end, ($imy - 1), imagecolorallocate($pik, 192, 192, 192));
	}
	// simply return the header for png and output the picture
	header("Content-type: image/png");
	header("Cache-Control: no-cache");
	imagepng($pik);
}




