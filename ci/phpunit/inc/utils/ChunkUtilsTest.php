<?php

namespace Tests\Utils;

use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\models\Task;
use Hashtopolis\inc\DataSet;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DPrince;
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

  // Verifies that CHUNK_SIZE static mode bypasses all adaptive math and
  // returns the configured chunk size value directly.
  // Arg #2 (chunkSpeed) is an int now but is ignored entirely on the static path.
  public function testStaticChunkSizeReturnsValueDirectly(): void {
    $this->assertSame(25000, ChunkUtils::calculateChunkSize(1000000, 5000, 60, 1.0, DTaskStaticChunking::CHUNK_SIZE, 25000));
  }

  // Verifies that NUM_CHUNKS static mode divides the keyspace evenly and
  // rounds up (ceil) so no candidates are left out.
  // Result is cast to int because PHP ceil() returns float.
  public function testStaticNumChunksReturnsCeilDivision(): void {
    $this->assertSame((int) ceil(1000000 / 3), (int) ChunkUtils::calculateChunkSize(1000000, 5000, 60, 1.0, DTaskStaticChunking::NUM_CHUNKS, 3));
  }

  // Verifies that misconfigured static chunking inputs always throw HTException.
  // Four cases via data provider: CHUNK_SIZE=0, NUM_CHUNKS=0,
  // NUM_CHUNKS>10000 (flood protection), and an unknown mode constant.
  // PHPUnit 12 requires the #[DataProvider] attribute — @dataProvider docblock no longer works.
  #[DataProvider('staticExceptionCases')]
  public function testStaticChunkingInvalidInputThrowsHTException(int $mode, int $size): void {
    $this->expectException(HTException::class);
    ChunkUtils::calculateChunkSize(1000000, 5000, 60, 1.0, $mode, $size);
  }

  public static function staticExceptionCases(): array {
    return [
      'CHUNK_SIZE zero'      => [DTaskStaticChunking::CHUNK_SIZE, 0],
      'NUM_CHUNKS zero'      => [DTaskStaticChunking::NUM_CHUNKS, 0],
      'NUM_CHUNKS too large' => [DTaskStaticChunking::NUM_CHUNKS, 10001],
      'unknown mode'         => [99, 0],
    ];
  }

  // Verifies the bootstrap special case: a zero chunkSpeed means there is no
  // usable observed speed yet, so the entire keyspace is returned as one chunk.
  // This subsumes the legacy "benchmark == 0 => return keyspace" behaviour.
  public function testZeroChunkSpeedReturnsFullKeyspace(): void {
    $this->assertSame(500, ChunkUtils::calculateChunkSize(500, 0, 60));
  }

  // Verifies the adaptive sizing formula: size = floor(chunkSpeed * chunkTime / SPEED_SCALE),
  // where chunkSpeed is stored scaled by SPEED_SCALE (1000). The keyspace (999999999 here) does
  // not enter the formula; it only bounds dispatch elsewhere.
  public function testAdaptiveFormulaSizesFromChunkSpeed(): void {
    $this->assertSame((int) floor(5000 * 60 / 1000), (int) ChunkUtils::calculateChunkSize(999999999, 5000, 60));
  }

  // Verifies the smallest positive product still yields a usable chunk of 1.
  // floor(1 * 1) * 1.0 == 1, so this goes through the normal adaptive path.
  // NOTE: the old fractional clamp/log branch (chunkSize <= 0 -> 1 + log entry)
  // is now unreachable from a positive integer chunkSpeed — a positive int speed
  // times a positive chunkTime can never floor below 1 — and is kept purely as
  // defense. Because this test never reaches that branch it must not touch
  // $GLOBALS['QUERY'] / Util::createLogEntry.
  public function testMinimumPositiveChunkSpeedReturnsOne(): void {
    // chunkSpeed is scaled; 1000 (=1 base-word/s) * 1s / SPEED_SCALE = 1, staying on the normal path.
    $this->assertSame(1, (int) ChunkUtils::calculateChunkSize(1000000, 1000, 1));
  }

  // Verifies that the tolerance multiplier correctly scales the chunk size up.
  // Both sides are cast to int because float arithmetic (300000.0 * 1.1)
  // produces 330000.00000000006 due to IEEE 754 precision — int cast aligns them.
  public function testToleranceScalesChunkSizeUp(): void {
    $base = (int) ChunkUtils::calculateChunkSize(1000000, 5000, 60, 1.0);
    $this->assertSame((int) ($base * 1.1), (int) ChunkUtils::calculateChunkSize(1000000, 5000, 60, 1.1));
  }

  // Verifies that chunkTime=0 triggers the SConfig fallback: the server-wide
  // CHUNK_DURATION value is used instead of the per-task setting.
  // Result is cast to int because PHP floor() returns float.
  public function testZeroChunkTimeFallsBackToSConfigValue(): void {
    $this->mockSConfig([DConfig::CHUNK_DURATION => 120]);
    $this->assertSame((int) floor(5000 * 120 / 1000), (int) ChunkUtils::calculateChunkSize(999999999, 5000, 0));
  }

  // Verifies the PRINCE guard: PRINCE_KEYSPACE is a negative sentinel (-1605).
  // With a zero chunkSpeed the bootstrap branch fires, and because the keyspace
  // is not > 0 it must clamp to 1 rather than returning the negative sentinel.
  public function testPrinceKeyspaceWithZeroChunkSpeedReturnsOne(): void {
    $this->assertSame(1, (int) ChunkUtils::calculateChunkSize(DPrince::PRINCE_KEYSPACE, 0, 60));
  }

  // --- reconcileSpeed: exponential-moving-average blending of observed speed ---

  // Unseeded ($old <= 0): adopt the observed value verbatim.
  public function testReconcileSpeedAdoptsObservedWhenUnseeded(): void {
    $this->assertSame(1000000, ChunkUtils::reconcileSpeed(0, 1000000));
  }

  // Stall filter: an observed speed <= RECONCILE_MIN_SPEED (1) is treated as a
  // measurement artifact and ignored, keeping the previous speed.
  public function testReconcileSpeedIgnoresStall(): void {
    $this->assertSame(100000, ChunkUtils::reconcileSpeed(100000, 0));
  }

  // Upward moves are clamped to RECONCILE_MAX_UP_RATIO (2x) before blending:
  // upper = floor(100000 * 2) = 200000; target = 200000;
  // blended = (1-0.6)*100000 + 0.6*200000 = 160000, i.e. at most 1.6x per completed chunk.
  public function testReconcileSpeedClampsUpwardJump(): void {
    $this->assertSame(160000, ChunkUtils::reconcileSpeed(100000, 1000000));
  }

  // Climb-only: a lower observed rate never shrinks the stored speed (maintainer rule against
  // automatic chunk shrinking, hashtopolis/server#729; also removes downward oscillation).
  public function testReconcileSpeedNeverShrinks(): void {
    $this->assertSame(1000000, ChunkUtils::reconcileSpeed(1000000, 100000));
  }

  // Upward convergence: starting well below a steady observed speed, the clamped
  // EMA should climb past 900000 within a few iterations. Verified sequence is
  // 160000,256000,409600,655360,862144,944858; crosses at iteration 6.
  public function testReconcileSpeedConvergesUpward(): void {
    $speed = 100000;
    $observed = 1000000;
    $crossed = false;
    for ($i = 1; $i <= 8; $i++) {
      $speed = ChunkUtils::reconcileSpeed($speed, $observed);
      if ($speed > 900000) {
        $crossed = true;
        break;
      }
    }
    $this->assertTrue($crossed, 'upward EMA should cross 900000 within 8 iterations');
  }

  // Climb-only stability: repeated lower observations never drag the speed down; it stays flat.
  public function testReconcileSpeedStaysFlatOnLowerObservations(): void {
    $speed = 1000000;
    for ($i = 0; $i < 5; $i++) {
      $speed = ChunkUtils::reconcileSpeed($speed, 100000);
    }
    $this->assertSame(1000000, $speed, 'climb-only speed must not decay on lower observations');
  }

  // --- completedChunkSpeed: scaled base-word rate measured over a WHOLE completed chunk ---

  // length / duration, scaled by SPEED_SCALE: 50000 base words over 10s -> 50000*1000/10 = 5000000.
  public function testCompletedChunkSpeedComputesScaledRate(): void {
    $this->assertSame(5000000, ChunkUtils::completedChunkSpeed(50000, 1000, 1010));
  }

  // The scaled rate is floored to an integer: 10001*1000/6 = 1666833.33 -> 1666833.
  public function testCompletedChunkSpeedFloorsToInteger(): void {
    $this->assertSame(1666833, ChunkUtils::completedChunkSpeed(10001, 1000, 1006));
  }

  // A chunk shorter than RECONCILE_MIN_DURATION (5s) is too short to measure a stable rate -> null.
  public function testCompletedChunkSpeedSkipsTooShortChunk(): void {
    $this->assertNull(ChunkUtils::completedChunkSpeed(50000, 1000, 1004));
  }

  // Defensive: a non-monotonic clock (solve < dispatch) yields a negative duration and is skipped.
  public function testCompletedChunkSpeedSkipsNonPositiveDuration(): void {
    $this->assertNull(ChunkUtils::completedChunkSpeed(50000, 1000, 999));
  }

  // Zero-length chunk teaches us nothing about the rate -> null.
  public function testCompletedChunkSpeedSkipsZeroLength(): void {
    $this->assertNull(ChunkUtils::completedChunkSpeed(0, 1000, 1010));
  }

  // The signal is base-words/s and multiplier-AGNOSTIC: it is a pure function of chunk length (base
  // words) and wall-clock duration, with no dependence on the raw hashcat hash-rate, the rule count,
  // or the salt count. The same base-word length over the same duration yields the same rate.
  public function testCompletedChunkSpeedIsMultiplierAgnostic(): void {
    $rate = ChunkUtils::completedChunkSpeed(15000, 1000, 1010);
    $this->assertSame(1500000, $rate);
    $this->assertSame($rate, ChunkUtils::completedChunkSpeed(15000, 9000, 9010));
  }

  // --- benchmarkToChunkSpeed: normalise stored benchmark values to H/s ---

  // SPEED_TEST "speed:time" format, scaled by SPEED_SCALE: floor(speed * 1000 * 1000 / time). Keyspace IGNORED.
  public function testBenchmarkToChunkSpeedSpeedTestFormat(): void {
    $this->assertSame(5000000, ChunkUtils::benchmarkToChunkSpeed("5000:1000", null));
  }

  // SPEED_TEST format ignores the keyspace argument entirely:
  // floor(12000 * 1000 * 1000 / 500) = 24000000 regardless of the (here bogus) keyspace 999.
  public function testBenchmarkToChunkSpeedSpeedTestIgnoresKeyspace(): void {
    $this->assertSame(24000000, ChunkUtils::benchmarkToChunkSpeed("12000:500", 999));
  }

  // RUN_TIME scalar format, scaled: floor(benchmark * keyspace * 1000 / 100) = floor(50 * 1000000 * 1000 / 100) = 500000000.
  public function testBenchmarkToChunkSpeedRunTimeFormat(): void {
    $this->assertSame(500000000, ChunkUtils::benchmarkToChunkSpeed(50, 1000000));
  }

  // A scalar RUN_TIME benchmark with no usable keyspace cannot be converted.
  #[DataProvider('benchmarkRunTimeNullKeyspaceCases')]
  public function testBenchmarkToChunkSpeedRunTimeNeedsKeyspace($keyspace): void {
    $this->assertNull(ChunkUtils::benchmarkToChunkSpeed(50, $keyspace));
  }

  public static function benchmarkRunTimeNullKeyspaceCases(): array {
    return [
      'null keyspace' => [null],
      'zero keyspace' => [0],
    ];
  }

  // Unparseable / non-positive benchmark inputs all normalise to null.
  #[DataProvider('benchmarkInvalidCases')]
  public function testBenchmarkToChunkSpeedReturnsNullOnInvalid($benchmark): void {
    $this->assertNull(ChunkUtils::benchmarkToChunkSpeed($benchmark, 1));
  }

  public static function benchmarkInvalidCases(): array {
    return [
      'zero speed component' => ["0:1000"],
      'zero time component'  => ["5000:0"],
      'non-numeric string'   => ["abc"],
      'empty string'         => [""],
    ];
  }

  // DESIGN §4.1 legacy-equivalence: seeding chunkSpeed from a RUN_TIME benchmark and then applying
  // the adaptive formula (calculateChunkSize, which divides the scaled speed back out) reproduces the
  // old legacy size floor(keyspace * benchmark * chunkTime / 100).
  // seed = floor(50 * 1000000 * 1000 / 100) = 500000000; floor(500000000 * 60 / 1000) = 30000000.
  public function testRunTimeSeedReproducesLegacySize(): void {
    $seed = ChunkUtils::benchmarkToChunkSpeed(50, 1000000);
    $this->assertSame((int) floor(1000000 * 50 * 60 / 100), (int) ChunkUtils::calculateChunkSize(999999999, $seed, 60));
  }

  // The scaled seed keeps the first-chunk size within one chunkTime of the legacy size even when
  // benchmark*keyspace/100 is non-integral. The x1000 scale preserves the sub-unit part that the old
  // unscaled seed truncated, so divergence is now at most a rounding unit (often exactly 0).
  public function testRunTimeSeedStaysWithinOneChunkTimeOfLegacy(): void {
    $keyspace = 999999;
    $benchmark = 50;
    $chunkTime = 60;
    $legacy = (int) floor($keyspace * $benchmark * $chunkTime / 100);
    $seed = ChunkUtils::benchmarkToChunkSpeed($benchmark, $keyspace);
    $adaptive = (int) ChunkUtils::calculateChunkSize(999999999, $seed, $chunkTime);
    $this->assertLessThan($chunkTime, abs($legacy - $adaptive), 'seed-vs-legacy divergence must stay under one chunkTime');
  }

  // Verifies that createNewChunk() returns null when the full keyspace has been
  // consumed (keyspace == keyspaceProgress). A mocked Task is used so no DB
  // records are needed; the mock returns getKeyspace()=1000 and
  // getKeyspaceProgress()=1000, making remaining=0 and triggering the null path.
  // The Assignment mock returns getChunkSpeed()=0 so the (short-circuited)
  // calculateChunkSize call stays on the bootstrap path.
  public function testCreateNewChunkReturnsNullWhenKeyspaceExhausted(): void {
    $this->mockSConfig([DConfig::DISP_TOLERANCE => 0, DConfig::CHUNK_DURATION => 600]);
    $task = $this->createStub(Task::class);
    $task->method('getSkipKeyspace')->willReturn(0);
    $task->method('getKeyspaceProgress')->willReturn(1000);
    $task->method('getKeyspace')->willReturn(1000);
    $assignment = $this->createStub(Assignment::class);
    $assignment->method('getChunkSpeed')->willReturn(0);
    $this->assertNull(ChunkUtils::createNewChunk($task, $assignment));
  }

  // TODO: handleExistingChunk() and createNewChunk() require further test coverage.
}
