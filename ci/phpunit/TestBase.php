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
use Hashtopolis\dba\models\FileDownload;
use Hashtopolis\dba\models\FileTask;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\HealthCheck;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\dba\models\JwtApiKey;
use Hashtopolis\dba\models\RightGroup;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\models\UserFactory;
use Hashtopolis\inc\apiv2\error\HttpConflict;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\InternalError;
use Hashtopolis\inc\defines\DHealthCheckAgentStatus;
use Hashtopolis\inc\defines\DHealthCheckMode;
use Hashtopolis\inc\defines\DHealthCheckStatus;
use Hashtopolis\inc\defines\DHealthCheckType;
use Hashtopolis\inc\defines\DFileDownloadStatus;
use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\inc\defines\DTaskTypes;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\StartupConfig;
use Hashtopolis\inc\utils\UserUtils;
use PHPUnit\Framework\TestCase;
use Override;

require_once(dirname(__FILE__) . '/TestMocks.php');
require_once(dirname(__FILE__) . '/../../src/inc/startup/include.php');


class TestBase extends TestCase {
  private array  $databaseObjects;
  private string $savedDbType;
  protected User $adminUser;
  
  #[Override]
  protected function setUp(): void {
    parent::setUp();
    
    $this->databaseObjects = [];
    $this->savedDbType = (string)getenv('HASHTOPOLIS_DB_TYPE');
    $this->adminUser = new User(1, 'admin', 'admin@example.com', 'hash', 'salt', 1, 0, 0, time(), 3600, 1, '', '', '', '', '');
    
    // Avoid test warnings
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? 80;
    
    \hashtopolis_clear_test_mocks();
  }
  
  /**
   * @throws HTException
   */
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
    
    // Restore the DB type environment variable so putenv() in one test
    // does not leak into the next test (affects LikeFilter etc.)
    if ($this->savedDbType !== '') {
      putenv('HASHTOPOLIS_DB_TYPE=' . $this->savedDbType);
    }
    else {
      putenv('HASHTOPOLIS_DB_TYPE');
    }
    StartupConfig::getInstance(true);
    
