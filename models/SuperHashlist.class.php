<?php

class SuperHashlist extends AbstractModel {
	private $modelName = "SuperHashlist";
	
	// Modelvariables
	private $superHashlistId;
	
	
	function __construct($superHashlistId) {
		$this->superHashlistId = $superHashlistId;

	}
	
	function getKeyValueDict() {
		$dict = array ();
		$dict['superHashlistId'] = $this->superHashlistId;
		
		return $dict;
	}
	
	function getPrimaryKey() {
		return "superHashlistId";
	}
	
	function getPrimaryKeyValue() {
		return $this->superHashlistId;
	}
	
	function getId() {
		return $this->superHashlistId;
	}
	
	function setId($id) {
		$this->superHashlistId = $id;
	}
}
