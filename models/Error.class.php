<?php

class Error extends AbstractModel {
	private $modelName = "Error";
	
	// Modelvariables
	private $errorId;
	private $agentId;
	private $taskId;
	private $time;
	private $error;
	
	
	function __construct($errorId, $agentId, $taskId, $time, $error) {
		$this->errorId = $errorId;
		$this->agentId = $agentId;
		$this->taskId = $taskId;
		$this->time = $time;
		$this->error = $error;

	}
	
	function getKeyValueDict() {
		$dict = array ();
		$dict['errorId'] = $this->errorId;
		$dict['agentId'] = $this->agentId;
		$dict['taskId'] = $this->taskId;
		$dict['time'] = $this->time;
		$dict['error'] = $this->error;
		
		return $dict;
	}
	
	function getPrimaryKey() {
		return "errorId";
	}
	
	function getPrimaryKeyValue() {
		return $this->errorId;
	}
	
	function getId() {
		return $this->errorId;
	}
	
	function setId($id) {
		$this->errorId = $id;
	}

	function getAgentId(){
		return $this->agentId;
	}

	function setAgentId($agentId){
		$this->agentId = $agentId;
	}

	function getTaskId(){
		return $this->taskId;
	}

	function setTaskId($taskId){
		$this->taskId = $taskId;
	}

	function getTime(){
		return $this->time;
	}

	function setTime($time){
		$this->time = $time;
	}

	function getError(){
		return $this->error;
	}

	function setError($error){
		$this->error = $error;
	}
}
