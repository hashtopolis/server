<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("agents");

$res = $FACTORIES::getBillFactory()->getDB()->query("SELECT agents.id,agents.uid,agents.active,agents.trusted,agents.cputype,agents.gpubrand,agents.gpudriver,agents.gpus,agents.hcversion,agents.lastact,agents.lasttime,agents.lastip,assignments.task,assignments.speed,agents.os,agents.name,IF(IFNULL(chunks.time,0)>".(time() - $CONFIG->getVal('chunktimeout')).",1,0) AS working FROM agents LEFT JOIN assignments ON agents.id=assignments.agent LEFT JOIN tasks ON assignments.task=tasks.id LEFT JOIN (SELECT agent,MAX(GREATEST(dispatchtime,solvetime)) AS time FROM chunks GROUP BY agent) chunks ON chunks.agent=agents.id ORDER BY agents.id ASC");
$OBJECTS['numAgents'] = sizeof($res);

// list agents
/*$kver=mysqli_query_wrapper($dblink,"SELECT agents.id,agents.uid,agents.active,agents.trusted,agents.cputype,agents.gpubrand,agents.gpudriver,agents.gpus,agents.hcversion,agents.lastact,agents.lasttime,agents.lastip,assignments.task,assignments.speed,agents.os,agents.name,IF(IFNULL(chunks.time,0)>".($cas-$config["chunktimeout"]).",1,0) AS working FROM agents LEFT JOIN assignments ON agents.id=assignments.agent LEFT JOIN tasks ON assignments.task=tasks.id LEFT JOIN (SELECT agent,MAX(GREATEST(dispatchtime,solvetime)) AS time FROM chunks GROUP BY agent) chunks ON chunks.agent=agents.id ORDER BY agents.id ASC");
echo "List of agents (".mysqli_num_rows($kver)."):";
echo "<table class=\"styled\">";
echo "<tr><td>id</td><td>Act</td><td>Name</td><td>OS</td><td>CPU</td><td>GPU brand</td><td>Driver</td><td>GPUs</td><td>Hashcat</td><td>Last activity</td><td>Assignment</td><td>Action</td></tr>";
while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
	$id=$erej["id"];
	echo "<tr><td><a href=\"$myself?a=agentdetail&agent=$id\">$id</a></td>";

	echo "<td><form id=\"active$id\" action=\"$myself?a=agentactive\" method=\"POST\">";
	echo "<input type=\"hidden\" name=\"agent\" value=\"$id\">";
	echo "<input type=\"hidden\" name=\"return\" value=\"a=agents\">";
        echo "<input type=\"checkbox\" name=\"active\" value=\"1\"";
        if ($erej["active"]==1) echo " checked";
        echo " onChange=\"javascript:document.getElementById('active$id').submit();\">";
        echo "</form></td>";

        echo "<td><a href=\"$myself?a=agentdetail&agent=$id\">".$erej["name"]."</a>";
        		if ($erej["trusted"]==1) echo " <img src=\"img/lock.gif\" alt=\"Trusted\">";
        if ($erej["working"]==1) echo " <img src=\"img/active.gif\" alt=\"Active\" title=\"".nicenum($erej["speed"],100000,1000)."H/s\">";
        if ($erej["active"]==0) echo " <img src=\"img/pause.gif\" alt=\"Paused\">";
        echo "</td><td>";
        		echo $oses[$erej["os"]];
        		echo "<td>".$erej["cputype"]."-bit</td><td>";
        		echo $platforms[$erej["gpubrand"]];
        		echo "</td><td>".$erej["gpudriver"]."</td><td>";
        		$gpus=explode($separator,$erej["gpus"]);
        		foreach ($gpus as $gpu) {
          shortenstring($gpu,32);
        	echo "<br>";
        }
        echo "</td><td>".$erej["hcversion"]."</td><td>".$erej["lastact"]." at ".date($config["timefmt"],$erej["lasttime"])."<br>IP: ".$erej["lastip"]."</td><td>";
        		$task=$erej["task"];
        echo "<form id=\"assign$id\" action=\"$myself?a=agentassign\" method=\"POST\">";
        echo "<input type=\"hidden\" name=\"return\" value=\"a=agents\">";
        echo "<input type=\"hidden\" name=\"agent\" value=\"$id\">";
        echo "<select name=\"task\" size=\"1\" onChange=\"javascript:document.getElementById('assign$id').submit();\"><option value=\"\">(unassigned)</option>";
        $kve=mysqli_query_wrapper($dblink,"SELECT id,name FROM tasks WHERE hashlist IS NOT NULL ORDER BY id ASC");
        while($ere=mysqli_fetch_array($kve,MYSQLI_ASSOC)) {
        $idu=$ere["id"];
        	$nameu=$ere["name"];
        	echo "<option value=\"$idu\"";
        	if ($idu==$task) echo " selected";
        	echo ">$nameu</option>";
        }
        echo "</select></form></td>";
        echo "<td><form action=\"$myself?a=agentdelete\" method=\"POST\" onSubmit=\"if (!confirm('Really delete agent $id?')) return false;\">";
        echo "<input type=\"hidden\" name=\"return\" value=\"a=agents\">";
        echo "<input type=\"hidden\" name=\"agent\" value=\"$id\">";
        echo "<input type=\"submit\" value=\"Delete\"></form></td></tr>";
      }
      echo "</table>";
        break;*/

echo $TEMPLATE->render($OBJECTS);




