<?php
class API{
	public static function registerAgent($key){
		global $FACTORIES, $SEPARATOR;
		
		$qF = new QueryFilter("voucher", $key, "=");
		$voucher = $FACTORIES::getRegVoucherFactory()->filter(array('filter' => array($qF)));
		if($voucher !== null && sizeof($voucher) > 0){
			$voucher = $voucher[0];
			$FACTORIES::getRegVoucherFactory()->delete($voucher);
			$gpu = $_POST["gpus"];
			$uid = htmlentities($_POST["uid"], false, "UTF-8");
			$name = htmlentities($_POST["name"], false, "UTF-8");
			$os = intval($_POST["os"]);
		
			//determine if the client has cpu only
			$cpuOnly = 1;
			foreach(explode($SEPARATOR, strtolower($gpu)) as $card){
				if((strpos($card, "amd") !== false) || (strpos($card, "ati ") !== false) || (strpos($card, "radeon") !== false) || strpos($card, "nvidia") !== false){
					$cpuOnly = 0;
				}
			}
			
			//create access token
			$token = Util::randomString(10);
		
			//save agent details
			$gpu = htmlentities($gpu, false, "UTF-8");
			$agent = new Agent(0, $name, $uid, $os, $gpu, "", "", "", "", 1, 0, $token, "", 0, Util::getIP(), 0, $cpuOnly);
			if($FACTORIES::getAgentFactory()->save($agent)){
				echo "reg_ok".$SEPARATOR.$token;
			}
			else{
				echo "reg_nok".$SEPARATOR."Could not register you to server.";
			}
		}
		else{
			echo "reg_nok".$SEPARATOR."Provided voucher does not exist.";
		}
	}
}