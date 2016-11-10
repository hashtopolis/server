<?php
class API{
    private static function updateAgent($QUERY, $agent){
        global $FACTORIES;

        $agent->setLastIp(Util::getIP());
        $agent->setLastAction($QUERY['action']);
        $agent->setLastTime(time());
        $FACTORIES->getAgentFactory()->update($agent);
    }

    private static function checkValues($QUERY, $values){
        foreach($values as $value){
            if(!isset($QUERY[$value])){
                return false;
            }
        }
        return true;
    }

    public static function sendErrorResponse($action, $msg){
        $ANS = array();
        $ANS['action'] = $action;
        $ANS['response'] = "ERROR";
        $ANS['message'] = $msg;
        header("Content-Type: application/json");
        echo json_encode($ANS, true);
        die();
    }

    public static function checkToken($QUERY){
        global $FACTORIES;

        $qF = new QueryFilter("token", $QUERY['token'], "=");
        $token = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
        if($token != null){
            return true;
        }
        return false;
    }

    private static function sendResponse($RESPONSE){
        header("Content-Type: application/json");
        echo json_encode($RESPONSE, true);
        die();
    }

    public static function registerAgent($QUERY){
        global $FACTORIES, $CONFIG, $SEPARATOR;

        //check required values
        if(API::checkValues($QUERY, array('voucher', 'gpus', 'uid', 'name', 'os'))){
            API::sendErrorResponse("register", "Invalid registering query!");
        }

        $qF = new QueryFilter("voucher", $QUERY['voucher'], "=");
        $voucher = $FACTORIES::getRegVoucherFactory()->filter(array('filter' => array($qF)), true);
        if($voucher == null){
            API::sendErrorResponse("register", "Provided voucher does not exist.");
        }

        $gpu = $_POST["gpus"];
        $uid = htmlentities($_POST["uid"], false, "UTF-8");
        $name = htmlentities($_POST["name"], false, "UTF-8");
        $os = intval($_POST["os"]);

        //determine if the client has cpu only
        $cpuOnly = 1;
        foreach(explode($SEPARATOR, strtolower($gpu)) as $card){
            if((strpos($card, "amd") !== false) || (strpos($card, "ati ") !== false) || (strpos($card, "radeon") !== false) || strpos($card, "nvidia") !== false){
                $cpuOnly = 0;
            }
        }

        //create access token & save agent details
        $token = Util::randomString(10);
        $gpu = htmlentities($gpu, false, "UTF-8");
        $agent = new Agent(0, $name, $uid, $os, $gpu, "", "", $CONFIG->getVal('agenttimeout'), "", 1, 0, $token, "", 0, Util::getIP(), 0, $cpuOnly);
        $FACTORIES::getRegVoucherFactory()->delete($voucher);
        if($FACTORIES::getAgentFactory()->save($agent)){
            API::sendResponse(array("action" => "register", "response" => "SUCCESS", "token" => $token));
        }
        else{
            API::sendErrorResponse("register", "Could not register you to server.");
        }
    }

    public static function loginAgent($QUERY){
        global $FACTORIES, $CONFIG;

        if(API::checkValues($QUERY, array('token'))){
            API::sendErrorResponse("login", "Invalid login query!");
        }

        // login to master server with previously provided token
        $qF = new QueryFilter("token", $QUERY['token'], "=");
        $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
        if($agent == null){
            // token was not found
            API::sendErrorResponse("login", "Unknown token, register again!");
        }
        API::updateAgent($QUERY, $agent);
        API::sendResponse(array("action" => "login", "response" => "SUCCESS", "timeout" => $CONFIG->getVal("agenttimeout")));
    }

    public static function checkClientUpdate($QUERY){
        global $SCRIPTVERSION, $SCRIPTNAME;

        // check if provided hash is the same as script and send file contents if not
        if(API::checkValues($QUERY, array('version'))){
            API::sendErrorResponse('update', 'Version value missing!');
        }

        $version = $QUERY['version'];

        if($version != $SCRIPTVERSION){
            API::sendResponse(array('action' => 'update', 'response' => 'SUCCESS', 'version' => 'NEW', 'data' => file_get_contents(dirname(__FILE__)."/../static/$SCRIPTNAME")));
        }
        else{
            API::sendResponse(array('action' => 'update', 'response' => 'SUCCESS', 'version' => 'OK'));
        }
    }

