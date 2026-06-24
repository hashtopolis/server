<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class _sqlx_migrations extends AbstractModel {
  private ?string $version;
  private ?string $description;
  private ?string $installed_on;
  private ?int $success;
  private ?string $checksum;
  private ?int $execution_time;
  
  function __construct(?string $version, ?string $description, ?string $installed_on, ?int $success, ?string $checksum, ?int $execution_time) {
    $this->version = $version;
    $this->description = $description;
    $this->installed_on = $installed_on;
    $this->success = $success;
    $this->checksum = $checksum;
    $this->execution_time = $execution_time;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['version'] = $this->version;
    $dict['description'] = $this->description;
    $dict['installed_on'] = $this->installed_on;
    $dict['success'] = $this->success;
    $dict['checksum'] = $this->checksum;
    $dict['execution_time'] = $this->execution_time;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['version'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "version", "public" => False, "dba_mapping" => False];
    $dict['description'] = ['read_only' => True, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "description", "public" => False, "dba_mapping" => False];
    $dict['installed_on'] = ['read_only' => True, "type" => "datetime", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "installed_on", "public" => False, "dba_mapping" => False];
    $dict['success'] = ['read_only' => True, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "success", "public" => False, "dba_mapping" => False];
    $dict['checksum'] = ['read_only' => True, "type" => "binary", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "checksum", "public" => False, "dba_mapping" => False];
    $dict['execution_time'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "execution_time", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "version";
  }
  
  function getPrimaryKeyValue(): ?string {
    return $this->version;
  }
  
  function getId(): ?string {
    return $this->version;
  }
  
  function setId($id): void {
    $this->version = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getDescription(): ?string {
    return $this->description;
  }
  
  function setDescription(?string $description): void {
    $this->description = $description;
  }
  
  function getInstalled_on(): ?string {
    return $this->installed_on;
  }
  
  function setInstalled_on(?string $installed_on): void {
    $this->installed_on = $installed_on;
  }
  
  function getSuccess(): ?int {
    return $this->success;
  }
  
  function setSuccess(?int $success): void {
    $this->success = $success;
  }
  
  function getChecksum(): ?string {
    return $this->checksum;
  }
  
  function setChecksum(?string $checksum): void {
    $this->checksum = $checksum;
  }
  
  function getExecution_time(): ?int {
    return $this->execution_time;
  }
  
  function setExecution_time(?int $execution_time): void {
    $this->execution_time = $execution_time;
  }
  
  const VERSION = "version";
  const DESCRIPTION = "description";
  const INSTALLED__ON = "installed_on";
  const SUCCESS = "success";
  const CHECKSUM = "checksum";
  const EXECUTION__TIME = "execution_time";

  const PERM_CREATE = "perm_sqlx_migrationsCreate";
  const PERM_READ = "perm_sqlx_migrationsRead";
  const PERM_UPDATE = "perm_sqlx_migrationsUpdate";
  const PERM_DELETE = "perm_sqlx_migrationsDelete";
}
