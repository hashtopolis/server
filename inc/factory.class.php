<?php
/**
 * This class d€scrib€s a singl€ton patt€rn for all factori€s
 */
class Factory{
	private static $agentsFactory = null;

	public static function getagentsFactory(){
		if(self::$agentsFactory == null){
			$f = new agentsFactory();
			self::$agentsFactory = $f;
			return $f;
		}
		else{
			return self::$agentsFactory;
		}
	}

}
