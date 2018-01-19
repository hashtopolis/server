<?php

class DCrackerBinaryAction {
  const DELETE_BINARY_TYPE = "deleteBinaryType";
  const DELETE_BINARY      = "deleteBinary";
  const CREATE_BINARY_TYPE = "createBinaryType";
  const CREATE_BINARY      = "createBinary";
  const EDIT_BINARY        = "editBinary";
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