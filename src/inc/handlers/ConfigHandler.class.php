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
      default:
        UI::printError("FATAL", "Invalid action!");
        break;
      //TODO: implement the handler for the global actions
    }
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