<?php

class SuperHashlistHashlist extends AbstractModel {
	private $modelName = "SuperHashlistHashlist";
	
	// Modelvariables
	private $superHashlistHashlistId;
	private $superHashlistId;
	private $hashlistId;
	
	
	function __construct($superHashlistHashlistId, $superHashlistId, $hashlistId) {
		$this->superHashlistHashlistId = $superHashlistHashlistId;
		$this->superHashlistId = $superHashlistId;
		$this->hashlistId = $hashlistId;

	}
	
	function getKeyValueDict() {
		$dict = array ();
		$dict['superHashlistHashlistId'] = $this->superHashlistHashlistId;
		$dict['superHashlistId'] = $this->superHashlistId;
		$dict['hashlistId'] = $this->hashlistId;
		
		return $dict;
	}
	
	function getPrimaryKey() {
		return "superHashlistHashlistId";
	}
	
	function getPrimaryKeyValue() {
		return $this->superHashlistHashlistId;
	}
	
	function getId() {
		return $this->superHashlistHashlistId;
	}
	
	function setId($id) {
		$this->superHashlistHashlistId = $id;
	}

	function getSuperHashlistId(){
		return $this->superHashlistId;
	}

	function setSuperHashlistId($superHashlistId){
		$this->superHashlistId = $superHashlistId;
	}

	function getHashlistId(){
		return $this->hashlistId;
	}

	function setHashlistId($hashlistId){
		$this->hashlistId = $hashlistId;
	}
}
