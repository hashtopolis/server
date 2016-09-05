<?php
class ComparisonFilter {
	private $key1;
	private $key2;
	private $operator;

	function __construct($key1, $key2, $operator){
		$this->key1 = $key1;
		$this->key2 = $key2;
		$this->operator = $operator;
	}

	function getQueryString($table = ""){
		if($table != ""){
			$table = $table . ".";
		}
		return $table . $this->key1 . $this->operator . $table . $this->key2;
	}

	function getValue(){
		return null;
	}
}
?>
