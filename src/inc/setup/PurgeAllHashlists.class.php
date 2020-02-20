<?php

use DBA\Factory;
use DBA\Hashlist;
use DBA\QueryFilter;

class PurgeAllHashlists extends HashtopolisSetup {
  /**
   * @inheritDoc
   */
  public function execute($options) {
    if (!$this->isApplicable()) {
      return false;
    }
    $qF = new QueryFilter(Hashlist::CRACKED, 0, ">");
    $hashlists = Factory::getHashlistFactory()->filter([Factory::FILTER => $qF]);
    foreach ($hashlists as $hashlist) {
      try {
        HashlistUtils::purgeHashlist($hashlist->getId(), Login::getInstance()->getUser());
      }
      catch (HTException $e) {
        // we silently ignore it, as this happens when we iterate through hashlists from other groups which this user does not see
      }
    }
    return true;
  }
  
  /**
   * @inheritDoc
   */
  public function isApplicable() {
    if ($this->isApplicableTested()) {
      return $this->getApplicableTestCache();
    }
    $qF = new QueryFilter(Hashlist::CRACKED, 0, ">");
    $check = Factory::getHashlistFactory()->countFilter([Factory::FILTER => $qF]);
    if ($check > 0) {
      $this->setApplicableResult(true);
      return true;
    }
    $this->setApplicableResult(false);
    return false;
  }
  
  function getIdentifier() {
    return "purgeAllHashlists";
  }
  
  function getSetupType() {
    return DSetupType::REMOVAL;
  }
  
  function getDescription() {
    return "Purges all founds from hashlists.";
  }
}

HashtopolisSetup::add('PurgeAllHashlists', new PurgeAllHashlists());
