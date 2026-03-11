<?php

namespace Hashtopolis\inc\apiv2\model;

use Firebase\JWT\JWT;
use Hashtopolis\dba\models\JwtApiKey;
use Hashtopolis\dba\models\User;
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
    return ['GET', 'POST', 'PATCH'];
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
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    $rightGroup = $this->getRightGroup($this->getCurrentUser()->getRightGroupId());

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
      "scope" => $rightGroup->getPermissions(),
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
  public function updateObject(int $objectId, array $data): void {
    throw new HttpError("ApiToken cannot be updated via API");
  }
  
  /**
   * @throws HttpError
   */
  protected function deleteObject(object $object): void {
    throw new HttpError("ApiToken cannot be deleted via API");
  }
}