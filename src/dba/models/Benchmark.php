<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class Benchmark extends AbstractModel {
  private ?int $benchmarkId;
  private ?int $crackerBinaryId;
  private ?int $hashTypeId;
  private ?string $attackParameters;
  private ?string $deviceSignature;
  private ?string $benchmarkType;
  private ?string $benchmarkValue;
  private ?int $createTime;
  private ?int $expireTime;
  
  function __construct(?int $benchmarkId, ?int $crackerBinaryId, ?int $hashTypeId, ?string $attackParameters, ?string $deviceSignature, ?string $benchmarkType, ?string $benchmarkValue, ?int $createTime, ?int $expireTime) {
    $this->benchmarkId = $benchmarkId;
    $this->crackerBinaryId = $crackerBinaryId;
    $this->hashTypeId = $hashTypeId;
    $this->attackParameters = $attackParameters;
    $this->deviceSignature = $deviceSignature;
    $this->benchmarkType = $benchmarkType;
    $this->benchmarkValue = $benchmarkValue;
    $this->createTime = $createTime;
    $this->expireTime = $expireTime;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['benchmarkId'] = $this->benchmarkId;
    $dict['crackerBinaryId'] = $this->crackerBinaryId;
    $dict['hashTypeId'] = $this->hashTypeId;
    $dict['attackParameters'] = $this->attackParameters;
    $dict['deviceSignature'] = $this->deviceSignature;
    $dict['benchmarkType'] = $this->benchmarkType;
    $dict['benchmarkValue'] = $this->benchmarkValue;
    $dict['createTime'] = $this->createTime;
    $dict['expireTime'] = $this->expireTime;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['benchmarkId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "benchmarkId", "public" => False, "dba_mapping" => False];
    $dict['crackerBinaryId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "crackerBinaryId", "public" => False, "dba_mapping" => False];
    $dict['hashTypeId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "hashTypeId", "public" => False, "dba_mapping" => False];
    $dict['attackParameters'] = ['read_only' => True, "type" => "str(64)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "attackParameters", "public" => False, "dba_mapping" => False];
    $dict['deviceSignature'] = ['read_only' => True, "type" => "str(64)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "deviceSignature", "public" => False, "dba_mapping" => False];
    $dict['benchmarkType'] = ['read_only' => True, "type" => "str(10)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "benchmarkType", "public" => False, "dba_mapping" => False];
    $dict['benchmarkValue'] = ['read_only' => True, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "benchmarkValue", "public" => False, "dba_mapping" => False];
    $dict['createTime'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "createTime", "public" => False, "dba_mapping" => False];
    $dict['expireTime'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "expireTime", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "benchmarkId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->benchmarkId;
  }
  
  function getId(): ?int {
    return $this->benchmarkId;
  }
  
  function setId($id): void {
    $this->benchmarkId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getCrackerBinaryId(): ?int {
    return $this->crackerBinaryId;
  }
  
  function setCrackerBinaryId(?int $crackerBinaryId): void {
    $this->crackerBinaryId = $crackerBinaryId;
  }
  
  function getHashTypeId(): ?int {
    return $this->hashTypeId;
  }
  
  function setHashTypeId(?int $hashTypeId): void {
    $this->hashTypeId = $hashTypeId;
  }
  
  function getAttackParameters(): ?string {
    return $this->attackParameters;
  }
  
  function setAttackParameters(?string $attackParameters): void {
    $this->attackParameters = $attackParameters;
  }
  
  function getDeviceSignature(): ?string {
    return $this->deviceSignature;
  }
  
  function setDeviceSignature(?string $deviceSignature): void {
    $this->deviceSignature = $deviceSignature;
  }
  
  function getBenchmarkType(): ?string {
    return $this->benchmarkType;
  }
  
  function setBenchmarkType(?string $benchmarkType): void {
    $this->benchmarkType = $benchmarkType;
  }
  
  function getBenchmarkValue(): ?string {
    return $this->benchmarkValue;
  }
  
  function setBenchmarkValue(?string $benchmarkValue): void {
    $this->benchmarkValue = $benchmarkValue;
  }
  
  function getCreateTime(): ?int {
    return $this->createTime;
  }
  
  function setCreateTime(?int $createTime): void {
    $this->createTime = $createTime;
  }
  
  function getExpireTime(): ?int {
    return $this->expireTime;
  }
  
  function setExpireTime(?int $expireTime): void {
    $this->expireTime = $expireTime;
  }
  
  const BENCHMARK_ID = "benchmarkId";
  const CRACKER_BINARY_ID = "crackerBinaryId";
  const HASH_TYPE_ID = "hashTypeId";
  const ATTACK_PARAMETERS = "attackParameters";
  const DEVICE_SIGNATURE = "deviceSignature";
  const BENCHMARK_TYPE = "benchmarkType";
  const BENCHMARK_VALUE = "benchmarkValue";
  const CREATE_TIME = "createTime";
  const EXPIRE_TIME = "expireTime";

  const PERM_CREATE = "permBenchmarkCreate";
  const PERM_READ = "permBenchmarkRead";
  const PERM_UPDATE = "permBenchmarkUpdate";
  const PERM_DELETE = "permBenchmarkDelete";
}
