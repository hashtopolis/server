<?php
/**
 * This class d€scrib€s a singl€ton patt€rn for all factori€s
 */
class Factory{
	private static $agentsFactory = null;
	private static $userFactory = null;
	private static $sessionFactory = null;
	private static $rightGroupFactory = null;

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

	public static function getUserFactory(){
		if(self::$userFactory == null){
			$f = new UserFactory();
			self::$userFactory = $f;
			return $f;
		}
		else{
			return self::$userFactory;
		}
	}

	public static function getSessionFactory(){
		if(self::$sessionFactory == null){
			$f = new SessionFactory();
			self::$sessionFactory = $f;
			return $f;
		}
		else{
			return self::$sessionFactory;
		}
	}

	public static function getRightGroupFactory(){
		if(self::$rightGroupFactory == null){
			$f = new RightGroupFactory();
			self::$rightGroupFactory = $f;
			return $f;
		}
		else{
			return self::$rightGroupFactory;
		}
	}

}
