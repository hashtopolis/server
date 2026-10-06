<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\UpdateSet;
use Hashtopolis\TestBase;
use Override;

require_once(dirname(__FILE__) . '/../TestBase.php');

/**
 * Tests AbstractModelFactory::compareAndSet, the primitive a state transition that must happen at
 * most once is built on.
 */
final class CompareAndSetTest extends TestBase {
  private User  $user;
  private Agent $agent;
  
  #[Override]
  protected function setUp(): void {
    parent::setUp();
    
    $this->user = $this->createUser('cas_user');
    $this->agent = $this->createAgent('cas_agent');
  }
  
  /**
   * @throws Exception
   */
  public function testTransitionAppliesWhenTheRowStillMatches(): void {
    $applied = Factory::getAgentFactory()->compareAndSet(
      $this->agent,
      [new QueryFilter(Agent::IS_ACTIVE, $this->agent->getIsActive(), "=")],
      [new UpdateSet(Agent::AGENT_NAME, 'renamed')]
    );
    
    $this->assertTrue($applied);
    $this->assertSame('renamed', Factory::getAgentFactory()->get($this->agent->getId())->getAgentName());
  }
  
  /**
   * The point of the primitive: the second caller is told it lost rather than overwriting the first.
   *
   * @throws Exception
   */
  public function testOnlyTheFirstOfTwoCallersWins(): void {
    $claim = fn(string $name): bool => Factory::getAgentFactory()->compareAndSet(
      $this->agent,
      [new QueryFilter(Agent::USER_ID, null, "=")],
      [new UpdateSet(Agent::USER_ID, $this->user->getId()), new UpdateSet(Agent::AGENT_NAME, $name)]
    );
    
    $this->assertTrue($claim('winner'));
    $this->assertFalse($claim('loser'));
    $this->assertSame('winner', Factory::getAgentFactory()->get($this->agent->getId())->getAgentName());
  }
  
  /**
   * @throws Exception
   */
  public function testTransitionIsRefusedWhenTheRowNoLongerMatches(): void {
    $applied = Factory::getAgentFactory()->compareAndSet(
      $this->agent,
      [new QueryFilter(Agent::AGENT_NAME, 'some other name', "=")],
      [new UpdateSet(Agent::AGENT_NAME, 'renamed')]
    );
    
    $this->assertFalse($applied);
    $this->assertSame($this->agent->getAgentName(), Factory::getAgentFactory()->get($this->agent->getId())->getAgentName());
  }
  
  /**
   * A null expectation has to become IS NULL: a bound parameter compared with = never matches null,
   * so spelling it as a normal comparison would make the transition permanently unreachable.
   *
   * @throws Exception
   */
  public function testNullIsExpectedAsIsNull(): void {
    $this->assertNull($this->agent->getUserId());
    
    $this->assertTrue(Factory::getAgentFactory()->compareAndSet(
      $this->agent,
      [new QueryFilter(Agent::USER_ID, null, "=")],
      [new UpdateSet(Agent::USER_ID, $this->user->getId())]
    ));
    
    // ... and once the column is set, the same expectation no longer holds
    $this->assertFalse(Factory::getAgentFactory()->compareAndSet(
      $this->agent,
      [new QueryFilter(Agent::USER_ID, null, "=")],
      [new UpdateSet(Agent::USER_ID, $this->user->getId())]
    ));
  }
  
  /**
   * Every expectation has to hold, not just one of them.
   *
   * @throws Exception
   */
  public function testAllExpectationsMustHold(): void {
    $applied = Factory::getAgentFactory()->compareAndSet(
      $this->agent,
      [new QueryFilter(Agent::USER_ID, null, "="), new QueryFilter(Agent::AGENT_NAME, 'some other name', "=")],
      [new UpdateSet(Agent::AGENT_NAME, 'renamed')]
    );
    
    $this->assertFalse($applied);
  }
  
