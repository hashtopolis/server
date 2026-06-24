<?php

namespace Hashtopolis\inc\api;

use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQueryGetHashlist;
use Hashtopolis\inc\agent\PResponseGetHashlist;
use Hashtopolis\inc\agent\PValues;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\Util;

class APIGetHashlist extends APIBasic {
  public function execute($QUERY = array()) {
    //check required values
    if (!PQueryGetHashlist::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::GET_HASHLIST, "Invalid hashlist query!");
    }
    $this->checkToken(PActions::GET_HASHLIST, $QUERY);
    
    $hashlist = Factory::getHashlistFactory()->get($QUERY[PQueryGetHashlist::HASHLIST_ID]);
    if ($hashlist == null) {
      $this->sendErrorResponse(PActions::GET_HASHLIST, "Invalid hashlist!");
    }
    
    DServerLog::log(DServerLog::DEBUG, "Requesting a hashlist...", [$this->agent, $hashlist]);
    
    $qF = new QueryFilter(Assignment::AGENT_ID, $this->agent->getId(), "=");
    $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF], true);
    if ($assignment == null) {
      $this->sendErrorResponse(PActions::GET_HASHLIST, "Agent is not assigned to a task!");
    }
    
    $task = Factory::getTaskFactory()->get($assignment->getTaskId());
    if ($task == null) {
      DServerLog::log(DServerLog::WARNING, "Assignment contained invalid task!", [$this->agent, $assignment]);
      $this->sendErrorResponse(PActions::GET_HASHLIST, "Assignment contains invalid task!");
    }
    
    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if ($taskWrapper == null) {
      DServerLog::log(DServerLog::FATAL, "Inconsistency between taskWrapper and tasks!", [$this->agent, $task]);
      $this->sendErrorResponse(PActions::GET_HASHLIST, "Inconsistent taskWrapper for task!");
    }
    
    if ($taskWrapper->getHashlistId() != $hashlist->getId()) {
      DServerLog::log(DServerLog::WARNING, "Agent requested hashlist not used for task!", [$this->agent, $taskWrapper, $task, $hashlist]);
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