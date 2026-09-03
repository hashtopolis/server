<?php

declare(strict_types=1);

/**
 * Entry point for the download API.
 *
 * Bootstraps the Slim 4 application defined in {@see DownloadApp}. The single
 * GET route serves downloads of resources like locally stored cracker binary
 * archives, authentication is done with either an agent token or an apiv2 JWT.
 */

use Hashtopolis\inc\downloadapi\DownloadApp;

require_once(dirname(__FILE__) . "/../inc/startup/include.php");
set_time_limit(0);

DownloadApp::create()->run();
