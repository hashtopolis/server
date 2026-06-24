<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\JwtApiKey;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\TestBase;
use Override;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class JwtTokenUtilsTest extends TestBase {
  private User $user;
  
  #[Override]
  protected function setUp(): void {
    parent::setUp();
    $this->user = $this->createUser('jwt_user');
  }
  
  public function testCreateKeyCreatesValidKey(): void {
    $start = time();
    $end = $start + 3600;
    
    $key = JwtTokenUtils::createKey($this->user->getId(), $start, $end);
    
    $this->assertInstanceOf(JwtApiKey::class, $key);
    $this->assertSame($start, $key->getStartValid());
    $this->assertSame($end, $key->getEndValid());
    $this->assertSame($this->user->getId(), $key->getUserId());
    $this->assertNotNull($key->getId());
    $this->registerDatabaseObject(Factory::getJwtApiKeyFactory(), $key);
  }
  
  public function testCreateKeyThrowsForInvalidUser(): void {
    $this->expectException(HttpError::class);
    JwtTokenUtils::createKey(-1, time(), time() + 3600);
  }
  
  public function testDeleteKeyDeletesExpiredKey(): void {
    $start = time() - 7200;
    $end = time() - 3600;
    $key = $this->createJwtApiKey($this->user, $start, $end);
    
    JwtTokenUtils::deleteKey($key);
    
    $this->assertNull(Factory::getJwtApiKeyFactory()->get($key->getId()));
  }
  
  public function testDeleteKeyThrowsForUnexpiredKey(): void {
    $key = $this->createJwtApiKey($this->user);
    
    $this->expectException(HttpForbidden::class);
    JwtTokenUtils::deleteKey($key);
  }
}
