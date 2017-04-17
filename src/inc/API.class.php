<?php

use DBA\Agent;
use DBA\AgentBinary;
use DBA\AgentError;
use DBA\AgentZap;
use DBA\Assignment;
use DBA\Chunk;
use DBA\ComparisonFilter;
use DBA\ContainFilter;
use DBA\File;
use DBA\Hash;
use DBA\HashBinary;
use DBA\HashcatRelease;
use DBA\Hashlist;
use DBA\HashlistAgent;
use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\SuperHashlistHashlist;
use DBA\Task;
use DBA\TaskFile;
use DBA\Zap;

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
  
  public static function test() {
    API::sendResponse(array(
        PResponse::ACTION => PActions::TEST,
        PResponse::RESPONSE => PValues::SUCCESS
      )
    );
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
    $qF = new QueryFilter(Agent::TOKEN, $QUERY[PQueryBenchmark::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    $qF1 = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "=");
    $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
    if ($assignment == null) {
      API::sendErrorResponse(PActions::BENCHMARK, "You are not assigned to this task!");
    }
    
    $type = $QUERY[PQueryBenchmark::TYPE];
    $benchmark = $QUERY[PQueryBenchmark::RESULT];
    
    switch ($type) {
      case PValuesBenchmarkType::SPEED_TEST:
        $split = explode(":", $benchmark);
        if (sizeof($split) != 2 || !is_numeric($split[0]) || !is_numeric($split[1]) || $split[0] <= 0 || $split[1] <= 0) {
          $agent->setIsActive(0);
          $FACTORIES::getAgentFactory()->update($agent);
          API::sendErrorResponse(PActions::BENCHMARK, "Invalid benchmark result!");
        }
        break;
      case PValuesBenchmarkType::RUN_TIME:
        if (!is_numeric($benchmark) || $benchmark <= 0) {
          $agent->setIsActive(0);
          $FACTORIES::getAgentFactory()->update($agent);
          API::sendErrorResponse(PActions::BENCHMARK, "Invalid benchmark result!");
        }
        // normalize time of the benchmark to 100 seconds
        $benchmark = $benchmark / $CONFIG->getVal(DConfig::BENCHMARK_TIME) * 100;
        break;
      default:
        $agent->setIsActive(0);
        $FACTORIES::getAgentFactory()->update($agent);
        API::sendErrorResponse(PActions::BENCHMARK, "Invalid benchmark type!");
    }
    
    $assignment->setBenchmark($benchmark);
    $FACTORIES::getAssignmentFactory()->update($assignment);
    API::sendResponse(array(
        PResponseBenchmark::ACTION => PActions::BENCHMARK,
        PResponseBenchmark::RESPONSE => PValues::SUCCESS,
        PResponseBenchmark::BENCHMARK => PValues::OK
      )
    );
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
    $qF = new QueryFilter(Agent::TOKEN, $QUERY[PQueryKeyspace::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    $qF1 = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "=");
    $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
    if ($assignment == null) {
      API::sendErrorResponse(PActions::KEYSPACE, "You are not assigned to this task!");
    }
    
    if ($task->getKeyspace() == 0) {
      // keyspace is still required
      $task->setKeyspace($keyspace);
      $FACTORIES::getTaskFactory()->update($task);
    }
    
    // test if the task may have a skip value which is too high for this keyspace
    if ($task->getSkipKeyspace() > $task->getKeyspace()) {
      // skip is too high
      $task->setPriority(0);
      $FACTORIES::getTaskFactory()->update($task);
      $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
      $FACTORIES::getAssignmentFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
      Util::createLogEntry(DLogEntryIssuer::API, $agent->getToken(), DLogEntry::ERROR, "Task with ID " . $task->getId() . " has set a skip value which is too high for its keyspace!");
    }
    
    API::sendResponse(array(
        PResponseKeyspace::ACTION => PActions::KEYSPACE,
        PResponseKeyspace::RESPONSE => PValues::SUCCESS,
        PResponseKeyspace::KEYSPACE => PValues::OK
      )
    );
  }
  
  /**
   * @param $chunk \DBA\Chunk
   */
  private static function sendChunk($chunk) {
    global $FACTORIES;
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
    API::sendResponse(array(
        PResponseChunk::ACTION => PActions::CHUNK,
        PResponseChunk::RESPONSE => PValues::SUCCESS,
        PResponseChunk::CHUNK_STATUS => PValuesChunkType::OK,
        PResponseChunk::CHUNK_ID => (int)($chunk->getId()),
        PResponseChunk::KEYSPACE_SKIP => (int)($chunk->getSkip()),
        PResponseChunk::KEYSPACE_LENGTH => (int)($chunk->getLength())
      )
    );
  }
  
  /**
   * @param $keyspace
   * @param $benchmark
   * @param $chunkTime
   * @param int $tolerance
   * @return int
   */
  private static function calculateChunkSize($keyspace, $benchmark, $chunkTime, $tolerance = 1) {
    /** @var DataSet $CONFIG */
    global $CONFIG, $QUERY;
    
    if ($chunkTime <= 0) {
      $chunkTime = $CONFIG->getVal(DConfig::CHUNK_DURATION);
    }
    if (strpos($benchmark, ":") === false) {
      // old benchmarking method
      $size = floor($keyspace * $benchmark * $chunkTime / 100);
    }
    else {
      // new benchmarking method
      $benchmark = explode(":", $benchmark);
      if (sizeof($benchmark) != 2 || $benchmark[0] <= 0 || $benchmark[1] <= 0) {
        return 0;
      }
      
      //TODO: check if time adjustments are needed
      $benchmark[1] *= 2 / 3;
      
      $factor = $chunkTime * 1000 / $benchmark[1];
      if ($factor <= 0.25) {
        $benchmark[0] /= 4;
      }
      else if ($factor <= 0.5) {
        $benchmark[0] /= 2;
      }
      else {
        $factor = floor($factor);
      }
      if ($factor == 0) {
        $factor = 1;
      }
      $size = $benchmark[0] * $factor;
    }
    
    $chunkSize = $size * $tolerance;
    if ($chunkSize <= 0) {
      $chunkSize = 1;
      Util::createLogEntry("API", $QUERY[PQuery::TOKEN], DLogEntry::WARN, "Calculated chunk size was 0 on benchmark $benchmark!");
    }
    
    return $chunkSize;
  }
  
  /**
   * @param $chunk \DBA\Chunk
   * @param $agent \DBA\Agent
   * @param $task \DBA\Task
   * @param $assignment \DBA\Assignment
   */
  private static function handleExistingChunk($chunk, $agent, $task, $assignment) {
    global $FACTORIES;
    
    $disptolerance = 1.2; //TODO: add this to config
    
    $agentChunkSize = API::calculateChunkSize($task->getKeyspace(), $assignment->getBenchmark(), $task->getChunkTime(), 1);
    $agentChunkSizeMax = API::calculateChunkSize($task->getKeyspace(), $assignment->getBenchmark(), $task->getChunkTime(), $disptolerance);
    if ($chunk->getProgress() == 0 && $agentChunkSizeMax > $chunk->getLength()) {
      //chunk has not started yet
      $chunk->setRprogress(0);
      $chunk->setDispatchTime(time());
      $chunk->setSolveTime(0);
      $chunk->setState(DHashcatStatus::INIT);
      $chunk->setAgentId($agent->getId());
      $FACTORIES::getChunkFactory()->update($chunk);
      API::sendChunk($chunk);
    }
    else if ($chunk->getProgress() == 0) {
      //split chunk into two parts
      $originalLength = $chunk->getLength();
      $firstPart = $chunk;
      $firstPart->setLength($agentChunkSize);
      $firstPart->setAgentId($agent->getId());
      $firstPart->setDispatchTime(time());
      $firstPart->setSolveTime(0);
      $firstPart->setState(DHashcatStatus::INIT);
      $firstPart->setRprogress(0);
      $FACTORIES::getChunkFactory()->update($firstPart);
      $secondPart = new Chunk(0, $task->getId(), $firstPart->getSkip() + $firstPart->getLength(), $originalLength - $firstPart->getLength(), null, 0, 0, 0, 0, DHashcatStatus::INIT, 0, 0);
      $FACTORIES::getChunkFactory()->save($secondPart);
      API::sendChunk($firstPart);
    }
    else {
      $newChunk = new Chunk(0, $task->getId(), $chunk->getSkip() + $chunk->getProgress(), $chunk->getLength() - $chunk->getProgress(), $agent->getId(), time(), 0, 0, DHashcatStatus::INIT, 0, 0, 0);
      $chunk->setLength($chunk->getProgress());
      $chunk->setRprogress(10000);
      $chunk->setState(DHashcatStatus::ABORTED_CHECKPOINT);
      $FACTORIES::getChunkFactory()->update($chunk);
      $newChunk = $FACTORIES::getChunkFactory()->save($newChunk);
      API::sendChunk($newChunk);
    }
  }
  
  /**
   * @param $agent \DBA\Agent
   * @param $task \DBA\Task
   * @param $assignment \DBA\Assignment
   */
  private static function createNewChunk($agent, $task, $assignment) {
    global $FACTORIES;
    
    $disptolerance = 1.2; //TODO: add to config
    
    // if we have set a skip keyspace we set the the current progress to the skip which was set initially
    if ($task->getSkipKeyspace() > $task->getProgress()) {
      $task->setProgress($task->getSkipKeyspace());
      $FACTORIES::getTaskFactory()->update($task);
    }
    
    $remaining = $task->getKeyspace() - $task->getProgress();
    if ($remaining == 0) {
      API::sendResponse(array(
          PResponseChunk::ACTION => PActions::CHUNK,
          PResponseChunk::RESPONSE => PValues::SUCCESS,
          PResponseChunk::CHUNK_STATUS => PValuesChunkType::FULLY_DISPATCHED
        )
      );
    }
    $agentChunkSize = API::calculateChunkSize($task->getKeyspace(), $assignment->getBenchmark(), $task->getChunkTime(), 1);
    $start = $task->getProgress();
    $length = $agentChunkSize;
    if ($remaining / $length <= $disptolerance) {
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
    $qF = new QueryFilter(Agent::TOKEN, $QUERY[PQueryChunk::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    
    // check if the agent should be notified about a hashcat update
    $oF = new OrderFilter(HashcatRelease::TIME, "DESC LIMIT 1");
    $hashcat = $FACTORIES::getHashcatReleaseFactory()->filter(array($FACTORIES::ORDER => array($oF)), true);
    if ($hashcat != null) {
      if ($agent->getHcVersion() != $hashcat->getVersion()) {
        /*API::sendResponse(array(
            PResponseChunk::ACTION => PActions::CHUNK,
            PResponseChunk::RESPONSE => PValues::SUCCESS,
            PResponseChunk::CHUNK_STATUS => PValuesChunkType::HASHCAT_UPDATE
          )
        );*/
      }
    }
    
    $qF1 = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "=");
    $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
    if ($assignment == null) {
      API::sendErrorResponse(PActions::CHUNK, "You are not assigned to this task!");
    }
    else if ($task->getKeyspace() == 0) {
      API::sendResponse(array(
          PResponseChunk::ACTION => PActions::CHUNK,
          PResponseChunk::RESPONSE => PValues::SUCCESS,
          PResponseChunk::CHUNK_STATUS => PValuesChunkType::KEYSPACE_REQUIRED
        )
      );
    }
    else if ($assignment->getBenchmark() == 0) {
      API::sendResponse(array(
          PResponseChunk::ACTION => PActions::CHUNK,
          PResponseChunk::RESPONSE => PValues::SUCCESS,
          PResponseChunk::CHUNK_STATUS => PValuesChunkType::BENCHMARK_REQUIRED
        )
      );
    }
    else if ($agent->getIsActive() == 0) {
      API::sendErrorResponse(PActions::CHUNK, "Agent is inactive!");
    }
    
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
    $dispatched = 0;
    foreach ($chunks as $chunk) {
      if (($chunk->getAgentId() == null || $chunk->getAgentId() == $agent->getId() || time() - $chunk->getSolveTime() > $CONFIG->getVal(DConfig::AGENT_TIMEOUT)) && $chunk->getRProgress() != 10000) {
        continue;
      }
      $dispatched += $chunk->getLength();
    }
    if ($task->getProgress() == $task->getKeyspace() && $task->getKeyspace() == $dispatched) {
      API::sendResponse(array(
          PResponseChunk::ACTION => PActions::CHUNK,
          PResponseChunk::RESPONSE => PValues::SUCCESS,
          PResponseChunk::CHUNK_STATUS => PValuesChunkType::FULLY_DISPATCHED
        )
      );
    }
    
    // check here either if we have a better task to run on, or we have no access anymore to this task
    $bestTask = Util::getBestTask($agent);
    if ($bestTask != null && $task->getPriority() < $bestTask->getPriority()) {
      API::sendErrorResponse(PActions::CHUNK, "Task with higher priority available!");
    }
    else if ($bestTask == null) {
      // this is a special case where this task is either not allowed anymore, or it has priority 0 so it doesn't get auto assigned
      if (!Util::agentHasAccessToTask($task, $agent)) {
        API::sendErrorResponse(PActions::CHUNK, "Not allowed to work on this task!");
      }
    }
    
    $qF1 = new ComparisonFilter(Chunk::PROGRESS, Chunk::LENGTH, "<");
    $qF2 = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $oF = new OrderFilter(Chunk::SKIP, "ASC");
    $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2), $FACTORIES::ORDER => $oF));
    foreach ($chunks as $chunk) {
      if ($chunk->getAgentId() == $agent->getId()) {
        API::handleExistingChunk($chunk, $agent, $task, $assignment);
      }
      $timeoutTime = time() - $CONFIG->getVal(DConfig::CHUNK_TIMEOUT);
      if ($chunk->getState() == DHashcatStatus::ABORTED || $chunk->getState() == DHashcatStatus::STATUS_ABORTED_RUNTIME || max($chunk->getDispatchTime(), $chunk->getSolveTime()) < $timeoutTime) {
        API::handleExistingChunk($chunk, $agent, $task, $assignment);
      }
    }
    API::createNewChunk($agent, $task, $assignment);
  }
  
  public static function sendErrorResponse($action, $msg) {
    $ANS = array();
    $ANS[PResponseErrorMessage::ACTION] = $action;
    $ANS[PResponseErrorMessage::RESPONSE] = PValues::ERROR;
    $ANS[PResponseErrorMessage::MESSAGE] = $msg;
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
    
    $qF = new QueryFilter(Agent::TOKEN, $QUERY[PQuery::TOKEN], "=");
    $token = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => array($qF)), true);
    if ($token == null) {
      API::sendErrorResponse($action, "Invalid token!");
    }
  }
  
  private static function sendResponse($RESPONSE) {
    header("Content-Type: application/json");
    echo json_encode($RESPONSE, true);
    die();
  }
  
  public static function registerAgent($QUERY) {
    global $FACTORIES;
    
    //check required values
    if (!PQueryRegister::isValid($QUERY)) {
      API::sendErrorResponse(PActions::REGISTER, "Invalid registering query!");
    }
    
    $qF = new QueryFilter(\DBA\RegVoucher::VOUCHER, $QUERY[PQueryRegister::VOUCHER], "=");
    $voucher = $FACTORIES::getRegVoucherFactory()->filter(array($FACTORIES::FILTER => array($qF)), true);
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
    $agent = new Agent(0, $name, $uid, $os, $gpu, "", "", 0, 1, 0, $token, PActions::REGISTER, time(), Util::getIP(), null, $cpuOnly);
    $FACTORIES::getRegVoucherFactory()->delete($voucher);
    if ($FACTORIES::getAgentFactory()->save($agent)) {
      $payload = new DataSet(array(DPayloadKeys::AGENT => $agent));
      NotificationHandler::checkNotifications(DNotificationType::NEW_AGENT, $payload);
      
      API::sendResponse(array(
          PQueryRegister::ACTION => PActions::REGISTER,
          PResponseRegister::RESPONSE => PValues::SUCCESS,
          PResponseRegister::TOKEN => $token
        )
      );
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
    $qF = new QueryFilter(Agent::TOKEN, $QUERY[PQueryLogin::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => array($qF)), true);
    if ($agent == null) {
      // token was not found
      API::sendErrorResponse(PActions::LOGIN, "Unknown token, register again!");
    }
    API::updateAgent($QUERY, $agent);
    API::sendResponse(array(
        PResponseLogin::ACTION => PActions::LOGIN,
        PResponseLogin::RESPONSE => PValues::SUCCESS,
        PResponseLogin::TIMEOUT => $CONFIG->getVal(DConfig::AGENT_TIMEOUT)
      )
    );
  }
  
  public static function checkClientUpdate($QUERY) {
    global $FACTORIES;
    
    // check if provided hash is the same as script and send file contents if not
    if (!PQueryUpdate::isValid($QUERY)) {
      API::sendErrorResponse(PActions::UPDATE, 'Invalid update query!');
    }
    
    $version = $QUERY[PQueryUpdate::VERSION];
    $type = $QUERY[PQueryUpdate::TYPE];
    
    $qF = new QueryFilter(AgentBinary::TYPE, $type, "=");
    $result = $FACTORIES::getAgentBinaryFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if ($result == null) {
      API::sendErrorResponse(PActions::UPDATE, "Type not found!");
    }
    $base = explode("/", $_SERVER['PHP_SELF']);
    unset($base[sizeof($base) - 1]);
    unset($base[sizeof($base) - 1]);
    $base = implode("/", $base);
    
    if (Util::versionComparison($result->getVersion(), $version) == -1) {
      API::sendResponse(array(
          PResponseUpdate::ACTION => PActions::UPDATE,
          PResponseUpdate::RESPONSE => PValues::SUCCESS,
          PResponseUpdate::VERSION => PValuesUpdateVersion::NEW_VERSION,
          PResponseUpdate::URL => Util::buildServerUrl() . $base . "/agents.php?download=" . $result->getId()
        )
      );
    }
    else {
      API::sendResponse(array(
          PResponseUpdate::ACTION => PActions::UPDATE,
          PResponseUpdate::RESPONSE => PValues::SUCCESS,
          PResponseUpdate::VERSION => PValuesUpdateVersion::UP_TO_DATE
        )
      );
    }
  }
  
  public static function downloadApp($QUERY) {
    global $FACTORIES;
    
    if (!PQueryDownload::isValid($QUERY)) {
      API::sendErrorResponse(PActions::DOWNLOAD, "Invalid download query!");
    }
    $qF = new QueryFilter(Agent::TOKEN, $QUERY[PQueryDownload::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => array($qF)), true);
    
    API::updateAgent($QUERY, $agent);
    
    // provide agent with requested download
    switch ($QUERY[PQueryDownload::BINARY_TYPE]) {
      case PValuesDownloadBinaryType::EXTRACTOR:
        // downloading 7zip
        $filename = "7zr";
        switch ($agent->getOs()) {
          case DOperatingSystem::LINUX:
            $filename .= ".unix";
            break;
          case DOperatingSystem::WINDOWS:
            $filename .= ".exe";
            break;
          case DOperatingSystem::OSX:
            $filename .= ".osx";
            break;
        }
        $url = explode("/", $_SERVER['REQUEST_URI']);
        unset($url[sizeof($url) - 1]);
        unset($url[sizeof($url) - 1]);
        $path = Util::buildServerUrl() . implode("/", $url) . "/static/" . $filename;
        API::sendResponse(array(
            PResponseDownload::ACTION => PActions::DOWNLOAD,
            PResponseDownload::RESPONSE => PValues::SUCCESS,
            PResponseDownload::EXECUTABLE => $path
          )
        );
        break;
      case PValuesDownloadBinaryType::HASHCAT:
        $oF = new OrderFilter(HashcatRelease::TIME, "DESC LIMIT 1");
        $hashcat = $FACTORIES::getHashcatReleaseFactory()->filter(array($FACTORIES::ORDER => array($oF)), true);
        if ($hashcat == null) {
          API::sendErrorResponse(PQueryDownload::ACTION, "No Hashcat release available!");
        }
        
        $postfix = array("hashcat64.bin", "hashcat64.exe", "hashcat");
        $executable = $postfix[$agent->getOs()];
        
        if ($agent->getHcVersion() == $hashcat->getVersion() && (!isset($QUERY[PQueryDownload::FORCE_UPDATE]) || $QUERY[PQueryDownload::FORCE_UPDATE] != '1')) {
          API::sendResponse(array(
              PResponseDownload::ACTION => PActions::DOWNLOAD,
              PResponseDownload::RESPONSE => PValues::SUCCESS,
              PResponseDownload::VERSION => PValuesDownloadVersion::UP_TO_DATE,
              PResponseDownload::EXECUTABLE => $executable
            )
          );
        }
        
        $url = $hashcat->getUrl();
        $rootdir = $hashcat->getRootdir();
        
        $agent->setHcVersion($hashcat->getVersion());
        $FACTORIES::getAgentFactory()->update($agent);
        API::sendResponse(array(
            PResponseDownload::ACTION => PActions::DOWNLOAD,
            PResponseDownload::RESPONSE => PValues::SUCCESS,
            PResponseDownload::VERSION => PValuesDownloadVersion::NEW_VERSION,
            PResponseDownload::URL => $url,
            PResponseDownload::ROOT_DIR => $rootdir,
            PResponseDownload::EXECUTABLE => $executable
          )
        );
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
    $qF = new QueryFilter(Agent::TOKEN, $QUERY[PQueryError::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => array($qF)), true);
    $task = $FACTORIES::getTaskFactory()->get($QUERY[PQueryError::TASK_ID]);
    if ($task == null) {
      API::sendErrorResponse(PActions::ERROR, "Invalid task!");
    }
    
    //check assignment
    $qF1 = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "=");
    $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
    if ($assignment == null) {
      API::sendErrorResponse(PActions::ERROR, "You are not assigned to this task!");
    }
    
    API::updateAgent($QUERY, $agent);
    
    //save error message
    $error = new AgentError(0, $agent->getId(), $task->getId(), time(), $QUERY[PQueryError::MESSAGE]);
    $FACTORIES::getAgentErrorFactory()->save($error);
    
    $payload = new DataSet(array(DPayloadKeys::AGENT => $agent, DPayloadKeys::AGENT_ERROR => $QUERY[PQueryError::MESSAGE]));
    NotificationHandler::checkNotifications(DNotificationType::AGENT_ERROR, $payload);
    
    if ($agent->getIgnoreErrors() == 0) {
      //deactivate agent
      $agent->setIsActive(0);
      $FACTORIES::getAgentFactory()->update($agent);
    }
    API::sendResponse(array(
        PQueryError::ACTION => PActions::ERROR,
        PResponseError::RESPONSE => PValues::SUCCESS
      )
    );
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
    $qF = new QueryFilter(File::FILENAME, $file, "=");
    $file = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if ($file == null) {
      API::sendErrorResponse(PActions::FILE, "Invalid file!");
    }
    
    $qF = new QueryFilter(Agent::TOKEN, $QUERY[PQueryFile::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => array($qF)), true);
    
    $qF1 = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $qF2 = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
    if ($assignment == null) {
      API::sendErrorResponse(PActions::FILE, "Client is not assigned to this task!");
    }
    
    $qF1 = new QueryFilter(TaskFile::TASK_ID, $task->getId(), "=");
    $qF2 = new QueryFilter(TaskFile::FILE_ID, $file->getId(), "=");
    $taskFile = $FACTORIES::getTaskFileFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
    if ($taskFile == null) {
      API::sendErrorResponse(PActions::FILE, "This files is not used for the specified task!");
    }
    
    if ($agent->getIsTrusted() < $file->getSecret()) {
      API::sendErrorResponse(PActions::FILE, "You have no access to get this file!");
    }
    $filename = $file->getFilename();
    $extension = explode(".", $filename)[sizeof(explode(".", $filename)) - 1];
    
    API::updateAgent($QUERY, $agent);
    
    API::sendResponse(array(
        PQueryFile::ACTION => PActions::FILE,
        PResponseFile::FILENAME => $filename,
        PResponseFile::EXTENSION => $extension,
        PResponseFile::RESPONSE => PValues::SUCCESS,
        PResponseFile::URL => "get.php?file=" . $file->getId() . "&token=" . $agent->getToken()
      )
    );
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
    
    $qF = new QueryFilter(Agent::TOKEN, $QUERY[PQueryHashes::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => array($qF)), true);
    if ($agent == null) {
      API::sendErrorResponse(PActions::HASHES, "Invalid agent!");
    }
    
    $qF = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => array($qF)), true);
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
    if ($format == DHashlistFormat::SUPERHASHLIST) {
      //we have a superhashlist
      $qF = new QueryFilter(SuperHashlistHashlist::SUPER_HASHLIST_ID, $hashlist->getId(), "=");
      $lists = $FACTORIES->getSuperHashlistHashlistFactory()->filter(array($FACTORIES::FILTER => array($qF)));
      foreach ($lists as $list) {
        $hl = $FACTORIES::getHashlistFactory()->get($list->getHashlistId());
        if ($hl->getSecret() > $agent->getIsTrusted()) {
          continue;
        }
        else if ($format == DHashlistFormat::SUPERHASHLIST) {
          // if we don't know the format yet, load it
          $format = $FACTORIES::getHashlistFactory()->get($list->getHashlistId())->getFormat();
        }
        $hashlists[] = $list->getHashlistId();
      }
    }
    else {
      $hashlists[] = $hashlist->getId();
    }
    
    API::updateAgent($QUERY, $agent);
    
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
          $size = 10000; //TODO: make this configurable
          do {
            $oF = new OrderFilter(Hash::HASH_ID, "ASC LIMIT $limit,$size");
            $qF1 = new QueryFilter(Hash::HASHLIST_ID, $list, "=");
            $qF2 = new QueryFilter(Hash::IS_CRACKED, "0", "=");
            $current = $FACTORIES::getHashFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2), $FACTORIES::ORDER => array($oF)));
            
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
        //header_remove("Content-Type");
        //header('Content-Type: application/octet-stream');
        $output = "";
        foreach ($hashlists as $list) {
          $qF1 = new QueryFilter(HashBinary::HASHLIST_ID, $list, "=");
          $qF2 = new QueryFilter(HashBinary::IS_CRACKED, "0", "=");
          $current = $FACTORIES::getHashBinaryFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)));
          $count += sizeof($current);
          foreach ($current as $entry) {
            $output .= Util::hextobin($entry->getHash());
          }
        }
        $data = base64_encode($output);
        API::sendResponse(array(
            PQueryFile::ACTION => PActions::HASHES,
            PResponse::RESPONSE => PValues::SUCCESS,
            PResponseHashes::DATA => $data
          )
        );
        break;
    }
    
    //update that the agent has downloaded the hashlist
    foreach ($hashlists as $list) {
      $qF1 = new QueryFilter(HashlistAgent::AGENT_ID, $agent->getId(), "=");
      $qF2 = new QueryFilter(HashlistAgent::HASHLIST_ID, $list, "=");
      $check = $FACTORIES::getHashlistAgentFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
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
    /** @var DataSet $CONFIG */
    global $FACTORIES, $CONFIG;
    
    if (!PQueryTask::isValid($QUERY)) {
      API::sendErrorResponse(PActions::TASK, "Invalid task query!");
    }
    
    $qF = new QueryFilter(Agent::TOKEN, $QUERY[PQueryTask::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if ($agent == null) {
      API::sendErrorResponse(PActions::TASK, "Invalid token!");
    }
    else if ($agent->getIsActive() == 0) {
      API::sendResponse(array(
          PResponseTask::ACTION => PActions::TASK,
          PResponseTask::RESPONSE => PValues::SUCCESS,
          PResponseTask::TASK_ID => PValues::NONE
        )
      );
    }
    
    API::updateAgent($QUERY, $agent);
    
    $qF = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => array($qF)), true);
    
    // test if the current task is obsolete anyway, this makes it easier to select a new one
    $currentTask = null;
    if ($assignment != null) {
      $currentTask = $FACTORIES::getTaskFactory()->get($assignment->getTaskId());
    }
    if ($currentTask != null && !Util::taskCanBeUsed($currentTask, $agent)) {
      $FACTORIES::getAssignmentFactory()->delete($assignment);
      $assignment = null;
      $currentTask = null;
    }
    
    $setToTask = null;
    if ($assignment == null) {
      // we have no task assigned currently
      // get the highest priority task possible (needs to be >0 here)
      $setToTask = Util::getBestTask($agent);
      $newAssignment = true;
    }
    else {
      // we are currently assigned to a task
      $setToTask = $currentTask;
      $betterTask = Util::getBestTask($agent, $currentTask->getPriority());
      if ($betterTask != null) {
        $setToTask = $betterTask;
        $newAssignment = true;
      }
      else {
        $newAssignment = false;
      }
    }
    
    if ($setToTask == null) {
      API::sendResponse(array(
          PResponseTask::ACTION => PActions::TASK,
          PResponseTask::RESPONSE => PValues::SUCCESS,
          PResponseTask::TASK_ID => PValues::NONE
        )
      );
    }
    if ($currentTask != null && $setToTask->getId() != $currentTask->getId()) {
      // delete old assignment
      $FACTORIES::getAssignmentFactory()->delete($assignment);
    }
    if ($newAssignment) {
      $assignment = new Assignment(0, $setToTask->getId(), $agent->getId(), 0);
      $FACTORIES::getAssignmentFactory()->save($assignment);
    }
    
    $qF = new QueryFilter(TaskFile::TASK_ID, $setToTask->getId(), "=");
    $jF = new JoinFilter($FACTORIES::getFileFactory(), File::FILE_ID, TaskFile::FILE_ID);
    $joinedFiles = $FACTORIES::getTaskFileFactory()->filter(array($FACTORIES::JOIN => $jF, $FACTORIES::FILTER => $qF));
    $files = array();
    for ($x = 0; $x < sizeof($joinedFiles[$FACTORIES::getFileFactory()->getModelName()]); $x++) {
      $files[] = \DBA\Util::cast($joinedFiles[$FACTORIES::getFileFactory()->getModelName()][$x], \DBA\File::class)->getFilename();
    }
    
    $hashlist = $FACTORIES::getHashlistFactory()->get($setToTask->getHashlistId());
    $benchType = ($setToTask->getUseNewBench()) ? "speed" : "run";
    
    API::sendResponse(array(
        PResponseTask::ACTION => PActions::TASK,
        PResponseTask::RESPONSE => PValues::SUCCESS,
        PResponseTask::TASK_ID => (int)$setToTask->getId(),
        PResponseTask::ATTACK_COMMAND => $setToTask->getAttackCmd(),
        PResponseTask::CMD_PARAMETERS => $agent->getCmdPars() . " --hash-type=" . $hashlist->getHashTypeId(),
        PResponseTask::HASHLIST_ID => (int)$setToTask->getHashlistId(),
        PResponseTask::BENCHMARK => (int)$CONFIG->getVal(DConfig::BENCHMARK_TIME),
        PResponseTask::STATUS_TIMER => (int)$setToTask->getStatusTimer(),
        PResponseTask::FILES => $files,
        PResponseTask::BENCHTYPE => $benchType,
        PResponseTask::HASHLIST_ALIAS => $CONFIG->getVal(DConfig::HASHLIST_ALIAS)
      )
    );
  }
  
  public static function solve($QUERY) {
    /** @var DataSet $CONFIG */
    global $FACTORIES, $CONFIG;
    
    if (!PQuerySolve::isValid($QUERY)) {
      API::sendErrorResponse(PActions::SOLVE, "Invalid hashes query!");
    }
    
    // upload cracked hashes to server
    $keyspaceProgress = $QUERY[PQuerySolve::KEYSPACE_PROGRESS];
    
    $combinationProgress = floatval($QUERY[PQuerySolve::COMBINATION_PROGRESS]);      //Normalized between 1-10k
    $combinationTotal = floatval($QUERY[PQuerySolve::COMBINATION_TOTAL]);
    $speed = floatval($QUERY[PQuerySolve::SPEED]);
    $state = intval($QUERY[PQuerySolve::HASHCAT_STATE]);
    
    /**
     * This part sends a lot of DB-Requests. It may need to be optimized in the future.
     */
    $chunk = $FACTORIES::getChunkFactory()->get(intval($QUERY[PQuerySolve::CHUNK_ID]));
    if ($chunk == null) {
      API::sendErrorResponse(PActions::SOLVE, "Invalid chunk id " . intval($QUERY[PQuerySolve::CHUNK_ID]));
    }
    
    $qF = new QueryFilter(Agent::TOKEN, $QUERY[PQuerySolve::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if ($agent == null) {
      API::sendErrorResponse(PActions::SOLVE, "Invalid agent token" . $QUERY[PQuerySolve::TOKEN]);
    }
    else if ($agent->getIsActive() == 0) {
      API::sendErrorResponse(PActions::SOLVE, "Agent is marked inactive!");
    }
    else if ($chunk->getAgentId() != $agent->getId()) {
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
    $chunkCombinationStart = floor($combinationTotal / ($skip + $length) * $skip);
    $currentRelativeProgress = round(($combinationProgress - $chunkCombinationStart) / ($combinationTotal - $chunkCombinationStart) * 10000);
    $keyspaceProgress -= $skip;
    
    //if by accident the number of the combinationProgress overshoots the limit
    if ($currentRelativeProgress > 10000) {
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
    if ($chunk->getState() == DHashcatStatus::ABORTED) {
      $aborting = true;
    }
    $chunk->setState($state);
    $FACTORIES::getChunkFactory()->update($chunk);
    
    API::updateAgent($QUERY, $agent);
    
    $hlistar = Util::checkSuperHashlist($hashList);
    $hlistarIds = array();
    foreach ($hlistar as $hl) {
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
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
    
    $plainUpdates = array();
    $crackHashes = array();
    $zaps = array();
    
    for ($i = 0; $i < sizeof($crackedHashes); $i++) {
      $crackedHash = $crackedHashes[$i];
      if ($crackedHash == "") {
        continue;
      }
      $splitLine = explode($CONFIG->getVal(DConfig::FIELD_SEPARATOR), $crackedHash);
      switch ($format) {
        case DHashlistFormat::PLAIN:
          $hashFilter = new QueryFilter(Hash::HASH, $splitLine[0], "=");
          $hashListFilter = new ContainFilter(Hash::HASHLIST_ID, $hlistarIds);
          $isCrackedFilter = new QueryFilter(Hash::IS_CRACKED, 0, "=");
          $hashes = $FACTORIES::getHashFactory()->filter(array($FACTORIES::FILTER => array($isCrackedFilter, $hashFilter, $hashListFilter)));
          if (sizeof($hashes) == 0) {
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
          
          foreach ($hashes as $hash) {
            $cracked[$hash->getHashlistId()]++;
            $plainUpdates[] = new MassUpdateSet($hash->getId(), $plain);
            $crackHashes[] = $hash->getId();
            $zaps[] = new Zap(0, $hash->getHash(), time(), $agent->getId(), $hashList->getId());
          }
          
          if (sizeof($plainUpdates) >= 1000) {
            $uS1 = new UpdateSet(Hash::CHUNK_ID, $chunk->getId());
            $uS2 = new UpdateSet(Hash::IS_CRACKED, 1);
            $qF = new ContainFilter(Hash::HASH_ID, $crackHashes);
            $FACTORIES::getHashFactory()->massSingleUpdate(Hash::HASH_ID, Hash::PLAINTEXT, $plainUpdates);
            $FACTORIES::getHashFactory()->massUpdate(array($FACTORIES::UPDATE => $uS1, $FACTORIES::FILTER => $qF));
            $FACTORIES::getHashFactory()->massUpdate(array($FACTORIES::UPDATE => $uS2, $FACTORIES::FILTER => $qF));
            $FACTORIES::getZapFactory()->massSave($zaps);
            $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
            $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
            $zaps = array();
            $plainUpdates = array();
            $crackHashes = array();
          }
          break;
        case DHashlistFormat::WPA:
          // save cracked wpa password
          // result sent: 408bc12965e7ce9987cf8fb61e62a90a:aef50f22801c:987bdcf9f950:8381533406003807685881523:hashcat!
          $mac_ap = $splitLine[1];
          $mac_cli = $splitLine[2];
          $essid = $splitLine[3];
          $plain = array();
          for ($i = 4; $i < sizeof($splitLine); $i++) {
            $plain[] = $splitLine[$i];
          }
          $plain = implode($CONFIG->getVal(DConfig::FIELD_SEPARATOR), $plain);
          //TODO: if we really want to be sure that not different wpas are cracked, we need to check here to which task the client is assigned. But not sure if this is still required if we check both MACs
          $qF1 = new QueryFilter(HashBinary::ESSID, $mac_ap . $CONFIG->getVal(DConfig::FIELD_SEPARATOR) . $mac_cli . $CONFIG->getVal(DConfig::FIELD_SEPARATOR) . $essid, "=");
          $qF2 = new QueryFilter(HashBinary::IS_CRACKED, 0, "=");
          $hashes = $FACTORIES::getHashBinaryFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)));
          if (sizeof($hashes) == 0) {
            $skipped++;
          }
          foreach ($hashes as $hash) {
            $cracked[$hash->getHashlistId()]++;
            $hash->setIsCracked(1);
            $hash->setChunkId($chunk->getId());
            $hash->setPlaintext($plain);
            $FACTORIES::getHashBinaryFactory()->update($hash);
          }
          break;
        case DHashlistFormat::BINARY:
          // save binary password
          $plain = implode($CONFIG->getVal(DConfig::FIELD_SEPARATOR), $splitLine);
          $qF1 = new QueryFilter(HashBinary::HASHLIST_ID, $hashList->getId(), "=");
          $qF2 = new QueryFilter(HashBinary::IS_CRACKED, 0, "=");
          $hashes = $FACTORIES::getHashBinaryFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)));
          if (sizeof($hashes) == 0) {
            $skipped++;
          }
          foreach ($hashes as $hash) {
            $cracked[$hash->getHashlistId()]++;
            $hash->setIsCracked(1);
            $hash->setChunkId($chunk->getId());
            $hash->setPlaintext($plain);
            $FACTORIES::getHashBinaryFactory()->update($hash);
          }
          break;
      }
    }
    if ($format == DHashlistFormat::PLAIN && sizeof($plainUpdates) > 0) {
      $uS1 = new UpdateSet(Hash::CHUNK_ID, $chunk->getId());
      $uS2 = new UpdateSet(Hash::IS_CRACKED, 1);
      $qF = new ContainFilter(Hash::HASH_ID, $crackHashes);
      $FACTORIES::getHashFactory()->massSingleUpdate(Hash::HASH_ID, Hash::PLAINTEXT, $plainUpdates);
      $FACTORIES::getHashFactory()->massUpdate(array($FACTORIES::UPDATE => $uS1, $FACTORIES::FILTER => $qF));
      $FACTORIES::getHashFactory()->massUpdate(array($FACTORIES::UPDATE => $uS2, $FACTORIES::FILTER => $qF));
      $FACTORIES::getZapFactory()->massSave($zaps);
    }
    
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
    
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
      $incompleteFilter = new QueryFilter(Chunk::RPROGRESS, 10000, "<");
      $taskFilter = new QueryFilter(Chunk::TASK_ID, $taskID, "=");
      $count = $FACTORIES::getChunkFactory()->countFilter(array($FACTORIES::FILTER => array($incompleteFilter, $taskFilter)));
      if ($count == 0) {
        // this was the last incomplete chunk!
        $taskdone = true;
      }
    }
    
    if ($taskdone) {
      // task is fully dispatched and this last chunk is done, deprioritize it
      $task->setPriority(0);
      $FACTORIES::getTaskFactory()->update($task);
      
      $payload = new DataSet(array(DPayloadKeys::TASK => $task));
      NotificationHandler::checkNotifications(DNotificationType::TASK_COMPLETE, $payload);
    }
    
    $hashlists = Util::checkSuperHashlist($hashList);
    $hashlistIds = array();
    foreach ($hashlists as $hl) {
      $hashlistIds[] = $hl->getId();
    }
    $toZap = array();
    
    if ($sumCracked > 0) {
      $payload = new DataSet(array(DPayloadKeys::NUM_CRACKED => $sumCracked, DPayloadKeys::AGENT => $agent, DPayloadKeys::TASK => $task, DPayloadKeys::HASHLIST => $hashList));
      NotificationHandler::checkNotifications(DNotificationType::HASHLIST_CRACKED_HASH, $payload);
    }
    
    if ($aborting) {
      $chunk->setSpeed(0);
      $chunk->setState(DHashcatStatus::ABORTED);
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
        $chunk->setProgress($chunk->getLength());
        $chunk->setRprogress(10000);
        $chunk->setSpeed(0);
        $FACTORIES::getChunkFactory()->update($chunk);
        
        $qF = new ContainFilter(Task::HASHLIST_ID, $hashlistIds);
        $uS = new UpdateSet(TASK::PRIORITY, "0");
        $FACTORIES::getTaskFactory()->massUpdate(array($FACTORIES::UPDATE => $uS, $FACTORIES::FILTER => $qF));
        
        $payload = new DataSet(array(DPayloadKeys::HASHLIST => $hashList));
        NotificationHandler::checkNotifications(DNotificationType::HASHLIST_ALL_CRACKED, $payload);
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
        $qF1 = new ComparisonFilter(Hashlist::CRACKED, Hashlist::HASH_COUNT, "<");
        $qF2 = new ContainFilter(Hashlist::HASHLIST_ID, $hashlistIds);
        $count = $FACTORIES::getHashlistFactory()->countFilter(array($FACTORIES::FILTER => array($qF1, $qF2)));
        if ($count == 0) {
          $payload = new DataSet(array(DPayloadKeys::HASHLIST => $hashList));
          NotificationHandler::checkNotifications(DNotificationType::HASHLIST_ALL_CRACKED, $payload);
          
          $task->setPriority(0);
          $chunk->setProgress($chunk->getLength());
          $chunk->setRprogress(10000);
          
          $uS = new UpdateSet(Task::PRIORITY, 0);
          $qF = new QueryFilter(Task::HASHLIST_ID, $hashList->getId(), "=");
          $FACTORIES::getTaskFactory()->massUpdate(array($FACTORIES::FILTER => $qF, $FACTORIES::UPDATE => $uS));
          
          $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
          $FACTORIES::getAssignmentFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
          
          $FACTORIES::getChunkFactory()->update($chunk);
          $FACTORIES::getTaskFactory()->update($task);
          
          //stop agent
          API::sendResponse(array(
              PResponseSolve::ACTION => PActions::SOLVE,
              PResponseSolve::RESPONSE => PValues::SUCCESS,
              PResponseSolve::NUM_CRACKED => $sumCracked,
              PResponseSolve::NUM_SKIPPED => $skipped,
              PResponseSolve::AGENT_COMMAND => "stop"
            )
          );
        }
        $chunk->setSpeed($speed * 1000);
        $FACTORIES::getChunkFactory()->update($chunk);
        
        $agentZap = $FACTORIES::getAgentZapFactory()->get($agent->getId());
        if ($agentZap == null) {
          $agentZap = new AgentZap($agent->getId(), 0);
          $FACTORIES::getAgentZapFactory()->save($agentZap);
        }
        
        $qF1 = new ContainFilter(Zap::HASHLIST_ID, $hashlistIds);
        $qF2 = new QueryFilter(Zap::ZAP_ID, $agentZap->getLastZapId(), ">");
        $qF3 = new QueryFilter(Zap::AGENT_ID, $agent->getId(), "<>");
        $zaps = $FACTORIES::getZapFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2, $qF3)));
        foreach ($zaps as $zap) {
          if ($zap->getId() > $agentZap->getId()) {
            $agentZap->setLastZapId($zap->getId());
          }
          $toZap[] = $zap->getHash();
        }
        $agent->setLastTime(time());
        $FACTORIES::getAgentFactory()->update($agent);
        
        if ($agentZap->getLastZapId() > 0) {
          $FACTORIES::getAgentZapFactory()->update($agentZap);
        }
        
        // update hashList age for agent to this task
        break;
    }
    Util::zapCleaning();
    API::sendResponse(array(
        PResponseSolve::ACTION => PActions::SOLVE,
        PResponseSolve::RESPONSE => PValues::SUCCESS,
        PResponseSolve::NUM_CRACKED => $sumCracked,
        PResponseSolve::NUM_SKIPPED => $skipped,
        PResponseSolve::HASH_ZAPS => $toZap
      )
    );
  }
}