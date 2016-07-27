<?php

class HashcatRelease extends AbstractModel {
	private $modelName = "HashcatRelease";
	
	// Modelvariables
	private $hashcatReleaseId;
	private $version;
	private $time;
	private $url;
	private $common_files;
	private $32binary;
	private $64binary;
	private $rootdir;
	private $minver;
	
	
	function __construct($hashcatReleaseId, $version, $time, $url, $common_files, $32binary, $64binary, $rootdir, $minver) {
		$this->hashcatReleaseId = $hashcatReleaseId;
		$this->version = $version;
		$this->time = $time;
		$this->url = $url;
		$this->common_files = $common_files;
		$this->32binary = $32binary;
		$this->64binary = $64binary;
		$this->rootdir = $rootdir;
		$this->minver = $minver;

	}
	
	function getKeyValueDict() {
		$dict = array ();
		$dict['hashcatReleaseId'] = $this->hashcatReleaseId;
		$dict['version'] = $this->version;
		$dict['time'] = $this->time;
		$dict['url'] = $this->url;
		$dict['common_files'] = $this->common_files;
		$dict['32binary'] = $this->32binary;
		$dict['64binary'] = $this->64binary;
		$dict['rootdir'] = $this->rootdir;
		$dict['minver'] = $this->minver;
		
		return $dict;
	}
	
	function getPrimaryKey() {
		return "hashcatReleaseId";
	}
	
	function getPrimaryKeyValue() {
		return $this->hashcatReleaseId;
	}
	
	function getId() {
		return $this->hashcatReleaseId;
	}
	
	function setId($id) {
		$this->hashcatReleaseId = $id;
	}

	function getVersion(){
		return $this->version;
	}

	function setVersion($version){
		$this->version = $version;
	}

	function getTime(){
		return $this->time;
	}

	function setTime($time){
		$this->time = $time;
	}

	function getUrl(){
		return $this->url;
	}

	function setUrl($url){
		$this->url = $url;
	}

	function getCommon_files(){
		return $this->common_files;
	}

	function setCommon_files($common_files){
		$this->common_files = $common_files;
	}

	function get32binary(){
		return $this->32binary;
	}

	function set32binary($32binary){
		$this->32binary = $32binary;
	}

	function get64binary(){
		return $this->64binary;
	}

	function set64binary($64binary){
		$this->64binary = $64binary;
	}

	function getRootdir(){
		return $this->rootdir;
	}

	function setRootdir($rootdir){
		$this->rootdir = $rootdir;
	}

	function getMinver(){
		return $this->minver;
	}

	function setMinver($minver){
		$this->minver = $minver;
	}
}
