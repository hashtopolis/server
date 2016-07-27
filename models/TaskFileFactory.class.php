<?php

class TaskFileFactory extends AbstractModelFactory {
	function getModelName() {
		return "TaskFile";
	}
	
	function getModelTable() {
		return "TaskFile";
	}
	
	function isCachable() {
		return false;
	}
	
	function getCacheValidTime() {
		return - 1;
	}
	
	function getNullObject() {
		$o = new TaskFile(-1, null, null);
		return $o;
	}
	
	function createObjectFromDict($pk, $dict) {
		$o = new TaskFile($pk, $dict['taskId'], $dict['fileId']);
		return $o;
	}
}