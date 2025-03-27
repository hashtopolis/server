<?php

use DBA\Agent;
use DBA\AgentZap;
use DBA\Assignment;
use DBA\Chunk;
use DBA\ComparisonFilter;
use DBA\ContainFilter;
use DBA\Hash;
use DBA\HashBinary;
use DBA\Hashlist;
use DBA\LikeFilterInsensitive;
use DBA\QueryFilter;
use DBA\Task;
use DBA\Zap;
use DBA\QueryFilterWithNull;
use DBA\TaskDebugOutput;
use DBA\AgentStat;
use DBA\Factory;
use DBA\TaskWrapper;
use DBA\Speed;

class APISendProgress extends APIBasic {
  public function execute($QUERY = array()) {
    if (!PQuerySendProgress::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::SEND_PROGRESS, "Invalid progress query!");
    }
    $this->checkToken(PActions::SEND_PROGRESS, $QUERY);
    $this->updateAgent(PActions::SEND_PROGRESS);
    
    // upload cracked hashes to server
    $keyspaceProgress = $QUERY[PQuerySendProgress::KEYSPACE_PROGRESS];
    $relativeProgress = intval($QUERY[PQuerySendProgress::RELATIVE_PROGRESS]);//Normalized between 1-10k
    $speed = intval($QUERY[PQuerySendProgress::SPEED]);
    $state = intval($QUERY[PQuerySendProgress::HASHCAT_STATE]);
    
    DServerLog::log(DServerLog::TRACE, "Agent sending progress", [$this->agent]);
    
    $chunk = Factory::getChunkFactory()->get(intval($QUERY[PQuerySendProgress::CHUNK_ID]));
    if ($chunk == null) {
      $this->sendErrorResponse(PActions::SEND_PROGRESS, "Invalid chunk id " . intval($QUERY[PQuerySendProgress::CHUNK_ID]));
    }
    else if ($chunk->getAgentId() != $this->agent->getId()) {
      $this->sendErrorResponse(PActions::SEND_PROGRESS, "You are not assigned to this chunk");
    }
    else if ($this->agent->getIsActive() == 0) {
      Factory::getChunkFactory()->set($chunk, Chunk::SPEED, 0);
      $this->sendErrorResponse(PActions::SEND_PROGRESS, "Agent is marked inactive!");
    }
    
    DServerLog::log(DServerLog::TRACE, "Agent is assigned to this chunk and active", [$this->agent, $chunk]);
    
    $task = Factory::getTaskFactory()->get($chunk->getTaskId());
    if ($task == null) {
      DServerLog::log(DServerLog::ERROR, "Inconsistency between chunk and task!", [$this->agent, $chunk]);
      $this->sendErrorResponse(PActions::SEND_PROGRESS, "No task exists for the given chunk");
    }
    else if ($task->getIsArchived() == 1) {
      $this->sendErrorResponse(PActions::SEND_PROGRESS, "Task is archived, no work to do");
    }
    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if ($taskWrapper == null) {
      DServerLog::log(DServerLog::ERROR, "Inconsistency between task and taskWrapper!", [$this->agent, $task]);
      $this->sendErrorResponse(PActions::SEND_PROGRESS, "Inconsistency error on taskWrapper");
    }
    
    DServerLog::log(DServerLog::TRACE, "Agent working on valid task", [$this->agent, $task]);
    
    $hashlist = Factory::getHashlistFactory()->get($taskWrapper->getHashlistId());
    if ($hashlist == null) {
      DServerLog::log(DServerLog::ERROR, "Task is not having a valid hashlist!", [$this->agent, $task]);
      $this->sendErrorResponse(PActions::SEND_PROGRESS, "The given task does not have a corresponding hashlist!");
    }
    $totalHashlist = $hashlist;
    $isSuperhashlist = false;
    if ($hashlist->getFormat() == DHashlistFormat::SUPERHASHLIST) {
      $isSuperhashlist = true;
    }
    $hashlists = Util::checkSuperHashlist($hashlist);
    foreach ($hashlists as $hashlist) {
      if ($hashlist->getIsSecret() > $this->agent->getIsTrusted()) {
        DServerLog::log(DServerLog::TRACE, "For some reason agent was working on a hashlist he is not allowed to (probabily permission change)", [$this->agent, $task, $hashlist]);
        $this->sendErrorResponse(PActions::SEND_PROGRESS, "Unknown Error. The API does not trust you with more information");
      }
    }
    
