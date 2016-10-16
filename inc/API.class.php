<?php
class API{
	private static function updateAgent($QUERY, $agent){
		global $FACTORIES;
		
		$agent->setLastIp(Util::getIP());
		$agent->setLastAction($QUERY['action']);
		$agent->setLastTime(time());
		$FACTORIES->getAgentFactory()->update($agent);
	}
	
	public static function registerAgent($QUERY){
		global $FACTORIES;
		
		$qF = new QueryFilter("voucher", $QUERY['voucher'], "=");
		$voucher = $FACTORIES::getRegVoucherFactory()->filter(array('filter' => array($qF)), true);
		if($voucher !== null){
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
			$FACTORIES::getRegVoucherFactory()->delete($voucher);
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

	public static function loginAgent(){
		global $FACTORIES, $TOKEN, $SEPARATOR, $CONFIG;
		
		// login to master server with previously provided token
		$qF = new QueryFilter("token", $TOKEN, "=");
		$agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
		if($agent !== null){
			API::updateAgent($QUERY, $agent);
			echo "log_ok".$SEPARATOR.$gpu.$SEPARATOR.$CONFIG->getVal("agenttimeout");
		}
		else{
			// token was not found
			echo "log_unknown".$SEPARATOR."Unknown token, register again";
		}
	}
}