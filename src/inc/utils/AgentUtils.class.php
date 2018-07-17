<?php
use DBA\Agent;
use DBA\QueryFilter;
use DBA\Assignment;
use DBA\User;
use DBA\NotificationSetting;
use DBA\AgentError;
use DBA\AgentZap;
use DBA\AccessGroupAgent;
use DBA\Chunk;
use DBA\RegVoucher;
use DBA\Zap;

class AgentUtils {
  /**
   * @param int $agentId
   * @param boolean $isCpuOnly
   * @param User $user
   */
  public static function setAgentCpu($agentId, $isCpuOnly, $user) {
    global $FACTORIES;

    $agent = AgentUtils::getAgent($agentId, $user);
    $isCpuOnly = ($isCpuOnly) ? 1 : 0;
    $agent->setCpuOnly($isCpuOnly);
    $FACTORIES::getAgentFactory()->update($agent);
  }

  /**
   * @param int $agentId 
   * @param User $user 
   * @throws HTException
   */
  public static function clearErrors($agentId, $user) {
    global $FACTORIES;

    $agent = AgentUtils::getAgent($agentId, $user);

    $qF = new QueryFilter(AgentError::AGENT_ID, $agent->getId(), "=");
    $FACTORIES::getAgentErrorFactory()->massDeletion(array($FACTORIES::FILTER => array($qF)));
  }

  /**
   * @param int $agentId
   * @param string $newname
   * @param User $user
   * @throws HTException
   */
  public static function rename($agentId, $newname, $user) {
    global $FACTORIES;

    $agent = AgentUtils::getAgent($agentId, $user);
    $name = htmlentities($newname, ENT_QUOTES, "UTF-8");
    if(strlen($name) == 0){
      throw new HTException("Agent name cannot be empty!");
    }
    $agent->setAgentName($name);
    $FACTORIES::getAgentFactory()->update($agent);
  }

  /**
   * @param int $agentId
   * @param User $user
   * @throws HTException
   */
  public static function delete($agentId, $user) {
    global $FACTORIES;

    $agent = AgentUtils::getAgent($agentId, $user);

    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $name = $agent->getAgentName();

    $payload = new DataSet(array(DPayloadKeys::AGENT => $agent));
    NotificationHandler::checkNotifications(DNotificationType::DELETE_AGENT, $payload);

    if (AgentUtils::deleteDependencies($agent)) {
      $FACTORIES::getAgentFactory()->getDB()->commit();
      Util::createLogEntry("User", $user->getId(), DLogEntry::INFO, "Agent " . $name . " got deleted.");
    }
    else {
      $FACTORIES::getAgentFactory()->getDB()->rollBack();
      throw new HTException("Error occured on deletion of agent!");
    }
  }

  /**
   * @param Agent $agent
   * @return boolean
   */
  public static function deleteDependencies($agent) {
    global $FACTORIES;

    $qF = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "=");
    $FACTORIES::getAssignmentFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $qF = new QueryFilter(NotificationSetting::OBJECT_ID, $agent->getId(), "=");
    $notifications = $FACTORIES::getNotificationSettingFactory()->filter(array($FACTORIES::FILTER => $qF));
    foreach ($notifications as $notification) {
      if (DNotificationType::getObjectType($notification->getAction()) == DNotificationObjectType::AGENT) {
        $FACTORIES::getNotificationSettingFactory()->delete($notification);
      }
    }
    $qF = new QueryFilter(AgentError::AGENT_ID, $agent->getId(), "=");
    $FACTORIES::getAgentErrorFactory()->massDeletion(array($FACTORIES::FILTER => $qF));

    $qF = new QueryFilter(AgentZap::AGENT_ID, $agent->getId(), "=");

    $FACTORIES::getAgentZapFactory()->massDeletion(array($FACTORIES::FILTER => $qF));

    $qF = new QueryFilter(Zap::AGENT_ID, $agent->getId(), "=");
    $uS = new UpdateSet(Zap::AGENT_ID, null);
    $FACTORIES::getZapFactory()->massUpdate(array($FACTORIES::FILTER => $qF, $FACTORIES::UPDATE => $uS));

