<?php

namespace Hashtopolis\inc\defines;

class USectionSupertask extends UApi {
  const LIST_SUPERTASKS    = "listSupertasks";
  const GET_SUPERTASK      = "getSupertask";
  const CREATE_SUPERTASK   = "createSupertask";
  const IMPORT_SUPERTASK   = "importSupertask";
  const SET_SUPERTASK_NAME = "setSupertaskName";
  const DELETE_SUPERTASK   = "deleteSupertask";
  const BULK_SUPERTASK     = "bulkSupertask";
  
  public function describe($constant) {
    return match ($constant) {
      USectionSupertask::LIST_SUPERTASKS => "List all supertasks",
      USectionSupertask::GET_SUPERTASK => "Get details of a supertask",
      USectionSupertask::CREATE_SUPERTASK => "Create a supertask",
      USectionSupertask::IMPORT_SUPERTASK => "Import a supertask from masks",
      USectionSupertask::SET_SUPERTASK_NAME => "Rename a configured supertask",
      USectionSupertask::DELETE_SUPERTASK => "Delete a supertask",
      USectionSupertask::BULK_SUPERTASK => "Create supertask out base command with files",
      default => "__" . $constant . "__",
    };
  }
}