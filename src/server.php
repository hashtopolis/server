<?php
require_once(dirname(__FILE__) . "/inc/load.php");
set_time_limit(0);

$QUERY = json_decode(@$_POST['query'], true);
header("Content-Type: application/json");

//debug logging
//TODO: remove later
file_put_contents("query.log", Util::getIP() . "=" . $_POST['query'] . "\n", FILE_APPEND);

switch ($QUERY['action']) {
  case "register":
    API::registerAgent($QUERY);
    break;
  case "login":
    API::loginAgent($QUERY);
    break;
  case "update":
    API::checkClientUpdate($QUERY);
    break;
  case "download":
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse('download', "Invalid token!");
    }
    API::downloadApp($QUERY);
    break;
  case 'error':
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse('error', "Invalid token!");
    }
    API::agentError($QUERY);
    break;
  case 'file':
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse('file', "Invalid token!");
    }
    API::getFile($QUERY);
    break;
  case "hashes":
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse('hashes', "Invalid token!");
    }
    API::getHashes($QUERY);
    break;
  case "task":
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse('task', "Invalid token!");
    }
    API::getTask($QUERY);
    break;
  
  
  case "chunk":
    // assign a correctly sized chunk to agent
    
    // default: 1.2 (120%) this says that if desired chunk size is X and remaining keyspace is 1.2 * X then
    // it will be assigned as a whole instead of first assigning X and then 0.2 * X (which would be very small
    // and therefore very slow due to lack of GPU utilization)
    $disptolerance = 1.2;
    
    $task = intval($_GET["task"]);
    $res = $DB->query("SELECT assignments.benchmark,agents.id,tasks.chunktime,tasks.progress,IFNULL(chunks.sumdispatch,0) AS sumdispatch,tasks.keyspace FROM agents JOIN assignments ON assignments.agent=agents.id JOIN tasks ON tasks.id=assignments.task LEFT JOIN (SELECT chunks.task,SUM(chunks.length) AS sumdispatch FROM chunks JOIN tasks ON chunks.task=tasks.id WHERE chunks.progress=chunks.length OR GREATEST(chunks.solvetime,chunks.dispatchtime)>=" . (time() - $CONFIG->getVal('chunktimeout')) . " GROUP BY chunks.task) chunks ON chunks.task=tasks.id WHERE agents.active=1 AND agents.token=$token AND tasks.id=$task");
    if ($res->rowCount() == 1) {
      $line = $res->fetch();
      $chunktime = $line["chunktime"];
      $bench = floatval($line["benchmark"]);
      $agid = $line["id"];
      if ($line["keyspace"] > 0) {
        // we know the keyspace already
        if ($line["progress"] < $line["keyspace"] || $line["sumdispatch"] < $line["keyspace"]) {
          // there are either some uncomplete chunks among fully dispatched chunk
          // or it was not fully dispatched yet
          if ($bench > 0) {
            // valid agent benchmark
            $DB->exec("START TRANSACTION");
            $res = $DB->query("SELECT chunks.id,chunks.length,chunks.skip,chunks.progress,chunks.agent,chunks.dispatchtime FROM chunks JOIN tasks ON chunks.task=tasks.id WHERE chunks.task=$task AND chunks.progress<chunks.length AND ((GREATEST(chunks.dispatchtime,chunks.solvetime)<" . (time() - $CONFIG->getVal('chunktimeout')) . " AND (chunks.agent!=$agid OR chunks.agent IS NULL)) OR (chunks.agent=$agid) OR (chunks.state=6) OR (chunks.state=10)) ORDER BY chunks.skip ASC LIMIT 1");
            $createnew = false;
            $cid = -1;
            $line = $res->fetch();
            if ($line) {
              // there is an unfinished chunk for too long
              $ocid = $line["id"];
              $oagid = $line["agent"];
              if ($oagid == "") {
                $oagid = "NULL";
              }
              $skip = $line["skip"];
              $delka = $line["length"];
              $prog = $line["progress"];
              $dpt = $line["dispatchtime"];
              
              // move on by checkpoint
              $skip += $prog;
              $delka -= $prog;
              
              if ($delka > $bench * $disptolerance && $oagid != $agid) {
                // if the remains are bigger than curent agent's benchmark, we assign the biggest possible part and create new chunk from the rest
                // this is the remaining ending part (the new chunk will be cut from starting point)
                $nskip = $skip + $bench;
                $ndelka = $delka - $bench;
                $DB->query("INSERT INTO chunks (task,skip,length,agent,dispatchtime,state) VALUES ($task,$nskip,$ndelka,$oagid,$dpt,9)");
                // and this is the length of the part that's being redispatched
                $delka = $bench;
              }
              
              if ($prog == 0) {
                // the remains of the incomplete chunk are in fact the whole chunk - there was no progress
                // we will transfer it to the new agent
                $DB->query("UPDATE chunks SET agent=$agid,length=$delka,rprogress=0,dispatchtime=" . time() . ",solvetime=0,state=0 WHERE id=$ocid");
                $cid = $ocid;
              }
              else {
                // some of the chunk was complete, cut the complete part to standalone finished chunk
                $DB->query("UPDATE chunks SET length=progress,rprogress=10000,state=9 WHERE id=$ocid");
                // and set indicator to create a new one
                $createnew = true;
              }
            }
            else {
              // all chunks are OK, cut a new one
              $res = $DB->query("SELECT progress,keyspace FROM tasks WHERE id=$task");
              $line = $res->fetch();
              $progress = $line["progress"];
              $keyspac = $line["keyspace"];
              
              $remains = $keyspac - $progress;
              
              if ($remains > 0) {
                // calculate length of next chunk (either benchmark or whole remaining keyspace, whichever is smaller)
                $delka = min($remains, $bench);
                // but if the proposed length is in the tolerance of what is remaining, just take it all
                if ($remains / $delka <= $disptolerance) {
                  $delka = $remains;
                }
                $nprogres = $progress + $delka;
                $DB->query("UPDATE tasks SET progress=$nprogres WHERE id=$task");
                // set indicator to create a new one
                $createnew = true;
                $skip = $progress;
              }
            }
            
            if ($createnew == true) {
              // now create a new chunk for the client
              $createnew = $DB->query("INSERT INTO chunks (task,skip,length,agent,dispatchtime) VALUES ($task,$skip,$delka,$agid," . time() . ")");
              if ($createnew && $createnew->rowCount() == 1) {
                // new chunk created, set the id
                $cid = $DB->lastInsertId();
              }
            }
            
            if ($cid > 0) {
              // the chunk creation query executed correctly or the chunk was reassigned
              $DB->exec("COMMIT");
              echo "chunk_ok" . $separator . $cid . $separator . $skip . $separator . $delka;
            }
            else {
              $DB->exec("ROLLBACK");
              echo "chunk_nok" . $separator . "Could not request a chunk.";
            }
          }
          else {
            // benchmark is not set or invalid, request it
            echo "bench_req" . $separator . $CONFIG->getVal('benchtime');
          }
        }
        else {
          // the task is fully dispatched into chunks
          echo "chunk_nok" . $separator . "The task has already been fully dispatched.";
        }
      }
      else {
        // the task doesn't know its keyspace yet
        echo "keyspace_req" . $separator;
      }
    }
    else {
      echo "chunk_nok" . $separator . "Task does not exist or you are not assigned to it.";
    }
    break;
  case "keyspace":
    // agent submits keyspace size for this task
    $task = intval($_GET["task"]);
    $ks = floatval($_GET["keyspace"]);
    $res = $DB->query("SELECT tasks.keyspace FROM assignments JOIN tasks ON tasks.id=assignments.task JOIN agents ON agents.id=assignments.agent WHERE agents.token=$token AND tasks.id=$task");
    if ($res->rowCount() == 1) {
      $line = $res->fetch();
      if ($line["keyspace"] == 0) {
        // if the keyspace is still unknown
        if ($ks > 0) {
          // its properly measured
          if ($DB->query("UPDATE tasks SET keyspace=$ks WHERE id=$task")) {
            echo "keyspace_ok";
          }
          else {
            echo "keyspace_nok" . $separator . "Could not set keyspace for this task.";
          }
        }
        else {
          // it came out to zero
          echo "keyspace_nok" . $separator . "You returned zero result.";
        }
      }
      else {
        // its already defined - pretend it's ok
        echo "keyspace_ok";
      }
    }
    else {
      echo "keyspace_nok" . $separator . "Task does not exist or you are not assigned to it.";
    }
    break;
  case "bench":
    // agent submits benchmark for task
    $task = intval($_GET["task"]);
    $bprog = floatval($_GET["progress"]);
    $btotal = floatval($_GET["total"]);
    $state = intval($_GET["state"]);
    $res = $DB->query("SELECT tasks.keyspace,agents.id,tasks.chunktime FROM assignments JOIN tasks ON tasks.id=assignments.task JOIN agents ON agents.id=assignments.agent WHERE agents.token=$token AND tasks.id=$task");
    if ($res->rowCount() == 1) {
      $line = $res->fetch();
      $agid = $line["id"];
      $chunktime = $line["chunktime"];
      if ($bprog <= 0) {
        // could not benchmark - pause agents assignment until further resolved
        $DB->query("UPDATE agents SET active=0 WHERE id=$agid");
        echo "bench_nok" . $separator . "Benchmarking didn't measure anything.";
      }
      else {
        // benchmark OK
        $ks = floatval($line["keyspace"]);
        if ($state == 4 || $state == 5) {
          $bprog = $btotal;
        }
        
        if ($state == 6) {
          // the bench ended the right way (aborted)
          // extrapolate from $benchtime to $chunktime
          $bprog = $bprog / ($btotal / $ks);
          $bprog = ($bprog / $CONFIG->getVal("benchtime")) * $chunktime;
          $bprog = round($bprog);
        }
        else if ($bprog == $btotal) {
          // the benchmark went through the whole keyspace (VERY small task - faster than actual benchmark)
          $bprog = $ks;
        }
        else {
          // benchmark ended in some problematic way
          $bprog = 0;
        }
        if ($bprog > 0 && $DB->query("UPDATE assignments SET speed=0, benchmark=$bprog WHERE agent=$agid AND task=$task")) {
          echo "bench_ok" . $separator . $bprog;
        }
        else {
          echo "bench_nok" . $separator . "Could not update your benchmark for this task.";
        }
      }
    }
    else {
      echo "bench_nok" . $separator . "Task does not exist or you are not assigned to it.";
    }
    break;
  case "solve":
    // upload cracked hashes to server
    $cid = intval($_GET["chunk"]);
    $prog = floatval($_GET["curku"]);
    $rprog = floatval($_GET["progress"]);
    $rtotal = floatval($_GET["total"]);
    $speed = floatval($_GET["speed"]);
    $state = intval($_GET["state"]);
    
    $res = $DB->query("SELECT chunks.dispatchtime,chunks.skip,chunks.length,chunks.state,agents.id AS agid,agents.trusted,agents.os,tasks.id AS otask,tasks.statustimer,assignments.autoadjust,tasks.hashlist,tasks.chunktime,tasks.progress,tasks.keyspace,hashlists.format,assignments.task,assignments.benchmark FROM chunks JOIN agents ON chunks.agent=agents.id JOIN tasks ON chunks.task=tasks.id JOIN hashlists ON tasks.hashlist=hashlists.id LEFT JOIN assignments ON assignments.task=tasks.id AND assignments.agent=agents.id WHERE chunks.id=$cid AND agents.token=$token AND agents.trusted>=hashlists.secret");
    if ($res->rowCount() == 1) {
      // agent is assigned to this chunk (not necessarily task!)
      // it can be already assigned to other task, but is still computing this chunk until it realizes it
      $line = $res->fetch();
      $agid = $line["agid"];
      $trusted = $line["trusted"];
      $bench = $line["benchmark"];
      $task = $line["task"];
      $otask = $line["otask"];
      $hlist = $line["hashlist"];
      $skip = $line["skip"];
      $length = $line["length"];
      $cstate = $line["state"];
      $chunktime = $line["chunktime"];
      $statustimer = $line["statustimer"];
      $dispatchtime = $line["dispatchtime"];
      $format = $line["format"];
      $taskprog = $line["progress"];
      $keyspace = $line["keyspace"];
      $autoadj = $line["autoadjust"];
      
      // strip the offset to get the real progress
      $subtr = ($skip * $rtotal) / ($skip + $length);
      $rprog -= $subtr;
      $rtotal -= $subtr;
      if ($prog > 0) {
        $prog -= $skip;
      }
      
      // workaround for hashcat overshooting its curku boundaries sometimes
      if ($state == 4) {
        $rprog = $rtotal;
      }
      
      if ($rprog <= $rtotal) {
        // if progress is between chunk boundaries
        if ($line["os"] == 1) {
          $newline = "\n";
        }
        else {
          $newline = "\r\n";
        }
        
        // workaround for hashcat not sending correct final curku=skip+len when done with chunk
        if ($rprog == $rtotal) {
          $prog = $length;
        }
        
        if ($prog >= 0 && $prog <= $length) {
          // if the curku is inside correct range
          if ($rprog == $rtotal) {
            // this chunk is done
            $rp = 10000;
          }
          else {
            $rp = round(($rprog / $rtotal) * 10000);
            // protection against rounding errors
            if ($rprog < $rtotal && $rp == 10000) {
              $rp--;
            }
            if ($rprog > 0 && $rp == 0) {
              $rp++;
            }
          }
          // update progress inside a chunk and chunk cache
          $DB->query("UPDATE chunks SET rprogress=$rp,progress=$prog,solvetime=" . time() . ",state=$state WHERE id=$cid");
        }
        else {
          file_put_contents("server_solve.txt", var_export($_GET, true) . var_export($_POST, true) . "\n----------------------------------------\n", FILE_APPEND);
        }
        // handle superhashlist
        if ($format == 3) {
          $superhash = true;
        }
        else {
          $superhash = false;
        }
        $hlistar = array();
        $hlistarzap = array();
        if ($superhash) {
          $res = $DB->query("SELECT hashlists.id,hashlists.format,hashlists.secret FROM superhashlists JOIN hashlists ON superhashlists.hashlist=hashlists.id WHERE superhashlists.id=$hlist");
          while ($line = $res->fetch()) {
            $format = $line["format"];
            $hlistar[] = $line["id"];
            if ($line["secret"] <= $trusted) {
              $hlistarzap[] = $line["id"];
            }
          }
        }
        else {
          $hlistar[] = $hlist;
          $hlistarzap[] = $hlist;
        }
        
        // create two lists:
        // list of all hashlists in this superhashlist
        $hlisty = implode(",", $hlistar);
        // list of those hashlists in superhashlist this agent is allowed to read
        $hlistyzap = implode(",", $hlistarzap);
        
        
        // reset values
        $cracked = 0;
        $skipped = 0;
        $errors = 0;
        
        // process solved hashes, should there be any
        $rawdata = file_get_contents("php://input");
        if (strlen($rawdata) > 0) {
          // there is some uploaded text (cracked hashes)
          $data = explode($newline, $rawdata);
          if (count($data) > 1) {
            // there is more then one line
            // (even for one hash, there is $newline at the end so that makes it two lines)
            $tbls = array(
              "hashes",
              "hashes_binary",
              "hashes_binary"
            );
            $tbl = $tbls[$format];
            
            // create temporary table to cache cracking stats
            $DB->query("CREATE TEMPORARY TABLE tmphlcracks (hashlist INT NOT NULL, cracked INT NOT NULL DEFAULT 0, zaps BIT(1) DEFAULT 0, PRIMARY KEY (hashlist))");
            $DB->query("INSERT INTO tmphlcracks (hashlist) SELECT id FROM hashlists WHERE id IN ($hlisty)");
            
            $crack_cas = time();
            foreach ($data as $dato) {
              // for non empty lines update solved hashes
              if ($dato == "") {
                continue;
              }
              $elementy = explode($separator, $dato);
              $podminka = "";
              $plain = "";
              switch ($format) {
                case 0:
                  // save regular password
                  $hash = substr($DB->quote($elementy[0]), 1, -1);
                  switch (count($elementy)) {
                    case 2:
                      // unsalted hashes
                      $salt = "";
                      $plain = substr($DB->quote($elementy[1]), 1, -1);
                      break;
                    case 3:
                      // salted hashes
                      $salt = substr($DB->quote($elementy[1]), 1, -1);
                      $plain = substr($DB->quote($elementy[2]), 1, -1);
                      file_put_contents("salt_log.txt", "$dato\n$hash###$salt###$plain\n", FILE_APPEND);
                      break;
                  }
                  $podminka = "$tbl.hash='$hash' AND $tbl.salt='$salt'";
                  break;
                case 1:
                  // save cracked wpa password
                  $network = substr($DB->quote($elementy[0]), 1, -1);
                  $plain = substr($DB->quote($elementy[1]), 1, -1);
                  // QUICK-FIX WPA/WPA2 strip mac address
                  if (preg_match("/.+:[0-9a-f]{12}:[0-9a-f]{12}$/", $network) === 1) {
                    // TODO: extend DB model by MACs and implement detection
                    $network = substr($network, 0, strlen($network) - 26);
                  }
                  $podminka = "$tbl.essid='$network'";
                  break;
                case 2:
                  // save binary password
                  $plain = substr($DB->quote($elementy[1]), 1, -1);
                  break;
              }
              
              // make the query
              $qu = "UPDATE $tbl JOIN tmphlcracks ON tmphlcracks.hashlist=$tbl.hashlist SET $tbl.plaintext='$plain',$tbl.time=$crack_cas,$tbl.chunk=$cid,tmphlcracks.cracked=tmphlcracks.cracked+1 WHERE $tbl.hashlist IN ($hlisty) AND $tbl.plaintext IS NULL" . ($podminka != "" ? " AND " . $podminka : "");
              $res = $DB->query($qu);
              
              // check if the update went right
              if ($res) {
                $affec = $res->rowCount();
                if ($affec > 0) {
                  $cracked++;
                }
                else {
                  $skipped++;
                }
              }
              else {
                $errors++;
              }
              
              // everytime we pass statustimer
              if (time() >= $crack_cas + $statustimer) {
                // update the cache
                Util::writecache();
              }
            }
            Util::writecache();
            // drop the temporary cache
            $DB->query("DROP TABLE tmphlcracks");
          }
        }
        
        if ($errors == 0) {
          if ($cstate == 10) {
            // the chunk was manually interrupted
            $DB->query("UPDATE chunks SET state=6 WHERE id=$cid");
            echo "solve_nok" . $separator . "Chunk was manually interrupted.";
          }
          else {
            // just inform the agent about the results
            echo "solve_ok" . $separator . $cracked . $separator . $skipped;
            $taskdone = false;
            if ($rprog == $rtotal && $taskprog == $keyspace) {
              // chunk is done and the task has been fully dispatched
              $res = $DB->query("SELECT COUNT(1) AS incomplete FROM chunks WHERE task=$task AND rprogress<10000");
              $line = $res->fetch();
              if ($line["incomplete"] == 0) {
                // this was the last incomplete chunk!
                $taskdone = true;
              }
            }
            
            if ($taskdone) {
              // task is fully dispatched and this last chunk is done, deprioritize it
              $DB->query("UPDATE tasks SET priority=0 WHERE id=$task");
              
              // email task done
              if ($CONFIG->getVal("emailtaskdone") == "1") {
                @mail($CONFIG->getVal("emailaddr"), "Hashtopus: task finished", "Your task ID $task was finished by agent $agid.");
              }
            }
            
            switch ($state) {
              case 4:
                // the chunk has finished (exhausted)
                if ($length == $bench && $task == $otask && $autoadj == 1 && $taskdone == false) {
                  // the chunk was originaly meant for this agent, the autoadjust is on, the agent is still at this task and the task is not done
                  $delka = time() - $dispatchtime;
                  $newbench = ($bench / $delka) * $chunktime;
                  // update the benchmark
                  $DB->query("UPDATE assignments SET speed=0, benchmark=$newbench WHERE task=$task AND agent=$agid");
                }
                break;
              case 5:
                // the chunk has finished (cracked whole hashlist)
                // deprioritize all tasks and unassign all agents
                if ($superhash && $hlistyzap == $hlisty) {
                  if ($hlistyzap != "") {
                    $hlistyzap .= ",";
                  }
                  $hlistyzap .= $hlist;
                }
                $DB->query("UPDATE tasks SET priority=0 WHERE hashlist IN ($hlistyzap)");
                
                // email hashlist done
                if ($CONFIG->getVal("emailhldone") == "1") {
                  @mail($CONFIG->getVal("emailaddr"), "Hashtopus: hashlist cracked", "Your hashlists ID $hlistyzap were cracked by agent $agid.");
                }
                break;
              case 6:
                // the chunk was aborted
                $DB->query("UPDATE assignments SET speed=0 WHERE task=$task AND agent=$agid");
                break;
              default:
                // the chunk isn't finished yet, we will send zaps
                $res = $DB->query("SELECT 1 FROM hashlists WHERE id IN ($hlistyzap) AND cracked<hashcount");
                echo $separator;
                if ($res->rowCount() > 0) {
                  // there are some hashes left uncracked in this (super)hashlist
                  if ($task == $otask) {
                    // if the agent is still assigned, update its speed
                    $DB->query("UPDATE assignments SET speed=$speed WHERE agent=$agid AND task=$task");
                  }
                  
                  $DB->query("START TRANSACTION");
                  switch ($format) {
                    case 0:
                      // return text zaps
                      $res = $DB->query("SELECT hashes.hash, hashes.salt FROM hashes JOIN zapqueue ON hashes.hashlist=zapqueue.hashlist AND zapqueue.agent=$agid AND hashes.time=zapqueue.time AND hashes.chunk=zapqueue.chunk WHERE hashes.hashlist IN ($hlistyzap)");
                      $pocet = $res->rowCount();
                      break;
                    case 1:
                      // return hccap zaps (essids)
                      $res = $DB->query("SELECT hashes_binary.essid AS hash, '' AS salt FROM hashes_binary JOIN zapqueue ON hashes_binary.hashlist=zapqueue.hashlist AND zapqueue.agent=$agid AND hashes_binary.time=zapqueue.time AND hashes_binary.chunk=zapqueue.chunk WHERE hashes_binary.hashlist IN ($hlistyzap)");
                      $pocet = $res->rowCount();
                      break;
                    case 2:
                      // binary hashes don't need zaps, there is just one hash
                      $pocet = 0;
                  }
                  
                  if ($pocet > 0) {
                    echo "zap_ok" . $separator . $pocet . $newline;
                    // list the zapped hashes
                    while ($line = $res->fetch()) {
                      echo $line["hash"];
                      if ($line["salt"] != "") {
                        echo $separator . $line["salt"];
                      }
                      echo $newline;
                    }
                  }
                  else {
                    echo "zap_no" . $separator . "0" . $newline;
                  }
                  // update hashlist age for agent to this task
                  $DB->query("DELETE FROM zapqueue WHERE hashlist IN ($hlistyzap) AND agent=$agid");
                  $DB->query("COMMIT");
                }
                else {
                  // kill the cracking agent, the (super)hashlist was done
                  echo "stop";
                }
                break;
            }
          }
        }
        else {
          echo "solve_nok" . $separator . $errors . " occured when updating hashes.";
        }
      }
      else {
        echo "solve_nok" . $separator . "You submitted bad progress details.";
      }
    }
    else {
      echo "solve_nok" . $separator . "Chunk does not exist or you are not assigned to it.";
    }
    break;
  default:
    die("Invalid query!");
}

?>
