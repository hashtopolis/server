<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 09.03.17
 * Time: 13:38
 */
abstract class HashtopussyNotification {
  public static $name;
  protected $receiver;
  //TODO: add HTML messages for templates which can issue html notifications
  
  /**
   * @param $notificationType string
   * @param $payload DataSet
   * @param $receiver string Contains the value where the message can be sent to. This can for example be an URL, an email address, etc.
   */
  public function execute($notificationType, $payload, $receiver) {
    $this->receiver = $receiver;
    $template = new Template($this->getTemplateName());
    $obj = $this->getObjects();
    switch ($notificationType) {
      case DNotificationType::TASK_COMPLETE:
        $task = $payload->getVal(DPayloadKeys::TASK);
        $obj['message'] = "Task '" . $task->getTaskName() . "'' (" . $task->getId() . ") is completed!";
        break;
      case DNotificationType::AGENT_ERROR:
        $agent = $payload->getVal(DPayloadKeys::AGENT);
        $obj['message'] = "Agent '" . $agent->getAgentName() . "'' (" . $agent->getId() . ") errored: " . $payload->getVal(DPayloadKeys::AGENT_ERROR);
        break;
      case DNotificationType::LOG_ERROR:
        $logEntry = $payload->getVal(DPayloadKeys::LOG_ENTRY);
        $obj['message'] = "Log level ERROR occured: " . $logEntry->getMessage() . "!";
        break;
      default:
        $obj['message'] = "Notification for unknown type: " . print_r($payload->getAllValues(), true);
        break;
    }
    $this->sendMessage($template->render($obj));
  }
  
  abstract function getTemplateName();
  
  abstract function getObjects();
  
  abstract function sendMessage($message);
}
