<?php
class BenchmarkHandler implements Handler {  
  public function __construct($benchmarkId = null) {
        //we need nothing to load
  }
  

  public function handle($action) {
    try {
      switch ($action) {
        case DBenchmarkAction::DELETE_BENCHMARK:
          AccessControl::getInstance()->checkPermission(DBenchmarkAction::DELETE_BENCHMARK);
          BenchmarkUtils::delete($_POST['benchmarkId']);
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
}