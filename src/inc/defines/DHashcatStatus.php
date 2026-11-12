<?php

namespace Hashtopolis\inc\defines;

// hashcat status values, kept in sync with hashcat's status_rc_t enum
// (include/types.h in the hashcat repository). The values 12 and 15 are
// unused by hashcat and therefore intentionally missing here as well.
class DHashcatStatus {
  const INIT               = 0;
  const AUTOTUNE           = 1;
  const SELFTEST           = 2;
  const RUNNING            = 3;
  const PAUSED             = 4;
  const EXHAUSTED          = 5;
  const CRACKED            = 6;
  const ABORTED            = 7;
  const QUIT               = 8;
  const BYPASS             = 9;
  const ABORTED_CHECKPOINT = 10;
  const ABORTED_RUNTIME    = 11;
  const ERROR              = 13;
  const ABORTED_FINISH     = 14;
  const AUTODETECT         = 16;

  // States telling that a chunk was stopped before it was completed normally.
  const ABORTED_STATES = [
      DHashcatStatus::ABORTED,
      DHashcatStatus::QUIT,
      DHashcatStatus::BYPASS,
      DHashcatStatus::ABORTED_CHECKPOINT,
      DHashcatStatus::ABORTED_RUNTIME,
      DHashcatStatus::ERROR,
      DHashcatStatus::ABORTED_FINISH,
  ];
}