<?php
$htpver="0.10.0";
$htphost=$_SERVER['HTTP_HOST'];
if (strpos($htphost,":")!==false) $htphost=substr($htphost,0,strpos($htphost,":"));
set_time_limit(0);
session_start();
include("dbconfig.php");

function mysqli_query_wrapper($dblink, $query, $bypass=false) {
  $log="\n<!-- $query";
  $time1=microtime(true);
  $kver=mysqli_query($dblink,$query);
  // uncomment this line to ditch the logs
  // $bypass=true
  if ($bypass==false) {
    $time2=microtime(true);
    echo $log;
    echo "\nTook: ".($time2-$time1);
    $afec=mysqli_affected_rows($dblink);
    if ($afec>=0) {
      echo ", affected: $afec";
    } else {
      echo ", error: ".mysqli_error($dblink);
    }
    echo " -->\n";
  }
  return $kver;
}

// kill cache to debug
//mysqli_query_wrapper($dblink,"SET SESSION query_cache_type = OFF");

$hashlistAlias="#HL#";
$myself=basename(__FILE__);
$cas=time();
$loadtime=microtime(true);
?><html>
<head>
  <title>Hashtopus <?php echo $htpver." [$htphost]"; ?></title>
  <link rel="icon" href="favicon.ico" type="image/x-icon"/>
  <link href='admin.css' rel='stylesheet' type='text/css'>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <!--<META http-equiv="cache-control" content="no-cache">-->
  <script type="text/javascript" src="jscolor/jscolor.js"></script>
  <script>
    function sourceChange(valu) {
      var pasteObject=document.getElementById("pasteLine");
      var uploadObject=document.getElementById("uploadLine");
      var importObject=document.getElementById("importLine");
      var downloadObject=document.getElementById("downloadLine");
      switch (valu) {
        case 'paste':
          pasteObject.style.display = '';
          uploadObject.style.display = 'none';
          importObject.style.display = 'none';
          downloadObject.style.display = 'none';
          break;
          
        case 'upload':
          pasteObject.style.display = 'none';
          uploadObject.style.display = '';
          importObject.style.display = 'none';
          downloadObject.style.display = 'none';
          break;
          
        case 'import':
          pasteObject.style.display = 'none';
          uploadObject.style.display = 'none';
          importObject.style.display = '';
          downloadObject.style.display = 'none';
          break;

        case 'url':
          pasteObject.style.display = 'none';
          uploadObject.style.display = 'none';
          importObject.style.display = 'none';
          downloadObject.style.display = '';
          break;
      }
    }
    function checkAll(formname, checktoggle)
    {
      var checkboxes = new Array(); 
      checkboxes = document.getElementById(formname).getElementsByTagName('input');
      for (var i=0; i<checkboxes.length; i++)  {
        if (checkboxes[i].type == 'checkbox')   {
          checkboxes[i].checked = checktoggle;
        }
      }
    }
  </script>
</head>
<body>
<?php
if (isset($_POST["pwd"])) {
  if (!isset($config["password"]) || makepwd($_POST["pwd"])==$config["password"]) $_SESSION[$sess_name]=1;
}

