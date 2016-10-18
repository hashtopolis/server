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
	
	public static function sendErrorResponse($action, $msg){
		$ANS = array();
		$ANS['action'] = $action;
		$ANS['response'] = "ERROR";
		$ANS['message'] = $msg;
		header("Content-Type: application/json");
		echo json_encode($ANS, true);
		die();
	}

	public static function checkToken($QUERY){
		global $FACTORIES;
		
		$qF = new QueryFilter("token", $QUERY['token'], "=");
		$token = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
		if($token != null){
			return true;
		}
		return false;
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
			API::sendResponse(array("action" => "register", "response" => "SUCCESS", "token" => $token));
		}
		else{
			API::sendErrorResponse("register", "Could not register you to server.");
		}
	}

	public static function loginAgent($QUERY){
		global $FACTORIES, $CONFIG;
		
		if(API::checkValues($QUERY, array('token'))){
			API::sendErrorResponse("login", "Invalid login query!");
		}
		
		// login to master server with previously provided token
		$qF = new QueryFilter("token", $QUERY['token'], "=");
		$agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
		if($agent == null){
			// token was not found
			API::sendErrorResponse("login", "Unknown token, register again!");
		}
		API::updateAgent($QUERY, $agent);
		API::sendResponse(array("action" => "login", "response" => "SUCCESS", "timeout" => $CONFIG->getVal("agenttimeout")));
	}

	public static function checkClientUpdate($QUERY){
		global $SCRIPTVERSION, $SCRIPTNAME;
		
		// check if provided hash is the same as script and send file contents if not
		if(API::checkValues($QUERY, array('version'))){
			API::sendErrorResponse('update', 'Version value missing!');
		}
		
		$version = $QUERY['version'];
		
		if($version != $SCRIPTVERSION){
			API::sendResponse(array('action' => 'update', 'response' => 'SUCCESS', 'version' => 'NEW', 'data' => file_get_contents(dirname(__FILE__)."/../static/$SCRIPTNAME")));
		}
		else{
			API::sendResponse(array('action' => 'update', 'response' => 'SUCCESS', 'version' => 'OK'));
		}
	}

	public static function downloadApp($QUERY){
		global $FACTORIES;
		
		if(API::checkValues($QUERY, array('token', 'type'))){
			API::sendErrorResponse("download", "Invalid download query!");
		}
		$qF = new QueryFilter("token", $QUERY['token'], "=");
		$agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
		
		// provide agent with requested download
		switch($QUERY['type']){
			case "7zr":
				// downloading 7zip
				$filename = "7zr".($agent->getOs() == 1)?".exe":"";
				header_remove("Content-Type");
				header('Content-Type: application/octet-stream');
				header("Content-Disposition: attachment; filename=\"".$filename."\"");
				echo file_get_contents("static/".$filename);
				die();
			case "hashcat":
				if(API::checkValues($QUERY, array('version'))){
					API::sendErrorResponse("download", "Invalid download (hashcat) query!");
				}
				$oF = new OrderFilter("time", "DESC LIMIT 1");
				$hashcat = $FACTORIES::getHashcatReleaseFactory()->filter(array('order' => array($oF)), true);
				if($hashcat == null){
					API::sendErrorResponse("download", "No Hashcat release available!");
				}
				
				$postfix = array("bin", "exe");
				$executable = "hashcat64".$postfix[$agent->getOs()];
				
				if($QUERY['version'] == $hashcat->getVersion() && (!isset($QUERY['force']) || $QUERY['force'] != '1')){
					API::sendResponse(array("action" => 'download', 'response' => 'SUCCESS', 'version' => 'OK', 'executable' => $executable));
				}
				
				$url = $hashcat->getUrl();
				$files = explode("\n", str_replace(" ", "\n", $hashcat->getCommonFiles()));
				$files[] = $executable;
				$rootdir = $hashcat->getRootdir();
				
				$agent->setHcVersion($hashcat->getVersion());
				$FACTORIES::getAgentFactory()->update($agent);
				API::sendResponse(array('action' => 'download', 'response' => 'SUCCESS', 'version' => 'NEW', 'url' => $url, 'files' => $files, 'rootdir' => $rootdir, 'executable' => $executable));
				break;
			default:
				API::sendErrorResponse('download', "Unknown download type!");
		}
	}

	public static function agentError($QUERY){
		global $FACTORIES;
		
		//check required values
		if(API::checkValues($QUERY, array('token', 'task', 'message'))){
			API::sendErrorResponse("error", "Invalid error query!");
		}

		//check agent and task
		$qF = new QueryFilter("token", $QUERY['token'], "=");
		$agent = $FACTORIES::getAgentFactory()->filter(array('filter' => array($qF)), true);
		$task = $FACTORIES::getTaskFactory()->get($QUERY['task']);
		if($task == null){
			API::sendErrorResponse("error", "Invalid task!");
		}
		
		//check assignment
		$qF1 = new QueryFilter("agentId", $agent->getId(), "=");
		$qF2 = new QueryFilter("taskId", $task->getId(), "=");
		$assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF1, $qF2)), true);
		if($assignment == null){
			API::sendErrorResponse("error", "You are not assigned to this task!");
		}
		
		//save error message
		$error = new AgentError(0, $agent->getId(), $task->getId(), time(), $message);
		$FACTORIES::getAgentErrorFactory()->save($error);
		
		if($agent->getIgnoreErrors() == 0){
			//deactivate agent 
			$agent->setIsActive(0);
			$FACTORIES::getAgentFactory()->update($agent);
		}
		API::sendResponse(array('action' => 'error', 'response' => 'SUCCESS'));
	}

	public static function getFile($QUERY){
		global $FACTORIES;
		
		//check required values
		if(API::checkValues($QUERY, array('token', 'task', 'filename'))){
			API::sendErrorResponse("file", "Invalid file query!");
		}
		
		// let agent download adjacent files
		$task = $FACTORIES::getTaskFactory()->get($QUERY['task']);
		if($task == null){
			API::sendErrorResponse('file', "Invalid task!");
		}
		
		$filename = $QUERY['filename'];
		$qF = new QueryFilter("filename", $filename, "=");
		$file = $FACTORIES::getFileFactory()->filter(array('filter' => array($qF)), true);
		if($file == null){
			API::sendErrorResponse('file', "Invalid file!");
		}
		
		$qF1 = new QueryFilter("taskId", $task->getId(), "=");
		$qF2 = new QueryFilter("agentId", $agent->getId(), "=");
		$assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF1, $qF2)), true);
		if($assignment == null){
			API::sendErrorResponse('file', "Client is not assigned to this task!");
		}
		
		$qF1 = new QueryFilter("taskId", $task->getId(), "=");
		$qF2 = new QueryFilter("fileId", $file->getId(), "=");
		$taskFile = $FACTORIES::getTaskFileFactory()->filter(array('filter' => array($qF1, $qF2)), true);
		if($taskFile == null){
			API::sendErrorResponse('file', "This files is not used for the specified task!");
		}
		
		if($agent->getIsTrusted() < $file->getSecret()){
			API::sendErrorResponse('file', "You have no access to get this file!");
		}
		API::sendResponse(array('action' => 'file', 'response' => 'SUCCESS', 'url' => 'get.php?file='.$file->getId()."&token=".$agent->getToken()));
	}
}