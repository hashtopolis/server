<?php

function mysqli_query_wrapper($dblink, $query) {
  // function to wrap mysql queries and log them if they take too long or end up in error
  // $logging sets how does the query need to take so it's logged (default = 1 /one second/)
  // to log all, simply set it to 0 (i know, somewhat illogical)
  $logging=1;
  if ($logging>-1) {
    $log=date("d.m.Y H:i:s")."\r\n".$query;
    $time1=microtime(true);
    $kver=mysqli_query($dblink,$query);
    $took=microtime(true)-$time1;
    $afrows=mysqli_affected_rows($dblink);
    if ($took>$logging || $afrows<0) {
      if ($afrows<0) $afrows.=" (".mysqli_error($dblink).")";
      $log.="\r\n[$took] $afrows\r\n\r\n";
      file_put_contents("server_db.txt",$log,FILE_APPEND);
    }
  } else {
    $kver=mysqli_query($dblink,$query);
  }
  return $kver;
}


set_time_limit(0);
include("dbconfig.php");
$exename="hashtopus.exe";

$action=mysqli_real_escape_string($dblink,@$_GET["a"]);
$token=mysqli_real_escape_string($dblink,@$_GET["token"]);
$cas=time();
header("Content-Type: application/octet-stream");


switch ($action) {
  case "reg":
    // register at master server - user will be given a token
    $voucher=mysqli_real_escape_string($dblink,$_POST["voucher"]);
    mysqli_query_wrapper($dblink,"START TRANSACTION");
    $tc=mysqli_query_wrapper($dblink,"SELECT 1 FROM regvouchers WHERE voucher='$voucher'");
    if (mysqli_num_rows($tc)==1) {
      mysqli_query_wrapper($dblink,"DELETE FROM regvouchers WHERE voucher='$voucher'");
      $cpu=intval($_POST["cpu"]);
      $gpu=mysqli_real_escape_string($dblink,$_POST["gpus"]);
      $uid=mysqli_real_escape_string($dblink,$_POST["uid"]);
      $name=mysqli_real_escape_string($dblink,$_POST["name"]);
      $os=intval($_POST["os"]);
      
      $brand=0;
      // detect brand strings
      foreach (explode($separator,strtolower($gpu)) as $karta) {
        if ((strpos($karta,"amd")!== false) || (strpos($karta,"ati ")!== false) || (strpos($karta,"radeon")!== false)) {
          $brand=2;
          break;
        }
        if (strpos($karta,"nvidia")!==false) {
          $brand=1;
        }
      }
      // create access token
      $token=generate_random(10);
      
      // save the new agent to the db or update existing one with the same hdd-serial
      if (mysqli_query_wrapper($dblink,"INSERT INTO agents (name, uid, os, cputype, gpubrand, gpus, token) VALUES ('$name', '$uid', $os, $cpu, $brand,'$gpu','$token')")) {
        echo "reg_ok".$separator.$token;
      } else {
        echo "reg_nok".$separator."Could not register you to server.";
      }
    } else {
      echo "reg_nok".$separator."Provided voucher does not exist.";
    }
    mysqli_query_wrapper($dblink,"COMMIT");
    break;

  case "log":
    // login to master server with previously provided token
    $kvery=mysqli_query_wrapper($dblink,"SELECT agents.cputype,agents.gpubrand,agents.os FROM agents WHERE agents.token='$token'");
    if (mysqli_num_rows($kvery)==1) {
      // there is a user with this token in the db
      $erej=mysqli_fetch_array($kvery,MYSQLI_ASSOC);
      $cpu=intval($erej["cputype"]);
      $gpu=intval($erej["gpubrand"]);
      $os=intval($erej["os"]);
      if (($gpu==0) || (($cpu!=32) && ($cpu!=64))){
        echo "log_nok".$separator."Unknown platform, wait to be manually assigned.";
      } else {
        // craft executable name
        echo "log_ok".$separator.$gpu.$separator.$config["agenttimeout"];
      } 
    } else {
      // token was not found
      echo "log_unknown".$separator."Unknown token, register again";
    }
    break;
  
  case "update":
    // check if provided hash is the same as executable and send file contents if not
    $hash=(isset($_GET["hash"]) ? $_GET["hash"] : "");
    $htexe=file_get_contents($exename)."http".(isset($_SERVER['HTTPS']) ? "s" : "")."://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
    $myhash=md5($htexe);
    if ($hash!=$myhash) {
      header("Content-Disposition: attachment; filename=\"$exename\""); 
      echo $htexe;
    }
    break;

  case "down":
    // provide agent with requested download
    $typ=$_GET["type"];
    switch ($typ) {
      case "7zr":
        // downloading 7zip
        $kver=mysqli_query_wrapper($dblink,"SELECT os FROM agents WHERE token='$token'");
        if (mysqli_num_rows($kver)==1) {
          // agent is ok
          $ere=mysqli_fetch_array($kver,MYSQLI_ASSOC);
          $fn="7zr";
          if ($ere["os"]==0) $fn.=".exe";
          echo file_get_contents($fn);
        }
        break;
        
      case "hc":
        // downloading hashcat
        $kver=mysqli_query_wrapper($dblink,"SELECT id,cputype,gpubrand,hcversion,os FROM agents WHERE token='$token'");
        if (mysqli_num_rows($kver)==1) {
          // agent is ok
          $ere=mysqli_fetch_array($kver,MYSQLI_ASSOC);

          $id=$ere["id"];
          $cpu=$ere["cputype"];
          $gpu=$ere["gpubrand"];
          $os=$ere["os"];
          $postf=array("","nvidia","amd");
          $platf=$postf[$gpu];
          
          $driver=intval($_GET["driver"]);

          // find out newest version
          $kver=mysqli_query_wrapper($dblink,"SELECT * FROM hashcatreleases WHERE minver_$platf<=$driver ORDER BY time DESC LIMIT 1");
          if ($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
            // there are some releases defined
            $verze=$erej["version"];
            $minver=$erej["minver_$platf"];
            $url=$erej["url_$platf"];
            $rootdir=$erej["rootdir_$platf"];
            
            $force=(isset($_GET["force"]) ? 1 : 0);
             
            $prefix=array("","cuda","ocl");
            $postfix=array("exe","bin");
            $exe=$prefix[$gpu]."Hashcat$cpu.".$postfix[$os];
             
            if (($ere["hcversion"]!=$verze) || ($force==1)) {
              // the agent needs updating
              $files=$erej["common_files"]." ".$erej[$cpu."_".$platf];
              
              // give the agent oclhashcat url, a name of root dir and list of files to extract from the archive
              echo "down_ok".$separator.$url.$separator.$files.$separator.$rootdir.$separator.$exe;
            } else {
              // the agent has up2date version
              echo "down_na".$separator.$exe;
            }
          } else {
            // no release was found at all
            $verze=$ere["hcversion"];
            echo "down_nok".$separator."No hashcat releases found for this driver!";
          }
          mysqli_query_wrapper($dblink,"UPDATE agents SET hcversion='$verze',gpudriver=$driver WHERE id=$id");
        } else {
          echo "down_nok".$separator."Access token invalid.";
        }
        break;
        
      default:
        echo "down_nok".$separator."Unknown download type.";
    }
    break;
    
  case "task":
    // tell agent information about its task
    
    // elaborate select where first line is agent's current assigned task (should there be any)
    // and the second is the following task the agent should be assigned to once his current is completed
    // (if agent is not assigned to any task, the next assigned is in the first line - this is
    // identified by column named 'this', where 0=to be assigned, and >0=is assigned)
    $kver=mysqli_query_wrapper($dblink,"SELECT tasks.id,tasks.autoadjust AS autotask,agents.wait,tasks.attackcmd,hashlists.hashtype,hashlists.format,agents.cmdpars,tasks.statustimer,tasks.hashlist,tasks.priority,IF(tasks.hashlist=atasks.hashlist AND atasks.hashlist IS NOT NULL AND tasks.hashlist IS NOT NULL,'continue','new') AS bench,IF(chunks.sumdispatch=tasks.keyspace AND tasks.progress=tasks.keyspace AND tasks.keyspace>0,0,1) AS taskinc,IF(hashlists.cracked<hashlists.hashcount,1,0) AS hlinc,IF(atasks.id=tasks.id,agents.id,0) AS this FROM tasks JOIN hashlists ON tasks.hashlist=hashlists.id LEFT JOIN (SELECT taskfiles.task,MAX(secret) AS secret FROM taskfiles JOIN files ON taskfiles.file=files.id GROUP BY taskfiles.task) taskfiles ON taskfiles.task=tasks.id JOIN agents ON agents.token='$token' AND agents.active=1 AND agents.trusted>=GREATEST(IFNULL(taskfiles.secret,0),hashlists.secret) LEFT JOIN assignments ON assignments.agent=agents.id LEFT JOIN tasks atasks ON assignments.task=atasks.id LEFT JOIN (SELECT chunks.task,SUM(chunks.length) AS sumdispatch FROM chunks JOIN tasks ON chunks.task=tasks.id WHERE chunks.progress=chunks.length OR GREATEST(chunks.solvetime,chunks.dispatchtime)>=".($cas-$config["chunktimeout"])." GROUP BY chunks.task) chunks ON chunks.task=tasks.id WHERE atasks.id=tasks.id OR ((tasks.progress<tasks.keyspace OR IFNULL(chunks.sumdispatch,0)<tasks.keyspace OR tasks.keyspace=0) AND tasks.priority>0 AND hashlists.cracked<hashlists.hashcount) ORDER BY this DESC, tasks.priority DESC LIMIT 2");
    $ere=mysqli_fetch_array($kver,MYSQLI_ASSOC);
    if ($ere!=false) {
      // first line is valid
      if ($ere["this"]>0) {
        // this agent is assigned to this task
        
        // is the current task done?
        $curdone=($ere["taskinc"]==0 || $ere["hlinc"]==0);
      
        // read the following task
        if ($erenew=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
          $reass=true;
          $newdone=($erenew["taskinc"]==0 || $erenew["hlinc"]==0);
        } else {
          // there is no other prioritized task
          $reass=false;
          $newdone=true;
        }

        if ($reass && !$newdone) {
          // there is some other incomplete prioritized task 
          if ($curdone || $erenew["priority"]>$ere["priority"]) {
            // the current task is done or the next one has higher priority
            
            // so reassign the agent to the next one
            mysqli_query_wrapper($dblink,"UPDATE assignments SET task=".$erenew["id"].",benchmark=IFNULL((SELECT length FROM chunks WHERE solvetime>dispatchtime AND progress=length AND state IN (4,5) AND agent=".$ere["this"]." AND task=".$erenew["id"]." ORDER BY solvetime DESC LIMIT 1),0),speed=0,autoadjust=".$erenew["autotask"]." WHERE agent=".$ere["this"]);
            // but keep agressivity of the previous one
            $ere=$erenew;
          }
        } else {
          // there is nothing else to move on
          if ($curdone) {
            // the current task is done so we unassign from it
            mysqli_query_wrapper($dblink,"DELETE FROM assignments WHERE agent=".$ere["this"]);
            // erase hashlist users, they will be returned on joining next task
            if ($ere["format"]==3) {
              mysqli_query_wrapper($dblink,"DELETE FROM hashlistusers WHERE hashlist IN (SELECT hashlist FROM superhashlists WHERE id=".$ere["hashlist"].") AND agent=".$ere["this"]);
              mysqli_query_wrapper($dblink,"DELETE FROM zapqueue WHERE hashlist IN (SELECT hashlist FROM superhashlists WHERE id=".$ere["hashlist"].") agent=".$ere["this"]);
            } else {
              mysqli_query_wrapper($dblink,"DELETE FROM hashlistusers WHERE hashlist=".$ere["hashlist"]." AND agent=".$ere["this"]);
              mysqli_query_wrapper($dblink,"DELETE FROM zapqueue WHERE hashlist=".$ere["hashlist"]." AND agent=".$ere["this"]);
            }
            echo "task_nok".$separator."No more active tasks.";
            break;
          }
        }
      } else {
        // the first line is the task to be assigned to (agent is not assigned to anything)
        mysqli_query_wrapper($dblink,"INSERT INTO assignments (agent,task,autoadjust,benchmark) SELECT agents.id,".$ere["id"].",".$ere["autotask"].",IFNULL(chunks.length,0) FROM agents LEFT JOIN chunks ON agents.id=chunks.agent AND chunks.solvetime>chunks.dispatchtime AND chunks.state IN (4,5) AND chunks.progress=chunks.length AND chunks.task=".$ere["id"]." WHERE agents.token='$token' ORDER BY chunks.solvetime DESC LIMIT 1");
      }

      // ok, we have something to begin with
      echo "task_ok".$separator.$ere["id"].$separator.$ere["wait"].$separator.$ere["attackcmd"].(strlen($ere["cmdpars"])>0 ? " ".$ere["cmdpars"] : "")." --hash-type=".$ere["hashtype"].$separator.$ere["hashlist"].$separator.$ere["bench"].$separator.$ere["statustimer"];
      // and add listing of related files
      $kver=mysqli_query_wrapper($dblink,"SELECT files.filename FROM taskfiles JOIN files ON taskfiles.file=files.id WHERE taskfiles.task=".$ere["id"]);
      while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
        echo $separator.$erej["filename"];
      }
    } else {
      // there was nothing
      mysqli_query_wrapper($dblink,"UPDATE assignments JOIN agents ON assignments.agent=agents.id AND agents.token='$token' SET assignments.speed=0");
      echo "task_nok".$separator."No active tasks.";
    }
    break;

  case "file":
    // let agent download adjacent files
    $task=intval($_GET["task"]);
    $file=mysqli_real_escape_string($dblink,$_GET["file"]);
    $kver=mysqli_query_wrapper($dblink,"SELECT 1 FROM assignments JOIN tasks ON tasks.id=assignments.task JOIN agents ON agents.id=assignments.agent JOIN taskfiles ON taskfiles.task=tasks.id JOIN files ON taskfiles.file=files.id WHERE agents.token='$token' AND tasks.id=$task AND files.filename='$file' AND agents.trusted>=files.secret");
    if (mysqli_num_rows($kver)==1) {
      // and add listing of related files
      header("Location: files/$file");
    }
    break;
    
  case "hashes":
    // download list of uncracked hashes from server
    $hlist=intval($_GET["hashlist"]);
    $kvery=mysqli_query_wrapper($dblink,"SELECT agents.id,agents.os,agents.trusted,tasks.hashlist,hashlists.format FROM agents JOIN assignments ON assignments.agent=agents.id JOIN tasks ON tasks.id=assignments.task JOIN hashlists ON hashlists.id=tasks.hashlist WHERE hashlists.id=$hlist AND agents.token='$token' AND agents.trusted>=hashlists.secret");
    if ($erej=mysqli_fetch_array($kvery,MYSQLI_ASSOC)) {
      // agent is assigned to an existing task
      if ($erej["os"]==1) {
        $newline="\n";
      } else {
        $newline="\r\n";
      }
      $agid=$erej["id"];
      $format=$erej["format"];
      $trusted=$erej["trusted"];
      // superhashlist detection
      if ($format==3) {
        $superhash=true;
      } else {
        $superhash=false;
      }
      
      // handle superhahslist - give agent hashes from included hashlists
      // and only those that his trust level is allowed to get
      $hlistar=array();
      if ($superhash) {
        $kve=mysqli_query_wrapper($dblink,"SELECT hashlists.id,hashlists.format FROM superhashlists JOIN hashlists ON superhashlists.hashlist=hashlists.id WHERE superhashlists.id=$hlist AND hashlists.secret<=$trusted");
        while($ere=mysqli_fetch_array($kve,MYSQLI_ASSOC)) {
          $format=$ere["format"];
          $hlistar[]=$ere["id"];
        }
      } else {
        $hlistar[]=$hlist;
      }
      if (count($hlistar)>0) {
        $hlisty=implode(",",$hlistar);
        
        // dump unresolved hashes
        switch ($format) {
          case 0:
            // for text file
            $kvery=mysqli_query_wrapper($dblink,"SELECT DISTINCT hash,salt FROM hashes WHERE hashlist IN ($hlisty) AND plaintext IS NULL");
            if (mysqli_num_rows($kvery)>0) {
              // there are hashes for this task
              while($erej=mysqli_fetch_array($kvery,MYSQLI_ASSOC)) {
                echo $erej["hash"];
                if ($erej["salt"]!="") echo $separator.$erej["salt"];
                echo $newline;
              }
            } else {
              // there are no hashes
              echo "hashes_na".$separator."No hashes to crack.";
            }
            break;
            
          case 1:
          case 2:
            // for binary file
            $kvery=mysqli_query_wrapper($dblink,"SELECT hash FROM hashes_binary WHERE hashlist IN ($hlisty) AND plaintext IS NULL");
            if (mysqli_num_rows($kvery)>0) {
              // there are binary hash(es) for this task
              while($erej=mysqli_fetch_array($kvery,MYSQLI_ASSOC)) {
                // simply concat the binary blobs one after another
                echo $erej["hash"];
              }
            } else {
              // there are no hashes
              echo "hashes_na".$separator."No binary hashes to crack.";
            }
            break;
        }
        // update the last time when the agent was given complete hashlist
        mysqli_query_wrapper($dblink,"INSERT IGNORE INTO hashlistusers (hashlist,agent) SELECT id,$agid FROM hashlists WHERE id IN ($hlisty)");
      } else {
        echo "hashes_nok".$separator."No hashes are available for you.";
      }
    } else {
      echo "hashes_nok".$separator."Task does not exist or you are not assigned to it.";
    }
    break;

  case "chunk":
    // assign a correctly sized chunk to agent
    
    // default: 1.2 (120%) this says that if desired chunk size is X and remaining keyspace is 1.2 * X then
    // it will be assigned as a whole instead of first assigning X and then 0.2 * X (which would be very small
    // and therefore very slow due to lack of GPU utilization)
    $disptolerance=1.2;
    
    $task=intval($_GET["task"]);
    $kver=mysqli_query_wrapper($dblink,"SELECT assignments.benchmark,agents.id,tasks.chunktime,tasks.progress,IFNULL(chunks.sumdispatch,0) AS sumdispatch,tasks.keyspace FROM agents JOIN assignments ON assignments.agent=agents.id JOIN tasks ON tasks.id=assignments.task LEFT JOIN (SELECT chunks.task,SUM(chunks.length) AS sumdispatch FROM chunks JOIN tasks ON chunks.task=tasks.id WHERE chunks.progress=chunks.length OR GREATEST(chunks.solvetime,chunks.dispatchtime)>=".($cas-$config["chunktimeout"])." GROUP BY chunks.task) chunks ON chunks.task=tasks.id WHERE agents.active=1 AND agents.token='$token' AND tasks.id=$task");
    if (mysqli_num_rows($kver)==1) {
      $erej=mysqli_fetch_array($kver,MYSQLI_ASSOC);
      $chunktime=$erej["chunktime"];
      $bench=floatval($erej["benchmark"]);
      $agid=$erej["id"];
      if ($erej["keyspace"]>0) {
        // we know the keyspace already
        if ($erej["progress"]<$erej["keyspace"] || $erej["sumdispatch"]<$erej["keyspace"]) {
          // there are either some uncomplete chunks among fully dispatched chunk
          // or it was not fully dispatched yet
          if ($bench>0) {
            // valid agent benchmark
            mysqli_query_wrapper($dblink,"START TRANSACTION");
            $kver=mysqli_query_wrapper($dblink,"SELECT chunks.id,chunks.length,chunks.skip,chunks.progress,chunks.agent,chunks.dispatchtime FROM chunks JOIN tasks ON chunks.task=tasks.id WHERE chunks.task=$task AND chunks.progress<chunks.length AND ((GREATEST(chunks.dispatchtime,chunks.solvetime)<".($cas-$config["chunktimeout"])." AND (chunks.agent!=$agid OR chunks.agent IS NULL)) OR (chunks.agent=$agid) OR (chunks.state=6) OR (chunks.state=10)) ORDER BY chunks.skip ASC LIMIT 1");
            $createnew=false;
            $cid=-1;
            if ($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
              // there is an unfinished chunk for too long
              $ocid=$erej["id"];
              $oagid=$erej["agent"];
              if ($oagid=="") $oagid="NULL";
              $skip=$erej["skip"];
              $delka=$erej["length"];
              $prog=$erej["progress"];
              $dpt=$erej["dispatchtime"];
              
              // move on by checkpoint
              $skip+=$prog;
              $delka-=$prog;

              if ($delka>$bench*$disptolerance && $oagid!=$agid) {
                // if the remains are bigger than curent agent's benchmark, we assign the biggest possible part and create new chunk from the rest

                // this is the remaining ending part (the new chunk will be cut from starting point)
                $nskip=$skip+$bench;
                $ndelka=$delka-$bench;
                mysqli_query_wrapper($dblink,"INSERT INTO chunks (task,skip,length,agent,dispatchtime,state) VALUES ($task,$nskip,$ndelka,$oagid,$dpt,9)");
                
                // and this is the length of the part that's being redispatched
                $delka=$bench;
              }
              
              if ($prog==0) {
                // the remains of the incomplete chunk are in fact the whole chunk - there was no progress
                // we will transfer it to the new agent
                mysqli_query_wrapper($dblink,"UPDATE chunks SET agent=$agid,length=$delka,rprogress=0,dispatchtime=$cas,solvetime=0,state=0 WHERE id=$ocid");
                $cid=$ocid;
              } else {
                // some of the chunk was complete, cut the complete part to standalone finished chunk
                mysqli_query_wrapper($dblink,"UPDATE chunks SET length=progress,rprogress=10000,state=9 WHERE id=$ocid");
                // and set indicator to create a new one
                $createnew=true;
              }
            } else {
              // all chunks are OK, cut a new one
              $kver=mysqli_query_wrapper($dblink,"SELECT progress,keyspace FROM tasks WHERE id=$task");
              $erej=mysqli_fetch_array($kver,MYSQLI_ASSOC);
              $progress=$erej["progress"];
              $keyspac=$erej["keyspace"];
              
              $remains=$keyspac-$progress;

              if ($remains>0) {
                // calculate length of next chunk (either benchmark or whole remaining keyspace, whichever is smaller)
                $delka=min($remains,$bench);
                // but if the proposed length is in the tolerance of what is remaining, just take it all
                if ($remains/$delka<=$disptolerance) $delka=$remains;
                $nprogres=$progress+$delka;
                mysqli_query_wrapper($dblink,"UPDATE tasks SET progress=$nprogres WHERE id=$task");
                // set indicator to create a new one
                $createnew=true;
                $skip=$progress;
              }
            }
            
            if ($createnew==true) {
              // now create a new chunk for the client
              $createnew=mysqli_query_wrapper($dblink,"INSERT INTO chunks (task,skip,length,agent,dispatchtime) VALUES ($task,$skip,$delka,$agid,$cas)");
              if ($createnew && mysqli_affected_rows($dblink)==1) {
                // new chunk created, set the id
                $cid=mysqli_insert_id($dblink);
              }
            }
            
            if ($cid>0) {
              // the chunk creation query executed correctly or the chunk was reassigned
              mysqli_query_wrapper($dblink,"COMMIT");
              echo "chunk_ok".$separator.$cid.$separator.$skip.$separator.$delka;
            } else {
              mysqli_query_wrapper($dblink,"ROLLBACK");
              echo "chunk_nok".$separator."Could not request a chunk.";
            }
          } else {
            // benchmark is not set or invalid, request it
            echo "bench_req".$separator.$config["benchtime"];
          }
        } else {
          // the task is fully dispatched into chunks
          echo "chunk_nok".$separator."The task has already been fully dispatched.";
        }
      } else {
        // the task doesn't know its keyspace yet
        echo "keyspace_req".$separator;
      }
    } else {
      echo "chunk_nok".$separator."Task does not exist or you are not assigned to it.";
    }
    break;

  case "keyspace":
    // agent submits keyspace size for this task
    $task=intval($_GET["task"]);
    $ks=floatval($_GET["keyspace"]);
    $kver=mysqli_query_wrapper($dblink,"SELECT tasks.keyspace FROM assignments JOIN tasks ON tasks.id=assignments.task JOIN agents ON agents.id=assignments.agent WHERE agents.token='$token' AND tasks.id=$task");
    if (mysqli_num_rows($kver)==1) {
      $erej=mysqli_fetch_array($kver,MYSQLI_ASSOC);
      if ($erej["keyspace"]==0) {
        // if the keyspace is still unknown
        if ($ks>0) {
          // its properly measured
          if (mysqli_query_wrapper($dblink,"UPDATE tasks SET keyspace=$ks WHERE id=$task")) {
            echo "keyspace_ok";
          } else {
            echo "keyspace_nok".$separator."Could not set keyspace for this task.";
          }
        } else {
          // it came out to zero
          echo "keyspace_nok".$separator."You returned zero result.";
        }
      } else {
        // its already defined - pretend it's ok
        echo "keyspace_ok";
      }
    } else {
      echo "keyspace_nok".$separator."Task does not exist or you are not assigned to it.";
    }
    break;

  case "bench":
    // agent submits benchmark for task
    $task=intval($_GET["task"]);
    $bprog=floatval($_GET["progress"]);
    $btotal=floatval($_GET["total"]);
    $state=intval($_GET["state"]);
    $kver=mysqli_query_wrapper($dblink,"SELECT tasks.keyspace,agents.id,tasks.chunktime FROM assignments JOIN tasks ON tasks.id=assignments.task JOIN agents ON agents.id=assignments.agent WHERE agents.token='$token' AND tasks.id=$task");
    if (mysqli_num_rows($kver)==1) {
      $erej=mysqli_fetch_array($kver,MYSQLI_ASSOC);
      $agid=$erej["id"];
      $chunktime=$erej["chunktime"];
      if ($bprog<=0) {
        // could not benchmark - pause agents assignment until further resolved
        mysqli_query_wrapper($dblink,"UPDATE agents SET active=0 WHERE id=$agid");
        echo "bench_nok".$separator."Benchmarking didn't measure anything.";
      } else {
        // benchmark OK
        $ks=floatval($erej["keyspace"]);
        if ($state==4 || $state==5) $bprog=$btotal;

        if ($state==6) {
          // the bench ended the right way (aborted)
          // extrapolate from $benchtime to $chunktime
          $bprog=$bprog/($btotal/$ks);
          $bprog=($bprog/$config["benchtime"])*$chunktime;
          $bprog=round($bprog);
        } else if ($bprog==$btotal) {
          // the benchmark went through the whole keyspace (VERY small task - faster than actual benchmark)
          $bprog=$ks;
        } else {
          // benchmark ended in some problematic way
          $bprog=0;
        }
        if ($bprog>0 && mysqli_query_wrapper($dblink,"UPDATE assignments SET speed=0, benchmark=$bprog WHERE agent=$agid AND task=$task")) {
          echo "bench_ok".$separator.$bprog;
        } else {
          echo "bench_nok".$separator."Could not update your benchmark for this task.";
        }
      }
    } else {
        echo "bench_nok".$separator."Task does not exist or you are not assigned to it.";
    }
    break;

  case "solve":
    // upload cracked hashes to server
    $cid=intval($_GET["chunk"]);
    $prog=floatval($_GET["curku"]);
    $rprog=floatval($_GET["progress"]);
    $rtotal=floatval($_GET["total"]);
    $speed=floatval($_GET["speed"]);
    $state=intval($_GET["state"]);

    $kvery=mysqli_query_wrapper($dblink,"SELECT chunks.dispatchtime,chunks.skip,chunks.length,chunks.state,agents.id AS agid,agents.trusted,agents.os,tasks.id AS otask,tasks.statustimer,assignments.autoadjust,tasks.hashlist,tasks.chunktime,tasks.progress,tasks.keyspace,hashlists.format,assignments.task,assignments.benchmark FROM chunks JOIN agents ON chunks.agent=agents.id JOIN tasks ON chunks.task=tasks.id JOIN hashlists ON tasks.hashlist=hashlists.id LEFT JOIN assignments ON assignments.task=tasks.id AND assignments.agent=agents.id WHERE chunks.id=$cid AND agents.token='$token' AND agents.trusted>=hashlists.secret");
    if (mysqli_num_rows($kvery)==1) {
      // agent is assigned to this chunk (not necessarily task!)
      // it can be already assigned to other task, but is still computing this chunk until it realizes it
      $erej=mysqli_fetch_array($kvery,MYSQLI_ASSOC);
      $agid=$erej["agid"];
      $trusted=$erej["trusted"];
      $bench=$erej["benchmark"];
      $task=$erej["task"];
      $otask=$erej["otask"];
      $hlist=$erej["hashlist"];
      $skip=$erej["skip"];
      $length=$erej["length"];
      $cstate=$erej["state"];
      $chunktime=$erej["chunktime"];
      $statustimer=$erej["statustimer"];
      $dispatchtime=$erej["dispatchtime"];
      $format=$erej["format"];
      $taskprog=$erej["progress"];
      $keyspace=$erej["keyspace"];
      $autoadj=$erej["autoadjust"];

      // strip the offset to get the real progress
      $subtr=($skip*$rtotal)/($skip+$length);
      $rprog-=$subtr;
      $rtotal-=$subtr;
      if ($prog>0) $prog-=$skip;
      
      // workaround for hashcat overshooting its curku boundaries sometimes
      if ($state==4) $rprog=$rtotal;

      if ($rprog<=$rtotal) {
        // if progress is between chunk boundaries
        
        if ($erej["os"]==1) {
          $newline="\n";
        } else {
          $newline="\r\n";
        }
        
        // workaround for hashcat not sending correct final curku=skip+len when done with chunk
        if ($rprog==$rtotal) $prog=$length;

        if ($prog>=0 && $prog<=$length) {
        // if the curku is inside correct range
          
          if ($rprog==$rtotal) {
            // this chunk is done
            $rp=10000;
          } else {
            $rp=round(($rprog/$rtotal)*10000);
            // protection against rounding errors
            if ($rprog<$rtotal && $rp==10000) $rp--;
            if ($rprog>0 && $rp==0) $rp++;
          }
          
          // update progress inside a chunk and chunk cache
          mysqli_query_wrapper($dblink,"UPDATE chunks SET rprogress=$rp,progress=$prog,solvetime=$cas,state=$state WHERE id=$cid");
        } else {
          file_put_contents("server_solve.txt",var_export($_GET,true).var_export($_POST,true)."\n----------------------------------------\n",FILE_APPEND);
        }

        // handle superhashlist
        if ($format==3) {
          $superhash=true;
        } else {
          $superhash=false;
        }
        $hlistar=array();
        $hlistarzap=array();
        if ($superhash) {
          $kve=mysqli_query_wrapper($dblink,"SELECT hashlists.id,hashlists.format,hashlists.secret FROM superhashlists JOIN hashlists ON superhashlists.hashlist=hashlists.id WHERE superhashlists.id=$hlist");
          while($ere=mysqli_fetch_array($kve,MYSQLI_ASSOC)) {
            $format=$ere["format"];
            $hlistar[]=$ere["id"];
            if ($ere["secret"]<=$trusted) $hlistarzap[]=$ere["id"];
          }
        } else {
          $hlistar[]=$hlist;
          $hlistarzap[]=$hlist;
        }
        
        // create two lists:
        // list of all hashlists in this superhashlist
        $hlisty=implode(",",$hlistar);
        // list of those hashlists in superhashlist this agent is allowed to read
        $hlistyzap=implode(",",$hlistarzap);

        
        // reset values
        $cracked=0;
        $skipped=0;
        $errors=0;

        // process solved hashes, should there be any
        $rawdata=file_get_contents("php://input");
        if (strlen($rawdata)>0) {
          // there is some uploaded text (cracked hashes)
          $data=explode($newline,$rawdata);
          if (count($data)>1) {
            // there is more then one line
            // (even for one hash, there is $newline at the end so that makes it two lines)

            $tbls=array("hashes","hashes_binary","hashes_binary");
            $tbl=$tbls[$format];

            // create temporary table to cache cracking stats
            mysqli_query_wrapper($dblink,"CREATE TEMPORARY TABLE tmphlcracks (hashlist INT NOT NULL, cracked INT NOT NULL DEFAULT 0, zaps BIT(1) DEFAULT 0, PRIMARY KEY (hashlist))");
            mysqli_query_wrapper($dblink,"INSERT INTO tmphlcracks (hashlist) SELECT id FROM hashlists WHERE id IN ($hlisty)");
            
            function writecache() {
              // flush temporary cache to the actual tables
              global $dblink, $tbl, $crack_cas, $cid, $agid, $superhash, $hlist;
              mysqli_query_wrapper($dblink,"UPDATE tmphlcracks SET zaps=1 WHERE cracked>0");
              if ($superhash) mysqli_query_wrapper($dblink,"UPDATE hashlists SET cracked=cracked+(SELECT IFNULL(SUM(cracked),0) FROM tmphlcracks) WHERE id=$hlist");
              mysqli_query_wrapper($dblink,"UPDATE hashlists JOIN tmphlcracks ON hashlists.id=tmphlcracks.hashlist SET hashlists.cracked=hashlists.cracked+tmphlcracks.cracked");
              mysqli_query_wrapper($dblink,"INSERT IGNORE INTO zapqueue (hashlist,agent,time,chunk) SELECT hashlistusers.hashlist,hashlistusers.agent,$crack_cas,$cid FROM hashlistusers JOIN tmphlcracks ON hashlistusers.hashlist=tmphlcracks.hashlist AND tmphlcracks.zaps=1 WHERE hashlistusers.agent!=$agid");
              // increase the timer so the chunks won't timeout during the result writing
              $crack_cas=time();
              mysqli_query_wrapper($dblink,"UPDATE chunks SET cracked=cracked+(SELECT IFNULL(SUM(cracked),0) FROM tmphlcracks),solvetime=$crack_cas WHERE id=$cid");
              mysqli_query_wrapper($dblink,"UPDATE tmphlcracks SET cracked=0,zaps=0");
            }
            
            $crack_cas=$cas;
            foreach ($data as $dato) {
              // for non empty lines update solved hashes
              if ($dato=="") continue;
              $elementy=explode($separator,$dato);
              $podminka="";
              $plain="";
              switch ($format) {
                case 0:
                  // save regular password
                  $hash=mysqli_real_escape_string($dblink,$elementy[0]);
                  switch (count($elementy)) {
                    case 2:
                      // unsalted hashes
                      $salt="";
                      $plain=mysqli_real_escape_string($dblink,$elementy[1]);
                      break;
                      
                    case 3:
                      // salted hashes
                      $salt=mysqli_real_escape_string($dblink,$elementy[1]);
                      $plain=mysqli_real_escape_string($dblink,$elementy[2]);
                      break;
                  }
                  $podminka="$tbl.hash='$hash' AND $tbl.salt='$salt'";
                  break;
                  
                case 1:
                  // save cracked wpa password
                  $network=mysqli_real_escape_string($dblink,$elementy[0]);
                  $plain=mysqli_real_escape_string($dblink,$elementy[1]);
                  
                  // QUICK-FIX WPA/WPA2 strip mac address
                  if (preg_match("/.+:[0-9a-f]{12}:[0-9a-f]{12}$/", $network)===1) {
                    // TODO: extend DB model by MACs and implement detection
                    $network=substr($network,0,strlen($network)-26);
                  }
                  
                  $podminka="$tbl.essid='$network'";
                  break;
                  
                case 2:
                  // save binary password
                  $plain=mysqli_real_escape_string($dblink,$elementy[1]);
                  break;
              }
              
              // make the query
              $qu="UPDATE $tbl JOIN tmphlcracks ON tmphlcracks.hashlist=$tbl.hashlist SET $tbl.plaintext='$plain',$tbl.time=$crack_cas,$tbl.chunk=$cid,tmphlcracks.cracked=tmphlcracks.cracked+1 WHERE $tbl.hashlist IN ($hlisty) AND $tbl.plaintext IS NULL".($podminka!="" ? " AND ".$podminka : "");
              $dbqu=mysqli_query_wrapper($dblink,$qu);
              
              // check if the update went right
              if ($dbqu) {
                $affec=mysqli_affected_rows($dblink);
                if ($affec>0) {
                  $cracked++;
                } else {
                  $skipped++;
                }
              } else {
                $errors++;
              }
              
              // everytime we pass statustimer
              if (time()>=$crack_cas+$statustimer) {
                // update the cache
                writecache();
              }
            }
            writecache();
            // drop the temporary cache
            mysqli_query_wrapper($dblink,"DROP TABLE tmphlcracks");
            
          }
        }

        if ($errors==0) {
          
          if ($cstate==10) {
            
            // the chunk was manually interrupted
            mysqli_query_wrapper($dblink,"UPDATE chunks SET state=6 WHERE id=$cid");
            echo "solve_nok".$separator."Chunk was manually interrupted.";
          
          } else {
          
            // just inform the agent about the results
            echo "solve_ok".$separator.$cracked.$separator.$skipped;
            $taskdone=false;
            if ($rprog==$rtotal && $taskprog==$keyspace) {
              // chunk is done and the task has been fully dispatched
              $incq=mysqli_query_wrapper($dblink,"SELECT COUNT(1) AS incomplete FROM chunks WHERE task=$task AND rprogress<10000");
              $iner=mysqli_fetch_array($incq,MYSQLI_ASSOC);
              if ($iner["incomplete"]==0) {
                // this was the last incomplete chunk!
                $taskdone=true;
              }
            }
            
            if ($taskdone) {
              // task is fully dispatched and this last chunk is done, deprioritize it
              mysqli_query_wrapper($dblink,"UPDATE tasks SET priority=0 WHERE id=$task");
              
              // email task done
              if ($config["emailtaskdone"]=="1")
                @mail($config["emailaddr"],"Hashtopus: task finished","Your task ID $task was finished by agent $agid.");
            }

            switch ($state) {
              case 4:
                // the chunk has finished (exhausted)
                if ($length==$bench && $task==$otask && $autoadj==1 && $taskdone==false) {
                  // the chunk was originaly meant for this agent, the autoadjust is on, the agent is still at this task and the task is not done
                  $delka=$cas-$dispatchtime;
                  $newbench=($bench/$delka)*$chunktime;
                  // update the benchmark
                  mysqli_query_wrapper($dblink,"UPDATE assignments SET speed=0, benchmark=$newbench WHERE task=$task AND agent=$agid");
                }
                break;
              
              case 5:
                // the chunk has finished (cracked whole hashlist)
                // deprioritize all tasks and unassign all agents
                if ($superhash && $hlistyzap==$hlisty) {
                  if ($hlistyzap!="") $hlistyzap.=",";
                  $hlistyzap.=$hlist;
                }
                mysqli_query_wrapper($dblink,"UPDATE tasks SET priority=0 WHERE hashlist IN ($hlistyzap)");

                // email hashlist done
                if ($config["emailhldone"]=="1")
                  @mail($config["emailaddr"],"Hashtopus: hashlist cracked","Your hashlists ID $hlistyzap were cracked by agent $agid.");
                break;
                
              case 6:
                // the chunk was aborted
                mysqli_query_wrapper($dblink,"UPDATE assignments SET speed=0 WHERE task=$task AND agent=$agid");
                break;
                
              default:
                // the chunk isn't finished yet, we will send zaps
                $verif=mysqli_query_wrapper($dblink,"SELECT 1 FROM hashlists WHERE id IN ($hlistyzap) AND cracked<hashcount");
                echo $separator;
                if (mysqli_num_rows($verif)>0) {
                  // there are some hashes left uncracked in this (super)hashlist
                  if ($task==$otask) {
                    // if the agent is still assigned, update its speed
                    mysqli_query_wrapper($dblink,"UPDATE assignments SET speed=$speed WHERE agent=$agid AND task=$task");
                  }
                  
                  mysqli_query_wrapper($dblink,"START TRANSACTION");
                  switch ($format) {
                    case 0:
                      // return text zaps
                      $kvery=mysqli_query_wrapper($dblink,"SELECT hashes.hash, hashes.salt FROM hashes JOIN zapqueue ON hashes.hashlist=zapqueue.hashlist AND zapqueue.agent=$agid AND hashes.time=zapqueue.time AND hashes.chunk=zapqueue.chunk WHERE hashes.hashlist IN ($hlistyzap)");
                      $pocet=mysqli_num_rows($kvery);
                      break;
                      
                    case 1:
                      // return hccap zaps (essids)
                      $kvery=mysqli_query_wrapper($dblink,"SELECT hashes_binary.essid AS hash, '' AS salt FROM hashes_binary JOIN zapqueue ON hashes_binary.hashlist=zapqueue.hashlist AND zapqueue.agent=$agid AND hashes_binary.time=zapqueue.time AND hashes_binary.chunk=zapqueue.chunk WHERE hashes_binary.hashlist IN ($hlistyzap)");
                      $pocet=mysqli_num_rows($kvery);
                      break;
                      
                    case 2:
                      // binary hashes don't need zaps, there is just one hash
                      $pocet=0;
                  }

                  if ($pocet>0) {
                    echo "zap_ok".$separator.$pocet.$newline;
                    // list the zapped hashes
                    while ($erej=mysqli_fetch_array($kvery,MYSQLI_ASSOC)) {
                      echo $erej["hash"];
                      if ($erej["salt"]!="") echo $separator.$erej["salt"];
                      echo $newline;
                    }
                  } else {
                    echo "zap_no".$separator."0".$newline;
                  }
                  // update hashlist age for agent to this task
                  mysqli_query_wrapper($dblink,"DELETE FROM zapqueue WHERE hashlist IN ($hlistyzap) AND agent=$agid");
                  mysqli_query_wrapper($dblink,"COMMIT");
                } else {
                  // kill the cracking agent, the (super)hashlist was done
                  echo "stop";
                }
                break;
            }
          }
        } else {
          echo "solve_nok".$separator.$errors." occured when updating hashes.";
        }
      } else {
        echo "solve_nok".$separator."You submitted bad progress details.";
      }
    } else {
      echo "solve_nok".$separator."Chunk does not exist or you are not assigned to it.";
    }
    break;
    
  case "err":
    // upload hashcat output errors or messages
    $task=intval($_GET["task"]);
    $kvery=mysqli_query_wrapper($dblink,"SELECT id,os FROM agents WHERE agents.token='$token'");
    if (mysqli_num_rows($kvery)==1) {
      // agent is assigned to this task
      $erej=mysqli_fetch_array($kvery,MYSQLI_ASSOC);
      if ($erej["os"]==1) {
        $newline="\n";
      } else {
        $newline="\r\n";
      }
      $agid=$erej["id"];
      $rawdata=file_get_contents("php://input");
      $data=explode($newline,$rawdata);
      $i=0; $j=0;
      foreach ($data as $dato) {
        // for non empty lines add error to the db
        if ($dato=="") continue;
        $j++;
        //$dato=bin2hex($dato);
        $ndato=bintohex($dato);
        if (mysqli_query_wrapper($dblink,"INSERT INTO errors (agent,task,time,error) VALUES ($agid,$task,$cas,x'$ndato')")) $i++;
      }
      if ($i==$j) {
        echo "err_ok".$separator.$i;
      } else {
        echo "err_nok".$separator."Uploaded $i/$j errors.";
        file_put_contents("err_".$agid."_".$cas.".txt",$rawdata);
      }
    } else {
      echo "err_nok".$separator."Task does not exist or you are not assigned to it.";
    }
    // pause any agent activity because of error
    mysqli_query_wrapper($dblink,"UPDATE agents SET active=0 WHERE id=$agid AND ignoreerrors=0");

    // email agent error
    if ($config["emailerror"]=="1")
      @mail($config["emailaddr"],"Hashtopus: agent error","Your agent $agid just encountered a hashcat error and was paused.");
    break;
}

$lastip=$_SERVER['REMOTE_ADDR'];
mysqli_query_wrapper($dblink,"UPDATE agents SET lasttime='$cas', lastip='$lastip', lastact='$action' WHERE token='$token'");

?>
