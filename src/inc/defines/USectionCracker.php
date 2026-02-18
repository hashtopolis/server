<?php

namespace Hashtopolis\inc\defines;

class USectionCracker extends UApi {
  const LIST_CRACKERS  = "listCrackers";
  const GET_CRACKER    = "getCracker";
  const DELETE_VERSION = "deleteVersion";
  const DELETE_CRACKER = "deleteCracker";
  
  const CREATE_CRACKER = "createCracker";
  const ADD_VERSION    = "addVersion";
  const UPDATE_VERSION = "updateVersion";
  
  public function describe($constant) {
    return match ($constant) {
      USectionCracker::LIST_CRACKERS => "List all crackers",
      USectionCracker::GET_CRACKER => "Get details of a cracker",
      USectionCracker::DELETE_VERSION => "Delete a specific version of a cracker",
      USectionCracker::DELETE_CRACKER => "Deleting crackers",
      USectionCracker::CREATE_CRACKER => "Create new crackers",
      USectionCracker::ADD_VERSION => "Add new cracker versions",
      USectionCracker::UPDATE_VERSION => "Update cracker versions",
      default => "__" . $constant . "__",
    };
  }
}