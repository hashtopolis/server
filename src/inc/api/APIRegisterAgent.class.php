<?php

use DBA\Agent;

class APIRegisterAgent extends APIBasic {
  public function execute($QUERY = array()) {
    global $FACTORIES;
    
    //check required values
    if (!PQueryRegister::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::REGISTER, "Invalid registering query!");
    }
    
    $qF = new QueryFilter(RegVoucher::VOUCHER, $QUERY[PQueryRegister::VOUCHER], "=");
    $voucher = $FACTORIES::getRegVoucherFactory()->filter(array($FACTORIES::FILTER => array($qF)), true);
    if ($voucher == null) {
      $this->sendErrorResponse(PActions::REGISTER, "Provided voucher does not exist.");
    }
    
    $name = htmlentities($QUERY[PQueryRegister::AGENT_NAME], ENT_QUOTES, "UTF-8");
    
    //create access token & save agent details
    $token = Util::randomString(10);
    $agent = new Agent(0, $name, "", -1, "", "", 0, 1, 0, $token, PActions::REGISTER, time(), Util::getIP(), null, 0);
    $FACTORIES::getRegVoucherFactory()->delete($voucher);
    if ($FACTORIES::getAgentFactory()->save($agent)) {
      $payload = new DataSet(array(DPayloadKeys::AGENT => $agent));
      NotificationHandler::checkNotifications(DNotificationType::NEW_AGENT, $payload);
      
      $this->sendResponse(array(
          PQueryRegister::ACTION => PActions::REGISTER,
          PResponseRegister::RESPONSE => PValues::SUCCESS,
          PResponseRegister::TOKEN => $token
        )
      );
    }
    else {
      $this->sendErrorResponse(PActions::REGISTER, "Could not register you to server: Saving failed!");
    }
  }
}