<?php
use DBA\NotificationSetting;

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 09.03.17
 * Time: 13:38
 */
abstract class HashtopussyNotification {
  public static $name;
  protected     $receiver;
  
  /** @var  $notification NotificationSetting */
  protected $notification;
  
  /**
   * @param $notificationType string
   * @param $payload DataSet
   * @param $receiver string Contains the value where the message can be sent to. This can for example be an URL, an email address, etc.
   * @param $notification NotificationSetting
   */
  public function execute($notificationType, $payload, $notification) {
    /** @var $CONFIG DataSet */
    global $CONFIG;
    
    $this->receiver = $notification->getReceiver();
    $this->notification = $notification;
    $template = new Template($this->getTemplateName());
    $obj = $this->getObjects();
    switch ($notificationType) {
      case DNotificationType::TASK_COMPLETE:
        $task = $payload->getVal(DPayloadKeys::TASK);
        $obj['message'] = "Task '" . $task->getTaskName() . "' (" . $task->getId() . ") is completed!";
        $obj['html'] = "Task <a href='" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/tasks.php?id=" . $task->getId() . "'>" . $task->getTaskName() . "</a> is completed!";
        $obj['simplified'] = "Task <" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/tasks.php?id=" . $task->getId() . "|" . $task->getTaskName() . "> is completed!";
        break;
      case DNotificationType::AGENT_ERROR:
        $agent = $payload->getVal(DPayloadKeys::AGENT);
        $obj['message'] = "Agent '" . $agent->getAgentName() . "' (" . $agent->getId() . ") errored: " . $payload->getVal(DPayloadKeys::AGENT_ERROR);
        $obj['html'] = "Agent <a href='" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/agents.php?id=" . $agent->getId() . "'>" . $agent->getAgentName() . "</a> errored: " . $payload->getVal(DPayloadKeys::AGENT_ERROR);
        $obj['simplified'] = "Agent <" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/agents.php?id=" . $agent->getId() . "|" . $agent->getAgentName() . "> errored: " . $payload->getVal(DPayloadKeys::AGENT_ERROR);
        break;
      case DNotificationType::LOG_ERROR:
        $logEntry = $payload->getVal(DPayloadKeys::LOG_ENTRY);
        $obj['message'] = "Log level ERROR occured by '" . $logEntry->getIssuer() . "-" . $logEntry->getIssuerId() . "': " . $logEntry->getMessage() . "!";
        $obj['html'] = $obj['message'];
        $obj['simplified'] = $obj['message'];
        break;
      case DNotificationType::NEW_TASK:
        $task = $payload->getVal(DPayloadKeys::TASK);
        $obj['message'] = "New Task '" . $task->getTaskName() . "' (" . $task->getId() . ") was created";
        $obj['html'] = "New Task <a href='" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/tasks.php?id=" . $task->getId() . "'>" . $task->getTaskName() . "</a> was created";
        $obj['simplified'] = "New Task <" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/tasks.php?id=" . $task->getId() . "|" . $task->getTaskName() . "> was created";
        break;
      case DNotificationType::NEW_HASHLIST:
        $hashlist = $payload->getVal(DPayloadKeys::HASHLIST);
        $obj['message'] = "New Hashlist '" . $hashlist->getHashlistName() . "' (" . $hashlist->getId() . ") was created";
        $obj['html'] = "New Hashlist <a href='" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/hashlists.php?id=" . $hashlist->getId() . "'>" . $hashlist->getHashlistName() . "</a> was created";
        $obj['simplified'] = "New Hashlist <" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/hashlists.php?id=" . $hashlist->getId() . "|" . $hashlist->getHashlistName() . "> was created";
        break;
      case DNotificationType::HASHLIST_ALL_CRACKED:
        $hashlist = $payload->getVal(DPayloadKeys::HASHLIST);
        $obj['message'] = "Hashlist '" . $hashlist->getHashlistName() . "' (" . $hashlist->getId() . ") was cracked completely";
        $obj['html'] = "Hashlist <a href='" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/users.php?id=" . $hashlist->getId() . "'>" . $hashlist->getHashlistName() . "</a> was cracked completely";
        $obj['simplified'] = "Hashlist <" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/users.php?id=" . $hashlist->getId() . "|" . $hashlist->getHashlistName() . "> was cracked completely";
        break;
      case DNotificationType::HASHLIST_CRACKED_HASH:
        $numCracked = $payload->getVal(DPayloadKeys::NUM_CRACKED);
        $agent = $payload->getVal(DPayloadKeys::AGENT);
        $task = $payload->getVal(DPayloadKeys::TASK);
        $hashlist = $payload->getVal(DPayloadKeys::HASHLIST);
        $obj['message'] = "$numCracked Hashes from Hashlist '" . $hashlist->getHashlistName() . "' (" . $hashlist->getId() . ") were cracked on Task '" . $task->getTaskName() . "' (" . $task->getId() . ") by agent '" . $agent->getAgentName() . "' (" . $agent->getId() . ")";
        $obj['html'] = "$numCracked Hashes from Hashlist <a href='" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/hashlists.php?id=" . $hashlist->getId() . "'>" . $hashlist->getHashlistName() . "</a> were cracked on Task <a href='" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/tasks.php?id=" . $task->getId() . "'>" . $task->getTaskName() . "</a> by agent <a href='" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/agents.php?id=" . $agent->getId() . "'>" . $agent->getAgentName() . "</a>";
        $obj['simplified'] = "$numCracked Hashes from Hashlist <" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/hashlists.php?id=" . $hashlist->getId() . "|" . $hashlist->getHashlistName() . "> were cracked on Task <" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/tasks.php?id=" . $task->getId() . "|" . $task->getTaskName() . "> by agent <" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/agents.php?id=" . $agent->getId() . "|" . $agent->getAgentName() . ">";
        break;
      case DNotificationType::USER_CREATED:
        $user = $payload->getVal(DPayloadKeys::USER);
        $obj['message'] = "New User '" . $user->getUsername() . "' (" . $user->getId() . ") was created";
        $obj['html'] = "New User <a href='" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/users.php?id=" . $user->getId() . "'>" . $user->getUsername() . "</a> was created";
        $obj['simplified'] = "New User <" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/users.php?id=" . $user->getId() . "|" . $user->getUsername() . "> was created";
        break;
        break;
      case DNotificationType::USER_DELETED:
        $user = $payload->getVal(DPayloadKeys::USER);
        $obj['message'] = "User '" . $user->getUsername() . "' (" . $user->getId() . ") got deleted";
        $obj['html'] = "User <a href='#'>" . $user->getUsername() . "</a> got deleted";
        $obj['simplified'] = "User '" . $user->getUsername() . "' got deleted";
        break;
      case DNotificationType::USER_LOGIN_FAILED:
        $user = $payload->getVal(DPayloadKeys::USER);
        $obj['message'] = "User '" . $user->getUsername() . "' (" . $user->getId() . ") failed to login due to wrong password";
        $obj['html'] = "User <a href='" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/users.php?id=" . $user->getId() . "'>" . $user->getUsername() . "</a> failed to login due to wrong password";
        $obj['simplified'] = "User <" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/users.php?id=" . $user->getId() . "|" . $user->getUsername() . "> failed to login due to wrong password";
        break;
      case DNotificationType::NEW_AGENT:
        $agent = $payload->getVal(DPayloadKeys::AGENT);
        $obj['message'] = "New Agent '" . $agent->getAgentName() . "' (" . $agent->getId() . ") was registered";
        $obj['html'] = "New Agent <a href='" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/agents.php?id=" . $agent->getId() . "'>" . $agent->getAgentName() . "</a> was registered";
        $obj['simplified'] = "New Agent <" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/agents.php?id=" . $agent->getId() . "|" . $agent->getAgentName() . "> was registered";
        break;
      case DNotificationType::DELETE_TASK:
        $task = $payload->getVal(DPayloadKeys::TASK);
        $obj['message'] = "Task '" . $task->getTaskName() . "' (" . $task->getId() . ") got deleted";
        $obj['html'] = "Task <a href='#'>" . $task->getTaskName() . "</a> got deleted";
        $obj['simplified'] = "Task '" . $task->getTaskName() . "' got deleted";
        break;
      case DNotificationType::DELETE_HASHLIST:
        $hashlist = $payload->getVal(DPayloadKeys::HASHLIST);
        $obj['message'] = "Hashlist '" . $hashlist->getHashlistName() . "' (" . $hashlist->getId() . ") got deleted";
        $obj['html'] = "Hashlist <a href='#'>" . $hashlist->getHashlistName() . "</a> got deleted";
        $obj['simplified'] = "Hashlist '" . $hashlist->getHashlistName() . "' got deleted";
        break;
      case DNotificationType::DELETE_AGENT:
        $agent = $payload->getVal(DPayloadKeys::AGENT);
        $obj['message'] = "Agent '" . $agent->getAgentName() . "' (" . $agent->getId() . ") got deleted";
        $obj['html'] = "Agent <a href='#'>" . $agent->getAgentName() . "</a> got deleted";
        $obj['simplified'] = "Agent '" . $agent->getAgentName() . "' got deleted";
        break;
      case DNotificationType::LOG_WARN:
        $logEntry = $payload->getVal(DPayloadKeys::LOG_ENTRY);
        $obj['message'] = "Log level WARN occured by '" . $logEntry->getIssuer() . "-" . $logEntry->getIssuerId() . "': " . $logEntry->getMessage() . "!";
        $obj['html'] = $obj['message'];
        $obj['simplified'] = $obj['message'];
        break;
      case DNotificationType::LOG_FATAL:
        $logEntry = $payload->getVal(DPayloadKeys::LOG_ENTRY);
        $obj['message'] = "Log level FATAL occured by '" . $logEntry->getIssuer() . "-" . $logEntry->getIssuerId() . "': " . $logEntry->getMessage() . "!";
        $obj['html'] = $obj['message'];
        $obj['simplified'] = $obj['message'];
        break;
      default:
        $obj['message'] = "Notification for unknown type: " . print_r($payload->getAllValues(), true);
        $obj['html'] = $obj['message'];
        $obj['simplified'] = $obj['message'];
        break;
    }
    $this->sendMessage($template->render($obj));
  }
  
  abstract function getTemplateName();
  
  abstract function getObjects();
  
  abstract function sendMessage($message);
}
