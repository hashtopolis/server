<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class HashcatRelease extends AbstractModel {
  private $hashcatReleaseId;
  private $version;
  private $time;
  private $url;
  private $commonFiles;
  private $rootdir;
  private $minver;
  
  function __construct($hashcatReleaseId, $version, $time, $url, $commonFiles, $rootdir, $minver) {
    $this->hashcatReleaseId = $hashcatReleaseId;
    $this->version = $version;
    $this->time = $time;
    $this->url = $url;
    $this->commonFiles = $commonFiles;
    $this->rootdir = $rootdir;
    $this->minver = $minver;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['hashcatReleaseId'] = $this->hashcatReleaseId;
    $dict['version'] = $this->version;
    $dict['time'] = $this->time;
    $dict['url'] = $this->url;
    $dict['commonFiles'] = $this->commonFiles;
    $dict['rootdir'] = $this->rootdir;
    $dict['minver'] = $this->minver;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "hashcatReleaseId";
  }
  
  function getPrimaryKeyValue() {
    return $this->hashcatReleaseId;
  }
  
  function getId() {
    return $this->hashcatReleaseId;
  }
  
  function setId($id) {
    $this->hashcatReleaseId = $id;
  }
  
  function getVersion(){
    return $this->version;
  }
  
  function setVersion($version){
    $this->version = $version;
  }
  
  function getTime(){
    return $this->time;
  }
  
  function setTime($time){
    $this->time = $time;
  }
  
  function getUrl(){
    return $this->url;
  }
  
  function setUrl($url){
    $this->url = $url;
  }
  
  function getCommonFiles(){
    return $this->commonFiles;
  }
  
  function setCommonFiles($commonFiles){
    $this->commonFiles = $commonFiles;
  }
  
  function getRootdir(){
    return $this->rootdir;
  }
  
  function setRootdir($rootdir){
    $this->rootdir = $rootdir;
  }
  
  function getMinver(){
    return $this->minver;
  }
  
  function setMinver($minver){
    $this->minver = $minver;
  }
}
