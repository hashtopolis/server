<?php

namespace Hashtopolis\inc\defines;

class DPreprocessorAction {
  const DELETE_PREPROCESSOR      = "deletePreprocessor";
  const DELETE_PREPROCESSOR_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
  
  const ADD_PREPROCESSOR      = "addPreprocessor";
  const ADD_PREPROCESSOR_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
  
  const EDIT_PREPROCESSOR      = "editPreprocessor";
  const EDIT_PREPROCESSOR_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
}


