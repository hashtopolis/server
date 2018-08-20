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
use DBA\AgentStat;
use DBA\OrderFilter;
use DBA\ContainFilter;
use DBA\Factory;

class AgentUtils {
  /**
   * @param AgentStat $temp
   * @param Agent $agent
   * @return string
   */
  public static function getTempStatusColor($temp, $agent){
    if($temp === false){
      if(time() - $agent->getLastTime() < SConfig::getInstance()->getVal(DConfig::AGENT_TIMEOUT)){
        return "#42d4f4";
      }
      return "#CCCCCC";
    }
    $temp = $temp->getValue();
    $temp = explode(",", $temp);
    $max = 0;
    foreach($temp as $t){
      $max = ($t > $max)?$t:$max;
    }
    if($max == 0){
      return "#FF0000"; // either util 0 for all or an error occurred
    }
    if($max <= 70){
      return "#009933";
    }
    else if($max <= 80){
      return "#ff9900";
    }
    else{
      return "#800000";
    }
  }

  /**
   * @param AgentStat $util
   * @param Agent $agent
   * @return string
   */
  public static function getUtilStatusColor($util, $agent){
    if($util === false){
      if(time() - $agent->getLastTime() < SConfig::getInstance()->getVal(DConfig::AGENT_TIMEOUT)){
        return "#42d4f4";
      }
      return "#CCCCCC";
    }
    $util = $util->getValue();
    $util = explode(",", $util);
    $sum = 0;
    foreach($util as $u){
      $sum += $u;
    }
    if($sum == 0){
      return "#FF0000"; // either util 0 for all or an error occurred
    }
    $avg = $sum/sizeof($util);
    if($avg > 90){
      return "#009933";
    }
    else if($avg > 75){
      return "#ff9900";
    }
    else{
      return "#800000";
    }
  }

