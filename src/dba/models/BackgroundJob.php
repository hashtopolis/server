<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class BackgroundJob extends AbstractModel {
  private ?int $backgroundJobId;
  private ?string $jobType;
  private ?string $payload;
  private ?int $status;
  private ?int $userId;
  private ?int $createdAt;
  private ?int $startedAt;
  private ?int $finishedAt;
  private ?int $exitCode;
  private ?string $resultMessage;
  
  function __construct(?int $backgroundJobId, ?string $jobType, ?string $payload, ?int $status, ?int $userId, ?int $createdAt, ?int $startedAt, ?int $finishedAt, ?int $exitCode, ?string $resultMessage) {
    $this->backgroundJobId = $backgroundJobId;
    $this->jobType = $jobType;
    $this->payload = $payload;
    $this->status = $status;
    $this->userId = $userId;
    $this->createdAt = $createdAt;
    $this->startedAt = $startedAt;
    $this->finishedAt = $finishedAt;
    $this->exitCode = $exitCode;
    $this->resultMessage = $resultMessage;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['backgroundJobId'] = $this->backgroundJobId;
    $dict['jobType'] = $this->jobType;
    $dict['payload'] = $this->payload;
    $dict['status'] = $this->status;
    $dict['userId'] = $this->userId;
    $dict['createdAt'] = $this->createdAt;
    $dict['startedAt'] = $this->startedAt;
    $dict['finishedAt'] = $this->finishedAt;
    $dict['exitCode'] = $this->exitCode;
    $dict['resultMessage'] = $this->resultMessage;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['backgroundJobId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "backgroundJobId", "public" => False, "dba_mapping" => False];
    $dict['jobType'] = ['read_only' => True, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "jobType", "public" => False, "dba_mapping" => False];
    $dict['payload'] = ['read_only' => True, "type" => "json", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "payload", "public" => False, "dba_mapping" => False];
    $dict['status'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => [-1 => "Failed", 0 => "Pending", 1 => "Running", 2 => "Done", ], "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "status", "public" => False, "dba_mapping" => False];
    $dict['userId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => True, "private" => False, "alias" => "userId", "public" => False, "dba_mapping" => False];
    $dict['createdAt'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "createdAt", "public" => False, "dba_mapping" => False];
    $dict['startedAt'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => True, "private" => False, "alias" => "startedAt", "public" => False, "dba_mapping" => False];
    $dict['finishedAt'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => True, "private" => False, "alias" => "finishedAt", "public" => False, "dba_mapping" => False];
    $dict['exitCode'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => True, "private" => False, "alias" => "exitCode", "public" => False, "dba_mapping" => False];
    $dict['resultMessage'] = ['read_only' => True, "type" => "str(1024)", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => True, "private" => False, "alias" => "resultMessage", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "backgroundJobId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->backgroundJobId;
  }
  
  function getId(): ?int {
    return $this->backgroundJobId;
  }
  
  function setId($id): void {
    $this->backgroundJobId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getJobType(): ?string {
    return $this->jobType;
  }
  
  function setJobType(?string $jobType): void {
    $this->jobType = $jobType;
  }
  
  function getPayload(): ?string {
    return $this->payload;
  }
  
  function setPayload(?string $payload): void {
    $this->payload = $payload;
  }
  
  function getStatus(): ?int {
    return $this->status;
  }
  
  function setStatus(?int $status): void {
    $this->status = $status;
  }
  
  function getUserId(): ?int {
    return $this->userId;
  }
  
  function setUserId(?int $userId): void {
    $this->userId = $userId;
  }
  
  function getCreatedAt(): ?int {
    return $this->createdAt;
  }
  
  function setCreatedAt(?int $createdAt): void {
    $this->createdAt = $createdAt;
  }
  
  function getStartedAt(): ?int {
    return $this->startedAt;
  }
  
  function setStartedAt(?int $startedAt): void {
    $this->startedAt = $startedAt;
  }
  
  function getFinishedAt(): ?int {
    return $this->finishedAt;
  }
  
  function setFinishedAt(?int $finishedAt): void {
    $this->finishedAt = $finishedAt;
  }
  
  function getExitCode(): ?int {
    return $this->exitCode;
  }
  
  function setExitCode(?int $exitCode): void {
    $this->exitCode = $exitCode;
  }
  
  function getResultMessage(): ?string {
    return $this->resultMessage;
  }
  
  function setResultMessage(?string $resultMessage): void {
    $this->resultMessage = $resultMessage;
  }
  
  const BACKGROUND_JOB_ID = "backgroundJobId";
  const JOB_TYPE = "jobType";
  const PAYLOAD = "payload";
  const STATUS = "status";
  const USER_ID = "userId";
  const CREATED_AT = "createdAt";
  const STARTED_AT = "startedAt";
  const FINISHED_AT = "finishedAt";
  const EXIT_CODE = "exitCode";
  const RESULT_MESSAGE = "resultMessage";

  const PERM_CREATE = "permBackgroundJobCreate";
  const PERM_READ = "permBackgroundJobRead";
  const PERM_UPDATE = "permBackgroundJobUpdate";
  const PERM_DELETE = "permBackgroundJobDelete";
}
