<?php

namespace Hashtopolis\inc\defines;

class DCrackerBinaryAction {
  const DELETE_BINARY_TYPE      = "deleteBinaryType";
  const DELETE_BINARY_TYPE_PERM = DAccessControl::CRACKER_BINARY_ACCESS;
  
  const DELETE_BINARY      = "deleteBinary";
  const DELETE_BINARY_PERM = DAccessControl::CRACKER_BINARY_ACCESS;
  
  const CREATE_BINARY_TYPE      = "createBinaryType";
  const CREATE_BINARY_TYPE_PERM = DAccessControl::CRACKER_BINARY_ACCESS;
  
  const CREATE_BINARY      = "createBinary";
  const CREATE_BINARY_PERM = DAccessControl::CRACKER_BINARY_ACCESS;
  
  const EDIT_BINARY      = "editBinary";
  const EDIT_BINARY_PERM = DAccessControl::CRACKER_BINARY_ACCESS;
}