if (isset($_SESSION[$sess_name]) && $_SESSION[$sess_name]==1) {
  
  // define redirect place
  if (isset($_POST["return"])) {
    $returnpage=$_POST["return"];
  } else {
    $returnpage="";
  }

  // create the menu
  echo '<table class="big"><tr><td><a href="'.$myself.'"><img src="img/logo.png" alt="Hashtopus"></a><br><ul>
<li><a href="'.$myself.'?a=agents">Agents</a></li>
<li><a href="'.$myself.'?a=deploy">Agent deployer</a></li>
<br>
<li><a href="'.$myself.'?a=tasks">Tasks</a> (<a href="'.$myself.'?a=newtask">new</a>)</li>
<li><a href="'.$myself.'?a=ptasks">Pre-conf tasks</a></li>
<br>
<li><a href="'.$myself.'?a=hashlists">Hashlists</a> (<a href="'.$myself.'?a=newhashlist">new</a>)</li>
<li><a href="'.$myself.'?a=superhashlists">Superhashlists</a> (<a href="'.$myself.'?a=newsuperhashlist">new</a>)</li>
<br>
<li><a href="'.$myself.'?a=files">Global files</a></li>
<br>
<li><a href="'.$myself.'?a=chunks">Chunk activity</a></li>
<br>
<li><a href="'.$myself.'?a=releases">Hashcat releases</a> (<a href="'.$myself.'?a=newrelease">new</a>)</li>';

if (file_exists("custmenu.php")) {
  // create custom menu, should it be present
  // refer to custmenu file for its documentation in comments
  include("custmenu.php");
  $custmenu=true;
  echo "<br>";
  foreach ($custmenuitems as $action=>$menuitem) {
    if ($menuitem["condition"]) {
      echo "<li><a href=\"$myself?a=custmenu&menu=$action\">".$menuitem["name"]."</a></li>";
    }
  }
}

echo '</ul>
<hr>
<ul>
<li><a href="'.$myself.'?a=config">Server configuration</a></li>
<li><a href="'.$myself.'?a=manual">Manual</a></li>
<li><a href="'.$myself.'?a=logout">Log out</a></li>
</ul>v'.$htpver.'</td><td>';
  // correct password
  $platforms=array("unknown","NVidia","AMD");
  //$workloads=array(1=>"Low utilization",2=>"Default profile",3=>"High utilization");
  $oses=array("<img src=\"img/win.png\" alt=\"Win\" title=\"Windows\">","<img src=\"img/unix.png\" alt=\"Unix\" title=\"Linux\">");
  $states=array("New","Init","Running","Paused","Exhausted","Cracked","Aborted","Quit","Bypass","Trimmed","Aborting...");
  $formats=array("Text","HCCAP","Binary","Superhashlist");
  $formattables=array("hashes","hashes_binary","hashes_binary");
  $uperrs=array("","Uploaded file is too big for server settings, use different transfer method (i.e. FTP) and run directory scan in Task detail","Uploaded file is too big for form setting. Have you been playing with admin again?!","File upload was interrupted","No file was uploaded","","Server doesn't have a temporary folder","Failed writing to disk. Maybe no space left or >2GB file on FAT32","Some PHP module stopped the transfer");
  switch (isset($_GET["a"]) ? $_GET["a"] : "") {

    case "agents":
      // list agents
      $kver=mysqli_query_wrapper($dblink,"SELECT agents.id,agents.uid,agents.active,agents.trusted,agents.cputype,agents.gpubrand,agents.gpudriver,agents.gpus,agents.hcversion,agents.lastact,agents.lasttime,agents.lastip,assignments.task,assignments.speed,agents.os,agents.name,IF(IFNULL(chunks.time,0)>".($cas-$config["chunktimeout"]).",1,0) AS working FROM agents LEFT JOIN assignments ON agents.id=assignments.agent LEFT JOIN tasks ON assignments.task=tasks.id LEFT JOIN (SELECT agent,MAX(GREATEST(dispatchtime,solvetime)) AS time FROM chunks GROUP BY agent) chunks ON chunks.agent=agents.id ORDER BY agents.id ASC");
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
      break;
      
    case "agentauto":
      // enable agent benchmark autoadjust for its current assignment
      $agid=intval($_POST["agent"]);
      $auto=intval($_POST["auto"]);
      $vysledek=mysqli_query_wrapper($dblink,"UPDATE assignments SET autoadjust=$auto WHERE agent=$agid");
      if (!$vysledek) {
        echo "<script>alert('Could not change autoadjust!');</script>";
      }
      break;
    
    case "taskauto":
      // enable agent benchmark autoadjust for all subsequent agents added to this task
      $task=intval($_POST["task"]);
      $auto=intval($_POST["auto"]);
      $vysledek=mysqli_query_wrapper($dblink,"UPDATE tasks SET autoadjust=$auto WHERE id=$task");
      if (!$vysledek) {
        echo "<script>alert('Could not change autoadjust!');</script>";
      }
      break;

    case "agentignore":
      // switch error ignoring for agent
      $agid=intval($_POST["agent"]);
      $ignore=intval($_POST["ignore"]);
      $vysledek=mysqli_query_wrapper($dblink,"UPDATE agents SET ignoreerrors=$ignore WHERE id=$agid");
      if (!$vysledek) {
        echo "<script>alert('Could not change error ignoring!');</script>";
      }
      break;
    
    case "agentactive":
      // switch agent activity
      $agid=intval($_POST["agent"]);
      $active=intval($_POST["active"]);
      $vysledek=mysqli_query_wrapper($dblink,"UPDATE agents SET active=$active WHERE id=$agid");
      if (!$vysledek) {
        echo "<script>alert('Could not change agent activity!');</script>";
      }
      break;
    
    case "agenttrusted":
      // switch agent trusted state
      $agid=intval($_POST["agent"]);
      $trusted=intval($_POST["trusted"]);
      $vysledek=mysqli_query_wrapper($dblink,"UPDATE agents SET trusted=$trusted WHERE id=$agid");
      if (!$vysledek) {
        echo "<script>alert('Could not change agent trust!');</script>";
      }
      break;
    
    case "hashlistsecret":
      // switch hashlist secret state
      $hlist=intval($_POST["hashlist"]);
      $secret=intval($_POST["secret"]);
      $vysledek=mysqli_query_wrapper($dblink,"UPDATE hashlists SET secret=$secret WHERE id=$hlist");
      if ($secret==1) mysqli_query_wrapper($dblink,"DELETE FROM hashlistusers JOIN agents ON agents.id=hashlistusers.agent WHERE hashlistusers.hashlist=$hlist AND agents.trusted<$secret");
      if (!$vysledek) {
        echo "<script>alert('Could not change hashlist secrecy!');</script>";
      }
      break;
    
    case "filesecret":
      // switch global file secret state
      $fid=intval($_POST["file"]);
      $secret=intval($_POST["secret"]);
      $vysledek=mysqli_query_wrapper($dblink,"UPDATE files SET secret=$secret WHERE id=$fid");
      if (!$vysledek) {
        echo "<script>alert('Could not change global file secrecy!');</script>";
      }
      break;
    
    case "agentassign";
      // assign agent to a task or unassign it
      $agid=intval($_POST["agent"]);
      $task=intval($_POST["task"]);
      $vysledek=false;
      if ($task==0) {
        // unassign
        $vysledek=mysqli_query_wrapper($dblink,"DELETE FROM assignments WHERE agent=$agid");
      } else {
        $ori=mysqli_query_wrapper($dblink,"SELECT task FROM assignments WHERE agent=$agid");
        // keep the clever pre-bench query in variable
        $asskv="IFNULL((SELECT length FROM chunks WHERE solvetime>dispatchtime AND progress=length AND state IN (4,5) AND agent=$agid AND task=$task ORDER BY solvetime DESC LIMIT 1),0)";
        if ($terej=mysqli_fetch_array($ori,MYSQLI_ASSOC)) {
          // agent was assigned to something, change the assignment
          $vysledek=mysqli_query_wrapper($dblink,"UPDATE assignments JOIN tasks ON tasks.id=$task SET assignments.task=tasks.id,assignments.benchmark=$asskv,assignments.autoadjust=tasks.autoadjust,assignments.speed=0 WHERE assignments.agent=$agid");
        } else {
          // agent was not assigned, we need a new assignment
          $vysledek=mysqli_query_wrapper($dblink,"INSERT INTO assignments (task,agent,benchmark,autoadjust) SELECT id,$agid,$asskv,autoadjust FROM tasks WHERE id=$task");
        }
      }
      if (!$vysledek) {
        echo "Could not assign agent to task $task.";
        $returnpage="";
      }
      break;

    case "agentbench";
      // adjust agent benchmark
      $agid=intval($_POST["agent"]);
      $bench=floatval($_POST["bench"]);
      $vysledek=false;
      $vysledek=mysqli_query_wrapper($dblink,"UPDATE assignments SET benchmark=$bench WHERE agent=$agid");
      if (!$vysledek) {
        echo "<script>alert('Could not set benchmark!');</script>";
      }
      break;

    case "agentdelete";
      // delete agent from the system
      $agid=intval($_POST["agent"]);
      mysqli_query_wrapper($dblink,"START TRANSACTION");
      if (delete_agent($agid)) {
        mysqli_query_wrapper($dblink,"COMMIT");
      } else {
        mysqli_query_wrapper($dblink,"ROLLBACK");
        echo "<script>alert('Could not delete agent!');</script>";
      }
      break;
      
    case "voucherdelete";
      // delete registration voucher 
      $id=$_POST["voucher"];
      $vysledek=mysqli_query_wrapper($dblink,"DELETE FROM regvouchers WHERE voucher='".mysqli_real_escape_string($dblink,$id)."'");
      if (!$vysledek) {
        echo "<script>alert('Could not delete registration voucher!');</script>";
      }
      break;
      
    case "filedelete";
      // delete global file
      $fid=intval($_POST["file"]);
      mysqli_query_wrapper($dblink,"START TRANSACTION");
      $kver=mysqli_query_wrapper($dblink,"SELECT * FROM files WHERE id=$fid");
      if ($erej=mysqli_fetch_array($kver)) {
        $fname=$erej["filename"];
        $kver=mysqli_query_wrapper($dblink,"SELECT 1 FROM taskfiles WHERE file=$fid");
        if (mysqli_num_rows($kver)>0) {
          // file is used
          echo "<script>alert('File is used in a task.');</script>";
        } else {
          $vysledek1=mysqli_query_wrapper($dblink,"DELETE FROM files WHERE id=$fid");
          if ($vysledek1) {
            if (file_exists("files/".$fname)) {
              $vysledek2=unlink("files/".$fname);
            } else {
              $vysledek2=true;
            }
          }
          if ($vysledek1 && $vysledek2) {
            mysqli_query_wrapper($dblink,"COMMIT");
          } else {
            mysqli_query_wrapper($dblink,"ROLLBACK");
            echo "<script>alert('Could not delete file!');</script>";
          }
        }
      } else {
        echo "<script>alert('Such file is not defined.');</script>";
      }
      break;
      
    case "releasedelete";
      // delete hashcat release
      $release=mysqli_real_escape_string($dblink,$_POST["release"]);
      mysqli_query_wrapper($dblink,"START TRANSACTION");
      $kv=mysqli_query_wrapper($dblink,"SELECT * FROM agents WHERE hcversion='$release'");
      if (mysqli_num_rows($kv)>0) {
        echo "<script>alert('There are registered agents running this Hashcat version.');</script>";
      } else {
        $vysledek1=mysqli_query_wrapper($dblink,"DELETE FROM hashcatreleases WHERE version='$release'");
        if ($vysledek1) {
          mysqli_query_wrapper($dblink,"COMMIT");
        } else {
          mysqli_query_wrapper($dblink,"ROLLBACK");
          echo "<script>alert('Could not delete Hashcat release!');</script>";
        }
      }
      break;
      
    case "hashlistdelete";
      // delete hashlist
      $hlist=intval($_POST["hashlist"]);
      mysqli_query_wrapper($dblink,"START TRANSACTION");
      $kv=mysqli_query_wrapper($dblink,"SELECT hashlists.format,hashlists.hashcount FROM hashlists WHERE hashlists.id=$hlist");
      $erej=mysqli_fetch_array($kv,MYSQLI_ASSOC);
      $hcount=$erej["hashcount"];

      // decrease supercount by that of deleted hashlist
      $vysledek0=mysqli_query_wrapper($dblink,"UPDATE hashlists JOIN superhashlists ON superhashlists.id=hashlists.id AND hashlists.format=3 AND superhashlists.hashlist=$hlist JOIN hashlists hashlists2 ON hashlists2.id=superhashlists.hashlist SET hashlists.cracked=hashlists.cracked-hashlists2.cracked,hashlists.hashcount=hashlists.hashcount-hashlists2.hashcount");

      // then actually delete the list
      $vysledek1=$vysledek0 && mysqli_query_wrapper($dblink,"DELETE FROM hashlists WHERE id=$hlist");
      $vysledek2=$vysledek1 && mysqli_query_wrapper($dblink,"DELETE FROM hashlistusers WHERE hashlist=$hlist");
      $vysledek3=$vysledek2 && mysqli_query_wrapper($dblink,"DELETE FROM zapqueue WHERE hashlist=$hlist");
      
      // and its tasks
      $vysledek4=$vysledek3 && mysqli_query_wrapper($dblink,"DELETE FROM taskfiles WHERE task IN (SELECT id FROM tasks WHERE hashlist=$hlist)");
      $vysledek5=$vysledek4 && mysqli_query_wrapper($dblink,"DELETE FROM assignments WHERE task IN (SELECT id FROM tasks WHERE hashlist=$hlist)");
      $vysledek6=$vysledek5 && mysqli_query_wrapper($dblink,"DELETE FROM chunks WHERE task IN (SELECT id FROM tasks WHERE hashlist=$hlist)");
      $vysledek7=$vysledek6 && mysqli_query_wrapper($dblink,"DELETE FROM tasks WHERE hashlist=$hlist");
      
      $vysledek8=$vysledek7 && mysqli_query_wrapper($dblink,"DELETE FROM superhashlists WHERE hashlist=$hlist");
      if ($vysledek8) {
        mysqli_query_wrapper($dblink,"COMMIT");
        echo "Deleted hashlist and associated zaps.<br>";
        switch ($erej["format"]) {
          case 0:
            $radku=mysqli_query_wrapper($dblink,"SELECT 1 FROM hashlists WHERE format=0");
            if (mysqli_num_rows($radku)>0) {
              echo "Deleting the actual rows (this is going to take A LONG TIME!)...<br>";
              $hdelete=0;
              $kolik=1;
              $cas_pinfo=time();
              $cas_start=time();
              mysqli_query_wrapper($dblink,"START TRANSACTION");
              while($kolik>0) {
                $kver="DELETE FROM ".$formattables[$erej["format"]]." WHERE hashlist=$hlist LIMIT 1000";
                $vysledek1=mysqli_query_wrapper($dblink,$kver);
                $kolik=mysqli_affected_rows($dblink);
                $hdelete+=$kolik;
                if (time()>=$cas_pinfo+10) {
                  echo "Progress: $hdelete/$hcount, time spent: ".(time()-$cas_start)." sec<br>";
                  flush();
                  mysqli_query_wrapper($dblink,"COMMIT");
                  mysqli_query_wrapper($dblink,"START TRANSACTION");
                  $cas_pinfo=time();
                }
              }
              mysqli_query_wrapper($dblink,"COMMIT");
            } else {
              echo "This was the last hashlist, truncating the table.";
              mysqli_query_wrapper($dblink,"TRUNCATE TABLE ".$formattables[$erej["format"]]);
            }
            break;
            
          case 1:
          case 2:
            echo "Deleting binary hashes...<br>";
            mysqli_query_wrapper($dblink,"DELETE FROM hashes_binary WHERE hashlist=$hlist");
            break;
            
          case 3:
            echo "Deleting superhashlist links...<br>";
            mysqli_query_wrapper($dblink,"DELETE FROM superhashlists WHERE id=$hlist");
            break;
        }
        echo "Done.";
      } else {
        mysqli_query_wrapper($dblink,"ROLLBACK");
        echo "Problems deleting hashlist!";
        $returnpage="";
      }
      break;

    case "agentpf";
      // change agent platform (none/nvidia/amd)
      $agid=intval($_POST["agent"]);
      $pf=intval($_POST["platform"]);
      $vysledek=mysqli_query_wrapper($dblink,"UPDATE agents SET gpubrand=$pf,gpudriver=0 WHERE id=$agid");
      if (!$vysledek) {
        echo "<script>alert('Could not change platform!');</script>";
      }
      break;
      
    case "agentwait";
      // change agent waiting time for idle
      $agid=intval($_POST["agent"]);
      $wait=intval($_POST["wait"]);
      $vysledek=mysqli_query_wrapper($dblink,"UPDATE agents SET wait=$wait WHERE id=$agid");
      if (!$vysledek) {
        echo "<script>alert('Could not change agent idle wait period!');</script>";
      }
      break;
      
    case "agentpars";
      // change agent extra cmd line parameters for hashcat
      $agid=intval($_POST["agent"]);
      $pars=mysqli_real_escape_string($dblink,$_POST["cmdpars"]);
      $vysledek=mysqli_query_wrapper($dblink,"UPDATE agents SET cmdpars='$pars' WHERE id=$agid");
      if (!$vysledek) {
        echo "<script>alert('Could not change agent-specific parameters!');</script>";
      }
      break;
      
    case "chunkreset";
      // reset chunk state and progress to zero
      $chunk=intval($_POST["chunk"]);
      $vysledek=mysqli_query_wrapper($dblink,"UPDATE chunks SET state=0,progress=0,rprogress=0,dispatchtime=$cas,solvetime=0 WHERE id=$chunk");
      if (!$vysledek) {
        echo "<script>alert('Could not reset chunk!');</script>";
      }
      break;
      
    case "chunkabort";
      // reset chunk state and progress to zero
      $chunk=intval($_POST["chunk"]);
      $vysledek=mysqli_query_wrapper($dblink,"UPDATE chunks SET state=10 WHERE id=$chunk");
      if (!$vysledek) {
        echo "<script>alert('Could not abort chunk!');</script>";
      }
      break;
      
    case "taskprio";
      // change task priority
      $task=intval($_POST["task"]);
      $prio=intval($_POST["priority"]);
      $kv=mysqli_query_wrapper($dblink,"SELECT 1 FROM tasks WHERE tasks.priority=$prio AND tasks.id!=$task AND tasks.priority>0 AND SIGN(IFNULL(tasks.hashlist,0))=(SELECT SIGN(IFNULL(hashlist,0)) FROM tasks WHERE id=$task) LIMIT 1");
      if (mysqli_num_rows($kv)==1) { 
        // must be unique
        echo "<script>alert('Each task has to have unique priority!');</script>";
      } else {
        $vysledek=mysqli_query_wrapper($dblink,"UPDATE tasks SET priority=$prio WHERE id=$task");
        if (!$vysledek) {
          echo "<script>alert('Could not change priority!');</script>";
        }
      }
      break;

    case "taskcolor";
      // change task color
      $task=intval($_POST["task"]);
      $color=$_POST["color"];
      if (preg_match("/[0-9A-Za-z]{6}/",$color)==1) {
        $color="'$color'";
      } else {
       $color="NULL"; 
      }
      $vysledek=mysqli_query_wrapper($dblink,"UPDATE tasks SET color=$color WHERE id=$task");
      if (!$vysledek) {
        echo "<script>alert('Could not change color!');</script>";
      }
      break;

    case "taskrename";
      // change task name
      $task=intval($_POST["task"]);
      $name=mysqli_real_escape_string($dblink,$_POST["name"]);
      $kv=mysqli_query_wrapper($dblink,"UPDATE tasks SET name='$name' WHERE id=$task");
      if (!$kv) { 
        echo "<script>alert('Could not rename task!');</script>";
      }
      break;

    case "hashlistrename";
      // change hashlist name
      $hlist=intval($_POST["hashlist"]);
      $name=mysqli_real_escape_string($dblink,$_POST["name"]);
      $kv=mysqli_query_wrapper($dblink,"UPDATE hashlists SET name='$name' WHERE id=$hlist");
      if (!$kv) { 
        echo "<script>alert('Could not rename hashlist!');</script>";
      }
      break;

    case "taskpurge";
      // delete all task chunks, forget its keyspace value and reset progress to zero
      $task=intval($_POST["task"]);
      mysqli_query_wrapper($dblink,"START TRANSACTION");
      $vysledek1=mysqli_query_wrapper($dblink,"UPDATE assignments SET benchmark=0 WHERE task=$task");
      $vysledek2=mysqli_query_wrapper($dblink,"UPDATE hashes SET chunk=NULL WHERE chunk IN (SELECT id FROM chunks WHERE task=$task)");
      $vysledek3=mysqli_query_wrapper($dblink,"UPDATE hashes_binary SET chunk=NULL WHERE chunk IN (SELECT id FROM chunks WHERE task=$task)");
      $vysledek4=mysqli_query_wrapper($dblink,"DELETE FROM zapqueue WHERE chunk IN (SELECT id FROM chunks WHERE task=$task)");
      $vysledek5=mysqli_query_wrapper($dblink,"DELETE FROM chunks WHERE task=$task");
      $vysledek6=mysqli_query_wrapper($dblink,"UPDATE tasks SET keyspace=0,progress=0 WHERE id=$task");
      if ($vysledek1 && $vysledek2 && $vysledek3 && $vysledek4 && $vysledek5 && $vysledek6) { 
        mysqli_query_wrapper($dblink,"COMMIT");
      } else {
        mysqli_query_wrapper($dblink,"ROLLBACK");
        echo "<script>alert('Could not purge task!');</script>";
      }
      break;

    case "rebuildcache";
      // db utility to recalculate cache for all chunks, hashlists and tasks
      // slow
      $kv=mysqli_query_wrapper($dblink,"UPDATE chunks LEFT JOIN (SELECT chunk,COUNT(1) AS cracked FROM hashes GROUP BY chunk) hashes ON chunks.id=hashes.chunk LEFT JOIN (SELECT chunk,COUNT(1) AS cracked FROM hashes_binary GROUP BY chunk) hashes_binary ON chunks.id=hashes_binary.chunk SET chunks.cracked=IFNULL(hashes.cracked,0)+IFNULL(hashes_binary.cracked,0)");
      $changed=mysqli_affected_rows($dblink);
      echo "Chunk cache rebuilt on $changed chunks!<br>";
      $kv=mysqli_query_wrapper($dblink,"UPDATE hashes SET chunk=NULL WHERE chunk!=0 AND chunk NOT IN (SELECT id FROM chunks)");
      $changed=mysqli_affected_rows($dblink);
      echo "Cleaned $changed orphaned text hashes!<br>";
      $kv=mysqli_query_wrapper($dblink,"UPDATE hashes_binary SET chunk=NULL WHERE chunk!=0 AND chunk NOT IN (SELECT id FROM chunks)");
      $changed=mysqli_affected_rows($dblink);
      echo "Cleaned $changed orphaned binary hashes!<br>";
      $kv=mysqli_query_wrapper($dblink,"UPDATE hashlists LEFT JOIN (SELECT hashlist,COUNT(1) AS htotal, COUNT(plaintext) AS ctotal FROM hashes GROUP BY hashlist) hashes ON hashlists.format=0 AND hashlists.id=hashes.hashlist LEFT JOIN (SELECT hashlist,COUNT(1) AS htotal, COUNT(plaintext) AS ctotal FROM hashes_binary GROUP BY hashlist) hashes_binary ON hashlists.format IN (1,2) AND hashlists.id=hashes_binary.hashlist SET hashlists.hashcount=IF(hashlists.format=0,hashes.htotal,hashes_binary.htotal),hashlists.cracked=IF(hashlists.format=0,hashes.ctotal,hashes_binary.ctotal) WHERE hashlists.format!=3");
      $changed=mysqli_affected_rows($dblink);
      echo "Hash count rebuilt on $changed hashlists!<br>";
      $kv=mysqli_query_wrapper($dblink,"UPDATE hashlists JOIN (SELECT superhashlists.id,SUM(hashlists.hashcount) AS hashcount,SUM(hashlists.cracked) AS cracked FROM superhashlists JOIN hashlists ON superhashlists.hashlist=hashlists.id GROUP BY superhashlists.id) super ON super.id=hashlists.id SET hashlists.cracked=super.cracked,hashlists.hashcount=super.hashcount");
      $changed=mysqli_affected_rows($dblink);
      echo "Hash count rebuilt on $changed superhashlists!<br>";
      break;

    case "rescanfiles";
      // db utility to rescan all files in system, chceck if their size match or if they exist
      // and attempt to fix problems
      // fast
      $kv=mysqli_query_wrapper($dblink,"SELECT * FROM files ORDER BY filename");
      while($erej=mysqli_fetch_array($kv,MYSQLI_ASSOC)) {
        $id=$erej["id"];
        $fil=$erej["filename"];
        if (file_exists("files/$fil")) {
          if (is_dir("files/$fil")) {
            mysqli_query_wrapper($dblink,"DELETE FROM files WHERE id=$id");
            echo "<b>File $fil was a directory, deleted from database</b>";
          } else {
            $nsize=filesize("files/$fil");
            $size=$erej["size"];
            if ($nsize==$size) {
              echo "File $fil OK.";
            } else {
              mysqli_query_wrapper($dblink,"UPDATE files SET size=$nsize WHERE id=$id");
              echo "<b>File $fil had wrong size, corrected from $size to $nsize</b>";
            }
          }
        } else {
          echo "<b>File $fil does not exist, checking usage in tasks...</b>";
          $us=mysqli_query_wrapper($dblink,"SELECT tasks.name FROM taskfiles JOIN tasks ON tasks.id=taskfiles.task WHERE file=$id");
          if (mysqli_num_rows($us)>0) {
            echo "FILE IS USED IN FOLLOWING TASKS:";
            while ($ere=mysqli_fetch_array($us,MYSQLI_ASSOC)) {
              echo "<br>- ".$ere["name"];
            }
          } else {
            echo "file is not used, deleting...";
            mysqli_query_wrapper($dblink,"DELETE FROM files WHERE id=$id");
          }
          echo "</b>";
        }
        echo "<br>";
      }
      break;

    case "clearall";
      // db utility to delete everything except agents and pre-conf tasks from the system
      // very fast - no logic here at all
      if (isset($_SESSION["cleartoken"])) {
        $cts=$_SESSION["cleartoken"];
      }
      if (isset($_GET["cleartoken"])) {
        $ctg=$_GET["cleartoken"]; 
        if ($ctg==$cts && $ctg!="") {
          unset($_SESSION["cleartoken"]);
          echo "Deleting tasks...<br>";
          mysqli_query_wrapper($dblink,"TRUNCATE TABLE assignments");
          mysqli_query_wrapper($dblink,"DELETE FROM tasks WHERE hashlist IS NOT NULL");
          mysqli_query_wrapper($dblink,"DELETE FROM taskfiles WHERE task NOT IN (SELECT id FROM tasks)");
          echo "Deleting chunks...<br>";
          mysqli_query_wrapper($dblink,"TRUNCATE TABLE chunks");
          echo "Deleting hashlists...<br>";
          mysqli_query_wrapper($dblink,"TRUNCATE TABLE hashlists");
          mysqli_query_wrapper($dblink,"TRUNCATE TABLE hashes");
          mysqli_query_wrapper($dblink,"TRUNCATE TABLE hashes_binary");
          echo "Deleted all.";
        } else {
          echo "You clicked the wrong link.";
        }
      } else {
        $cts=generate_random(10);
        $_SESSION["cleartoken"]=$cts;
        echo "<img src=\"img/pause.gif\"> <a href=\"$myself?a=clearall&cleartoken=$cts\">Really delete all data</a> <img src=\"img/pause.gif\">";
      }
      break;
      
    case "preconf";
      // deploy selected pre-configured tasks on a hashlist
      $hlist=intval($_POST["hashlist"]);
      $addc=0; $filc=0;
      if (isset($_POST["task"])) {
        $kv=mysqli_query_wrapper($dblink,"SELECT IFNULL(MAX(priority),0) AS base FROM tasks WHERE hashlist IS NOT NULL");
        $erej=mysqli_fetch_array($kv,MYSQLI_ASSOC);
        $base=$erej["base"];
        foreach($_POST["task"] as $ida) {
          $id=intval($ida);
          if ($id>0) {
            $kv=mysqli_query_wrapper($dblink,"SELECT name,attackcmd,chunktime,statustimer,autoadjust,priority FROM tasks WHERE tasks.id=$id");
            $erej=mysqli_fetch_array($kv,MYSQLI_ASSOC);
            $addq=mysqli_query_wrapper($dblink,"INSERT INTO tasks (name, attackcmd, hashlist, chunktime, statustimer, autoadjust, priority) VALUES ('HL".$hlist."_".mysqli_real_escape_string($dblink,$erej["name"])."', '".mysqli_real_escape_string($dblink,$erej["attackcmd"])."', $hlist, ".$erej["chunktime"].", ".$erej["statustimer"].", ".$erej["autoadjust"].", ".($erej["priority"]>0 ? $base+$erej["priority"] : 0).")");
            $addc+=mysqli_affected_rows($dblink);
            $tid=mysqli_insert_id($dblink);
            $filq=mysqli_query_wrapper($dblink,"INSERT INTO taskfiles (task, file) SELECT $tid,file FROM taskfiles WHERE task=$id"); 
            $filc+=mysqli_affected_rows($dblink);
            echo "Added task $id as $tid.<br>";
          }
        }
      }
      if ($addc==0) { 
        echo "No new tasks were created!";
      } else {
        $returnpage="a=tasks";
      }
      break;
      
    case "taskchunk";
      // update task chunk time
      $task=intval($_POST["task"]);
      $chunktime=intval($_POST["chunktime"]);
      mysqli_query_wrapper($dblink,"SET autocommit = 0");
      $vysledek1=mysqli_query_wrapper($dblink,"UPDATE assignments JOIN tasks ON tasks.id=assignments.task SET assignments.benchmark=(assignments.benchmark/tasks.chunktime)*$chunktime WHERE assignments.task=$task");
      $vysledek2=mysqli_query_wrapper($dblink,"UPDATE tasks SET chunktime=$chunktime WHERE id=$task");
      if ($vysledek1 && $vysledek2) {
        mysqli_query_wrapper($dblink,"COMMIT");
      } else {
        mysqli_query_wrapper($dblink,"ROLLBACK");
        echo "<script>alert('Could not update task chunk time!');</script>";
      }
      break;
      
    case "taskdelete";
      // delete a task
      $task=intval($_POST["task"]);
      mysqli_query_wrapper($dblink,"START TRANSACTION");
      if (delete_task($task)) {
        mysqli_query_wrapper($dblink,"COMMIT");
      } else {
        mysqli_query_wrapper($dblink,"ROLLBACK");
        echo "<script>alert('Could not delete task!');</script>";
      }
      break;
      
    case "finishedtasksdelete";
      // delete finished tasks
      $sez=mysqli_query_wrapper($dblink,"SELECT tasks.id,hashlists.format,tasks.hashlist FROM tasks JOIN hashlists ON tasks.hashlist=hashlists.id JOIN (SELECT task,SUM(progress) AS sumprog FROM chunks WHERE rprogress=10000 GROUP BY task) chunks ON chunks.task=tasks.id WHERE (tasks.progress=tasks.keyspace AND chunks.sumprog=tasks.keyspace) OR hashlists.cracked=hashlists.hashcount");
      $seznam=array();
      // load the tasks first
      while($erej=mysqli_fetch_array($sez,MYSQLI_ASSOC)) {
        $seznam[]=$erej;
      }
      // then process them
      foreach ($seznam as $ttd) {  
        $task=$ttd["id"];
        $format=$ttd["format"];
        $hlist=$ttd["hashlist"];
        mysqli_query_wrapper($dblink,"START TRANSACTION");
        if (delete_task($task)) {
          mysqli_query_wrapper($dblink,"COMMIT");
          echo "Deleted task $task<br>";
        } else {
          mysqli_query_wrapper($dblink,"ROLLBACK");
          echo "<script>alert('Could not delete task $task');</script><br>";
        }
      }
      break;

    case "tasks":
      // list tasks
      $kver=mysqli_query_wrapper($dblink,"SELECT tasks.id AS task,tasks.chunktime,tasks.priority,tasks.color,tasks.name,tasks.attackcmd,tasks.hashlist,tasks.progress,IFNULL(chunks.sumprog,0) AS sumprog,tasks.keyspace,IFNULL(chunks.pcount,0) AS pcount,IFNULL(chunks.ccount,0) AS ccount,IFNULL(taskfiles.secret,0) AS secret,IF(chunks.lastact>".($cas-$config["chunktimeout"]).",1,0) AS active,IFNULL(assignments.acount,0) AS assigncount,taskfiles.fcount AS filescount,IFNULL(taskfiles.fsize,0) AS filesize,hashlists.name AS hname,hashlists.secret AS hsecret,IF(hashlists.cracked<hashlists.hashcount,1,0) AS hlinc FROM tasks JOIN hashlists ON hashlists.id=tasks.hashlist LEFT JOIN (SELECT task,SUM(cracked) AS pcount,COUNT(1) AS ccount,GREATEST(MAX(dispatchtime),MAX(solvetime)) AS lastact,SUM(progress) AS sumprog FROM chunks GROUP BY task) chunks ON chunks.task=tasks.id LEFT JOIN (SELECT task,COUNT(1) AS acount FROM assignments GROUP BY task) assignments ON assignments.task=tasks.id LEFT JOIN (SELECT taskfiles.task,COUNT(1) AS fcount,SUM(files.size) AS fsize,MAX(files.secret) AS secret FROM taskfiles JOIN files ON files.id=taskfiles.file GROUP BY taskfiles.task) taskfiles ON taskfiles.task=tasks.id ORDER BY active DESC, tasks.priority DESC, tasks.id ASC");
      echo "List of tasks (".mysqli_num_rows($kver)."): ";

      echo "<form action=\"$myself?a=finishedtasksdelete\" method=\"POST\" onSubmit=\"if (!confirm('Really delete all finished tasks?')) return false;\">";
      echo "<input type=\"hidden\" name=\"return\" value=\"a=tasks\">";
      echo "<input type=\"submit\" value=\"Delete finished\"></form>";
      
      echo "<table class=\"styled\">";
      echo "<tr><td>id</td><td>Name</td><td>Hashlist</td><td>Chunks</td><td>Dispatched</td><td>Searched</td><td>Cracked</td><td>Agents</td><td>Files</td><td>Priority</td><td>Action</td></tr>";
      while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
        $id=$erej["task"];
        $hlist=$erej["hashlist"];
        echo "<tr><td";
        if (strlen($erej["color"])>0) {
          echo " style=\"background-color: #".$erej["color"]."\"";
        }
        echo "><a href=\"$myself?a=taskdetail&task=$id\">$id</a></td><td title=\"".$erej["attackcmd"]."\"><a href=\"$myself?a=taskdetail&task=$id\">".$erej["name"]."</a>";
        echo tickdone($erej["sumprog"],$erej["keyspace"]);
        if ($erej["active"]==1 && $erej["sumprog"]<$erej["keyspace"]) echo " <img src=\"img/active.gif\" alt=\"Active\">";
        echo "</td><td>";
        if ($hlist=="") {
          echo "(pre-configured task)";
        } else {
          echo "<a href=\"$myself?a=hashlistdetail&hashlist=$hlist\">".$erej["hname"]."</a>";
          if ($erej["hsecret"]==1) echo " <img src=\"img/lock.gif\" alt=\"Secret\">";
          echo tickdone(($erej["hlinc"]==0 ? 1 : 0),1);
        }
        echo "</td><td class=\"num\">".$erej["ccount"]." @ ".$erej["chunktime"]."s</td>";
        if ($erej["keyspace"]>0) {
          echo "<td class=\"num\">";
          echo showperc($erej["progress"],$erej["keyspace"])."%";
          echo "</td><td class=\"num\">";
          echo showperc($erej["sumprog"],$erej["keyspace"])."%";
        } else {
          echo "<td colspan=\"2\">Keyspace unknown";
        }
        echo "</td><td class=\"num\">";
        if ($erej["pcount"]>0) echo "<a href=\"$myself?a=hashes&task=$id\">".$erej["pcount"]."</a>";
        echo "</td><td class=\"num\">";
        if ($erej["assigncount"]>0) echo $erej["assigncount"];
        echo "</td><td>";
        if ($erej["filescount"]>0) echo $erej["filescount"]." (".nicenum($erej["filesize"])."B)";
        if ($erej["secret"]==1) echo " <img src=\"img/lock.gif\" alt=\"Secret\">";
        echo "</td>";

        echo "<td><form action=\"$myself?a=taskprio\" method=\"POST\">";
        echo "<input type=\"hidden\" name=\"return\" value=\"a=tasks\">";
        echo "<input type=\"hidden\" name=\"task\" value=\"$id\">";
        echo "<input type=\"text\" name=\"priority\" size=\"4\" value=\"".$erej["priority"]."\"><input type=\"submit\" value=\"Set\"></form></td>";
        
        echo "<td><form action=\"$myself\" method=\"GET\">";
        echo "<input type=\"hidden\" name=\"a\" value=\"newtask\">";
        echo "<input type=\"hidden\" name=\"task\" value=\"$id\">";
        echo "<input type=\"submit\" value=\"Copy\"></form> ";

        echo "<form action=\"$myself?a=taskdelete\" method=\"POST\" onSubmit=\"if (!confirm('Really delete task $id?')) return false;\">";
        echo "<input type=\"hidden\" name=\"return\" value=\"a=tasks\">";
        echo "<input type=\"hidden\" name=\"task\" value=\"$id\">";
        echo "<input type=\"submit\" value=\"Delete\"></form></td>";

        echo "</tr>";
        if ($erej["sumprog"]<$erej["keyspace"]) echo "<tr><td colspan=\"11\"><img style=\"width: 100%; height: 6px;\" src=\"taskimg.php?task=$id&x=800&y=6\"></td></tr>";
      }
      echo "</table>";
      break;

    case "ptasks":
      // list pre-conf tasks
      $kver=mysqli_query_wrapper($dblink,"SELECT tasks.id,tasks.name,tasks.color,tasks.attackcmd,tasks.priority,taskfiles.fcount AS filescount,IFNULL(taskfiles.fsize,0) AS filesize,IFNULL(taskfiles.secret,0) AS secret FROM tasks LEFT JOIN (SELECT taskfiles.task,COUNT(1) AS fcount,SUM(files.size) AS fsize,MAX(files.secret) AS secret FROM taskfiles JOIN files ON files.id=taskfiles.file GROUP BY taskfiles.task) taskfiles ON taskfiles.task=tasks.id WHERE tasks.hashlist IS NULL ORDER by tasks.priority DESC, tasks.id ASC");
      echo "List of pre-configured tasks (".mysqli_num_rows($kver)."):";
      echo "<table class=\"styled\">";
      echo "<tr><td>id</td><td>Name</td><td>Attack command</td><td>Files</td><td>Priority</td><td>Action</td></tr>";
      while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
        $id=$erej["id"];
        echo "<tr><td";
        if (strlen($erej["color"])>0) {
          echo " style=\"background-color: #".$erej["color"]."\"";
        }
        echo "><a href=\"$myself?a=taskdetail&task=$id\">$id</a></td><td><a href=\"$myself?a=taskdetail&task=$id\">".$erej["name"]."</a>";
        echo "</td><td>";
        echo $erej["attackcmd"];
        echo "</td><td>";
        if ($erej["filescount"]>0) echo $erej["filescount"]." (".nicenum($erej["filesize"])."B)";
        if ($erej["secret"]==1) echo " <img src=\"img/lock.gif\" alt=\"Secret\">";
        echo "</td><td>";

        echo "<form action=\"$myself?a=taskprio\" method=\"POST\">";
        echo "<input type=\"hidden\" name=\"return\" value=\"a=ptasks\">";
        echo "<input type=\"hidden\" name=\"task\" value=\"$id\">";
        echo "<input type=\"text\" name=\"priority\" size=\"4\" value=\"".$erej["priority"]."\"><input type=\"submit\" value=\"Set\"></form></td>";

        echo "<td><form action=\"$myself\" method=\"GET\">";
        echo "<input type=\"hidden\" name=\"a\" value=\"newtask\">";
        echo "<input type=\"hidden\" name=\"task\" value=\"$id\">";
        echo "<input type=\"submit\" value=\"Copy\"></form> ";

        echo "<form action=\"$myself?a=taskdelete\" method=\"POST\" onSubmit=\"if (!confirm('Really delete task $id?')) return false;\">";
        echo "<input type=\"hidden\" name=\"return\" value=\"a=ptasks\">";
        echo "<input type=\"hidden\" name=\"task\" value=\"$id\">";
        echo "<input type=\"submit\" value=\"Delete\"></form></td></tr>";
      }
      echo "</table>";
      break;
      
    case "hashlists":
      // list hashlists
      $kver=mysqli_query_wrapper($dblink,"SELECT hashlists.id,hashlists.name,hashlists.hashtype,hashlists.format,hashlists.hashcount,hashlists.cracked,hashlists.secret,hashtypes.description FROM hashlists LEFT JOIN hashtypes ON hashtypes.id=hashlists.hashtype WHERE format!=3 ORDER BY id ASC");
      echo "List of hashlists (".mysqli_num_rows($kver)."):";

      echo "<table class=\"styled\">";
      echo "<tr><td>id</td><td>Name</td><td>Hash type</td><td>Format</td><td>Cracked</td><td>Pre-cracked</td><td>Action</td></tr>";
      while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
        $id=$erej["id"];
        echo "<tr><td><a href=\"$myself?a=hashlistdetail&hashlist=$id\">$id</a></td><td><a href=\"$myself?a=hashlistdetail&hashlist=$id\">".$erej["name"]."</a>";
        if ($erej["secret"]==1) echo " <img src=\"img/lock.gif\" alt=\"Secret\">";
        echo tickdone($erej["cracked"],$erej["hashcount"]);
        echo "</td><td>";
        echo ($erej["description"]=="" ? $erej["hashtype"] : $erej["description"]);
        echo "</td><td>";
        echo $formats[$erej["format"]];
        echo "</td><td class=\"num\">";
        echo showperc($erej["cracked"],$erej["hashcount"])."%<br>";
        echo "(<a href=\"$myself?a=hashes&hashlist=$id&filter=cracked\">".$erej["cracked"]."</a> / <a href=\"$myself?a=hashes&hashlist=$id\">".$erej["hashcount"]."</a>)";
        echo "</td>";
        echo "<td><form action=\"$myself\" method=\"GET\">";
        echo "<input type=\"hidden\" name=\"a\" value=\"hashlistzap\">";
        echo "<input type=\"hidden\" name=\"hashlist\" value=\"$id\">";
        echo "<input type=\"submit\" value=\"Import\"></form>";
        echo "<form action=\"$myself\" method=\"GET\"> ";
        echo "<input type=\"hidden\" name=\"a\" value=\"export\">";
        echo "<input type=\"hidden\" name=\"hashlist\" value=\"$id\">";
        echo "<input type=\"submit\" value=\"Export\"></form></td>";
        echo "<td><form action=\"$myself?a=hashlistdelete\" method=\"POST\" onSubmit=\"if (!confirm('Really delete hashlist $id?')) return false;\">";
        echo "<input type=\"hidden\" name=\"return\" value=\"a=hashlists\">";
        echo "<input type=\"hidden\" name=\"hashlist\" value=\"$id\">";
        echo "<input type=\"submit\" value=\"Delete\"></form></td>";
        echo "</tr>";
      }
      echo "</table>";
      break;

    case "superhashlists":
      // list superhashlists
      $kver=mysqli_query_wrapper($dblink,"SELECT hashlists.id,hashlists.name,hashlists.secret,hashlists.hashtype,hashlists.format,hashlists.hashcount,hashlists.cracked,GROUP_CONCAT(hashlists2.name ORDER BY hashlists2.id SEPARATOR '<br>') AS lists,hashtypes.description FROM hashlists LEFT JOIN hashtypes ON hashtypes.id=hashlists.hashtype JOIN superhashlists ON superhashlists.id=hashlists.id JOIN hashlists hashlists2 ON hashlists2.id=superhashlists.hashlist WHERE hashlists.format=3 GROUP BY superhashlists.id ORDER BY id ASC");
      echo "List of superhashlists (".mysqli_num_rows($kver)."):";
      echo "<table class=\"styled\">";
      echo "<tr><td>id</td><td>Name</td><td>Hash type</td><td>Cracked</td><td>Hashlists</td><td>Pre-cracked</td><td>Action</td></tr>";
      while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
        $id=$erej["id"];
        echo "<tr><td><a href=\"$myself?a=hashlistdetail&hashlist=$id\">$id</a></td><td><a href=\"$myself?a=hashlistdetail&hashlist=$id\">".$erej["name"]."</a>";
        if ($erej["secret"]==1) echo " <img src=\"img/lock.gif\" alt=\"Secret\">";
        echo tickdone($erej["cracked"],$erej["hashcount"]);
        echo "</td><td>";
        echo ($erej["description"]=="" ? $erej["hashtype"] : $erej["description"]);
        echo "</td><td>";
        echo showperc($erej["cracked"],$erej["hashcount"])."%<br>";
        echo "(<a href=\"$myself?a=hashes&hashlist=$id&filter=cracked\">".$erej["cracked"]."</a> / <a href=\"$myself?a=hashes&hashlist=$id\">".$erej["hashcount"]."</a>)";
        echo "</td><td>";
        echo $erej["lists"];
        echo "</td>";
        echo "<td><form action=\"$myself\" method=\"GET\">";
        echo "<input type=\"hidden\" name=\"a\" value=\"hashlistzap\">";
        echo "<input type=\"hidden\" name=\"hashlist\" value=\"$id\">";
        echo "<input type=\"submit\" value=\"Import\"></form>";
        echo "<form action=\"$myself\" method=\"GET\"> ";
        echo "<input type=\"hidden\" name=\"a\" value=\"export\">";
        echo "<input type=\"hidden\" name=\"hashlist\" value=\"$id\">";
        echo "<input type=\"submit\" value=\"Export\"></form></td>";
        echo "<td><form action=\"$myself?a=hashlistdelete\" method=\"POST\" onSubmit=\"if (!confirm('Really delete superhashlist $id?\\n(Included hashes will stay intact in their hashlists)')) return false;\">";
        echo "<input type=\"hidden\" name=\"return\" value=\"a=superhashlists\">";
        echo "<input type=\"hidden\" name=\"hashlist\" value=\"$id\">";
        echo "<input type=\"submit\" value=\"Delete\"></form>";
        echo "</td></tr>";
      }
      echo "</table>";
      break;
      
    case "releases":
      // list hashcat releases
      $kver=mysqli_query_wrapper($dblink,"SELECT * FROM hashcatreleases ORDER BY time DESC");
      echo "List of Hashcat releases (".mysqli_num_rows($kver).")";
      echo "<table class=\"styled\">";
      echo "<tr><td>Version</td><td>Added</td><td>URL</td><td>Common files</td><td>Specific files</td><td>Root directory</td><td>Req. driver</td><td>Action</td></tr>";
      while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
        $ver=$erej["version"];
        echo "<tr><td>$ver</td><td>";
        if ($erej["time"]>0) {
          echo date($config["timefmt"],$erej["time"]);
        }
        echo "</td><td>NVidia:<br><a href=\"".$erej["url_nvidia"]."\" target=\"_blank\" title=\"Test URL\">".basename($erej["url_nvidia"])."</a><br>AMD:<br><a href=\"".$erej["url_amd"]."\" target=\"_blank\" title=\"Test URL\">".basename($erej["url_amd"])."</a><td>".$erej["common_files"]."</td><td>NVidia 32: ".$erej["32_nvidia"]."<br>NVidia 64: ".$erej["64_nvidia"]."<br>AMD 32: ".$erej["32_amd"]."<br>AMD 64: ".$erej["64_amd"]."</td><td>NVidia:<br>".$erej["rootdir_nvidia"]."<br>AMD:<br>".$erej["rootdir_amd"]."</td><td>NVidia:<br>".$erej["minver_nvidia"]."<br>AMD:<br>".$erej["minver_amd"];

        echo "</td><td><form action=\"$myself?a=releasedelete\" method=\"POST\" onSubmit=\"if (!confirm('Really delete Hashcat release $ver?')) return false;\">";
        echo "<input type=\"hidden\" name=\"return\" value=\"a=releases\">";
        echo "<input type=\"hidden\" name=\"release\" value=\"$ver\">";
        echo "<input type=\"submit\" value=\"Delete\"></form></td></tr>";

      }
      echo "</table>";
      break;
    
    case "files":
      // list global files
      $kver=mysqli_query_wrapper($dblink,"SELECT files.id,files.filename,files.secret,files.size,IFNULL(taskfiles.tasks,0) AS tasks FROM files LEFT JOIN (SELECT   file,COUNT(task) AS tasks FROM taskfiles GROUP BY file) taskfiles ON taskfiles.file=files.id ORDER BY filename ASC");
      echo "<script>function addLine(tablename) { var table=document.getElementById(tablename); var pos=table.getElementsByTagName('tr').length-1; var row=table.insertRow(pos); var cell=row.insertCell(0); cell.innerHTML= '<input type=\\x22file\\x22 name=\\x22upfile[]\\x22><br>'; }</script>";
      echo "<table><tr><td>";
      echo "Existing files (".mysqli_num_rows($kver)."):";
      echo "<table class=\"styled\">";
      echo "<tr><td>id</td><td>File</td><td><img src=\"img/lock.gif\" alt=\"Secret\"></td><td>Size</td><td>Used</td><td>Action</td></tr>";
      while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
        $id=$erej["id"];
        $fname=$erej["filename"];
        echo "<tr><td>$id</td><td><a name=\"$id\" href=\"files/$fname\" target=\"_blank\">$fname</a>";
        if ($erej["secret"]==1) echo " <img src=\"img/lock.gif\" alt=\"Secret\">";
        echo "</td>";

        echo "<td><form id=\"secret$id\" action=\"$myself?a=filesecret\" method=\"POST\">";
        echo "<input type=\"hidden\" name=\"file\" value=\"$id\">";
        echo "<input type=\"hidden\" name=\"return\" value=\"a=files#$id\">";
        echo "<input type=\"checkbox\" name=\"secret\" value=\"1\"";
        if ($erej["secret"]==1) echo " checked";
        echo " onChange=\"javascript:document.getElementById('secret$id').submit();\">";
        echo "</form></td>";
        
        echo "<td class=\"num\">".nicenum($erej["size"])."B</td>";
        echo "<td>".$erej["tasks"]."x</td>";
        echo "<td><form action=\"$myself?a=filedelete\" method=\"POST\" onSubmit=\"if (!confirm('Really delete file $fname?')) return false;\">";
        echo "<input type=\"hidden\" name=\"return\" value=\"a=files\">";
        echo "<input type=\"hidden\" name=\"file\" value=\"$id\">";
        echo "<input type=\"submit\" value=\"Delete\"></form></td></tr>";
      }
      echo "</table></td><td>Add new files:<br>";
      echo "<form action=\"$myself?a=filesp\" method=\"POST\" enctype=\"multipart/form-data\">";
      echo "<input type=\"hidden\" name=\"source\" value=\"upload\">";
      echo "<table class=\"styled\" id=\"upfiles\">";
      echo "<tr><td>Upload files <button type=\"button\" onclick=\"javascript:addLine('upfiles');\">Add file</button></td></tr>";
      echo "<tr><td><input type=\"submit\" value=\"Upload files\"></td></tr>";
      echo "</table>";
      echo "</form><br>";
      echo "<form id=\"impfiles\" action=\"$myself?a=filesp\" method=\"POST\" enctype=\"multipart/form-data\">";
      echo "<input type=\"hidden\" name=\"source\" value=\"import\">";
      echo "<table class=\"styled\" id=\"imfiles\">";
      echo "<tr><td>Import files</td></tr>";
      if (file_exists("import") && is_dir("import")) {
        $impdir=opendir("import");
        $impfiles=array();
        while ($f=readdir($impdir)) {
          if (($f!=".") && ($f!="..") && (!is_dir($f))) {
            $impfiles[]=$f;
          }
        } 
        if (count($impfiles)>0) {
          foreach ($impfiles as $impfile) {
            echo "<tr><td><input type=\"checkbox\" name=\"imfile[]\" value=\"$impfile\">$impfile (".nicenum(filesize("import/".$impfile))."B)</td></tr>";
          }
          echo "<tr><td><input type=\"submit\" value=\"Import files\"> <input type=\"checkbox\" onChange=\"javascript:checkAll('impfiles',this.checked);\">Select All</td></tr>";
        } else {
          echo "<tr><td>'import' directory is empty.</td></tr>";
        }
      } else {
        echo "<tr><td>'import' directory is missing,<br>nowhere to import from!</td></tr>";
      }
      echo "</td></tr>";
      echo "</table>";
      echo "</form><br>";
      echo "<form action=\"$myself?a=filesp\" method=\"POST\" enctype=\"multipart/form-data\">";
      echo "<input type=\"hidden\" name=\"source\" value=\"url\">";
      echo "<table class=\"styled\" id=\"upfiles\">";
      echo "<tr><td colspan=\"2\">Download URL</td></tr>";
      echo "<tr><td>URL:</td><td><input type=\"text\" name=\"url\" size=\"35\"></td></tr>";
      echo "<tr><td colspan=\"2\"><input type=\"submit\" value=\"Download file\"></td></tr>";
      echo "</table>";
      echo "</form>";
      echo "</td></tr></table>";
      break;
      
    case "filesp":
      // add file(s)
      $pocetup=0;
      $source=$_POST["source"];
      if (!file_exists("files")) {
        echo "First imported file, creating files subdir...";
        if (mkdir("files")) echo "OK";
      }
      
      $allok=true;
      switch ($source) {
        case "upload":
          // from http upload
          $soubory=$_FILES["upfile"];
          $pocet=count($_FILES["upfile"]["name"]);
          for($i=0;$i<$pocet;$i++) {
            // copy all uploaded attached files to proper directory
            $realname=basename($soubory["name"][$i]);
            if ($realname=="") continue;

            $nsoubor=array();
            foreach ($soubory as $klic=>$soubor) {
              $nsoubor[$klic]=$soubor[$i];
            }
            $tmpfile="files/".$realname;
            if (uploadFile($tmpfile,$source,$nsoubor)) {
              if (insertFile($tmpfile)) {
                $pocetup++;
              } else {
                $allok=false;
              }
            } else {
              $allok=false;
            }
          }
          break;
          
        case "import":
          // from import dir
          $soubory=$_POST["imfile"];
          $pocet=count($soubory);
          foreach($soubory as $soubor) {
            // copy all uploaded attached files to proper directory
            $realname=basename($soubor);
            $tmpfile="files/".$realname;
            if (uploadFile($tmpfile,$source,$realname)) {
              if (insertFile($tmpfile)) {
                $pocetup++;
              } else {
                $allok=false;
              }
            } else {
              $allok=false;
            }
          }
          break;
          
        case "url":
          // from url
          $realname=basename($_POST["url"]);
          $tmpfile="files/".$realname;
          if (uploadFile($tmpfile,$source,$_POST["url"])) {
            if (insertFile($tmpfile)) {
              $pocetup++;
            } else {
              $allok=false;
            }
          } else {
            $allok=false;
          }
          break;
      }
      if ($allok) $returnpage="a=files";
      break;
      
    case "newtask":
      // new task form
      $oname="";
      $oattack="";
      $ochunk=$config["chunktime"];
      $ostatus=$config["statustimer"];
      $oadjust=0;
      $hlist="";
      $color="";
      if (isset($_GET["task"])) {
        $orig=intval($_GET["task"]);
        if ($orig>0) {
          $ori=mysqli_query_wrapper($dblink,"SELECT name,attackcmd,chunktime,statustimer,autoadjust,hashlist,color FROM tasks WHERE id=$orig");
          if ($erej=mysqli_fetch_array($ori,MYSQLI_ASSOC)) {
            $oname=$erej["name"]." (copy)";
            $oattack=$erej["attackcmd"];
            $ochunk=$erej["chunktime"];
            $ostatus=$erej["statustimer"];
            $oadjust=$erej["autoadjust"];
            $hlist=$erej["hashlist"];
            $color=$erej["color"];
            if ($hlist=="") $hlist="preconf";
          } else {
            $orig=0;
          }
        }
      }
      echo "<table><tr><td>";
      echo "Create new task:";
      echo "<form action=\"$myself?a=newtaskp\" method=\"POST\" enctype=\"multipart/form-data\">";
      echo "<table class=\"styled\">";
      echo "<tr><td>Property</td><td>Value</td></tr>";
      echo "<tr><td>Name:</td><td><input type=\"text\" name=\"name\" value=\"$oname\"></td></tr>";
      echo "<tr><td>Hashlist:</td><td><select name=\"hashlist\">";
      $hlists=array(""=>"(please select)","preconf"=>"(pre-configured task)");
      $kver=mysqli_query_wrapper($dblink,"SELECT id,name FROM hashlists ORDER BY id ASC");
      while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
        $hlists[$erej["id"]]=$erej["name"];
      }
      foreach ($hlists as $hlid=>$hlname) {
        echo "<option value=\"$hlid\"".($hlid==$hlist ? " selected" : "").">$hlname</option>";
      }
      echo "</select> (hashlist needs to be created before task)</td></tr>";
      echo "<tr><td>Command line:</td><td><textarea name=\"cmdline\" cols=\"64\" id=\"cmdLine\">$oattack</textarea><br>";
      echo "Use <b>$hashlistAlias</b> for hash list and assume all files in current directory.<br>If you have Linux agents, please mind the filename case sensitivity!<br>Also, don't use any of these parameters, they will be invoked automatically:<br>hash-type, limit, outfile-check-dir, outfile-check-timer, potfile-disable, remove,<br>remove-timer, separator, session, skip, status, status-timer</td></tr>";
      echo "<tr><td>Chunk size:</td><td><input type=\"text\" name=\"chunk\" value=\"$ochunk\"> seconds</td></tr>";
      echo "<tr><td>Status timer:</td><td><input type=\"text\" name=\"status\" value=\"$ostatus\"> seconds</td></tr>";
      echo "<tr><td>Benchmark:</td><td><input type=\"checkbox\" name=\"autoadjust\" value=\"1\"".($oadjust==1 ? " checked" : "")."> Auto adjust<br>(Not recommended for AMD and/or in combination with small chunks sizes)</td></tr>";
      echo "<tr><td>Color:</td><td>#<input type=\"text\" name=\"color\" size=\"6\" class=\"color {required:false}\" value=\"$color\"></td></tr>";
      echo "<tr><td colspan=\"2\"><input type=\"submit\" value=\"Create task\"></td></tr>";
      echo "</table>";
      echo "</td><td>";
      echo "Attach files:";
      echo "<script>function assignFile(cmdLine,addObject,fileName) { if (fileName.indexOf('.7z') != -1) fileName=fileName.substring(0,fileName.length-2)+'???'; var cmdObject = document.getElementById(cmdLine); if (addObject == true) { if (cmdObject.value.indexOf(fileName) == -1) { if (cmdObject.value.length>0 && cmdObject.value.slice(-1)!=' ') cmdObject.value += ' '; cmdObject.value += fileName; } } else { cmdObject.value = cmdObject.value.replace(fileName,''); while (cmdObject.value.slice(-1)==' ') cmdObject.value=cmdObject.value.substring(0,cmdObject.value.length-1); while (cmdObject.value.substring(0,1)==' ') cmdObject.value=cmdObject.value.substring(1); } }</script>";
      echo "<table class=\"styled\">";
      echo "<tr><td>Filename</td><td>Size</td></tr>";
      if ($orig>0) {
        $kver=mysqli_query_wrapper($dblink,"SELECT files.*,SIGN(IFNULL(taskfiles.task,0)) AS che FROM files LEFT JOIN taskfiles ON taskfiles.file=files.id AND taskfiles.task=$orig ORDER BY filename ASC");
      } else {
        $kver=mysqli_query_wrapper($dblink,"SELECT files.*,0 AS che FROM files ORDER BY filename ASC");
      }
      while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
        $fid=$erej["id"];
        echo "<tr><td><input type=\"checkbox\" name=\"adfile[]\" value=\"$fid\" onChange=\"javscript:assignFile('cmdLine',this.checked,'".$erej["filename"]."');\"";
        if ($erej["che"]==1) echo " checked";
        echo ">".$erej["filename"];
        if ($erej["secret"]==1) echo " <img src=\"img/lock.gif\" alt=\"Secret\">";
        echo "</td><td>".nicenum($erej["size"])."B</td></tr>";
      }
      echo "</table>";
      echo "</form>";
      echo "</td></tr></table>";
      break;
      
    case "newtaskp";
      // new task creator
      $name=mysqli_real_escape_string($dblink,$_POST["name"]);
      $cmdline=mysqli_real_escape_string($dblink,$_POST["cmdline"]);
      $autoadj=intval($_POST["autoadjust"]);
      $chunk=intval($_POST["chunk"]);
      $status=intval($_POST["status"]);
      $color=$_POST["color"];
      if (preg_match("/[0-9A-Za-z]{6}/",$color)==1) {
        $color="'$color'";
      } else {
       $color="NULL"; 
      }
      if (strpos($cmdline,$hashlistAlias)===false) {
        echo "Command line must contain hashlist ($hashlistAlias).";
      } else {
        if ($_POST["hashlist"]=="preconf") {
          // it will be a preconfigured task
          $hashlist="NULL";
          if ($name=="") $name="PC_".date("Ymd_Hi");
          $treturnpage="a=ptasks";
        } else {
          $thashlist=intval($_POST["hashlist"]);
          if ($thashlist>0) $hashlist=$thashlist;
          if ($name=="") $name="HL".$hashlist."_".date("Ymd_Hi");
          $treturnpage="a=tasks";
        }
        if ($hashlist!="") {
          if ($status>0 && $chunk>0 && $chunk>$status) {
            mysqli_query_wrapper($dblink,"SET autocommit = 0");
            mysqli_query_wrapper($dblink,"START TRANSACTION");
            echo "Creating task in the DB...";
            $vysledek=mysqli_query_wrapper($dblink,"INSERT INTO tasks (name, attackcmd, hashlist, chunktime, statustimer, autoadjust, color) VALUES ('$name', '$cmdline', $hashlist, $chunk, $status, $autoadj, $color)");
            if ($vysledek) {
              // insert succeeded
              $id=mysqli_insert_id($dblink);
              echo "OK (id: $id)<br>";
              // attach files
              $attachok=true;
              if (isset($_POST["adfile"])) {
                foreach($_POST["adfile"] as $fid) {
                  if ($fid>0) {
                    echo "Attaching file $fid...";
                    if (mysqli_query_wrapper($dblink,"INSERT INTO taskfiles (task,file) VALUES ($id, $fid)")) {
                      echo "OK";
                    } else {
                      echo "ERROR!";
                      $attachok=false;
                    }
                    echo "<br>";
                  }
                }
              }
              if ($attachok==true) {
                mysqli_query_wrapper($dblink,"COMMIT");
                echo "Task created successfuly!";
                $returnpage=$treturnpage;
              } else {
                mysqli_query_wrapper($dblink,"ROLLBACK");
              }
            } else {
              echo "ERROR: ".mysqli_error($dblink);
            }
          } else {
            echo "Chunk time must be higher than status timer.";
          }
        } else {
          echo "Every task requires a hashlist, even if it should contain only one hash.";
        }
      }
      break;
      
    case "newsuperhashlist":
      // new superhashlist form
      echo "<form id=\"newsuper\" action=\"$myself?a=newsuperhashlistp\" method=\"POST\">";
      echo "Create superhashlist over these hashlists:";
      echo "<table class=\"styled\">";
      echo "<tr><td>id</td><td>Name</td><td>Hash type</td></tr>";
      $kver=mysqli_query_wrapper($dblink,"SELECT id,name,hashtype FROM hashlists WHERE format!=3 ORDER BY hashtype ASC, id ASC");
      while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
        $id=$erej["id"];
        echo "<tr><td><input type=\"checkbox\" name=\"hlist[]\" value=\"$id\">$id</td><td>".$erej["name"]."</td><td>".$erej["hashtype"]."</td></tr>";
      }
      echo "<tr><td>Name:</td><td colspan=\"2\"><input type=\"text\" name=\"name\"></td></tr>";
      echo "<tr><td colspan=\"3\"><input type=\"submit\" value=\"Create\"> <input type=\"checkbox\" onChange=\"javascript:checkAll('newsuper',this.checked);\">Select All</td></tr>";
      echo "</table>";
      echo "</form>";
      break;
      
    case "newsuperhashlistp":
      // new superhashlist creator
      $hlistar=$_POST["hlist"];
      for ($i=0;$i<count($hlistar);$i++) {
        if (intval($hlistar[$i])<=0) unset($hlistar[$i]);
      }
      $allok=false;
      if (count($hlistar)>0) {
        $hlisty=implode(",",$hlistar);
        $kv=mysqli_query_wrapper($dblink,"SELECT DISTINCT format, hashtype FROM hashlists WHERE id IN ($hlisty)");
        if (mysqli_num_rows($kv)==1) {
          mysqli_query_wrapper($dblink,"SET autocommit = 0");
          mysqli_query_wrapper($dblink,"START TRANSACTION");
          $erej=mysqli_fetch_array($kv,MYSQLI_ASSOC);
          echo "Creating superhashlist in the DB...<br>";
          $name=mysqli_real_escape_string($dblink,$_POST["name"]);
          if ($name=="") $name="SHL_".$erej["hashtype"];
          $vysledek=mysqli_query_wrapper($dblink,"INSERT INTO hashlists (name,format,hashtype,hashcount,cracked) SELECT '$name',3,".$erej["hashtype"].",SUM(hashlists.hashcount),SUM(hashlists.cracked) FROM hashlists WHERE hashlists.id IN ($hlisty)");
          if ($vysledek) {
            $id=mysqli_insert_id($dblink);
            echo "Inserting hashlists...<br>";
            if (mysqli_query_wrapper($dblink,"INSERT INTO superhashlists (id,hashlist) SELECT $id,hashlists.id FROM hashlists WHERE hashlists.id IN ($hlisty)")) {
              mysqli_query_wrapper($dblink,"COMMIT");
              $allok=true;
              $returnpage="a=superhashlists";
              echo "Done.<br>";
            } else {
              echo "Could not insert hashes to superhashlist";
            }
          } else {
            echo "Could not create superhashlist";
          }
          mysqli_query_wrapper($dblink,"SET autocommit = 1");
        } else {
          echo "Hashlists must be the same format and hash type to create a superhashlist.";
        }
      } else {
        echo "No valid hashlists provided.";
      }
      if (!$allok) {
        mysqli_query_wrapper($dblink,"ROLLBACK");
      }
      break;
    
    case "newhashlist":
      // new hashlist form
      echo "Upload new hashlist:";
      echo "<script>function formatChange(valu) { var txtObject=document.getElementById('textopt'); if (valu=='0') { txtObject.style.display = ''; } else { txtObject.style.display = 'none'; } }</script>";
      echo "<form action=\"$myself?a=newhashlistp\" method=\"POST\" enctype=\"multipart/form-data\">";
      echo "<table class=\"styled\">";
      echo "<tr><td>Property</td><td>Value</td></tr>";
      echo "<tr><td>Name:</td><td><input type=\"text\" name=\"name\" size=\"35\"></td></tr>";
      echo "<tr><td>Hashtype:</td><td><input type=\"text\" name=\"hashtype\" size=\"5\"></td></tr>";
      echo "<tr><td>Hashlist format:</td><td><select name=\"format\" onChange=\"formatChange(this.value);\"><option value=\"0\">Text file</option><option value=\"1\">HCCAP file</option><option value=\"2\">Binary file (single hash)</option></select>";
      echo "<span id=\"textopt\">";
      echo "<br><input type=\"checkbox\" name=\"salted\" value=\"1\"> Salted hashes, separator <input type=\"text\" name=\"separator\" value=\"".$config["fieldseparator"]."\" size=\"1\">";
      //echo "<br>(Accepted format is hash[:salt])";
      echo "</span>";
      echo "<tr><td>Hash source<br><i>(make sure<br>it's sorted!)</i></td><td>";
      echo "<input type=\"radio\" name=\"source\" onChange=\"sourceChange(this.value);\" value=\"paste\">Paste<br>";
      echo "<input type=\"radio\" name=\"source\" onChange=\"sourceChange(this.value);\" value=\"upload\" checked>Upload<br>";
      echo "<input type=\"radio\" name=\"source\" onChange=\"sourceChange(this.value);\" value=\"import\">Import<br>";
      echo "<input type=\"radio\" name=\"source\" onChange=\"sourceChange(this.value);\" value=\"url\">URL download";
      echo "</td></tr>";
      echo "<tr id=\"pasteLine\" style=\"display: none;\"><td>Input field:</td><td><textarea name=\"hashfield\" cols=\"60\" rows=\"10\"></textarea></td></tr>";
      echo "<tr id=\"uploadLine\"><td>File to upload:</td><td><input type=\"file\" name=\"hashfile\"></td></tr>";
      echo "<tr id=\"importLine\" style=\"display: none;\"><td>File to import:</td><td>";
      if (file_exists("import") && is_dir("import")) {
        $impdir=opendir("import");
        $impfiles=array();
        while ($f=readdir($impdir)) {
          if (($f!=".") && ($f!="..") && (!is_dir($f))) {
            $impfiles[]=$f;
          }
        }
        if (count($impfiles)>0) {
          echo "<select name=\"importfile\">";
          foreach ($impfiles as $impfile) {
            echo "<option value=\"$impfile\">$impfile</option>";
          }
          echo "</select>";
        } else {
          echo "'import' directory is empty.";
        }
      } else {
        echo "'import' directory does not exist.";
      }
      echo "</td></tr>";
      echo "<tr id=\"downloadLine\" style=\"display: none;\"><td>File URL:</td><td><input type=\"text\" name=\"url\" size=\"35\"></td></tr>";
      echo "<tr><td colspan=\"3\"><input type=\"submit\" value=\"Create hashlist\"></td></tr>";
      echo "</table>";
      echo "</form>";
      break;
      
    case "newhashlistp":
      // new hashlist creator
      $name=mysqli_real_escape_string($dblink,$_POST["name"]);
      $salted=(isset($_POST["salted"]) && intval($_POST["salted"])==1);
      $fs=mysqli_real_escape_string($dblink,$_POST["separator"]);
      $format=$_POST["format"];
      $hashtype=intval($_POST["hashtype"]);
      if ($format>=0 and $format<=2) {
        if ($name=="") {
          echo "You must specify hashlist name";
        } else {
          echo "Creating hashlist in the DB...";
          $vysledek=mysqli_query_wrapper($dblink,"INSERT INTO hashlists (name,format,hashtype) VALUES ('$name',$format,$hashtype)");
          if ($vysledek) {
            // insert succeeded
            $id=mysqli_insert_id($dblink);
            echo "OK (id: $id)<br>";
            $source=$_POST["source"];
            switch ($source) {
              case "paste":
                $sourcedata=$_POST["hashfield"];
                break;
              
              case "upload":
                $sourcedata=$_FILES["hashfile"];
                break;
              
              case "import":
                $sourcedata=$_POST["importfile"];
                break;
                
              case "url":
                $sourcedata=$_POST["url"];
                break;
            }
            $tmpfile="hashlist_$id";
            if (uploadFile($tmpfile,$source,$sourcedata)) {
              $hsize=filesize($tmpfile);
              if ($hsize>0) {
                echo "Opening file $tmpfile ($hsize B)...";
                $hhandle=fopen($tmpfile,"rb");
                echo "OK<br>";

                $pocet=0;
                $chyby=0;
                $cas_start=time();

                switch ($format) {
                  case 0:
                    echo "Determining line separator...";
                    // read buffer and get the pointer back to start
                    $buf=fread($hhandle,1024);
                    $seps=array("\r\n","\n","\r");
                    $ls="";
                    foreach ($seps as $sep) {
                      if (strpos($buf,$sep)!==false) {
                        $ls=$sep;
                        echo bintohex($ls);
                        break;
                      }
                    }
                    if ($ls=="") {
                      echo "nothing (assuming one hash)";
                    }
                    echo "<br>";

                    if ($salted) {
                      // find out if the first line contains field separator
                      echo "Searching for field separator inside the first line...";
                      rewind($hhandle);
                      $bufline=stream_get_line($hhandle,1024,$ls);
                      if (strpos($bufline,$fs)===false) {
                        echo "NOTHING - assuming unsalted hashes";
                        $fs="";
                      } else {
                        echo "OK - assuming salted hashes";
                      }
                      echo "<br>";
                    } else {
                      $fs="";
                    }
                    // now read the lines
                    echo "Importing hashes from text file...<br>";
                    rewind($hhandle);
                    
                    $tmpfull=mysqli_real_escape_string($dblink,dirname($_SERVER["SCRIPT_FILENAME"])."/chunk_$id");
                    
                    // how many hashes to import at once:
                    $loopsize=100000;
                    
                    while(!feof($hhandle)) {
                      $tmpchunk=fopen("chunk_$id","w");
                      $chunklines=0;
                      while (!feof($hhandle) && $chunklines<$loopsize) {
                        $dato=stream_get_line($hhandle, 1024, $ls);
                        if ($dato=="") continue;
                        fwrite($tmpchunk,$dato.$ls);
                        $chunklines++;
                      }
                      fclose($tmpchunk);
                      echo "Loading $chunklines lines...";
                      $cas_xstart=time();
                      // try fast load data
                      $kv="LOAD DATA INFILE '$tmpfull' IGNORE INTO TABLE hashes ".($fs=="" ? "" : "FIELDS TERMINATED BY '$fs' ")."LINES TERMINATED BY '".mysqli_real_escape_string($dblink,$ls)."' (hash, salt) SET hashlist=$id";
                      $kvr=mysqli_query_wrapper($dblink,$kv);
                      if ($kvr) {
                        echo "OK";
                        $pocet+=mysqli_affected_rows($dblink);
                      } else {
                        // load data failed, could be bad privileges or mysql on different server than www
                        echo "fail, inserting...";
                        $slow=fopen("chunk_$id","r");
                        mysqli_query_wrapper($dblink,"START TRANSACTION");
                        while (!feof($slow)) {
                          $dato=stream_get_line($slow, 1024, $ls);
                          if ($fs=="") {
                            $hash=$dato;
                            $salt="";
                          } else {
                            $poz=strpos($dato,$fs);
                            if ($poz!==false) {
                              $hash=substr($dato,0,$poz);
                              $salt=substr($dato,$poz+1);
                            } else {
                              $hash=$dato;
                              $salt="";
                            }
                          }
                          $hash=mysqli_real_escape_string($dblink,$hash);
                          $salt=mysqli_real_escape_string($dblink,$salt);
                          $kvr=mysqli_query($dblink,"INSERT IGNORE INTO hashes (hashlist,hash,salt) VALUES ($id,'$hash','$salt')");
                          if ($kvr) {
                            $pocet+=mysqli_affected_rows($dblink);
                          }
                        }
                        fclose($slow);
                        mysqli_query_wrapper($dblink,"COMMIT");
                      }
                      echo " (took ".(time()-$cas_xstart)."s, total $pocet)<br>";
                      flush();
                    }
                    unlink("chunk_$id");
                    break;
                    
                  case 1:
                    echo "Importing wireless networks...<br>";
                    while (!feof($hhandle)) {
                      $dato=fread($hhandle, 392);
                      if (strlen($dato)==392) {
                        $nazev="";
                        for ($i=0;$i<36;$i++) {
                          $znak=$dato[$i];
                          if ($znak!="\x00") {
                            $nazev.=$znak;
                          } else {
                            break;
                          }
                        }
                        echo "Found network $nazev";
                        if (mysqli_query_wrapper($dblink,"INSERT INTO hashes_binary (hashlist, essid, hash) VALUES ($id, '$nazev',x'".bintohex($dato)."')")) {
                          $pocet+=mysqli_affected_rows($dblink);
                        } else {
                          $chyby++;
                        }
                      } else {
                        if (strlen($dato)>0) echo "Found garbage (only ".strlen($dato)." bytes)";
                      }
                      echo "<br>";
                    }
                    break;
                    
                  case 2:
                    if (!feof($hhandle)) {
                      $dato=fread($hhandle,$hsize);
                      echo "Inserting binary file as one hash...<br>";
                      if (mysqli_query_wrapper($dblink,"INSERT INTO hashes_binary (hashlist, hash) VALUES ($id, x'".bintohex($dato)."')")) {
                        echo "OK";
                        $pocet=mysqli_affected_rows($dblink);
                      } else {
                        echo "ERROR";
                        $chyby++;
                      }
                      echo "<br>";
                    }
                    break;
                }
                fclose($hhandle);
                echo "<br>";
                $cas_stop=time();

                // evaluate, what have we accomplished
                if ($pocet>0) {
                  mysqli_query_wrapper($dblink,"UPDATE hashlists SET hashcount=$pocet WHERE id=$id");
                  echo "Insert completed ($pocet hashes inserted, $chyby errors, took ".($cas_stop-$cas_start)." sec)";
                } else {
                  echo "ERROR";
                  mysqli_query_wrapper($dblink,"DELETE FROM hashlists WHERE id=$id");
                  echo "Nothing was inserted ($chyby errors). Perhaps empty hashlist or database problem?";
                }
              } else {
                echo "Hashlist file is empty!";
              }
              unlink($tmpfile);
            }
          } else {
            echo "ERROR: ".mysqli_error($dblink);
          }
          echo "<br>";
        }
      } else {
        echo "Select correct hashlist format";
      }
      break;

    case "hashlistzap":
      // pre-crack hashes form
      $hlist=intval($_GET["hashlist"]);
      $kv=mysqli_query_wrapper($dblink,"SELECT hashlists.*,IFNULL(hashes.salted,0) AS salted FROM hashlists LEFT JOIN (SELECT hashlist,1 AS salted FROM hashes WHERE hashlist=$hlist AND salt!='' LIMIT 1) hashes ON hashlists.format=0 AND hashes.hashlist=hashlists.id WHERE hashlists.id=$hlist");
      if (mysqli_num_rows($kv)==1) {
        $erej=mysqli_fetch_array($kv,MYSQLI_ASSOC);
        $format=$erej["format"];
        $salted=$erej["salted"];
        echo "Import pre-cracked hashes:";
        echo "<form action=\"$myself?a=hashlistzapp\" method=\"POST\" enctype=\"multipart/form-data\">";
        echo "<input type=\"hidden\" name=\"hashlist\" value=\"$hlist\">";
        echo "<table class=\"styled\">";
        echo "<tr><td>Property</td><td>Value</td></tr>";
        echo "<tr><td>Name:</td><td>".$erej["name"]."</td></tr>";
        echo "<tr><td>Hashtype:</td><td>".$erej["hashtype"]."</td></tr>";
        echo "<tr><td>Salted:</td><td>".($salted==1 ? "Yes" : "No")."</td></tr>";
        echo "<tr><td>Hashlist format:</td><td>".$formats[$erej["format"]];
        echo "<br>Field separator: <input type=\"text\" name=\"separator\" value=\"".$config["fieldseparator"]."\" size=\"1\"><br>(Accepted format is ";
        switch ($erej["format"]) {
          case 0:
            echo "hash[:salt]:plaintext";
            break;
            
          case 1:
            echo "essid:plaintext";
            break;
            
          case 2:
            echo "just plaintext";
            break;
        }
        echo ")<tr><td>Hash source</td><td>";
        echo "<input type=\"radio\" name=\"source\" onChange=\"sourceChange(this.value);\" value=\"paste\">Paste<br>";
        echo "<input type=\"radio\" name=\"source\" onChange=\"sourceChange(this.value);\" value=\"upload\" checked>Upload<br>";
        echo "<input type=\"radio\" name=\"source\" onChange=\"sourceChange(this.value);\" value=\"import\">Import<br>";
        echo "<input type=\"radio\" name=\"source\" onChange=\"sourceChange(this.value);\" value=\"url\">URL download";
        echo "</td></tr>";
        echo "<tr id=\"pasteLine\" style=\"display: none;\"><td>Input field:</td><td><textarea name=\"hashfield\" cols=\"60\" rows=\"10\"></textarea></td></tr>";
        echo "<tr id=\"uploadLine\"><td>File to upload:</td><td><input type=\"file\" name=\"hashfile\"></td></tr>";
        echo "<tr id=\"importLine\" style=\"display: none;\"><td>File to import:</td><td>";
        if (file_exists("import") && is_dir("import")) {
          $impdir=opendir("import");
          $impfiles=array();
          while ($f=readdir($impdir)) {
            if (($f!=".") && ($f!="..") && (!is_dir($f))) {
              $impfiles[]=$f;
            }
          }
          if (count($impfiles)>0) {
            echo "<select name=\"importfile\">";
            foreach ($impfiles as $impfile) {
              echo "<option value=\"$impfile\">$impfile</option>";
            }
            echo "</select>";
          } else {
            echo "'import' directory is empty.";
          }
        } else {
          echo "'import' directory does not exist.";
        }
        echo "</td></tr>";
        echo "<tr id=\"downloadLine\" style=\"display: none;\"><td>File URL:</td><td><input type=\"text\" name=\"url\" size=\"35\"></td></tr>";
        echo "<tr><td>Conflict resolution:</td><td><input type=\"checkbox\" name=\"overwrite\" value=\"1\">Overwrite already cracked hashes</td></tr>";
        echo "<tr><td colspan=\"3\"><input type=\"submit\" value=\"Pre-crack hashes\"></td></tr>";
        echo "</table>";
        echo "</form>";
      }
      break;
      
    case "hashlistzapp":
      // pre-crack hashes processor
      $hlist=intval($_POST["hashlist"]);
      $kv=mysqli_query_wrapper($dblink,"SELECT hashlists.*,IFNULL(hashes.salted,0) AS salted FROM hashlists LEFT JOIN (SELECT hashlist,1 AS salted FROM hashes WHERE hashlist=$hlist AND salt!='' LIMIT 1) hashes ON hashlists.format=0 AND hashes.hashlist=hashlists.id WHERE hashlists.id=$hlist");
      if (mysqli_num_rows($kv)==1) {
        $erej=mysqli_fetch_array($kv,MYSQLI_ASSOC);
        $format=$erej["format"];
        $salted=$erej["salted"];

        $fs=mysqli_real_escape_string($dblink,$_POST["separator"]);
        $source=$_POST["source"];
        // switch based on source
        switch ($source) {
          case "paste":
            $sourcedata=$_POST["hashfield"];
            break;
          
          case "upload":
            $sourcedata=$_FILES["hashfile"];
            break;
          
          case "import":
            $sourcedata=$_POST["importfile"];
            break;
            
          case "url":
            $sourcedata=$_POST["url"];
            break;
        }
        $tmpfile="zaplist_$hlist";
        if (uploadFile($tmpfile,$source,$sourcedata)) {
          $hsize=filesize($tmpfile);
          if ($hsize>0) {
            echo "Opening file $tmpfile ($hsize B)...";
            $hhandle=fopen($tmpfile,"rb");
            echo "OK<br>";

            $pocet=0;
            $chyby=0;
            $cas_start=time();

            echo "Determining line separator...";
            // read buffer and get the pointer back to start
            $buf=fread($hhandle,1024);
            $seps=array("\r\n","\n","\r");
            $ls="";
            foreach ($seps as $sep) {
              if (strpos($buf,$sep)!==false) {
                $ls=$sep;
                echo bintohex($ls);
                break;
              }
            }
            if ($ls=="") {
              echo "not found - assuming single hash";
            }
            echo "<br>";

            // create proper superhashlist field if needed
            list($superhash,$hlisty)=superList($hlist,$format);

            // now read the lines
            echo "Importing pre-cracked hashes from text file...<br>";
            rewind($hhandle);
            $zapy=0; $chyby=0; $skipy=0; $total=0;

            // create temporary hell to handle all that crack/crap
            mysqli_query_wrapper($dblink,"CREATE TEMPORARY TABLE tmphlcracks (hashlist INT NOT NULL, zaps BIT(1) DEFAULT 0, PRIMARY KEY (hashlist))");
            mysqli_query_wrapper($dblink,"INSERT INTO tmphlcracks (hashlist) SELECT id FROM hashlists WHERE id IN ($hlisty)");


            mysqli_query_wrapper($dblink,"START TRANSACTION");
            $zaptable=$formattables[$format];
            while(!feof($hhandle)) {
              $dato=stream_get_line($hhandle, 1024, $ls);
              if ($dato=="") continue;
              $total++;
              $kv="UPDATE $zaptable JOIN hashlists ON $zaptable.hashlist=hashlists.id JOIN tmphlcracks ON tmphlcracks.hashlist=$zaptable.hashlist SET tmphlcracks.zaps=1,$zaptable.chunk=0,$zaptable.plaintext='";
              $datko=explode($fs,$dato);
              $zaphash=""; $zapsalt=""; $zapplain="";
              // distribute data into vars
              if ($salted==1) {
                if (count($datko)>=3) {
                  $zaphash=$datko[0];
                  $zapsalt=$datko[1];
                  $zapplain=$datko[2];
                  for ($i=3;$i<count($datko);$i++) {
                    $zapplain.=$fs.$datko[$i];
                  }
                } else {
                  echo "Bad line: $dato<br>";
                  $chyby++;
                  continue;
                }
              } else {
                if (count($datko)>=2) {
                  $zaphash=$datko[0];
                  $zapplain=$datko[1];
                  for ($i=2;$i<count($datko);$i++) {
                    $zapplain.=$fs.$datko[$i];
                  }
                } else {
                  echo "Bad line: $dato<br>";
                  $chyby++;
                  continue;
                }
              }
              //overwritting condition
              if (isset($_POST["overwrite"]) && $_POST["overwrite"]=="1") {
                $over=true;
              } else {
                $over=false;
              }
              $kv2="',$zaptable.time=$cas,hashlists.cracked=hashlists.cracked+".($over ? "IF($zaptable.plaintext IS NULL,1,0)" : "1")." WHERE $zaptable.hashlist IN ($hlisty)".($over ? "" : " AND $zaptable.plaintext IS NULL");
              switch ($format) {
                case 0:
                  $kv2.=" AND $zaptable.hash='".mysqli_real_escape_string($dblink,$zaphash)."'";
                  if ($zapsalt!="") $kv2.=" AND $zaptable.salt='".mysqli_real_escape_string($dblink,$zapsalt)."'";
                  break;
                  
                case 1:
                  $kv2.=" AND $zaptable.essid='".mysqli_real_escape_string($dblink,$zaphash)."'";
                  break;
              }
              if ($zapplain!="") {
                $vysledek=mysqli_query_wrapper($dblink,$kv.mysqli_real_escape_string($dblink,$zapplain).$kv2,true);
                if (!$vysledek) {
                  $vysledek=mysqli_query_wrapper($dblink,$kv."\$HEX[".bintohex($zapplain)."]".$kv2,true);
                }
                if ($vysledek) {
                  $aff=mysqli_affected_rows($dblink);
                  if ($aff==0) {
                    $skipy++;
                  } else {
                    $zapy++;
                  }
                } else {
                  echo "Problems pre-cracking hash ".$zaphash." ($kv--$kv2)<br>";
                  $chyby++;
                }
              } else {
                $skipy++;
              }
              if ($total % 10000 == 0) {
                echo "Read $total lines...<br>";
                flush();
              }
              
            }
            mysqli_query_wrapper($dblink,"COMMIT");
            $cas_stop=time();

            mysqli_query_wrapper($dblink,"INSERT IGNORE INTO zapqueue (hashlist,agent,time,chunk) SELECT hashlistusers.hashlist,hashlistusers.agent,$cas,0 FROM hashlistusers JOIN tmphlcracks ON hashlistusers.hashlist=tmphlcracks.hashlist AND tmphlcracks.zaps=1");
            mysqli_query_wrapper($dblink,"DROP TABLE tmphlcracks");

            // evaluate, what have we accomplished
            if ($superhash) {
              // recount cracked
              mysqli_query_wrapper($dblink,"SET @ctotal=(SELECT SUM(hashlists.cracked) FROM superhashlists JOIN hashlists ON superhashlists.hashlist=hashlists.id WHERE superhashlists.id=$hlist)");
              mysqli_query_wrapper($dblink,"UPDATE hashlists SET cracked=@ctotal WHERE id=$hlist AND format=3");
            }
            echo "Pre-cracking completed ($zapy hashes pre-cracked, $skipy skipped for duplicity or empty plaintext, $chyby SQL errors, took ".($cas_stop-$cas_start)." sec)";
            fclose($hhandle);
          } else {
            echo "Pre-cracked file is empty!";
          }
          unlink($tmpfile);
        }
      }
      break;

    case "newrelease":
      // new hashcat release form
      echo "Create new Hashcat release:";
      echo "<form action=\"$myself?a=newreleasep\" method=\"POST\" enctype=\"multipart/form-data\">";
      echo "<table class=\"styled\">";
      $kver=mysqli_query_wrapper($dblink,"SELECT * FROM hashcatreleases ORDER BY time DESC LIMIT 1");
      $erej=mysqli_fetch_array($kver,MYSQLI_ASSOC);

      echo "<tr><td>Property</td><td>NVidia</td><td>AMD</td></tr>";
      echo "<tr><td>Version:</td><td colspan=\"2\"><input type=\"text\" name=\"version\"></td></tr>";
      echo "<tr><td>Archive URL:</td><td><textarea name=\"url_nvidia\" cols=\"32\">{$erej["url_nvidia"]}</textarea></td><td><textarea name=\"url_amd\" cols=\"32\">{$erej["url_amd"]}</textarea></td></tr>";
      echo "<tr><td>Archive root directory:</td><td><input type=\"text\" name=\"rootdir_nvidia\" size=\"32\" value=\"{$erej["rootdir_nvidia"]}\"></td><td><input type=\"text\" name=\"rootdir_amd\" size=\"32\" value=\"{$erej["rootdir_amd"]}\"></td></tr>";
      echo "<tr><td colspan=\"3\">Files to extract (relative to root directory, omit executables and use forward slashes):</td></tr>";
      echo "<tr><td>Common:</td><td colspan=\"2\"><textarea name=\"common_files\" cols=\"64\">{$erej["common_files"]}</textarea></td></tr>";
      echo "<tr><td>32bit:</td><td><textarea name=\"32_nvidia\" cols=\"32\">{$erej["32_nvidia"]}</textarea></td><td><textarea name=\"32_amd\" cols=\"32\">{$erej["32_amd"]}</textarea></td></tr>";
      echo "<tr><td>64bit:</td><td><textarea name=\"64_nvidia\" cols=\"32\">{$erej["64_nvidia"]}</textarea></td><td><textarea name=\"64_amd\" cols=\"32\">{$erej["64_amd"]}</textarea></td></tr>";
      echo "<tr><td colspan=\"3\">Minimum driver versions</td></tr>";
      echo "<tr><td>NVidia:</td><td><input type=\"text\" name=\"minver_nvidia\" value=\"{$erej["minver_nvidia"]}\"></td><td><input type=\"text\" name=\"minver_amd\" value=\"{$erej["minver_amd"]}\"></td></tr>";
      echo "<tr><td colspan=\"3\"><input type=\"submit\" value=\"Create release\"></td></tr>";
      echo "</table>";
      echo "</form>";
      break;
      
    case "newreleasep":
      // new hashcat release creator
      $version=mysqli_real_escape_string($dblink,$_POST["version"]);
      $url["1"]=mysqli_real_escape_string($dblink,$_POST["url_nvidia"]);
      $url["2"]=mysqli_real_escape_string($dblink,$_POST["url_amd"]);
      $common_files=mysqli_real_escape_string($dblink,$_POST["common_files"]);
      $files["1"]["32"]=mysqli_real_escape_string($dblink,$_POST["32_nvidia"]);
      $files["1"]["64"]=mysqli_real_escape_string($dblink,$_POST["64_nvidia"]);
      $files["2"]["32"]=mysqli_real_escape_string($dblink,$_POST["32_amd"]);
      $files["2"]["64"]=mysqli_real_escape_string($dblink,$_POST["64_amd"]);
      $minver["1"]=floatval($_POST["minver_nvidia"]);
      $minver["2"]=floatval($_POST["minver_amd"]);
      $rootdir["1"]=mysqli_real_escape_string($dblink,$_POST["rootdir_nvidia"]);
      $rootdir["2"]=mysqli_real_escape_string($dblink,$_POST["rootdir_amd"]);
      if ($version=="") {
        echo "You must specify the version";
      } else {
        echo "Creating release in the DB...";
        $vysledek=mysqli_query_wrapper($dblink,"INSERT INTO hashcatreleases (version,time,url_nvidia,url_amd,common_files,32_nvidia,64_nvidia,32_amd,64_amd,rootdir_nvidia,rootdir_amd,minver_nvidia,minver_amd) VALUES ('$version',$cas,'".$url["1"]."','".$url["2"]."','$common_files','".$files["1"]["32"]."','".$files["1"]["64"]."','".$files["2"]["32"]."','".$files["2"]["64"]."','".$rootdir["1"]."','".$rootdir["2"]."',".$minver["1"].",".$minver["2"].")");
        if ($vysledek) {
          // insert succeeded
          echo "OK";
          $returnpage="a=releases";
        } else {
          echo "ERROR: ".mysqli_error($dblink);
        }
        echo "<br>";
      }
      break;

    case "hashlistdetail":
      // show hashlist detail
      echo "<table><tr><td>";
      $hlist=intval($_GET["hashlist"]);
      $kver=mysqli_query_wrapper($dblink,"SELECT hashlists.*,hashtypes.description FROM hashlists LEFT JOIN hashtypes ON hashtypes.id=hashlists.hashtype WHERE hashlists.id=$hlist");
      if (mysqli_num_rows($kver)==1) {
        $erej=mysqli_fetch_array($kver,MYSQLI_ASSOC);
        $id=$erej["id"];
        $format=$erej["format"];
        $total=$erej["hashcount"];
        $cracked=$erej["cracked"];
        echo "Hashlist details:<table class=\"styled\">";
        echo "<tr><td>Property</td><td>Value</td></tr>";
        echo "<tr><td>ID:</td><td>$id</td></tr>";
        echo "<tr><td>Name:</td><td>";
        echo "<form action=\"$myself?a=hashlistrename\" method=\"POST\">";
        echo "<input type=\"hidden\" name=\"return\" value=\"a=hashlistdetail&hashlist=$id\">";
        echo "<input type=\"hidden\" name=\"hashlist\" value=\"$id\">";
        echo "<input type=\"text\" name=\"name\" size=\"20\" value=\"".$erej["name"]."\">";
        echo "<input type=\"submit\" value=\"Change\"></form>";
        echo "</td></tr>";
        echo "<tr><td>Hash type:</td><td>".($erej["description"]=="" ? $erej["hashtype"] : $erej["description"])."</td></tr>";
        echo "<tr><td>Format:</td><td>".$formats[$format]."</td></tr>";
        echo "<tr><td>Hashes:</td><td><a href=\"$myself?a=hashes&hashlist=$id\">$total</a></td></tr>";
        echo "<tr><td>Cracked:</td><td><a href=\"$myself?a=hashes&hashlist=$id&filter=cracked\">$cracked</a> (".showperc($cracked,$total)."%)</td></tr>";
        echo "<tr><td>Remaining:</td><td><a href=\"$myself?a=hashes&hashlist=$id&filter=uncracked\">".($total-$cracked)."</a> (".showperc(($total-$cracked),$total)."%)</td></tr>";

        echo "<tr><td>Secret:</td><td>";
        echo "<form id=\"hashlistsecret\" action=\"$myself?a=hashlistsecret\" method=\"POST\">";
        echo "<input type=\"hidden\" name=\"hashlist\" value=\"$id\">";
        echo "<input type=\"hidden\" name=\"return\" value=\"a=hashlistdetail&hashlist=$id\">";
        echo "<input type=\"checkbox\" name=\"secret\" value=\"1\"";
        if ($erej["secret"]==1) echo " checked";
        echo " onChange=\"javascript:document.getElementById('hashlistsecret').submit();\"> Hashlist contains secret data";
        echo "</form>";
        echo "</td></tr>";

        echo "<tr><td>Actions:</td><td>";
        if ($cracked>0) {
          echo "<a href=\"$myself?a=wordlist&hashlist=$id\">Generate wordlist</a><br>";
          echo "<a href=\"$myself?a=export&hashlist=$id\">Export hashes for pre-crack</a><br>";
        }
        if ($cracked<$total) echo "<a href=\"$myself?a=hashlistzap&hashlist=$id\">Import pre-cracked hashes</a>";
        echo "</td></tr>";
        echo "</table>";
        
        if ($format==3) {
          $kver=mysqli_query_wrapper($dblink,"SELECT hashlists.* FROM superhashlists JOIN hashlists ON superhashlists.hashlist=hashlists.id WHERE superhashlists.id=$id");
          if (mysqli_num_rows($kver)>0) {
            echo "<br>Hashlists included in this superhashlist:";
            echo "<table class=\"styled\">";
            echo "<tr><td>id</td><td>Name</td><td>Cracked</td></tr>";
            while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
              $id=$erej["id"];
              echo "<tr><td><a href=\"$myself?a=hashlistdetail&hashlist=$id\">$id</a></td><td><a href=\"$myself?a=hashlistdetail&hashlist=$id\">".$erej["name"]."</a>";
              if ($erej["secret"]==1) echo " <img src=\"img/lock.gif\" alt=\"Secret\">";
              echo tickdone($erej["cracked"],$erej["hashcount"]);
              echo "</td><td class=\"num\">";
              echo showperc($erej["cracked"],$erej["hashcount"])."%<br>";
              echo "(<a href=\"$myself?a=hashes&hashlist=$id&filter=cracked\">".$erej["cracked"]."</a> / <a href=\"$myself?a=hashes&hashlist=$id\">".$erej["hashcount"]."</a>)";
              echo "</td></tr>";
            }
            echo "</table>";
          }
        }

        // assigned tasks
        $kver=mysqli_query_wrapper($dblink,"SELECT tasks.id,tasks.name,tasks.attackcmd,tasks.progress,chunks.sumprog,tasks.keyspace,IFNULL(chunks.cracked,0) AS cracked,IF(chunks.lastact>".($cas-$config["chunktimeout"]).",1,0) AS active FROM tasks LEFT JOIN (SELECT task,SUM(cracked) AS cracked,SUM(progress) AS sumprog,GREATEST(MAX(dispatchtime),MAX(solvetime)) AS lastact FROM chunks GROUP BY task) chunks ON chunks.task=tasks.id WHERE tasks.hashlist=$hlist ORDER by tasks.priority DESC,tasks.id ASC");
        if (mysqli_num_rows($kver)>0) {
          echo "<br>Tasks cracking this hashlist:";
          echo "<table class=\"styled\">";
          echo "<tr><td>id</td><td>Name</td><td>Dispatched</td><td>Searched</td><td>Cracked</td></tr>";
          while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
            $id=$erej["id"];
            echo "<tr><td><a href=\"$myself?a=taskdetail&task=$id\">$id</a></td><td><a href=\"$myself?a=taskdetail&task=$id\" title=\"".$erej["attackcmd"]."\">";
            echo $erej["name"];
            echo "</a>";
            echo tickdone($erej["progress"],$erej["keyspace"]);
            if ($erej["active"]==1 && $erej["sumprog"]<$erej["keyspace"]) echo " <img src=\"img/active.gif\" alt=\"Active\">";
            echo "</td><td class=\"num\">";
            echo showperc($erej["progress"],$erej["keyspace"]);
            echo "%</td><td class=\"num\">";
            echo showperc($erej["sumprog"],$erej["keyspace"]);
            echo "%</td><td class=\"num\">";
            if ($erej["cracked"]>0) echo "<a href=\"$myself?a=hashes&task=$id\">".$erej["cracked"]."</a>";
            echo "</td></tr>";
          }
          echo "</table>";
        }

        // preconf tasks
        $kver=mysqli_query_wrapper($dblink,"SELECT id,name,attackcmd,color FROM tasks WHERE hashlist IS NULL ORDER BY priority DESC, id ASC");
        if (mysqli_num_rows($kver)>0) {
          echo "</td><td>";
          echo "Create pre-configured tasks:";
          echo "<form id=\"preconf\" action=\"$myself?a=preconf\" method=\"POST\">";
          echo "<input type=\"hidden\" name=\"hashlist\" value=\"$hlist\">";
          echo "<table class=\"styled\">";
          echo "<tr><td>id</td><td>Name</td></tr>";
          while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
            $fid=$erej["id"];
            echo "<tr><td";
            if (strlen($erej["color"])>0) {
              echo " style=\"background-color: #".$erej["color"]."\"";
            }
            echo "><a href=\"$myself?a=taskdetail&task=$fid\">$fid</a></td><td><input type=\"checkbox\" name=\"task[]\" value=\"$fid\"><a href=\"$myself?a=taskdetail&task=$fid\" title=\"".$erej["attackcmd"]."\">".$erej["name"]."</a></td></tr>";
          }
          echo "<tr><td colspan=\"2\"><input type=\"submit\" value=\"Create\"> <input type=\"checkbox\" onChange=\"javascript:checkAll('preconf',this.checked);\">Select All</td></tr>";
          echo "</table>";
          echo "</form>";
        }
        echo "</td></tr></table>";
      } else {
        echo "No such hashlist";
      }
      break;
    
    case "wordlist":
      // create wordlist from hashlist cracked hashes
      $hlist=intval($_GET["hashlist"]);
      $kver=mysqli_query_wrapper($dblink,"SELECT format FROM hashlists WHERE id=$hlist");
      if ($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
        $format=$erej["format"];

        // create proper superhashlist field if needed
        list($superhash,$hlisty)=superList($hlist,$format);

        $kvery="SELECT plaintext FROM ".$formattables[$format]." WHERE hashlist IN ($hlisty) AND plaintext IS NOT NULL";
        $kv=mysqli_query_wrapper($dblink,$kvery);
        if (mysqli_num_rows($kv)>0) {
          $wlist="Wordlist_".$hlist."_".date("Y-m-d_H-i-s",$cas).".txt";
          echo "Opening wordlist for writing...<br>";
          $fx=fopen("files/".$wlist,"w");
          $p=0;
          while ($erej=mysqli_fetch_array($kv,MYSQLI_ASSOC)) {
            $plain=$erej["plaintext"];
            if (strlen($plain)>=8 && substr($plain,0,5)=="\$HEX[" && substr($plain,strlen($plain)-1,1)=="]") {
              // strip $HEX[]
              $nplain="";
              $plain=hextobin(substr($plain,5,strlen($plain)-6));
            }
            fwrite($fx,$plain."\n");
            $p++;
          }
          fclose($fx);
          echo "Written $p words.<br>";
          insertFile("files/".$wlist);
        } else {
          echo "Nothing cracked.";
        }
      }
      break;
    
    case "export":
      // export cracked hashes to a file
      $hlist=intval($_GET["hashlist"]);
      $kver=mysqli_query_wrapper($dblink,"SELECT format FROM hashlists WHERE id=$hlist");
      if ($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
        $format=$erej["format"];

        // create proper superhashlist field if needed
        list($superhash,$hlisty)=superList($hlist,$format);
        
        $tmpfile="Pre-cracked_".$hlist."_".date("Y-m-d_H-i-s",$cas).".txt";
        $tmpfull=mysqli_real_escape_string($dblink,dirname($_SERVER["SCRIPT_FILENAME"])."/files/".$tmpfile);
        $salted=false;
        $kvery1="SELECT ";
        switch ($format) {
          case 0:
            $kver=mysqli_query_wrapper($dblink,"SELECT 1 FROM hashes WHERE hashlist IN ($hlisty) AND salt!='' LIMIT 1");
            if (mysqli_num_rows($kver)>0) {
              $kvery1.="hash,salt,plaintext";
              $salted=true;
            } else {
              $kvery1.="hash,plaintext";
            }
            break;
            
          case 1:
            $kvery1.="essid AS hash,plaintext";
            break;

          case 2:
            $kvery1.="plaintext";
            break;
        }
        $kvery2=" INTO OUTFILE '$tmpfull' FIELDS TERMINATED BY '".mysqli_real_escape_string($dblink,$config["fieldseparator"])."' ESCAPED BY '' LINES TERMINATED BY '\\n'";
        $kvery3=" FROM ".$formattables[$format]." WHERE hashlist IN ($hlisty) AND plaintext IS NOT NULL";
        if (!file_exists("files")) mkdir("files");
        $kvery=$kvery1.$kvery2.$kvery3;
        $kv=mysqli_query_wrapper($dblink,$kvery);
        
        if (!$kv) {
          echo "File export failed, trying SELECT with file output<br>";
          $kvery=$kvery1.$kvery3;
          $kv=mysqli_query_wrapper($dblink,$kvery);
          $fexp=fopen("files/".$tmpfile,"w");
          while($erej=mysqli_fetch_array($kv,MYSQLI_ASSOC)) {
            fwrite($fexp,$erej["hash"].($salted ? $config["fieldseparator"].$erej["salt"] : "").$config["fieldseparator"].$erej["plaintext"]."\n");
          }
          fclose($fexp);
        }
        
        if ($kv) {
          if (insertFile("files/".$tmpfile)) {
            echo "Cracked hashes from hashlist $hlist exported.";
          } else {
            echo "Cracked hashes exported, but the file is missing.";
          }
        } else {
          echo "Could not export hashlist $hlist";
        }
      } else {
        echo "No such hashlist.";
      }
      break;
    
    case "hashes":
      // show hashes based on provided criteria
      $hlist=intval($_GET["hashlist"]);
      $chunk=intval($_GET["chunk"]);
      $task=intval($_GET["task"]);
      echo "<form action=\"$myself\" method=\"GET\">";
      echo "<input type=\"hidden\" name=\"a\" value=\"hashes\">";
      if ($chunk>0) {
        $fmt=mysqli_query_wrapper($dblink,"SELECT hashlists.id,hashlists.format FROM chunks JOIN tasks ON chunks.task=tasks.id JOIN hashlists ON hashlists.id=tasks.hashlist WHERE chunks.id=$chunk");
        $er=mysqli_fetch_array($fmt,MYSQLI_ASSOC);
        $hlist=$er["id"];
        $format=$er["format"];
        $src="chunk";
      } else {
        if ($task>0) {
          $fmt=mysqli_query_wrapper($dblink,"SELECT hashlists.id,tasks.name,hashlists.format FROM tasks JOIN hashlists ON hashlists.id=tasks.hashlist WHERE tasks.id=$task");
          $er=mysqli_fetch_array($fmt,MYSQLI_ASSOC);
          $hlist=$er["id"];
          $format=$er["format"];
          $src="task";
        } else {
          if ($hlist>0) {
            $fmt=mysqli_query_wrapper($dblink,"SELECT name,format FROM hashlists WHERE id=$hlist");
            $er=mysqli_fetch_array($fmt,MYSQLI_ASSOC);
            $format=$er["format"];
            $src="hashlist";
          }
        }
      }

      // create proper superhashlist field if needed
      list($superhash,$hlisty)=superList($hlist,$format);

      switch ($src) {
        case "chunk":
          echo "<input type=\"hidden\" name=\"chunk\" value=\"$chunk\">";
          echo "Hashes of chunk $chunk, filter: ";
          $viewfilter="WHERE chunk=$chunk";
          break;
          
        case "task":
          echo "<input type=\"hidden\" name=\"task\" value=\"$task\">";
          echo "Hashes of task <a href=\"$myself?a=taskdetail&task=$task\">".$er["name"]."</a>, filter: ";
          $viewfilter="JOIN chunks ON chunk=chunks.id WHERE ".$formattables[$format].".chunk IS NOT NULL AND chunks.task=$task";
          break;
          
        case "hashlist":
          echo "<input type=\"hidden\" name=\"hashlist\" value=\"$hlist\">";
          echo "Hashes of hashlist <a href=\"$myself?a=hashlistdetail&hashlist=$hlist\">".$er["name"]."</a>, filter: ";
          $viewfilter="WHERE hashlist IN ($hlisty)";
          break;
      }
      $what=$_GET["display"];
      $displays=array("hash"=>"Hashes only",""=>"Hashes + plaintexts","plain"=>"Plaintexts only");
      $filters=array("cracked"=>"Cracked","uncracked"=>"Uncracked",""=>"All");

      echo "<select name=\"display\">";
      foreach ($displays as $disid=>$distext) {
        echo "<option value=\"$disid\"";
        if ($disid==$what) echo " selected";
        echo ">$distext</option>";
      }

      echo "</select>";
      if ($src=="hashlist") {
        $filt=$_GET["filter"];
        echo "<select name=\"filter\">";
        foreach ($filters as $filid=>$filtext) {
          echo "<option value=\"$filid\"";
          if ($filid==$filt) echo " selected";
          echo ">$filtext</option>";
        }
        echo "</select>";
      }
      $filter=array("cracked"=>" AND plaintext IS NOT NULL","uncracked"=>" AND plaintext IS NULL");
      $kve="SELECT ";
      switch ($format) {
        case 0:
          // get regular hashes
          $kve.="hashes.hash,hashes.salt,hashes.plaintext";
          break;
          
        case 1:
          // get access points and their passwords
          $kve.="hashes_binary.essid AS hash,hashes_binary.plaintext";
          break;
          
        case 2:
          // get binary - only passwords
          $kve.="'' AS hash,hashes_binary.plaintext";
          break;
      }
      $kve.=" FROM ".$formattables[$format]." ".$viewfilter.$filter[$filt];
      $kver=mysqli_query_wrapper($dblink,$kve);
      echo "<input type=\"submit\" value=\"OK\"></form> Matching hashes: ".mysqli_num_rows($kver);
      echo "<br>";
      echo "<textarea cols=\"100\" rows=\"30\" readonly>";
      while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
        $out="";
        $hash=$erej["hash"];
        $salt=$erej["salt"];
        $plain=$erej["plaintext"];

        switch ($what) {
          case "hash":
            $out.=$hash;
            if ($salt!="") $out.=$config["fieldseparator"]."$salt";
            break;
            
          case "":
            $out.=$hash;
            if ($salt!="") $out.=$config["fieldseparator"]."$salt";
            $out.=$config["fieldseparator"];
          case "plain":
            if ($plain!="") $out.=$plain;
            break;
        }
        if ($out!="") echo "$out\n";
      }
      echo "</textarea>";
      echo "<br>HEX convertor: <script>function hex2a(hex) { var str = ''; for (var i = 0; i < hex.length; i += 2) str += String.fromCharCode(parseInt(hex.substr(i, 2), 16)); return str; }</script>";
      echo "\$HEX[<input type=\"text\" id=\"conv\">] -> <input type=\"text\" id=\"convr\" readonly> <button onclick=\"javascript:document.getElementById('convr').value=hex2a(document.getElementById('conv').value);\">Convert</button>";

      break;
      
    case "agentdetail":
      // show agent details
      $agid=intval($_GET["agent"]);
      $kver=mysqli_query_wrapper($dblink,"SELECT agents.*,assignments.task,SUM(GREATEST(chunks.solvetime,chunks.dispatchtime)-chunks.dispatchtime) AS spent FROM agents LEFT JOIN assignments ON assignments.agent=agents.id LEFT JOIN chunks ON chunks.agent=agents.id WHERE agents.id=$agid");
      if (mysqli_num_rows($kver)!=1) break;
      $erej=mysqli_fetch_array($kver,MYSQLI_ASSOC);
      $task=$erej["task"];
      $wait=$erej["wait"];
      echo "Agent details:";
      echo "<table class=\"styled\">";
      echo "<tr><td>Property</td><td>Value</td></tr>";
      echo "<tr><td>ID:</td><td>$agid</td></tr>";
      echo "<tr><td>Activity:</td><td>";
      echo "<form id=\"agentactive\" action=\"$myself?a=agentactive\" method=\"POST\">";
      echo "<input type=\"hidden\" name=\"agent\" value=\"$agid\">";
      echo "<input type=\"hidden\" name=\"return\" value=\"a=agentdetail&agent=$agid\">";
      echo "<input type=\"checkbox\" name=\"active\" value=\"1\"";
      if ($erej["active"]==1) echo " checked";
      echo " onChange=\"javascript:document.getElementById('agentactive').submit();\"> Agent is active";
      echo "</form>";
      echo "</td></tr>";
      echo "<tr><td>Machine name:</td><td>".$erej["name"]."</td></tr>";
      echo "<tr><td>Operating system:</td><td>".$oses[$erej["os"]]."</td></tr>";
      echo "<tr><td>Access token:</td><td>".$erej["token"]."</td></tr>";
      echo "<tr><td>Machine ID:</td><td>".$erej["uid"]."</td></tr>";
      echo "<tr><td>CPU platform:</td><td>".$erej["cputype"]."-bit</td></tr>";
      echo "<tr><td>GPU platform:</td><td>";
      echo "<form action=\"$myself?a=agentpf\" method=\"POST\">";
      echo "<input type=\"hidden\" name=\"agent\" value=\"$agid\">";
      echo "<input type=\"hidden\" name=\"return\" value=\"a=agentdetail&agent=$agid\">";
      echo "<select name=\"platform\">";
      foreach ($platforms as $idp=>$namep) {
        echo "<option value=\"$idp\"";
        if ($idp==$erej["gpubrand"]) echo " selected";
        echo ">$namep</option>";
      }
      echo "</select> <input type=\"submit\" value=\"Set\"></form>";
      echo "</td></tr>";
      echo "<tr><td>GPU driver:</td><td>".$erej["gpudriver"]."</td></tr>";
      echo "<tr><td>Graphic cards:</td><td>".str_replace($separator,"<br>",$erej["gpus"])."</td></tr>";
      echo "<tr><td>Hashcat version:</td><td>".$erej["hcversion"]."</td></tr>";

      echo "<tr><td>Idle wait:</td><td>";
      echo "<form action=\"$myself?a=agentwait\" method=\"POST\">";
      echo "<input type=\"hidden\" name=\"agent\" value=\"$agid\">";
      echo "<input type=\"hidden\" name=\"return\" value=\"a=agentdetail&agent=$agid\">";
      echo "<input type=\"text\" name=\"wait\" value=\"".$erej["wait"]."\" size=\"4\"> seconds ";
      echo "<input type=\"submit\" value=\"Set\"></form>";
      echo "</td></tr>";

      echo "<tr><td>Extra parameters:</td><td>";
      echo "<form action=\"$myself?a=agentpars\" method=\"POST\">";
      echo "<input type=\"hidden\" name=\"agent\" value=\"$agid\">";
      echo "<input type=\"hidden\" name=\"return\" value=\"a=agentdetail&agent=$agid\">";
      echo "<input type=\"text\" name=\"cmdpars\" value=\"".$erej["cmdpars"]."\" size=\"30\"> ";
      echo "<input type=\"submit\" value=\"Set\"></form>";
      echo "</td></tr>";

      echo "<tr><td>Hashcat errors:</td><td>";
      echo "<form id=\"agentignore\" action=\"$myself?a=agentignore\" method=\"POST\">";
      echo "<input type=\"hidden\" name=\"agent\" value=\"$agid\">";
      echo "<input type=\"hidden\" name=\"return\" value=\"a=agentdetail&agent=$agid\">";
      echo "<input type=\"checkbox\" name=\"ignore\" value=\"1\"";
      if ($erej["ignoreerrors"]==1) echo " checked";
      echo " onChange=\"javascript:document.getElementById('agentignore').submit();\"> Ignore <br><i>(Be careful, this might lead to endless loops!)</i>";
      echo "</form>";
      echo "</td></tr>";

      echo "<tr><td>Trust:</td><td>";
      echo "<form id=\"agenttrusted\" action=\"$myself?a=agenttrusted\" method=\"POST\">";
      echo "<input type=\"hidden\" name=\"agent\" value=\"$agid\">";
      echo "<input type=\"hidden\" name=\"return\" value=\"a=agentdetail&agent=$agid\">";
      echo "<input type=\"checkbox\" name=\"trusted\" value=\"1\"";
      if ($erej["trusted"]==1) echo " checked";
      echo " onChange=\"javascript:document.getElementById('agenttrusted').submit();\"> Trust agent with secret data";
      echo "</form>";
      echo "</td></tr>";

      echo "<tr><td>Assignment:</td><td>";
      echo "<form action=\"$myself?a=agentassign\" method=\"POST\">";
      echo "<input type=\"hidden\" name=\"return\" value=\"a=agentdetail&agent=$agid\">";
      echo "<input type=\"hidden\" name=\"agent\" value=\"$agid\">";
      echo "<select name=\"task\" size=\"1\"><option value=\"\">(unassigned)</option>";
      $kver=mysqli_query_wrapper($dblink,"SELECT id,name FROM tasks WHERE hashlist IS NOT NULL ORDER BY id ASC");
      while($ere=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
        $tid=$ere["id"];
        echo "<option value=\"$tid\"";
        if ($ere["id"]==$task) echo " selected";
        echo ">".$ere["name"]."</option>";
      }
      echo "</select> <input type=\"submit\" value=\"Assign\"></form></td></tr>";

      echo "<tr><td>Last activity:</td><td>";
      echo "Action: ".$erej["lastact"]."<br>";
      echo "Time: ".date($config["timefmt"],$erej["lasttime"])."<br>";
      echo "IP: ".$erej["lastip"];
      echo "</td></tr>";
      echo "<tr><td>Time spent cracking:</td><td>".sectotime($erej["spent"])."</td></tr>";

      echo "</table>";
      $kver=mysqli_query_wrapper($dblink,"SELECT errors.*,chunks.id FROM errors LEFT JOIN chunks ON (errors.time BETWEEN chunks.dispatchtime AND chunks.solvetime) AND chunks.agent=errors.agent WHERE errors.agent=$agid ORDER BY time DESC");
      if (mysqli_num_rows($kver)>0) {
        echo "<br>Error messages:";
        echo "<table class=\"styled\">";
        echo "<tr><td>Time</td><td>Task</td><td>Chunk</td><td>Error message</td></tr>";
        while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
          echo "<tr><td>";
          echo date($config["timefmt"],$erej["time"]);
          echo "</td><td>";
          $task=$erej["task"];
          if ($task=="") {
            echo "N/A";
          } else {
            echo "<a href=\"$myself?a=taskdetail&task=$task\">$task</a>";
          }
          echo "</td><td>";
          echo $erej["id"];
          echo "</td><td style=\"white-space: normal;\">";
          echo $erej["error"];
          echo "</td></tr>";
        }
        echo "</table>";
      }
      $kver=mysqli_query_wrapper($dblink,"SELECT chunks.*,GREATEST(chunks.dispatchtime,chunks.solvetime)-chunks.dispatchtime AS spent,tasks.name AS taskname FROM chunks JOIN tasks ON chunks.task=tasks.id WHERE agent=$agid ORDER BY chunks.dispatchtime DESC,chunks.skip DESC LIMIT 100");
      if (mysqli_num_rows($kver)>0) {
        echo "<br>Dispatched chunks:";
        echo "<table class=\"styled\">";
        echo "<tr><td>id</td><td>Start</td><td>Length</td><td>Checkpoint</td><td>Progress</td><td>Task</td><td>Dispatch time</td><td>Last activity</td><td>Time spent</td><td>State</td><td>Cracked</td></tr>";
        while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
          $task=$erej["task"];
          $dispatchtime=$erej["dispatchtime"];
          $solvetime=$erej["solvetime"];
          $progress=$erej["progress"];
          $length=$erej["length"];
          echo "<tr><td>".$erej["id"]."</td><td class=\"num\">".$erej["skip"]."</td><td class=\"num\">$length</td><td class=\"num\">$progress";
          if ($progress>0 && $progress!=$length) {
            echo "<br>(".showperc($progress,$length)."%)";
          }
          echo "</td><td class=\"num\">";
          echo showperc($erej["rprogress"],10000);
          echo "%</td><td>";
          echo "<a href=\"$myself?a=taskdetail&task=$task\">".$erej["taskname"]."</a>";
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
          if ($erej["cracked"]>0) echo "<a href=\"$myself?a=hashes&chunk=".$erej["id"]."\">".$erej["cracked"]."</a>";
          echo "</td></tr>";
        }
        echo "</table>";
      }
      break;

    case "chunks":
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
    
    case "taskdetail":
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
      
    case "config":
      // show/update server config
      echo "Server configuration:<br>";
      if (isset($_POST["password"])) {
        $pwd=$_POST["password"];
        if (strlen($pwd)>0) {
          $mpwd=makepwd($pwd);
          mysqli_query_wrapper($dblink,"INSERT INTO config (item, value) VALUES ('password','$mpwd') ON DUPLICATE KEY UPDATE value='$mpwd'");
          echo "New password set.";
        }
        break;
      }
      
      if (isset($_POST["setconfig"])) {
        foreach ($_POST as $item=>$val) {
          if (substr($item,0,7)=="config_") {
            $item=substr($item,7);
            mysqli_query_wrapper($dblink,"INSERT INTO config (item, value) VALUES ('$item','".mysqli_real_escape_string($dblink,$val)."') ON DUPLICATE KEY UPDATE value='".mysqli_real_escape_string($dblink,$val)."'");
          }
        }
        echo "Configured server variables.";
        break;
      }

      echo "<form action=\"$myself?a=config\" method=\"POST\">";
      echo "<table class=\"styled\"><tr><td>Item</td><td>Value</td></tr>";
      echo "<tr><td>New password</td><td><input type=\"text\" name=\"password\"></td></tr>";
      echo "<tr><td colspan=\"2\"><input type=\"submit\" value=\"Set\"></td></tr>";
      echo "</table>";
      echo "</form><br>";
      
      echo "<form action=\"$myself?a=config\" method=\"POST\">";
      echo "<input type=\"hidden\" name=\"setconfig\" value=\"1\">";
      echo "<table class=\"styled\"><tr><td>Item</td><td>Value</td></tr>";
      foreach ($config as $item=>$val) {
        if ($item=="password") continue;
        echo "<tr><td>$item</td><td><input type=\"text\" name=\"config_$item\" value=\"$val\"></td></tr>";
      }
      echo "<tr><td colspan=\"2\"><input type=\"submit\" value=\"Set\"></td></tr>";
      echo "</table>";
      echo "</form>";

      echo "<br><br>Database tools:<ul>";
      echo "<li><a href=\"$myself?a=rebuildcache\">Rebuild chunk cache</a><br>Counts cracked hashes in all chunks and all hashlists<br>using slow but precise COUNT() function.</li><br>";
      echo "<li><a href=\"$myself?a=rescanfiles\">Rescan global files</a><br>Scans all global files for size mismatch or inexistence.</li><br>";
      echo "<li><a href=\"$myself?a=clearall\">Clear all</a><br>Erases all hashlists, tasks (not pre-configured) and chunks of those tasks.</li><br>";
      echo "</ul>";
      break;
      
    case "deploy":
      // manage registration vouchers
      echo "Provide agent with valid voucher and this link:<br>";
      echo "<a href=\"server.php?a=update\">Download agent</a><br><br>";
      if (isset($_POST["newvoucher"])) {
        mysqli_query_wrapper($dblink,"INSERT INTO regvouchers (voucher,time) VALUES ('".mysqli_real_escape_string($dblink,$_POST["newvoucher"])."',$cas)");
      }
      $kver=mysqli_query_wrapper($dblink,"SELECT voucher,time FROM regvouchers");
      if (mysqli_num_rows($kver)>0) {
        echo "Existing vouchers:<br>";
        echo "<table class=\"styled\"><tr><td>Voucher</td><td>Issued</td><td>Action</td></tr>";
        while ($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
          $id=$erej["voucher"];
          echo "<tr><td>$id</td>";
          echo "<td>".date($config["timefmt"],$erej["time"])."</td>";
          echo "<td><form action=\"$myself?a=voucherdelete\" method=\"POST\" onSubmit=\"if (!confirm('Really delete this voucher?')) return false;\">";
          echo "<input type=\"hidden\" name=\"return\" value=\"a=deploy\">";
          echo "<input type=\"hidden\" name=\"voucher\" value=\"$id\">";
          echo "<input type=\"submit\" value=\"Delete\"></form></td></tr>";
        }
        echo "</table>Used vouchers are automaticaly deleted to prevent double spending.<br><br>";
      }
      echo "<form action=\"$myself?a=deploy\" method=\"POST\">";
      echo "<table class=\"styled\"><tr><td>New voucher</td></tr>";
      echo "<tr><td><input type=\"text\" name=\"newvoucher\" value=\"".generate_random(8)."\"></td></tr>";
      echo "<tr><td><input type=\"submit\" value=\"Create\"></td></tr>";
      echo "</table></form>";
      break;
    
    case "logout":
      unset($_SESSION[$sess_name]);
      echo "<script>alert('Logged out.');location.href='$myself';</script>";
      break;
      
    case "manual":
      echo "<iframe src=\"manual.html\"></iframe>";
      break;
    
    case "custmenu":
      // custom menu
      $menu=$_GET["menu"];
      if (isset($custmenuitems[$menu])) {
        echo $custmenuitems[$menu]["name"].":<br>";
        echo "<iframe src=\"".$custmenuitems[$menu]["source"]."\"></iframe>";
      } else {
        echo "Unknown menu.";
      }
      break;
    
    case "":
      echo "Welcome to Hashtopus $htpver, the ultimate multi-platform portable solution to distributed hash cracking.<br>";
      echo "If you have problems with anything, try <a href=\"manual.html\" target=\"_blank\">consulting the manual</a>.";
  }

  // if there is someplace to return, go there
  if ($returnpage!="") echo "<script>location.href='$myself?$returnpage';</script>";

} else {
  echo "<form action=\"$myself?a=\" method=\"POST\"><input type=\"hidden\" name=\"return\" value=\"".$_SERVER['QUERY_STRING']."\">Password: <input type=\"password\" name=\"pwd\" autofocus><input type=\"submit\" value=\"Login\"></form>";
}

