<?php

namespace Hashtopolis\inc\defines;

class DSupertaskAction {
  const DELETE_SUPERTASK      = "deleteSupertask";
  const DELETE_SUPERTASK_PERM = DAccessControl::CREATE_SUPERTASK_ACCESS;
  
  const CREATE_SUPERTASK      = "createSupertask";
  const CREATE_SUPERTASK_PERM = DAccessControl::CREATE_SUPERTASK_ACCESS;
  
  const APPLY_SUPERTASK      = "applySupertask";
  const APPLY_SUPERTASK_PERM = DAccessControl::RUN_TASK_ACCESS;
  
  const IMPORT_SUPERTASK      = "importSupertask";
  const IMPORT_SUPERTASK_PERM = DAccessControl::CREATE_SUPERTASK_ACCESS;
  
  const BULK_SUPERTASK      = "bulkSupertaskCreation";
  const BULK_SUPERTASK_PERM = DAccessControl::CREATE_SUPERTASK_ACCESS;
  
  const REMOVE_PRETASK_FROM_SUPERTASK      = "removePretaskFromSupertask";
  const REMOVE_PRETASK_FROM_SUPERTASK_PERM = DAccessControl::CREATE_SUPERTASK_ACCESS;
  
  const ADD_PRETASK_TO_SUPERTASK      = "addPretaskToSupertask";
  const ADD_PRETASK_TO_SUPERTASK_PERM = DAccessControl::CREATE_SUPERTASK_ACCESS;
}