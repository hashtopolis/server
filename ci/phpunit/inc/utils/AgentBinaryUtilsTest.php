<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\AgentBinary;
use Hashtopolis\dba\models\AgentBinaryFactory;
use Hashtopolis\dba\models\AgentFactory;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\HTException;
use Hashtopolis\TestBase;
use Override;

use function PHPUnit\Framework\assertNotNull;

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
    $this->assertNotEmpty($agentBinary->getId(), "AgentBinary id should be set after binary save");
    
    $result = $this->getAgentBinaryById($agentBinary->getId()); 
    
    $this->assertEquals($agentBinary->getBinaryType(), $result->getBinaryType());
    $this->assertEquals($agentBinary->getFilename(), $result->getFilename());
    $this->assertEquals($agentBinary->getOperatingSystems(), $result->getOperatingSystems());
  }

  function testEditBinaryEmptyVersionThrows(): void {
    $this->expectException(HTException::class);
    $this->expectExceptionMessage("Version cannot be empty!");

    AgentBinaryUtils::editBinary(0, "hashcat", "hurd", "agent.zip", "", "superstream", $this->user);
  }

  function testEditBinaryMissingBinaryFileThrows(): void {
    $this->expectException(HTException::class);
    $this->expectExceptionMessage("Provided filename does not exist!");

    AgentBinaryUtils::editBinary(0, "hashcat", "hurd", "agent.zip", "1.0.1", "superstream", $this->user);
  }

  function testEditBinaryMissingBinary(): void {
    \hashtopolis_set_test_mock('Hashtopolis\\inc\\utils\\file_exists', static function ($path): bool {
      return true;
    });

    $this->expectException(HTException::class);
    $this->expectExceptionMessage("Binary does not exist!");

    AgentBinaryUtils::editBinary(9999, "hashcat", "hurd", "agent.zip", "1.0.1", "superstream", $this->user);
  }

  
  function testEditBinaryChecksBinaryTypeDuplicates(): void {
    $this->createAgentBinary("hashcat", "1.0.0", "hurd", "hashcat.tar.gz", "superstream", "false");
    $existingAgentBinary2 = $this->createAgentBinary("tachsah", "1.0.0", "hurd", "hashcat.tar.gz", "superstream", "false");

    $this->expectException(HTException::class);
    $this->expectExceptionMessage("You cannot have two binaries with the same type!");

    \hashtopolis_set_test_mock('Hashtopolis\\inc\\utils\\file_exists', static function ($path): bool {
      return true;
    });

    AgentBinaryUtils::editBinary($existingAgentBinary2->getId(), "hashcat", "hurd", "hashcat.tar.gz", "1.0.0", "track", $this->user);
  }

  function testEditBinaryUpdateTrackResetUpdateAvailable(): void {
    \hashtopolis_set_test_mock('Hashtopolis\\inc\\utils\\file_exists', static function ($path): bool {
      return true;
    });
    
    $agentBinary = $this->createAgentBinary("hashcat", "1.0.0", "hurd", "hashcat.tar.gz", "superstream", "false");

    AgentBinaryUtils::editBinary(
      $agentBinary->getId(),
      $agentBinary->getBinaryType(),
      $agentBinary->getOperatingSystems(),
      $agentBinary->getFilename(),
      $agentBinary->getVersion(),
      "newUpdateTrack",
      $this->user
    );

    $result = $this->getAgentBinaryById($agentBinary->getId());
    
    $this->assertNotNull($result);
    $this->assertEmpty($result->getUpdateAvailable(), "Update available should be reset when updateTrack changes");
  }

  function testEditBinaryUpdateBinary(): void {
    \hashtopolis_set_test_mock('Hashtopolis\\inc\\utils\\file_exists', static function ($path): bool {
      return true;
    });

    $agentBinary = $this->createAgentBinary("hashcat", "hurd", "hashcat.tar.gz", "1.0.0", "stable", true);

    AgentBinaryUtils::editBinary(
      $agentBinary->getId(),
      strrev($agentBinary->getBinaryType()),
      strrev($agentBinary->getOperatingSystems()),
      strrev($agentBinary->getFilename()),
      strrev($agentBinary->getVersion()),
      $agentBinary->getUpdateTrack(),
      $this->user
    );

    $updatedAgentBinary = $this->getAgentBinaryById($agentBinary->getId());
    $this->assertNotNull($updatedAgentBinary);

    $this->assertEquals(strrev($agentBinary->getBinaryType()), $updatedAgentBinary->getBinaryType());
    $this->assertEquals(strrev($agentBinary->getOperatingSystems()), $updatedAgentBinary->getOperatingSystems());
    $this->assertEquals(strrev($agentBinary->getFilename()), $updatedAgentBinary->getFilename());
    $this->assertEquals(strrev($agentBinary->getVersion()), $updatedAgentBinary->getVersion());
    $this->assertEquals($agentBinary->getUpdateTrack(), $updatedAgentBinary->getUpdateTrack());
    $this->assertTrue((bool) $updatedAgentBinary->getUpdateAvailable());
  }
  
  function testEditUpdateTrackResetsUpdateAvailableIfChanged(): void {
    $newUpdateTrack = "unstable";
    $agentBinary = $this->createAgentBinary("hashcat", "10", "hurd", "hashcat.tar.gz", "stable", "11");

    AgentBinaryUtils::editUpdateTracker($agentBinary->getId(), $newUpdateTrack, $this->user);

    $updatedAgentBinary = $this->getAgentBinaryById($agentBinary->getId());
    $this->assertNotNull($updatedAgentBinary);

    $this->assertEquals($newUpdateTrack, $updatedAgentBinary->getUpdateTrack());
    $this->assertEmpty($updatedAgentBinary->getUpdateAvailable());
  }

  function testEditUpdatTrackKeepsUpdateInformationIfUnchaged(): void {
    $agentBinary = $this->createAgentBinary("hashcat", "10", "hurd", "hashcat.tar.gz", "stable", "11");

    AgentBinaryUtils::editUpdateTracker($agentBinary->getId(), $agentBinary->getUpdateTrack(), $this->user);

    $updatedAgentBinary = $this->getAgentBinaryById($agentBinary->getId());
    $this->assertNotNull($updatedAgentBinary);

    $this->assertEquals($agentBinary->getUpdateTrack(), $updatedAgentBinary->getUpdateTrack());
  }

  function testEditFilenameInvalidFilename(): void {
    $agentBinary = $this->createAgentBinary("hashcat", "10", "hurd", "hashcat.tar.gz", "stable", "11");

    $this->expectException(HTException::class);
    $this->expectExceptionMessage("Provided filename does not exist!");

    AgentBinaryUtils::editName($agentBinary->getId(), "new-filename.tar.gz", $this->user);
  }

  function testEditFileNameValidFilename(): void {
    \hashtopolis_set_test_mock('Hashtopolis\\inc\\utils\\file_exists', static function ($path): bool {
      return true;
    });

    $newFileName = "new-agent-binary.tar.gz";
    $agentBinary = $this->createAgentBinary("hashcat", "10", "hurd", "hashcat.tar.gz", "stable", "11");

    AgentBinaryUtils::editName($agentBinary->getId(), $newFileName, $this->user);
    $updatedAgentBinary = $this->getAgentBinaryById($agentBinary->getId());
    $this->assertNotNull($updatedAgentBinary);

    $this->assertEquals($newFileName, $updatedAgentBinary->getFilename());
  }

  function testUpdateTypeToAnAlreadyExistingType(): void {
    $agentBinary1 = $this->createAgentBinary("python", "1.0.0", "hurd", "py-agent.tar.gz", "stable", "");
    $agentBinary2 = $this->createAgentBinary("unknown", "1.0.0", "hurd", "unk-agent.tar.gz", "stable", "");

    $this->expectException(HTException::class);
    $this->expectExceptionMessage("You cannot have two binaries with the same type!");

    AgentBinaryUtils::editType($agentBinary2->getId(), $agentBinary1->getBinaryType(), $this->user);
  }

  function testUpdateBinaryType(): void {
    $newBinaryType = "binaryType";
    $this->createAgentBinary("python", "1.0.0", "hurd", "py-agent.tar.gz", "stable", "");
    $agentBinary2 = $this->createAgentBinary("unknown", "1.0.0", "hurd", "unk-agent.tar.gz", "stable", "");

    AgentBinaryUtils::editType($agentBinary2->getId(), $newBinaryType, $this->user);

    $updatedAgentBinary = $this->getAgentBinaryById($agentBinary2->getId());
    $this->assertNotNull($updatedAgentBinary);

    $this->assertEquals($newBinaryType, $updatedAgentBinary->getBinaryType());
  }

  function testDeleteBinary(): void {
    $unlinkedBinaryFile = false;
    \hashtopolis_set_test_mock('Hashtopolis\\inc\\utils\\unlink', static function ($path) use (&$unlinkedBinaryFile): bool {
      $unlinkedBinaryFile = true;
      return true;
    });

    $agentBinary = $this->createAgentBinary("python", "1.0.0", "hurd", "py-agent.tar.gz", "stable", '');

    AgentBinaryUtils::deleteBinary($agentBinary->getId());

    $this->assertNull($this->getAgentBinaryById($agentBinary->getId()));
    $this->assertTrue($unlinkedBinaryFile);
  }

  private function getAgentBinaryById(int $id): ?AgentBinary {
    $qF = new QueryFilter(AgentBinary::AGENT_BINARY_ID, $id, "=");
    return Factory::getAgentBinaryFactory()->filter([Factory::FILTER => [$qF]], true);
  }

}