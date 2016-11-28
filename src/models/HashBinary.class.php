<?php

class HashBinary extends AbstractModel {
	private $modelName = "HashBinary";
	
	// Modelvariables
	private $hashBinaryId;
	private $hahslistId;
	private $essid;
	private $hash;
	private $plaintext;
	private $time;
	private $chunkId;
	private $isCracked;
	
	
	function __construct($hashBinaryId, $hahslistId, $essid, $hash, $plaintext, $time, $chunkId, $isCracked) {
		$this->hashBinaryId = $hashBinaryId;
		$this->hahslistId = $hahslistId;
		$this->essid = $essid;
		$this->hash = $hash;
		$this->plaintext = $plaintext;
		$this->time = $time;
		$this->chunkId = $chunkId;
		$this->isCracked = $isCracked;

	}
	
	function getKeyValueDict() {
		$dict = array ();
		$dict['hashBinaryId'] = $this->hashBinaryId;
		$dict['hahslistId'] = $this->hahslistId;
		$dict['essid'] = $this->essid;
		$dict['hash'] = $this->hash;
		$dict['plaintext'] = $this->plaintext;
		$dict['time'] = $this->time;
		$dict['chunkId'] = $this->chunkId;
		$dict['isCracked'] = $this->isCracked;
		
		return $dict;
	}
	
	function getPrimaryKey() {
		return "hashBinaryId";
	}
	
	function getPrimaryKeyValue() {
		return $this->hashBinaryId;
	}
	
	function getId() {
		return $this->hashBinaryId;
	}
	
	function setId($id) {
		$this->hashBinaryId = $id;
	}

	function getHahslistId(){
		return $this->hahslistId;
	}

	function setHahslistId($hahslistId){
		$this->hahslistId = $hahslistId;
	}

	function getEssid(){
		return $this->essid;
	}

	function setEssid($essid){
		$this->essid = $essid;
	}

	function getHash(){
		return $this->hash;
	}

	function setHash($hash){
		$this->hash = $hash;
	}

	function getPlaintext(){
		return $this->plaintext;
	}

	function setPlaintext($plaintext){
		$this->plaintext = $plaintext;
	}

	function getTime(){
		return $this->time;
	}

	function setTime($time){
		$this->time = $time;
	}

	function getChunkId(){
		return $this->chunkId;
	}

	function setChunkId($chunkId){
		$this->chunkId = $chunkId;
	}

	function getIsCracked(){
		return $this->isCracked;
	}

	function setIsCracked($isCracked){
		$this->isCracked = $isCracked;
	}
}
