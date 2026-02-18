<?php

namespace Hashtopolis\inc\defines;

class DConfigAction {
  const UPDATE_CONFIG      = "updateConfig";
  const UPDATE_CONFIG_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
  
  const REBUILD_CACHE      = "rebuildCache";
  const REBUILD_CACHE_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
  
  const RESCAN_FILES      = "rescanFiles";
  const RESCAN_FILES_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
  
  const CLEAR_ALL      = "clearAll";
  const CLEAR_ALL_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
}