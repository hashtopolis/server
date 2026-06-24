<?php

namespace Tests\Utils;

use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\models\Task;
use Hashtopolis\inc\DataSet;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DTaskStaticChunking;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\utils\ChunkUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class ChunkUtilsTest extends TestBase {

  // Injects a fake DataSet into the SConfig singleton via Reflection,
  // so tests can control config values without touching the database.
  private function mockSConfig(array $v): void {
    $p = (new \ReflectionClass(SConfig::class))->getProperty('instance');
    $p->setValue(null, new DataSet($v));
  }

  // Resets the SConfig singleton to null after every test so a mocked config
  // from one test never leaks into the next.
  protected function tearDown(): void {
    $p = (new \ReflectionClass(SConfig::class))->getProperty('instance');
    $p->setValue(null, null);
  }

  // Verifies that CHUNK_SIZE static mode bypasses all benchmark math and
  // returns the configured chunk size value directly.
  public function testStaticChunkSizeReturnsValueDirectly(): void {
    $this->assertSame(25000, ChunkUtils::calculateChunkSize(1000000, '5000:1000', 60, 1.0, DTaskStaticChunking::CHUNK_SIZE, 25000));
  }

  // Verifies that NUM_CHUNKS static mode divides the keyspace evenly and
  // rounds up (ceil) so no candidates are left out.
  // Result is cast to int because PHP ceil() returns float.
  public function testStaticNumChunksReturnsCeilDivision(): void {
    $this->assertSame((int) ceil(1000000 / 3), (int) ChunkUtils::calculateChunkSize(1000000, '5000:1000', 60, 1.0, DTaskStaticChunking::NUM_CHUNKS, 3));
  }

  // Verifies that misconfigured static chunking inputs always throw HTException.
  // Four cases via data provider: CHUNK_SIZE=0, NUM_CHUNKS=0,
  // NUM_CHUNKS>10000 (flood protection), and an unknown mode constant.
  // PHPUnit 12 requires the #[DataProvider] attribute — @dataProvider docblock no longer works.
  #[DataProvider('staticExceptionCases')]
  public function testStaticChunkingInvalidInputThrowsHTException(int $mode, int $size): void {
    $this->expectException(HTException::class);
    ChunkUtils::calculateChunkSize(1000000, '5000:1000', 60, 1.0, $mode, $size);
  }

  public static function staticExceptionCases(): array {
    return [
      'CHUNK_SIZE zero'      => [DTaskStaticChunking::CHUNK_SIZE, 0],
      'NUM_CHUNKS zero'      => [DTaskStaticChunking::NUM_CHUNKS, 0],
      'NUM_CHUNKS too large' => [DTaskStaticChunking::NUM_CHUNKS, 10001],
      'unknown mode'         => [99, 0],
    ];
  }

  // Verifies the old benchmark special case: benchmark=0 means the agent
  // reported no speed, so the entire keyspace is returned as one chunk.
  public function testOldBenchmarkZeroValueReturnsFullKeyspace(): void {
    $this->assertSame(500, ChunkUtils::calculateChunkSize(500, '0', 60));
  }

  // Verifies the old benchmark formula: floor(keyspace * benchmark * chunkTime / 100).
  // Result is cast to int because PHP floor() returns float.
  public function testOldBenchmarkNormalReturnsCorrectFormula(): void {
    $this->assertSame((int) floor(1000000 * 50 * 60 / 100), (int) ChunkUtils::calculateChunkSize(1000000, '50', 60));
  }

  // Verifies the new benchmark formula using "speed:time" format.
  // factor = chunkTime / time * 1000, size = floor(factor * speed).
  // Result is cast to int because PHP floor() returns float.
  public function testNewBenchmarkValidFormatReturnsCorrectFormula(): void {
    $this->assertSame((int) floor(30.0 * 5000000), (int) ChunkUtils::calculateChunkSize(999999999, '5000000:1000', 30));
  }

  // Verifies that new-format benchmarks with zero speed or zero time return 0
  // instead of crashing — the guard in calculateChunkSize() catches both.
  #[DataProvider('invalidBenchmarkCases')]
  public function testNewBenchmarkInvalidInputReturnsZero(string $benchmark): void {
    $this->assertSame(0, ChunkUtils::calculateChunkSize(1000000, $benchmark, 60));
  }

  public static function invalidBenchmarkCases(): array {
    return [
      'zero speed' => ['0:1000'],
      'zero time'  => ['5000000:0'],
    ];
  }

  // Verifies that a benchmark string with no colon routes to the old-benchmark
  // path and PHP 8 throws TypeError on arithmetic with a non-numeric string.
  public function testOldBenchmarkNonNumericStringThrowsTypeError(): void {
    $this->expectException(\TypeError::class);
    ChunkUtils::calculateChunkSize(1000000, 'invalid', 60);
  }

  // Verifies the safety floor: when the formula produces a size <= 0 the result
  // is clamped to 1 so dispatching never stalls on an infinite zero-size loop.
  // $QUERY must be set because the clamp path calls Util::createLogEntry which
  // reads $QUERY['token'] as a non-null TEXT value for the log entry.
  public function testSizeClampedToOneWhenCalculationProducesZero(): void {
    $GLOBALS['QUERY'] = ['token' => 'test'];
    $this->assertSame(1, (int) ChunkUtils::calculateChunkSize(1000000, '1:999999999', 1));
  }

  // Verifies that the tolerance multiplier correctly scales the chunk size up.
  // Both sides are cast to int because float arithmetic (30000000.0 * 1.1)
  // produces 33000000.000000004 due to IEEE 754 precision — int cast aligns them.
  public function testToleranceScalesChunkSizeUp(): void {
    $base = (int) ChunkUtils::calculateChunkSize(1000000, '50', 60, 1.0);
    $this->assertSame((int) ($base * 1.1), (int) ChunkUtils::calculateChunkSize(1000000, '50', 60, 1.1));
  }

  // Verifies that chunkTime=0 triggers the SConfig fallback: the server-wide
  // CHUNK_DURATION value is used instead of the per-task setting.
  // Result is cast to int because PHP floor() returns float.
  public function testZeroChunkTimeFallsBackToSConfigValue(): void {
    $this->mockSConfig([DConfig::CHUNK_DURATION => 120]);
    $this->assertSame((int) floor(1000000 * 50 * 120 / 100), (int) ChunkUtils::calculateChunkSize(1000000, '50', 0));
  }

  // Verifies that createNewChunk() returns null when the full keyspace has been
  // consumed (keyspace == keyspaceProgress). A mocked Task is used so no DB
  // records are needed; the mock returns getKeyspace()=1000 and
  // getKeyspaceProgress()=1000, making remaining=0 and triggering the null path.
  public function testCreateNewChunkReturnsNullWhenKeyspaceExhausted(): void {
    $this->mockSConfig([DConfig::DISP_TOLERANCE => 0, DConfig::CHUNK_DURATION => 600]);
    $task = $this->createMock(Task::class);
    $task->method('getSkipKeyspace')->willReturn(0);
    $task->method('getKeyspaceProgress')->willReturn(1000);
    $task->method('getKeyspace')->willReturn(1000);
    $this->assertNull(ChunkUtils::createNewChunk($task, $this->createMock(Assignment::class)));
  }

  // TODO: handleExistingChunk() and createNewChunk() require further test coverage.
}
