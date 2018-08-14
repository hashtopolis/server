<?php

use DBA\File;
use DBA\QueryFilter;
use DBA\Task;
use DBA\FileTask;
use DBA\JoinFilter;
use DBA\FilePretask;
use DBA\Pretask;
use DBA\OrderFilter;

class FileUtils {
  /**
   * @param int $fileId
   * @param int $fileType
   * @throws HTException
   */
  public static function setFileType($fileId, $fileType) {
    global $FACTORIES;

    $file = FileUtils::getFile($fileId);
    if ($fileType < DFileType::WORDLIST || $fileType > DFileType::OTHER) {
      throw new HTException("Invalid file type!");
    }
    $file->setFileType($fileType);
    $FACTORIES::getFileFactory()->update($file);
  }

  /**
   * @return File[]
   */
  public static function getFiles() {
    global $FACTORIES;

    $oF = new OrderFilter(File::FILE_ID, "ASC");
    $qF = new QueryFilter(File::FILE_TYPE, DFileType::TEMPORARY, "<>");
    return $FACTORIES::getFileFactory()->filter(array($FACTORIES::ORDER => $oF, $FACTORIES::FILTER => $qF));
  }

  /**
   * @param int $fileId
   * @throws HTException
   */
  public static function delete($fileId) {
    global $FACTORIES;

    $file = FileUtils::getFile($fileId);

    $qF = new QueryFilter(FileTask::FILE_ID, $file->getId(), "=");
    $tasks = $FACTORIES::getFileTaskFactory()->filter(array($FACTORIES::FILTER => $qF));
    $qF = new QueryFilter(FilePretask::FILE_ID, $file->getId(), "=");
    $pretasks = $FACTORIES::getFilePretaskFactory()->filter(array($FACTORIES::FILTER => $qF));
    if (sizeof($tasks) > 0) {
      throw new HTException("This file is currently used in a task!");
    }
    else if (sizeof($pretasks) > 0) {
      throw new HTException("This file is currently used in a preconfigured task!");
    }
    FileDownloadUtils::removeFile($file->getId());
    $FACTORIES::getFileFactory()->delete($file);
    unlink(dirname(__FILE__) . "/../../files/" . $file->getFilename());
  }

