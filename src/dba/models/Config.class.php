<?php

namespace DBA;

class Config extends AbstractModel {
  private ?int $configId;
  private ?int $configSectionId;
  private ?string $item;
  private ?string $value;
  
  function __construct(?int $configId, ?int $configSectionId, ?string $item, ?string $value) {
    $this->configId = $configId;
    $this->configSectionId = $configSectionId;
    $this->item = $item;
    $this->value = $value;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['configId'] = $this->configId;
    $dict['configSectionId'] = $this->configSectionId;
    $dict['item'] = $this->item;
    $dict['value'] = $this->value;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['configId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "configId", "public" => False];
    $dict['configSectionId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "configSectionId", "public" => False];
    $dict['item'] = ['read_only' => False, "type" => "str(128)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "item", "public" => False];
    $dict['value'] = ['read_only' => False, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "value", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "configId";
  }
  
  function getPrimaryKeyValue(): int {
    return $this->configId;
  }
  
  function getId(): int {
    return $this->configId;
  }
  
  function setId($id): void {
    $this->configId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getConfigSectionId(): ?int {
    return $this->configSectionId;
  }
  
  function setConfigSectionId(?int $configSectionId): void {
    $this->configSectionId = $configSectionId;
  }
  
  function getItem(): ?string {
    return $this->item;
  }
  
  function setItem(?string $item): void {
    $this->item = $item;
  }
  
  function getValue(): ?string {
    return $this->value;
  }
  
  function setValue(?string $value): void {
    $this->value = $value;
  }
  
  const CONFIG_ID = "configId";
  const CONFIG_SECTION_ID = "configSectionId";
  const ITEM = "item";
  const VALUE = "value";

  const PERM_CREATE = "permConfigCreate";
  const PERM_READ = "permConfigRead";
  const PERM_UPDATE = "permConfigUpdate";
  const PERM_DELETE = "permConfigDelete";
}
