<?php

namespace Hashtopolis\inc\defines;

class DPlatforms {
  const LINUX   = "linux";
  const WINDOWS = "win";
  const MAC_OSX = "osx";
  
  public static function getName($type) {
    return match ($type) {
      DPlatforms::LINUX => "Linux",
      DPlatforms::MAC_OSX => "Max OSX",
      DPlatforms::WINDOWS => "Windows",
      default => "Unknown",
    };
  }
}