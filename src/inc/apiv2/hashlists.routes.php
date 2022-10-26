<?php


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpForbiddenException;

require_once(dirname(__FILE__) . "/../load.php");

$app->group("/api/v2/ui/hashlists", function (RouteCollectorProxy $group) { 
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });


    $group->get('', function (Request $request, Response $response, array $args): Response {
        $userId = $request->getAttribute(('userId'));
        $user = UserUtils::getUser($userId);

        $hashlists = HashlistUtils::getHashlists($user);

        $lists = [];
        foreach ($hashlists as $hashlist) {
        $lists[] = [
            UResponseHashlist::HASHLISTS_ID => (int)$hashlist->getId(),
            UResponseHashlist::HASHLISTS_HASHTYPE_ID => (int)$hashlist->getHashTypeId(),
            UResponseHashlist::HASHLISTS_NAME => $hashlist->getHashlistName(),
            UResponseHashlist::HASHLISTS_FORMAT => (int)$hashlist->getFormat(),
            UResponseHashlist::HASHLISTS_COUNT => (int)$hashlist->getHashCount()
        ];

        $body = $response->getBody();
        $body->write(json_encode($lists, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        return $response->withStatus(201)
            ->withHeader("Content-Type", "application/json");
        }
    });

    $group->post('', function (Request $request, Response $response, array $args): Response {
        $userId = $request->getAttribute(('userId'));
        $user = UserUtils::getUser($userId);

        if(!AccessControl::getInstance($user)->hasPermission(DAccessControl::CREATE_HASHLIST_ACCESS)) {
            throw new HttpForbiddenException($request, "No '" . DAccessControl::getDescription(DAccessControl::CREATE_HASHLIST_ACCESS) . "' permission");
        }

        $QUERY = $request->getParsedBody();

        $toCheck = [
            UQueryHashlist::HASHLIST_NAME,
            UQueryHashlist::HASHLIST_IS_SALTED,
            UQueryHashlist::HASHLIST_IS_SECRET,
            UQueryHashlist::HASHLIST_HEX_SALTED,
            UQueryHashlist::HASHLIST_SEPARATOR,
            UQueryHashlist::HASHLIST_FORMAT,
            UQueryHashlist::HASHLIST_HASHTYPE_ID,
            UQueryHashlist::HASHLIST_ACCESS_GROUP_ID,
            UQueryHashlist::HASHLIST_DATA,
            UQueryHashlist::HASHLIST_USE_BRAIN,
            UQueryHashlist::HASHLIST_BRAIN_FEATURES
          ];
          foreach ($toCheck as $input) {
            if (!isset($QUERY[$input])) {
              throw new HTException("Required parameter '" . $input . "' not specified");
            }
          }

        $hashlist = HashlistUtils::createHashlist(
            $QUERY[UQueryHashlist::HASHLIST_NAME],
            $QUERY[UQueryHashlist::HASHLIST_IS_SALTED],
            $QUERY[UQueryHashlist::HASHLIST_IS_SECRET],
            $QUERY[UQueryHashlist::HASHLIST_HEX_SALTED],
            $QUERY[UQueryHashlist::HASHLIST_SEPARATOR],
            $QUERY[UQueryHashlist::HASHLIST_FORMAT],
            $QUERY[UQueryHashlist::HASHLIST_HASHTYPE_ID],
            $QUERY[UQueryHashlist::HASHLIST_SEPARATOR],
            $QUERY[UQueryHashlist::HASHLIST_ACCESS_GROUP_ID],
            "paste",
            ['hashfield' => base64_decode($QUERY[UQueryHashlist::HASHLIST_DATA])],
            [],
            $user,
            $QUERY[UQueryHashlist::HASHLIST_USE_BRAIN],
            $QUERY[UQueryHashlist::HASHLIST_BRAIN_FEATURES]
          );  

          
        $item = [
            UResponseHashlist::HASHLISTS_ID => (int)$hashlist->getId(),
            UResponseHashlist::HASHLISTS_HASHTYPE_ID => (int)$hashlist->getHashTypeId(),
            UResponseHashlist::HASHLISTS_NAME => $hashlist->getHashlistName(),
            UResponseHashlist::HASHLISTS_FORMAT => (int)$hashlist->getFormat(),
            UResponseHashlist::HASHLISTS_COUNT => (int)$hashlist->getHashCount()
        ];

        $body = $response->getBody();
        $body->write(json_encode($item, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        return $response->withStatus(201)
            ->withHeader("Content-Type", "application/json");
    });
});


$app->group("/api/v2/ui/hashlists/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });


    $group->get('', function (Request $request, Response $response, array $args): Response {
        $userId = $request->getAttribute(('userId'));
        $user = UserUtils::getUser($userId);

        $hashlistId = $args['id'];
        try {
            $hashlist = HashlistUtils::getHashlist($hashlistId);
        } catch (HTException $ex) {
            throw new HttpNotFoundException($request, $ex->getMessage());
        }
        if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
          throw new HttpForbiddenException($request, "No access to hashlist!");
        }

        $item = [
            UResponseHashlist::HASHLISTS_ID => (int)$hashlist->getId(),
            UResponseHashlist::HASHLISTS_HASHTYPE_ID => (int)$hashlist->getHashTypeId(),
            UResponseHashlist::HASHLISTS_NAME => $hashlist->getHashlistName(),
            UResponseHashlist::HASHLISTS_FORMAT => (int)$hashlist->getFormat(),
            UResponseHashlist::HASHLISTS_COUNT => (int)$hashlist->getHashCount()
        ];

        $body = $response->getBody();
        $body->write(json_encode($item, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        return $response->withStatus(201)
            ->withHeader("Content-Type", "application/json");
    });


    $group->patch('', function (Request $request, Response $response, array $args): Response {
        $userId = $request->getAttribute(('userId'));
        $user = UserUtils::getUser($userId);

        if(!AccessControl::getInstance($user)->hasPermission(DAccessControl::MANAGE_HASHLIST_ACCESS)) {
            throw new HttpForbiddenException($request, "No '" . DAccessControl::getDescription(DAccessControl::MANAGE_HASHLIST_ACCESS) . "' permission");
        }

        $hashlistId = $args['id'];
        try {
            $hashlist = HashlistUtils::getHashlist($hashlistId);
        } catch (HTException $ex) {
            throw new HttpNotFoundException($request, $ex->getMessage());
        }
        if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
          throw new HttpForbiddenException($request, "No access to hashlist!");
        }

        $data = $request->getParsedBody();
        if (isset($data[UResponseHashlist::HASHLISTS_NAME])) {
            $hashlist->setHashlistName($data[UResponseHashlist::HASHLISTS_NAME]);
            HashlistUtils::rename($hashlistId, $data[UQueryHashlist::HASHLIST_NAME], $user);
        }

        $hashlist = HashlistUtils::getHashlist($hashlistId);
        $item = [
            UResponseHashlist::HASHLISTS_ID => (int)$hashlist->getId(),
            UResponseHashlist::HASHLISTS_HASHTYPE_ID => (int)$hashlist->getHashTypeId(),
            UResponseHashlist::HASHLISTS_NAME => $hashlist->getHashlistName(),
            UResponseHashlist::HASHLISTS_FORMAT => (int)$hashlist->getFormat(),
            UResponseHashlist::HASHLISTS_COUNT => (int)$hashlist->getHashCount()
        ];

        $body = $response->getBody();
        $body->write(json_encode($item, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        return $response->withStatus(201)
            ->withHeader("Content-Type", "application/json");
    });

    $group->delete('', function (Request $request, Response $response, array $args): Response {
        $userId = $request->getAttribute(('userId'));
        $user = UserUtils::getUser($userId);

        if(!AccessControl::getInstance($user)->hasPermission(DAccessControl::MANAGE_HASHLIST_ACCESS)) {
            throw new HttpForbiddenException($request, "No '" . DAccessControl::getDescription(DAccessControl::MANAGE_HASHLIST_ACCESS) . "' permission");
        }

        $hashlistId = $args['id'];
        try {
            $hashlist = HashlistUtils::getHashlist($hashlistId);
        } catch (HTException $ex) {
            throw new HttpNotFoundException($request, $ex->getMessage());
        }
        if (!AccessUtils::userCanAccessHashlists($hashlist, $user)) {
          throw new HttpForbiddenException($request, "No access to hashlist!");
        }

        return $response->withStatus(204)
        ->withHeader("Content-Type", "application/json");
    });
});