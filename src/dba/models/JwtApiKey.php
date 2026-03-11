<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class JwtApiKey extends AbstractModel {
  private ?int $JwtApiKeyId;
  private ?int $startValid;
  private ?int $endValid;
  private ?int $userId;
  private ?int $isRevoked;
  
  function __construct(?int $JwtApiKeyId, ?int $startValid, ?int $endValid, ?int $userId, ?int $isRevoked) {
    $this->JwtApiKeyId = $JwtApiKeyId;
    $this->startValid = $startValid;
    $this->endValid = $endValid;
    $this->userId = $userId;
    $this->isRevoked = $isRevoked;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['JwtApiKeyId'] = $this->JwtApiKeyId;
    $dict['startValid'] = $this->startValid;
    $dict['endValid'] = $this->endValid;
    $dict['userId'] = $this->userId;
    $dict['isRevoked'] = $this->isRevoked;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['JwtApiKeyId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "JwtApiKeyId", "public" => False, "dba_mapping" => False];
    $dict['startValid'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "startValid", "public" => False, "dba_mapping" => False];
    $dict['endValid'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "endValid", "public" => False, "dba_mapping" => False];
    $dict['userId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "userId", "public" => False, "dba_mapping" => False];
    $dict['isRevoked'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "isRevoked", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "JwtApiKeyId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->JwtApiKeyId;
  }
  
  function getId(): ?int {
    return $this->JwtApiKeyId;
  }
  
  function setId($id): void {
    $this->JwtApiKeyId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getStartValid(): ?int {
    return $this->startValid;
  }
  
  function setStartValid(?int $startValid): void {
    $this->startValid = $startValid;
  }
  
  function getEndValid(): ?int {
    return $this->endValid;
  }
  
  function setEndValid(?int $endValid): void {
    $this->endValid = $endValid;
  }
  
  function getUserId(): ?int {
    return $this->userId;
  }
  
  function setUserId(?int $userId): void {
    $this->userId = $userId;
  }
  
  function getIsRevoked(): ?int {
    return $this->isRevoked;
  }
  
  function setIsRevoked(?int $isRevoked): void {
    $this->isRevoked = $isRevoked;
  }
  
  const _JWT_API_KEY_ID = "JwtApiKeyId";
  const START_VALID = "startValid";
  const END_VALID = "endValid";
  const USER_ID = "userId";
  const IS_REVOKED = "isRevoked";

  const PERM_CREATE = "permJwtApiKeyCreate";
  const PERM_READ = "permJwtApiKeyRead";
  const PERM_UPDATE = "permJwtApiKeyUpdate";
  const PERM_DELETE = "permJwtApiKeyDelete";
}
