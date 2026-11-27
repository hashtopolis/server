<?php

namespace Hashtopolis\inc\apiv2\helper;

use Exception;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\defines\DNotificationType;
use Hashtopolis\inc\utils\AccessControl;
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetNotificationActionsHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/getNotificationActions";
  }

  public static function getAvailableMethods(): array {
    return ['GET'];
  }

  public function getRequiredPermissions(string $method): array {
    return [];
  }

  public static function getResponse(): null {
    return null;
  }

  /**
   * The actions are defined by the server, so a client cannot know them upfront.
   * Describe the meta member with a schema instead of a getResponse() sample.
   */
  public static function getMetaResponseSchema(): ?array {
    return [
      "type" => "object",
      "required" => ["actions"],
      "properties" => [
        "actions" => [
          "type" => "array",
          "description" => "All notification actions available on this server, in the order they are defined in.",
          "items" => [
            "type" => "object",
            "required" => ["action", "objectType", "requiredPermission", "allowed"],
            "properties" => [
              "action" => [
                "type" => "string",
                "description" => "Action to create a notification for, to be used as the 'action' attribute of a notification setting.",
              ],
              "objectType" => [
                "type" => "string",
                "enum" => ["Hashlist", "Agent", "User", "Task", "NONE"],
                "description" => "Type of the objects the action can be tied to via the 'actionFilter' of a notification setting, 'NONE' for actions which are only available for all objects.",
              ],
              "requiredPermission" => [
                "type" => "array",
                "items" => ["type" => "string"],
                "description" => "Legacy permission(s), any of which grants the use of the action.",
              ],
              "allowed" => [
                "type" => "boolean",
                "description" => "Whether the requesting user holds one of the required permissions and is allowed to create a notification for this action.",
              ],
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * @param array $data
   * @return AbstractModel|array|null
   * @throws HttpError
   */
  public function actionPost(array $data): AbstractModel|array|null {
    throw new HttpError("GetNotificationActions has no POST");
  }

  public function getParamsSwagger(): array {
    return [];
  }

  /**
   * Lists the notification actions this server offers, together with the
   * object type they can be tied to, the permission required to use them and
   * whether the requesting user holds it. Clients should offer the actions
   * with 'allowed' set to true when creating a notification setting.
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   * @throws JsonException
   * @throws HttpError
   * @throws Exception
   */
  public function handleGet(Request $request, Response $response): Response {
    $this->preCommon($request);

    $actions = [];
    foreach (DNotificationType::getAll() as $action) {
      $requiredPermission = DNotificationType::getRequiredPermission($action);
      $actions[] = [
        "action" => $action,
        "objectType" => DNotificationType::getObjectType($action),
        "requiredPermission" => is_array($requiredPermission) ? $requiredPermission : [$requiredPermission],
        "allowed" => AccessControl::getInstance()->hasPermission($requiredPermission),
      ];
    }

    $ret = self::createJsonResponse(meta: ["actions" => $actions]);

    $body = $response->getBody();
    $body->write($this->ret2json($ret));

    return $response->withStatus(200)
      ->withHeader("Content-Type", 'application/vnd.api+json;');
  }

  static public function register($app): void {
    $baseUri = self::getBaseUri();

    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, [self::class, 'handleGet']);
  }
}