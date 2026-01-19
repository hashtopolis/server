<?php

use DBA\File;
use DBA\QueryFilter;
use DBA\Task;
use DBA\FileTask;
use DBA\JoinFilter;
use DBA\FilePretask;
use DBA\Pretask;
use DBA\OrderFilter;
use DBA\User;
use DBA\ContainFilter;
use DBA\FileDelete;
use DBA\Factory;

class FileUtils {
  /**
   * @param User $user
   * @param int[] $checkedFilesIds
   * @return array
   */
  public static function loadFilesByCategory($user, $checkedFilesIds) {
    $accessGroupIds = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user));
    $oF = new OrderFilter(File::FILENAME, "ASC");
    $qF = new ContainFilter(File::ACCESS_GROUP_ID, $accessGroupIds);
    $allFiles = Factory::getFileFactory()->filter([Factory::ORDER => $oF, Factory::FILTER => $qF]);
    $rules = [];
    $wordlists = [];
    $other = [];
    foreach ($allFiles as $singleFile) {
      $set = new DataSet();
      $checked = "0";
      if (in_array($singleFile->getId(), $checkedFilesIds)) {
        $checked = "1";
      }
      $set->addValue('checked', $checked);
      $set->addValue('file', $singleFile);
      if ($singleFile->getFileType() == DFileType::RULE) {
        $rules[] = $set;
      }
      else if ($singleFile->getFileType() == DFileType::WORDLIST) {
        $wordlists[] = $set;
      }
      else if ($singleFile->getFileType() == DFileType::OTHER) {
        $other[] = $set;
      }
    }
    return [$rules, $wordlists, $other];
  }
  
  /**
   * @param int $fileId
   * @param int $fileType
   * @param User $user
   * @throws HTException
   */
  public static function setFileType($fileId, $fileType, $user) {
    $file = FileUtils::getFile($fileId, $user);
    if ($fileType < DFileType::WORDLIST || $fileType > DFileType::OTHER) {
      throw new HTException("Invalid file type!");
    }
    Factory::getFileFactory()->set($file, File::FILE_TYPE, $fileType);
  }
  
  /**
   * @param User $user
   * @return File[]
   */
  public static function getFiles($user) {
    $accessGroupIds = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user));
    
    $oF = new OrderFilter(File::FILE_ID, "ASC");
    $qF1 = new ContainFilter(File::ACCESS_GROUP_ID, $accessGroupIds);
    $qF2 = new QueryFilter(File::FILE_TYPE, DFileType::TEMPORARY, "<>");
    return Factory::getFileFactory()->filter([Factory::ORDER => $oF, Factory::FILTER => [$qF1, $qF2]]);
  }
  
  /**
   * @param int $fileId
   * @param User $user
   * @throws HTException
   */
  public static function delete($fileId, $user) {
    $file = FileUtils::getFile($fileId, $user);
    
    $qF = new QueryFilter(FileTask::FILE_ID, $file->getId(), "=");
    $tasks = Factory::getFileTaskFactory()->filter([Factory::FILTER => $qF]);
    $qF = new QueryFilter(FilePretask::FILE_ID, $file->getId(), "=");
    $pretasks = Factory::getFilePretaskFactory()->filter([Factory::FILTER => $qF]);
    if (sizeof($tasks) > 0) {
      throw new HTException("This file is currently used in a task!");
    }
    else if (sizeof($pretasks) > 0) {
      throw new HTException("This file is currently used in a preconfigured task!");
    }
    
    FileDownloadUtils::removeFile($file->getId());
    $fileDelete = new FileDelete(null, $file->getFilename(), time());
    Factory::getFileDeleteFactory()->save($fileDelete);
    Factory::getFileFactory()->delete($file);
    unlink(Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . "/" . $file->getFilename());
  }
  
  /**
   * @param string $source
   * @param array $file
   * @param array $post
   * @param string $view
   * @return integer
   * @throws HTException
   */
  public static function add($source, $file, $post, $view) {
    $fileCount = 0;
    
    $accessGroup = Factory::getAccessGroupFactory()->get($post['accessGroupId']);
    if ($accessGroup == null) {
      throw new HttpError("Invalid access group selected!");
    }
    
    switch ($source) {
      case 'inline':
        $realname = str_replace(" ", "_", htmlentities(basename($post["filename"]), ENT_QUOTES, "UTF-8"));
        if ($realname == "") {
          throw new HttpError("Empty filename!");
        }
        $tmpfile = Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . "/" . $realname;
        $resp = Util::uploadFile($tmpfile, 'paste', $post['data']);
        if ($resp[0]) {
          $resp = Util::insertFile($tmpfile, $realname, $view, $accessGroup->getId());
          if ($resp) {
            $fileCount++;
          }
          else {
            throw new HttpError("Failed to insert file $realname into DB!");
          }
        }
        else {
          throw new HttpError("Failed to copy file $realname to the right place! " . $resp[1]);
        }
        break;
      case "upload":
        // from http upload
        $uploaded = $file["upfile"];
        $numFiles = count($file["upfile"]["name"]);
        for ($i = 0; $i < $numFiles; $i++) {
          // copy all uploaded attached files to proper directory
          $realname = str_replace(" ", "_", htmlentities(basename($uploaded["name"][$i]), ENT_QUOTES, "UTF-8"));
          if ($realname == "") {
            continue;
          }
          
          $toMove = array();
          foreach ($uploaded as $key => $upload) {
            $toMove[$key] = $upload[$i];
          }
          if ($realname[0] == '.') {
            $realname[0] = "_";
          }
          $tmpfile = Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . "/" . $realname;
          $resp = Util::uploadFile($tmpfile, $source, $toMove);
          if ($resp[0]) {
            $resp = Util::insertFile($tmpfile, $realname, $view, $accessGroup->getId());
            if ($resp) {
              $fileCount++;
            }
            else {
              throw new HttpError("Failed to insert file $realname into DB!");
            }
          }
          else {
            throw new HttpError("Failed to copy file $realname to the right place! " . $resp[1]);
          }
        }
        break;
      case "import":
        // from import dir
        $imports = $post["imfile"];
        if (!$imports) {
          break;
        }
        foreach ($imports as $import) {
          if ($import[0] == '.') {
            continue;
          }
          // copy all uploaded attached files to proper directory
          $realname = str_replace(" ", "_", htmlentities(basename($import), ENT_QUOTES, "UTF-8"));
          if ($realname[0] == '.') {
            $realname[0] = "_";
          }
          $tmpfile = Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . "/" . $realname;
          $resp = Util::uploadFile($tmpfile, $source, $import);
          if ($resp[0]) {
            $resp = Util::insertFile($tmpfile, $realname, $view, $accessGroup->getId());
            if ($resp) {
              $fileCount++;
            }
            else {
              throw new HttpError("Failed to insert file $realname into DB!");
            }
          }
          else {
            throw new HttpError("Failed to copy file $realname to the right place! " . $resp[1]);
          }
        }
        break;
      case "url":
        // from url
        $realname = (isset($post["filename"])) ? $post["filename"] :
                    str_replace(" ", "_", htmlentities(basename($post["url"]), ENT_QUOTES, "UTF-8"));
    
        if (strlen($realname) == 0) {
          throw new HttpError("Empty URL/name provided!");
        }
        else if ($realname[0] == '.') {
          $realname[0] = "_";
        }
        $tmpfile = Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . "/" . $realname;
        if (stripos($post["url"], "https://") !== 0 && stripos($post["url"], "http://") !== 0 && stripos($post["url"], "ftp://") !== 0) {
          throw new HttpError("Only downloads from http://, https:// and ftp:// are allowed!");
        }
        $resp = Util::uploadFile($tmpfile, $source, $post["url"]);
        if ($resp[0]) {
          $resp = Util::insertFile($tmpfile, $realname, $view, $accessGroup->getId());
          if ($resp) {
            $fileCount++;
          }
          else {
            throw new HttpError("Failed to insert file $realname into DB!");
          }
        }
        else {
          throw new HttpError("Failed to copy file $realname to the right place! " . $resp[1]);
        }
        break;
    }
    return $fileCount;
  }
  
  /**
   * @param int $fileId
   * @param int $isSecret
   * @param User $user
   * @throws HTException
   */
  public static function switchSecret($fileId, $isSecret, $user) {
    // switch global file secret state
    $file = FileUtils::getFile($fileId, $user);
    Factory::getFileFactory()->set($file, File::IS_SECRET, intval($isSecret));
  }
  
  /**
   * @param int $fileId
   * @param string $filename
   * @param int $accessGroupId
   * @param User $user
   * @throws HTException
   */
  public static function saveChanges($fileId, $filename, $accessGroupId, $user) {
    $file = FileUtils::getFile($fileId, $user);
    $newName = str_replace(" ", "_", htmlentities($filename, ENT_QUOTES, "UTF-8"));
    $newName = str_replace("/", "_", str_replace("\\", "_", $newName));
    if (strlen($newName) == 0) {
      throw new HTException("Filename cannot be empty!");
    }
    if ($newName[0] == '.') {
      $newName[0] = "_";
    }
    $qF1 = new QueryFilter(File::FILENAME, $newName, "=");
    $qF2 = new QueryFilter(File::FILE_ID, $file->getId(), "<>");
    $files = Factory::getFileFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
    if (sizeof($files) > 0) {
      throw new HTException("This filename is already used!");
    }
    
    if ($accessGroupId > 0) {
      $accessGroup = AccessGroupUtils::getGroup($accessGroupId);
      if ($accessGroup == null) {
        throw new HTException("Invalid access group Id!");
      }
      Factory::getFileFactory()->set($file, File::ACCESS_GROUP_ID, $accessGroup->getId());
    }
    
    if ($file->getFilename() == $newName) {
      return; // no name change was applied
    }
    
    Factory::getAgentFactory()->getDB()->beginTransaction();
    
    //check where the file is used and replace the filename in all the tasks
    $qF = new QueryFilter(FileTask::FILE_ID, $file->getId(), "=", Factory::getFileTaskFactory());
    $jF = new JoinFilter(Factory::getFileTaskFactory(), Task::TASK_ID, FileTask::TASK_ID);
    $joined = Factory::getTaskFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    foreach ($joined[Factory::getTaskFactory()->getModelName()] as $task) {
      /** @var $task Task */
      Factory::getTaskFactory()->set($task, Task::ATTACK_CMD, str_replace($file->getFilename(), $newName, $task->getAttackCmd()));
    }
    
    //check where the file is used and replace the filename in all the preconfigured tasks
    $qF = new QueryFilter(FilePretask::FILE_ID, $file->getId(), "=", Factory::getFilePretaskFactory());
    $jF = new JoinFilter(Factory::getFilePretaskFactory(), Pretask::PRETASK_ID, FilePretask::PRETASK_ID);
    $joined = Factory::getPretaskFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    foreach ($joined[Factory::getPretaskFactory()->getModelName()] as $pretask) {
      /** @var $pretask Pretask */
      Factory::getPretaskFactory()->set($pretask, Pretask::ATTACK_CMD, str_replace($file->getFilename(), $newName, $pretask->getAttackCmd()));
    }
    
    $success = rename(Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . "/" . $file->getFilename(), Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . "/" . $newName);
    if (!$success) {
      Factory::getAgentFactory()->getDB()->rollback();
      throw new HTException("Failed to rename file!");
    }
    
    // check if there are old deletion requests with the same name
    $qF = new QueryFilter(FileDelete::FILENAME, $newName, "=");
    Factory::getFileDeleteFactory()->massDeletion([Factory::FILTER => $qF]);
    
    Factory::getFileFactory()->set($file, File::FILENAME, $newName);
    Factory::getAgentFactory()->getDB()->commit();
  }
  
  /**
   * @param int $fileId
   * @param User $user
   * @return File
   * @throws HTException
   */
  public static function getFile($fileId, $user) {
    $accessGroups = AccessUtils::getAccessGroupsOfUser($user);
    $accessGroupIds = Util::arrayOfIds($accessGroups);
    
    $file = Factory::getFileFactory()->get($fileId);
    if ($file == null) {
      throw new HTException("Invalid file ID!");
    }
    else if (!in_array($file->getAccessGroupId(), $accessGroupIds)) {
      throw new HTException("No access to this file!");
    }
    return $file;
  }
  
  /**
   * @param $fileId
   * @throws HTException
   */
  public static function fileCountLines($fileId) {
    $file = Factory::getFileFactory()->get($fileId);
    $fileName = $file->getFilename();
    $filePath = Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . "/" . $fileName;
    if (!file_exists($filePath)) {
      throw new HTException("File not found!");
    }
    if ($file->getFileType() == DFileType::RULE) {
      $count = Util::rulefileLineCount($filePath);
    }
    else {
      $count = Util::fileLineCount($filePath);
    }
    
    if ($count == -1) {
      throw new HTException("Could not determine line count.");
    }
    else {
      Factory::getFileFactory()->set($file, File::LINE_COUNT, $count);
    }
  }
}
