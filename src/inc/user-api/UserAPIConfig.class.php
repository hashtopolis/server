<?php

use DBA\Config;

class UserAPIConfig extends UserAPIBasic {
  public function execute($QUERY = array()) {
    try {
      switch ($QUERY[UQuery::REQUEST]) {
        case USectionConfig::LIST_SECTIONS:
          $this->listSections($QUERY);
          break;
        case USectionConfig::LIST_CONFIG:
          $this->listConfig($QUERY);
          break;
        case USectionConfig::GET_CONFIG:
          $this->getConfig($QUERY);
          break;
        case USectionConfig::SET_CONFIG:
          $this->setConfig($QUERY);
          break;
        default:
          $this->sendErrorResponse($QUERY[UQuery::SECTION], "INV", "Invalid section request!");
      }
    }
    catch (Exception $e) {
      $this->sendErrorResponse($QUERY[UQueryTask::SECTION], $QUERY[UQueryTask::REQUEST], $e->getMessage());
    }
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setConfig($QUERY) {
    if (!isset($QUERY[UQueryConfig::CONFIG_ITEM]) || !isset($QUERY[UQueryConfig::CONFIG_VALUE]) || !isset($QUERY[UQueryConfig::CONFIG_FORCE])) {
      throw new HTException("Invalid query!");
    }
    $value = SConfig::getInstance()->getVal($QUERY[UQueryConfig::CONFIG_ITEM]);
    $new = false;
    if ($value === false && $QUERY[UQueryConfig::CONFIG_FORCE] !== true) {
      throw new HTException("Unknown config item!");
    }
    else if ($QUERY[UQueryConfig::CONFIG_FORCE] === true) {
      try {
        $config = ConfigUtils::get($QUERY[UQueryConfig::CONFIG_ITEM]);
        $config->setValue($QUERY[UQueryConfig::CONFIG_VALUE]);
      }
      catch (Exception $e) {
        $config = new Config(null, 1, $QUERY[UQueryConfig::CONFIG_ITEM], $QUERY[UQueryConfig::CONFIG_VALUE]);
        $new = true;
      }
    }
    else {
      $config = ConfigUtils::get($QUERY[UQueryConfig::CONFIG_ITEM]);
      $config->setValue($QUERY[UQueryConfig::CONFIG_VALUE]);
    }
    $type = DConfig::getConfigType($config->getItem());
    switch ($type) {
      case DConfigType::EMAIL:
        if (!filter_var($config->getValue(), FILTER_VALIDATE_EMAIL)) {
          throw new HTException("Value must be email!");
        }
        break;
      case DConfigType::STRING_INPUT:
        break;
      case DConfigType::NUMBER_INPUT:
        if (!is_numeric($config->getValue())) {
          throw new HTException("Value must be numeric!");
        }
        break;
      case DConfigType::TICKBOX:
        if ($config->getValue() != '1' && $config->getValue()) {
          throw new HTException("Value most be boolean!");
        }
        # Workaround, inserting 'false' into text field will cause an empty field.
        if (!$config->getValue()) {
          $config->setValue('0');
        }
        else{
          $config->setValue('1');
        }
        break;
      case DConfigType::SELECT:
        if (!in_array($config->getValue(), DConfig::getSelection($config->getItem())->getKeys())) {
          throw new HTException("Value is not in selection!");
        }
        break;
    }
    ConfigUtils::set($config, $new);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function getConfig($QUERY) {
    if (!isset($QUERY[UQueryConfig::CONFIG_ITEM])) {
      throw new HTException("Invalid query!");
    }
    
    $value = SConfig::getInstance()->getVal($QUERY[UQueryConfig::CONFIG_ITEM]);
    if ($value === false) {
      throw new HTException("Unknown config item!");
    }
    $response = [
      UResponseConfig::SECTION => $QUERY[UQueryConfig::SECTION],
      UResponseConfig::REQUEST => $QUERY[UQueryConfig::REQUEST],
      UResponseConfig::RESPONSE => UValues::OK,
      UResponseConfig::CONFIG_ITEM => $QUERY[UQueryConfig::CONFIG_ITEM],
      UResponseConfig::CONFIG_TYPE => DConfig::getConfigType($QUERY[UQueryConfig::CONFIG_ITEM])
    ];
    switch (DConfig::getConfigType($QUERY[UQueryConfig::CONFIG_ITEM])) {
      case DConfigType::EMAIL:
      case DConfigType::STRING_INPUT:
        $response[UResponseConfig::CONFIG_VALUE] = $value;
        break;
      case DConfigType::NUMBER_INPUT:
        $response[UResponseConfig::CONFIG_VALUE] = (int)$value;
        break;
      case DConfigType::TICKBOX:
        $response[UResponseConfig::CONFIG_VALUE] = ($value == 1) ? true : false;
        break;
    }
    $this->sendResponse($response);
  }
  
  /**
   * @param mixed $QUERY
   */
  private function listSections($QUERY) {
    $sections = ConfigUtils::getSections();
    $response = [
      UResponseConfig::SECTION => $QUERY[UQueryConfig::SECTION],
      UResponseConfig::REQUEST => $QUERY[UQueryConfig::REQUEST],
      UResponseConfig::RESPONSE => UValues::OK
    ];
    $list = [];
    foreach ($sections as $section) {
      $list[] = [
        UResponseConfig::SECTIONS_ID => (int)$section->getId(),
        UResponseConfig::SECTIONS_NAME => $section->getSectionName()
      ];
    }
    $response[UResponseConfig::SECTIONS] = $list;
    $this->sendResponse($response);
  }
  
  /**
   * @param mixed $QUERY
   */
  private function listConfig($QUERY) {
    $configs = ConfigUtils::getAll();
    $response = [
      UResponseConfig::SECTION => $QUERY[UQueryConfig::SECTION],
      UResponseConfig::REQUEST => $QUERY[UQueryConfig::REQUEST],
      UResponseConfig::RESPONSE => UValues::OK
    ];
    $list = [];
    foreach ($configs as $config) {
      $list[] = [
        UResponseConfig::CONFIG_ITEM => $config->getItem(),
        UResponseConfig::CONFIG_SECTION_ID => $config->getConfigSectionId(),
        UResponseConfig::CONFIG_DESCRIPTION => DConfig::getConfigDescription($config->getItem())
      ];
    }
    $response[UResponseConfig::CONFIG] = $list;
    $this->sendResponse($response);
  }
}