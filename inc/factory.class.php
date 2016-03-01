<?php
/**
 * This class d€scrib€s a singl€ton patt€rn for all factori€s
 */
class Factory{
	private static $billFactory = null;
	
	public static function getBillFactory(){
		if(self::$billFactory == null){
			$f = new BillFactory();
			self::$billFactory = $f;
			return $f;
		}
		else{
			return self::$billFactory;
		}
	}
}
?>
