<?php

class SessionFactory extends AbstractModelFactory {
	function getModelName() {
		return "Session";
	}
	
	function getModelTable() {
		return "Session";
	}
	
	function isCachable() {
		return false;
	}
	
	function getCacheValidTime() {
		return - 1;
	}
	
	function getNullObject() {
		$o = new Session(-1, null, null, null, null, null, null);
		return $o;
	}
	
	function createObjectFromDict($pk, $dict) {
		$o = new Session($pk, $dict['userId'], $dict['sessionStartDate'], $dict['lastActionDate'], $dict['isOpen'], $dict['sessionLifetime'], $dict['sessionKey']);
		return $o;
	}
}