    DServerLog::log(DServerLog::TRACE, "Agent working on correct hashlist(s)", [$this->agent, $totalHashlist]);
    
    $dataTime = time();
    if (isset($QUERY[PQuerySendProgress::GPU_TEMP])) {
      for ($i = 0; $i < sizeof($QUERY[PQuerySendProgress::GPU_TEMP]); $i++) {
        if (!is_numeric($QUERY[PQuerySendProgress::GPU_TEMP][$i]) || $QUERY[PQuerySendProgress::GPU_TEMP][$i] <= 0) {
          unset($QUERY[PQuerySendProgress::GPU_TEMP][$i]);
        }
      }
      if (sizeof($QUERY[PQuerySendProgress::GPU_TEMP]) > 0) {
        $data = implode(",", $QUERY[PQuerySendProgress::GPU_TEMP]);
        $agentStat = new AgentStat(null, $this->agent->getId(), DAgentStatsType::GPU_TEMP, $dataTime, $data);
        Factory::getAgentStatFactory()->save($agentStat);
      }
    }
    if (isset($QUERY[PQuerySendProgress::GPU_UTIL])) {
      for ($i = 0; $i < sizeof($QUERY[PQuerySendProgress::GPU_UTIL]); $i++) {
        if (!is_numeric($QUERY[PQuerySendProgress::GPU_UTIL][$i]) || $QUERY[PQuerySendProgress::GPU_UTIL][$i] < 0) {
          unset($QUERY[PQuerySendProgress::GPU_UTIL][$i]);
        }
      }
      if (sizeof($QUERY[PQuerySendProgress::GPU_UTIL]) > 0) {
        $data = implode(",", $QUERY[PQuerySendProgress::GPU_UTIL]);
        $agentStat = new AgentStat(null, $this->agent->getId(), DAgentStatsType::GPU_UTIL, $dataTime, $data);
        Factory::getAgentStatFactory()->save($agentStat);
      }
    }
    if (isset($QUERY[PQuerySendProgress::CPU_UTIL])) {
      for ($i = 0; $i < sizeof($QUERY[PQuerySendProgress::CPU_UTIL]); $i++) {
        if (!is_numeric($QUERY[PQuerySendProgress::CPU_UTIL][$i]) || $QUERY[PQuerySendProgress::CPU_UTIL][$i] < 0) {
          unset($QUERY[PQuerySendProgress::CPU_UTIL][$i]);
        }
      }
      if (sizeof($QUERY[PQuerySendProgress::CPU_UTIL]) > 0) {
        $data = implode(",", $QUERY[PQuerySendProgress::CPU_UTIL]);
        $agentStat = new AgentStat(null, $this->agent->getId(), DAgentStatsType::CPU_UTIL, $dataTime, $data);
        Factory::getAgentStatFactory()->save($agentStat);
      }
    }
    
    // agent is assigned to this chunk (not necessarily task!)
    // it can be already assigned to other task, but is still computing this chunk until it realizes it
    $skip = $chunk->getSkip();
    $length = $chunk->getLength();
    $taskID = $task->getId();
    
    //if by accident the number of the combinationProgress overshoots the limit
    if ($relativeProgress > 10000) {
      $relativeProgress = 10000;
    }
    if ($keyspaceProgress > $length + $skip) {
      $keyspaceProgress = $length + $skip;
    }
    
    /*
     * Save Debug output if provided
     */
    if (isset($QUERY[PQuerySendProgress::DEBUG_OUTPUT])) {
      $lines = $QUERY[PQuerySendProgress::DEBUG_OUTPUT];
      $taskDebugOutputs = [];
      foreach ($lines as $line) {
        $taskDebugOutputs[] = new TaskDebugOutput(null, $chunk->getTaskId(), $line);
      }
      if (sizeof($taskDebugOutputs) > 0) {
        Factory::getTaskDebugOutputFactory()->massSave($taskDebugOutputs);
      }
    }
    
