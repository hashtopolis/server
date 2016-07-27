<?php

class HashcatReleaseFactory extends AbstractModelFactory {
	function getModelName() {
		return "HashcatRelease";
	}
	
	function getModelTable() {
		return "HashcatRelease";
	}
	
	function isCachable() {
		return false;
	}
	
	function getCacheValidTime() {
		return - 1;
	}
	
	function getNullObject() {
		$o = new HashcatRelease(-1, null, null, null, null, null, null, null, null);
		return $o;
	}
	
	function createObjectFromDict($pk, $dict) {
		$o = new HashcatRelease($pk, $dict['version'], $dict['time'], $dict['url'], $dict['common_files'], $dict['32binary'], $dict['64binary'], $dict['rootdir'], $dict['minver']);
		return $o;
	}
}