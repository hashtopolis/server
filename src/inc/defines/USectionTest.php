<?php

namespace Hashtopolis\inc\defines;

class USectionTest extends UApi {
  const CONNECTION = "connection";
  const ACCESS     = "access";
  
  public function describe($constant) {
    return match ($constant) {
      USectionTest::CONNECTION => "Connection testing",
      USectionTest::ACCESS => "Verifying the API key and test if user has access to the API",
      default => "__" . $constant . "__",
    };
  }
}