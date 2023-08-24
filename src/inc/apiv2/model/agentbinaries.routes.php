<?php
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;

use DBA\AgentBinary;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class AgentBinaryAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/agentbinaries";
    }

    public static function getDBAclass(): string {
      return AgentBinary::class;
    }    
  
    protected function createObject(array $data): int {
      AgentBinaryUtils::newBinary(
        $data[AgentBinary::TYPE],
        $data[AgentBinary::OPERATING_SYSTEMS],
        $data[AgentBinary::FILENAME],
        $data[AgentBinary::VERSION],
        $data[AgentBinary::UPDATE_TRACK],
        $this->getCurrentUser()
      );

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(AgentBinary::FILENAME, $data[AgentBinary::FILENAME], '='),
        new QueryFilter(AgentBinary::VERSION, $data[AgentBinary::VERSION], '='),
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