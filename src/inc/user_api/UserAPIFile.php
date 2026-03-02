<?php

namespace Hashtopolis\inc\user_api;

use Exception;
use Hashtopolis\inc\utils\FileUtils;
use Hashtopolis\inc\defines\DFileType;
use Hashtopolis\inc\defines\UQuery;
use Hashtopolis\inc\defines\UQueryFile;
use Hashtopolis\inc\defines\UQueryTask;
use Hashtopolis\inc\defines\UResponseFile;
use Hashtopolis\inc\defines\USectionFile;
use Hashtopolis\inc\defines\UValues;
use Hashtopolis\inc\HTException;

class UserAPIFile extends UserAPIBasic {
  public function execute($QUERY = array()) {
    try {
      switch ($QUERY[UQuery::REQUEST]) {
        case USectionFile::LIST_FILES:
          $this->listFiles($QUERY);
          break;
        case USectionFile::GET_FILE:
          $this->getFile($QUERY);
          break;
        case USectionFile::ADD_FILE:
          $this->addFile($QUERY);
          break;
        case USectionFile::RENAME_FILE:
          $this->renameFile($QUERY);
          break;
        case USectionFile::SET_SECRET:
          $this->setSecret($QUERY);
          break;
        case USectionFile::DELETE_FILE:
          $this->deleteFile($QUERY);
          break;
        case USectionFile::SET_FILE_TYPE:
          $this->setFileType($QUERY);
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
  private function setFileType($QUERY) {
    if (!isset($QUERY[UQueryFile::FILE_ID]) || !isset($QUERY[UQueryFile::FILE_TYPE])) {
      throw new HTException("Invalid query!");
    }
    FileUtils::setFileType($QUERY[UQueryFile::FILE_ID], $QUERY[UQueryFile::FILE_TYPE], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function deleteFile($QUERY) {
    if (!isset($QUERY[UQueryFile::FILE_ID])) {
      throw new HTException("Invalid query!");
    }
    FileUtils::delete($QUERY[UQueryFile::FILE_ID], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setSecret($QUERY) {
    if (!isset($QUERY[UQueryFile::FILE_ID]) || !isset($QUERY[UQueryFile::SET_SECRET])) {
      throw new HTException("Invalid query!");
    }
    FileUtils::switchSecret($QUERY[UQueryFile::FILE_ID], ($QUERY[UQueryFile::SET_SECRET]) ? 1 : 0, $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function renameFile($QUERY) {
    if (!isset($QUERY[UQueryFile::FILE_ID]) || !isset($QUERY[UQueryFile::FILENAME])) {
      throw new HTException("Invalid query!");
    }
    FileUtils::saveChanges($QUERY[UQueryFile::FILE_ID], $QUERY[UQueryFile::FILENAME], 0, $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function addFile($QUERY) {
    $toCheck = [
      UQueryFile::FILENAME,
      UQueryFile::FILE_TYPE,
      UQueryFile::SOURCE,
      UQueryFile::DATA
    ];
    foreach ($toCheck as $input) {
      if (!isset($QUERY[$input])) {
        throw new HTException("Invalid query!");
      }
    }
    switch ($QUERY[UQueryFile::FILE_TYPE]) {
      case DFileType::WORDLIST:
        $type = 'dict';
        break;
      case DFileType::RULE:
        $type = 'rule';
        break;
      case DFileType::OTHER:
        $type = 'other';
        break;
      default:
        throw new HTException("Invalid file type!");
    }
    switch ($QUERY[UQueryFile::SOURCE]) {
      case 'url':
        FileUtils::add('url', [], ['url' => $QUERY[UQueryFile::DATA], 'accessGroupId' => $QUERY[UQueryFile::ACCESS_GROUP_ID]], $type);
        break;
      case 'import':
        FileUtils::add('import', [], ['imfile' => [$QUERY[UQueryFile::DATA]], 'accessGroupId' => $QUERY[UQueryFile::ACCESS_GROUP_ID]], $type);
        break;
      case 'inline':
        FileUtils::add('inline', [], ['filename' => $QUERY[UQueryFile::FILENAME], 'data' => base64_decode($QUERY[UQueryFile::DATA]), 'accessGroupId' => $QUERY[UQueryFile::ACCESS_GROUP_ID]], $type);
        break;
      default:
        throw new HTException("Invalid source!");
    }
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function getFile($QUERY) {
    if (!isset($QUERY[UQueryFile::FILE_ID])) {
      throw new HTException("Invalid query!");
    }
    $file = FileUtils::getFile($QUERY[UQueryFile::FILE_ID], $this->user);
    $response = [
      UResponseFile::SECTION => $QUERY[UQueryFile::SECTION],
      UResponseFile::REQUEST => $QUERY[UQueryFile::REQUEST],
      UResponseFile::RESPONSE => UValues::OK,
      UResponseFile::FILE_ID => (int)$file->getId(),
      UResponseFile::FILE_TYPE => (int)$file->getFileType(),
      UResponseFile::FILE_FILENAME => $file->getFilename(),
      UResponseFile::FILE_SECRET => ($file->getIsSecret() == 1) ? true : false,
      UResponseFile::FILE_SIZE => (int)$file->getSize(),
      UResponseFile::FILE_URL => "getFile.php?file=" . $file->getId() . "&apiKey=" . $this->apiKey->getAccessKey()
    ];
    $this->sendResponse($response);
  }
  
  /**
   * @param array $QUERY
   */
  private function listFiles($QUERY) {
    $files = FileUtils::getFiles($this->user);
    $all = [];
    $response = [
      UResponseFile::SECTION => $QUERY[UQueryFile::SECTION],
      UResponseFile::REQUEST => $QUERY[UQueryFile::REQUEST],
      UResponseFile::RESPONSE => UValues::OK
    ];
    foreach ($files as $file) {
      $all[] = [
        UResponseFile::FILES_FILE_ID => (int)$file->getId(),
        UResponseFile::FILES_FILETYPE => (int)$file->getFileType(),
        UResponseFile::FILES_FILENAME => $file->getFilename()
      ];
    }
    $response[UResponseFile::FILES] = $all;
    $this->sendResponse($response);
  }
}