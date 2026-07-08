<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\inc\DataSet;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\models\NotificationSetting;
use Hashtopolis\dba\models\AgentError;
use Hashtopolis\dba\models\AgentZap;
use Hashtopolis\dba\models\AccessGroupAgent;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\models\RegVoucher;
use Hashtopolis\dba\models\Zap;
use Hashtopolis\dba\models\AgentStat;
use Hashtopolis\dba\OrderFilter;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\UpdateSet;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\dba\models\Speed;
use Hashtopolis\inc\apiv2\error\HttpConflict;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\defines\DAgentStatsType;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DLogEntry;
use Hashtopolis\inc\defines\DLogEntryIssuer;
use Hashtopolis\inc\defines\DNotificationObjectType;
use Hashtopolis\inc\defines\DNotificationType;
use Hashtopolis\inc\defines\DPayloadKeys;
use Hashtopolis\inc\handlers\NotificationHandler;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\Util;

class AgentUtils {
  /**
   * @param ?AgentStat $deviceUtil
   * @param Agent $agent
   * @return string
   * @throws Exception
   */
  public static function getDeviceUtilStatusColor(?AgentStat $deviceUtil, Agent $agent): string {
    if ($deviceUtil === null) {
      if (($agent->getIsActive() == 1) && (time() - $agent->getLastTime() < SConfig::getInstance()->getVal(DConfig::AGENT_TIMEOUT))) {
        return "#42d4f4";
      }
      return "#CCCCCC";
    }
    $deviceUtil = $deviceUtil->getValue();
    $deviceUtil = explode(",", $deviceUtil);
    $sum = 0;
    foreach ($deviceUtil as $u) {
      $sum += intval($u);
    }
    if ($sum == 0) {
      return "#FF0000"; // either util 0 for all or an error occurred
    }
    $avg = $sum / sizeof($deviceUtil);
    if ($avg > SConfig::getInstance()->getVal(DConfig::AGENT_UTIL_THRESHOLD_1)) {
      return "#009933";
    }
    else if ($avg > SConfig::getInstance()->getVal(DConfig::AGENT_UTIL_THRESHOLD_2)) {
      return "#ff9900";
    }
    else {
      return "#800000";
    }
  }
  
  /**
   * @param ?AgentStat $deviceTemp
   * @param Agent $agent
   * @return string
   * @throws Exception
   */
  public static function getDeviceTempStatusColor(?AgentStat $deviceTemp, Agent $agent): string {
    if ($deviceTemp === null) {
      if (($agent->getIsActive() == 1) && (time() - $agent->getLastTime() < SConfig::getInstance()->getVal(DConfig::AGENT_TIMEOUT))) {
        return "#42d4f4";
      }
      return "#CCCCCC";
    }
    $deviceTemp = $deviceTemp->getValue();
    $deviceTemp = explode(",", $deviceTemp);
    $max = 0;
    foreach ($deviceTemp as $t) {
      $max = ($t > $max) ? $t : $max;
    }
    if ($max == 0) {
      return "#FF0000"; // either temp 0 for all or an error occurred
    }
    if ($max <= SConfig::getInstance()->getVal(DConfig::AGENT_TEMP_THRESHOLD_1)) {
      return "#009933";
    }
    else if ($max <= SConfig::getInstance()->getVal(DConfig::AGENT_TEMP_THRESHOLD_2)) {
      return "#ff9900";
    }
    else {
      return "#800000";
    }
  }
  
