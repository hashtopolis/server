<?php

namespace Hashtopolis\inc\defines;

class DNotificationAction {
  const CREATE_NOTIFICATION      = "createNotification";
  const CREATE_NOTIFICATION_PERM = DAccessControl::LOGIN_ACCESS;
  
  const SET_ACTIVE      = "setActive";
  const SET_ACTIVE_PERM = DAccessControl::LOGIN_ACCESS;
  
  const DELETE_NOTIFICATION      = "deleteNotification";
  const DELETE_NOTIFICATION_PERM = DAccessControl::LOGIN_ACCESS;
}
