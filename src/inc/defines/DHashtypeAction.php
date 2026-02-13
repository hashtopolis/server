<?php

namespace Hashtopolis\inc\defines;

class DHashtypeAction {
  const DELETE_HASHTYPE      = "deleteHashtype";
  const DELETE_HASHTYPE_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
  
  const ADD_HASHTYPE      = "addHashtype";
  const ADD_HASHTYPE_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
}