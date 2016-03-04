<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("tasks");
$MENU->setActive("tasks_list");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		//TODO:
	}
}

if(isset($_GET['id'])){
	$TEMPLATE = new Template("tasks.detail");
	
	// show task details
	$task=intval($_GET["task"]);
	$filter=intval(isset($_GET["all"]) ? $_GET["all"] : "");
	$kver=mysqli_query_wrapper($dblink,"SELECT tasks.*,hashlists.name AS hname,hashlists.format,hashlists.hashtype AS htype,hashtypes.description AS htypename,ROUND(chunks.cprogress) AS cprogress,SUM(assignments.speed*IFNULL(achunks.working,0)) AS taskspeed,IF(chunks.lastact>".($cas-$config["chunktimeout"]).",1,0) AS active FROM tasks LEFT JOIN hashlists ON hashlists.id=tasks.hashlist LEFT JOIN hashtypes ON hashlists.hashtype=hashtypes.id LEFT JOIN (SELECT task,SUM(progress) AS cprogress,MAX(GREATEST(dispatchtime,solvetime)) AS lastact FROM chunks GROUP BY task) chunks ON chunks.task=tasks.id LEFT JOIN assignments ON assignments.task=tasks.id LEFT JOIN (SELECT DISTINCT agent,1 AS working FROM chunks WHERE task=$task AND GREATEST(dispatchtime,solvetime)>".($cas-$config["chunktimeout"]).") achunks ON achunks.agent=assignments.agent WHERE tasks.id=$task GROUP BY tasks.id");
	 
	if (mysqli_num_rows($kver)!=1) break;
	$erej=mysqli_fetch_array($kver,MYSQLI_ASSOC);
	echo "Task details:<table class=\"styled\">";
	$format=$erej["format"];
	$id=$erej["id"];
	$kspace=$erej["keyspace"];
	$stimer=$erej["statustimer"];
	echo "<tr><td>Property</td><td>Value</td></tr>";
	echo "<tr><td>ID:</td><td>$id</td></tr>";
	echo "<tr><td>Name:</td><td>";
	
      echo "<form action=\"$myself?a=taskrename\" method=\"POST\">";
	      echo "<input type=\"hidden\" name=\"return\" value=\"a=taskdetail&task=$id\">";
	      echo "<input type=\"hidden\" name=\"task\" value=\"$id\">";
	      echo "<input type=\"text\" name=\"name\" size=\"25\" value=\"".$erej["name"]."\">";
	      		echo "<input type=\"submit\" value=\"Change\"></form>";
	
      echo "</td></tr>";
      echo "<tr><td>Attack command:</td><td>".$erej["attackcmd"]."</td></tr>";
      echo "<tr><td>Chunk size:</td><td>";
      echo "<form action=\"$myself?a=taskchunk\" method=\"POST\">";
	      echo "<input type=\"hidden\" name=\"return\" value=\"a=taskdetail&task=$id\">";
	      echo "<input type=\"hidden\" name=\"task\" value=\"$id\">";
	      echo "<input type=\"text\" name=\"chunktime\" size=\"5\" value=\"".$erej["chunktime"]."\"> seconds ";
      echo "<input type=\"submit\" value=\"Set\"></form></td></tr>";
      echo "<tr><td>Benchmark:</td><td>";
      echo "<form id=\"taskauto\" action=\"$myself?a=taskauto\" method=\"POST\">";
	      echo "<input type=\"hidden\" name=\"return\" value=\"a=taskdetail&task=$task\">";
	      echo "<input type=\"hidden\" name=\"task\" value=\"$id\">";
	      echo "<input type=\"checkbox\" name=\"auto\" value=\"1\"";
      if ($erej["autoadjust"]==1) echo " checked";
      echo " onChange=\"javascript:document.getElementById('taskauto').submit();\"> Autoadjust by default";
      echo "</form>";
      echo "</td></tr>";
      echo "<tr><td>Color:</td><td>";
      echo "<form id=\"taskcolor\" action=\"$myself?a=taskcolor\" method=\"POST\">";
	      echo "<input type=\"hidden\" name=\"return\" value=\"a=taskdetail&task=$task\">";
	      echo "<input type=\"hidden\" name=\"task\" value=\"$id\">";
	      echo "#<input type=\"text\" size=\"6\" name=\"color\" class=\"color {required:false}\" value=\"".$erej["color"]."\">";
	      		echo "<input type=\"submit\" value=\"Set\">";
	      				echo "</form>";
	      						echo "</td></tr>";
	      						echo "<tr><td>Status timer:</td><td>".$erej["statustimer"]." seconds</td></tr>";
	      						echo "<tr><td>Priority:</td><td>";
	      						echo "<form action=\"$myself?a=taskprio\" method=\"POST\">";
      echo "<input type=\"hidden\" name=\"return\" value=\"a=tasks\">";
	      echo "<input type=\"hidden\" name=\"task\" value=\"$id\">";
      echo "<input type=\"text\" name=\"priority\" size=\"4\" value=\"".$erej["priority"]."\"> ";
      echo "<input type=\"submit\" value=\"Set\"></form>";
	      		echo "</td></tr>";
      $hlist=$erej["hashlist"];
	      if ($hlist!="") {
        echo "<tr><td>Keyspace size:</td><td>";
	        echo ($kspace > 0 ? $kspace : "N/A");
	        		echo "</td></tr>";
        echo "<tr><td>Keyspace dispatched:</td><td>";
	        $progre=showperc($erej["progress"],$kspace);
	        		echo $erej["progress"]." ($progre%) ";
	        				if ($erej["active"]==0) {
	        						echo "<form action=\"$myself?a=taskpurge\" method=\"POST\" onSubmit=\"if (!confirm('Really completely purge task $id?')) return false;\">";
          echo "<input type=\"hidden\" name=\"return\" value=\"a=tasks\">";
	          echo "<input type=\"hidden\" name=\"task\" value=\"$id\">";
          echo "<input type=\"submit\" value=\"Purge\"></form>";
	      }
	      echo "</td></tr>";
        echo "<tr><td>Keyspace searched:</td><td>";
	        $progre=showperc($erej["cprogress"],$kspace);
	        		echo $erej["cprogress"]." ($progre%)";
	      echo "</td></tr>";
        echo "<tr><td>Time spent:</td><td>";
	        $kve=mysqli_query_wrapper($dblink,"SELECT dispatchtime,solvetime FROM chunks WHERE task=$id AND solvetime>dispatchtime ORDER BY dispatchtime ASC");
	        		$intervaly=array();
	        		while ($ere=mysqli_fetch_array($kve,MYSQLI_ASSOC)) {
	          $interval=array();
	          $interval["start"]=$ere["dispatchtime"];
	          $interval["stop"]=$ere["solvetime"];
	          $intervaly[]=$interval;
	      }
	      $soucet=0;
	      // interval counter - this one was a real bitch to write considering how short it is :D
	      for ($i=1;$i<=count($intervaly);$i++) {
	      if (isset($intervaly[$i]) && $intervaly[$i]["start"]<=$intervaly[$i-1]["stop"]) {
	      $intervaly[$i]["start"]=$intervaly[$i-1]["start"];
	      if ($intervaly[$i]["stop"]<$intervaly[$i-1]["stop"]) {
	      	$intervaly[$i]["stop"]=$intervaly[$i-1]["stop"];
	      }
	      } else {
	      $soucet+=($intervaly[$i-1]["stop"]-$intervaly[$i-1]["start"]);
	      }
	      }
	      echo sectotime($soucet);
	      echo "</td></tr>";
	      $kve=mysqli_query_wrapper($dblink,"SELECT ROUND((tasks.keyspace-SUM(tchunks.length))/SUM(tchunks.length*tchunks.active/tchunks.time)) AS eta FROM (SELECT SUM(chunks.length*chunks.rprogress/10000) AS length,SUM(chunks.solvetime-chunks.dispatchtime) AS time,IF(MAX(solvetime)>=$cas-".$config["chunktimeout"].",1,0) AS active FROM chunks WHERE chunks.solvetime>chunks.dispatchtime AND chunks.task=$id GROUP BY chunks.agent) tchunks CROSS JOIN tasks WHERE tasks.id=$id");
	        $ere=mysqli_fetch_array($kve,MYSQLI_ASSOC);
	        echo "<tr><td>Estimated time:</td><td>".sectotime($ere["eta"])."</td></tr>";
	        		echo "<tr><td>Speed:</td><td>".nicenum($erej["taskspeed"],100000,1000)."H/s</td></tr>";
	        		echo "<tr><td>".($erej["format"]==3 ? "Superh" : "H")."ashlist:</td><td><a href=\"$myself?a=hashlistdetail&hashlist=$hlist\">".$erej["hname"]."</a> (".($erej["htypename"]=="" ? $erej["htype"] : $erej["htypename"]).")</td></tr>";
	      }
	        				echo "</table>";
	
	        						if ($hlist!="") {
	        						// graph only for regular tasks
	        						echo "<br><table class=\"styled\">";
	        						echo "<tr><td>Visual representation</td></tr>";
	        						echo "<tr><td><img style=\"width: 800px; height: 32px\" src=\"taskimg.php?task=$task&x=800&y=32\"></td></tr>";
	        						echo "</table>";
	      }
	
	      $kver=mysqli_query_wrapper($dblink,"SELECT files.id,files.filename,files.size,files.secret FROM taskfiles JOIN files ON files.id=taskfiles.file WHERE task=$task ORDER BY filename");
	      if (mysqli_num_rows($kver)>0) {
        echo "<br>Attached files:";
        echo "<table class=\"styled\">";
        echo "<tr><td>id</td><td>Filename</td><td>Size</td></tr>";
	        while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
	        	$id=$erej["id"];
	        	$filnam=$erej["filename"];
	        	echo "<tr><td>$id</td><td><a href=\"$myself?a=files#$id\">$filnam</a>";
	        	if ($erej["secret"]==1) echo " <img src=\"img/lock.gif\" alt=\"Secret\">";
          echo "</td><td class=\"num\">".nicenum($erej["size"])."B</td></tr>";
	        						}
	        						echo "</table>";
	      }
	
	      	if ($hlist!="") {
	      			echo "<br>Assigned agents:";
	      			$kver=mysqli_query_wrapper($dblink,"SELECT agents.id,agents.active,agents.trusted,agents.name,assignments.benchmark,assignments.autoadjust,IF(chunks.lastact>=".($cas-$config["chunktimeout"]).",1,0) AS working,assignments.speed,IFNULL(chunks.lastact,0) AS time,IFNULL(chunks.searched,0) AS searched,chunks.spent,IFNULL(chunks.cracked,0) AS cracked FROM agents JOIN assignments ON agents.id=assignments.agent JOIN tasks ON tasks.id=assignments.task LEFT JOIN (SELECT agent,SUM(progress) AS searched,SUM(solvetime-dispatchtime) AS spent,SUM(cracked) AS cracked,MAX(GREATEST(dispatchtime,solvetime)) AS lastact FROM chunks WHERE task=$task AND solvetime>dispatchtime GROUP BY agent) chunks ON chunks.agent=agents.id WHERE assignments.task=$task GROUP BY agents.id ORDER BY agents.id");
	      					echo "<table class=\"styled\">";
        echo "<tr><td>id</td><td>Name</td><td>Benchmark</td><td>Speed</td><td>Keyspace searched</td><td>Time spent</td><td>Cracked</td><td>Last activity</td><td>Action</td></tr>";
	        while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
	        $id=$erej["id"];
	        echo "<tr><td>";
	        echo "<a href=\"$myself?a=agentdetail&agent=$id\">$id</a>";
	        echo "</td><td>";
	        echo "<a href=\"$myself?a=agentdetail&agent=$id\">".$erej["name"]."</a>";
	          if ($erej["trusted"]==1) echo " <img src=\"img/lock.gif\" alt=\"Trusted\">";
	          		if ($erej["working"]==1) echo " <img src=\"img/active.gif\" alt=\"Active\">";
	          		if ($erej["active"]==0) echo " <img src=\"img/pause.gif\" alt=\"Paused\">";
	        echo "</td><td>";
	        echo "<form action=\"$myself?a=agentbench\" method=\"POST\">";
	        echo "<input type=\"hidden\" name=\"return\" value=\"a=taskdetail&task=$task\">";
	        echo "<input type=\"hidden\" name=\"agent\" value=\"$id\">";
          echo "<input type=\"text\" name=\"bench\" value=\"".$erej["benchmark"]."\" size=\"8\">";
          echo "<input type=\"submit\" value=\"Set\">";
	          		echo "</form>";
	          		echo "<form id=\"auto$id\" action=\"$myself?a=agentauto\" method=\"POST\">";
	          		echo "<input type=\"hidden\" name=\"return\" value=\"a=taskdetail&task=$task\">";
	          		echo "<input type=\"hidden\" name=\"agent\" value=\"$id\">";
          echo "<input type=\"checkbox\" name=\"auto\" value=\"1\"";
	          		if ($erej["autoadjust"]==1) echo " checked";
	          		echo " onChange=\"javascript:document.getElementById('auto$id').submit();\"> Auto";
	          		echo "</form>";
	          				echo "</td><td class=\"num\">";
	          				if ($erej["working"]==1) echo nicenum($erej["speed"],100000,1000)."H/s";
	          				echo "</td><td class=\"num\">";
	          				if ($kspace>0) {
	          				$searched=$erej["searched"];
	          				echo $searched." (".showperc($searched,$kspace)."%)";
	        }
	        echo "</td><td class=\"num\">";
	          				echo sectotime($erej["spent"]);
	          				echo "</td><td class=\"num\">";
	          						echo $erej["cracked"];
	          						echo "</td><td>";
	          						$hlage=$erej["time"];
	          						if ($hlage>0) echo date($config["timefmt"],$hlage);
	          						echo "</td><td>";
	          				echo "<form action=\"$myself?a=agentassign\" method=\"POST\">";
	          				echo "<input type=\"hidden\" name=\"return\" value=\"a=taskdetail&task=$task\">";
	          				echo "<input type=\"hidden\" name=\"agent\" value=\"$id\">";
          echo "<input type=\"hidden\" name=\"task\" value=\"\">";
	          				echo "<input type=\"submit\" value=\"Unassign\">";
	          				echo "</form>";
	          				echo "</td></tr>";
	      	}
	      	echo "<tr><td colspan=\"2\"><form action=\"$myself?a=agentassign\" method=\"POST\">";
	      	echo "<input type=\"hidden\" name=\"return\" value=\"a=taskdetail&task=$task\">";
        echo "<input type=\"hidden\" name=\"task\" value=\"$task\">";
	        echo "<select name=\"agent\">";
	        $kvery=mysqli_query_wrapper($dblink,"SELECT agents.id,agents.name FROM agents LEFT JOIN assignments ON assignments.agent=agents.id WHERE IFNULL(assignments.task,0)!=$task ORDER BY agents.id ASC");
	        						while($ere=mysqli_fetch_array($kvery,MYSQLI_ASSOC)) {
	        						$agi=$ere["id"];
	        						echo "<option value=\"$agi\">$agi";
	        						if ($ere["name"]!="") echo " (".$ere["name"].")";
          echo "</option>";
        }
	        								echo "</select></td>";
	        								echo "<td colspan=\"8\"><input type=\"submit\" value=\"Assign\"></form></td></tr>";
        echo "</table>";
	
	        echo "<br>Dispatched chunks";
	        if ($filter!="1") {
	        $filtr="AND progress<length ";
	        								echo " (showing active only - <a href=\"$myself?a=taskdetail&task=$task&all=1\">show latest 100</a>)";
	        						} else {
	        						echo " (showing latest 100)";
	        						}
	        						echo ":";
	        						$kver=mysqli_query_wrapper($dblink,"SELECT chunks.*,GREATEST(chunks.dispatchtime,chunks.solvetime)-chunks.dispatchtime AS spent,agents.name FROM chunks LEFT JOIN agents ON chunks.agent=agents.id WHERE task=$task ".$filtr."ORDER BY chunks.dispatchtime DESC LIMIT 100");
        echo "<table class=\"styled\">";
        echo "<tr><td>id</td><td>Start</td><td>Length</td><td>Checkpoint</td><td>Progress</td><td>Agent</td><td>Dispatch time</td><td>Last activity</td><td>Time spent</td><td>State</td><td>Cracked</td><td>Action</td></tr>";
        while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
	        								$id=$erej["id"];
	        										$agid=$erej["agent"];
	        										$dispatchtime=$erej["dispatchtime"];
	        										$solvetime=$erej["solvetime"];
	        										$progress=$erej["progress"];
	        										$length=$erej["length"];
          $active=(max($dispatchtime,$solvetime)>$cas-$config["chunktimeout"] && $progress<$length && $erej["state"]<4);
	          echo "<tr><td>$id";
	          if ($active) echo " <img src=\"img/active.gif\" alt=\"Active\">";
	          		echo "</td><td class=\"num\">".$erej["skip"]."</td><td class=\"num\">$length</td><td class=\"num\">$progress";
	          		if ($progress>0 && $progress!=$length) {
	            echo "<br>(".showperc($progress,$length)."%)";
	            }
	            echo "</td><td class=\"num\">".showperc($erej["rprogress"],10000)."%</td><td>";
	            if ($agid=="") {
	            echo "N/A";
	        								} else {
	        								echo "<a href=\"$myself?a=agentdetail&agent=$agid\">".$erej["name"]."</a>";
	        								}
	        								echo "</td><td>".date($config["timefmt"],$dispatchtime)."</td><td>";
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
	        								if ($erej["cracked"]>0) echo "<a href=\"$myself?a=hashes&chunk=$id\">".$erej["cracked"]."</a>";
	        								echo "</td><td>";
	        								if (!$active) {
	        								echo "<form action=\"$myself?a=chunkreset\" method=\"POST\" onSubmit=\"if (!confirm('Really reset chunk $id?')) return false;\">";
	        								echo "<input type=\"hidden\" name=\"return\" value=\"a=taskdetail&task=$task\">";
	        								echo "<input type=\"hidden\" name=\"chunk\" value=\"$id\">";
	        								echo "<input type=\"submit\" value=\"Reset\"></form>";
	        								} else {
	        								echo "<form action=\"$myself?a=chunkabort\" method=\"POST\" onSubmit=\"if (!confirm('Really abort cracking chunk $id?')) return false;\">";
	            echo "<input type=\"hidden\" name=\"return\" value=\"a=taskdetail&task=$task\">";
	            echo "<input type=\"hidden\" name=\"chunk\" value=\"$id\">";
	            echo "<input type=\"submit\" value=\"Abort\"></form>";
	          }
	          echo "</td></tr>";
	        }
	      }
	      echo "</table>";
	      break;
}
else{
	$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT tasks.id AS task,tasks.chunktime,tasks.priority,tasks.color,tasks.name,tasks.attackcmd,tasks.hashlist,tasks.progress,IFNULL(chunks.sumprog,0) AS sumprog,tasks.keyspace,IFNULL(chunks.pcount,0) AS pcount,IFNULL(chunks.ccount,0) AS ccount,IFNULL(taskfiles.secret,0) AS secret,IF(chunks.lastact>".(time()-$CONFIG->getVal('chunktimeout')).",1,0) AS active,IFNULL(assignments.acount,0) AS assigncount,taskfiles.fcount AS filescount,IFNULL(taskfiles.fsize,0) AS filesize,hashlists.name AS hname,hashlists.secret AS hsecret,IF(hashlists.cracked<hashlists.hashcount,1,0) AS hlinc FROM tasks JOIN hashlists ON hashlists.id=tasks.hashlist LEFT JOIN (SELECT task,SUM(cracked) AS pcount,COUNT(1) AS ccount,GREATEST(MAX(dispatchtime),MAX(solvetime)) AS lastact,SUM(progress) AS sumprog FROM chunks GROUP BY task) chunks ON chunks.task=tasks.id LEFT JOIN (SELECT task,COUNT(1) AS acount FROM assignments GROUP BY task) assignments ON assignments.task=tasks.id LEFT JOIN (SELECT taskfiles.task,COUNT(1) AS fcount,SUM(files.size) AS fsize,MAX(files.secret) AS secret FROM taskfiles JOIN files ON files.id=taskfiles.file GROUP BY taskfiles.task) taskfiles ON taskfiles.task=tasks.id ORDER BY active DESC, tasks.priority DESC, tasks.id ASC");
	$res = $res->fetchAll();
	$tasks = array();
	foreach($res as $task){
		$set = new DataSet();
		$set->setValues($task);
		$tasks[] = $set;
	}
	
	$OBJECTS['numTasks'] = sizeof($tasks);
	$OBJECTS['tasks'] = $tasks;
}
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




