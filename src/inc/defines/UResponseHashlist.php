<?php

namespace Hashtopolis\inc\defines;

class UResponseHashlist extends UResponse {
  const HASHLISTS             = "hashlists";
  const HASHLISTS_ID          = "hashlistId";
  const HASHLISTS_NAME        = "name";
  const HASHLISTS_HASHTYPE_ID = "hashtypeId";
  const HASHLISTS_FORMAT      = "format";
  const HASHLISTS_COUNT       = "hashCount";
  
  const HASHLIST_ID             = "hashlistId";
  const HASHLIST_NAME           = "name";
  const HASHLIST_HASHTYPE_ID    = "hashtypeId";
  const HASHLIST_FORMAT         = "format";
  const HASHLIST_COUNT          = "hashCount";
  const HASHLIST_CRACKED        = "cracked";
  const HASHLIST_ACCESS_GROUP   = "accessGroupId";
  const HASHLIST_HEX_SALT       = "isHexSalt";
  const HASHLIST_SALTED         = "isSalted";
  const HASHLIST_SECRET         = "isSecret";
  const HASHLIST_SALT_SEPARATOR = "saltSeparator";
  const HASHLIST_NOTES          = "hashlistNotes";
  const HASHLIST_BRAIN          = "useBrain";
  const HASHLIST_IS_ARCHIVED    = "isArchived";
  
  const ZAP_LINES_PROCESSED = "linesProcessed";
  const ZAP_NEW_CRACKED     = "newCracked";
  const ZAP_ALREADY_CRACKED = "alreadyCracked";
  const ZAP_INVALID         = "invalidLines";
  const ZAP_NOT_FOUND       = "notFound";
  const ZAP_TIME_REQUIRED   = "processTime";
  const ZAP_TOO_LONG        = "tooLongPlains";
  
  const EXPORT_FILE_ID   = "fileId";
  const EXPORT_FILE_NAME = "filename";
  
  const HASH     = "hash";
  const PLAIN    = "plain";
  const CRACKPOS = "crackpos";
  const CRACKED  = "cracked";
}