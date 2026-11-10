<?php

namespace Hashtopolis\inc\defines;

use PHPUnit\Framework\TestCase;

require_once(dirname(__FILE__) . '/../../../../src/inc/defines/DHashcatStatus.php');

/**
 * Unit tests for {@see DHashcatStatus} — the chunk state constants must stay
 * in sync with hashcat's status_rc_t enum (include/types.h in the hashcat
 * repository).
 */
final class DHashcatStatusTest extends TestCase {
  /**
   * The constants mirror hashcat's status_rc_t values, including the gaps at
   * 12 and 15 which hashcat does not use.
   */
  public function testValuesMatchHashcatStatusRc(): void {
    $this->assertSame(0, DHashcatStatus::INIT);
    $this->assertSame(1, DHashcatStatus::AUTOTUNE);
    $this->assertSame(2, DHashcatStatus::SELFTEST);
    $this->assertSame(3, DHashcatStatus::RUNNING);
    $this->assertSame(4, DHashcatStatus::PAUSED);
    $this->assertSame(5, DHashcatStatus::EXHAUSTED);
    $this->assertSame(6, DHashcatStatus::CRACKED);
    $this->assertSame(7, DHashcatStatus::ABORTED);
    $this->assertSame(8, DHashcatStatus::QUIT);
    $this->assertSame(9, DHashcatStatus::BYPASS);
    $this->assertSame(10, DHashcatStatus::ABORTED_CHECKPOINT);
    $this->assertSame(11, DHashcatStatus::ABORTED_RUNTIME);
    $this->assertSame(13, DHashcatStatus::ERROR);
    $this->assertSame(14, DHashcatStatus::ABORTED_FINISH);
    $this->assertSame(16, DHashcatStatus::AUTODETECT);
  }

  /**
   * ABORTED_STATES covers exactly the states telling that a chunk was stopped
   * prematurely and none of the states of a normally completed chunk.
   */
  public function testAbortedStatesContents(): void {
    $this->assertSame(
      [
        DHashcatStatus::ABORTED,
        DHashcatStatus::QUIT,
        DHashcatStatus::BYPASS,
        DHashcatStatus::ABORTED_CHECKPOINT,
        DHashcatStatus::ABORTED_RUNTIME,
        DHashcatStatus::ERROR,
        DHashcatStatus::ABORTED_FINISH,
      ],
      DHashcatStatus::ABORTED_STATES
    );
    $this->assertNotContains(DHashcatStatus::EXHAUSTED, DHashcatStatus::ABORTED_STATES);
    $this->assertNotContains(DHashcatStatus::CRACKED, DHashcatStatus::ABORTED_STATES);
  }
}
