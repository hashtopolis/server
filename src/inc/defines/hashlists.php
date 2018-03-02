<?php

class DHashlistAction {
  const APPLY_PRECONFIGURED_TASKS = "applyPreconfiguredTasks";
  const CREATE_WORDLIST           = "createWordlist";
  const SET_SECRET                = "setSecret";
  const RENAME_HASHLIST           = "renameHashlist";
  const PROCESS_ZAP               = "processZap";
  const EXPORT_HASHLIST           = "exportHashlist";
  const ZAP_HASHLIST              = "zapHashlist";
  const DELETE_HASHLIST           = "deleteHashlist";
  const CREATE_HASHLIST           = "createHashlist";
  const CREATE_SUPERHASHLIST      = "createSuperhashlist";
  const CREATE_LEFTLIST           = "createLeftlist";
}

class DHashtypeAction {
  const DELETE_HASHTYPE = "deleteHashtype";
  const ADD_HASHTYPE    = "addHashtype";
}

// hashlist formats
class DHashlistFormat {
  const PLAIN         = 0;
  const WPA           = 1;
  const BINARY        = 2;
  const SUPERHASHLIST = 3;
}