    /*
     * Save chunk updates
     */
    $aborting = false;
    if ($chunk->getState() == DHashcatStatus::ABORTED) {
      DServerLog::log(DServerLog::TRACE, "Chunk was aborted, we need to stop afterwards", [$this->agent]);
      $aborting = true;
    }
    Factory::getChunkFactory()->mset($chunk, [
        Chunk::PROGRESS => $relativeProgress,
        Chunk::CHECKPOINT => $keyspaceProgress,
        Chunk::SOLVE_TIME => time(),
        Chunk::STATE => $state
      ]
    );
    DServerLog::log(DServerLog::TRACE, "Progress updated chunk", [$this->agent, $chunk]);
    
    $format = $hashlists[0]->getFormat();
    
    // reset values
    $skipped = 0;
    $cracked = array();
    foreach ($hashlists as $hashlist) {
      $cracked[$hashlist->getId()] = 0;
    }
    
    // process solved hashes, should there be any
    $crackedHashes = $QUERY[PQuerySendProgress::CRACKED_HASHES];
    Factory::getAgentFactory()->getDB()->beginTransaction();
    
    $plainUpdates = [];
    $crackPosUpdates = [];
    $crackHashes = [];
    $timeUpdates = [];
    $zaps = [];
    
    $isNewWPA = false;
    foreach ($hashlists as $hashlist) {
      if ($hashlist->getHashTypeId() == 22000) {
        $isNewWPA = true;
        break;
      }
    }
    
