<?php

class HashBinaryFactory extends AbstractModelFactory {
	function getModelName() {
		return "HashBinary";
	}
	
	function getModelTable() {
		return "HashBinary";
	}
	
	function isCachable() {
		return false;
	}
	
	function getCacheValidTime() {
		return - 1;
	}
	
	function getNullObject() {
		$o = new HashBinary(-1, null, null, null, null, null, null, null);
		return $o;
	}
	
	function createObjectFromDict($pk, $dict) {
		$o = new HashBinary($pk, $dict['hahslistId'], $dict['essid'], $dict['hash'], $dict['plaintext'], $dict['time'], $dict['chunkId'], $dict['isCracked']);
		return $o;
	}
}