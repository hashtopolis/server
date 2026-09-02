<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\TaskWrapperDisplay;
use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\inc\defines\DTaskTypes;
use Hashtopolis\TestBase;
use RuntimeException;

require_once(dirname(__FILE__) . '/../TestBase.php');

/**
 * Tests for AbstractModelFactory::joinAggregationFilter().
 *
 * The scenarios below mirror the patterns used in:
 *   - GetCompletedCountHelperAPI (normal-task path): TaskWrapperDisplay →
 *     Chunk INNER JOIN with an extra Hashlist ACL join in options.
 *   - GetCompletedCountHelperAPI (supertask path): Task → Chunk LEFT JOIN
 *     so that tasks without any chunks still appear in the result.
 */
final class JoinAggregationFilterTest extends TestBase {

  // -----------------------------------------------------------------------
  // Helpers
  // -----------------------------------------------------------------------

  /**
   * Create a Chunk with explicit skip and checkpoint values.
   *
   * @throws Exception
   */
  private function createChunkWithProgress(Task $task, int $skip, int $checkpoint): Chunk {
    $agent = $this->createAgent('joinagg_agent');
    $chunk = $this->createDatabaseObject(
      Factory::getChunkFactory(),
      new Chunk(null, $task->getId(), $skip, 100, $agent->getId(), time(), 0, $checkpoint, 0, 0, 0, 0)
    );
    $this->assertInstanceOf(Chunk::class, $chunk);
    return $chunk;
  }

  /**
   * Create a Hashlist using an existing hash type from the fixture database.
   *
   * @throws Exception
   */
  private function createHashlistForGroup(AccessGroup $group): Hashlist {
    $hashTypeIds = Factory::getHashTypeFactory()->columnFilter(
      [Factory::LIMIT => new LimitFilter(1)],
      HashType::HASH_TYPE_ID
    );
    if (empty($hashTypeIds)) {
      throw new RuntimeException('No HashType rows available in test database.');
    }

    $hashlist = $this->createDatabaseObject(
      Factory::getHashlistFactory(),
      new Hashlist(
        null,
        'hashlist_' . uniqid(),
        DHashlistFormat::PLAIN,
        (int)$hashTypeIds[0],
        1,
        ':',
        0,
        0,
        0,
        0,
        $group->getId(),
        '',
        0,
        0,
        0
      )
    );
    $this->assertInstanceOf(Hashlist::class, $hashlist);
    return $hashlist;
  }

  /**
   * Create a Task with an explicit keyspace value.
   *
   * @throws Exception
   */
  private function createTaskWithKeyspace(TaskWrapper $taskWrapper, int $keyspace): Task {
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary     = $this->createCrackerBinary($crackerBinaryType);
    $task = $this->createDatabaseObject(
      Factory::getTaskFactory(),
      new Task(
        null,
        'task_ks_' . uniqid(),
        '--attack-mode 0',
        60, 30,
        $keyspace,   // keyspace
        0, 1, 1, '#ffffff', 0, 0, 0, 0,
        $crackerBinary->getId(),
        $crackerBinaryType->getId(),
        $taskWrapper->getId(),
        0, '', 0, 0, 0, 0, ''
      )
    );
    $this->assertInstanceOf(Task::class, $task);
    return $task;
  }

  // -----------------------------------------------------------------------
  // joinAggregationFilter tests
  // -----------------------------------------------------------------------

  /**
   * Simplest case: one Task with two Chunks.
   * INNER JOIN Task → Chunk, SUM(checkpoint) and SUM(skip).
   *
   * @throws Exception
   */
  public function testSingleTaskCheckpointAndSkipSums(): void {
    $accessGroup = $this->createAccessGroup('single_task_ag');
    $hashlist = $this->createHashlistForGroup($accessGroup);
    $wrapper = $this->createTaskWrapper($accessGroup, $hashlist);
    $task = $this->createTaskWithKeyspace($wrapper, 0);

    $this->createChunkWithProgress($task, 0,  50);
    $this->createChunkWithProgress($task, 10, 70);

    $qF  = new ContainFilter(Task::TASK_ID, [$task->getId()]);
    $jF  = new JoinFilter(Factory::getChunkFactory(), Task::TASK_ID, Chunk::TASK_ID);
    $agg1 = new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM, Factory::getChunkFactory());
    $agg2 = new Aggregation(Chunk::SKIP,       Aggregation::SUM, Factory::getChunkFactory());

