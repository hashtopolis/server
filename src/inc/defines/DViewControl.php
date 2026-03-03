<?php

namespace Hashtopolis\inc\defines;


class DViewControl {
  const ABOUT_VIEW_PERM          = DAccessControl::PUBLIC_ACCESS;
  const ACCESS_VIEW_PERM         = DAccessControl::USER_CONFIG_ACCESS;
  const ACCOUNT_VIEW_PERM        = DAccessControl::LOGIN_ACCESS;
  const AGENTS_VIEW_PERM         = DAccessControl::VIEW_AGENT_ACCESS;
  const BINARIES_VIEW_PERM       = DAccessControl::SERVER_CONFIG_ACCESS;
  const CHUNKS_VIEW_PERM         = DAccessControl::VIEW_TASK_ACCESS;
  const CONFIG_VIEW_PERM         = DAccessControl::SERVER_CONFIG_ACCESS;
  const CRACKERS_VIEW_PERM       = DAccessControl::CRACKER_BINARY_ACCESS;
  const FILES_VIEW_PERM          = DAccessControl::VIEW_FILE_ACCESS;
  const FORGOT_VIEW_PERM         = DAccessControl::PUBLIC_ACCESS;
  const GETFILE_VIEW_PERM        = DAccessControl::PUBLIC_ACCESS;
  const GETHASHLIST_VIEW_PERM    = DAccessControl::PUBLIC_ACCESS;
  const GROUPS_VIEW_PERM         = DAccessControl::MANAGE_ACCESS_GROUP_ACCESS;
  const HASHES_VIEW_PERM         = DAccessControl::VIEW_HASHES_ACCESS;
  const HASHLISTS_VIEW_PERM      = DAccessControl::VIEW_HASHLIST_ACCESS;
  const HASHTYPES_VIEW_PERM      = DAccessControl::SERVER_CONFIG_ACCESS;
  const HELP_VIEW_PERM           = DAccessControl::PUBLIC_ACCESS;
  const INDEX_VIEW_PERM          = DAccessControl::PUBLIC_ACCESS;
  const LOG_VIEW_PERM            = DAccessControl::PUBLIC_ACCESS;
  const LOGIN_VIEW_PERM          = DAccessControl::PUBLIC_ACCESS;
  const LOGOUT_VIEW_PERM         = DAccessControl::LOGIN_ACCESS;
  const NOTIFICATIONS_VIEW_PERM  = DAccessControl::LOGIN_ACCESS;
  const PRETASKS_VIEW_PERM       = DAccessControl::VIEW_PRETASK_ACCESS;
  const SEARCH_VIEW_PERM         = DAccessControl::VIEW_HASHES_ACCESS;
  const SUPERHASHLISTS_VIEW_PERM = DAccessControl::VIEW_HASHLIST_ACCESS;
  const SUPERTASKS_VIEW_PERM     = DAccessControl::VIEW_SUPERTASK_ACCESS;
  const TASKS_VIEW_PERM          = DAccessControl::VIEW_TASK_ACCESS;
  const USERS_VIEW_PERM          = DAccessControl::USER_CONFIG_ACCESS;
  const API_VIEW_PERM            = DAccessControl::USER_CONFIG_ACCESS;
  const HEALTH_VIEW_PERM         = DAccessControl::SERVER_CONFIG_ACCESS;
  const PREPROCESSORS_VIEW_PERM  = DAccessControl::SERVER_CONFIG_ACCESS;
}