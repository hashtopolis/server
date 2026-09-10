<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\AgentBinary;
use Hashtopolis\dba\models\AgentBinaryFactory;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\TestBase;
use Override;

final class AgentBinaryUtilsTest extends TestBase {

  private User $user;

  #[Override]
  protected function setUp(): void {
    parent::setUp();
    $this->user = $this->createUser("valid_user");
  }

  function testNewBinaryEmptyVersionThrows(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage('Version cannot be empty!');

    AgentBinaryUtils::newBinary("php", "Hurd", "hashtopolis.tar.gz", "", "unstable", $this->user);
  }

  function testMissingBinaryFileThrows(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("Provided filename does not exist!");

    AgentBinaryUtils::newBinary("php", "Hurd", "hashtopolis.tar.gz", "1", "unstable", $this->user);
  }

  function testBinaryTypeAlreadyExistsThrows(): void {
    $binaryTypeName = "aBinaryType";
    $this->createAgentBinary($binaryTypeName, "0", "hurd", "/opt/hashtopolis.tar.gz", "", "false");
    
    \hashtopolis_set_test_mock('Hashtopolis\\inc\\utils\\file_exists', static function ($path): bool {
      return true;
    });

    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("You cannot have two binaries with the same type!");

    AgentBinaryUtils::newBinary($binaryTypeName, "Hurd", "hashtopolis.tar.gz", "1", "unstable", $this->user);
  }

  function testValidNewBinaryTypeIsSaved(): void {
    \hashtopolis_set_test_mock('Hashtopolis\\inc\\utils\\file_exists', static function ($path): bool {
      return true;
    });

    $agentBinary = AgentBinaryUtils::newBinary("aBinaryType", "Hurd", "hashtopolis.tar.gz", "1", "unstable", $this->user);
    $this->registerDatabaseObject(Factory::getAgentBinaryFactory(), $agentBinary);
    $this->assertNotEmpty($agentBinary->getId(), "AgentBinary id should be set after newBinary");
    
    $qF = new QueryFilter(AgentBinary::AGENT_BINARY_ID, $agentBinary->getId(), "=");
    $result = Factory::getAgentBinaryFactory()->filter([Factory::FILTER => $qF], true);
    
    $this->assertEquals($agentBinary->getBinaryType(), $result->getBinaryType());
    $this->assertEquals($agentBinary->getFilename(), $result->getFilename());
    $this->assertEquals($agentBinary->getOperatingSystems(), $result->getOperatingSystems());
  }

}