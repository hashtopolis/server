<?php

use DBA\Agent;
use DBA\AgentError;
use DBA\Assignment;
use DBA\Chunk;
use DBA\ComparisonFilter;
use DBA\ContainFilter;
use DBA\HashlistAgent;
use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;

class API {
  /**
   * @param $QUERY
   * @param $agent \DBA\Agent
   */
  private static function updateAgent($QUERY, $agent) {
    global $FACTORIES;
    
    $agent->setLastIp(Util::getIP());
    $agent->setLastAct($QUERY[PQuery::ACTION]);
    $agent->setLastTime(time());
    $FACTORIES->getAgentFactory()->update($agent);
  }
  
  public static function setBenchmark($QUERY) {
    /** @var DataSet $CONFIG */
    global $FACTORIES, $CONFIG;
  
    if (!PQueryBenchmark::isValid($QUERY)) {
      API::sendErrorResponse(PActions::BENCHMARK, "Invalid benchmark query!");
    }
    
    // agent submits benchmark for task
    $task = $FACTORIES::getTaskFactory()->get($QUERY[PQueryBenchmark::TASK_ID]);
    if ($task == null) {
      API::sendErrorResponse(PActions::BENCHMARK, "Invalid task ID!");
    }
    $qF = new QueryFilter("token", $QUERY[PQueryBenchmark::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => $qF), true);
    $qF1 = new QueryFilter("agentId", $agent->getId(), "=");
    $qF2 = new QueryFilter("taskId", $task->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF1, $qF2)), true);
    if ($assignment == null) {
      API::sendErrorResponse(PActions::BENCHMARK, "You are not assigned to this task!");
    }
    
    $type = $QUERY[PQueryBenchmark::TYPE];
    $benchmark = $QUERY[PQueryBenchmark::RESULT];
    
    switch($type){
      case PValuesBenchmarkType::SPEED_TEST:
        $split = explode(":", $benchmark);
        if(sizeof($split) != 2 || !is_numeric($split[0]) || !is_numeric($split[1]) || $split[0] <=0 || $split[1] <= 0){
          $agent->setIsActive(0);
          $FACTORIES::getAgentFactory()->update($agent);
          API::sendErrorResponse(PActions::BENCHMARK, "Invalid benchmark result!");
        }
        break;
      case PValuesBenchmarkType::RUN_TIME:
        if(!is_numeric($benchmark) || $benchmark <= 0){
          $agent->setIsActive(0);
          $FACTORIES::getAgentFactory()->update($agent);
          API::sendErrorResponse(PActions::BENCHMARK, "Invalid benchmark result!");
        }
        // normalize time of the benchmark to 100 seconds
        $benchmark = floor($benchmark/$CONFIG->getVal('benchtime')*100);
        break;
      default:
        $agent->setIsActive(0);
        $FACTORIES::getAgentFactory()->update($agent);
        API::sendErrorResponse(PActions::BENCHMARK, "Invalid benchmark type!");
    }
    
    $assignment->setBenchmark($benchmark);
    $FACTORIES::getAssignmentFactory()->update($assignment);
    API::sendResponse(array(PQueryBenchmark::ACTION => PActions::BENCHMARK, "response" => "SUCCESS", "benchmark" => "OK"));
  }
  
  public static function setKeyspace($QUERY) {
    global $FACTORIES;
  
    if (!PQueryKeyspace::isValid($QUERY)) {
      API::sendErrorResponse(PActions::KEYSPACE, "Invalid keyspace query!");
    }
    
    // agent submits keyspace size for this task
    $keyspace = intval($QUERY[PQueryKeyspace::KEYSPACE]);
    $task = $FACTORIES::getTaskFactory()->get($QUERY[PQueryKeyspace::TASK_ID]);
    if ($task == null) {
      API::sendErrorResponse(PActions::KEYSPACE, "Invalid task ID!");
    }
    $qF = new QueryFilter("token", $QUERY[PQueryKeyspace::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => $qF), true);
    $qF1 = new QueryFilter("agentId", $agent->getId(), "=");
    $qF2 = new QueryFilter("taskId", $task->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF1, $qF2)), true);
    if ($assignment == null) {
      API::sendErrorResponse(PActions::KEYSPACE, "You are not assigned to this task!");
    }
    
    if ($task->getKeyspace() == 0) {
      // keyspace is still required
      $task->setKeyspace($keyspace);
      $FACTORIES::getTaskFactory()->update($task);
    }
    API::sendResponse(array(PQueryKeyspace::ACTION => PActions::KEYSPACE, "response" => "SUCCESS", "keyspace" => "OK"));
  }
  
  /**
   * @param $chunk \DBA\Chunk
   */
  private static function sendChunk($chunk) {
    global $FACTORIES;
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
    API::sendResponse(array(PQuery::ACTION => PActions::TASK, "response" => "SUCCESS", "chunk" => $chunk->getId(), "skip" => $chunk->getSkip(), "length" => $chunk->getLength()));
  }
  
  /**
   * @param $benchmark
   * @param int $tolerance
   * @return int
   */
  private static function calculateChunkSize($benchmark, $tolerance = 1){
    /** @var DataSet $CONFIG */
    global $CONFIG;
    
    $chunkTime = $CONFIG->getVal('chunktime');
    if(strpos($benchmark, ":") === false){
      // old benchmarking method
      $size = floor($benchmark*$chunkTime/100);
    }
    else {
      // new benchmarking method
      $benchmark = explode(":", $benchmark);
      if(sizeof($benchmark) != 2 || $benchmark[0] <= 0 || $benchmark[1] <= 0){
        return 0;
      }
  
      //TODO: check if time adjustments are needed
      $benchmark[1] *= 2/3;
  
      $factor = $chunkTime*1000/$benchmark[1];
      if($factor <= 0.25){
        $benchmark[0] /= 4;
      }
      else if($factor <= 0.5){
        $benchmark[0] /= 2;
      }
      else{
        $factor = floor($factor);
      }
      if($factor == 0){
        $factor = 1;
      }
      $size = $benchmark[0]*$factor;
    }
    
    return $size*$tolerance;
  }
  
  /**
   * @param $chunk \DBA\Chunk
   * @param $agent \DBA\Agent
   * @param $task \DBA\Task
   * @param $assignment \DBA\Assignment
   */
  private static function handleExistingChunk($chunk, $agent, $task, $assignment){
    global $FACTORIES;
    
    $disptolerance = 1.2; //TODO: add this to config
    
    $agentChunkSize = API::calculateChunkSize($assignment->getBenchmark(), 1);
    $agentChunkSizeMax = API::calculateChunkSize($assignment->getBenchmark(), $disptolerance);
    if($chunk->getProgress() == 0 && $agentChunkSizeMax > $chunk->getLength()){
      //chunk has not started yet
      $chunk->setRprogress(0);
      $chunk->setDispatchTime(time());
      $chunk->setSolveTime(0);
      $chunk->setState(DHashcatStatus::INIT);
      $chunk->setAgentId($agent->getId());
      $FACTORIES::getChunkFactory()->update($chunk);
      API::sendChunk($chunk);
    }
    else if($chunk->getProgress() == 0){
      //split chunk into two parts
      $firstPart = $chunk;
      $firstPart->setLength($agentChunkSize);
      $firstPart->setAgentId($agent->getId());
      $firstPart->setDispatchTime(time());
      $firstPart->setSolveTime(0);
      $firstPart->setState(DHashcatStatus::INIT);
      $firstPart->setRprogress(0);
      $FACTORIES::getChunkFactory()->update($firstPart);
      $secondPart = new Chunk(0, $task->getId(), $firstPart->getSkip() + $firstPart->getLength(), $chunk->getLength() - $firstPart->getLength(), null, 0, 0, 0, 0, DHashcatStatus::INIT, 0, 0);
      $FACTORIES::getChunkFactory()->save($secondPart);
      API::sendChunk($firstPart);
    }
    else{
      $chunk->setLength($chunk->getProgress());
      $chunk->setRprogress(10000);
      $chunk->setState(DHashcatStatus::ABORTED_CHECKPOINT);
      $FACTORIES::getChunkFactory()->update($chunk);
      API::createNewChunk($agent, $task, $assignment);
    }
  }
  
  /**
   * @param $agent \DBA\Agent
   * @param $task \DBA\Task
   * @param $assignment \DBA\Assignment
   */
  private static function createNewChunk($agent, $task, $assignment){
    global $FACTORIES;
    
    $disptolerance = 1.2; //TODO: add to config
    
    $remaining = $task->getKeyspace() - $task->getProgress();
    $agentChunkSize = API::calculateChunkSize($assignment->getBenchmark(), 1);
    $start = $task->getProgress();
    $length = $agentChunkSize;
    if($remaining/$length <= $disptolerance){
      $length = $remaining;
    }
    $newProgress = $task->getProgress() + $length;
    $task->setProgress($newProgress);
    $FACTORIES::getTaskFactory()->update($task);
    $chunk = new Chunk(0, $task->getId(), $start, $length, $agent->getId(), time(), 0, 0, DHashcatStatus::INIT, 0, 0, 0);
    $FACTORIES::getChunkFactory()->save($chunk);
    API::sendChunk($chunk);
  }

  
  public static function getChunk($QUERY) {
    /** @var DataSet $CONFIG */
    global $FACTORIES, $CONFIG;
  
    if (!PQueryChunk::isValid($QUERY)) {
      API::sendErrorResponse(PActions::CHUNK, "Invalid chunk query!");
    }
    
    $task = $FACTORIES::getTaskFactory()->get($QUERY[PQueryChunk::TASK_ID]);
    if ($task == null) {
      API::sendErrorResponse(PActions::CHUNK, "Invalid task ID!");
    }
    
    //check if agent is assigned
    $qF = new QueryFilter("token", $QUERY[PQueryChunk::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => $qF), true);
    $qF1 = new QueryFilter("agentId", $agent->getId(), "=");
    $qF2 = new QueryFilter("taskId", $task->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF1, $qF2)), true);
    if ($assignment == null) {
      API::sendErrorResponse(PActions::CHUNK, "You are not assigned to this task!");
    }
    else if ($task->getKeyspace() == 0) {
      API::sendResponse(array(PQuery::ACTION => PActions::TASK, "response" => "SUCCESS", "chunk" => "keyspace_required"));
    }
    else if ($assignment->getBenchmark() == 0) {
      API::sendResponse(array(PQuery::ACTION => PActions::TASK, "response" => "SUCCESS", "chunk" => "benchmark"));
    }
  
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
    $qF = new QueryFilter("taskId", $task->getId(), "=");
    $chunks = $FACTORIES::getChunkFactory()->filter(array('filter' => $qF));
    $dispatched = 0;
    foreach ($chunks as $chunk) {
      if($chunk->getAgentId() == $agent->getId() && $chunk->getLength() != $chunk->getProgress()){
        //this is the case when the agent got interrupted in some way, so he can just continue with his chunk he was working on
        $chunk->setDispatchTime(time()); //reset time count to make sure the spent time gets calculated correctly
        $chunk->setSolveTime(0);
        $FACTORIES::getChunkFactory()->update($chunk);
        API::sendChunk($chunk);
      }
      $dispatched += $chunk->getLength();
    }
    if ($task->getProgress() == $task->getKeyspace() || $task->getKeyspace() == $dispatched) {
      API::sendResponse(array(PQuery::ACTION => PActions::TASK, "response" => "SUCCESS", "chunk" => "fully_dispatched"));
    }
  
    $qF1 = new ComparisonFilter("progress", "length", "<");
    $qF2 = new QueryFilter("taskId", $task->getId(), "=");
    $oF = new OrderFilter("skip", "ASC");
    $chunks = $FACTORIES::getChunkFactory()->filter(array('filter' => array($qF1, $qF2), 'order' => $oF));
    foreach($chunks as $chunk){
      if($chunk->getAgentId() == $agent->getId()){
        API::sendChunk($chunk);
      }
      $timeoutTime = time() - $CONFIG->getVal('chunktimeout');
      if($chunk->getState() == DHashcatStatus::ABORTED || $chunk->getState() == DHashcatStatus::STATUS_ABORTED_RUNTIME || max($chunk->getDispatchTime(), $chunk->getSolveTime()) < $timeoutTime){
        API::handleExistingChunk($chunk, $agent, $task, $assignment);
      }
    }
    API::createNewChunk($agent, $task, $assignment);
  }
  
  public static function sendErrorResponse($action, $msg) {
    $ANS = array();
    $ANS[PQuery::ACTION] = $action;
    $ANS['response'] = "ERROR";
    $ANS['message'] = $msg;
    header("Content-Type: application/json");
    echo json_encode($ANS, true);
    die();
  }
  
  /**
   * @param $action string
   * @param $QUERY array
   */
  public static function checkToken($action, $QUERY) {
    global $FACTORIES;
    
    $qF = new QueryFilter("token", $QUERY[PQuery::TOKEN], "=");
    $token = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
    if ($token != null) {
      API::sendErrorResponse($action, "Invalid token!");
    }
  }
  
  private static function sendResponse($RESPONSE) {
    header("Content-Type: application/json");
    echo json_encode($RESPONSE, true);
    die();
  }
  
  public static function registerAgent($QUERY) {
    /** @var DataSet $CONFIG */
    global $FACTORIES, $CONFIG;
    
    //check required values
    if (!PQueryRegister::isValid($QUERY)) {
      API::sendErrorResponse(PActions::REGISTER, "Invalid registering query!");
    }
    
    $qF = new QueryFilter("voucher", $QUERY[PQueryRegister::VOUCHER], "=");
    $voucher = $FACTORIES::getRegVoucherFactory()->filter(array('filter' => array($qF)), true);
    if ($voucher == null) {
      API::sendErrorResponse(PActions::REGISTER, "Provided voucher does not exist.");
    }
    
    $gpu = $QUERY[PQueryRegister::GPUS];
    $uid = htmlentities($QUERY[PQueryRegister::USERID], false, "UTF-8");
    $name = htmlentities($QUERY[PQueryRegister::AGENT_NAME], false, "UTF-8");
    $os = intval($QUERY[PQueryRegister::OPERATING_SYSTEM]);
    
    //determine if the client has cpu only
    $cpuOnly = 1;
    foreach ($gpu as $card) {
      $card = strtolower($card);
      if ((strpos($card, "amd") !== false) || (strpos($card, "ati ") !== false) || (strpos($card, "radeon") !== false) || strpos($card, "nvidia") !== false || strpos($card, "gtx") !== false || strpos($card, "ti") !== false) {
        $cpuOnly = 0;
      }
    }
    
    //create access token & save agent details
    $token = Util::randomString(10);
    $gpu = htmlentities(implode("\n", $gpu), false, "UTF-8");
    $agent = new Agent(0, $name, $uid, $os, $gpu, "", "", $CONFIG->getVal('agenttimeout'), "", 1, 0, $token, PActions::REGISTER, time(), Util::getIP(), null, $cpuOnly);
    $FACTORIES::getRegVoucherFactory()->delete($voucher);
    if ($FACTORIES::getAgentFactory()->save($agent)) {
      API::sendResponse(array(PQueryRegister::ACTION => PActions::REGISTER, "response" => "SUCCESS", PQueryRegister::TOKEN => $token));
    }
    else {
      API::sendErrorResponse(PActions::REGISTER, "Could not register you to server.");
    }
  }
  
  public static function loginAgent($QUERY) {
    /** @var DataSet $CONFIG */
    global $FACTORIES, $CONFIG;
    
    if (!PQueryLogin::isValid($QUERY)) {
      API::sendErrorResponse(PActions::LOGIN, "Invalid login query!");
    }
    
    // login to master server with previously provided token
    $qF = new QueryFilter("token", $QUERY[PQueryLogin::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
    if ($agent == null) {
      // token was not found
      API::sendErrorResponse(PActions::LOGIN, "Unknown token, register again!");
    }
    API::updateAgent($QUERY, $agent);
    API::sendResponse(array(PQuery::ACTION => PActions::LOGIN, "response" => "SUCCESS", "timeout" => $CONFIG->getVal("agenttimeout")));
  }
  
  public static function checkClientUpdate($QUERY) {
    global $SCRIPTVERSION, $SCRIPTNAME;
    
    // TODO: updating needs to be done for new management of agent binaries/scripts
    
    // check if provided hash is the same as script and send file contents if not
    if (!PQueryUpdate::isValid($QUERY)) {
      API::sendErrorResponse(PActions::UPDATE, 'Version value missing!');
    }
    
    $version = $QUERY[PQueryUpdate::VERSION];
    
    if ($version != $SCRIPTVERSION) {
      API::sendResponse(array(PQueryUpdate::ACTION => PActions::UPDATE, 'response' => 'SUCCESS', 'version' => 'NEW', 'data' => file_get_contents(dirname(__FILE__) . "/../static/$SCRIPTNAME")));
    }
    else {
      API::sendResponse(array(PQueryUpdate::ACTION => PActions::UPDATE, 'response' => 'SUCCESS', 'version' => 'OK'));
    }
  }
  
  public static function downloadApp($QUERY) {
    global $FACTORIES;
    
    if (!PQueryDownload::isValid($QUERY)) {
      API::sendErrorResponse(PActions::DOWNLOAD, "Invalid download query!");
    }
    $qF = new QueryFilter("token", $QUERY[PQueryDownload::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
    
    // provide agent with requested download
    switch ($QUERY[PQueryDownload::BINARY_TYPE]) {
      case PValuesDownloadBinaryType::EXTRACTOR:
        // downloading 7zip
        $filename = "7zr" . (($agent->getOs() == DOperatingSystem::WINDOWS) ? ".exe" : "");
        header_remove("Content-Type");
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
        echo file_get_contents(dirname(__FILE__) . "/../static/" . $filename);
        die();
      case PValuesDownloadBinaryType::HASHCAT:
        $oF = new OrderFilter("time", "DESC LIMIT 1");
        $hashcat = $FACTORIES::getHashcatReleaseFactory()->filter(array('order' => array($oF)), true);
        if ($hashcat == null) {
          API::sendErrorResponse(PQueryDownload::ACTION, "No Hashcat release available!");
        }
        
        $postfix = array("bin", "exe");
        $executable = "hashcat64." . $postfix[$agent->getOs()];
        
        if ($agent->getHcVersion() == $hashcat->getVersion() && (!isset($QUERY[PQueryDownload::FORCE_UPDATE]) || $QUERY[PQueryDownload::FORCE_UPDATE] != '1')) {
          API::sendResponse(array(PQueryDownload::ACTION => PActions::DOWNLOAD, 'response' => 'SUCCESS', 'version' => 'OK', 'executable' => $executable));
        }
        
        $url = $hashcat->getUrl();
        $files = explode("\n", str_replace(" ", "\n", $hashcat->getCommonFiles()));
        $files[] = $executable;
        $rootdir = $hashcat->getRootdir();
        
        $agent->setHcVersion($hashcat->getVersion());
        $FACTORIES::getAgentFactory()->update($agent);
        API::sendResponse(array(PQueryDownload::ACTION => PActions::DOWNLOAD, 'response' => 'SUCCESS', 'version' => 'NEW', 'url' => $url, 'files' => $files, 'rootdir' => $rootdir, 'executable' => $executable));
        break;
      default:
        API::sendErrorResponse(PActions::DOWNLOAD, "Unknown download type!");
    }
  }
  
  public static function agentError($QUERY) {
    global $FACTORIES;
    
    //check required values
    if (!PQueryError::isValid($QUERY)) {
      API::sendErrorResponse(PActions::ERROR, "Invalid error query!");
    }
    
    //check agent and task
    $qF = new QueryFilter("token", $QUERY[PQueryError::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
    $task = $FACTORIES::getTaskFactory()->get($QUERY[PQueryError::TASK_ID]);
    if ($task == null) {
      API::sendErrorResponse(PActions::ERROR, "Invalid task!");
    }
    
    //check assignment
    $qF1 = new QueryFilter("agentId", $agent->getId(), "=");
    $qF2 = new QueryFilter("taskId", $task->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF1, $qF2)), true);
    if ($assignment == null) {
      API::sendErrorResponse(PActions::ERROR, "You are not assigned to this task!");
    }
    
    //save error message
    $error = new AgentError(0, $agent->getId(), $task->getId(), time(), $QUERY[PQueryError::MESSAGE]);
    $FACTORIES::getAgentErrorFactory()->save($error);
    
    if ($agent->getIgnoreErrors() == 0) {
      //deactivate agent
      $agent->setIsActive(0);
      $FACTORIES::getAgentFactory()->update($agent);
    }
    API::sendResponse(array(PQueryError::ACTION => PActions::ERROR, 'response' => 'SUCCESS'));
  }
  
  public static function getFile($QUERY) {
    global $FACTORIES;
    
    //check required values
    if (!PQueryFile::isValid($QUERY)) {
      API::sendErrorResponse(PActions::FILE, "Invalid file query!");
    }
    
    // let agent download adjacent files
    $task = $FACTORIES::getTaskFactory()->get($QUERY[PQueryFile::TASK_ID]);
    if ($task == null) {
      API::sendErrorResponse(PActions::FILE, "Invalid task!");
    }
    
    $file = $QUERY[PQueryFile::FILENAME];
    $qF = new QueryFilter("filename", $file, "=");
    $file = $FACTORIES::getFileFactory()->filter(array('filter' => $qF), true);
    if ($file == null) {
      API::sendErrorResponse(PActions::FILE, "Invalid file!");
    }
    
    $qF = new QueryFilter("token", $QUERY[PQueryFile::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
    
    $qF1 = new QueryFilter("taskId", $task->getId(), "=");
    $qF2 = new QueryFilter("agentId", $agent->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF1, $qF2)), true);
    if ($assignment == null) {
      API::sendErrorResponse(PActions::FILE, "Client is not assigned to this task!");
    }
    
    $qF1 = new QueryFilter("taskId", $task->getId(), "=");
    $qF2 = new QueryFilter("fileId", $file->getId(), "=");
    $taskFile = $FACTORIES::getTaskFileFactory()->filter(array('filter' => array($qF1, $qF2)), true);
    if ($taskFile == null) {
      API::sendErrorResponse(PActions::FILE, "This files is not used for the specified task!");
    }
    
    if ($agent->getIsTrusted() < $file->getSecret()) {
      API::sendErrorResponse(PActions::FILE, "You have no access to get this file!");
    }
    $filename = $file->getFilename();
    $extension = explode(".", $filename)[sizeof(explode(".", $filename)) - 1];
    //TODO: make correct url here
    API::sendResponse(array(PQueryFile::ACTION => PActions::FILE, 'filename' => $filename, 'extension' => $extension, 'response' => 'SUCCESS', 'url' => "https://". $_SERVER['HTTP_HOST'].'/src/get.php?file=' . $file->getId() . "&token=" . $agent->getToken()));
  }
  
  public static function getHashes($QUERY) {
    global $FACTORIES;
    
    //check required values
    if (!PQueryHashes::isValid($QUERY)) {
      API::sendErrorResponse(PActions::HASHES, "Invalid hashes query!");
    }
    
    $hashlist = $FACTORIES::getHashlistFactory()->get($QUERY[PQueryHashes::HASHLIST_ID]);
    if ($hashlist == null) {
      API::sendErrorResponse(PActions::HASHES, "Invalid hashlist!");
    }
    
    $qF = new QueryFilter("token", $QUERY[PQueryHashes::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
    if ($agent == null) {
      API::sendErrorResponse(PActions::HASHES, "Invalid agent!");
    }
    
    $qF = new QueryFilter("agentId", $agent->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF)), true);
    if ($assignment == null) {
      API::sendErrorResponse(PActions::HASHES, "Agent is not assigned to a task!");
    }
    
    $task = $FACTORIES::getTaskFactory()->get($assignment->getTaskId());
    if ($task == null) {
      API::sendErrorResponse(PActions::HASHES, "Assignment contains invalid task!");
    }
    
    if ($task->getHashlistId() != $hashlist->getId()) {
      API::sendErrorResponse(PActions::HASHES, "This hashlist is not used for the assigned task!");
    }
    else if ($agent->getIsTrusted() < $hashlist->getSecret()) {
      API::sendErrorResponse(PActions::HASHES, "You have not access to this hashlist!");
    }
    $lineDelimiter = "\n";
    if ($agent->getOs() == DOperatingSystem::WINDOWS) {
      $lineDelimiter = "\r\n";
    }
    
    $hashlists = array();
    $format = $hashlist->getFormat();
    if ($hashlist->getFormat() == DHashlistFormat::SUPERHASHLIST) {
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
      API::sendErrorResponse(PActions::HASHES, "No hashlists selected!");
    }
    $count = 0;
    switch ($format) {
      case DHashlistFormat::PLAIN:
        header_remove("Content-Type");
        header('Content-Type: text/plain');
        foreach ($hashlists as $list) {
          $limit = 0;
          $size = 50000;
          do {
            $oF = new OrderFilter("hashId", "ASC LIMIT $limit,$size");
            $qF1 = new QueryFilter("hashlistId", $list, "=");
            $qF2 = new QueryFilter("isCracked", "0", "=");
            $current = $FACTORIES::getHashFactory()->filter(array('filter' => array($qF1, $qF2), 'order' => array($oF)));
            
            $output = "";
            $count += sizeof($current);
            foreach ($current as $entry) {
              $output .= $entry->getHash();
              if (strlen($entry->getSalt()) > 0) {
                $output .= $hashlist->getSaltSeparator() . $entry->getSalt();
              }
              $output .= $lineDelimiter;
            }
            echo $output;
            
            $limit += $size;
          } while (sizeof($current) > 0);
        }
        break;
      case DHashlistFormat::BINARY:
      case DHashlistFormat::WPA:
        header_remove("Content-Type");
        header('Content-Type: application/octet-stream');
        foreach ($hashlists as $list) {
          $qF1 = new QueryFilter("hashlistId", $list, "=");
          $qF2 = new QueryFilter("isCracked", "0", "=");
          $current = $FACTORIES::getHashBinaryFactory()->filter(array('filter' => array($qF1, $qF2)));
          $count += sizeof($current);
          $output = "";
          foreach ($current as $entry) {
            $output .= Util::hextobin($entry->getHash());
          }
          echo $output;
        }
        break;
    }
    
    //update that the agent has downloaded the hashlist
    foreach ($hashlists as $list) {
      $qF1 = new QueryFilter("agentId", $agent->getId(), "=");
      $qF2 = new QueryFilter("hashlistId", $list, "=");
      $check = $FACTORIES::getHashlistAgentFactory()->filter(array('filter' => array($qF1, $qF2)), true);
      if ($check == null) {
        $downloaded = new HashlistAgent(0, $list, $agent->getId());
        $FACTORIES::getHashlistAgentFactory()->save($downloaded);
      }
    }
    
    if ($count == 0) {
      API::sendErrorResponse(PActions::HASHES, "No hashes are available to crack!");
    }
  }
  
  public static function getTask($QUERY) {
    global $FACTORIES;
  
    if (!PQueryTask::isValid($QUERY)) {
      API::sendErrorResponse(PActions::TASK, "Invalid task query!");
    }
    
    $qF = new QueryFilter("token", $QUERY[PQueryTask::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
    if ($agent == null) {
      API::sendErrorResponse(PActions::TASK, "Invalid token!");
    }
    else if ($agent->getIsActive() == 0) {
      API::sendResponse(array(PQueryTask::ACTION => PActions::TASK, 'response' => 'SUCCESS', 'task' => 'NONE'));
    }
    
    $qF = new QueryFilter("agentId", $agent->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF)), true);
    $assignedTask = null;
    if ($assignment == null) {
      //search which task we should assign to the agent
      $nextTask = Util::getNextTask($agent);
      if ($nextTask == null) {
        API::sendResponse(array(PQueryTask::ACTION => PActions::TASK, 'response' => 'SUCCESS', 'task' => 'NONE'));
      }
      $assignment = new Assignment(0, $nextTask->getId(), $agent->getId(), 0);
      $FACTORIES::getAssignmentFactory()->save($assignment);
      $assignedTask = $nextTask;
    }
    else {
      //check if the agent is assigned to the correct task, if not assign him the right one
      $task = $FACTORIES::getTaskFactory()->get($assignment->getTaskId());
      $hashlist = $FACTORIES::getHashlistFactory()->get($task->getHashlistId());
      $qF = new QueryFilter("taskId", $task->getId(), "=");
      $chunks = $FACTORIES::getChunkFactory()->filter(array('filter' => $qF));
      $sumProgress = 0;
      $chunkIds = array();
      foreach($chunks as $chunk){
        $sumProgress += $chunk->getProgress();
        $chunkIds[] = $chunk->getId();
      }
      $finished = false;
      
      //check if the task is finished
      if (($task->getKeyspace() == $sumProgress && $task->getKeyspace() != 0) || $hashlist->getCracked() == $hashlist->getHashCount()) {
        //task is finished
        $task->setPriority(0);
        //TODO: make massUpdate
        foreach($chunks as $chunk){
          $chunk->setProgress($chunk->getLength());
          $chunk->setRprogress(10000);
          $FACTORIES::getChunkFactory()->update($chunk);
        }
        $task->setProgress($task->getKeyspace());
        $FACTORIES::getTaskFactory()->update($task);
        $finished = true;
      }
      
      $highPriorityTask = Util::getNextTask($agent, $task->getPriority());
      if ($highPriorityTask != null) {
        //there is a more important task
        $FACTORIES::getAssignmentFactory()->delete($assignment);
        $assignment = new Assignment(0, $highPriorityTask->getId(), $agent->getId(), 0);
        $FACTORIES::getAssignmentFactory()->save($assignment);
        $assignedTask = $highPriorityTask;
      }
      else {
        if (!$finished) {
          $assignedTask = $task;
        }
        else{
          $qF = new QueryFilter("agentId", $agent->getId(), "=");
          $FACTORIES::getAssignmentFactory()->massDeletion(array('filter' => $qF));
          $assignedTask = Util::getNextTask($agent);
          if($assignedTask != null) {
            $assignment = new Assignment(0, $assignedTask->getId(), $agent->getId(), 0);
            $FACTORIES::getAssignmentFactory()->save($assignment);
          }
        }
      }
    }
    
    if ($assignedTask == null) {
      //no task available
      API::sendResponse(array(PQueryTask::ACTION => PActions::TASK, 'response' => 'SUCCESS', 'task' => 'NONE'));
    }
    
    $qF = new QueryFilter("taskId", $assignedTask->getId(), "=");
    $jF = new JoinFilter($FACTORIES::getFileFactory(), "fileId", "fileId");
    $joinedFiles = $FACTORIES::getTaskFileFactory()->filter(array('join' => $jF, 'filter' => $qF));
    $files = array();
    for ($x = 0; $x < sizeof($joinedFiles['File']); $x++) {
      $files[] = \DBA\Util::cast($joinedFiles['File'][$x], \DBA\File::class)->getFilename();
    }
    
    $hashlist = $FACTORIES::getHashlistFactory()->get($assignedTask->getHashlistId());
    
    API::sendResponse(array(
        PQueryTask::ACTION => PActions::TASK,
        'response' => 'SUCCESS',
        'task' => $assignedTask->getId(),
        'wait' => $agent->getWait(),
        'attackcmd' => $assignedTask->getAttackCmd(),
        'cmdpars' => $agent->getCmdPars() . " --hash-type=" . $hashlist->getHashTypeId(),
        'hashlist' => $assignedTask->getHashlistId(),
        'bench' => 'new', //TODO: here we should tell him new or continue depending if he was already working on this hashlist or not
        'statustimer' => $assignedTask->getStatusTimer(),
        'files' => $files
      )
    );
  }
  
  //TODO Handle the case where an agent needs reassignment
  public static function solve($QUERY) {
    global $FACTORIES;
  
    if (!PQuerySolve::isValid($QUERY)) {
      API::sendErrorResponse(PActions::SOLVE, "Invalid hashes query!");
    }
    
    // upload cracked hashes to server
    $keyspaceProgress = $QUERY[PQuerySolve::KEYSPACE_PROGRESS];
    
    $combinationProgress = floatval($QUERY[PQuerySolve::COMBINATION_PROGRESS]);      //Normalized between 1-10k
    $combinationTotal = floatval($QUERY[PQuerySolve::COMBINATION_TOTAL]);           //TODO Not sure what this variable does
    $speed = floatval($QUERY[PQuerySolve::SPEED]);
    $state = intval($QUERY[PQuerySolve::HASHCAT_STATE]);     //Util::getStaticArray($states, $state)
    
    /**
     * This part sends a lot of DB-Requests. It may need to be optimized in the future.
     */
    $chunk = $FACTORIES::getChunkFactory()->get(intval($QUERY[PQuerySolve::CHUNK_ID]));
    if ($chunk == null) {
      API::sendErrorResponse(PActions::SOLVE, "Invalid chunk id " . intval($QUERY[PQuerySolve::CHUNK_ID]));
    }
    
    $qF = new QueryFilter("token", $QUERY[PQuerySolve::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array('filter' => $qF), true);
    if ($agent == null) {
      API::sendErrorResponse(PActions::SOLVE, "Invalid agent token" . $QUERY[PQuerySolve::TOKEN]);
    }
    if ($chunk->getAgentId() != $agent->getId()) {
      API::sendErrorResponse(PActions::SOLVE, "You are not assigned to this chunk");
    }
    
    $task = $FACTORIES::getTaskFactory()->get($chunk->getTaskId());
    if ($task == null) {
      API::sendErrorResponse(PActions::SOLVE, "No task exists for the given chunk");
    }
    
    $hashList = $FACTORIES::getHashlistFactory()->get($task->getHashlistId());
    if ($hashList->getSecret() > $agent->getIsTrusted()) {
      API::sendErrorResponse(PActions::SOLVE, "Unknown Error. The API does not trust you with more information");
    }
    if ($hashList == null) {    //There are preconfigured task with hashlistID == null, but a solving task should never be preconfigured
      API::sendErrorResponse(PActions::SOLVE, "The given task does not have a corresponding hashList");
    }
    
    // agent is assigned to this chunk (not necessarily task!)
    // it can be already assigned to other task, but is still computing this chunk until it realizes it
    $skip = $chunk->getSkip();
    $length = $chunk->getLength();
    $taskID = $task->getId();
    
    /*
     * Calculate the relative progress inside of the chunk
     */
    $chunkCombinationStart = $combinationTotal/($skip+$length)*$skip;
    $currentRelativeProgress = round(($combinationProgress - $chunkCombinationStart)/($combinationTotal - $chunkCombinationStart)*10000);
    $keyspaceProgress -= $skip;
    
    //if by accident the number of the combinationProgress overshoots the limit
    if($currentRelativeProgress > 10000){
      $currentRelativeProgress = 10000;
    }
    
    //avoid rounding errors
    if ($combinationProgress < $combinationTotal && $currentRelativeProgress == 10000) {
      $currentRelativeProgress--;
    }
    else if ($combinationProgress > 0 && $currentRelativeProgress == 0) {
      $currentRelativeProgress++;
    }
    
    // workaround for hashcat not sending correct final curku(keyspaceprogress) =skip+len when done with chunk
    if ($combinationProgress == $combinationTotal) {
      $keyspaceProgress = $length;
    }
    
    /*
     * Save chunk updates
     */
    $chunk->setRprogress($currentRelativeProgress);
    $chunk->setProgress($keyspaceProgress);
    $chunk->setSolveTime(time());
    $aborting = false;
    if($chunk->getState() == DHashcatStatus::ABORTED){
      $aborting = true;
    }
    $chunk->setState($state);
    $FACTORIES::getChunkFactory()->update($chunk);
    
    
    $hlistar = Util::checkSuperHashlist($hashList);
    $hlistarIds = array();
    foreach($hlistar as $hl){
      $hlistarIds[] = $hl->getId();
    }
    $format = $FACTORIES::getHashlistFactory()->get($hlistar[0]->getId())->getFormat();
    
    // reset values
    $skipped = 0;
    $cracked = array();
    foreach ($hlistar as $l) {
      $cracked[$l->getId()] = 0;
    }
    
    // process solved hashes, should there be any
    $crackedHashes = $QUERY[PQuerySolve::CRACKED_HASHES];
    foreach ($crackedHashes as $crackedHash) {
      if ($crackedHash == "") {
        continue;
      }
      //TODO: get separator from config
      $splitLine = explode(":", $crackedHash);
      $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
      switch ($format) {
        case DHashlistFormat::PLAIN:
          //TODO search for hash in DB
          //get salt
          //replace hash + salt from the line -> plaintext remains
          // save regular password
          $hashFilter = new QueryFilter("hash", $splitLine[0], "=");
          $hashListFilter = new ContainFilter("hashlistId", $hlistarIds);
          $isCrackedFilter = new QueryFilter("isCracked", 0, "=");
          $hashes = $FACTORIES::getHashFactory()->filter(array("filter" => array($isCrackedFilter, $hashFilter, $hashListFilter)));
          if(sizeof($hashes) == 0){
            continue;
          }
          $salt = $hashes[0]->getSalt();
          if (strlen($salt) == 0) {
            // unsalted hashes
            $plain = str_replace($hashes[0]->getHash() . ':', "", $crackedHash);
          }
          else {
            // salted hashes
            $plain = str_replace($hashes[0]->getHash() . ':' . $hashes[0]->getSalt() . ':', "", $crackedHash);
          }
          if (sizeof($hashes) == 0) {
            $skipped++;
          }
          foreach ($hashes as $hash) {  //TODO Mass-update
            $cracked[$hash->getHashlistId()]++;
            $hash->setPlaintext($plain);
            $hash->setChunkId($chunk->getId());
            $hash->setIsCracked(1);
            $FACTORIES::getHashFactory()->update($hash);
          }
          break;
        case DHashlistFormat::WPA:
          // save cracked wpa password
          $network = $splitLine[0];
          $plain = $splitLine[1];
          // QUICK-FIX WPA/WPA2 strip mac address
          if (preg_match("/.+:[0-9a-f]{12}:[0-9a-f]{12}$/", $network) === 1) {
            // TODO: extend DB model by MACs and implement detection
            $network = substr($network, 0, strlen($network) - 26);
          }
          $essIDFilter = new QueryFilter("essid", $network, "=");
          $hashes = $FACTORIES::getHashBinaryFactory()->filter(array("filter" => $essIDFilter));
          if (sizeof($hashes) == 0) {
            $skipped++;
          }
          foreach ($hashes as $hash) {
            $cracked[$hash->getHashlistId()]++;
            $hash->setIsCracked(1);
            $hash->setChunkId($chunk->getId());
            $hash->setPlaintext($plain);
            $FACTORIES::getHashFactory()->update($hash);
          }
          break;
        case DHashlistFormat::BINARY:
          // save binary password
          // TODO Fix issue with superhashlists
          //$plain = $splitLine[1];
          break;
      }
      $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
    }
    
    //insert #Cracked hashes and update in hashlist how many hashes were cracked
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
    $sumCracked = 0;
    foreach ($cracked as $listId => $cracks) {
      $list = $FACTORIES::getHashlistFactory()->get($listId);
      $list->setCracked($cracks + $list->getCracked());
      $FACTORIES::getHashlistFactory()->update($list);
      $sumCracked += $cracks;
    }
    $chunk = $FACTORIES::getChunkFactory()->get($chunk->getId());
    $chunk->setCracked($chunk->getCracked() + $sumCracked);
    $FACTORIES::getChunkFactory()->update($chunk);
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
    
    if ($chunk->getState() == DHashcatStatus::STATUS_ABORTED_RUNTIME) {
      // the chunk was manually interrupted
      $chunk->setState(DHashcatStatus::ABORTED);
      $FACTORIES::getChunkFactory()->update($chunk);
      API::sendErrorResponse(PActions::SOLVE, "Chunk was manually interrupted.");
    }
    /** Check if the task is done */
    $taskdone = false;
    if ($combinationProgress == $combinationTotal && $task->getProgress() == $task->getKeyspace()) {
      // chunk is done and the task has been fully dispatched
      $incompleteFilter = new QueryFilter("rprogress", 10000, "<");
      $taskFilter = new QueryFilter("taskId", $taskID, "=");
      $count = $FACTORIES::getChunkFactory()->countFilter(array("filter" => array($incompleteFilter, $taskFilter)));
      if ($count == 0) {
        // this was the last incomplete chunk!
        $taskdone = true;
      }
    }
    
    if ($taskdone) {
      // task is fully dispatched and this last chunk is done, deprioritize it
      $task->setPriority(0);
      $FACTORIES::getTaskFactory()->update($task);
      
      // TODO: notificate task done
    }
    
    $hashlists = Util::checkSuperHashlist($hashList);
    $hashlistIds = array();
    foreach($hashlists as $hl){
      $hashlistIds[] = $hl->getId();
    }
    $toZap = array();
    
    if($aborting){
      $chunk->setSpeed(0);
      $FACTORIES::getChunkFactory()->update($chunk);
      API::sendErrorResponse(PActions::SOLVE, "Chunk was aborted!");
    }
    
    switch ($state) {
      case DHashcatStatus::EXHAUSTED:
        // the chunk has finished (exhausted)
        $chunk->setSpeed(0);
        $FACTORIES::getChunkFactory()->update($chunk);
        break;
      case DHashcatStatus::CRACKED:
        // the chunk has finished (cracked whole hashList)
        // deprioritize all tasks and unassign all agents
        $qF = new ContainFilter("hashlistId", $hashlistIds);
        $uS = new UpdateSet("priority", "0");
        $FACTORIES::getTaskFactory()->massUpdate(array('update' => $uS, 'filter' => $qF));
        
        $chunk->setSpeed(0);
        $FACTORIES::getChunkFactory()->update($chunk);
        
        //TODO: notificate hashList done
        break;
      case DHashcatStatus::ABORTED:
      case DHashcatStatus::QUIT:
        // the chunk was aborted or quit
        $chunk->setSpeed(0);
        $FACTORIES::getChunkFactory()->update($chunk);
        API::sendErrorResponse(PActions::SOLVE, "Chunk was aborted!");
        break;
      case DHashcatStatus::RUNNING:
      default:
        // the chunk isn't finished yet, we will send zaps
        $qF1 = new ComparisonFilter("cracked", "hashCount", "<");
        $qF2 = new ContainFilter("hashlistId", $hashlistIds);
        $count = $FACTORIES::getHashlistFactory()->countFilter(array('filter' => array($qF1, $qF2)));
        if ($count == 0) {
          //stop agent
          API::sendResponse(array(PQuerySolve::ACTION => PActions::SOLVE, "response" => "SUCCESS", "cracked" => $sumCracked, "skipped" => $skipped, "agent" => "stop"));
        }
        $chunk->setSpeed($speed);
        $FACTORIES::getChunkFactory()->update($chunk);
        
        $qF1 = new ContainFilter("hashlistId", $hashlistIds);
        $qF2 = new QueryFilter("solveTime", $agent->getLastAct(), ">=");
        $zaps = $FACTORIES::getZapFactory()->filter(array('filter' => array($qF1, $qF2)));
        foreach ($zaps as $zap) {
          $toZap[] = $zap->getHash();
        }
        $agent->setLastAct(time());
        $FACTORIES::getAgentFactory()->update($agent);
        
        // update hashList age for agent to this task
        break;
    }
    Util::zapCleaning();
    API::sendResponse(array(PQuerySolve::ACTION => PActions::SOLVE, "response" => "SUCCESS", "cracked" => $sumCracked, "skipped" => $skipped, "zaps" => $toZap));
  }
}