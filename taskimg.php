<?php 
/*
 * This file is completely rewritten for Hashtopussy
 * Copyright 2016 by s3in!c
 * 
 * Draws graphic about chunk progress
 */
require_once(dirname(__FILE__)."/inc/load.php");

//check if there is a session
if(!$LOGIN->isLoggedin()){
	header("Location: index.php?err=4".time()."&fw=".urlencode($_SERVER['PHP_SELF']));
	die();
}

//get image dimenstions
$size = array(min(1920, intval($_GET["x"])), min(1080, intval($_GET["y"])));
if ($size[0] == 0 || $size[0] == 0){
	die();
}

//check if task exists and get information
$taskid = intval($_GET["task"]);
$res = $DB->query("SELECT * FROM tasks WHERE id=$taskid");
$task = $res->fetch();
if(!$task){
	die("Not a valid task!");
}

//create image
$image = imagecreatetruecolor($size[0], $size[1]);
imagesavealpha($image, true);

//set colors
$transparency = imagecolorallocatealpha($image, 0, 0, 0, 127);
$yellow = imagecolorallocate($image, 255, 255, 0);
$red = imagecolorallocate($image, 255, 0, 0);
$grey = imagecolorallocate($image, 192, 192, 192);
$green = imagecolorallocate($image, 0, 255, 0);

//prepare image
imagefill($image, 0, 0, $transparency);

$progress = $task['progress'];
$keyspace = max($task['keyspace'], 1);
$taskid = $task['id'];

//load chunks
$res = $DB->query("SELECT * FROM chunks WHERE task=$taskid ORDER BY state ASC");
$res = $res->fetchAll();
foreach($res as $chunk){
	$start = ($size[0] - 1) * $chunk['skip'] / $keyspace;
	$end = ($size[0] - 1) * ($chunk['skip'] + $chunk['length']) / $keyspace;
	//division by 10000 is required because rprogress is saved in percents with two decimals
	$current = ($size[0] - 1) * ($chunk['skip'] + $chunk['length'] * $chunk['rprogress']) / 10000 / $keyspace;
	
	//draw rectangles for chunk
	if($chunk['cracked'] > 0){
		imagefilledrectangle($image, $start, 1, $current, $size[1] - 2, $green);
	}
	else{
		imagefilledrectangle($image, $start, 1, $current, $size[1] - 2, $yellow);
	}
	if($chunk['state'] >= 6){
		//there occured an error on the chunk
		imagerectangle($image, $start, 0, $end, $size[1] - 1, $red);
	}
	else{
		imagerectangle($image, $start, 0, $end, $size[1] - 1, $grey);
	}
	if($end - $start >= 2){
		//we can only really draw a rectangle around the chunk if he needs at least 2 pixels
		$start++; 
	}
	if($current - $start >= 1){
		//if the current is at least at 1 we can draw the rectangle around it
		$current--;
	}
}

//send image data to output
header("Content-type: image/png");
header("Cache-Control: no-cache");
imagepng($image);




