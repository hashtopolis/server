<?php

class agentsFactory extends AbstractModelFactory {
	function getModelName() {
		return "agents";
	}
	
	function getModelTable() {
		return "agents";
	}
	
	function isCachable() {
		return false;
	}
	
	function getCacheValidTime() {
		return - 1;
	}
	
	function getNullObject() {
		$o = new agents(-1, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
		return $o;
	}
	
	function createObjectFromDict($pk, $dict) {
		$o = new agents($pk, $dict['name'], $dict['uid'], $dict['os'], $dict['cputype'], $dict['gpubrand'], $dict['gpudriver'], $dict['gpus'], $dict['hcversion'], $dict['cmdpars'], $dict['wait'], $dict['ignoreerrors'], $dict['active'], $dict['trusted'], $dict['token'], $dict['lastact'], $dict['lasttime'], $dict['lastip']);
		return $o;
	}
}