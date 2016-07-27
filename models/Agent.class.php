<?php

class Agent extends AbstractModel {
	private $modelName = "Agent";
	
	// Modelvariables
	private $agentId;
	private $agentName;
	private $uid;
	private $os;
	private $cpuType;
	private $gpuBrand;
	private $gpuDriver;
	private $gpus;
	private $hcVersion;
	private $cmdPars;
	private $wait;
	private $ignoreErrors;
	private $isActive;
	private $isTrusted;
	private $token;
	private $lastAct;
	private $lastTime;
	private $lastIp;
	private $userId;
	private $cpuOnly;
	
	
	function __construct($agentId, $agentName, $uid, $os, $cpuType, $gpuBrand, $gpuDriver, $gpus, $hcVersion, $cmdPars, $wait, $ignoreErrors, $isActive, $isTrusted, $token, $lastAct, $lastTime, $lastIp, $userId, $cpuOnly) {
		$this->agentId = $agentId;
		$this->agentName = $agentName;
		$this->uid = $uid;
		$this->os = $os;
		$this->cpuType = $cpuType;
		$this->gpuBrand = $gpuBrand;
		$this->gpuDriver = $gpuDriver;
		$this->gpus = $gpus;
		$this->hcVersion = $hcVersion;
		$this->cmdPars = $cmdPars;
		$this->wait = $wait;
		$this->ignoreErrors = $ignoreErrors;
		$this->isActive = $isActive;
		$this->isTrusted = $isTrusted;
		$this->token = $token;
		$this->lastAct = $lastAct;
		$this->lastTime = $lastTime;
		$this->lastIp = $lastIp;
		$this->userId = $userId;
		$this->cpuOnly = $cpuOnly;

	}
	
	function getKeyValueDict() {
		$dict = array ();
		$dict['agentId'] = $this->agentId;
		$dict['agentName'] = $this->agentName;
		$dict['uid'] = $this->uid;
		$dict['os'] = $this->os;
		$dict['cpuType'] = $this->cpuType;
		$dict['gpuBrand'] = $this->gpuBrand;
		$dict['gpuDriver'] = $this->gpuDriver;
		$dict['gpus'] = $this->gpus;
		$dict['hcVersion'] = $this->hcVersion;
		$dict['cmdPars'] = $this->cmdPars;
		$dict['wait'] = $this->wait;
		$dict['ignoreErrors'] = $this->ignoreErrors;
		$dict['isActive'] = $this->isActive;
		$dict['isTrusted'] = $this->isTrusted;
		$dict['token'] = $this->token;
		$dict['lastAct'] = $this->lastAct;
		$dict['lastTime'] = $this->lastTime;
		$dict['lastIp'] = $this->lastIp;
		$dict['userId'] = $this->userId;
		$dict['cpuOnly'] = $this->cpuOnly;
		
		return $dict;
	}
	
	function getPrimaryKey() {
		return "agentId";
	}
	
	function getPrimaryKeyValue() {
		return $this->agentId;
	}
	
	function getId() {
		return $this->agentId;
	}
	
	function setId($id) {
		$this->agentId = $id;
	}

	function getAgentName(){
		return $this->agentName;
	}

	function setAgentName($agentName){
		$this->agentName = $agentName;
	}

	function getUid(){
		return $this->uid;
	}

	function setUid($uid){
		$this->uid = $uid;
	}

	function getOs(){
		return $this->os;
	}

	function setOs($os){
		$this->os = $os;
	}

	function getCpuType(){
		return $this->cpuType;
	}

	function setCpuType($cpuType){
		$this->cpuType = $cpuType;
	}

	function getGpuBrand(){
		return $this->gpuBrand;
	}

	function setGpuBrand($gpuBrand){
		$this->gpuBrand = $gpuBrand;
	}

	function getGpuDriver(){
		return $this->gpuDriver;
	}

	function setGpuDriver($gpuDriver){
		$this->gpuDriver = $gpuDriver;
	}

	function getGpus(){
		return $this->gpus;
	}

	function setGpus($gpus){
		$this->gpus = $gpus;
	}

	function getHcVersion(){
		return $this->hcVersion;
	}

	function setHcVersion($hcVersion){
		$this->hcVersion = $hcVersion;
	}

	function getCmdPars(){
		return $this->cmdPars;
	}

	function setCmdPars($cmdPars){
		$this->cmdPars = $cmdPars;
	}

	function getWait(){
		return $this->wait;
	}

	function setWait($wait){
		$this->wait = $wait;
	}

	function getIgnoreErrors(){
		return $this->ignoreErrors;
	}

	function setIgnoreErrors($ignoreErrors){
		$this->ignoreErrors = $ignoreErrors;
	}

	function getIsActive(){
		return $this->isActive;
	}

	function setIsActive($isActive){
		$this->isActive = $isActive;
	}

	function getIsTrusted(){
		return $this->isTrusted;
	}

	function setIsTrusted($isTrusted){
		$this->isTrusted = $isTrusted;
	}

	function getToken(){
		return $this->token;
	}

	function setToken($token){
		$this->token = $token;
	}

	function getLastAct(){
		return $this->lastAct;
	}

	function setLastAct($lastAct){
		$this->lastAct = $lastAct;
	}

	function getLastTime(){
		return $this->lastTime;
	}

	function setLastTime($lastTime){
		$this->lastTime = $lastTime;
	}

	function getLastIp(){
		return $this->lastIp;
	}

	function setLastIp($lastIp){
		$this->lastIp = $lastIp;
	}

	function getUserId(){
		return $this->userId;
	}

	function setUserId($userId){
		$this->userId = $userId;
	}

	function getCpuOnly(){
		return $this->cpuOnly;
	}

	function setCpuOnly($cpuOnly){
		$this->cpuOnly = $cpuOnly;
	}
}
