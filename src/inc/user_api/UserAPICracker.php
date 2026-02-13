<?php

namespace Hashtopolis\inc\user_api;

use Hashtopolis\inc\utils\CrackerUtils;
use Exception;
use Hashtopolis\inc\defines\UQuery;
use Hashtopolis\inc\defines\UQueryCracker;
use Hashtopolis\inc\defines\UQueryTask;
use Hashtopolis\inc\defines\UResponseCracker;
use Hashtopolis\inc\defines\USectionCracker;
use Hashtopolis\inc\defines\UValues;
use Hashtopolis\inc\HTException;

class UserAPICracker extends UserAPIBasic {
  public function execute($QUERY = array()) {
    try {
      switch ($QUERY[UQuery::REQUEST]) {
        case USectionCracker::LIST_CRACKERS:
          $this->listCrackers($QUERY);
          break;
        case USectionCracker::GET_CRACKER:
          $this->getCracker($QUERY);
          break;
        case USectionCracker::DELETE_CRACKER:
          $this->deleteCracker($QUERY);
          break;
        case USectionCracker::DELETE_VERSION:
          $this->deleteVersion($QUERY);
          break;
        case USectionCracker::CREATE_CRACKER:
          $this->createCracker($QUERY);
          break;
        case USectionCracker::ADD_VERSION:
          $this->addVersion($QUERY);
          break;
        case USectionCracker::UPDATE_VERSION:
          $this->updateVersion($QUERY);
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
  private function updateVersion($QUERY) {
    if (!isset($QUERY[UQueryCracker::CRACKER_VERSION_ID]) || !isset($QUERY[UQueryCracker::BINARY_VERSION]) || !isset($QUERY[UQueryCracker::BINARY_NAME]) || !isset($QUERY[UQueryCracker::BINARY_URL])) {
      throw new HTException("Invalid query!");
    }
    $binary = CrackerUtils::getBinary($QUERY[UQueryCracker::CRACKER_VERSION_ID]);
    CrackerUtils::updateBinary($QUERY[UQueryCracker::BINARY_VERSION], $QUERY[UQueryCracker::BINARY_NAME], $QUERY[UQueryCracker::BINARY_URL], $binary->getId());
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function addVersion($QUERY) {
    if (!isset($QUERY[UQueryCracker::CRACKER_ID]) || !isset($QUERY[UQueryCracker::BINARY_VERSION]) || !isset($QUERY[UQueryCracker::BINARY_NAME]) || !isset($QUERY[UQueryCracker::BINARY_URL])) {
      throw new HTException("Invalid query!");
    }
    $cracker = CrackerUtils::getBinaryType($QUERY[UQueryCracker::CRACKER_ID]);
    CrackerUtils::createBinary($QUERY[UQueryCracker::BINARY_VERSION], $QUERY[UQueryCracker::BINARY_NAME], $QUERY[UQueryCracker::BINARY_URL], $cracker->getId());
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function createCracker($QUERY) {
    if (!isset($QUERY[UQueryCracker::CRACKER_NAME])) {
      throw new HTException("Invalid query!");
    }
    CrackerUtils::createBinaryType($QUERY[UQueryCracker::CRACKER_NAME]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function deleteVersion($QUERY) {
    if (!isset($QUERY[UQueryCracker::CRACKER_VERSION_ID])) {
      throw new HTException("Invalid query!");
    }
    CrackerUtils::deleteBinary($QUERY[UQueryCracker::CRACKER_VERSION_ID]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function deleteCracker($QUERY) {
    if (!isset($QUERY[UQueryCracker::CRACKER_ID])) {
      throw new HTException("Invalid query!");
    }
    CrackerUtils::deleteBinaryType($QUERY[UQueryCracker::CRACKER_ID]);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function getCracker($QUERY) {
    if (!isset($QUERY[UQueryCracker::CRACKER_ID])) {
      throw new HTException("Invalid query!");
    }
    $cracker = CrackerUtils::getBinaryType($QUERY[UQueryCracker::CRACKER_ID]);
    $versions = CrackerUtils::getBinaries($cracker);
    $list = [];
    $response = [
      UResponseCracker::SECTION => $QUERY[UQueryCracker::SECTION],
      UResponseCracker::REQUEST => $QUERY[UQueryCracker::REQUEST],
      UResponseCracker::RESPONSE => UValues::OK,
      UResponseCracker::CRACKER_ID => (int)$cracker->getId(),
      UResponseCracker::CRACKER_NAME => $cracker->getTypeName()
    ];
    foreach ($versions as $version) {
      $list[] = [
        UResponseCracker::VERSIONS_ID => (int)$version->getId(),
        UResponseCracker::VERSIONS_VERSION => $version->getVersion(),
        UResponseCracker::VERSIONS_URL => $version->getDownloadUrl(),
        UResponseCracker::VERSIONS_BINARY_NAME => $version->getBinaryName()
      ];
    }
    $response[UResponseCracker::VERSIONS] = $list;
    $this->sendResponse($response);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function listCrackers($QUERY) {
    $crackers = CrackerUtils::getBinaryTypes();
    $list = [];
    $response = [
      UResponseCracker::SECTION => $QUERY[UQueryCracker::SECTION],
      UResponseCracker::REQUEST => $QUERY[UQueryCracker::REQUEST],
      UResponseCracker::RESPONSE => UValues::OK
    ];
    foreach ($crackers as $cracker) {
      $list[] = [
        UResponseCracker::CRACKERS_ID => (int)$cracker->getId(),
        UResponseCracker::CRACKERS_NAME => $cracker->getTypeName()
      ];
    }
    $response[UResponseCracker::CRACKERS] = $list;
    $this->sendResponse($response);
  }
}