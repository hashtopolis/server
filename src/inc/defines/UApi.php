<?php

namespace Hashtopolis\inc\defines;

use ReflectionClass;
use ReflectionException;

abstract class UApi {
  abstract function describe($constant);
  
  static function getConstants() {
    try {
      $oClass = new ReflectionClass(static::class);
    }
    catch (ReflectionException $e) {
      die("Exception: " . $e->getMessage());
    }
    return $oClass->getConstants();
  }
  
  static function getSection($section) {
    return match ($section) {
      USection::TEST => new USectionTest(),
      USection::AGENT => new USectionAgent(),
      USection::TASK => new USectionTask(),
      USection::PRETASK => new USectionPretask(),
      USection::SUPERTASK => new USectionSupertask(),
      USection::HASHLIST => new USectionHashlist(),
      USection::SUPERHASHLIST => new USectionSuperhashlist(),
      USection::FILE => new USectionFile(),
      USection::CRACKER => new USectionCracker(),
      USection::CONFIG => new USectionConfig(),
      USection::USER => new USectionUser(),
      USection::GROUP => new USectionGroup(),
      USection::ACCESS => new USectionAccess(),
      USection::ACCOUNT => new USectionAccount(),
      default => null,
    };
  }
  
  static function getDescription($section, $constant) {
    $sectionObject = UApi::getSection($section);
    if ($sectionObject == null) {
      return "__" . $section . "_" . $constant . "__";
    }
    return $sectionObject->describe($constant);
  }
}