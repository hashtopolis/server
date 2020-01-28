<?php

use DBA\QueryFilter;
use DBA\User;
use DBA\Factory;

class ForgotHandler implements Handler {
  public function __construct($configId = null) {
    //we need nothing to load
  }
  
  public function handle($action) {
    switch ($action) {
      case DForgotAction::RESET:
        $this->forgot($_POST['username'], $_POST['email']);
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
  
  private function forgot($username, $email) {
    $username = htmlentities($username, ENT_QUOTES, "UTF-8");
    $qF = new QueryFilter(User::USERNAME, $username, "=");
    $res = Factory::getUserFactory()->filter([Factory::FILTER => $qF]);
    if ($res == null || sizeof($res) == 0) {
      UI::addMessage(UI::ERROR, "No such user!");
      return;
    }
    $user = $res[0];
    if ($user->getEmail() != $email) {
      UI::addMessage(UI::ERROR, "No such user!");
      return;
    }
    $newSalt = Util::randomString(20);
    $newPass = Util::randomString(10);
    $newHash = Encryption::passwordHash($newPass, $newSalt);
    
    $tmpl = new Template("email/forgot");
    $tmplPlain = new Template("email/forgot.plain");
    $obj = array('username' => $user->getUsername(), 'password' => $newPass);
    if (Util::sendMail($user->getEmail(), "Password reset", $tmpl->render($obj), $tmplPlain->render($obj))) {
      Factory::getUserFactory()->mset($user, [User::PASSWORD_HASH => $newHash, User::PASSWORD_SALT => $newSalt, User::IS_COMPUTED_PASSWORD => 1]);
      UI::addMessage(UI::SUCCESS, "Password reset! You should receive an email soon.");
    }
    else {
      UI::addMessage(UI::ERROR, "Password reset failed because of an error when sending the email! Please check if PHP is able to send emails.");
    }
  }
}