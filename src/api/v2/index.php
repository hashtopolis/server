<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/auth/apiKeyAuthenticate.php';
require_once __DIR__ . '/compose/swaggerPatch.php';
require_once __DIR__ . '/controller/access.php';
require_once __DIR__ . '/controller/hashlist.php';
require_once __DIR__ . '/controller/hashType.php';
require_once __DIR__ . '/controller/health.php';
require_once __DIR__ . '/controller/task.php';

use Luracast\Restler\Defaults,
    Luracast\Restler\Restler,
    Luracast\Restler\Explorer\Info,
    Luracast\Restler\Explorer\v2\Explorer;

use APIv2\AccessController,
    APIv2\ApiKeyAuthenticate,
    APIv2\HashlistController,
    APIv2\HashTypeController,
    APIv2\HealthController,
    APIv2\TaskController,
    APIv2\SwaggerPatchCompose;

Info::$title = 'Hashtopolis API Explorer';
Info::$description = 'Live API Documentation';
Info::$termsOfServiceUrl = null;
Info::$contactName = null;
Info::$contactEmail = null;
Info::$contactUrl = 'https://github.com/hashtopolis/server';
Info::$license = 'GNU General Public License v3.0';
Info::$licenseUrl = 'https://github.com/hashtopolis/server/blob/master/LICENSE.txt';

Explorer::$hideProtected = true;

// TODO: ensure correct access right for cache directory
$productionMode = false;
$refreshCache = false;

$r = new Restler($productionMode, $refreshCache);
$r->addAuthenticationClass(ApiKeyAuthenticate::class);

$r->addAPIClass(AccessController::class, 'access');
$r->addAPIClass(HashlistController::class, 'hashlists');
$r->addAPIClass(HashTypeController::class, 'hashTypes');
$r->addAPIClass(HealthController::class, 'health');
$r->addAPIClass(TaskController::class, 'tasks');

if (!$productionMode) {
    // only enable Swagger in debug mode
    $r->addAPIClass(Explorer::class);
    // only detect and patch Swagger document in debug mode
    Defaults::$composeClass = SwaggerPatchCompose::class;
    // also enable free CORS in debug mode (for now) TODO: implement correct handling
    Defaults::$crossOriginResourceSharing = true;
    Defaults::$accessControlAllowOrigin = '*';
}

$r->handle();