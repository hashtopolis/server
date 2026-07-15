<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\dba\Factory;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../../TestBase.php');

final class AgentUtilsTest extends TestBase {
  
  protected function setUp(): void {
    parent::setUp();
  }
  
  /**
   * Test cracking time aggregation for an agent on a task.
   *
   * @return void
   * @throws Exception
   */
  public function testCrackingTimeAggregation(): void {
    $task = $this->createTaskHelper()["task"];
    $agent1 = $this->createAgent("test");
    $agent2 = $this->createAgent("test");
    $timeSpans = (array) [
      [1000, 2000, $agent1],
      [3000, 5000, $agent2],
      [3000, 8000, $agent1],
      [8000, 10000, $agent1],
      [15000, 20000, $agent1],
    ];
    
    foreach ($timeSpans as [$start, $end, $agent]) {
      $chunk = $this->createChunk($task, $agent, 4);
      $chunk->setDispatchTime($start);
      $chunk->setSolveTime($end);
      Factory::getChunkFactory()->update($chunk);
    }
    
    // Calculate reference value via an interval merge algorithm
    $totalEnd = $referenceSum = 0;
    usort($timeSpans, fn($a, $b) => $a[0] <=> $b[0]); // Make sure time spans are sorted
    foreach ($timeSpans as [$currentStart, $currentEnd, $agent]) { // Expects list to be sorted by time
      if ($agent == $agent1) {
        $referenceSum = $referenceSum + ($currentEnd - $currentStart) // Add current time span to running total
          - max(0, $totalEnd - $currentStart) // Correct for potential overlapping when current start before previous end
          + max(0, $totalEnd - $currentEnd); // Correct for potential overcorrection when current end before previous end
        $totalEnd = max($totalEnd, $currentEnd); // Extend window if current end exceeds previous end
      }
    }
    
    // Calculate aggregate cracking time via AgentUtils
    $crackingTime = AgentUtils::getAggregateCrackingTime($agent1->getId(), $task->getId());
    
    $this->assertEquals($referenceSum, $crackingTime);
  }
}
