<?php

namespace Hashtopolis\inc\defines;

class USectionSuperhashlist extends UApi {
  const LIST_SUPERHASHLISTS  = "listSuperhashlists";
  const GET_SUPERHASHLIST    = "getSuperhashlist";
  const CREATE_SUPERHASHLIST = "createSuperhashlist";
  const DELETE_SUPERHASHLIST = "deleteSuperhashlist";
  
  public function describe($constant) {
    return match ($constant) {
      USectionSuperhashlist::LIST_SUPERHASHLISTS => "List all superhashlists",
      USectionSuperhashlist::GET_SUPERHASHLIST => "Get details about a superhashlist",
      USectionSuperhashlist::CREATE_SUPERHASHLIST => "Create superhashlists",
      USectionSuperhashlist::DELETE_SUPERHASHLIST => "Delete superhashlists",
      default => "__" . $constant . "__",
    };
  }
}