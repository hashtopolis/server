<?php

class AssignmentFactory extends AbstractModelFactory {
	function getModelName() {
		return "Assignment";
	}
	
	function getModelTable() {
		return "Assignment";
	}
	
	function isCachable() {
		return false;
	}
	
	function getCacheValidTime() {
		return - 1;
	}
	
	function getNullObject() {
		$o = new Assignment(-1, null, null, null);
		return $o;
	}
	
	function createObjectFromDict($pk, $dict) {
		$o = new Assignment($pk, $dict['taskId'], $dict['agentId'], $dict['benchmark']);
		return $o;
	}
}