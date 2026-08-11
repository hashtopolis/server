<?php

/**
 * Generate the APIv2 OpenAPI spec offline (no database, no HTTP server).
 *
 * Usage: php ci/tools/generate-openapi.php [--compliant] [--pretty]
 *   --compliant  apply the sanitizer used by /api/v2/openapi-compliant.json
 *   --pretty     pretty-print the JSON output
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Hashtopolis\inc\apiv2\common\ApiRegistry;
use Hashtopolis\inc\apiv2\openapi\SpecBuilder;
use Hashtopolis\inc\apiv2\openapi\SpecSanitizer;

$spec = (new SpecBuilder())->buildForApiClasses(ApiRegistry::allApiClasses());
if (in_array('--compliant', $argv, true)) {
  $spec = (new SpecSanitizer())->sanitize($spec);
}
$flags = JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;
if (in_array('--pretty', $argv, true)) {
  $flags |= JSON_PRETTY_PRINT;
}
echo json_encode($spec, $flags), PHP_EOL;
