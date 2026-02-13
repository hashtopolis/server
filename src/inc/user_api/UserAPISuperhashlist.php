<?php

namespace Hashtopolis\inc\user_api;

use Hashtopolis\inc\utils\AccessUtils;
use Exception;
use Hashtopolis\inc\utils\HashlistUtils;
use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\inc\defines\UQuery;
use Hashtopolis\inc\defines\UQueryHashlist;
use Hashtopolis\inc\defines\UQuerySuperhashlist;
use Hashtopolis\inc\defines\UQueryTask;
use Hashtopolis\inc\defines\UResponseSuperhashlist;
use Hashtopolis\inc\defines\USectionSuperhashlist;
use Hashtopolis\inc\defines\UValues;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\Util;

class UserAPISuperhashlist extends UserAPIBasic {
  public function execute($QUERY = array()) {
    try {
      switch ($QUERY[UQuery::REQUEST]) {
        case USectionSuperhashlist::LIST_SUPERHASHLISTS:
          $this->listSuperhashlists($QUERY);
          break;
        case USectionSuperhashlist::GET_SUPERHASHLIST:
          $this->getSuperhashlist($QUERY);
          break;
        case USectionSuperhashlist::CREATE_SUPERHASHLIST:
          $this->createSuperhashlist($QUERY);
          break;
        case USectionSuperhashlist::DELETE_SUPERHASHLIST:
          $this->deleteSuperhashlist($QUERY);
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
  private function deleteSuperhashlist($QUERY) {
    if (!isset($QUERY[UQuerySuperhashlist::SUPERHASHLIST_ID])) {
      throw new HTException("Invalid query!");
    }
    $hashlist = HashlistUtils::getHashlist($QUERY[UQuerySuperhashlist::SUPERHASHLIST_ID]);
    if ($hashlist->getFormat() != DHashlistFormat::SUPERHASHLIST) {
      throw new HTException("This is not a superhashlist!");
    }
    HashlistUtils::delete($hashlist->getId(), $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function createSuperhashlist($QUERY) {
    if (!isset($QUERY[UQuerySuperhashlist::SUPERHASHLIST_NAME]) || !isset($QUERY[UQuerySuperhashlist::SUPERHASHLIST_HASHLISTS])) {
      throw new HTException("Invalid query!");
    }
    HashlistUtils::createSuperhashlist($QUERY[UQuerySuperhashlist::SUPERHASHLIST_HASHLISTS], $QUERY[UQuerySuperhashlist::SUPERHASHLIST_NAME], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function getSuperhashlist($QUERY) {
    if (!isset($QUERY[UQuerySuperhashlist::SUPERHASHLIST_ID])) {
      throw new HTException("Invalid query!");
    }
    $hashlist = HashlistUtils::getHashlist($QUERY[UQuerySuperhashlist::SUPERHASHLIST_ID]);
    if ($hashlist->getFormat() != DHashlistFormat::SUPERHASHLIST) {
      throw new HTException("This is not a superhashlist!");
    }
    else if (!AccessUtils::userCanAccessHashlists($hashlist, $this->user)) {
      throw new HTException("No access to this hashlist!");
    }
    $hashlists = Util::arrayOfIds(Util::checkSuperHashlist($hashlist));
    $hashlistIds = [];
    foreach ($hashlists as $l) {
      $hashlistIds[] = (int)$l;
    }
    $response = [
      UResponseSuperhashlist::SECTION => $QUERY[UQueryHashlist::SECTION],
      UResponseSuperhashlist::REQUEST => $QUERY[UQueryHashlist::REQUEST],
      UResponseSuperhashlist::RESPONSE => UValues::OK,
      UResponseSuperhashlist::SUPERHASHLIST_ID => (int)$hashlist->getId(),
      UResponseSuperhashlist::SUPERHASHLIST_HASHTYPE_ID => (int)$hashlist->getHashTypeId(),
      UResponseSuperhashlist::SUPERHASHLIST_NAME => $hashlist->getHashlistName(),
      UResponseSuperhashlist::SUPERHASHLIST_COUNT => (int)$hashlist->getHashCount(),
      UResponseSuperhashlist::SUPERHASHLIST_CRACKED => (int)$hashlist->getCracked(),
      UResponseSuperhashlist::SUPERHASHLIST_ACCESS_GROUP => (int)$hashlist->getAccessGroupId(),
      UResponseSuperhashlist::SUPERHASHLIST_SECRET => ($hashlist->getIsSecret() == 1) ? true : false,
      UResponseSuperhashlist::SUPERHASHLIST_HASHLISTS => $hashlistIds
    ];
    $this->sendResponse($response);
  }
  
  /**
   * @param array $QUERY
   */
  private function listSuperhashlists($QUERY) {
    $hashlists = HashlistUtils::getSuperhashlists($this->user);
    $lists = [];
    $response = [
      UResponseSuperhashlist::SECTION => $QUERY[UQuerySuperhashlist::SECTION],
      UResponseSuperhashlist::REQUEST => $QUERY[UQuerySuperhashlist::REQUEST],
      UResponseSuperhashlist::RESPONSE => UValues::OK
    ];
    foreach ($hashlists as $hashlist) {
      $lists[] = [
        UResponseSuperhashlist::SUPERHASHLISTS_ID => (int)$hashlist->getId(),
        UResponseSuperhashlist::SUPERHASHLISTS_HASHTYPE_ID => (int)$hashlist->getHashTypeId(),
        UResponseSuperhashlist::SUPERHASHLISTS_NAME => $hashlist->getHashlistName(),
        UResponseSuperhashlist::SUPERHASHLISTS_COUNT => (int)$hashlist->getHashCount()
      ];
    }
    $response[UResponseSuperhashlist::SUPERHASHLISTS] = $lists;
    $this->sendResponse($response);
  }
}