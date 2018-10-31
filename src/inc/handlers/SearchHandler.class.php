<?php

use DBA\Hash;
use DBA\Hashlist;
use DBA\JoinFilter;
use DBA\LikeFilter;
use DBA\QueryFilter;
use DBA\Factory;

class SearchHandler implements Handler {
  public function __construct($id = null) {
    // nothing
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DSearchAction::SEARCH:
          AccessControl::getInstance()->checkPermission(DSearchAction::SEARCH_PERM);
          $this->search();
          break;
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch (HTException $e) {
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }
  
  /**
   * @throws HTException
   */
  private function search() {
    $query = $_POST['search'];
    if (strlen($query) == 0) {
      throw new HTException("Search query cannot be empty!");
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
      $jF = new JoinFilter(Factory::getHashlistFactory(), Hash::HASHLIST_ID, Hashlist::HASHLIST_ID);
      $joined = Factory::getHashFactory()->filter([Factory::FILTER => $filters, Factory::JOIN => $jF]);
      
      $qF = new LikeFilter(Hash::PLAINTEXT, "%" . $queryEntry . "%");
      $joined2 = Factory::getHashFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
      for ($i = 0; $i < sizeof($joined2[Factory::getHashFactory()->getModelName()]); $i++) {
        $joined[Factory::getHashFactory()->getModelName()][] = $joined2[Factory::getHashFactory()->getModelName()][$i];
        $joined[Factory::getHashlistFactory()->getModelName()][] = $joined2[Factory::getHashlistFactory()->getModelName()][$i];
      }
      
      $resultEntry = new DataSet();
      if (sizeof($joined[Factory::getHashFactory()->getModelName()]) == 0) {
        $resultEntry->addValue("found", false);
        $resultEntry->addValue("query", $queryEntry);
      }
      else {
        $resultEntry->addValue("found", true);
        $resultEntry->addValue("query", $queryEntry);
        $matches = array();
        for ($i = 0; $i < sizeof($joined[Factory::getHashFactory()->getModelName()]); $i++) {
          /** @var $hash Hash */
          $hash = $joined[Factory::getHashFactory()->getModelName()][$i];
          $matches[] = $hash;
          if ($hashlists->getVal($hash->getHashlistId()) == false) {
            $hashlists->addValue($hash->getHashlistId(), $joined[Factory::getHashlistFactory()->getModelName()][$i]);
          }
        }
        $resultEntry->addValue("matches", $matches);
      }
      $resultEntries[] = $resultEntry;
    }
    UI::add('resultEntries', $resultEntries);
    UI::add('hashlists', $hashlists);
    UI::add('result', true);
    UI::addMessage(UI::SUCCESS, "Searched for " . sizeof($resultEntries) . " entries!");
  }
}