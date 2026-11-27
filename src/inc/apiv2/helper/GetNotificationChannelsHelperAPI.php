<?php

namespace Hashtopolis\inc\apiv2\helper;

use Exception;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\notifications\HashtopolisNotification;
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetNotificationChannelsHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/getNotificationChannels";
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
   * An installation can register its own notification channel implementations,
   * so the set of channel names is only known to the server. Describe the meta
   * member with a schema instead of a getResponse() sample.
   */
  public static function getMetaResponseSchema(): ?array {
    return [
      "type" => "object",
      "required" => ["channels"],
      "properties" => [
        "channels" => [
          "type" => "array",
          "description" => "All notification channels (delivery implementations) available on this server.",
          "items" => [
            "type" => "object",
            "required" => ["name"],
            "properties" => [
              "name" => [
                "type" => "string",
                "description" => "Channel name, to be used as the 'notification' attribute of a notification setting.",
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
    throw new HttpError("GetNotificationChannels has no POST");
  }

  public function getParamsSwagger(): array {
    return [];
  }

  /**
   * Lists the notification channels (delivery implementations) this server
   * offers. Clients should offer them for the 'notification' attribute when
   * creating a notification setting.
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

    $channels = [];
    foreach (HashtopolisNotification::getInstances() as $name => $notification) {
      $channels[] = ["name" => $name];
    }

    $ret = self::createJsonResponse(meta: ["channels" => $channels]);

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