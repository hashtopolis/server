<?php

class ZapFactory extends AbstractModelFactory {
	function getModelName() {
		return "Zap";
	}
	
	function getModelTable() {
		return "Zap";
	}
	
	function isCachable() {
		return false;
	}
	
	function getCacheValidTime() {
		return - 1;
	}
	
	function getNullObject() {
		$o = new Zap(-1, null, null, null);
		return $o;
	}
	
	function createObjectFromDict($pk, $dict) {
		$o = new Zap($pk, $dict['hash'], $dict['solveTime'], $dict['hashlistId']);
		return $o;
	}
}