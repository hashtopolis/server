<?php

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

class DPlatforms {
  const LINUX   = "linux";
  const WINDOWS = "win";
  const MAC_OSX = "osx";
  
  public static function getName($type) {
    switch ($type) {
      case DPlatforms::LINUX:
        return "Linux";
      case DPlatforms::MAC_OSX:
        return "Max OSX";
      case DPlatforms::WINDOWS:
        return "Windows";
      default:
        return "Unknown";
    }
  }
}