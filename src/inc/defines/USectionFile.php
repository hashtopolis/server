<?php

namespace Hashtopolis\inc\defines;

class USectionFile extends UApi {
  const LIST_FILES = "listFiles";
  const GET_FILE   = "getFile";
  const ADD_FILE   = "addFile";
  
  const RENAME_FILE   = "renameFile";
  const SET_SECRET    = "setSecret";
  const DELETE_FILE   = "deleteFile";
  const SET_FILE_TYPE = "setFileType";
  
  public function describe($constant) {
    return match ($constant) {
      USectionFile::LIST_FILES => "List all files",
      USectionFile::GET_FILE => "Get details of a file",
      USectionFile::ADD_FILE => "Add new files",
      USectionFile::RENAME_FILE => "Rename files",
      USectionFile::SET_SECRET => "Set if a file is secret or not",
      USectionFile::DELETE_FILE => "Delete files",
      USectionFile::SET_FILE_TYPE => "Change type of files",
      default => "__" . $constant . "__",
    };
  }
}