    for ($i = 0; $i < sizeof($crackedHashes); $i++) {
      // hash[:salt]:plain:hex_plain:crack_pos
      $crackedHash = $crackedHashes[$i];
      if (!is_array($crackedHash) && $crackedHash == "") {
        continue;
      }
      else if (!is_array($crackedHash)) {
        // this is here for compatibility with older client versions
        $splitLine = explode(SConfig::getInstance()->getVal(DConfig::FIELD_SEPARATOR), $crackedHash);
        $splitLine[] = ''; // for hex plain
        $splitLine[] = -1; // for crack pos
      }
      else {
        $splitLine = $crackedHash;
      }
      switch ($format) {
        case DHashlistFormat::PLAIN:
          if ($isNewWPA) { // special 22000 handle, hashcat prints it completely different than the input looks like
            $split = explode(":", $splitLine[0]);
            if (sizeof($split) == 4) { // this format was sent up to (and including) release 5.1.0 for -m 2500
              $split[3] = Util::strToHex($split[3]);
              $identifier = "WPA*%*" . implode("*", $split) . "%";
            }
            $qF1 = new LikeFilterInsensitive(Hash::HASH, $identifier);
          }
          else { // we use the exact match for all other hashes to avoid performance loss
            $qF1 = new QueryFilter(Hash::HASH, $splitLine[0], "=");
          }
          
          $qF2 = new ContainFilter(Hash::HASHLIST_ID, Util::arrayOfIds($hashlists));
          $qF3 = new QueryFilter(Hash::IS_CRACKED, 0, "=");
          $hashes = Factory::getHashFactory()->filter([Factory::FILTER => [$qF1, $qF2, $qF3]]);
          if (sizeof($hashes) == 0) {
            //This can happen if agent rebuild the hash incorrectly
            //Log the skipped hash so that admin can spot this false negative
            $logMessage = "Hash has been cracked but skipped! This happened while cracking hashlist with ID: "
              . $hashlist->getId() . " during chunk with ID: " . $chunk->getId() . " This happens when the agent returns
               a cracked hash that does not exist in the database. This can happen when hashcat malforms the hash.";
            Util::createLogEntry(DLogEntryIssuer::API, $this->agent->getToken(), DLogEntry::FATAL, $logMessage);
            DServerLog::log(DServerLog::FATAL, $logMessage);

            $skipped++;
            break;
          }
          else if (sizeof($splitLine) == 5) {
            $plain = $splitLine[2]; // if hash is salted
            $crackPos = $splitLine[4];
          }
          else {
            $plain = $splitLine[1];
            $crackPos = $splitLine[3];
          }
          
          foreach ($hashes as $hash) {
            $cracked[$hash->getHashlistId()]++;
            $plainUpdates[] = new MassUpdateSet($hash->getId(), $plain);
            $crackPosUpdates[] = new MassUpdateSet($hash->getId(), $crackPos);
            $timeUpdates[] = new MassUpdateSet($hash->getId(), time());
            $crackHashes[] = $hash->getId();
            $zaps[] = new Zap(null, $hash->getHash(), time(), $this->agent->getId(), $totalHashlist->getId());
          }
          
          if (sizeof($plainUpdates) >= 1000) {
            $uS1 = new UpdateSet(Hash::CHUNK_ID, $chunk->getId());
            $uS2 = new UpdateSet(Hash::IS_CRACKED, 1);
            $qF = new ContainFilter(Hash::HASH_ID, $crackHashes);
            Factory::getHashFactory()->massSingleUpdate(Hash::HASH_ID, Hash::PLAINTEXT, $plainUpdates);
            Factory::getHashFactory()->massSingleUpdate(Hash::HASH_ID, Hash::CRACK_POS, $crackPosUpdates);
            Factory::getHashFactory()->massSingleUpdate(Hash::HASH_ID, Hash::TIME_CRACKED, $timeUpdates);
            Factory::getHashFactory()->massUpdate([Factory::UPDATE => $uS1, Factory::FILTER => $qF]);
            Factory::getHashFactory()->massUpdate([Factory::UPDATE => $uS2, Factory::FILTER => $qF]);
            Factory::getZapFactory()->massSave($zaps);
            Factory::getAgentFactory()->getDB()->commit();
            Factory::getAgentFactory()->getDB()->beginTransaction();
            $zaps = array();
            $plainUpdates = array();
            $crackHashes = array();
          }
          break;
        case DHashlistFormat::WPA:
          // save cracked wpa / pmkid password
          // possible results:
          // HCCAPX:        [a895f7d62ccc3e892fa9e9f9146232c1:]aef50f22801c:987bdcf9f950:8381533406003807685881523	hashcat!	6861736863617421	12
          // PMKID (16800): 4604ba734d4e:89acf0e761f4:$HEX[ed487162465a774bfba60eb603a39f3a]        hashcat!        6861736863617421        31
          // PMKID (16801): 4604ba734d4e:89acf0e761f4       5b13d4babb3714ccc62c9f71864bc984efd6a55f237c7a87fc2151e1ca658a9-        3562313364346261626233373134636363363263396637313836346263393834656664366135356632333763376138376663323135316531636136353861392d   14
          $split = explode(":", $splitLine[0]);
          if (sizeof($split) == 4) { // this format was sent up to (and including) release 5.1.0 for -m 2500
            $mac_ap = $split[1];
            $mac_cli = $split[2];
            $essid = $split[3];
          }
          else if (sizeof($split) == 3) { // this format is used in the current state of hashcat for -m 2500,16800
            $mac_ap = $split[0];
            $mac_cli = $split[1];
            $essid = $split[2];
          }
          else { // this format is used for -m 16801
            $mac_ap = $split[0];
            $mac_cli = $split[1];
          }
          if (Util::startsWith($essid, '$HEX[') && Util::endsWith($essid, "]") && strlen($essid) % 2 == 0) {
            $essid = substr($essid, 5, strlen($essid) - 6);
          }
          else if (sizeof($split) < 4) { // for the new formats, if the SSID is not given in hex, we need to convert it back, as the input is always in hex
            $essid = Util::strToHex($essid);
          }
          $identification = $mac_ap . SConfig::getInstance()->getVal(DConfig::FIELD_SEPARATOR) . $mac_cli;
          if (sizeof($split) > 2) {
            $identification .= SConfig::getInstance()->getVal(DConfig::FIELD_SEPARATOR) . $essid;
          }
          $plain = $splitLine[1];
          $crackPos = $splitLine[3];
          $qF1 = new QueryFilter(HashBinary::ESSID, $identification, "=");
          $qF2 = new QueryFilter(HashBinary::IS_CRACKED, 0, "=");
          $hashes = Factory::getHashBinaryFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
          if (sizeof($hashes) == 0) {
            $skipped++;
          }
          foreach ($hashes as $hash) {
            $cracked[$hash->getHashlistId()]++;
            $hash->setIsCracked(1);
            $hash->setChunkId($chunk->getId());
            $hash->setPlaintext($plain);
            $hash->setCrackPos($crackPos);
            $hash->setTimeCracked(time());
            Factory::getHashBinaryFactory()->update($hash);
          }
          break;
        case DHashlistFormat::BINARY:
          // save binary password
          // result sent: ..\hashcat_luks_testfiles\luks_tests\hashcat_ripemd160_aes_cbc-essiv_128.luks:hashcat:68617368636174:12
          $plain = $splitLine[1];
          $crackPos = $splitLine[3];
          $qF1 = new QueryFilter(HashBinary::HASHLIST_ID, $totalHashlist->getId(), "=");
          $qF2 = new QueryFilter(HashBinary::IS_CRACKED, 0, "=");
          $hashes = Factory::getHashBinaryFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
          if (sizeof($hashes) == 0) {
            $skipped++;
          }
          foreach ($hashes as $hash) {
            $cracked[$hash->getHashlistId()]++;
            $hash->setIsCracked(1);
            $hash->setChunkId($chunk->getId());
            $hash->setPlaintext($plain);
            $hash->setCrackPos($crackPos);
            $hash->setTimeCracked(time());
            Factory::getHashBinaryFactory()->update($hash);
          }
          break;
      }
    }
    if ($format == DHashlistFormat::PLAIN && sizeof($plainUpdates) > 0) {
      $uS1 = new UpdateSet(Hash::CHUNK_ID, $chunk->getId());
      $uS2 = new UpdateSet(Hash::IS_CRACKED, 1);
      $qF = new ContainFilter(Hash::HASH_ID, $crackHashes);
      Factory::getHashFactory()->massSingleUpdate(Hash::HASH_ID, Hash::PLAINTEXT, $plainUpdates);
      Factory::getHashFactory()->massSingleUpdate(Hash::HASH_ID, Hash::CRACK_POS, $crackPosUpdates);
      Factory::getHashFactory()->massSingleUpdate(Hash::HASH_ID, Hash::TIME_CRACKED, $timeUpdates);
      Factory::getHashFactory()->massUpdate([Factory::UPDATE => $uS1, Factory::FILTER => $qF]);
      Factory::getHashFactory()->massUpdate([Factory::UPDATE => $uS2, Factory::FILTER => $qF]);
      Factory::getZapFactory()->massSave($zaps);
    }
    
