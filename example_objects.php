<?php
/*
* This file is part of Bricky (https://github.com/s3inlc/bricky)
* Copyright 2016 by Sein Coray
*/

include("inc/template.class.php");
include("inc/lang.class.php");

class Car {
	private $name;
	private $type;
	
	public function __construct($n, $t){
		$this->name = $n;
		$this->type = $t;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function getType(){
		return $this->type;
	}
}

//create some cars
$cars = array(new Car('Car1', 'SUV'), new Car('Car2', 'Hybrid'), new Car('Car3', 'Sportscar'), new Car('Car4', 'Hybrid'));

$TEMPLATE = new Bricky\Template("example_objects");

$OBJECTS = array();
$OBJECTS['cars'] = $cars;

echo $TEMPLATE->render($OBJECTS);


