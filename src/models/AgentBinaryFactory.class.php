<?php

class AgentBinaryFactory extends AbstractModelFactory {
	function getModelName() {
		return "AgentBinary";
	}
	
	function getModelTable() {
		return "AgentBinary";
	}
	
	function isCachable() {
		return false;
	}
	
	function getCacheValidTime() {
		return - 1;
	}
	
	function getNullObject() {
		$o = new AgentBinary(-1, null, null, null);
		return $o;
	}
	
	function createObjectFromDict($pk, $dict) {
		$o = new AgentBinary($pk, $dict['language'], $dict['operatingSystems'], $dict['filename']);
		return $o;
	}
}