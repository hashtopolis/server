<?php
class API{
	private static function updateAgent($QUERY, $agent){
		global $FACTORIES;
		
		$agent->setLastIp(Util::getIP());
		$agent->setLastAction($QUERY['action']);
		$agent->setLastTime(time());
		$FACTORIES->getAgentFactory()->update($agent);
	}
	
	private static function checkValues($QUERY, $values){
		foreach($values as $value){
			if(!isset($QUERY[$value])){
				return false;
			}
		}
		return true;
	}
	
	private static function sendErrorResponse($action, $msg){
		$ANS = array();
		$ANS['action'] = $action;
		$ANS['response'] = "ERROR";
		$ANS['message'] = $msg;
		header("Content-Type: application/json");
		echo json_encode($ANS, true);
		die();
	}

	private static function sendResponse($RESPONSE){
		header("Content-Type: application/json");
		echo json_encode($RESPONSE, true);
		die();
	}
	
	public static function registerAgent($QUERY){
		global $FACTORIES, $CONFIG;
		
		//check required values
		if(API::checkValues($QUERY, array('voucher', 'gpus', 'uid', 'name', 'os'))){
			API::sendErrorResponse("register", "Invalid registering query!");
		}
		
		$qF = new QueryFilter("voucher", $QUERY['voucher'], "=");
		$voucher = $FACTORIES::getRegVoucherFactory()->filter(array('filter' => array($qF)), true);
		if($voucher == null){
			API::sendErrorResponse("register", "Provided voucher does not exist.");
		}
		
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
		
		//create access token & save agent details
		$token = Util::randomString(10);
		$gpu = htmlentities($gpu, false, "UTF-8");
		$agent = new Agent(0, $name, $uid, $os, $gpu, "", "", $CONFIG->getVal('agenttimeout'), "", 1, 0, $token, "", 0, Util::getIP(), 0, $cpuOnly);
		$FACTORIES::getRegVoucherFactory()->delete($voucher);
		if($FACTORIES::getAgentFactory()->save($agent)){
			$RESPONSE = array("action" => "register", "response" => "SUCCESS", "token" => $token);
			API::sendResponse($RESPONSE);
		}
		else{
			API::sendErrorResponse("register", "Could not register you to server.");
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