    $qF = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=");
    $FACTORIES::getAccessGroupAgentFactory()->massDeletion(array($FACTORIES::FILTER => $qF));

    $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
    $chunkIds = array();
    foreach ($chunks as $chunk) {
      $chunkIds[] = $chunk->getId();
    }
    if (sizeof($chunks) > 0) {
      $uS = new UpdateSet(Chunk::AGENT_ID, null);
      $FACTORIES::getChunkFactory()->massUpdate(array($FACTORIES::FILTER => $qF, $FACTORIES::UPDATE => $uS));
    }
    $FACTORIES::getAgentFactory()->delete($agent);
    return true;
  }

  /**
   * @param int $agentId
   * @param int $taskId
   * @throws HTException
   */
  public static function assign($agentId, $taskId, $user) {
    global $FACTORIES;

    $agent = AgentUtils::getAgent($agentId, $user);

    if($taskId == 0){ // unassign
      $qF = new QueryFilter(Agent::AGENT_ID, $agent->getId(), "=");
      $FACTORIES::getAssignmentFactory()->massDeletion(array($FACTORIES::FILTER => array($qF)));
      if (isset($_GET['task'])) {
        header("Location: tasks.php?id=" . intval($_GET['task']));
        die();
      }
      return;
    }

    $task = $FACTORIES::getTaskFactory()->get(intval($taskId));
    if ($task == null) {
      throw new HTException("Invalid task!");
    }
    else if (!AccessUtils::agentCanAccessTask($agent, $task)) {
      throw new HTException("This agent cannot access this task - either group mismatch, or agent is not configured as Trusted to access secret tasks");
    }

    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if(!AccessUtils::userCanAccessTask($taskWrapper, $user)){
      throw new HTException("No access to this task!");
    }

    $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignments = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => $qF));
    if ($task->getIsSmall() && sizeof($assignments) > 0) {
      throw new HTException("You cannot assign agent to this task as the limit of assignments is reached!");
    }

    $qF = new QueryFilter(Agent::AGENT_ID, $agent->getId(), "=");
    $assignments = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => array($qF)));

    $benchmark = 0;
    if (sizeof($assignments) > 0) {
      for ($i = 1; $i < sizeof($assignments); $i++) { // clean up if required
        $FACTORIES::getAssignmentFactory()->delete($assignments[$i]);
      }
      $assignment = $assignments[0];
      $assignment->setTaskId($task->getId());
      $assignment->setBenchmark($benchmark);
      $FACTORIES::getAssignmentFactory()->update($assignment);
    }
    else {
      $assignment = new Assignment(0, $task->getId(), $agent->getId(), $benchmark);
      $FACTORIES::getAssignmentFactory()->save($assignment);
    }
    if (isset($_GET['task'])) {
      header("Location: tasks.php?id=" . intval($_GET['task']));
      die();
    }
  }

  /**
   * @param int $agentId
   * @param int $ignoreErrors
   * @param User $user
   * @throws HTException
   */
  public static function changeIgnoreErrors($agentId, $ignoreErrors, $user) {
    global $FACTORIES;

    $agent = AgentUtils::getAgent($agentId, $user);
    $ignore = intval($ignoreErrors);
    if ($ignore != 0 && $ignore != 1 && $ignore != 2) {
      throw new HTException("Invalid Ignore state!");
    }
    $agent->setIgnoreErrors($ignore);
    $FACTORIES::getAgentFactory()->update($agent);
  }

  /**
   * @param int $agentId
   * @param User $user
   * @throws HTException
   * @return Agent
   */
  public static function getAgent($agentId, $user){
    global $FACTORIES;

    $agent = $FACTORIES::getAgentFactory()->get($agentId);
    if ($agent == null) {
      throw new HTException("Invalid agent!");
    }
    else if(!AccessUtils::userCanAccessAgent($agent, $user)){
      throw new HTException("No access to this agent!");
    }
    return $agent;
  }

  /**
   * @param int $agentId
   * @param boolean $trusted
   * @param User $user
   * @throws HTException
   */
  public static function setTrusted($agentId, $trusted, $user){
    global $FACTORIES;

    $agent = AgentUtils::getAgent($agentId, $user);
    $trusted = ($trusted)?1:0;
    $agent->setIsTrusted($trusted);
    $FACTORIES::getAgentFactory()->update($agent);
  }

  /**
   * @param int $agentId
   * @param int|string $ownerId
   * @param User $user
   * @throws HTException
   */
  public static function changeOwner($agentId, $ownerId, $user) {
    global $FACTORIES;

    $agent = AgentUtils::getAgent($agentId, $user);
    if ($ownerId == 0) {
      $agent->setUserId(null);
      $username = "NONE";
      $FACTORIES::getAgentFactory()->update($agent);
    }
    else if(is_numeric($ownerId)){
      $owner = $FACTORIES::getUserFactory()->get(intval($ownerId));
      if (!$owner) {
        throw new HTException("Invalid user selected!");
      }
      $username = $user->getUsername();
      $agent->setUserId($owner->getId());
    }
    else {
      $qF = new QueryFilter(User::USERNAME, $ownerId, "=");
      $owner = $FACTORIES::getUserFactory()->filter(array($FACTORIES::FILTER => $qF), true);
      if (!$owner) {
        throw new HTException("Invalid user selected!");
      }
      $username = $user->getUsername();
      $agent->setUserId($owner->getId());
    }
    Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::INFO, "Owner for agent " . $agent->getAgentName() . " was changed to " . $username);
    $FACTORIES::getAgentFactory()->update($agent);
  }

  /**
   * @param int $agentId
   * @param string $cmdParameters
   * @param User $user
   * @throws HTException
   */
  public static function changeCmdParameters($agentId, $cmdParameters, $user) {
    global $FACTORIES;

    $agent = AgentUtils::getAgent($agentId, $user);
    if (Util::containsBlacklistedChars($cmdParameters)) {
      throw new HTException("Parameters must contain no blacklisted characters!");
    }
    $agent->setCmdPars($cmdParameters);
    $FACTORIES::getAgentFactory()->update($agent);
  }

  /**
   * @param int $agentId
   * @param boolean $active
   * @param User $user
   * @param boolean $toggle
   * @throws HTException
   */
  public static function setActive($agentId, $active, $user, $toggle = false) {
    global $FACTORIES;

    $agent = $FACTORIES::getAgentFactory()->get($agentId);
    if ($agent == null) {
      throw new HTException("Invalid agent!");
    }
    else if(!AccessUtils::userCanAccessAgent($agent, $user)){
      throw new HTException("No access to this agent!");
    }

    if ($toggle && $agent->getIsActive() == 1) {
      $agent->setIsActive(0);
    }
    else if($toggle) {
      $agent->setIsActive(1);
    }
    else{
      $active = ($active)?1:0;
      $agent->setIsActive($active);
    }
    $FACTORIES::getAgentFactory()->update($agent);
  }

  /**
   * @param string $newVoucher
   */
  public static function createVoucher($newVoucher) {
    global $FACTORIES;

    $key = htmlentities($newVoucher, ENT_QUOTES, "UTF-8");
    $voucher = new RegVoucher(0, $key, time());
    $FACTORIES::getRegVoucherFactory()->save($voucher);
  }

  /**
   * @param int|string $voucher
   * @throws HTException
   */
  public static function deleteVoucher($voucher) {
    global $FACTORIES;

    if(is_numeric($voucher)){
      $voucher = $FACTORIES::getRegVoucherFactory()->get($voucher);
    }
    else{
      $qF = new QueryFilter(RegVoucher::VOUCHER, $voucher, "=");
      $voucher = $FACTORIES::getRegVoucherFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    }
    if($voucher == null){
      throw new HTException("Invalid voucher!");
    }
    $FACTORIES::getRegVoucherFactory()->delete($voucher);
  }
}