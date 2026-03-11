<?php

namespace Hashtopolis\inc\apiv2\model;

use Firebase\JWT\JWT;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\JwtApiKey;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\StartupConfig;

use Hashtopolis\inc\utils\JwtTokenUtils;

class ApiTokenAPI extends AbstractModelAPI {
  private ?string $jwtToken = null;

  private function setJwtToken(string $token): void {
      $this->jwtToken = $token;
  }

  private function getJwtToken(): ?string {
    return $this->jwtToken;
  }

  public static function getBaseUri(): string {
    return "/api/v2/ui/apiTokens";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET', 'POST', 'PATCH', 'DELETE'];
  }
  
  public static function getDBAclass(): string {
    return JwtApiKey::class;
  }
  
  public static function getToOneRelationships(): array {
    return [
      'user' => [
        'key' => JwtApiKey::USER_ID,
        
        'relationType' => User::class,
        'relationKey' => User::USER_ID,
      ]
    ];
  }

  public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return [
      "scopes" => ['type' => 'array', 'subtype' => 'string']
    ];
  }

  protected function getSingleACL(User $user, object $object): bool {
    return ($object->getUserId() === $user->getId());
  }
  
  protected function getFilterACL(): array {
    $userId = $this->getCurrentUser()->getId();
    return [
      Factory::FILTER => [
        new QueryFilter(User::USER_ID, $userId, "=")
      ]
    ];
  }
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    //Scopes is an array of permissions in format [permFileTaskUpdate, permAgentDelete]
    $scopes = explode(",", $data["scopes"]);

    $allPermissions = $this->getRightGroup($this->getCurrentUser()->getRightGroupId())->getPermissions();
    if ($allPermissions == 'ALL') {
      // Special (legacy) case for administrative access, enable all available permissions
      $all_perms = array_keys(self::$acl_mapping);
      $rightgroup_perms = array_combine($all_perms, array_fill(0, count($all_perms), true));
    }
    else {
      $rightgroup_perms = json_decode($allPermissions, true);
    }
    $NotAllowedPerms = array_filter($rightgroup_perms, fn($v) => $v === false);
    $allowedPerms = array_intersect_key($rightgroup_perms, array_flip($scopes));

    $requestedScopes = $allowedPerms + $NotAllowedPerms;

    $secret = StartupConfig::getInstance()->getPepper(0);
    $iat = $data[JwtApiKey::START_VALID];
    $expires = $data[JwtApiKey::END_VALID];
    $token = JwtTokenUtils::createKey($this->getCurrentUser()->getId(), $iat, $expires);
    $jti = $token->getId();

    $payload = [
      "iat" => $iat,
      "exp" => $expires,
      "jti" => $jti,
      "userId" => $this->getCurrentUser()->getId(),
      "scope" => $requestedScopes,
      "iss" => "Hashtopolis",
      "kid" =>  hash("sha256", $secret)
    ];

    $tokenEncoded = JWT::encode($payload, $secret, "HS256");
    $this->setJwtToken($tokenEncoded);

    return $token->getId();
  }

  function aggregateData(object $object, array &$included_data = [], ?array $aggregateFieldsets = null): array {
    // $token is only set in POST, this way the actual token is only returned after creation.
    $aggregatedData = [];
    $token = $this->getJwtToken();
    if ($token !== null) {
      $aggregatedData["Token"] = $token;
    }

    return $aggregatedData;
  }
  
  /**
   * @throws HttpError
   */
  protected function deleteObject(object $object): void {
    JwtTokenUtils::deleteKey($object);
  }
}