    Factory::getAgentFactory()->getDB()->commit();
    
    //insert #Cracked hashes and update in hashlist how many hashes were cracked
    Factory::getAgentFactory()->getDB()->beginTransaction();
    $sumCracked = 0;
    foreach ($cracked as $listId => $cracks) {
      $list = Factory::getHashlistFactory()->get($listId);
      Factory::getHashlistFactory()->inc($list, Hashlist::CRACKED, $cracks);
      
      if (!$isSuperhashlist) {
        // check if it is part of one or more superhashlists and if yes, update the count there as well
        $superHashlists = Util::getParentSuperHashlists($list);
        foreach ($superHashlists as $superHashlist) {
          Factory::getHashlistFactory()->inc($superHashlist, Hashlist::CRACKED, $cracks);
        }
      }
      
      $sumCracked += $cracks;
    }
    Factory::getChunkFactory()->inc($chunk, Chunk::CRACKED, $sumCracked);
    if ($isSuperhashlist) {
      // if it's a superhashlist, we need to update the count for the superhashlist as well
      $hashlist = Factory::getHashlistFactory()->get($taskWrapper->getHashlistId());
      Factory::getHashlistFactory()->inc($hashlist, Hashlist::CRACKED, $sumCracked);
    }
    Factory::getAgentFactory()->getDB()->commit();
    
    DServerLog::log(DServerLog::TRACE, "Updated with received cracks", [$this->agent, $chunk]);
    
    if ($chunk->getState() == DHashcatStatus::STATUS_ABORTED_RUNTIME) {
      // the chunk was manually interrupted
      Factory::getChunkFactory()->set($chunk, Chunk::STATE, DHashcatStatus::ABORTED);
      DServerLog::log(DServerLog::TRACE, "Chunk was manually interrupted", [$this->agent]);
      $this->sendErrorResponse(PActions::SEND_PROGRESS, "Chunk was manually interrupted.");
    }
    /** Check if the task is done */
    $taskdone = false;
    if ($relativeProgress == 10000 && $task->getKeyspaceProgress() == $task->getKeyspace()) {
      // chunk is done and the task has been fully dispatched
      $incompleteFilter = new QueryFilter(Chunk::PROGRESS, 10000, "<");
      $taskFilter = new QueryFilter(Chunk::TASK_ID, $taskID, "=");
      $count = Factory::getChunkFactory()->countFilter([Factory::FILTER => [$incompleteFilter, $taskFilter]]);
      $incompleteFilter = new QueryFilter(Chunk::PROGRESS, null, "=");
      $count += Factory::getChunkFactory()->countFilter([Factory::FILTER => [$incompleteFilter, $taskFilter]]);
      if ($count == 0) {
        // this was the last incomplete chunk!
        $taskdone = true;
        DServerLog::log(DServerLog::INFO, "Chunk is the last one and is completed and keyspace is reached", [$this->agent, $task, $chunk]);
      }
    }
    