  /**
   * @param ?AgentStat $cpuUtil
   * @param Agent $agent
   * @return string
   * @throws Exception
   */
  public static function getCpuUtilStatusColor(?AgentStat $cpuUtil, Agent $agent): string {
    if ($cpuUtil === null) {
      if (($agent->getIsActive() == 1) && (time() - $agent->getLastTime() < SConfig::getInstance()->getVal(DConfig::AGENT_TIMEOUT))) {
        return "#42d4f4";
      }
      return "#CCCCCC";
    }
    $cpuUtil = $cpuUtil->getValue();
    $cpuUtil = explode(",", $cpuUtil);
    $sum = 0;
    foreach ($cpuUtil as $u) {
      $sum += intval($u);
    }
    if ($sum == 0) {
      return "#FF0000"; // either util 0 for all or an error occurred
    }
    $avg = $sum / sizeof($cpuUtil);
    if ($avg > SConfig::getInstance()->getVal(DConfig::AGENT_UTIL_THRESHOLD_1)) {
      return "#009933";
    }
    else if ($avg > SConfig::getInstance()->getVal(DConfig::AGENT_UTIL_THRESHOLD_2)) {
      return "#ff9900";
    }
    else {
      return "#800000";
    }
  }
  
  /**
   * @param ?AgentStat $deviceUtil
   * @return string
   */
  public static function getDeviceUtilStatusValue(?AgentStat $deviceUtil): string {
    if ($deviceUtil === null) {
      return "No data";
    }
    $deviceUtil = $deviceUtil->getValue();
    if ($deviceUtil === null) {
      return "No valid data";
    }
    $deviceUtil = explode(",", $deviceUtil);
    $sum = 0;
    foreach ($deviceUtil as $u) {
      $sum += intval($u);
    }
    $avg = $sum / sizeof($deviceUtil);
    return round($avg, 1) . "%";
  }
  
  /**
   * @param ?AgentStat $deviceTemp
   * @return string
   */
  public static function getDeviceTempStatusValue(?AgentStat $deviceTemp): string {
    if ($deviceTemp === null) {
      return 'No data';
    }
    $deviceTemp = $deviceTemp->getValue();
    if ($deviceTemp === null) {
      return 'No valid data';
    }
    $deviceTemp = explode(",", $deviceTemp);
    $max = 0;
    foreach ($deviceTemp as $t) {
      $max = ($t > $max) ? $t : $max;
    }
    return $max . "°";
  }
  
  /**
   * @param ?AgentStat $cpuUtil
   * @return string
   */
  public static function getCpuUtilStatusValue(?AgentStat $cpuUtil): string {
    if ($cpuUtil === null) {
      return "No data";
    }
    $cpuUtil = $cpuUtil->getValue();
    if ($cpuUtil === null) {
      return "No valid data";
    }
    $cpuUtil = explode(",", $cpuUtil);
    $sum = 0;
    foreach ($cpuUtil as $u) {
      $sum += intval($u);
    }
    $avg = $sum / sizeof($cpuUtil);
    return round($avg, 1) . "%";
  }
  