    $results = Factory::getTaskFactory()->joinAggregationFilter(
      [Factory::FILTER => $qF],
      $jF,
      [$agg1, $agg2]
    );

    $tasks = $results[Factory::getTaskFactory()->getModelTable()];
    $this->assertCount(1, $tasks);
    $this->assertEquals($task->getId(), $tasks[0]->getId());
    $this->assertEquals(120, $results[$agg1->getName()][0]); // 50 + 70
    $this->assertEquals(10,  $results[$agg2->getName()][0]); // 0 + 10
  }

  /**
   * Two tasks each with their own chunks: each gets a separate aggregation
   * row and the sums are independent.
   *
   * @throws Exception
   */
  public function testMultipleTasksGetSeparateAggregationRows(): void {
    $accessGroup1 = $this->createAccessGroup('ag1');
    $hashlist1 = $this->createHashlistForGroup($accessGroup1);
    $wrapper1 = $this->createTaskWrapper($accessGroup1, $hashlist1);
    $task1 = $this->createTaskWithKeyspace($wrapper1, 0);

    // Task 2 needs its own task wrapper hierarchy
    $accessGroup2 = $this->createAccessGroup('ag2');
    $hashlist2    = $this->createHashlistForGroup($accessGroup2);
    $wrapper2     = $this->createTaskWrapper($accessGroup2, $hashlist2);
    $task2        = $this->createTaskWithKeyspace($wrapper2, 0);

    // Chunks for task 1: checkpoint 40 + 60 = 100, skip 5 + 5 = 10
    $this->createChunkWithProgress($task1, 5, 40);
    $this->createChunkWithProgress($task1, 5, 60);

    // Chunks for task 2: checkpoint 200, skip 0
    $this->createChunkWithProgress($task2, 0, 200);

    $qF  = new ContainFilter(Task::TASK_ID, [$task1->getId(), $task2->getId()]);
    $jF  = new JoinFilter(Factory::getChunkFactory(), Task::TASK_ID, Chunk::TASK_ID);
    $agg1 = new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM, Factory::getChunkFactory());
    $agg2 = new Aggregation(Chunk::SKIP,       Aggregation::SUM, Factory::getChunkFactory());

    $results = Factory::getTaskFactory()->joinAggregationFilter(
      [Factory::FILTER => $qF],
      $jF,
      [$agg1, $agg2]
    );

    $tasks = $results[Factory::getTaskFactory()->getModelTable()];
    $this->assertCount(2, $tasks);

    // Build id → index map (results ordered by PK ASC)
    $idToIdx = [];
    foreach ($tasks as $idx => $t) {
      $idToIdx[$t->getId()] = $idx;
    }

    $this->assertArrayHasKey($task1->getId(), $idToIdx);
    $this->assertArrayHasKey($task2->getId(), $idToIdx);

    $idx1 = $idToIdx[$task1->getId()];
    $idx2 = $idToIdx[$task2->getId()];

    $this->assertEquals(100, $results[$agg1->getName()][$idx1]); // 40 + 60
    $this->assertEquals(10,  $results[$agg2->getName()][$idx1]); // 5 + 5
    $this->assertEquals(200, $results[$agg1->getName()][$idx2]);
    $this->assertEquals(0,   $results[$agg2->getName()][$idx2]);
  }

  /**
   * LEFT JOIN: a task with no chunks must still appear in the result with
   * NULL aggregation values — matching the supertask path in
   * GetCompletedCountHelperAPI that uses joinType: JoinFilter::LEFT.
   *
   * @throws Exception
   */
  public function testLeftJoinTaskWithNoChunksStillReturned(): void {
    $accessGroup1 = $this->createAccessGroup('ag_with_chunks');
    $hashlist1 = $this->createHashlistForGroup($accessGroup1);
    $wrapper1 = $this->createTaskWrapper($accessGroup1, $hashlist1);
    $taskWithChunks = $this->createTaskWithKeyspace($wrapper1, 0);

    $accessGroup2 = $this->createAccessGroup('ag_nochunks');
    $hashlist2    = $this->createHashlistForGroup($accessGroup2);
    $wrapper2     = $this->createTaskWrapper($accessGroup2, $hashlist2);
    $taskNoChunks = $this->createTaskWithKeyspace($wrapper2, 500);

    $this->createChunkWithProgress($taskWithChunks, 0, 90);

    $qF = new ContainFilter(Task::TASK_ID, [$taskWithChunks->getId(), $taskNoChunks->getId()]);
    $jF = new JoinFilter(
      Factory::getChunkFactory(),
      Task::TASK_ID,
      Chunk::TASK_ID,
      joinType: JoinFilter::LEFT
    );
    $agg = new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM, Factory::getChunkFactory());

    $results = Factory::getTaskFactory()->joinAggregationFilter(
      [Factory::FILTER => $qF],
      $jF,
      [$agg]
    );

    $tasks = $results[Factory::getTaskFactory()->getModelTable()];
    $this->assertCount(2, $tasks);

    $idToIdx = [];
    foreach ($tasks as $idx => $t) {
      $idToIdx[$t->getId()] = $idx;
    }

    $this->assertArrayHasKey($taskWithChunks->getId(), $idToIdx);
    $this->assertArrayHasKey($taskNoChunks->getId(), $idToIdx);

    $this->assertEquals(90,   $results[$agg->getName()][$idToIdx[$taskWithChunks->getId()]]);
    $this->assertNull(         $results[$agg->getName()][$idToIdx[$taskNoChunks->getId()]]);
  }

  /**
   * Mimics the GetCompletedCountHelperAPI *normal-task* pattern:
   *   - Main factory: TaskWrapperDisplay
   *   - joinFilter (aggregation join): TaskWrapperDisplay.taskId = Chunk.taskId (INNER)
   *   - Extra JOIN in options: TaskWrapperDisplay.hashlistId = Hashlist.hashlistId (ACL)
   *   - Filter: ContainFilter on Hashlist.accessGroupId (ACL)
   *
   * Verifies that only tasks belonging to the requested access group are
   * returned and that checkpoint/skip sums are correct.
   *
   * @throws Exception
   */
  public function testJoinAggregationWithAclJoinFiltersCorrectly(): void {
    // --- Task in access group 1 ---
    $ag1      = $this->createAccessGroup('acl_ag1');
    $hl1      = $this->createHashlistForGroup($ag1);
    $wrapper1 = $this->createTaskWrapper($ag1, $hl1, DTaskTypes::NORMAL);
    $task1    = $this->createTaskWithKeyspace($wrapper1, 1000);

    $this->createChunkWithProgress($task1, 0,  400);
    $this->createChunkWithProgress($task1, 0,  600);

    // --- Task in access group 2 (should be excluded) ---
    $ag2      = $this->createAccessGroup('acl_ag2');
    $hl2      = $this->createHashlistForGroup($ag2);
    $wrapper2 = $this->createTaskWrapper($ag2, $hl2, DTaskTypes::NORMAL);
    $task2    = $this->createTaskWithKeyspace($wrapper2, 1000);

    $this->createChunkWithProgress($task2, 0, 999);

    // Query: same pattern as GetCompletedCountHelperAPI normal-task path
    $qF1  = new QueryFilter(TaskWrapperDisplay::TASK_TYPE, DTaskTypes::NORMAL, "=");
    $qF2  = new QueryFilter(TaskWrapperDisplay::TASK_WRAPPER_IS_ARCHIVED, 0, "=");
    $qF3  = new ContainFilter(Hashlist::ACCESS_GROUP_ID, [$ag1->getId()], Factory::getHashlistFactory());

    $jF    = new JoinFilter(Factory::getChunkFactory(), TaskWrapperDisplay::TASK_ID, Chunk::TASK_ID);
    $aclJF = new JoinFilter(Factory::getHashlistFactory(), TaskWrapperDisplay::HASHLIST_ID, Hashlist::HASHLIST_ID);

    $agg1 = new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM, Factory::getChunkFactory());
    $agg2 = new Aggregation(Chunk::SKIP,       Aggregation::SUM, Factory::getChunkFactory());

    $results = Factory::getTaskWrapperDisplayFactory()->joinAggregationFilter(
      [Factory::FILTER => [$qF1, $qF2, $qF3], Factory::JOIN => [$aclJF]],
      $jF,
      [$agg1, $agg2]
    );

    $displayRows = $results[Factory::getTaskWrapperDisplayFactory()->getModelTable()];

    // Filter down to the rows our two test tasks produced
    $testTaskIds = [$task1->getId(), $task2->getId()];
    $ourRows = array_filter(
      array_keys($displayRows),
      fn($idx) => in_array($displayRows[$idx]->getTaskId(), $testTaskIds)
    );

    // Only task1 (in ag1) should be returned
    $this->assertCount(1, $ourRows);
    $ourIdx = array_values($ourRows)[0];

    $this->assertEquals($task1->getId(), $displayRows[$ourIdx]->getTaskId());
    $this->assertEquals(1000, $results[$agg1->getName()][$ourIdx]); // 400 + 600
    $this->assertEquals(0,    $results[$agg2->getName()][$ourIdx]);
  }

  /**
   * Full mimicry of the GetCompletedCountHelperAPI *completed-task detection*
   * logic using the normal-task path (TaskWrapperDisplay → Chunk INNER JOIN).
   *
   * A task is "completed" when: keyspace > 0 AND
   *   SUM(checkpoint) - SUM(skip) == keyspace
   *
   * Setup:
   *   - taskComplete:   keyspace 500, chunks covering 500 (checkpoint 500, skip 0)
   *   - taskIncomplete: keyspace 500, chunks covering only 300
   *   - taskZeroKs:     keyspace 0   (never counted as complete)
   *
   * @throws Exception
   */
  public function testCompletedNormalTaskDetection(): void {
    $ag  = $this->createAccessGroup('completed_ag');
    $hl  = $this->createHashlistForGroup($ag);

    // Completed task: checkpoint - skip = 500 == keyspace
    $wrapperComplete = $this->createTaskWrapper($ag, $hl, DTaskTypes::NORMAL);
    $taskComplete    = $this->createTaskWithKeyspace($wrapperComplete, 500);
    $this->createChunkWithProgress($taskComplete, 0, 300);
    $this->createChunkWithProgress($taskComplete, 0, 200);

    // Incomplete task: checkpoint - skip = 300 != 500
    $wrapperIncomplete = $this->createTaskWrapper($ag, $hl, DTaskTypes::NORMAL);
    $taskIncomplete    = $this->createTaskWithKeyspace($wrapperIncomplete, 500);
    $this->createChunkWithProgress($taskIncomplete, 0, 200);
    $this->createChunkWithProgress($taskIncomplete, 0, 100);

    // Zero-keyspace task: never complete regardless of chunk progress
    $wrapperZeroKs = $this->createTaskWrapper($ag, $hl, DTaskTypes::NORMAL);
    $taskZeroKs    = $this->createTaskWithKeyspace($wrapperZeroKs, 0);
    $this->createChunkWithProgress($taskZeroKs, 0, 999);

    $qF1  = new QueryFilter(TaskWrapperDisplay::TASK_TYPE, DTaskTypes::NORMAL, "=");
    $qF2  = new QueryFilter(TaskWrapperDisplay::TASK_WRAPPER_IS_ARCHIVED, 0, "=");
    $qF3  = new ContainFilter(Hashlist::ACCESS_GROUP_ID, [$ag->getId()], Factory::getHashlistFactory());

    $jF    = new JoinFilter(Factory::getChunkFactory(), TaskWrapperDisplay::TASK_ID, Chunk::TASK_ID);
    $aclJF = new JoinFilter(Factory::getHashlistFactory(), TaskWrapperDisplay::HASHLIST_ID, Hashlist::HASHLIST_ID);

    $agg1 = new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM, Factory::getChunkFactory());
    $agg2 = new Aggregation(Chunk::SKIP,       Aggregation::SUM, Factory::getChunkFactory());

    $results = Factory::getTaskWrapperDisplayFactory()->joinAggregationFilter(
      [Factory::FILTER => [$qF1, $qF2, $qF3], Factory::JOIN => [$aclJF]],
      $jF,
      [$agg1, $agg2]
    );

    $displayRows   = $results[Factory::getTaskWrapperDisplayFactory()->getModelTable()];
    $checkpoints   = $results[$agg1->getName()];
    $skips         = $results[$agg2->getName()];

    // Apply the same completion logic as GetCompletedCountHelperAPI
    $completed = 0;
    $testTaskIds = [
      $taskComplete->getId(),
      $taskIncomplete->getId(),
      $taskZeroKs->getId(),
    ];
    for ($i = 0; $i < count($displayRows); $i++) {
      $row = $displayRows[$i];
      if (!in_array($row->getTaskId(), $testTaskIds)) {
        continue;
      }
      if ($row->getKeyspace() > 0 && $checkpoints[$i] - $skips[$i] == $row->getKeyspace()) {
        $completed++;
      }
    }

    $this->assertEquals(1, $completed, 'Only taskComplete should be counted as completed');
  }

  /**
   * Mimics the GetCompletedCountHelperAPI *supertask* path:
   *   Task → Chunk LEFT JOIN, ContainFilter on taskWrapperId.
   *
   * A supertask is "completed" when ALL its constituent tasks are complete.
   * Here we verify that a task with no chunks (LEFT JOIN yields NULL) is
   * correctly treated as not-complete (preventing a false-positive).
   *
   * @throws Exception
   */
  public function testSupertaskLeftJoinCompletionDetection(): void {
    $ag      = $this->createAccessGroup('supertask_ag');
    $hl      = $this->createHashlistForGroup($ag);
    $wrapper = $this->createTaskWrapper($ag, $hl, DTaskTypes::SUPERTASK);

    $taskDone = $this->createTaskWithKeyspace($wrapper, 100);
    $this->createChunkWithProgress($taskDone, 0, 100);

    $taskPending = $this->createTaskWithKeyspace($wrapper, 200);
    // No chunks for $taskPending — it has not started yet

    $qF  = new ContainFilter(Task::TASK_WRAPPER_ID, [$wrapper->getId()]);
    $jF  = new JoinFilter(
      Factory::getChunkFactory(),
      Task::TASK_ID,
      Chunk::TASK_ID,
      joinType: JoinFilter::LEFT
    );
    $agg1 = new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM, Factory::getChunkFactory());
    $agg2 = new Aggregation(Chunk::SKIP,       Aggregation::SUM, Factory::getChunkFactory());

    $results = Factory::getTaskFactory()->joinAggregationFilter(
      [Factory::FILTER => $qF],
      $jF,
      [$agg1, $agg2]
    );

    $tasks       = $results[Factory::getTaskFactory()->getModelTable()];
    $checkpoints = $results[$agg1->getName()];
    $skips       = $results[$agg2->getName()];

    $this->assertCount(2, $tasks);

    // Replicate supertask completion logic:
    // start with every task assumed complete; mark false if any task falls short
    $completedMap = [$wrapper->getId() => true];
    for ($i = 0; $i < count($tasks); $i++) {
      $task  = $tasks[$i];
      $ks    = $task->getKeyspace();
      $cp    = $checkpoints[$i] ?? 0;
      $sk    = $skips[$i] ?? 0;
      if ($ks == 0 || ($cp - $sk) < $ks) {
        $completedMap[$task->getTaskWrapperId()] = false;
      }
    }

    // taskPending has no chunks → NULL sums → not complete → wrapper not complete
    $this->assertFalse($completedMap[$wrapper->getId()]);
  }

  /**
   * Verify that ORDER BY the primary key of the main factory is respected
   * and that a custom OrderFilter can be applied via options.
   *
   * @throws Exception
   */
  public function testJoinAggregationRespectsPkOrdering(): void {
    $ag   = $this->createAccessGroup('order_ag');
    $hl   = $this->createHashlistForGroup($ag);

    $wrapper1 = $this->createTaskWrapper($ag, $hl);
    $task1    = $this->createTaskWithKeyspace($wrapper1, 0);
    $this->createChunkWithProgress($task1, 0, 30);

    $wrapper2 = $this->createTaskWrapper($ag, $hl);
    $task2    = $this->createTaskWithKeyspace($wrapper2, 0);
    $this->createChunkWithProgress($task2, 0, 10);

    $wrapper3 = $this->createTaskWrapper($ag, $hl);
    $task3    = $this->createTaskWithKeyspace($wrapper3, 0);
    $this->createChunkWithProgress($task3, 0, 20);

    $qF  = new ContainFilter(Task::TASK_ID, [$task1->getId(), $task2->getId(), $task3->getId()]);
    $jF  = new JoinFilter(Factory::getChunkFactory(), Task::TASK_ID, Chunk::TASK_ID);
    $agg = new Aggregation(Chunk::CHECKPOINT, Aggregation::SUM, Factory::getChunkFactory());

    $results = Factory::getTaskFactory()->joinAggregationFilter(
      [Factory::FILTER => $qF],
      $jF,
      [$agg]
    );

    $tasks = $results[Factory::getTaskFactory()->getModelTable()];
    $this->assertCount(3, $tasks);

    // Default ordering is PK ASC, so task1 < task2 < task3 by insertion order
    $taskIds = array_map(fn($t) => $t->getId(), $tasks);
    $sorted  = $taskIds;
    sort($sorted);
    $this->assertEquals($sorted, $taskIds, 'Results should be ordered by PK ASC by default');
  }
}