  public static function getGraphData($agent, $types){
    $limit = intval(SConfig::getInstance()->getVal(DConfig::AGENT_STAT_LIMIT));
    if($limit <= 0){
      $limit = 100;
    }

    $qF = new ContainFilter(AgentStat::STAT_TYPE, $types);
    $oF1 = new OrderFilter(AgentStat::TIME, "DESC");
    $oF2 = new OrderFilter(AgentStat::STAT_TYPE, "ASC LIMIT $limit");
    $entries = Factory::getAgentStatFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => [$oF1, $oF2]]);
    $xlabels = [];
    $datasets = [];
    $axes = [];
    $yLabels = [DAgentStatsType::GPU_TEMP => 'Temp (Celsius)', DAgentStatsType::GPU_UTIL => 'Util (%)'];
    $position = 'left';
    $colors = ["#FF0000", "#00CCCC", "#008000", "#CCCC00", "#FF9333", "#800080", "#0000FF"];
    foreach($entries as $entry){
      $found = false;
      foreach($axes as $axis){
        if($axis['id'] == $entry->getStatType()){
          $found = true;
          break;
        }
      }
      if(!$found){
        $axes[] = ["id" => $entry->getStatType(), 'type' => 'linear', 'position' => $position, "display" => true];
        $position = ($position == 'left')?'right':'left';
      }
      $data = explode(",", $entry->getValue());
      for($i = 0; $i < sizeof($data); $i++){
        $pos = (int)($i + sizeof($data)*array_search($entry->getStatType(), $types));
        if(!isset($datasets[$pos])){
          $datasets[$pos] = array(
            "label" => "Dev #" . ($i + 1) . " - " . $yLabels[$entry->getStatType()],
            "fill" => false,
            "lineTension" => (SConfig::getInstance()->getVal(DConfig::AGENT_STAT_TENSION) == 1)?0:1,
            "yAxisID" => $entry->getStatType(),
            "backgroundColor" => $colors[$pos%sizeof($colors)],
					  "borderColor" => $colors[$pos%sizeof($colors)],
            "data" => []
          );
        }
        if(!in_array(date(SConfig::getInstance()->getVal(DConfig::TIME_FORMAT), $entry->getTime()), $xlabels)){
          array_unshift($xlabels, date(SConfig::getInstance()->getVal(DConfig::TIME_FORMAT), $entry->getTime()));
        }
        array_unshift($datasets[$pos]['data'], (int)$data[$i]);
      }
    }
    return ["xlabels" => $xlabels, "sets" => $datasets, "axes" => $axes];
  }

  /**
   * @param int $agentId
   * @param boolean $isCpuOnly
   * @param User $user
   * @throws HTException
   */
  public static function setAgentCpu($agentId, $isCpuOnly, $user) {
    $agent = AgentUtils::getAgent($agentId, $user);
    $isCpuOnly = ($isCpuOnly) ? 1 : 0;
    $agent->setCpuOnly($isCpuOnly);
    Factory::getAgentFactory()->update($agent);
  }

  /**
   * @param int $agentId
   * @param User $user
   * @throws HTException
   */
  public static function clearErrors($agentId, $user) {
    $agent = AgentUtils::getAgent($agentId, $user);

    $qF = new QueryFilter(AgentError::AGENT_ID, $agent->getId(), "=");
    Factory::getAgentErrorFactory()->massDeletion([Factory::FILTER => $qF]);
  }

  /**
   * @param int $agentId
   * @param string $newname
   * @param User $user
   * @throws HTException
   */
  public static function rename($agentId, $newname, $user) {
    $agent = AgentUtils::getAgent($agentId, $user);
    $name = htmlentities($newname, ENT_QUOTES, "UTF-8");
    if (strlen($name) == 0) {
      throw new HTException("Agent name cannot be empty!");
    }
    $agent->setAgentName($name);
    Factory::getAgentFactory()->update($agent);
  }

  /**
   * @param int $agentId
   * @param User $user
   * @throws HTException
   */
  public static function delete($agentId, $user) {
    $agent = AgentUtils::getAgent($agentId, $user);

    Factory::getAgentFactory()->getDB()->beginTransaction();
    $name = $agent->getAgentName();

    $payload = new DataSet(array(DPayloadKeys::AGENT => $agent));
    NotificationHandler::checkNotifications(DNotificationType::DELETE_AGENT, $payload);

    if (AgentUtils::deleteDependencies($agent)) {
      Factory::getAgentFactory()->getDB()->commit();
      Util::createLogEntry("User", $user->getId(), DLogEntry::INFO, "Agent " . $name . " got deleted.");
    }
    else {
      Factory::getAgentFactory()->getDB()->rollBack();
      throw new HTException("Error occured on deletion of agent!");
    }
  }

  /**
   * @param Agent $agent
   * @return boolean
   */
  public static function deleteDependencies($agent) {
    $qF = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "=");
    Factory::getAssignmentFactory()->massDeletion([Factory::FILTER => $qF]);
    $qF = new QueryFilter(NotificationSetting::OBJECT_ID, $agent->getId(), "=");
    $notifications = Factory::getNotificationSettingFactory()->filter([Factory::FILTER => $qF]);
    foreach ($notifications as $notification) {
      if (DNotificationType::getObjectType($notification->getAction()) == DNotificationObjectType::AGENT) {
        Factory::getNotificationSettingFactory()->delete($notification);
      }
    }
    $qF = new QueryFilter(AgentError::AGENT_ID, $agent->getId(), "=");
    Factory::getAgentErrorFactory()->massDeletion([Factory::FILTER => $qF]);

    $qF = new QueryFilter(AgentZap::AGENT_ID, $agent->getId(), "=");

    Factory::getAgentZapFactory()->massDeletion([Factory::FILTER => $qF]);

    $qF = new QueryFilter(Zap::AGENT_ID, $agent->getId(), "=");
    $uS = new UpdateSet(Zap::AGENT_ID, null);
    Factory::getZapFactory()->massUpdate([Factory::FILTER => $qF, Factory::UPDATE => $uS]);

    $qF = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=");
    Factory::getAccessGroupAgentFactory()->massDeletion([Factory::FILTER => $qF]);

    $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);
    $chunkIds = array();
    foreach ($chunks as $chunk) {
      $chunkIds[] = $chunk->getId();
    }
    if (sizeof($chunks) > 0) {
      $uS = new UpdateSet(Chunk::AGENT_ID, null);
      Factory::getChunkFactory()->massUpdate([Factory::FILTER => $qF, Factory::UPDATE => $uS]);
    }
    Factory::getAgentFactory()->delete($agent);
    return true;
  }

  /**
   * @param int $agentId
   * @param int $taskId
   * @throws HTException
   */
  public static function assign($agentId, $taskId, $user) {
    $agent = AgentUtils::getAgent($agentId, $user);

    if ($taskId == 0) { // unassign
      $qF = new QueryFilter(Agent::AGENT_ID, $agent->getId(), "=");
      Factory::getAssignmentFactory()->massDeletion([Factory::FILTER => $qF]);
      if (isset($_GET['task'])) {
        header("Location: tasks.php?id=" . intval($_GET['task']));
        die();
      }
      return;
    }

    $task = Factory::getTaskFactory()->get(intval($taskId));
    if ($task == null) {
      throw new HTException("Invalid task!");
    }
    else if (!AccessUtils::agentCanAccessTask($agent, $task)) {
      throw new HTException("This agent cannot access this task - either group mismatch, or agent is not configured as Trusted to access secret tasks");
    }

    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }

    $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignments = Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF]);
    if ($task->getIsSmall() && sizeof($assignments) > 0) {
      throw new HTException("You cannot assign agent to this task as the limit of assignments is reached!");
    }

    $qF = new QueryFilter(Agent::AGENT_ID, $agent->getId(), "=");
    $assignments = Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF]);

    $benchmark = 0;
    if (sizeof($assignments) > 0) {
      for ($i = 1; $i < sizeof($assignments); $i++) { // clean up if required
        Factory::getAssignmentFactory()->delete($assignments[$i]);
      }
      $assignment = $assignments[0];
      $assignment->setTaskId($task->getId());
      $assignment->setBenchmark($benchmark);
      Factory::getAssignmentFactory()->update($assignment);
    }
    else {
      $assignment = new Assignment(0, $task->getId(), $agent->getId(), $benchmark);
      Factory::getAssignmentFactory()->save($assignment);
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
    $agent = AgentUtils::getAgent($agentId, $user);
    $ignore = intval($ignoreErrors);
    if ($ignore != 0 && $ignore != 1 && $ignore != 2) {
      throw new HTException("Invalid Ignore state!");
    }
    $agent->setIgnoreErrors($ignore);
    Factory::getAgentFactory()->update($agent);
  }

  /**
   * @param int $agentId
   * @param User $user
   * @throws HTException
   * @return Agent
   */
  public static function getAgent($agentId, $user = null) {
    $agent = Factory::getAgentFactory()->get($agentId);
    if ($agent == null) {
      throw new HTException("Invalid agent!");
    }
    else if ($user != null && !AccessUtils::userCanAccessAgent($agent, $user)) {
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
  public static function setTrusted($agentId, $trusted, $user) {
    $agent = AgentUtils::getAgent($agentId, $user);
    $trusted = ($trusted) ? 1 : 0;
    $agent->setIsTrusted($trusted);
    Factory::getAgentFactory()->update($agent);
  }

  /**
   * @param int $agentId
   * @param int|string $ownerId
   * @param User $user
   * @throws HTException
   */
  public static function changeOwner($agentId, $ownerId, $user) {
    $agent = AgentUtils::getAgent($agentId, $user);
    if ($ownerId == 0) {
      $agent->setUserId(null);
      $username = "NONE";
      Factory::getAgentFactory()->update($agent);
    }
    else if (is_numeric($ownerId)) {
      $owner = Factory::getUserFactory()->get(intval($ownerId));
      if (!$owner) {
        throw new HTException("Invalid user selected!");
      }
      $username = $user->getUsername();
      $agent->setUserId($owner->getId());
    }
    else {
      $qF = new QueryFilter(User::USERNAME, $ownerId, "=");
      $owner = Factory::getUserFactory()->filter([Factory::FILTER => $qF], true);
      if (!$owner) {
        throw new HTException("Invalid user selected!");
      }
      $username = $user->getUsername();
      $agent->setUserId($owner->getId());
    }
    Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::INFO, "Owner for agent " . $agent->getAgentName() . " was changed to " . $username);
    Factory::getAgentFactory()->update($agent);
  }

  /**
   * @param int $agentId
   * @param string $cmdParameters
   * @param User $user
   * @throws HTException
   */
  public static function changeCmdParameters($agentId, $cmdParameters, $user) {
    $agent = AgentUtils::getAgent($agentId, $user);
    if (Util::containsBlacklistedChars($cmdParameters)) {
      throw new HTException("Parameters must contain no blacklisted characters!");
    }
    $agent->setCmdPars($cmdParameters);
    Factory::getAgentFactory()->update($agent);
  }

  /**
   * @param int $agentId
   * @param boolean $active
   * @param User $user
   * @param boolean $toggle
   * @throws HTException
   */
  public static function setActive($agentId, $active, $user, $toggle = false) {
    $agent = Factory::getAgentFactory()->get($agentId);
    if ($agent == null) {
      throw new HTException("Invalid agent!");
    }
    else if (!AccessUtils::userCanAccessAgent($agent, $user)) {
      throw new HTException("No access to this agent!");
    }

    if ($toggle && $agent->getIsActive() == 1) {
      $agent->setIsActive(0);
    }
    else if ($toggle) {
      $agent->setIsActive(1);
    }
    else {
      $active = ($active) ? 1 : 0;
      $agent->setIsActive($active);
    }
    Factory::getAgentFactory()->update($agent);
  }

  /**
   * @param string $newVoucher
   */
  public static function createVoucher($newVoucher) {
    $key = htmlentities($newVoucher, ENT_QUOTES, "UTF-8");
    $voucher = new RegVoucher(0, $key, time());
    Factory::getRegVoucherFactory()->save($voucher);
  }

  /**
   * @param int|string $voucher
   * @throws HTException
   */
  public static function deleteVoucher($voucher) {
    if (is_numeric($voucher)) {
      $voucher = Factory::getRegVoucherFactory()->get($voucher);
    }
    else {
      $qF = new QueryFilter(RegVoucher::VOUCHER, $voucher, "=");
      $voucher = Factory::getRegVoucherFactory()->filter([Factory::FILTER => $qF], true);
    }
    if ($voucher == null) {
      throw new HTException("Invalid voucher!");
    }
    Factory::getRegVoucherFactory()->delete($voucher);
  }
}