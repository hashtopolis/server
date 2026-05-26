<?php

namespace Hashtopolis;

use Exception;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\AccessGroupUser;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\dba\models\File;
use Hashtopolis\dba\models\FileTask;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\RightGroup;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\models\UserFactory;
use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\inc\defines\DTaskTypes;
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
  
  protected function createChunk(Task $task, Agent $agent, int $state): Chunk {
    $chunk = $this->createDatabaseObject(
      Factory::getChunkFactory(),
      new Chunk(null, $task->getId(), 0, 100, $agent->getId(), time(), 0, 0, 0, $state, 0, 0)
    );
    $this->assertTrue($chunk instanceof Chunk);
    return $chunk;
  }
  
  protected function createAccessGroup(string $prefix): AccessGroup {
    $group = $this->createDatabaseObject(
      Factory::getAccessGroupFactory(),
      new AccessGroup(null, $prefix . '_' . uniqid())
    );
    $this->assertTrue($group instanceof AccessGroup);
    return $group;
  }
  
  protected function createAccessGroupUser(User $user, AccessGroup $accessGroup): AccessGroupUser {
    $relation = $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $accessGroup->getId(), $user->getId())
    );
    $this->assertTrue($relation instanceof AccessGroupUser);
    return $relation;
  }
  
  protected function createRightGroup(): RightGroup {
    $group = $this->createDatabaseObject(
      Factory::getRightGroupFactory(),
      new RightGroup(null, 'phpunit-' . uniqid('', true), '[]')
    );
    $this->assertTrue($group instanceof RightGroup);
    return $group;
  }
  
  protected function createUser(string $prefix): User {
    $username = $prefix . '_' . uniqid();
    $user = UserUtils::createUser($username, $username . '@example.com', $this->createRightGroup()->getId(), $this->adminUser);
    $this->registerDatabaseObject(Factory::getUserFactory(), $user);
    return $user;
  }
  
  protected function createHashType(): HashType {
    $hashType = $this->createDatabaseObject(
      Factory::getHashTypeFactory(),
      new HashType(null, 'hash_type_' . uniqid(), 0, 0)
    );
    $this->assertTrue($hashType instanceof HashType);
    return $hashType;
  }
  
  protected function createHashlist(AccessGroup $group, HashType $hashType, int $isSecret = 0): Hashlist {
    $hashlist = $this->createDatabaseObject(
      Factory::getHashlistFactory(),
      new Hashlist(null, 'hashlist_' . uniqid(), DHashlistFormat::PLAIN, $hashType->getId(), 1, ':', 0, $isSecret, 0, 0, $group->getId(), '', 0, 0, 0)
    );
    $this->assertTrue($hashlist instanceof Hashlist);
    return $hashlist;
  }
  
  protected function createTaskWrapper(AccessGroup $group, Hashlist $hashlist, int $taskType = DTaskTypes::NORMAL): TaskWrapper {
    $taskWrapper = $this->createDatabaseObject(
      Factory::getTaskWrapperFactory(),
      new TaskWrapper(null, 1, 1, $taskType, $hashlist->getId(), $group->getId(), 'wrapper_' . uniqid(), 0, 0)
    );
    $this->assertTrue($taskWrapper instanceof TaskWrapper);
    return $taskWrapper;
  }
  
  protected function createCrackerBinaryType(): CrackerBinaryType {
    $crackerBinaryType = $this->createDatabaseObject(
      Factory::getCrackerBinaryTypeFactory(),
      new CrackerBinaryType(null, 'type_' . uniqid(), 1)
    );
    $this->assertTrue($crackerBinaryType instanceof CrackerBinaryType);
    return $crackerBinaryType;
  }
  
  protected function createCrackerBinary(CrackerBinaryType $crackerBinaryType): CrackerBinary {
    $crackerBinary = $this->createDatabaseObject(
      Factory::getCrackerBinaryFactory(),
      new CrackerBinary(null, $crackerBinaryType->getId(), '1.0.' . uniqid(), 'https://example.invalid/' . uniqid(), 'binary_' . uniqid())
    );
    $this->assertTrue($crackerBinary instanceof CrackerBinary);
    return $crackerBinary;
  }
  
  protected function createTask(TaskWrapper $taskWrapper, CrackerBinary $crackerBinary, CrackerBinaryType $crackerBinaryType): Task {
    $task = $this->createDatabaseObject(
      Factory::getTaskFactory(),
      new Task(null, 'task_' . uniqid(), '--attack-mode 0', 60, 30, 0, 0, 1, 1, '#ffffff', 0, 0, 0, 0, $crackerBinary->getId(), $crackerBinaryType->getId(), $taskWrapper->getId(), 0, '', 0, 0, 0, 0, '')
    );
    $this->assertTrue($task instanceof Task);
    return $task;
  }
  
  protected function createFile(AccessGroup $group, int $isSecret = 0): File {
    $file = $this->createDatabaseObject(
      Factory::getFileFactory(),
      new File(null, 'file_' . uniqid(), 0, $isSecret, 0, $group->getId(), 0)
    );
    $this->assertTrue($file instanceof File);
    return $file;
  }
  
  protected function createFileTask(File $file, Task $task): FileTask {
    $fileTask = $this->createDatabaseObject(
      Factory::getFileTaskFactory(),
      new FileTask(null, $file->getId(), $task->getId())
    );
    $this->assertTrue($fileTask instanceof FileTask);
    return $fileTask;
  }
  
  protected function createAgent(string $prefix, int $isTrusted = 1): Agent {
    $suffix = uniqid();
    $agent = $this->createDatabaseObject(
      Factory::getAgentFactory(),
      new Agent(null, $prefix . '_' . $suffix, 'uid_' . $suffix, 0, '[]', '', 0, 1, $isTrusted, 'token_' . $suffix, 'idle', time(), '127.0.0.1', null, 0, 'sig_' . $suffix)
    );
    $this->assertTrue($agent instanceof Agent);
    return $agent;
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
