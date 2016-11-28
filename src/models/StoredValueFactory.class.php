<?php

class StoredValueFactory extends AbstractModelFactory {
	function getModelName() {
		return "StoredValue";
	}
	
	function getModelTable() {
		return "StoredValue";
	}
	
	function isCachable() {
		return false;
	}
	
	function getCacheValidTime() {
		return - 1;
	}
	
	function getNullObject() {
		$o = new StoredValue(-1, null);
		return $o;
	}
	
	function createObjectFromDict($pk, $dict) {
		$o = new StoredValue($pk, $dict['val']);
		return $o;
	}
}