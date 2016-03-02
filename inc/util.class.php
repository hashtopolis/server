<?php
use Bricky\Template;
/**
 * @author Sein
 *
 * Bunch of useful static functions.
 */
class Util{
	/**
	 * Checks if a given email is of valid syntax
	 *
	 * @param string $email email address to check
	 * @return true if valid email, false if not
	 */
	public static function isValidEmail($email){
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}
	
	public static function getStaticArray($val, $id){
		$platforms = array(
				"unknown", 
				"NVidia", 
				"AMD"
		);
		$oses = array(
				"<img src='static/win.png' alt='Win' title='Windows'>",
				"<img src='static/unix.png' alt='Unix' title='Linux'>"
		);
		switch($id){
			case 'os':
				return $oses[$val];
				break;
			case 'platforms':
				return $platforms[$val];
				break;
		}
		return "";
	}

	public static function shortenstring($co, $kolik){
		// shorten string that would be too long
		$rest =  "<span title='$co'>";
		if(strlen($co) > $kolik){
			$ret .= substr($co, 0, $kolik - 3) . "...";
		}
		else{
			$ret .= $co;
		}
		$ret .= "</span>";
		return $ret;
	}
	
	public static function prefixNum($num, $size){
		$string = "".$num;
		while(strlen($string) < $size){
			$string = "0".$string;
		}
		return $string;
	}

	/**
	 * Converts a given string to hex code.
	 *
	 * @param string $string string to convert
	 * @return string converted string into hex
	 */
	public static function strToHex($string){
		return implode(unpack("H*",$string));
	}

	/**
	 * This sends a given email with text and subject to the address.
	 *
	 * @param string $address email address of the receiver
	 * @param string $subject subject of the email
	 * @param string $text html content of the email
	 * @return true on success, false on failure
	 */
	public static function sendMail($address, $subject, $text){
		$header = "Content-type: text/html; charset=utf8\r\n";
		//TODO: set sender...
		$header .= "From: Sein Coray Informatics <noreply@coray-it.ch>\r\n";
		if(!mail($address, $subject, $text, $header)){
			return false;
		}
		else{
			return true;
		}
	}

	/**
	 * Generates a random string with mixedalphanumeric chars
	 *
	 * @param int $length length of random string to generate
	 * @return string random string
	 */
	public static function randomString($length){
		$charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$result = "";
		for($x=0;$x<$length;$x++){
			$result .= $charset[rand(0, strlen($charset) - 1)];
		}
		return $result;
	}

	public static function createPrefixedString($table, $dict) {
		$concat = "";
		$counter = 0;
		$size = count($dict);

		foreach($dict as $key => $val) {
			if($counter < $size - 1) {
				$concat = $concat. "`".$table."`"."."."`".$key."`"." AS ".$val.",";
				$counter = $counter + 1;
			} else {
				$concat = $concat. "`".$table."`"."."."`".$key."`"." AS ".$val;
				$counter = $counter + 1;
			}
		}

		return $concat;
	}

	public static function startsWith($search, $pattern) {
	  	if(strpos($search, $pattern) === 0) {
			return true;
		} else {
			return false;
		}
	}

	public static function endsWith($haystack, $needle) {
	    // search forward starting from end minus needle length characters
	    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
	}

	public static function deleteDuplicatesFromJoinResult($dict) {
		$pkStack = array();
		$nonDuplicates = array();
		foreach($dict as $elem) {
			if(!in_array($elem->getId(),$pkStack)) {
				array_push($pkStack, $elem->getId());
				array_push($nonDuplicates, $elem);
			}
		}
		return $nonDuplicates;
	}
}
