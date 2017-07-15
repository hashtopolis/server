<?php
use DBA\Hash;
use DBA\Hashlist;
use DBA\JoinFilter;
use DBA\LikeFilter;
use DBA\QueryFilter;

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 13.04.17
 * Time: 12:43
 */
class SearchHandler implements Handler {
  public function __construct($id = null) {
    // nothing
  }
  
  public function handle($action) {
    switch ($action) {
      case 'search':
        $this->search();
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
  
  private function search() {
    global $FACTORIES, $OBJECTS;
    
    $query = $_POST['search'];
    if (strlen($query) == 0) {
      UI::addMessage(UI::ERROR, "Search query cannot be empty!");
      return;
    }
    $query = str_replace("\r\n", "\n", $query);
    $query = explode("\n", $query);
    $resultEntries = array();
    $hashlists = new DataSet();
    foreach ($query as $queryEntry) {
      if (strlen($queryEntry) == 0) {
        continue;
      }
      
      // test if hash contains salt
      if (strpos($queryEntry, ":") !== false) {
        $split = explode(":", $queryEntry);
        $hash = $split[0];
        unset($split[0]);
        $salt = implode(":", $split);
      }
      else {
        $hash = $queryEntry;
        $salt = "";
      }
      
      // TODO: add option to select if exact match or like match
      
      $filters = array();
      $filters[] = new LikeFilter(Hash::HASH, "%" . $hash . "%");
      if (strlen($salt) > 0) {
        $filters[] = new QueryFilter(Hash::SALT, $salt, "=");
      }
      $jF = new JoinFilter($FACTORIES::getHashlistFactory(), Hash::HASHLIST_ID, Hashlist::HASHLIST_ID);
      $joined = $FACTORIES::getHashFactory()->filter(array($FACTORIES::FILTER => $filters, $FACTORIES::JOIN => $jF));
      
      $qF = new LikeFilter(Hash::PLAINTEXT, "%" . $queryEntry . "%");
      $joined2 = $FACTORIES::getHashFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
      for ($i = 0; $i < sizeof($joined2['Hash']); $i++) {
        $joined['Hash'][] = $joined2['Hash'][$i];
        $joined['Hashlist'][] = $joined2['Hashlist'][$i];
      }
      
      $resultEntry = new DataSet();
      if (sizeof($joined['Hash']) == 0) {
        $resultEntry->addValue("found", false);
        $resultEntry->addValue("query", $queryEntry);
      }
      else {
        $resultEntry->addValue("found", true);
        $resultEntry->addValue("query", $queryEntry);
        $matches = array();
        for ($i = 0; $i < sizeof($joined['Hash']); $i++) {
          /** @var $hash Hash */
          $hash = $joined['Hash'][$i];
          $matches[] = $hash;
          if ($hashlists->getVal($hash->getHashlistId()) == false) {
            $hashlists->addValue($hash->getHashlistId(), $joined['Hashlist'][$i]);
          }
        }
        $resultEntry->addValue("matches", $matches);
      }
      $resultEntries[] = $resultEntry;
    }
    $OBJECTS['resultEntries'] = $resultEntries;
    $OBJECTS['hashlists'] = $hashlists;
    $OBJECTS['result'] = true;
    UI::addMessage(UI::SUCCESS, "Searched for " . sizeof($resultEntries) . " entries!");
  }
}