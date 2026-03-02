<?php

namespace Hashtopolis\inc\defines;

class USectionConfig extends UApi {
  const LIST_SECTIONS = "listSections";
  const LIST_CONFIG   = "listConfig";
  const GET_CONFIG    = "getConfig";
  const SET_CONFIG    = "setConfig";
  
  public function describe($constant) {
    return match ($constant) {
      USectionConfig::LIST_SECTIONS => "List available sections in config",
      USectionConfig::LIST_CONFIG => "List config options of a given section",
      USectionConfig::GET_CONFIG => "Get current value of a config",
      USectionConfig::SET_CONFIG => "Change values of configs",
      default => "__" . $constant . "__",
    };
  }
}