    if ($taskdone) {
      // task is fully dispatched and this last chunk is done, deprioritize it
      Factory::getTaskFactory()->set($task, Task::PRIORITY, 0);
      
      if ($taskWrapper->getTaskType() == DTaskTypes::SUPERTASK) {
        // check if the task wrapper is a supertask and is completed
        if (Util::checkTaskWrapperCompleted($taskWrapper)) {
          Factory::getTaskWrapperFactory()->set($taskWrapper, TaskWrapper::PRIORITY, 0);
        }
      }
      else {
        Factory::getTaskWrapperFactory()->set($taskWrapper, TaskWrapper::PRIORITY, 0);
      }
      DServerLog::log(DServerLog::TRACE, "As task is done, finished it and updated taskWrapper", [$this->agent, $task, $taskWrapper]);
      
      $payload = new DataSet(array(DPayloadKeys::TASK => $task));
      NotificationHandler::checkNotifications(DNotificationType::TASK_COMPLETE, $payload);
    }
    
    $toZap = array();
    
    if ($sumCracked > 0) {
      $payload = new DataSet(array(DPayloadKeys::NUM_CRACKED => $sumCracked, DPayloadKeys::AGENT => $this->agent, DPayloadKeys::TASK => $task, DPayloadKeys::HASHLIST => $totalHashlist));
      NotificationHandler::checkNotifications(DNotificationType::HASHLIST_CRACKED_HASH, $payload);
      
      Factory::getTaskWrapperFactory()->inc($taskWrapper, TaskWrapper::CRACKED, $sumCracked);
    }
    
    if ($aborting) {
      Factory::getChunkFactory()->mset($chunk, [Chunk::SPEED => 0, Chunk::STATE => DHashcatStatus::ABORTED]);
      DServerLog::log(DServerLog::TRACE, "From earlier setting, chunk needed to be aborted.", [$this->agent, $chunk]);
      $this->sendErrorResponse(PActions::SEND_PROGRESS, "Chunk was aborted!");
    }
    
