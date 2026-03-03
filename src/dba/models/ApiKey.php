<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class ApiKey extends AbstractModel {
  private ?int $apiKeyId;
  private ?int $startValid;
  private ?int $endValid;
  private ?string $accessKey;
  private ?int $accessCount;
  private ?int $userId;
  private ?int $apiGroupId;
  
  function __construct(?int $apiKeyId, ?int $startValid, ?int $endValid, ?string $accessKey, ?int $accessCount, ?int $userId, ?int $apiGroupId) {
    $this->apiKeyId = $apiKeyId;
    $this->startValid = $startValid;
    $this->endValid = $endValid;
    $this->accessKey = $accessKey;
    $this->accessCount = $accessCount;
    $this->userId = $userId;
    $this->apiGroupId = $apiGroupId;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['apiKeyId'] = $this->apiKeyId;
    $dict['startValid'] = $this->startValid;
    $dict['endValid'] = $this->endValid;
    $dict['accessKey'] = $this->accessKey;
    $dict['accessCount'] = $this->accessCount;
    $dict['userId'] = $this->userId;
    $dict['apiGroupId'] = $this->apiGroupId;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['apiKeyId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "apiKeyId", "public" => False, "dba_mapping" => False];
    $dict['startValid'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "startValid", "public" => False, "dba_mapping" => False];
    $dict['endValid'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "endValid", "public" => False, "dba_mapping" => False];
    $dict['accessKey'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "accessKey", "public" => False, "dba_mapping" => False];
    $dict['accessCount'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "accessCount", "public" => False, "dba_mapping" => False];
    $dict['userId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "userId", "public" => False, "dba_mapping" => False];
    $dict['apiGroupId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "apiGroupId", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "apiKeyId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->apiKeyId;
  }
  
  function getId(): ?int {
    return $this->apiKeyId;
  }
  
  function setId($id): void {
    $this->apiKeyId = $id;
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
  
  function getAccessKey(): ?string {
    return $this->accessKey;
  }
  
  function setAccessKey(?string $accessKey): void {
    $this->accessKey = $accessKey;
  }
  
  function getAccessCount(): ?int {
    return $this->accessCount;
  }
  
  function setAccessCount(?int $accessCount): void {
    $this->accessCount = $accessCount;
  }
  
  function getUserId(): ?int {
    return $this->userId;
  }
  
  function setUserId(?int $userId): void {
    $this->userId = $userId;
  }
  
  function getApiGroupId(): ?int {
    return $this->apiGroupId;
  }
  
  function setApiGroupId(?int $apiGroupId): void {
    $this->apiGroupId = $apiGroupId;
  }
  
  const API_KEY_ID = "apiKeyId";
  const START_VALID = "startValid";
  const END_VALID = "endValid";
  const ACCESS_KEY = "accessKey";
  const ACCESS_COUNT = "accessCount";
  const USER_ID = "userId";
  const API_GROUP_ID = "apiGroupId";

  const PERM_CREATE = "permApiKeyCreate";
  const PERM_READ = "permApiKeyRead";
  const PERM_UPDATE = "permApiKeyUpdate";
  const PERM_DELETE = "permApiKeyDelete";
}