    parent::tearDown();
  }
  
  /**
   * @throws Exception
   */
  protected function createChunk(Task $task, Agent $agent, int $state): Chunk {
    $chunk = $this->createDatabaseObject(
      Factory::getChunkFactory(),
      new Chunk(null, $task->getId(), 0, 100, $agent->getId(), time(), 0, 0, 0, $state, 0, 0)
    );
    $this->assertTrue($chunk instanceof Chunk);
    return $chunk;
  }
  
  /**
   * @throws Exception
   */
  protected function createAccessGroup(string $prefix): AccessGroup {
    $group = $this->createDatabaseObject(
      Factory::getAccessGroupFactory(),
      new AccessGroup(null, $prefix . '_' . uniqid())
    );
    $this->assertTrue($group instanceof AccessGroup);
    return $group;
  }
  
  /**
   * @throws Exception
   */
  protected function createAccessGroupUser(User $user, AccessGroup $accessGroup): AccessGroupUser {
    $relation = $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $accessGroup->getId(), $user->getId())
    );
    $this->assertTrue($relation instanceof AccessGroupUser);
    return $relation;
  }
  
  /**
   * @throws Exception
   */
  protected function createRightGroup(): RightGroup {
    $group = $this->createDatabaseObject(
      Factory::getRightGroupFactory(),
      new RightGroup(null, 'phpunit-' . uniqid('', true), '[]')
    );
    $this->assertTrue($group instanceof RightGroup);
    return $group;
  }
  
  /**
   * @throws InternalError
   * @throws HTException
   * @throws HttpError
   * @throws HttpConflict
   */
  protected function createUser(string $prefix): User {
    $username = $prefix . '_' . uniqid();
    $user = UserUtils::createUser($username, $username . '@example.com', $this->createRightGroup()->getId(), $this->adminUser);
    $this->registerDatabaseObject(Factory::getUserFactory(), $user);
    return $user;
  }
  
  /**
   * @throws Exception
   */
  protected function createHashType(): HashType {
    $hashType = $this->createDatabaseObject(
      Factory::getHashTypeFactory(),
      new HashType(null, 'hash_type_' . uniqid(), 0, 0)
    );
    $this->assertTrue($hashType instanceof HashType);
    return $hashType;
  }
  
  /**
   * @throws Exception
   */
  protected function createHashlist(AccessGroup $group, HashType $hashType, int $isSecret = 0): Hashlist {
    $hashlist = $this->createDatabaseObject(
      Factory::getHashlistFactory(),
      new Hashlist(null, 'hashlist_' . uniqid(), DHashlistFormat::PLAIN, $hashType->getId(), 1, ':', 0, $isSecret, 0, 0, $group->getId(), '', 0, 0, 0)
    );
    $this->assertTrue($hashlist instanceof Hashlist);
    return $hashlist;
  }
  
  /**
   * @throws Exception
   */
  protected function createTaskWrapper(AccessGroup $group, Hashlist $hashlist, int $taskType = DTaskTypes::NORMAL): TaskWrapper {
    $taskWrapper = $this->createDatabaseObject(
      Factory::getTaskWrapperFactory(),
      new TaskWrapper(null, 1, 1, $taskType, $hashlist->getId(), $group->getId(), 'wrapper_' . uniqid(), 0, 0)
    );
    $this->assertTrue($taskWrapper instanceof TaskWrapper);
    return $taskWrapper;
  }
  
  /**
   * @throws Exception
   */
  protected function createCrackerBinaryType(): CrackerBinaryType {
    $crackerBinaryType = $this->createDatabaseObject(
      Factory::getCrackerBinaryTypeFactory(),
      new CrackerBinaryType(null, 'type_' . uniqid(), 1)
    );
    $this->assertTrue($crackerBinaryType instanceof CrackerBinaryType);
    return $crackerBinaryType;
  }
  
  /**
   * @throws Exception
   */
  protected function createCrackerBinary(CrackerBinaryType $crackerBinaryType): CrackerBinary {
    $crackerBinary = $this->createDatabaseObject(
      Factory::getCrackerBinaryFactory(),
      new CrackerBinary(null, $crackerBinaryType->getId(), '1.0.' . uniqid(), 'https://example.invalid/' . uniqid(), 'binary_' . uniqid())
    );
    $this->assertTrue($crackerBinary instanceof CrackerBinary);
    return $crackerBinary;
  }
  
  /**
   * @throws Exception
   */
  protected function createTask(TaskWrapper $taskWrapper, CrackerBinary $crackerBinary, CrackerBinaryType $crackerBinaryType, ?int $usePreprocessor = null, string $preprocessorCommand = ''): Task {
    $task = $this->createDatabaseObject(
      Factory::getTaskFactory(),
      new Task(null, 'task_' . uniqid(), '--attack-mode 0', 60, 30, 0, 0, 1, 1, '#ffffff', 0, 0, 0, 0, $crackerBinary->getId(), $crackerBinaryType->getId(), $taskWrapper->getId(), 0, '', 0, 0, 0, $usePreprocessor ?? 0, $preprocessorCommand)
    );
    $this->assertTrue($task instanceof Task);
    return $task;
  }
  
  /**
   * @throws Exception
   */
  protected function createJwtApiKey(User $user, ?int $startValid = null, ?int $endValid = null, int $isRevoked = 0): JwtApiKey {
    $key = $this->createDatabaseObject(
      Factory::getJwtApiKeyFactory(),
      new JwtApiKey(null, $startValid ?? time(), $endValid ?? time() + 3600, $user->getId(), $isRevoked)
    );
    $this->assertTrue($key instanceof JwtApiKey);
    return $key;
  }
  
  /**
   * @throws Exception
   */
  protected function createHealthCheck(CrackerBinary $crackerBinary, int $status = DHealthCheckStatus::PENDING, int $checkType = DHealthCheckType::BRUTE_FORCE, int $hashtypeId = DHealthCheckMode::MD5, int $expectedCracks = 0, string $attackCmd = ''): HealthCheck {
    $check = $this->createDatabaseObject(
      Factory::getHealthCheckFactory(),
      new HealthCheck(null, time(), $status, $checkType, $hashtypeId, $crackerBinary->getId(), $expectedCracks, $attackCmd)
    );
    $this->assertTrue($check instanceof HealthCheck);
    return $check;
  }
  
  /**
   * @throws Exception
   */
  protected function createHealthCheckAgent(HealthCheck $healthCheck, Agent $agent, int $status = DHealthCheckAgentStatus::PENDING, int $cracked = 0, int $numGpus = 0, int $start = 0, int $end = 0, string $errors = ''): HealthCheckAgent {
    $agentCheck = $this->createDatabaseObject(
      Factory::getHealthCheckAgentFactory(),
      new HealthCheckAgent(null, $healthCheck->getId(), $agent->getId(), $status, $cracked, $numGpus, $start, $end, $errors)
    );
    $this->assertTrue($agentCheck instanceof HealthCheckAgent);
    return $agentCheck;
  }
  
  /**
   * @throws Exception
   */
  protected function createFile(AccessGroup $group, int $isSecret = 0, ?string $filename = null, int $size = 0, int $fileType = 0, int $lineCount = 0): File {
    $file = $this->createDatabaseObject(
      Factory::getFileFactory(),
      new File(null, $filename ?? 'file_' . uniqid(), $size, $isSecret, $fileType, $group->getId(), $lineCount)
    );
    $this->assertTrue($file instanceof File);
    return $file;
  }
  
  /**
   * @throws Exception
   */
  protected function createFileTask(File $file, Task $task): FileTask {
    $fileTask = $this->createDatabaseObject(
      Factory::getFileTaskFactory(),
      new FileTask(null, $file->getId(), $task->getId())
    );
    $this->assertTrue($fileTask instanceof FileTask);
    return $fileTask;
  }
  
  /**
   * @throws Exception
   */
  protected function createFileDownload(int $fileId, int $status = DFileDownloadStatus::PENDING): FileDownload {
    $fileDownload = $this->createDatabaseObject(
      Factory::getFileDownloadFactory(),
      new FileDownload(null, time(), $fileId, $status)
    );
    $this->assertTrue($fileDownload instanceof FileDownload);
    return $fileDownload;
  }
  
  /**
   * @throws Exception
   */
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
   * @template TModel of AbstractModel
   * @param AbstractModelFactory $factory
   * @param TModel $obj
   * @return TModel
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
