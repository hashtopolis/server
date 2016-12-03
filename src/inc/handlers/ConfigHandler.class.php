<?php
/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 18.11.16
 * Time: 20:21
 */

class ConfigHandler implements Handler {
  public function __construct($configId = null) {
    //we need nothing to load
  }
  
  public function handle($action) {
    switch ($action) {
      case 'update':
        $this->updateConfig();
        break;
      case 'rebuildcache':
        $this->rebuildCache();
        break;
      default:
        UI::addMessage("danger", "Invalid action!");
        break;
      //TODO: implement the handler for the global actions -> see issue #18
    }
  }
  
  private function rebuildCache(){
    global $FACTORIES;
  
    $correctedChunks = 0;
    $correctedHashlists = 0;
    
    //check chunks
    AbstractModelFactory::getDB()->query("START TRANSACTION");
    $jF1 = new JoinFilter($FACTORIES::getTaskFactory(), "taskId", "taskId", $FACTORIES::getChunkFactory());
    $jF2 = new JoinFilter($FACTORIES::getHashlistFactory(), "hashlistId", "hashlistId", $FACTORIES::getTaskFactory());
    $joined = $FACTORIES::getChunkFactory()->filter(array('join' => array($jF1, $jF2)));
    for($i=0;$i<sizeof($joined['Chunk']);$i++){
      $chunk = $joined['Chunk'][$i];
      $hashFactory = $FACTORIES::getHashFactory();
      if($joined['Hashlist'][$i]->getFormat() == 3){
        $hashlists = Util::checkSuperHashlist($joined['Hashlist'][$i]);
        if($hashlists[0]->getFormat() != 0){
          $hashFactory = $FACTORIES::getHashBinaryFactory();
        }
      }
      $qF1 = new QueryFilter("chunkId", $chunk->getId(), "=");
      $qF2 = new QueryFilter("isCracked", "1", "=");
      $count = $hashFactory->countFilter(array('filter' => array($qF1, $qF2)));
      if($count != $chunk->getCracked()){
        $correctedChunks++;
        $chunk->setCracked($count);
        $FACTORIES::getChunkFactory()->update($chunk);
      }
    }
    AbstractModelFactory::getDB()->query("COMMIT");
    
    //check hashlists
    AbstractModelFactory::getDB()->query("START TRANSACTION");
    $qF = new QueryFilter("format", "3", "<>");
    $hashlists = $FACTORIES::getHashlistFactory()->filter(array('filter' => $qF));
    foreach($hashlists as $hashlist){
      $qF1 = new QueryFilter("hashlistId", $hashlist->getId(), "=");
      $qF2 = new QueryFilter("isCracked", "1", "=");
      $hashFactory = $FACTORIES::getHashFactory();
      if($hashlist->getFormat() != 0){
        $hashFactory = $FACTORIES::getHashBinaryFactory();
      }
      $count = $hashFactory->countFilter(array('filter' => array($qF1, $qF2)));
      if($count != $hashlist->getCracked()){
        $correctedHashlists++;
        $hashlist->setCracked($count);
        $FACTORIES::getHashlistFactory()->update($hashlist);
      }
    }
    AbstractModelFactory::getDB()->query("COMMIT");
  
    //check superhashlists
    AbstractModelFactory::getDB()->query("START TRANSACTION");
    $qF = new QueryFilter("format", "3", "=");
    $hashlists = $FACTORIES::getHashlistFactory()->filter(array('filter' => $qF));
    foreach($hashlists as $hashlist){
      $children = Util::checkSuperHashlist($hashlist);
      $cracked = 0;
      foreach($children as $child){
        $cracked += $child->getCracked();
      }
      if($cracked != $hashlist->getCracked()){
        $correctedHashlists++;
        $hashlist->setCracked($cracked);
        $FACTORIES::getHashlistFactory()->update($hashlist);
      }
    }
    AbstractModelFactory::getDB()->query("COMMIT");
    
    UI::addMessage("success", "Updated all chunks and hashlists. Corrected $correctedChunks chunks and $correctedHashlists hashlists.");
  }
  
  private function updateConfig(){
    global $OBJECTS, $FACTORIES;
    
    $CONFIG = new DataSet();
    foreach ($_POST as $item => $val) {
      if (substr($item, 0, 7) == "config_") {
        $name = substr($item, 7);
        $CONFIG->addValue($name, $val);
        $qF = new QueryFilter("item", $name, "=");
        $config = $FACTORIES::getConfigFactory()->filter(array('filter' => array($qF)), true);
        if($config == null){
          $config = new Config(0, $name, $val);
          $FACTORIES::getConfigFactory()->save($config);
        }
        else{
          $config->setValue($val);
          $FACTORIES::getConfigFactory()->update($config);
        }
      }
    }
    $OBJECTS['config'] = $CONFIG;
  }
}