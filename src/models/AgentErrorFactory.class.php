<?php

class AgentErrorFactory extends AbstractModelFactory {
	function getModelName() {
		return "AgentError";
	}
	
	function getModelTable() {
		return "AgentError";
	}
	
	function isCachable() {
		return false;
	}
	
	function getCacheValidTime() {
		return - 1;
	}
	
	function getNullObject() {
		$o = new AgentError(-1, null, null, null, null);
		return $o;
	}
	
	function createObjectFromDict($pk, $dict) {
		$o = new AgentError($pk, $dict['agentId'], $dict['taskId'], $dict['time'], $dict['error']);
		return $o;
	}
}