<?php

declare(strict_types=1);

/**
 * Entry point for the agent API.
 *
 * Bootstraps the Slim 4 application defined in {@see AgentApiApp}.  The single
 * POST route dispatches on the ``action`` field of the JSON body via
 * {@see ActionRegistry}.  In Phase 1 the existing ``src/inc/api/API*.php``
 * handlers are delegated to (they use ``echo`` + ``die()``); in Phase 2 these
 * will be replaced by clean PSR-7 controllers.
 */

use Hashtopolis\inc\agentapi\AgentApiApp;

require_once(dirname(__FILE__) . "/../inc/startup/include.php");
set_time_limit(0);

AgentApiApp::create()->run();
