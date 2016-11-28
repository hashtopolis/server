<?php

class StoredValue extends AbstractModel {
	private $modelName = "StoredValue";
	
	// Modelvariables
	private $storedValueId;
	private $val;
	
	
	function __construct($storedValueId, $val) {
		$this->storedValueId = $storedValueId;
		$this->val = $val;

	}
	
	function getKeyValueDict() {
		$dict = array ();
		$dict['storedValueId'] = $this->storedValueId;
		$dict['val'] = $this->val;
		
		return $dict;
	}
	
	function getPrimaryKey() {
		return "storedValueId";
	}
	
	function getPrimaryKeyValue() {
		return $this->storedValueId;
	}
	
	function getId() {
		return $this->storedValueId;
	}
	
	function setId($id) {
		$this->storedValueId = $id;
	}

	function getVal(){
		return $this->val;
	}

	function setVal($val){
		$this->val = $val;
	}
}
