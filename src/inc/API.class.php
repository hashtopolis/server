<?php

class API {
  private static function updateAgent($QUERY, $agent) {
    global $FACTORIES;
    
    $agent->setLastIp(Util::getIP());
    $agent->setLastAction($QUERY['action']);
    $agent->setLastTime(time());
    $FACTORIES->getAgentFactory()->update($agent);
  }
  
  private static function checkValues($QUERY, $values) {
    foreach ($values as $value) {
      if (!isset($QUERY[$value])) {
        return false;
      }
    }
    return true;
  }
  
  public static function sendErrorResponse($action, $msg) {
    $ANS = array();
    $ANS['action'] = $action;
    $ANS['response'] = "ERROR";
    $ANS['message'] = $msg;
    header("Content-Type: application/json");
    echo json_encode($ANS, true);
    die();
  }
  
  public static function checkToken($QUERY) {
    global $FACTORIES;
    
    $qF = new QueryFilter("token", $QUERY['token'], "=");
    $token = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
    if ($token != null) {
      return true;
    }
    return false;
  }
  
  private static function sendResponse($RESPONSE) {
    header("Content-Type: application/json");
    echo json_encode($RESPONSE, true);
    die();
  }
  
  public static function registerAgent($QUERY) {
    global $FACTORIES, $CONFIG;
    
    //check required values
    if (!API::checkValues($QUERY, array('voucher', 'gpus', 'uid', 'name', 'os'))) {
      API::sendErrorResponse("register", "Invalid registering query!");
    }
    
    $qF = new QueryFilter("voucher", $QUERY['voucher'], "=");
    $voucher = $FACTORIES::getRegVoucherFactory()->filter(array('filter' => array($qF)), true);
    if ($voucher == null) {
      API::sendErrorResponse("register", "Provided voucher does not exist.");
    }
    
    $gpu = $QUERY["gpus"];
    $uid = htmlentities($QUERY["uid"], false, "UTF-8");
    $name = htmlentities($QUERY["name"], false, "UTF-8");
    $os = intval($QUERY["os"]);
    
    //determine if the client has cpu only
    $cpuOnly = 1;
    foreach ($gpu as $card) {
      $card = strtolower($card);
      if ((strpos($card, "amd") !== false) || (strpos($card, "ati ") !== false) || (strpos($card, "radeon") !== false) || strpos($card, "nvidia") !== false) {
        $cpuOnly = 0;
      }
    }
    
    //create access token & save agent details
    $token = Util::randomString(10);
    $gpu = htmlentities(implode("\n", $gpu), false, "UTF-8");
    $agent = new Agent(0, $name, $uid, $os, $gpu, "", "", $CONFIG->getVal('agenttimeout'), "", 1, 0, $token, "register", time(), Util::getIP(), 0, $cpuOnly);
    $FACTORIES::getRegVoucherFactory()->delete($voucher);
    if ($FACTORIES::getAgentFactory()->save($agent)) {
      API::sendResponse(array("action" => "register", "response" => "SUCCESS", "token" => $token));
    }
    else {
      API::sendErrorResponse("register", "Could not register you to server.");
    }
  }
  
