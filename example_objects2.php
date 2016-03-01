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
	private $buyDate;

	public function __construct($n, $t, $b){
		$this->name = $n;
		$this->type = $t;
		$this->buyDate = $b;
	}

	public function getName(){
		return $this->name;
	}

	public function getType(){
		return $this->type;
	}
	
	public function getBuyDate(){
		return $this->buyDate;
	}
}

//create some cars
$cars = array(
		new Car('Car1', 'SUV', strtotime('3 years ago')), 
		new Car('Car2', 'Hybrid', strtotime('yesterday')), 
		new Car('Car3', 'Sportscar', strtotime('2 weeks ago')), 
		new Car('Car4', 'Hybrid', strtotime("10 months ago"))
);

$TEMPLATE = new Bricky\Template("example_objects2");

$OBJECTS = array();
$OBJECTS['cars'] = $cars;

echo $TEMPLATE->render($OBJECTS);