    switch ($state) {
      case DHashcatStatus::EXHAUSTED:
        // the chunk has finished (exhausted)
        Factory::getChunkFactory()->mset($chunk, [Chunk::SPEED => 0, Chunk::PROGRESS => 10000, Chunk::CHECKPOINT => $chunk->getSkip() + $chunk->getLength()]);
        DServerLog::log(DServerLog::TRACE, "Chunk is exhausted (cracker status)", [$this->agent, $chunk]);
        break;
      case DHashcatStatus::CRACKED:
        // the chunk has finished (cracked whole hashList)
        // de-prioritize all tasks and un-assign all agents
        Factory::getChunkFactory()->mset($chunk, [Chunk::CHECKPOINT => $chunk->getSkip() + $chunk->getLength(), Chunk::PROGRESS => 10000, Chunk::SPEED => 0]);
        DServerLog::log(DServerLog::TRACE, "Last hash was cracked (cracker status)", [$this->agent, $chunk]);
        
        TaskUtils::depriorizeAllTasks($hashlists);
        TaskUtils::unassignAllAgents($hashlists);
        DServerLog::log(DServerLog::TRACE, "Depriorized all tasks of the hashlist and unassigned all agents", [$this->agent, $totalHashlist]);
        
        $payload = new DataSet(array(DPayloadKeys::HASHLIST => $totalHashlist));
        NotificationHandler::checkNotifications(DNotificationType::HASHLIST_ALL_CRACKED, $payload);
        break;
      case DHashcatStatus::ABORTED:
      case DHashcatStatus::QUIT:
        // the chunk was aborted or quit
        Factory::getChunkFactory()->set($chunk, Chunk::SPEED, 0);
        $this->sendErrorResponse(PActions::SEND_PROGRESS, "Chunk was aborted!");
        break;
      case DHashcatStatus::RUNNING:
      default:
        // the chunk isn't finished yet, we will send zaps
        $qF1 = new ComparisonFilter(Hashlist::CRACKED, Hashlist::HASH_COUNT, "<");
        $qF2 = new ContainFilter(Hashlist::HASHLIST_ID, Util::arrayOfIds($hashlists));
        $count = Factory::getHashlistFactory()->countFilter([Factory::FILTER => [$qF1, $qF2]]);
        if ($count == 0) {
          $payload = new DataSet(array(DPayloadKeys::HASHLIST => $totalHashlist));
          NotificationHandler::checkNotifications(DNotificationType::HASHLIST_ALL_CRACKED, $payload);
          DServerLog::log(DServerLog::TRACE, "Agent still is running, but all hashes got cracked (all agents together), stop it", [$this->agent]);
          
          Factory::getChunkFactory()->mset($chunk, [Chunk::CHECKPOINT => $chunk->getSkip() + $chunk->getLength(), Chunk::PROGRESS => 10000, Chunk::SPEED => 0]);
          TaskUtils::depriorizeAllTasks($hashlists);
          
          $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
          Factory::getAssignmentFactory()->massDeletion([Factory::FILTER => $qF]);
          
          Factory::getTaskFactory()->set($task, Task::PRIORITY, 0);
          DServerLog::log(DServerLog::TRACE, "Depriorized all tasks and updated", [$this->agent, $task, $chunk, $totalHashlist]);
          
          //stop agent
          $this->sendResponse(array(
              PResponseSendProgress::ACTION => PActions::SEND_PROGRESS,
              PResponseSendProgress::RESPONSE => PValues::SUCCESS,
              PResponseSendProgress::NUM_CRACKED => $sumCracked,
              PResponseSendProgress::NUM_SKIPPED => $skipped,
              PResponseSendProgress::AGENT_COMMAND => "stop"
            )
          );
        }
        Factory::getChunkFactory()->set($chunk, Chunk::SPEED, $speed);
        
        // save speed in history
        if ($speed > 0) {
          $s = new Speed(null, $this->agent->getId(), $task->getId(), $speed, time());
          Factory::getSpeedFactory()->save($s);
        }
        
        $qF = new QueryFilter(AgentZap::AGENT_ID, $this->agent->getId(), "=");
        $agentZap = Factory::getAgentZapFactory()->filter([Factory::FILTER => $qF], true);
        if ($agentZap == null) {
          $agentZap = new AgentZap(null, $this->agent->getId(), null);
          Factory::getAgentZapFactory()->save($agentZap);
        }
        
        $qF1 = new ContainFilter(Zap::HASHLIST_ID, Util::arrayOfIds($hashlists));
        $qF2 = new QueryFilter(Zap::ZAP_ID, ($agentZap->getLastZapId() == null) ? 0 : $agentZap->getLastZapId(), ">");
        $qF3 = new QueryFilterWithNull(Zap::AGENT_ID, $this->agent->getId(), "<>", true);
        $zaps = Factory::getZapFactory()->filter([Factory::FILTER => [$qF1, $qF2, $qF3]]);
        foreach ($zaps as $zap) {
          if ($zap->getId() > $agentZap->getId()) {
            $agentZap->setLastZapId($zap->getId());
          }
          $toZap[] = $zap->getHash();
        }
        Factory::getAgentFactory()->set($this->agent, Agent::LAST_TIME, time());
        if ($agentZap->getLastZapId() > 0) {
          Factory::getAgentZapFactory()->update($agentZap);
        }
        
        DServerLog::log(DServerLog::TRACE, "Checked zaps and sending new ones to agent", [$this->agent, $zaps]);
        break;
    }
    Util::zapCleaning();
    Util::agentStatCleaning();
    $this->sendResponse(array(
        PResponseSendProgress::ACTION => PActions::SEND_PROGRESS,
        PResponseSendProgress::RESPONSE => PValues::SUCCESS,
        PResponseSendProgress::NUM_CRACKED => $sumCracked,
        PResponseSendProgress::NUM_SKIPPED => $skipped,
        PResponseSendProgress::HASH_ZAPS => $toZap
      )
    );
  }
}