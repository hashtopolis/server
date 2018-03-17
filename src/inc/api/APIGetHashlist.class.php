<?php

use DBA\Assignment;
use DBA\QueryFilter;

class APIGetHashlist extends APIBasic {
  public function execute($QUERY = array()) {
    global $FACTORIES;
    
    //check required values
    if (!PQueryGetHashlist::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::GET_HASHLIST, "Invalid hashlist query!");
    }
    $this->checkToken(PActions::GET_HASHLIST, $QUERY);
    
    $hashlist = $FACTORIES::getHashlistFactory()->get($QUERY[PQueryGetHashlist::HASHLIST_ID]);
    if ($hashlist == null) {
      $this->sendErrorResponse(PActions::GET_HASHLIST, "Invalid hashlist!");
    }
    
    $qF = new QueryFilter(Assignment::AGENT_ID, $this->agent->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => array($qF)), true);
    if ($assignment == null) {
      $this->sendErrorResponse(PActions::GET_HASHLIST, "Agent is not assigned to a task!");
    }
    
    $task = $FACTORIES::getTaskFactory()->get($assignment->getTaskId());
    if ($task == null) {
      $this->sendErrorResponse(PActions::GET_HASHLIST, "Assignment contains invalid task!");
    }
    
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if ($taskWrapper == null) {
      $this->sendErrorResponse(PActions::GET_HASHLIST, "Inconsistent taskWrapper for task!");
    }
    
    if ($taskWrapper->getHashlistId() != $hashlist->getId()) {
      $this->sendErrorResponse(PActions::GET_HASHLIST, "This hashlist is not used for the assigned task!");
    }
    else if ($this->agent->getIsTrusted() < $hashlist->getIsSecret()) {
      $this->sendErrorResponse(PActions::GET_HASHLIST, "You have not access to this hashlist!");
    }
    
    $hashlists = Util::checkSuperHashlist($hashlist);
    foreach ($hashlists as $hashlist) {
      if ($hashlist->getIsSecret() > $this->agent->getIsTrusted()) {
        $this->sendErrorResponse(PActions::GET_HASHLIST, "Agent would require to download secret hashlist with insufficient level!");
      }
    }
    
    $this->updateAgent(PActions::GET_HASHLIST);
    
    if (sizeof($hashlists) == 0) {
      $this->sendErrorResponse(PActions::GET_HASHLIST, "No hashlists selected/available!");
    }
    $this->sendResponse(array(
        PResponseGetHashlist::ACTION => PActions::GET_HASHLIST,
        PResponseGetHashlist::RESPONSE => PValues::SUCCESS,
        PResponseGetHashlist::URL => "getHashlist.php?hashlists=" . implode(",", Util::arrayOfIds($hashlists)) . "&token=" . $this->agent->getToken()
      )
    );
  }
}