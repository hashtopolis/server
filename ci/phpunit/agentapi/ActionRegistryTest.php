<?php

namespace Hashtopolis\agentapi;

use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agentapi\common\ActionRegistry;
use Hashtopolis\inc\agentapi\model\CheckClientVersionAction;
use Hashtopolis\inc\agentapi\model\ClientErrorAction;
use Hashtopolis\inc\agentapi\model\DeregisterAction;
use Hashtopolis\inc\agentapi\model\DownloadBinaryAction;
use Hashtopolis\inc\agentapi\model\GetFileAction;
use Hashtopolis\inc\agentapi\model\GetFileStatusAction;
use Hashtopolis\inc\agentapi\model\GetFoundAction;
use Hashtopolis\inc\agentapi\model\GetHashlistAction;
use Hashtopolis\inc\agentapi\model\GetHealthCheckAction;
use Hashtopolis\inc\agentapi\model\LoginAction;
use Hashtopolis\inc\agentapi\model\RegisterAgentAction;
use Hashtopolis\inc\agentapi\model\SendHealthCheckAction;
use Hashtopolis\inc\agentapi\model\TestConnectionAction;
use Hashtopolis\inc\agentapi\model\UpdateInformationAction;
use Hashtopolis\inc\api\APIGetChunk;
use Hashtopolis\inc\api\APIGetTask;
use Hashtopolis\inc\api\APISendBenchmark;
use Hashtopolis\inc\api\APISendKeyspace;
use Hashtopolis\inc\api\APISendProgress;
use PHPUnit\Framework\TestCase;

require_once(dirname(__FILE__) . '/../TestBase.php');

/**
 * Unit tests for {@see ActionRegistry} — verifies that all 19 agent API action
 * strings are correctly mapped to their handler classes and that unknown /
 * missing actions return null.
 */
final class ActionRegistryTest extends TestCase {

  /**
   * Every action string in PActions must be registered in the ActionRegistry.
   */
  public function testAllPActionsAreRegistered(): void {
    $registered = ActionRegistry::getActions();
    $expected = [
      PActions::TEST_CONNECTION,
      PActions::REGISTER,
      PActions::UPDATE_CLIENT_INFORMATION,
      PActions::LOGIN,
      PActions::CHECK_CLIENT_VERSION,
      PActions::DOWNLOAD_BINARY,
      PActions::CLIENT_ERROR,
      PActions::GET_FILE,
      PActions::GET_HASHLIST,
      PActions::GET_TASK,
      PActions::GET_CHUNK,
      PActions::SEND_KEYSPACE,
      PActions::SEND_BENCHMARK,
      PActions::SEND_PROGRESS,
      PActions::GET_FILE_STATUS,
      PActions::GET_HEALTH_CHECK,
      PActions::SEND_HEALTH_CHECK,
      PActions::GET_FOUND,
      PActions::DEREGISTER,
    ];
    foreach ($expected as $action) {
      $this->assertContains($action, $registered, "Action '$action' should be registered");
    }
    $this->assertCount(19, $registered, 'All 19 actions should be registered');
  }

  /**
   * Each action string maps to the correct handler class.
   */
  public function testGetHandlerReturnsCorrectClass(): void {
    $this->assertEquals(TestConnectionAction::class, ActionRegistry::getHandler(PActions::TEST_CONNECTION));
    $this->assertEquals(RegisterAgentAction::class, ActionRegistry::getHandler(PActions::REGISTER));
    $this->assertEquals(UpdateInformationAction::class, ActionRegistry::getHandler(PActions::UPDATE_CLIENT_INFORMATION));
    $this->assertEquals(LoginAction::class, ActionRegistry::getHandler(PActions::LOGIN));
    $this->assertEquals(CheckClientVersionAction::class, ActionRegistry::getHandler(PActions::CHECK_CLIENT_VERSION));
    $this->assertEquals(DownloadBinaryAction::class, ActionRegistry::getHandler(PActions::DOWNLOAD_BINARY));
    $this->assertEquals(ClientErrorAction::class, ActionRegistry::getHandler(PActions::CLIENT_ERROR));
    $this->assertEquals(GetFileAction::class, ActionRegistry::getHandler(PActions::GET_FILE));
    $this->assertEquals(GetHashlistAction::class, ActionRegistry::getHandler(PActions::GET_HASHLIST));
    $this->assertEquals(APIGetTask::class, ActionRegistry::getHandler(PActions::GET_TASK));
    $this->assertEquals(APIGetChunk::class, ActionRegistry::getHandler(PActions::GET_CHUNK));
    $this->assertEquals(APISendKeyspace::class, ActionRegistry::getHandler(PActions::SEND_KEYSPACE));
    $this->assertEquals(APISendBenchmark::class, ActionRegistry::getHandler(PActions::SEND_BENCHMARK));
    $this->assertEquals(APISendProgress::class, ActionRegistry::getHandler(PActions::SEND_PROGRESS));
    $this->assertEquals(GetFileStatusAction::class, ActionRegistry::getHandler(PActions::GET_FILE_STATUS));
    $this->assertEquals(GetHealthCheckAction::class, ActionRegistry::getHandler(PActions::GET_HEALTH_CHECK));
    $this->assertEquals(SendHealthCheckAction::class, ActionRegistry::getHandler(PActions::SEND_HEALTH_CHECK));
    $this->assertEquals(GetFoundAction::class, ActionRegistry::getHandler(PActions::GET_FOUND));
    $this->assertEquals(DeregisterAction::class, ActionRegistry::getHandler(PActions::DEREGISTER));
  }

  /**
   * An unknown action string returns null.
   */
  public function testGetHandlerReturnsNullForUnknownAction(): void {
    $this->assertNull(ActionRegistry::getHandler('nonexistent'));
    $this->assertNull(ActionRegistry::getHandler(''));
    $this->assertNull(ActionRegistry::getHandler('INV'));
  }

  /**
   * A null action returns null.
   */
  public function testGetHandlerReturnsNullForNullAction(): void {
    $this->assertNull(ActionRegistry::getHandler(null));
  }

  /**
   * getActions returns a list of all registered action strings.
   */
  public function testGetActionsReturnsList(): void {
    $actions = ActionRegistry::getActions();
    $this->assertCount(19, $actions);
  }
}
