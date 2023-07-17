<?php

namespace DBA;

class RegVoucher extends AbstractModel {
  private $regVoucherId;
  private $voucher;
  private $time;
  
  function __construct($regVoucherId, $voucher, $time) {
    $this->regVoucherId = $regVoucherId;
    $this->voucher = $voucher;
    $this->time = $time;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['regVoucherId'] = $this->regVoucherId;
    $dict['voucher'] = $this->voucher;
    $dict['time'] = $this->time;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['regVoucherId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "regVoucherId"];
    $dict['voucher'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "voucher"];
    $dict['time'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "time"];

    return $dict;
  }

  function getPrimaryKey() {
    return "regVoucherId";
  }
  
  function getPrimaryKeyValue() {
    return $this->regVoucherId;
  }
  
  function getId() {
    return $this->regVoucherId;
  }
  
  function setId($id) {
    $this->regVoucherId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getVoucher() {
    return $this->voucher;
  }
  
  function setVoucher($voucher) {
    $this->voucher = $voucher;
  }
  
  function getTime() {
    return $this->time;
  }
  
  function setTime($time) {
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
