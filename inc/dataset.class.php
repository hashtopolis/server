<?php
/*
 * $Id: dataset.class.php 1346 2015-12-25 16:05:47Z sein $
 */

if(ALLOW != "yes"){
	die("No Access!");
}

class DataSet {
	private $values;

	public function setValues($arr){
		$this->values = $arr;
	}
	
	public function addValue($key, $val){
		$this->values[$key] = $val;
	}

	public function getVal($key){
		if(isset($this->values[$key])){
			return $this->values[$key];
		}
		return false;
	}
}