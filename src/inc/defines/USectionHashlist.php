<?php

namespace Hashtopolis\inc\defines;

class USectionHashlist extends UApi {
  const LIST_HASLISTS     = "listHashlists";
  const GET_HASHLIST      = "getHashlist";
  const CREATE_HASHLIST   = "createHashlist";
  const SET_HASHLIST_NAME = "setHashlistName";
  const SET_SECRET        = "setSecret";
  const SET_ARCHIVED      = "setArchived";
  
  const IMPORT_CRACKED    = "importCracked";
  const EXPORT_CRACKED    = "exportCracked";
  const GENERATE_WORDLIST = "generateWordlist";
  const EXPORT_LEFT       = "exportLeft";
  
  const DELETE_HASHLIST = "deleteHashlist";
  const GET_HASH        = "getHash";
  const GET_CRACKED     = "getCracked";
  
  public function describe($constant) {
    return match ($constant) {
      USectionHashlist::LIST_HASLISTS => "List all hashlists",
      USectionHashlist::GET_HASHLIST => "Get details of a hashlist",
      USectionHashlist::CREATE_HASHLIST => "Create a new hashlist",
      USectionHashlist::SET_HASHLIST_NAME => "Rename hashlists",
      USectionHashlist::SET_SECRET => "Set if a hashlist is secret or not",
      USectionHashlist::IMPORT_CRACKED => "Import cracked hashes",
      USectionHashlist::EXPORT_CRACKED => "Export cracked hashes",
      USectionHashlist::GENERATE_WORDLIST => "Generate wordlist from founds",
      USectionHashlist::EXPORT_LEFT => "Export a left list of uncracked hashes",
      USectionHashlist::DELETE_HASHLIST => "Delete hashlists",
      USectionHashlist::GET_HASH => "Query for specific hashes",
      USectionHashlist::GET_CRACKED => "Query cracked hashes of a hashlist",
      USectionHashlist::SET_ARCHIVED => "Query to archive/un-archie hashlist",
      default => "__" . $constant . "__",
    };
  }
}