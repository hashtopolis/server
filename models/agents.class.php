<?php

class agents extends AbstractModel {
	private $modelName = "agents";
	
	// Modelvariables
	private $id;
	private $name;
	private $uid;
	private $os;
	private $cputype;
	private $gpubrand;
	private $gpudriver;
	private $gpus;
	private $hcversion;
	private $cmdpars;
	private $wait;
	private $ignoreerrors;
	private $active;
	private $trusted;
	private $token;
	private $lastact;
	private $lasttime;
	private $lastip;
	private $userId;
	
	
	function __construct($id, $name, $uid, $os, $cputype, $gpubrand, $gpudriver, $gpus, $hcversion, $cmdpars, $wait, $ignoreerrors, $active, $trusted, $token, $lastact, $lasttime, $lastip, $userId) {
		$this->id = $id;
		$this->name = $name;
		$this->uid = $uid;
		$this->os = $os;
		$this->cputype = $cputype;
		$this->gpubrand = $gpubrand;
		$this->gpudriver = $gpudriver;
		$this->gpus = $gpus;
		$this->hcversion = $hcversion;
		$this->cmdpars = $cmdpars;
		$this->wait = $wait;
		$this->ignoreerrors = $ignoreerrors;
		$this->active = $active;
		$this->trusted = $trusted;
		$this->token = $token;
		$this->lastact = $lastact;
		$this->lasttime = $lasttime;
		$this->lastip = $lastip;
		$this->userId = $userId;

	}
	
	function getKeyValueDict() {
		$dict = array ();
		$dict['id'] = $this->id;
		$dict['name'] = $this->name;
		$dict['uid'] = $this->uid;
		$dict['os'] = $this->os;
		$dict['cputype'] = $this->cputype;
		$dict['gpubrand'] = $this->gpubrand;
		$dict['gpudriver'] = $this->gpudriver;
		$dict['gpus'] = $this->gpus;
		$dict['hcversion'] = $this->hcversion;
		$dict['cmdpars'] = $this->cmdpars;
		$dict['wait'] = $this->wait;
		$dict['ignoreerrors'] = $this->ignoreerrors;
		$dict['active'] = $this->active;
		$dict['trusted'] = $this->trusted;
		$dict['token'] = $this->token;
		$dict['lastact'] = $this->lastact;
		$dict['lasttime'] = $this->lasttime;
		$dict['lastip'] = $this->lastip;
		$dict['userId'] = $this->userId;
		
		return $dict;
	}
	
	function getPrimaryKey() {
		return "id";
	}
	
	function getPrimaryKeyValue() {
		return $this->id;
	}
	
	function getId() {
		return $this->id;
	}
	
	function setId($id) {
		$this->id = $id;
	}

	function getName(){
		return $this->name;
	}

	function setName($name){
		$this->name = $name;
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

	function getCputype(){
		return $this->cputype;
	}

	function setCputype($cputype){
		$this->cputype = $cputype;
	}

	function getGpubrand(){
		return $this->gpubrand;
	}

	function setGpubrand($gpubrand){
		$this->gpubrand = $gpubrand;
	}

	function getGpudriver(){
		return $this->gpudriver;
	}

	function setGpudriver($gpudriver){
		$this->gpudriver = $gpudriver;
	}

	function getGpus(){
		return $this->gpus;
	}

	function setGpus($gpus){
		$this->gpus = $gpus;
	}

	function getHcversion(){
		return $this->hcversion;
	}

	function setHcversion($hcversion){
		$this->hcversion = $hcversion;
	}

	function getCmdpars(){
		return $this->cmdpars;
	}

	function setCmdpars($cmdpars){
		$this->cmdpars = $cmdpars;
	}

	function getWait(){
		return $this->wait;
	}

	function setWait($wait){
		$this->wait = $wait;
	}

	function getIgnoreerrors(){
		return $this->ignoreerrors;
	}

	function setIgnoreerrors($ignoreerrors){
		$this->ignoreerrors = $ignoreerrors;
	}

	function getActive(){
		return $this->active;
	}

	function setActive($active){
		$this->active = $active;
	}

	function getTrusted(){
		return $this->trusted;
	}

	function setTrusted($trusted){
		$this->trusted = $trusted;
	}

	function getToken(){
		return $this->token;
	}

	function setToken($token){
		$this->token = $token;
	}

	function getLastact(){
		return $this->lastact;
	}

	function setLastact($lastact){
		$this->lastact = $lastact;
	}

	function getLasttime(){
		return $this->lasttime;
	}

	function setLasttime($lasttime){
		$this->lasttime = $lasttime;
	}

	function getLastip(){
		return $this->lastip;
	}

	function setLastip($lastip){
		$this->lastip = $lastip;
	}

	function getUserId(){
		return $this->userId;
	}

	function setUserId($userId){
		$this->userId = $userId;
	}
}
