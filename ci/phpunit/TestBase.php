<?php


namespace Hashtopolis;

use Exception;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\models\UserFactory;
use Hashtopolis\inc\utils\UserUtils;
use PHPUnit\Framework\TestCase;
use Override;

require_once(dirname(__FILE__) . '/TestMocks.php');
require_once(dirname(__FILE__) . '/../../src/inc/startup/include.php');


class TestBase extends TestCase {
  private array  $databaseObjects;
  protected User $adminUser;
  
  #[Override]
  protected function setUp(): void {
    parent::setUp();
    
    $this->databaseObjects = [];
    $this->adminUser = new User(1, 'admin', 'admin@example.com', 'hash', 'salt', 1, 0, 0, time(), 3600, 1, '', '', '', '', '');
    
    // Avoid test warnings
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? 80;

    \hashtopolis_clear_test_mocks();
  }
  
  #[Override]
  protected function tearDown(): void {
    \hashtopolis_clear_test_mocks();
    
    $numObjects = sizeof($this->databaseObjects);
    for ($i = $numObjects - 1; $i >= 0; $i--) {
      $factory = $this->databaseObjects[$i]["factory"];
      $object = $this->databaseObjects[$i]["object"];
      // we cover special complex objects here and just use utils functions for these to avoid too complex dependency problems on deletion
      if ($factory instanceof UserFactory) {
        UserUtils::deleteUser($object->getId(), $this->adminUser);
      }
      else {
        $factory->delete($object);
      }
    }
    
    parent::tearDown();
  }
  
  /**
   * used to create an object in the database and then register it directly for deletion to be cleaned up after the test
   *
   * @param AbstractModelFactory $factory
   * @param AbstractModel $obj
   * @return AbstractModel
   * @throws Exception
   */
  public function createDatabaseObject(AbstractModelFactory $factory, AbstractModel $obj): AbstractModel {
    $obj = $factory->save($obj);
    $this->registerDatabaseObject($factory, $obj);
    return $obj;
  }
  
  /**
   * used to just register an already existing database object during the tests to be deleted at the end
   *
   * @param AbstractModelFactory $factory
   * @param AbstractModel $obj
   * @return void
   */
  public function registerDatabaseObject(AbstractModelFactory $factory, AbstractModel $obj): void {
    $this->databaseObjects[] = ["factory" => $factory, "object" => $obj];
  }
  
  /**
   * used to just register already existing database objects during the tests to be deleted at the end
   *
   * @param AbstractModelFactory $factory
   * @param array $objects
   * @return void
   */
  public function registerDatabaseObjects(AbstractModelFactory $factory, array $objects): void {
    foreach ($objects as $object) {
      $this->databaseObjects[] = ["factory" => $factory, "object" => $object];
    }
  }
}