  /**
   * The row is addressed by its primary key, so a matching expectation on another row changes nothing.
   *
   * @throws Exception
   */
  public function testOnlyTheAddressedRowIsTouched(): void {
    $other = $this->createAgent('cas_other');
    
    Factory::getAgentFactory()->compareAndSet(
      $this->agent,
      [new QueryFilter(Agent::USER_ID, null, "=")],
      [new UpdateSet(Agent::AGENT_NAME, 'renamed')]
    );
    
    $this->assertSame($other->getAgentName(), Factory::getAgentFactory()->get($other->getId())->getAgentName());
  }
  
  /**
   * @throws Exception
   */
  public function testNoExpectationsMeansAnUnconditionalUpdate(): void {
    $this->assertTrue(Factory::getAgentFactory()->compareAndSet(
      $this->agent,
      [],
      [new UpdateSet(Agent::AGENT_NAME, 'renamed')]
    ));
  }
  
  /**
   * @throws Exception
   */
  public function testUpdatingNothingIsRejected(): void {
    $this->expectException(Exception::class);
    
    Factory::getAgentFactory()->compareAndSet($this->agent, [new QueryFilter(Agent::USER_ID, null, "=")], []);
  }
  
  /**
   * Writing back exactly what was expected changes no row, and MySQL reports a changed-row count
   * while PostgreSQL reports a matched one. Rather than answer differently per database, refuse it.
   *
   * @throws Exception
   */
  public function testWritingBackTheExpectedValueIsRejected(): void {
    $this->expectException(Exception::class);
    
    Factory::getAgentFactory()->compareAndSet(
      $this->agent,
      [new QueryFilter(Agent::IS_TRUSTED, 1, "=")],
      [new UpdateSet(Agent::IS_TRUSTED, 1)]
    );
  }
  
  /**
   * Only the whole update has to be a no-op to be refused; changing one column while holding another
   * to an expected value is the normal shape of a claim.
   *
   * @throws Exception
   */
  public function testHoldingOneColumnWhileChangingAnotherIsAllowed(): void {
    $this->assertTrue(Factory::getAgentFactory()->compareAndSet(
      $this->agent,
      [new QueryFilter(Agent::IS_TRUSTED, 1, "="), new QueryFilter(Agent::USER_ID, null, "=")],
      [new UpdateSet(Agent::IS_TRUSTED, 1), new UpdateSet(Agent::USER_ID, $this->user->getId())]
    ));
  }
  
  /**
   * Conditions describe one known state, so anything but equality is refused: a range would no longer
   * identify the state being transitioned away from, and with it the at-most-one-winner guarantee.
   *
   * @throws Exception
   */
  public function testALooseConditionIsRejected(): void {
    $this->expectException(Exception::class);

    Factory::getAgentFactory()->compareAndSet(
      $this->agent,
      [new QueryFilter(Agent::AGENT_ID, 0, ">")],
      [new UpdateSet(Agent::AGENT_NAME, 'renamed')]
    );
  }

  /**
   * Booleans are stored as tinyint on MySQL and as a real boolean on PostgreSQL, so a flag flip has
   * to survive both dialects.
   *
   * @throws Exception
   */
  public function testBooleanColumnTransitions(): void {
    $this->assertEquals(1, $this->agent->getIsTrusted());
    
    $this->assertTrue(Factory::getAgentFactory()->compareAndSet(
      $this->agent,
      [new QueryFilter(Agent::IS_TRUSTED, 1, "=")],
      [new UpdateSet(Agent::IS_TRUSTED, 0)]
    ));
    $this->assertEquals(0, Factory::getAgentFactory()->get($this->agent->getId())->getIsTrusted());
    
    // The flag has already been flipped, so the same transition must not apply a second time
    $this->assertFalse(Factory::getAgentFactory()->compareAndSet(
      $this->agent,
      [new QueryFilter(Agent::IS_TRUSTED, 1, "=")],
      [new UpdateSet(Agent::IS_TRUSTED, 0)]
    ));
  }
}
