<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("chunks");
$MENU->setActive("chunks");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		//TODO:
	}
}

// show overall chunk activity
$kver=mysqli_query_wrapper($dblink,"SELECT chunks.*,assignments.speed,GREATEST(chunks.dispatchtime,chunks.solvetime)-chunks.dispatchtime AS spent,agents.name AS aname,tasks.name AS tname FROM chunks JOIN tasks ON chunks.task=tasks.id LEFT JOIN agents ON chunks.agent=agents.id LEFT JOIN assignments ON assignments.task=tasks.id AND assignments.agent=agents.id ORDER BY chunks.dispatchtime DESC");
echo "Most recent chunks (showing all ".mysqli_num_rows($kver)."):";
echo "<table class=\"styled\">";
echo "<tr><td>id</td><td>Start</td><td>Length</td><td>Checkpoint</td><td>Progress</td><td>Task</td><td>Agent</td><td>Dispatch time</td><td>Last activity</td><td>Time spent</td><td>State</td><td>Cracked</td></tr>";
while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
	$task=$erej["task"];
	$agent=$erej["agent"];
	$dispatchtime=$erej["dispatchtime"];
	$solvetime=$erej["solvetime"];
	$progress=$erej["progress"];
	$length=$erej["length"];
	echo "<tr><td>".$erej["id"];
	if (max($solvetime,$dispatchtime)>$cas-$config["chunktimeout"] && $progress<$length && $erej["state"]<4) {
		echo " <img src=\"img/active.gif\" alt=\"Active\" title=\"".nicenum($erej["speed"],100000,1000)."H/s\">";
	}
	echo "</td><td class=\"num\">".$erej["skip"]."</td><td class=\"num\">$length</td><td class=\"num\">$progress";
	if ($progress>0 && $progress!=$length) {
		echo "<br>(".showperc($progress,$length)."%)";
	}
	echo "</td><td class=\"num\">".showperc($erej["rprogress"],10000)."%</td><td>";
	if ($task=="") {
		echo "N/A";
	} else {
		echo "<a href=\"$myself?a=taskdetail&task=$task\">".$erej["tname"]."</a>";
	}
				echo "</td><td>";
				if ($agent=="") {
				echo "N/A";
} else {
	echo "<a href=\"$myself?a=agentdetail&agent=$agent\">".$erej["aname"]."</a>";
	}
			echo "</td><td>";
			echo date($config["timefmt"],$dispatchtime)."</td><td>";
	if ($solvetime==0) {
		echo "(no activity)</td><td>";
} else {
	echo date($config["timefmt"],$solvetime);
          echo "</td><td class=\"num\">";
          echo sectotime($erej["spent"]);
	}
	echo "</td><td>";
        echo $states[$erej["state"]];
        echo "</td><td class=\"num\">";
          if ($erej["cracked"]>0) {
          echo "<a href=\"$myself?a=hashes&chunk=".$erej["id"]."\">".$erej["cracked"]."</a>";
}
	echo "</td></tr>";
}
echo "</table>";
break;

$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




