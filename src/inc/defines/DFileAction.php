<?php

namespace Hashtopolis\inc\defines;

class DFileAction {
  const DELETE_FILE      = "deleteFile";
  const DELETE_FILE_PERM = DAccessControl::ADD_FILE_ACCESS;
  
  const SET_SECRET      = "setSecret";
  const SET_SECRET_PERM = DAccessControl::MANAGE_FILE_ACCESS;
  
  const ADD_FILE      = "addFile";
  const ADD_FILE_PERM = DAccessControl::ADD_FILE_ACCESS;
  
  const EDIT_FILE      = "editFile";
  const EDIT_FILE_PERM = DAccessControl::MANAGE_FILE_ACCESS;
  
  const COUNT_FILE_LINES      = "countFileLines";
  const COUNT_FILE_LINES_PERM = DAccessControl::MANAGE_FILE_ACCESS;
}