  public static function loginAgent($QUERY) {
    global $FACTORIES, $CONFIG;
    
    if (!API::checkValues($QUERY, array('token'))) {
      API::sendErrorResponse("login", "Invalid login query!");
    }
    
    // login to master server with previously provided token
    $qF = new QueryFilter("token", $QUERY['token'], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
    if ($agent == null) {
      // token was not found
      API::sendErrorResponse("login", "Unknown token, register again!");
    }
    API::updateAgent($QUERY, $agent);
    API::sendResponse(array("action" => "login", "response" => "SUCCESS", "timeout" => $CONFIG->getVal("agenttimeout")));
  }
  
  public static function checkClientUpdate($QUERY) {
    global $SCRIPTVERSION, $SCRIPTNAME;
    
    // check if provided hash is the same as script and send file contents if not
    if (!API::checkValues($QUERY, array('version'))) {
      API::sendErrorResponse('update', 'Version value missing!');
    }
    
    $version = $QUERY['version'];
    
    if ($version != $SCRIPTVERSION) {
      API::sendResponse(array('action' => 'update', 'response' => 'SUCCESS', 'version' => 'NEW', 'data' => file_get_contents(dirname(__FILE__) . "/../static/$SCRIPTNAME")));
    }
    else {
      API::sendResponse(array('action' => 'update', 'response' => 'SUCCESS', 'version' => 'OK'));
    }
  }
  
  public static function downloadApp($QUERY) {
    global $FACTORIES;
    
    if (!API::checkValues($QUERY, array('token', 'type'))) {
      API::sendErrorResponse("download", "Invalid download query!");
    }
    $qF = new QueryFilter("token", $QUERY['token'], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
    
    // provide agent with requested download
    switch ($QUERY['type']) {
      case "7zr":
        // downloading 7zip
        $filename = "7zr" . ($agent->getOs() == 1) ? ".exe" : "";
        header_remove("Content-Type");
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
        echo file_get_contents("static/" . $filename);
        die();
      case "hashcat":
        if (API::checkValues($QUERY, array('version'))) {
          API::sendErrorResponse("download", "Invalid download (hashcat) query!");
        }
        $oF = new OrderFilter("time", "DESC LIMIT 1");
        $hashcat = $FACTORIES::getHashcatReleaseFactory()->filter(array('order' => array($oF)), true);
        if ($hashcat == null) {
          API::sendErrorResponse("download", "No Hashcat release available!");
        }
        
        $postfix = array("bin", "exe");
        $executable = "hashcat64" . $postfix[$agent->getOs()];
        
        if ($QUERY['version'] == $hashcat->getVersion() && (!isset($QUERY['force']) || $QUERY['force'] != '1')) {
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
  
  public static function agentError($QUERY) {
    global $FACTORIES;
    
    //check required values
    if (!API::checkValues($QUERY, array('token', 'task', 'message'))) {
      API::sendErrorResponse("error", "Invalid error query!");
    }
    
    //check agent and task
    $qF = new QueryFilter("token", $QUERY['token'], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
    $task = $FACTORIES::getTaskFactory()->get($QUERY['task']);
    if ($task == null) {
      API::sendErrorResponse("error", "Invalid task!");
    }
    
    //check assignment
    $qF1 = new QueryFilter("agentId", $agent->getId(), "=");
    $qF2 = new QueryFilter("taskId", $task->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF1, $qF2)), true);
    if ($assignment == null) {
      API::sendErrorResponse("error", "You are not assigned to this task!");
    }
    
    //save error message
    $error = new AgentError(0, $agent->getId(), $task->getId(), time(), $QUERY['message']);
    $FACTORIES::getAgentErrorFactory()->save($error);
    
    if ($agent->getIgnoreErrors() == 0) {
      //deactivate agent
      $agent->setIsActive(0);
      $FACTORIES::getAgentFactory()->update($agent);
    }
    API::sendResponse(array('action' => 'error', 'response' => 'SUCCESS'));
  }
  
  public static function getFile($QUERY) {
    global $FACTORIES;
    
    //check required values
    if (!API::checkValues($QUERY, array('token', 'task', 'filename'))) {
      API::sendErrorResponse("file", "Invalid file query!");
    }
    
    // let agent download adjacent files
    $task = $FACTORIES::getTaskFactory()->get($QUERY['task']);
    if ($task == null) {
      API::sendErrorResponse('file', "Invalid task!");
    }
    
    $filename = $QUERY['filename'];
    $qF = new QueryFilter("filename", $filename, "=");
    $file = $FACTORIES::getFileFactory()->filter(array('filter' => array($qF)), true);
    if ($file == null) {
      API::sendErrorResponse('file', "Invalid file!");
    }
    
    $qF = new QueryFilter("token", $QUERY['token'], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
    
    $qF1 = new QueryFilter("taskId", $task->getId(), "=");
    $qF2 = new QueryFilter("agentId", $agent->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF1, $qF2)), true);
    if ($assignment == null) {
      API::sendErrorResponse('file', "Client is not assigned to this task!");
    }
    
    $qF1 = new QueryFilter("taskId", $task->getId(), "=");
    $qF2 = new QueryFilter("fileId", $file->getId(), "=");
    $taskFile = $FACTORIES::getTaskFileFactory()->filter(array('filter' => array($qF1, $qF2)), true);
    if ($taskFile == null) {
      API::sendErrorResponse('file', "This files is not used for the specified task!");
    }
    
    if ($agent->getIsTrusted() < $file->getSecret()) {
      API::sendErrorResponse('file', "You have no access to get this file!");
    }
    API::sendResponse(array('action' => 'file', 'response' => 'SUCCESS', 'url' => 'get.php?file=' . $file->getId() . "&token=" . $agent->getToken()));
  }
  
  public static function getHashes($QUERY) {
    global $FACTORIES;
    
    //check required values
    if (!API::checkValues($QUERY, array('token', 'hashlist'))) {
      API::sendErrorResponse("hashes", "Invalid hashes query!");
    }
    
    $hashlist = $FACTORIES::getHashlistFactory()->get($QUERY['hashlist']);
    if ($hashlist == null) {
      API::sendErrorResponse('hashes', "Invalid hashlist!");
    }
    
    $qF = new QueryFilter("token", $QUERY['token'], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
    if ($agent == null) {
      API::sendErrorResponse('hashes', "Invalid agent!");
    }
    
    $qF = new QueryFilter("agentId", $agent->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF)), true);
    if ($assignment == null) {
      API::sendErrorResponse('hashes', "Agent is not assigned to a task!");
    }
    
    $task = $FACTORIES::getTaskFactory()->get($assignment->getTaskId());
    if ($task == null) {
      API::sendErrorResponse('hashes', "Assignment contains invalid task!");
    }
    
    if ($task->getHashlistId() != $hashlist->getId()) {
      API::sendErrorResponse('hashes', "This hashlist is not used for the assigned task!");
    }
    else if ($agent->getIsTrusted() < $hashlist->getSecret()) {
      API::sendErrorResponse('hashes', "You have not access to this hashlist!");
    }
    $LINEDELIM = "\n";
    if ($agent->getOs() == 1) {
      $LINEDELIM = "\r\n";
    }
    
    $hashlists = array();
    $format = $hashlist->getFormat();
    if ($hashlist->getFormat() == 3) {
      //we have a superhashlist
      $qF = new QueryFilter("superHashlistId", $hashlist->getId(), "=");
      $lists = $FACTORIES->getSuperHashlistHashlistFactory()->filter(array('filter' => array($qF)));
      foreach ($lists as $list) {
        $hl = $FACTORIES::getHashlistFactory()->get($list->getHashlistId());
        if ($hl->getSecret() > $agent->getIsTrusted()) {
          continue;
        }
        $hashlists[] = $list->getHashlistId();
      }
    }
    else {
      $hashlists[] = $hashlist->getId();
    }
    
    if (sizeof($hashlists) == 0) {
      API::sendErrorResponse('hashes', "No hashlists selected!");
    }
    $count = 0;
    switch ($format) {
      case 0:
        header_remove("Content-Type");
        header('Content-Type: text/plain');
        foreach ($hashlists as $list) {
          $limit = 0;
          $size = 50000;
          do {
            $oF = new OrderFilter("hashId", "ASC LIMIT $limit,$size");
            $qF1 = new QueryFilter("hashlistId", $list->getId(), "=");
            $qF2 = new QueryFilter("plaintext", "NULL", "=");
            $current = $FACTORIES::getHashFactory()->filter(array('filter' => array($qF1, $qF2), 'order' => array($oF)));
            
            $output = "";
            $count += sizeof($current);
            foreach ($current as $entry) {
              $output += $entry->getHash();
              if (strlen($entry->getSalt()) > 0) {
                $output += $list->getSaltSeparator() . $entry->getSalt();
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
        foreach ($hashlists as $list) {
          $qF1 = new QueryFilter("hashlistId", $list->getId(), "=");
          $qF2 = new QueryFilter("plaintext", "NULL", "=");
          $current = $FACTORIES::getHashBinaryFactory()->filter(array('filter' => array($qF1, $qF2)));
          $count += sizeof($current);
          $output = "";
          foreach ($current as $entry) {
            $output += $entry->getHash();
          }
          echo $output;
        }
        break;
    }
    
    //update that the agent has downloaded the hashlist
    foreach ($hashlists as $list) {
      $qF1 = new QueryFilter("agentId", $agent->getId(), "=");
      $qF2 = new QueryFilter("hashlistId", $list->getId(), "=");
      $check = $FACTORIES::getHashlistAgentFactory()->filter(array('filter' => array($qF1, $qF2)), true);
      if ($check == null) {
        $downloaded = new HashlistAgent(0, $list->getId(), $agent->getId());
        $FACTORIES::getHashlistAgentFactory()->save($downloaded);
      }
    }
    
    if ($count == 0) {
      API::sendErrorResponse('hashes', "No hashes are available to crack!");
    }
  }
  
  public static function getTask($QUERY) {
    global $FACTORIES;
    
    $qF = new QueryFilter("token", $QUERY['token'], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
    if ($agent == null) {
      API::sendErrorResponse('task', "Invalid token!");
    }
    else if($agent->getIsActive() == 0){
      API::sendResponse(array('action' => 'task', 'response' => 'SUCCESS', 'task' => 'NONE'));
    }
    
    $qF = new QueryFilter("agentId", $agent->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF)), true);
    $assignedTask = null;
    if($assignment == null){
      //search which task we should assign to the agent
      $nextTask = Util::getNextTask($agent);
      $assignment = new Assignment(0, $nextTask->getId(), $agent->getId(), 0, $nextTask->getAutoadjust(), 0);
      $FACTORIES::getAssignmentFactory()->save($assignment);
      $assignedTask = $nextTask;
    }
    else{
      //check if the agent is assigned to the correct task, if not assign him the right one
      $task = $FACTORIES::getTaskFactory()->get($assignment->getTaskId());
      $finished = false;
      
      //check if the task is finished
      if($task->getKeyspace() == $task->getProgress() && $task->getKeyspace() != 0){
        //task is finished
        $task->setPriority(0);
        $FACTORIES::getTaskFactory()->update($task);
        $finished = true;
      }
      
      $highPriorityTask = Util::getNextTask($agent);
      if($highPriorityTask != null){
        //there is a more important task
        $FACTORIES::getAssignmentFactory()->delete($assignment);
        $assignment = new Assignment(0, $highPriorityTask->getId(), $agent->getId(), 0, $highPriorityTask->getAutoadjust(), 0);
        $FACTORIES::getAssignmentFactory()->save($assignment);
        $assignedTask = $highPriorityTask;
      }
      else{
        if(!$finished) {
          $assignedTask = $task;
        }
      }
    }
    
    if($assignedTask == null){
      //no task available
      API::sendResponse(array('action' => 'task', 'response' => 'SUCCESS', 'task' => 'NONE'));
    }
    
    $qF = new QueryFilter("taskId", $assignedTask->getId(), "=");
    $jF = new JoinFilter($FACTORIES::getFileFactory(), "fileId", "fileId");
    $joinedFiles = $FACTORIES::getTaskFileFactory()->filter(array('join' => $jF, 'filter' => $qF));
    $files = array();
    for($x=0;$x<sizeof($joinedFiles['File']);$x++){
      $files[] = $joinedFiles['File'][$x];
    }
  
    API::sendResponse(array(
      'action' => 'task',
      'response' => 'SUCCESS',
      'task' => $assignedTask->getId(),
      'wait' => $agent->getWait(),
      'attackcmd' => $assignedTask->getAttackCmd(),
      'cmdpars' => $agent->getCmdPars()." --hash-type=".$assignedTask->getHashTypeId(),
      'hashlist' => $assignedTask->getHashlistId(),
      'bench' => 'new', //TODO: here we should tell him new or continue depending if he was already worked on this hashlist or not
      'statustimer' => $assignedTask->getStatusTimer(),
      'files' => array($files)
      )
    );
  }
}