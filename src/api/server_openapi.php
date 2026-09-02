<?php

declare(strict_types=1);

/**
 * Serves the generated OpenAPI 3.1 specification for the agent API.
 *
 * The spec is generated at request time by {@see OpenApiSchema} from
 * {@see ActionRegistry} and {@see SchemaRegistry} — no static file to maintain.
 */

use Hashtopolis\inc\agentapi\schema\OpenApiSchema;

require_once(__DIR__ . "/../inc/startup/include.php");

header('Content-Type: application/json');
echo OpenApiSchema::generateJson();
