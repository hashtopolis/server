<?php

class ErrorFactory extends AbstractModelFactory {
	function getModelName() {
		return "Error";
	}
	
	function getModelTable() {
		return "Error";
	}
	
	function isCachable() {
		return false;
	}
	
	function getCacheValidTime() {
		return - 1;
	}
	
	function getNullObject() {
		$o = new Error(-1, null, null, null, null);
		return $o;
	}
	
	function createObjectFromDict($pk, $dict) {
		$o = new Error($pk, $dict['agentId'], $dict['taskId'], $dict['time'], $dict['error']);
		return $o;
	}
}