  /**
   * @param Agent $agent
   * @param mixed $types
   * @return array
   * @throws Exception
   */
  public static function getGraphData(Agent $agent, mixed $types): array {
    $limit = intval(SConfig::getInstance()->getVal(DConfig::AGENT_STAT_LIMIT));
    if ($limit <= 0) {
      $limit = 100;
    }
    
    $qF1 = new ContainFilter(AgentStat::STAT_TYPE, $types);
    $qF2 = new QueryFilter(AgentStat::AGENT_ID, $agent->getId(), "=");
    $oF1 = new OrderFilter(AgentStat::TIME, "DESC");
    $oF2 = new OrderFilter(AgentStat::STAT_TYPE, "ASC LIMIT $limit");
    $entries = Factory::getAgentStatFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => [$oF1, $oF2]]);
    $xlabels = [];
    $datasets = [];
    $axes = [];
    $yLabels = [DAgentStatsType::GPU_TEMP => 'Device Temp (Celsius)', DAgentStatsType::GPU_UTIL => 'Device Util (%)', DAgentStatsType::CPU_UTIL => 'CPU Util (%)'];
    $position = 'left';
    $colors = [
      "#013220",
      "#FF4500",
      "#000080",
      "#B03060",
      "#008080",
      "#A0522D",
      "#FFA500",
      "#FF00FF",
      "#00FFFF",
      "#FF0000",
      "#00FF00",
      "#A52A2A",
      "#2E0854",
      "#BDECB6",
      "#ADD8E6"
    ];
    foreach ($entries as $entry) {
      $found = false;
      foreach ($axes as $axis) {
        if ($axis['id'] == $entry->getStatType()) {
          $found = true;
          break;
        }
      }
      if (!$found) {
        $axes[] = ["id" => $entry->getStatType(), 'type' => 'linear', 'position' => $position, "display" => true, 'scaleLabel' => ['display' => true, 'labelString' => $yLabels[$entry->getStatType()]]];
        $position = ($position == 'left') ? 'right' : 'left';
      }
      $data = explode(",", $entry->getValue());
      for ($i = 0; $i < sizeof($data); $i++) {
        $pos = (int)($i + sizeof($data) * array_search($entry->getStatType(), $types));
        if (!isset($datasets[$pos])) {
          $datasets[$pos] = array(
            "label" => "Dev #" . ($i + 1), // note: it is written as Dev instead of Device to avoid too wide legends if there are multiple devices
            "fill" => false,
            "lineTension" => (SConfig::getInstance()->getVal(DConfig::AGENT_STAT_TENSION) == 1) ? 0 : 0.5,
            "yAxisID" => $entry->getStatType(),
            "backgroundColor" => $colors[$pos % sizeof($colors)],
            "borderColor" => $colors[$pos % sizeof($colors)],
            "data" => []
          );
        }
        if (!in_array(date(SConfig::getInstance()->getVal(DConfig::TIME_FORMAT), $entry->getTime()), $xlabels)) {
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
   * @throws Exception
   */
  public static function setAgentCpu(int $agentId, bool $isCpuOnly, User $user): void {
    $agent = AgentUtils::getAgent($agentId, $user);
    Factory::getAgentFactory()->set($agent, Agent::CPU_ONLY, ($isCpuOnly) ? 1 : 0);
  }
  
  /**
   * @param int $agentId
   * @param User $user
   * @throws HTException
   * @throws Exception
   */
  public static function clearErrors(int $agentId, User $user): void {
    $agent = AgentUtils::getAgent($agentId, $user);
    
    $qF = new QueryFilter(AgentError::AGENT_ID, $agent->getId(), "=");
    Factory::getAgentErrorFactory()->massDeletion([Factory::FILTER => $qF]);
  }
  
  /**
   * @param int $agentId
   * @param string $newname
   * @param User $user
   * @throws HTException
   * @throws Exception
   */
  public static function rename(int $agentId, string $newname, User $user): void {
    $agent = AgentUtils::getAgent($agentId, $user);
    $name = htmlentities($newname, ENT_QUOTES, "UTF-8");
    if (strlen($name) == 0) {
      throw new HTException("Agent name cannot be empty!");
    }
    Factory::getAgentFactory()->set($agent, Agent::AGENT_NAME, $name);
  }
  
  /**
   * @param int $agentId
   * @param ?User $user
   * @throws HTException
   * @throws Exception
   */
  public static function delete(int $agentId, ?User $user): void {
    $agent = AgentUtils::getAgent($agentId, $user);
    
    Factory::getAgentFactory()->getDB()->beginTransaction();
    $name = $agent->getAgentName();
    
    $payload = new DataSet(array(DPayloadKeys::AGENT => $agent));
    NotificationHandler::checkNotifications(DNotificationType::DELETE_AGENT, $payload);
    
    if (AgentUtils::deleteDependencies($agent)) {
      Factory::getAgentFactory()->getDB()->commit();
      Util::createLogEntry("User", (($user == null) ? 0 : ($user->getId())), DLogEntry::INFO, "Agent " . $name . " got deleted.");
    }
    else {
      Factory::getAgentFactory()->getDB()->rollBack();
      throw new HTException("Error occured on deletion of agent!");
    }
  }
  
  /**
   * @param Agent $agent
   * @return boolean
   * @throws Exception
   */
  public static function deleteDependencies(Agent $agent): bool {
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
    
    $qF = new QueryFilter(AgentStat::AGENT_ID, $agent->getId(), "=");
    Factory::getAgentStatFactory()->massDeletion([Factory::FILTER => $qF]);
    
    $qF = new QueryFilter(AgentZap::AGENT_ID, $agent->getId(), "=");
    Factory::getAgentZapFactory()->massDeletion([Factory::FILTER => $qF]);
    
    $qF = new QueryFilter(HealthCheckAgent::AGENT_ID, $agent->getId(), "=");
    Factory::getHealthCheckAgentFactory()->massDeletion([Factory::FILTER => $qF]);
    
    $qF = new QueryFilter(Speed::AGENT_ID, $agent->getId(), "=");
    Factory::getSpeedFactory()->massDeletion([Factory::FILTER => $qF]);
    
    $qF = new QueryFilter(Zap::AGENT_ID, $agent->getId(), "=");
    $uS = new UpdateSet(Zap::AGENT_ID, null);
    Factory::getZapFactory()->massUpdate([Factory::FILTER => $qF, Factory::UPDATE => $uS]);
    
    $qF = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=");
    Factory::getAccessGroupAgentFactory()->massDeletion([Factory::FILTER => $qF]);
    
    $qF = new QueryFilter(Chunk::AGENT_ID, $agent->getId(), "=");
    $uS = new UpdateSet(Chunk::AGENT_ID, null);
    Factory::getChunkFactory()->massUpdate([Factory::FILTER => $qF, Factory::UPDATE => $uS]);
    
    Factory::getAgentFactory()->delete($agent);
    return true;
  }
  
  /**
   * @param int $agentId
   * @param int $taskId
   * @param User $user
   * @return ?Assignment
   * @throws HTException
   * @throws HttpError
   * @throws Exception
   */
  public static function assign(int $agentId, int $taskId, User $user): ?Assignment {
    $agent = AgentUtils::getAgent($agentId, $user);
    
    if ($taskId == 0) { // unassign
      $qF = new QueryFilter(Agent::AGENT_ID, $agent->getId(), "=");
      Factory::getAssignmentFactory()->massDeletion([Factory::FILTER => $qF]);
      if (isset($_GET['task'])) {
        header("Location: tasks.php?id=" . intval($_GET['task']));
        die();
      }
      return null;
    }
    
    $task = Factory::getTaskFactory()->get($taskId);
    if ($task == null) {
      throw new HttpError("Invalid task!");
    }
    else if (!AccessUtils::agentCanAccessTask($agent, $task)) {
      throw new HttpError("This agent cannot access this task - either group mismatch, or agent is not configured as Trusted to access secret tasks");
    }
    
    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HttpError("No access to this task!");
    }
    
    $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignments = Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF]);
    if ($task->getIsSmall() && sizeof($assignments) > 0) {
      throw new HttpError("You cannot assign agent to this task as the limit of assignments is reached!");
    }
    
    $qF = new QueryFilter(Agent::AGENT_ID, $agent->getId(), "=");
    $assignments = Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF]);
    
    $benchmark = 0;
    if (sizeof($assignments) > 0) {
      if ($assignments[0]->getTaskId() === $taskId) {
        throw new HttpError("Agent is already assigned to this task");
      }
      for ($i = 1; $i < sizeof($assignments); $i++) { // clean up if required
        Factory::getAssignmentFactory()->delete($assignments[$i]);
      }
      $assignment = $assignments[0];
      $assignment = Factory::getAssignmentFactory()->mset($assignment, [Assignment::TASK_ID => $task->getId(), Assignment::BENCHMARK => $benchmark]);
    }
    else {
      $assignment = new Assignment(null, $task->getId(), $agent->getId(), $benchmark);
      $assignment = Factory::getAssignmentFactory()->save($assignment);
    }
    if (isset($_GET['task'])) {
      header("Location: tasks.php?id=" . intval($_GET['task']));
      die();
    }
    return $assignment;
  }
  
  /**
   * @param int $agentId
   * @param int $ignoreErrors
   * @param User $user
   * @throws HTException
   * @throws Exception
   */
  public static function changeIgnoreErrors(int $agentId, int $ignoreErrors, User $user): void {
    $agent = AgentUtils::getAgent($agentId, $user);
    if ($ignoreErrors != 0 && $ignoreErrors != 1 && $ignoreErrors != 2) {
      throw new HTException("Invalid Ignore state!");
    }
    Factory::getAgentFactory()->set($agent, Agent::IGNORE_ERRORS, $ignoreErrors);
  }
  
  /**
   * @param int $agentId
   * @param ?User $user
   * @return Agent
   * @throws HTException
   * @throws Exception
   */
  public static function getAgent(int $agentId, ?User $user = null): Agent {
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
   * @throws Exception
   */
  public static function setTrusted(int $agentId, bool $trusted, User $user): void {
    $agent = AgentUtils::getAgent($agentId, $user);
    Factory::getAgentFactory()->set($agent, Agent::IS_TRUSTED, ($trusted) ? 1 : 0);
  }
  
  /**
   * @param int $agentId
   * @param int|string $ownerId
   * @param User $user
   * @throws HTException
   * @throws Exception
   */
  public static function changeOwner(int $agentId, int|string $ownerId, User $user): void {
    $agent = AgentUtils::getAgent($agentId, $user);
    if ($ownerId == 0) {
      $username = "NONE";
      $agent = Factory::getAgentFactory()->set($agent, Agent::USER_ID, null);
    }
    else {
      if (is_numeric($ownerId)) {
        $owner = Factory::getUserFactory()->get(intval($ownerId));
      }
      else {
        $qF = new QueryFilter(User::USERNAME, $ownerId, "=");
        $owner = Factory::getUserFactory()->filter([Factory::FILTER => $qF], true);
      }
      if (!$owner) {
        throw new HTException("Invalid user selected!");
      }
      $username = $user->getUsername();
      $agent = Factory::getAgentFactory()->set($agent, Agent::USER_ID, $owner->getId());
    }
    Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::INFO, "Owner for agent " . $agent->getAgentName() . " was changed to " . $username);
  }
  
  /**
   * @param int $agentId
   * @param string $cmdParameters
   * @param User $user
   * @throws HTException
   * @throws Exception
   */
  public static function changeCmdParameters(int $agentId, string $cmdParameters, User $user): void {
    $agent = AgentUtils::getAgent($agentId, $user);
    if (Util::containsBlacklistedChars($cmdParameters)) {
      throw new HTException("Parameters must contain no blacklisted characters!");
    }
    Factory::getAgentFactory()->set($agent, Agent::CMD_PARS, $cmdParameters);
  }
  
  /**
   * @param int $agentId
   * @param boolean $active
   * @param User $user
   * @param boolean $toggle
   * @throws HTException
   * @throws Exception
   */
  public static function setActive(int $agentId, bool $active, User $user, bool $toggle = false): void {
    $agent = Factory::getAgentFactory()->get($agentId);
    if ($agent == null) {
      throw new HTException("Invalid agent!");
    }
    else if (!AccessUtils::userCanAccessAgent($agent, $user)) {
      throw new HTException("No access to this agent!");
    }
    
    if ($toggle) {
      $set = ($agent->getIsActive() == 1) ? 0 : 1;
    }
    else {
      $set = ($active) ? 1 : 0;
    }
    Factory::getAgentFactory()->set($agent, Agent::IS_ACTIVE, $set);
  }
  
  /**
   * @param string $newVoucher
   * @return RegVoucher
   * @throws HttpConflict
   * @throws Exception
   */
  public static function createVoucher(string $newVoucher): RegVoucher {
    $qF = new QueryFilter(RegVoucher::VOUCHER, $newVoucher, "=");
    $check = Factory::getRegVoucherFactory()->filter([Factory::FILTER => $qF]);
    if ($check != null) {
      throw new HttpConflict("Same voucher already exists!");
    }
    
    $key = htmlentities($newVoucher, ENT_QUOTES, "UTF-8");
    $voucher = new RegVoucher(null, $key, time());
    return Factory::getRegVoucherFactory()->save($voucher);
  }
  
  /**
   * @param int|string $voucher
   * @throws HTException
   * @throws Exception
   */
  public static function deleteVoucher(int|string $voucher): void {
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
