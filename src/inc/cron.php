<?php

use Hashtopolis\inc\jobs\BackgroundJobRunner;
use Hashtopolis\inc\jobs\PeriodicJobScheduler;

require_once(__DIR__ . "/../../vendor/autoload.php");
require_once(__DIR__ . "/startup/include.php");

set_time_limit(0);
// Enqueue the periodic jobs which are due, then drain the queue. Scheduling deliberately
// does not live in BackgroundJobRunner::run(), whose contract is to drain the queue and
// which the tests call directly: every entrypoint which wants periodic jobs calls both.
PeriodicJobScheduler::scheduleDue();
BackgroundJobRunner::run();
