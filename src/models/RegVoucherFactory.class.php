<?php

class RegVoucherFactory extends AbstractModelFactory {
	function getModelName() {
		return "RegVoucher";
	}
	
	function getModelTable() {
		return "RegVoucher";
	}
	
	function isCachable() {
		return false;
	}
	
	function getCacheValidTime() {
		return - 1;
	}
	
	function getNullObject() {
		$o = new RegVoucher(-1, null, null);
		return $o;
	}
	
	function createObjectFromDict($pk, $dict) {
		$o = new RegVoucher($pk, $dict['voucher'], $dict['time']);
		return $o;
	}
}