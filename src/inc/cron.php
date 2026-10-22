<?php

use Hashtopolis\inc\jobs\BackgroundJobRunner;

require_once(__DIR__ . "/../../vendor/autoload.php");
require_once(__DIR__ . "/startup/include.php");

set_time_limit(0);
BackgroundJobRunner::run();
