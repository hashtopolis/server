<?php

class ConfigFactory extends AbstractModelFactory {
	function getModelName() {
		return "Config";
	}
	
	function getModelTable() {
		return "Config";
	}
	
	function isCachable() {
		return false;
	}
	
	function getCacheValidTime() {
		return - 1;
	}
	
	function getNullObject() {
		$o = new Config(-1, null, null);
		return $o;
	}
	
	function createObjectFromDict($pk, $dict) {
		$o = new Config($pk, $dict['item'], $dict['value']);
		return $o;
	}
}