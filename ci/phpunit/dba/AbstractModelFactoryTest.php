<?php
namespace Tests\DBA;

use Exception;
use PHPUnit\Framework\TestCase;
use DBA\Factory;
use DBA\QueryFilter;
use DBA\User;

final class AbstractModelFactoryTest extends TestCase {
  /**
   * @throws Exception
   */
  public function testGetDBWithTest(): void {
    $db = Factory::getAgentFactory()->getDB(true);
    
    $this->assertSame(null, $db);
  }
  
  /**
   * Tests both cases to be used on a simple QueryFilter with no result.
   * When single is true, null must be returned if no matching entry was found, empty array otherwise
   *
   * @return void
   */
  public function testSimpleFilter(): void {
    $qF = new QueryFilter(User::USER_ID, 99999, "=");
    
    $user = Factory::getUserFactory()->filter([Factory::FILTER => $qF], true);
    $this->assertSame(null, $user);
    
    $user = Factory::getUserFactory()->filter([Factory::FILTER => $qF]);
    $this->assertSame([], $user);
  }
}