function makepwd($pwd) {
  // password hashing function (how ironic:D)
  return sha1("l3Bsn@^auh28".$pwd."m+3RuKngLy\$t0alT");
}

function shortenstring($co,$kolik) {
  // shorten string that would be too long
  echo "<span title=\"$co\">";
  if (strlen($co)>$kolik) {
    echo substr($co,0,$kolik-3)."...";
  } else {
    echo $co;
  }
  echo "</span>";
}

function niceround($num,$dec) {
  // round to specific amount of decimal places
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

function uploadFile($tmpfile,$source,$sourcedata) {
  // upload file from multiple sources
  global $uperrs;
  
  $povedlo=false;
  echo "<b>Adding file $tmpfile:</b><br>";
  if (!file_exists($tmpfile)) {
    switch ($source) {
      case "paste":
        echo "Creating file from text field...";
        if (file_put_contents($tmpfile,$sourcedata)) {
          echo "OK";
          $povedlo=true;
        } else {
          echo "ERROR!";
        }
        break;
          
      case "upload":
        $hashfile=$sourcedata;
        $hashchyba=$hashfile["error"];
        if ($hashchyba==0) {
          echo "Moving uploaded file...";
          if (move_uploaded_file($hashfile["tmp_name"],$tmpfile) && file_exists($tmpfile)) {
            echo "OK";
            $povedlo=true;
          } else {
            echo "ERROR";
          }
        } else {
          echo "Upload file error: ".$uperrs[$hashchyba];
        }
        break;
      
      case "import":
        echo "Loading imported file...";
        if (file_exists("import/".$sourcedata)) {
          rename("import/".$sourcedata,$tmpfile);
          if (file_exists($tmpfile)) {
            echo "OK";
            $povedlo=true;
          } else {
            echo "DST ERROR";
          }
        } else {
          echo "SRC ERROR";
        }
        break;
        
      case "url":
        $local=basename($sourcedata);
        echo "Downloading remote file <a href=\"$sourcedata\" target=\"_blank\">$local</a>...";

        $furl=fopen($sourcedata,"rb");
        if (!$furl) {
          echo "SRC ERROR";
        } else {
          $floc=fopen($tmpfile,"w");
          if (!$floc) {
            echo "DST ERROR";
          } else {
            $downed=0;
            $bufsize=131072;
            $cas_pinfo=time();
            while (!feof($furl)) {
              if (!$data=fread($furl,$bufsize)) {
                echo "READ ERROR";
                break;
              }
              fwrite($floc,$data);
              $downed+=strlen($data);
              if ($cas_pinfo<time()-10) {
                echo nicenum($downed,1024)."B...\n";
                $cas_pinfo=time();
                flush();
              }
            }
            fclose($floc);
            echo "OK (".nicenum($downed,1024)."B)";
            $povedlo=true;
          }
          fclose($furl);
        }
        break;

      default:
        echo "Wrong file source.";
    }
  } else {
    echo "File already exists.";
  }
  echo "<br>";
  return $povedlo;
}

function insertFile($tmpfile) {
  // insert existing file into global files
  global $dblink;
  $allok=false;
  if (file_exists($tmpfile)) {
    $velikost=filesize($tmpfile);
    $nazev=mysqli_real_escape_string($dblink,basename($tmpfile));
    echo "Inserting <a href=\"$tmpfile\" target=\"_blank\">$nazev</a> into global files...";
    if (mysqli_query_wrapper($dblink,"INSERT INTO files (filename,size) VALUES ('$nazev',$velikost)")) {
      $fid=mysqli_insert_id($dblink);
      echo "OK (<a href=\"$myself?a=files#$fid\">list</a>)";
      $allok=true;
    } else {
      echo "DB ERROR";
    }
  }
  echo "<br>";
  return $allok;
}

function tickdone($prog,$total) {
  // show tick of progress is done
  if ($total>0 && $prog==$total) {
    return " <img src=\"img/check.png\" alt=\"Finished\">";
  }
  return "";
}

function showperc($part,$total,$decs=2) {
  // show nicely formated percentage
  if ($total>0) {
    $vys=round(($part/$total)*100,$decs);
    if ($vys==100 && $part<$total) {
      $vys-=1/(10^$decs);
    }
    if ($vys==0 && $part>0) {
      $vys+=1/(10^$decs);
    }
  } else {
    $vys=0;
  }
  $vysnew=niceround($vys,$decs);
  return $vysnew;
}

function superList($hlist,&$format) {
  // detect superhashlists and create array of its contents
  global $dblink;
  
  if ($format==3) {
    $superhash=true;
  } else {
    $superhash=false;
  }
  
  $hlistar=array();
  if ($superhash) {
    $kve=mysqli_query_wrapper($dblink,"SELECT hashlists.id,hashlists.format FROM superhashlists JOIN hashlists ON superhashlists.hashlist=hashlists.id WHERE superhashlists.id=$hlist");
    while($ere=mysqli_fetch_array($kve,MYSQLI_ASSOC)) {
      $format=$ere["format"];
      $hlistar[]=$ere["id"];
    }
  } else {
    $hlistar[]=$hlist;
  }
  $hlisty=implode(",",$hlistar);
  return array($superhash,$hlisty);
}

function sectotime($soucet) {
  // convert seconds to human readable format
  $vysledek="";
  if ($soucet>86400) {
    $dnu=floor($soucet/86400);
    if ($dnu>0) $vysledek.=$dnu."d ";
    $soucet=$soucet%86400;
  }
  $vysledek.=gmdate("H:i:s",$soucet);
  return $vysledek;
}

function delete_task($task) {
  // delete task
  global $dblink;
  $vysledek1=mysqli_query_wrapper($dblink,"DELETE FROM assignments WHERE task=$task");
  $vysledek2=$vysledek1 && mysqli_query_wrapper($dblink,"DELETE FROM errors WHERE task=$task");
  $vysledek3=$vysledek2 && mysqli_query_wrapper($dblink,"DELETE FROM taskfiles WHERE task=$task");

  $vysledek4=$vysledek3 && mysqli_query_wrapper($dblink,"UPDATE hashes JOIN chunks ON hashes.chunk=chunks.id AND chunks.task=$task SET chunk=NULL");
  $vysledek5=$vysledek4 && mysqli_query_wrapper($dblink,"UPDATE hashes_binary JOIN chunks ON hashes_binary.chunk=chunks.id AND chunks.task=$task SET chunk=NULL");
  $vysledek6=$vysledek5 && mysqli_query_wrapper($dblink,"DELETE FROM zapqueue WHERE chunk IN (SELECT id FROM chunks WHERE task=$task)");
  $vysledek7=$vysledek6 && mysqli_query_wrapper($dblink,"DELETE FROM chunks WHERE task=$task");

  $vysledek8=$vysledek7 && mysqli_query_wrapper($dblink,"DELETE FROM tasks WHERE id=$task");
  
  return ($vysledek8);
}

function delete_agent($agent) {
  // delete agent
  global $dblink;

  $vysledek1=mysqli_query_wrapper($dblink,"DELETE FROM assignments WHERE agent=$agent");
  $vysledek2=$vysledek1 && mysqli_query_wrapper($dblink,"DELETE FROM errors WHERE agent=$agent");
  $vysledek3=$vysledek2 && mysqli_query_wrapper($dblink,"DELETE FROM hashlistusers WHERE agent=$agent");
  $vysledek4=$vysledek3 && mysqli_query_wrapper($dblink,"DELETE FROM zapqueue WHERE agent=$agent");

  // orphan the chunks
  $vysledek5=$vysledek4 && mysqli_query_wrapper($dblink,"UPDATE hashes JOIN chunks ON hashes.chunk=chunks.id AND chunks.agent=$agent SET chunk=NULL");
  $vysledek6=$vysledek5 && mysqli_query_wrapper($dblink,"UPDATE hashes_binary JOIN chunks ON hashes_binary.chunk=chunks.id AND chunks.agent=$agent SET chunk=NULL");
  $vysledek7=$vysledek6 && mysqli_query_wrapper($dblink,"UPDATE chunks SET agent=NULL WHERE agent=$agent");

  $vysledek8=$vysledek7 && mysqli_query_wrapper($dblink,"DELETE FROM agents WHERE id=$agent");
  
  return ($vysledek8);
}

$endtime=microtime(true);
echo "<!-- Load time: ".($endtime-$loadtime)."ms -->";
?>
</td></tr></table>
</body></html>
