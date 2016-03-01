<?php
/*
 * $Id: $
 */

class BillFactory extends AbstractModelFactory {
	function getModelName() {
		return "Bill";
	}
	
	function getModelTable() {
		return "Bill";
	}
	
	function isCachable() {
		return false;
	}
	
	function getCacheValidTime() {
		return - 1;
	}
	
	function getNullObject() {
		$o = new Bill(-1, null, null, null, null);
		return $o;
	}
	
	function createObjectFromDict($pk, $dict) {
		$o = new Bill($pk, $dict['userId'], $dict['isPaid'], $dict['userIsNotified'], $dict['notes']);
		return $o;
	}
}