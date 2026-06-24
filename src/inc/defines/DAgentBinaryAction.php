<?php

namespace Hashtopolis\inc\defines;

// TODO: this needs to be removed when we drop automatic download
define("HTP_AGENT_ARCHIVE", 'https://archive.hashtopolis.org/agent/');

class DAgentBinaryAction {
  const NEW_BINARY      = "newBinary";
  const NEW_BINARY_PERM = DAccessControl::CRACKER_BINARY_ACCESS;
  
  const EDIT_BINARY      = "editBinary";
  const EDIT_BINARY_PERM = DAccessControl::CRACKER_BINARY_ACCESS;
  
  const DELETE_BINARY      = "deleteBinary";
  const DELETE_BINARY_PERM = DAccessControl::CRACKER_BINARY_ACCESS;
  
  const UPGRADE_BINARY      = "upgradeBinary";
  const UPGRADE_BINARY_PERM = DAccessControl::CRACKER_BINARY_ACCESS;
  
  const CHECK_UPDATE      = "checkUpdate";
  const CHECK_UPDATE_PERM = DAccessControl::CRACKER_BINARY_ACCESS;
}
