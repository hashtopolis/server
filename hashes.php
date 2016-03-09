<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

if(!$LOGIN->isLoggedin()){
	header("Location: index.php?err=4".time()."&fw=".urlencode($_SERVER['PHP_SELF']));
	die();
}
else if($LOGIN->getLevel() < 5){
	$TEMPLATE = new Template("restricted");
	die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("hashes");
$MENU->setActive("hashes");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		//TODO:
	}
}

// show hashes based on provided criteria
$hlist = 0;
$chunk = 0;
$task = 0;
$src = "";
$srcId = 0;
if(isset($_GET['hashlist'])){
	$hlist = intval($_GET["hashlist"]);
	$src = "hashlist";
	$srcId = $hlist;
}
else if(isset($_GET['chunk'])){
	$chunk = intval($_GET["chunk"]);
	$src = "chunk";
	$srcId = $chunk;
}
else if(isset($_GET['task'])){
	$task = intval($_GET["task"]);
	$src = "task";
	$srcId = $task;
}

$valid = false;
if ($chunk > 0) {
	$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT hashlists.id,hashlists.format FROM chunks JOIN tasks ON chunks.task=tasks.id JOIN hashlists ON hashlists.id=tasks.hashlist WHERE chunks.id=$chunk");
	$chunkRes = $res->fetch();
	if(!$chunkRes){
		$message = "<div class='alert alert-danger'>Invalid Chunk!</div>";
	}
	else{
		$hlist = $chunkRes['id'];
		$format = $chunkRes['format'];
		$valid = true;
	}
} 
else if ($task > 0) {
	$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT hashlists.id,tasks.name,hashlists.format FROM tasks JOIN hashlists ON hashlists.id=tasks.hashlist WHERE tasks.id=$task");
	$taskRes = $res->fetch();
	if(!$taskRes){
		$message = "<div class='alert alert-danger'>Invalid task!</div>";
	}
	else{
		$hlist = $taskRes['id'];
		$format = $taskRes["format"];
		$valid = true;
	}
} 
else if ($hlist > 0) {
	$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT name,format FROM hashlists WHERE id=$hlist");
	$hlistRes = $res->fetch();
	if(!$hlistRes){
		$message = "<div class='alert alert-danger'>Invalid hashlist!</div>";
	}
	else{
		$format = $hlistRes["format"];
		$valid = true;
	}
}

if($valid){
	$OBJECTS['src'] = $src;
	$OBJECTS['srcId'] = $srcId;
	
	// create proper superhashlist field if needed
	list($superhash,$hlisty) = Util::superList($hlist,$format);

	switch ($src) {
		case "chunk":
			$viewfilter = "WHERE chunk=$chunk";
			break;
		case "task":
			$viewfilter = "JOIN chunks ON chunk=chunks.id WHERE ".Util::getStaticArray($format, 'formattables').".chunk IS NOT NULL AND chunks.task=$task";
          	break;
		case "hashlist":
			$viewfilter = "WHERE hashlist IN ($hlisty)";
			break;
	}
	$displaying = "";
	if(isset($_GET['display'])){
		$displaying = $_GET["display"];
	}
	$OBJECTS['displaying'] = $displaying;
	$filt = "";
	if(isset($_GET['filter'])){
		$filt = $_GET['filter'];
	}
	$OBJECTS['filtering'] = $filt;
    
	$displays = array("hash"=>"Hashes only", ""=>"Hashes + plaintexts", "plain"=>"Plaintexts only");
    $filters = array("cracked"=>"Cracked", "uncracked"=>"Uncracked", ""=>"All");
    
    $displaysSet = array();
    foreach($displays as $id=>$text){
    	$set = new DataSet();
    	$set->addValue('id', $id);
    	$set->addValue('text', $text);
    	$displaysSet[] = $set;
    }
    $OBJECTS['displays'] = $displaysSet;
    
    $filtersSet = array();
    foreach($filters as $id=>$text){
    	$set = new DataSet();
    	$set->addValue('id', $id);
    	$set->addValue('text', $text);
    	$filtersSet[] = $set;
    }
    $OBJECTS['filters'] = $filtersSet;
    
    $filter = array("cracked"=>" AND plaintext IS NOT NULL", "uncracked"=>" AND plaintext IS NULL", "" => "");
    $kve = "SELECT ";
    switch ($format) {
    	case 0:
    		// get regular hashes
    		$kve .= "hashes.hash,hashes.salt,hashes.plaintext";
    		break;
    
    	case 1:
    		// get access points and their passwords
    		$kve .= "hashes_binary.essid AS hash,hashes_binary.plaintext";
    		break;
    
    	case 2:
    		// get binary - only passwords
    		$kve .= "'' AS hash,hashes_binary.plaintext";
    		break;
    }
    $kve .= " FROM ".Util::getStaticArray($format, 'formattables')." ".$viewfilter.$filter[$filt];
    
    $res = $FACTORIES::getagentsFactory()->getDB()->query($kve);
    $res = $res->fetchAll();
    $output = "";
    foreach($res as $entry){
    	$out = "";
    	if(strlen($entry['hash']) == 0){
    		continue;
    	}
    	switch($displaying){
    		case 'hash':
    			$out .= $entry['hash'];
    			if($entry['salt'] != ""){
    				$out .= $CONFIG->getVal('fieldseparator').$entry['salt'];
    			}
    			break;
    		case '':
    			$out .= $entry['hash'];
    			if($entry['salt'] != ""){
    				$out .= $CONFIG->getVal('fieldseparator').$entry['salt'];
    			}
    			$out .= $CONFIG->getVal('fieldseparator');
    		case 'plain':
    			if($entry['plaintext'] != ""){
    				$out .= $entry['plaintext'];
    			}
    			break;
    	}
    	if(strlen($out) > 0){
    		$output .= "$out\n";
    	}
    }
    $OBJECTS['matches'] = $output;
    $OBJECTS['numMatches'] = sizeof($res);
}

$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




