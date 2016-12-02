<?php

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
    global $LOGIN;
    
    switch ($action) {
      case 'releasedelete':
        $this->delete();
        break;
      case 'newhashcatp':
        $this->newHashcat();
        break;
      default:
        UI::addMessage("danger", "Invalid action!");
        break;
    }
  }
  
  private static function newHashcat(){
    global $FACTORIES;
  
    // new hashcat release
    $version = $_POST["version"];
    $url = $_POST["url"];
    $common_files = $_POST["common_files"];
    $common_files = str_replace("\r\n", "\n", $common_files);
    $rootdir = $_POST["rootdir"];
    if (strlen($version ) == 0) {
      UI::addMessage("danger", "You must specify a version!");
      return;
    }
    
    $hashcat = new HashcatRelease(0, $version, time(), $url, $common_files, "", "", $rootdir, 0);
    $hashcat = $FACTORIES::getHashcatReleaseFactory()->save($hashcat);
    if($hashcat == null){
      UI::addMessage("danger", "Could not create new hashcat release!");
    }
    else{
      header("Location: hashcat.php");
      die();
    }
  }
  
  private static function delete(){
    global $FACTORIES;
  
    // delete hashcat release
    $release = $FACTORIES::getHashcatReleaseFactory()->get($_POST['release']);
    AbstractModelFactory::getDB()->query("START TRANSACTION");
    $qF = new QueryFilter("hcVersion", $release->getVersion(), "=");
    $agents = $FACTORIES::getAgentFactory()->filter(array('filter' => $qF));
    if(sizeof($agents)){
      UI::addMessage("danger", "There are registered agents running this Hashcat version!");
      return;
    }
    $FACTORIES::getHashcatReleaseFactory()->delete($release);
    AbstractModelFactory::getDB()->query("COMMIT");
    Util::refresh();
  }
}