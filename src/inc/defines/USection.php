<?php

namespace Hashtopolis\inc\defines;

class USection extends UApi {
  const TEST          = "test";
  const AGENT         = "agent";
  const TASK          = "task";
  const PRETASK       = "pretask";
  const SUPERTASK     = "supertask";
  const HASHLIST      = "hashlist";
  const SUPERHASHLIST = "superhashlist";
  const FILE          = "file";
  const CRACKER       = "cracker";
  const CONFIG        = "config";
  const USER          = "user";
  const GROUP         = "group";
  const ACCESS        = "access";
  const ACCOUNT       = "account";
  
  public function describe($section) {
    // placeholder
    return $section;
  }
}