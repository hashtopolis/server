<?php

namespace DBA;

class Hashlist extends AbstractModel {
  private $hashlistId;
  private $hashlistName;
  private $format;
  private $hashTypeId;
  private $hashCount;
  private $saltSeparator;
  private $cracked;
  private $isSecret;
  private $hexSalt;
  private $isSalted;
  private $accessGroupId;
  private $notes;
  private $brainId;
  private $brainFeatures;
  
  function __construct($hashlistId, $hashlistName, $format, $hashTypeId, $hashCount, $saltSeparator, $cracked, $isSecret, $hexSalt, $isSalted, $accessGroupId, $notes, $brainId, $brainFeatures) {
    $this->hashlistId = $hashlistId;
    $this->hashlistName = $hashlistName;
    $this->format = $format;
    $this->hashTypeId = $hashTypeId;
    $this->hashCount = $hashCount;
    $this->saltSeparator = $saltSeparator;
    $this->cracked = $cracked;
    $this->isSecret = $isSecret;
    $this->hexSalt = $hexSalt;
    $this->isSalted = $isSalted;
    $this->accessGroupId = $accessGroupId;
    $this->notes = $notes;
    $this->brainId = $brainId;
    $this->brainFeatures = $brainFeatures;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['hashlistId'] = $this->hashlistId;
    $dict['hashlistName'] = $this->hashlistName;
    $dict['format'] = $this->format;
    $dict['hashTypeId'] = $this->hashTypeId;
    $dict['hashCount'] = $this->hashCount;
    $dict['saltSeparator'] = $this->saltSeparator;
    $dict['cracked'] = $this->cracked;
    $dict['isSecret'] = $this->isSecret;
    $dict['hexSalt'] = $this->hexSalt;
    $dict['isSalted'] = $this->isSalted;
    $dict['accessGroupId'] = $this->accessGroupId;
    $dict['notes'] = $this->notes;
    $dict['brainId'] = $this->brainId;
    $dict['brainFeatures'] = $this->brainFeatures;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "hashlistId";
  }
  
  function getPrimaryKeyValue() {
    return $this->hashlistId;
  }
  
  function getId() {
    return $this->hashlistId;
  }
  
  function setId($id) {
    $this->hashlistId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getHashlistName() {
    return $this->hashlistName;
  }
  
  function setHashlistName($hashlistName) {
    $this->hashlistName = $hashlistName;
  }
  
  function getFormat() {
    return $this->format;
  }
  
  function setFormat($format) {
    $this->format = $format;
  }
  
  function getHashTypeId() {
    return $this->hashTypeId;
  }
  
  function setHashTypeId($hashTypeId) {
    $this->hashTypeId = $hashTypeId;
  }
  
  function getHashCount() {
    return $this->hashCount;
  }
  
  function setHashCount($hashCount) {
    $this->hashCount = $hashCount;
  }
  
  function getSaltSeparator() {
    return $this->saltSeparator;
  }
  
  function setSaltSeparator($saltSeparator) {
    $this->saltSeparator = $saltSeparator;
  }
  
  function getCracked() {
    return $this->cracked;
  }
  
  function setCracked($cracked) {
    $this->cracked = $cracked;
  }
  
  function getIsSecret() {
    return $this->isSecret;
  }
  
  function setIsSecret($isSecret) {
    $this->isSecret = $isSecret;
  }
  
  function getHexSalt() {
    return $this->hexSalt;
  }
  
  function setHexSalt($hexSalt) {
    $this->hexSalt = $hexSalt;
  }
  
  function getIsSalted() {
    return $this->isSalted;
  }
  
  function setIsSalted($isSalted) {
    $this->isSalted = $isSalted;
  }
  
  function getAccessGroupId() {
    return $this->accessGroupId;
  }
  
  function setAccessGroupId($accessGroupId) {
    $this->accessGroupId = $accessGroupId;
  }
  
  function getNotes() {
    return $this->notes;
  }
  
  function setNotes($notes) {
    $this->notes = $notes;
  }
  
  function getBrainId() {
    return $this->brainId;
  }
  
  function setBrainId($brainId) {
    $this->brainId = $brainId;
  }
  
  function getBrainFeatures() {
    return $this->brainFeatures;
  }
  
  function setBrainFeatures($brainFeatures) {
    $this->brainFeatures = $brainFeatures;
  }
  
  const HASHLIST_ID = "hashlistId";
  const HASHLIST_NAME = "hashlistName";
  const FORMAT = "format";
  const HASH_TYPE_ID = "hashTypeId";
  const HASH_COUNT = "hashCount";
  const SALT_SEPARATOR = "saltSeparator";
  const CRACKED = "cracked";
  const IS_SECRET = "isSecret";
  const HEX_SALT = "hexSalt";
  const IS_SALTED = "isSalted";
  const ACCESS_GROUP_ID = "accessGroupId";
  const NOTES = "notes";
  const BRAIN_ID = "brainId";
  const BRAIN_FEATURES = "brainFeatures";
}
