<?php
use DBA\Agent;
use DBA\HashcatRelease;
use DBA\QueryFilter;

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 10.11.16
 * Time: 14:38
 */
class HashcatHandler implements Handler {
  public function __construct($hashcatId = null) {
    //nothing
  }
  
  public function handle($action) {
    switch ($action) {
      case 'releasedelete':
        $this->delete();
        break;
      case 'newhashcatp':
        $this->newHashcat();
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
  
  private static function newHashcat() {
    /** @var $LOGIN Login */
    global $FACTORIES, $LOGIN;
    
    // new hashcat release
    $version = $_POST["version"];
    $url = $_POST["url"];
    $rootdir = $_POST["rootdir"];
    if (strlen($version) == 0) {
      UI::addMessage(UI::ERROR, "You must specify a version!");
      return;
    }
    
    $hashcat = new HashcatRelease(0, $version, time(), $url, $rootdir, 0);
    $hashcat = $FACTORIES::getHashcatReleaseFactory()->save($hashcat);
    if ($hashcat == null) {
      UI::addMessage(UI::ERROR, "Could not create new hashcat release!");
    }
    else {
      Util::createLogEntry("User", $LOGIN->getUserID(), "INFO", "New hashcat release was created: " . $version);
      header("Location: hashcat.php");
      die();
    }
  }
  
  private static function delete() {
    global $FACTORIES;
    
    // delete hashcat release
    $release = $FACTORIES::getHashcatReleaseFactory()->get($_POST['release']);
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
    $qF = new QueryFilter(Agent::HC_VERSION, $release->getVersion(), "=");
    $agents = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => $qF));
    if (sizeof($agents)) {
      UI::addMessage(UI::ERROR, "There are registered agents running this Hashcat version!");
      return;
    }
    $FACTORIES::getHashcatReleaseFactory()->delete($release);
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
    Util::refresh();
  }
}