  /**
   * @param string $source
   * @param array $file
   * @param array $post
   * @param string $view
   * @throws HTException
   * @return integer
   */
  public static function add($source, $file, $post, $view) {
    $fileCount = 0;
    if (!file_exists(dirname(__FILE__) . "/../../files")) {
      mkdir(dirname(__FILE__) . "/../../files");
    }

    switch ($source) {
      case 'inline':
        $realname = str_replace(" ", "_", htmlentities(basename($post["filename"]), ENT_QUOTES, "UTF-8"));
        if ($realname == "") {
          throw new HTException("Empty filename!");
        }
        $tmpfile = dirname(__FILE__) . "/../../files/" . $realname;
        $resp = Util::uploadFile($tmpfile, 'paste', $post['data']);
        if ($resp[0]) {
          $resp = Util::insertFile($tmpfile, $realname, $view);
          if ($resp) {
            $fileCount++;
          }
          else {
            throw new HTException("Failed to insert file $realname into DB!");
          }
        }
        else {
          throw new HTException("Failed to copy file $realname to the right place! " . $resp[1]);
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
          $tmpfile = dirname(__FILE__) . "/../../files/" . $realname;
          $resp = Util::uploadFile($tmpfile, $source, $toMove);
          if ($resp[0]) {
            $resp = Util::insertFile($tmpfile, $realname, $view);
            if ($resp) {
              $fileCount++;
            }
            else {
              throw new HTException("Failed to insert file $realname into DB!");
            }
          }
          else {
            throw new HTException("Failed to copy file $realname to the right place! " . $resp[1]);
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
          $tmpfile = dirname(__FILE__) . "/../../files/" . $realname;
          $resp = Util::uploadFile($tmpfile, $source, $import);
          if ($resp[0]) {
            $resp = Util::insertFile($tmpfile, $realname, $view);
            if ($resp) {
              $fileCount++;
            }
            else {
              throw new HTException("Failed to insert file $realname into DB!");
            }
          }
          else {
            throw new HTException("Failed to copy file $realname to the right place! " . $resp[1]);
          }
        }
        break;
      case "url":
        // from url
        $realname = str_replace(" ", "_", htmlentities(basename($post["url"]), ENT_QUOTES, "UTF-8"));
        if(strlen($realname) == 0){
          throw new HTException("Empty URL provided!");
        }
        else if ($realname[0] == '.') {
          $realname[0] = "_";
        }
        $tmpfile = dirname(__FILE__) . "/../../files/" . $realname;
        if (stripos($post["url"], "https://") !== 0 && stripos($post["url"], "http://") !== 0 && stripos($post["url"], "ftp://") !== 0) {
          throw new HTException("Only downloads from http://, https:// and ftp:// are allowed!");
        }
        $resp = Util::uploadFile($tmpfile, $source, $post["url"]);
        if ($resp[0]) {
          $resp = Util::insertFile($tmpfile, $realname, $view);
          if ($resp) {
            $fileCount++;
          }
          else {
            throw new HTException("Failed to insert file $realname into DB!");
          }
        }
        else {
          throw new HTException("Failed to copy file $realname to the right place! " . $resp[1]);
        }
        break;
    }
    return $fileCount;
  }

  /**
   * @param int $fileId
   * @param int $isSecret
   * @throws HTException
   */
  public static function switchSecret($fileId, $isSecret) {
    global $FACTORIES;

    // switch global file secret state
    $file = FileUtils::getFile($fileId);
    $secret = intval($isSecret);
    $file->setIsSecret($secret);
    $FACTORIES::getFileFactory()->update($file);
  }

  /**
   * @param int $fileId
   * @param string $filename
   * @throws HTException
   */
  public static function saveChanges($fileId, $filename) {
    global $FACTORIES;

    $file = FileUtils::getFile($fileId);
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
    $files = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)));
    if (sizeof($files) > 0) {
      throw new HTException("This filename is already used!");
    }

    if($file->getFilename() == $newName){
      return; // no name change was applied
    }

    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();

    //check where the file is used and replace the filename in all the tasks
    $qF = new QueryFilter(FileTask::FILE_ID, $file->getId(), "=", $FACTORIES::getFileTaskFactory());
    $jF = new JoinFilter($FACTORIES::getFileTaskFactory(), Task::TASK_ID, FileTask::TASK_ID);
    $joined = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    foreach ($joined[$FACTORIES::getTaskFactory()->getModelName()] as $task) {
      /** @var $task Task */
      $task->setAttackCmd(str_replace($file->getFilename(), $newName, $task->getAttackCmd()));
      $FACTORIES::getTaskFactory()->update($task);
    }

    //check where the file is used and replace the filename in all the preconfigured tasks
    $qF = new QueryFilter(FilePretask::FILE_ID, $file->getId(), "=", $FACTORIES::getFilePretaskFactory());
    $jF = new JoinFilter($FACTORIES::getFilePretaskFactory(), Pretask::PRETASK_ID, FilePretask::PRETASK_ID);
    $joined = $FACTORIES::getPretaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    foreach ($joined[$FACTORIES::getPretaskFactory()->getModelName()] as $pretask) {
      /** @var $pretask Pretask */
      $pretask->setAttackCmd(str_replace($file->getFilename(), $newName, $pretask->getAttackCmd()));
      $FACTORIES::getPretaskFactory()->update($pretask);
    }

    $success = rename(dirname(__FILE__) . "/../../files/" . $file->getFilename(), dirname(__FILE__) . "/../../files/" . $newName);
    if (!$success) {
      $FACTORIES::getAgentFactory()->getDB()->rollback();
      throw new HTException("Failed to rename file!");
    }
    $file->setFilename($newName);
    $FACTORIES::getFileFactory()->update($file);
    $FACTORIES::getAgentFactory()->getDB()->commit();
  }

  /**
   * @param int $fileId
   * @throws HTException
   * @return File
   */
  public static function getFile($fileId) {
    global $FACTORIES;

    $file = $FACTORIES::getFileFactory()->get($fileId);
    if ($file == null) {
      throw new HTException("Invalid file ID!");
    }
    return $file;
  }
}