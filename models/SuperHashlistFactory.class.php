<?php

class SuperHashlistFactory extends AbstractModelFactory {
	function getModelName() {
		return "SuperHashlist";
	}
	
	function getModelTable() {
		return "SuperHashlist";
	}
	
	function isCachable() {
		return false;
	}
	
	function getCacheValidTime() {
		return - 1;
	}
	
	function getNullObject() {
		$o = new SuperHashlist(-1);
		return $o;
	}
	
	function createObjectFromDict($pk, $dict) {
		$o = new SuperHashlist($pk);
		return $o;
	}
}