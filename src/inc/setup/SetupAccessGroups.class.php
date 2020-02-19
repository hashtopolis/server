<?php

use DBA\ContainFilter;
use DBA\Factory;
use DBA\RightGroup;

class SetupAccessGroups extends HashtopolisSetup {
  protected static $identifier  = "setupAccessGroups";
  protected static $type        = DSetupType::INSTALLATION;
  protected static $description = "Sets up some useful access group types beside the default 'Administrators' to be used for various access control.";
  
  private static $levels = [
    "Superuser" => ["viewHashlistAccess" => true, "manageHashlistAccess" => true, "createHashlistAccess" => true, "createSuperhashlistAccess" => true, "viewHashesAccess" => true, "viewAgentsAccess" => true, "manageAgentAccess" => true, "createAgentAccess" => true, "viewTaskAccess" => true, "runTaskAccess" => true, "createTaskAccess" => true, "manageTaskAccess" => true, "viewPretaskAccess" => true, "createPretaskAccess" => true, "managePretaskAccess" => true, "viewSupertaskAccess" => true, "createSupertaskAccess" => true, "manageSupertaskAccess" => true, "viewFileAccess" => true, "manageFileAccess" => true, "addFileAccess" => true, "crackerBinaryAccess" => true, "serverConfigAccess" => true, "userConfigAccess" => false],
    "User" => ["viewHashlistAccess" => true, "manageHashlistAccess" => true, "createHashlistAccess" => true, "createSuperhashlistAccess" => true, "viewHashesAccess" => true, "viewAgentsAccess" => true, "manageAgentAccess" => true, "createAgentAccess" => false, "viewTaskAccess" => true, "runTaskAccess" => true, "createTaskAccess" => true, "manageTaskAccess" => true, "viewPretaskAccess" => true, "createPretaskAccess" => true, "managePretaskAccess" => true, "viewSupertaskAccess" => true, "createSupertaskAccess" => true, "manageSupertaskAccess" => true, "viewFileAccess" => true, "manageFileAccess" => true, "addFileAccess" => true, "crackerBinaryAccess" => false, "serverConfigAccess" => false, "userConfigAccess" => false],
    "Viewer" => ["viewHashlistAccess" => true, "manageHashlistAccess" => false, "createHashlistAccess" => false, "createSuperhashlistAccess" => false, "viewHashesAccess" => true, "viewAgentsAccess" => true, "manageAgentAccess" => false, "createAgentAccess" => false, "viewTaskAccess" => true, "runTaskAccess" => false, "createTaskAccess" => false, "manageTaskAccess" => false, "viewPretaskAccess" => true, "createPretaskAccess" => false, "managePretaskAccess" => false, "viewSupertaskAccess" => true, "createSupertaskAccess" => false, "manageSupertaskAccess" => false, "viewFileAccess" => true, "manageFileAccess" => false, "addFileAccess" => false, "crackerBinaryAccess" => false, "serverConfigAccess" => false, "userConfigAccess" => false]
  ];
  
  /**
   * @inheritDoc
   */
  public function execute($options) {
    if (!$this->isApplicable()) {
      return false;
    }
    $groups = [];
    foreach (self::$levels as $name => $permissions) {
      $groups[] = new RightGroup(null, $name, json_encode($permissions));
    }
    Factory::getRightGroupFactory()->massSave($groups);
    return true;
  }
  
  /**
   * @inheritDoc
   */
  public function isApplicable() {
    if ($this->isApplicableTested()) {
      return $this->getApplicableTestCache();
    }
    $qF = new ContainFilter(RightGroup::GROUP_NAME, array_keys(self::$levels));
    $check = Factory::getRightGroupFactory()->countFilter([Factory::FILTER => $qF]);
    if ($check > 0) {
      $this->setApplicableResult(false);
      return false;
    }
    $this->setApplicableResult(true);
    return true;
  }
}
