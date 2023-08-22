<?php
use DBA\AgentBinary;
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class AgentBinaryAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/agentbinaries";
    }

    public static function getDBAclass(): string {
      return AgentBinary::class;
    }    

    public function getFeatures(): array {
      return AgentBinary::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getAgentBinaryFactory();
    }

    public function getExpandables(): array {
      return [];
    }

    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [];
    }
  
    protected function createObject($mappedQuery, $QUERY): int {
      AgentBinaryUtils::newBinary(
        $mappedQuery[AgentBinary::TYPE],
        $mappedQuery[AgentBinary::OPERATING_SYSTEMS],
        $mappedQuery[AgentBinary::FILENAME],
        $mappedQuery[AgentBinary::VERSION],
        $mappedQuery[AgentBinary::UPDATE_TRACK],
        $this->getUser()
      );

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(AgentBinary::FILENAME, $mappedQuery[AgentBinary::FILENAME], '='),
        new QueryFilter(AgentBinary::VERSION, $mappedQuery[AgentBinary::VERSION], '='),
      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(AgentBinary::AGENT_BINARY_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      /* No unique properties set on columns, thus multiple entries could exists, pick the latest (DESC ordering used) */
      assert(count($objects) >= 1);
      
      return $objects[0]->getId();
    }

    protected function deleteObject(object $object): void {
      AgentBinaryUtils::deleteBinary($object->getId());
    }
}

AgentBinaryAPI::register($app);