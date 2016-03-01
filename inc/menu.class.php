<?php
/**
 * This class is a small workaround to make it possible for the menu to request which page
 * is active through a object passed to the template system.
 * 
 * @author Sein
 */
class Menu {
	private $active;
	
	/**
	 * Construct a new Menu class.
	 * 
	 * @param string $name name of the identifier in the menu to set active
	 */
	public function __construct($name = ""){
		$this->active = $name;
	}
	
	/**
	 * Set which menu point should be active
	 * 
	 * @param string $name identifier used on checking for active
	 */
	public function setActive($name){
		$this->active = $name;
	}
	
	/**
	 * Request for a identifier if it is active or no.
	 * 
	 * @param string $name identifier to check
	 * @return string with " class='active'" if is active or "" if not
	 */
	public function isActive($name, $classonly = ""){
		if($classonly == 'classonly'){
			$split = explode("_", $this->active);
			if($split[0] == $name){
				return " active";
			}
			return "";
		}
		if($name == $this->active){
			return " class='active'";
		}
		return "";
	}
}








