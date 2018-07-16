<?php
use DBA\Agent;
use DBA\QueryFilter;
use DBA\Assignment;

class AgentUtils {
  /**
   * @param int $agentId 
   * @param int $taskId 
   * @return boolean|string
   */
  public static function assign($agentId, $taskId, $user) {
    global $FACTORIES;

    $agent = $FACTORIES::getAgentFactory()->get($agentId);
    if($agent == null){
      return "Agent not found!";
    }
    if($taskId == 0){ // unassign
      $qF = new QueryFilter(Agent::AGENT_ID, $agent->getId(), "=");
      $FACTORIES::getAssignmentFactory()->massDeletion(array($FACTORIES::FILTER => array($qF)));
      if (isset($_GET['task'])) {
        header("Location: tasks.php?id=" . intval($_GET['task']));
        die();
      }
      return false;
    }

    $task = $FACTORIES::getTaskFactory()->get(intval($taskId));
    if ($task == null) {
      return "Invalid task!";
    }
    else if (!AccessUtils::agentCanAccessTask($agent, $task)) {
      return "This agent cannot access this task - either group mismatch, or agent is not configured as Trusted to access secret tasks";
    }

    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if(!AccessUtils::userCanAccessTask($taskWrapper, $user)){
      return "No access to this task!":
    }

    $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignments = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => $qF));
    if ($task->getIsSmall() && sizeof($assignments) > 0) {
      return "You cannot assign agent to this task as the limit of assignments is reached!";
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
    return false;
  }
}