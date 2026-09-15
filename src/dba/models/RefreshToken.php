<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

/**
 * A single refresh token, used to obtain a new short-lived access token without re-authenticating.
 *
 * Tokens are never stored in plaintext; only the SHA-256 hash of the token string is kept, so a
 * database leak does not hand out usable tokens.
 *
 * Tokens rotate: every successful refresh consumes the presented token (sets usedAt) and issues a
 * new one carrying the same familyId. A family therefore represents one login session across all of
 * its rotations. Presenting an already consumed token means the token leaked, and the whole family
 * is revoked.
 */
class RefreshToken extends AbstractModel {
  private ?int $refreshTokenId;
  private ?int $userId;
  private ?string $tokenHash;
  private ?string $familyId;
  private ?int $issuedAt;
  private ?int $endValid;
  private ?int $usedAt;
  private ?int $isRevoked;
  
  function __construct(?int $refreshTokenId, ?int $userId, ?string $tokenHash, ?string $familyId, ?int $issuedAt, ?int $endValid, ?int $usedAt, ?int $isRevoked) {
    $this->refreshTokenId = $refreshTokenId;
    $this->userId = $userId;
    $this->tokenHash = $tokenHash;
    $this->familyId = $familyId;
    $this->issuedAt = $issuedAt;
    $this->endValid = $endValid;
    $this->usedAt = $usedAt;
    $this->isRevoked = $isRevoked;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['refreshTokenId'] = $this->refreshTokenId;
    $dict['userId'] = $this->userId;
    $dict['tokenHash'] = $this->tokenHash;
    $dict['familyId'] = $this->familyId;
    $dict['issuedAt'] = $this->issuedAt;
    $dict['endValid'] = $this->endValid;
    $dict['usedAt'] = $this->usedAt;
    $dict['isRevoked'] = $this->isRevoked;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['refreshTokenId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "refreshTokenId", "public" => False, "dba_mapping" => False];
    $dict['userId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "userId", "public" => False, "dba_mapping" => False];
    $dict['tokenHash'] = ['read_only' => True, "type" => "str(64)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => True, "alias" => "tokenHash", "public" => False, "dba_mapping" => False];
    $dict['familyId'] = ['read_only' => True, "type" => "str(32)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => True, "alias" => "familyId", "public" => False, "dba_mapping" => False];
    $dict['issuedAt'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "issuedAt", "public" => False, "dba_mapping" => False];
    $dict['endValid'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "endValid", "public" => False, "dba_mapping" => False];
    $dict['usedAt'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "usedAt", "public" => False, "dba_mapping" => False];
    $dict['isRevoked'] = ['read_only' => True, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isRevoked", "public" => False, "dba_mapping" => False];
    
    return $dict;
  }
  
  function getPrimaryKey(): string {
    return "refreshTokenId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->refreshTokenId;
  }
  
  function getId(): ?int {
    return $this->refreshTokenId;
  }
  
  function setId($id): void {
    $this->refreshTokenId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getUserId(): ?int {
    return $this->userId;
  }
  
  function setUserId(?int $userId): void {
    $this->userId = $userId;
  }
  
  function getTokenHash(): ?string {
    return $this->tokenHash;
  }
  
  function setTokenHash(?string $tokenHash): void {
    $this->tokenHash = $tokenHash;
  }
  
  function getFamilyId(): ?string {
    return $this->familyId;
  }
  
  function setFamilyId(?string $familyId): void {
    $this->familyId = $familyId;
  }
  
  function getIssuedAt(): ?int {
    return $this->issuedAt;
  }
  
  function setIssuedAt(?int $issuedAt): void {
    $this->issuedAt = $issuedAt;
  }
  
  function getEndValid(): ?int {
    return $this->endValid;
  }
  
  function setEndValid(?int $endValid): void {
    $this->endValid = $endValid;
  }
  
  function getUsedAt(): ?int {
    return $this->usedAt;
  }
  
  function setUsedAt(?int $usedAt): void {
    $this->usedAt = $usedAt;
  }
  
  function getIsRevoked(): ?int {
    return $this->isRevoked;
  }
  
  function setIsRevoked(?int $isRevoked): void {
    $this->isRevoked = $isRevoked;
  }
  
  const REFRESH_TOKEN_ID = "refreshTokenId";
  const USER_ID = "userId";
  const TOKEN_HASH = "tokenHash";
  const FAMILY_ID = "familyId";
  const ISSUED_AT = "issuedAt";
  const END_VALID = "endValid";
  const USED_AT = "usedAt";
  const IS_REVOKED = "isRevoked";
}
