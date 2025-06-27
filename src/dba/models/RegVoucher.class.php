<?php

namespace DBA;

class RegVoucher extends AbstractModel {
  private ?int $regVoucherId;
  private ?string $voucher;
  private ?int $time;
  
  function __construct(?int $regVoucherId, ?string $voucher, ?int $time) {
    $this->regVoucherId = $regVoucherId;
    $this->voucher = $voucher;
    $this->time = $time;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['regVoucherId'] = $this->regVoucherId;
    $dict['voucher'] = $this->voucher;
    $dict['time'] = $this->time;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['regVoucherId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "regVoucherId", "public" => False];
    $dict['voucher'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "voucher", "public" => False];
    $dict['time'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "time", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "regVoucherId";
  }
  
  function getPrimaryKeyValue(): int {
    return $this->regVoucherId;
  }
  
  function getId(): int {
    return $this->regVoucherId;
  }
  
  function setId($id): void {
    $this->regVoucherId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getVoucher(): ?string {
    return $this->voucher;
  }
  
  function setVoucher(?string $voucher): void {
    $this->voucher = $voucher;
  }
  
  function getTime(): ?int {
    return $this->time;
  }
  
  function setTime(?int $time): void {
    $this->time = $time;
  }
  
  const REG_VOUCHER_ID = "regVoucherId";
  const VOUCHER = "voucher";
  const TIME = "time";

  const PERM_CREATE = "permRegVoucherCreate";
  const PERM_READ = "permRegVoucherRead";
  const PERM_UPDATE = "permRegVoucherUpdate";
  const PERM_DELETE = "permRegVoucherDelete";
}
