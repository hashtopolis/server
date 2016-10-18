<?php

class HashlistFactory extends AbstractModelFactory {
	function getModelName() {
		return "Hashlist";
	}
	
	function getModelTable() {
		return "Hashlist";
	}
	
	function isCachable() {
		return false;
	}
	
	function getCacheValidTime() {
		return - 1;
	}
	
	function getNullObject() {
		$o = new Hashlist(-1, null, null, null, null, null, null, null, null);
		return $o;
	}
	
	function createObjectFromDict($pk, $dict) {
		$o = new Hashlist($pk, $dict['hashlistName'], $dict['format'], $dict['hashTypeId'], $dict['hashCount'], $dict['saltSeparator'], $dict['cracked'], $dict['secret'], $dict['hexSalt']);
		return $o;
	}
}