    public static function downloadApp($QUERY){
        global $FACTORIES;

        if(API::checkValues($QUERY, array('token', 'type'))){
            API::sendErrorResponse("download", "Invalid download query!");
        }
        $qF = new QueryFilter("token", $QUERY['token'], "=");
        $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);

        // provide agent with requested download
        switch($QUERY['type']){
            case "7zr":
                // downloading 7zip
                $filename = "7zr".($agent->getOs() == 1)?".exe":"";
                header_remove("Content-Type");
                header('Content-Type: application/octet-stream');
                header("Content-Disposition: attachment; filename=\"".$filename."\"");
                echo file_get_contents("static/".$filename);
                die();
            case "hashcat":
                if(API::checkValues($QUERY, array('version'))){
                    API::sendErrorResponse("download", "Invalid download (hashcat) query!");
                }
                $oF = new OrderFilter("time", "DESC LIMIT 1");
                $hashcat = $FACTORIES::getHashcatReleaseFactory()->filter(array('order' => array($oF)), true);
                if($hashcat == null){
                    API::sendErrorResponse("download", "No Hashcat release available!");
                }

                $postfix = array("bin", "exe");
                $executable = "hashcat64".$postfix[$agent->getOs()];

                if($QUERY['version'] == $hashcat->getVersion() && (!isset($QUERY['force']) || $QUERY['force'] != '1')){
                    API::sendResponse(array("action" => 'download', 'response' => 'SUCCESS', 'version' => 'OK', 'executable' => $executable));
                }

                $url = $hashcat->getUrl();
                $files = explode("\n", str_replace(" ", "\n", $hashcat->getCommonFiles()));
                $files[] = $executable;
                $rootdir = $hashcat->getRootdir();

                $agent->setHcVersion($hashcat->getVersion());
                $FACTORIES::getAgentFactory()->update($agent);
                API::sendResponse(array('action' => 'download', 'response' => 'SUCCESS', 'version' => 'NEW', 'url' => $url, 'files' => $files, 'rootdir' => $rootdir, 'executable' => $executable));
                break;
            default:
                API::sendErrorResponse('download', "Unknown download type!");
        }
    }

    public static function agentError($QUERY){
        global $FACTORIES;

        //check required values
        if(API::checkValues($QUERY, array('token', 'task', 'message'))){
            API::sendErrorResponse("error", "Invalid error query!");
        }

        //check agent and task
        $qF = new QueryFilter("token", $QUERY['token'], "=");
        $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
        $task = $FACTORIES::getTaskFactory()->get($QUERY['task']);
        if($task == null){
            API::sendErrorResponse("error", "Invalid task!");
        }

        //check assignment
        $qF1 = new QueryFilter("agentId", $agent->getId(), "=");
        $qF2 = new QueryFilter("taskId", $task->getId(), "=");
        $assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF1, $qF2)), true);
        if($assignment == null){
            API::sendErrorResponse("error", "You are not assigned to this task!");
        }

        //save error message
        $error = new AgentError(0, $agent->getId(), $task->getId(), time(), $QUERY['message']);
        $FACTORIES::getAgentErrorFactory()->save($error);

        if($agent->getIgnoreErrors() == 0){
            //deactivate agent
            $agent->setIsActive(0);
            $FACTORIES::getAgentFactory()->update($agent);
        }
        API::sendResponse(array('action' => 'error', 'response' => 'SUCCESS'));
    }

    public static function getFile($QUERY){
        global $FACTORIES;

        //check required values
        if(API::checkValues($QUERY, array('token', 'task', 'filename'))){
            API::sendErrorResponse("file", "Invalid file query!");
        }

        // let agent download adjacent files
        $task = $FACTORIES::getTaskFactory()->get($QUERY['task']);
        if($task == null){
            API::sendErrorResponse('file', "Invalid task!");
        }

        $filename = $QUERY['filename'];
        $qF = new QueryFilter("filename", $filename, "=");
        $file = $FACTORIES::getFileFactory()->filter(array('filter' => array($qF)), true);
        if($file == null){
            API::sendErrorResponse('file', "Invalid file!");
        }

        $qF = new QueryFilter("token", $QUERY['token'], "=");
        $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);

        $qF1 = new QueryFilter("taskId", $task->getId(), "=");
        $qF2 = new QueryFilter("agentId", $agent->getId(), "=");
        $assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF1, $qF2)), true);
        if($assignment == null){
            API::sendErrorResponse('file', "Client is not assigned to this task!");
        }

        $qF1 = new QueryFilter("taskId", $task->getId(), "=");
        $qF2 = new QueryFilter("fileId", $file->getId(), "=");
        $taskFile = $FACTORIES::getTaskFileFactory()->filter(array('filter' => array($qF1, $qF2)), true);
        if($taskFile == null){
            API::sendErrorResponse('file', "This files is not used for the specified task!");
        }

        if($agent->getIsTrusted() < $file->getSecret()){
            API::sendErrorResponse('file', "You have no access to get this file!");
        }
        API::sendResponse(array('action' => 'file', 'response' => 'SUCCESS', 'url' => 'get.php?file='.$file->getId()."&token=".$agent->getToken()));
    }

    public static function getHashes($QUERY){
        global $FACTORIES;

        //check required values
        if(API::checkValues($QUERY, array('token', 'hashlist'))){
            API::sendErrorResponse("hashes", "Invalid hashes query!");
        }

        $hashlist = $FACTORIES::getHashlistFactory()->get($QUERY['hashlist']);
        if($hashlist == null){
            API::sendErrorResponse('hashes', "Invalid hashlist!");
        }

        $qF = new QueryFilter("token", $QUERY['token'], "=");
        $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
        if($agent == null){
            API::sendErrorResponse('hashes', "Invalid agent!");
        }

        $qF = new QueryFilter("agentId", $agent->getId(), "=");
        $assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF)), true);
        if($assignment == null){
            API::sendErrorResponse('hashes', "Agent is not assigned to a task!");
        }

        $task = $FACTORIES::getTaskFactory()->get($assignment->getTaskId());
        if($task == null){
            API::sendErrorResponse('hashes', "Assignment contains invalid task!");
        }

        if($task->getHashlistId() != $hashlist->getId()){
            API::sendErrorResponse('hashes', "This hashlist is not used for the assigned task!");
        }
        else if($agent->getIsTrusted() < $hashlist->getSecret()){
            API::sendErrorResponse('hashes', "You have not access to this hashlist!");
        }
        $LINEDELIM = "\n";
        if($agent->getOs() == 1){
            $LINEDELIM = "\r\n";
        }

        $hashlists = array();
        $format = $hashlist->getFormat();
        if($hashlist->getFormat() == 3){
            //we have a superhashlist
            $qF = new QueryFilter("superHashlistId", $hashlist->getId(), "=");
            $lists = $FACTORIES->getSuperHashlistHashlistFactory()->filter(array('filter' => array($qF)));
            foreach($lists as $list){
                $hl = $FACTORIES::getHashlistFactory()->get($list->getHashlistId());
                if($hl->getSecret() > $agent->getIsTrusted()){
                    continue;
                }
                $hashlists[] = $list->getHashlistId();
            }
        }
        else{
            $hashlists[] = $hashlist->getId();
        }

        if(sizeof($hashlists) == 0){
            API::sendErrorResponse('hashes', "No hashlists selected!");
        }
        $count = 0;
        switch($format){
            case 0:
                header_remove("Content-Type");
                header('Content-Type: text/plain');
                foreach($hashlists as $list){
                    $limit = 0;
                    $size = 50000;
                    do {
                        $oF = new OrderFilter("hashId", "ASC LIMIT $limit,$size");
                        $qF1 = new QueryFilter("hashlistId", $list->getId(), "=");
                        $qF2 = new QueryFilter("plaintext", "NULL", "=");
                        $current = $FACTORIES::getHashFactory()->filter(array('filter' => array($qF1, $qF2), 'order' => array($oF)));

                        $output = "";
                        $count += sizeof($current);
                        foreach($current as $entry){
                            $output += $entry->getHash();
                            if(strlen($entry->getSalt()) > 0){
                                $output += $list->getSaltSeparator().$entry->getSalt();
                            }
                            $output += $LINEDELIM;
                        }
                        echo $output;

                        $limit += $size;
                    } while (sizeof($current) > 0);
                }
                break;
            case 1:
            case 2:
                header_remove("Content-Type");
                header('Content-Type: application/octet-stream');
                foreach($hashlists as $list){
                    $qF1 = new QueryFilter("hashlistId", $list->getId(), "=");
                    $qF2 = new QueryFilter("plaintext", "NULL", "=");
                    $current = $FACTORIES::getHashBinaryFactory()->filter(array('filter' => array($qF1, $qF2)));
                    $count += sizeof($current);
                    $output = "";
                    foreach($current as $entry){
                        $output += $entry->getHash();
                    }
                    echo $output;
                }
                break;
        }

        //update that the agent has downloaded the hashlist
        foreach($hashlists as $list){
            $qF1 = new QueryFilter("agentId", $agent->getId(), "=");
            $qF2 = new QueryFilter("hashlistId", $list->getId(), "=");
            $check = $FACTORIES::getHashlistAgentFactory()->filter(array('filter' => array($qF1, $qF2)), true);
            if($check == null){
                $downloaded = new HashlistAgent(0, $list->getId(), $agent->getId());
                $FACTORIES::getHashlistAgentFactory()->save($downloaded);
            }
        }

        if($count == 0){
            API::sendErrorResponse('hashes', "No hashes are available to crack!");
        }
    }

    public static function getTask($QUERY){
        global $FACTORIES;

        $qF = new QueryFilter("token", $QUERY['token'], "=");
        $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
        if($agent == null){
            API::sendErrorResponse('task', "Invalid token!");
        }

        $qF = new QueryFilter("agentId", $agent->getId(), "=");
        $assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF)), true);



        // tell agent information about its task

        // elaborate select where first line is agent's current assigned task (should there be any)
        // and the second is the following task the agent should be assigned to once his current is completed
        // (if agent is not assigned to any task, the next assigned is in the first line - this is
        // identified by column named 'this', where 0=to be assigned, and >0=is assigned)
        $res = $DB->query("SELECT tasks.id,tasks.autoadjust AS autotask,agents.wait,tasks.attackcmd,hashlists.hashtype,hashlists.format,agents.cmdpars,tasks.statustimer,tasks.hashlist,tasks.priority,IF(tasks.hashlist=atasks.hashlist AND atasks.hashlist IS NOT NULL AND tasks.hashlist IS NOT NULL,'continue','new') AS bench,IF(chunks.sumdispatch=tasks.keyspace AND tasks.progress=tasks.keyspace AND tasks.keyspace>0,0,1) AS taskinc,IF(hashlists.cracked<hashlists.hashcount,1,0) AS hlinc,IF(atasks.id=tasks.id,agents.id,0) AS this FROM tasks JOIN hashlists ON tasks.hashlist=hashlists.id LEFT JOIN (SELECT taskfiles.task,MAX(secret) AS secret FROM taskfiles JOIN files ON taskfiles.file=files.id GROUP BY taskfiles.task) taskfiles ON taskfiles.task=tasks.id JOIN agents ON agents.token=$token AND agents.active=1 AND agents.trusted>=GREATEST(IFNULL(taskfiles.secret,0),hashlists.secret) LEFT JOIN assignments ON assignments.agent=agents.id LEFT JOIN tasks atasks ON assignments.task=atasks.id LEFT JOIN (SELECT chunks.task,SUM(chunks.length) AS sumdispatch FROM chunks JOIN tasks ON chunks.task=tasks.id WHERE chunks.progress=chunks.length OR GREATEST(chunks.solvetime,chunks.dispatchtime)>=" . (time() - $CONFIG->getVal("chunktimeout")) . " GROUP BY chunks.task) chunks ON chunks.task=tasks.id WHERE atasks.id=tasks.id OR ((tasks.progress<tasks.keyspace OR IFNULL(chunks.sumdispatch,0)<tasks.keyspace OR tasks.keyspace=0) AND tasks.priority>0 AND hashlists.cracked<hashlists.hashcount) ORDER BY this DESC, tasks.priority DESC LIMIT 2");
        $task = $res->fetch();
        if($task){
            // first line is valid
            if($task["this"] > 0){
                // this agent is assigned to this task
                // is the current task done?
                $curdone = ($task["taskinc"] == 0 || $task["hlinc"] == 0);

                // read the following task
                if($newtask = $res->fetch()){
                    $reass = true;
                    $newdone = ($newtask["taskinc"] == 0 || $newtask["hlinc"] == 0);
                }
                else{
                    // there is no other prioritized task
                    $reass = false;
                    $newdone = true;
                }

                if($reass && !$newdone){
                    // there is some other incomplete prioritized task
                    if($curdone || $newtask["priority"] > $task["priority"]){
                        // the current task is done or the next one has higher priority
                        // so reassign the agent to the next one
                        $DB->query("UPDATE assignments SET task=" . $newtask["id"] . ",benchmark=IFNULL((SELECT length FROM chunks WHERE solvetime>dispatchtime AND progress=length AND state IN (4,5) AND agent=" . $task["this"] . " AND task=" . $newtask["id"] . " ORDER BY solvetime DESC LIMIT 1),0),speed=0,autoadjust=" . $newtask["autotask"] . " WHERE agent=" . $task["this"]);
                        // but keep agressivity of the previous one
                        $task = $newtask;
                    }
                }
                else{
                    // there is nothing else to move on
                    if($curdone){
                        // the current task is done so we unassign from it
                        $DB->query("DELETE FROM assignments WHERE agent=" . $task["this"]);
                        // erase hashlist users, they will be returned on joining next task
                        if($task["format"] == 3){
                            $DB->query("DELETE FROM hashlistusers WHERE hashlist IN (SELECT hashlist FROM superhashlists WHERE id=" . $task["hashlist"] . ") AND agent=" . $task["this"]);
                            $DB->query("DELETE FROM zapqueue WHERE hashlist IN (SELECT hashlist FROM superhashlists WHERE id=" . $task["hashlist"] . ") agent=" . $task["this"]);
                        }
                        else{
                            $DB->query("DELETE FROM hashlistusers WHERE hashlist=" . $task["hashlist"] . " AND agent=" . $task["this"]);
                            $DB->query("DELETE FROM zapqueue WHERE hashlist=" . $task["hashlist"] . " AND agent=" . $task["this"]);
                        }
                        echo "task_nok" . $separator . "No more active tasks.";
                        break;
                    }
                }
            }
            else{
                // the first line is the task to be assigned to (agent is not assigned to anything)
                $DB->query("INSERT INTO assignments (agent,task,autoadjust,benchmark) SELECT agents.id," . $task["id"] . "," . $task["autotask"] . ",IFNULL(chunks.length,0) FROM agents LEFT JOIN chunks ON agents.id=chunks.agent AND chunks.solvetime>chunks.dispatchtime AND chunks.state IN (4,5) AND chunks.progress=chunks.length AND chunks.task=" . $task["id"] . " WHERE agents.token=$token ORDER BY chunks.solvetime DESC LIMIT 1");
            }

            // ok, we have something to begin with
            echo "task_ok" . $separator . $task["id"] . $separator . $task["wait"] . $separator . $task["attackcmd"] . (strlen($task["cmdpars"]) > 0 ? " " . $task["cmdpars"] : "") . " --hash-type=" . $task["hashtype"] . $separator . $task["hashlist"] . $separator . $task["bench"] . $separator . $task["statustimer"];
            // and add listing of related files
            $res = $DB->query("SELECT files.filename FROM taskfiles JOIN files ON taskfiles.file=files.id WHERE taskfiles.task=" . $task["id"]);
            while($file = $res->fetch()){
                echo $separator . $file["filename"];
            }
        }
        else{
            // there was nothing
            $DB->query("UPDATE assignments JOIN agents ON assignments.agent=agents.id AND agents.token=$token SET assignments.speed=0");
            echo "task_nok" . $separator . "No active tasks.";
        }
    }
}