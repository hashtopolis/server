<?php
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
	
	public function getAllValues(){
		return $this->values;
	}
}