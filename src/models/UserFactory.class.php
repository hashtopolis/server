<?php

class UserFactory extends AbstractModelFactory {
	function getModelName() {
		return "User";
	}
	
	function getModelTable() {
		return "User";
	}
	
	function isCachable() {
		return false;
	}
	
	function getCacheValidTime() {
		return - 1;
	}
	
	function getNullObject() {
		$o = new User(-1, null, null, null, null, null, null, null, null, null, null);
		return $o;
	}
	
	function createObjectFromDict($pk, $dict) {
		$o = new User($pk, $dict['username'], $dict['email'], $dict['passwordHash'], $dict['passwordSalt'], $dict['isValid'], $dict['isComputedPassword'], $dict['lastLoginDate'], $dict['registeredSince'], $dict['sessionLifetime'], $dict['rightGroupId']);
		return $o;
	}
}