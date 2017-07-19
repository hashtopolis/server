<?php

namespace DBA;

class HashcatRelease extends AbstractModel {
  private $hashcatReleaseId;
  private $version;
  private $time;
  private $url;
  private $rootdir;
  
  function __construct($hashcatReleaseId, $version, $time, $url, $rootdir) {
    $this->hashcatReleaseId = $hashcatReleaseId;
    $this->version = $version;
    $this->time = $time;
    $this->url = $url;
    $this->rootdir = $rootdir;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['hashcatReleaseId'] = $this->hashcatReleaseId;
    $dict['version'] = $this->version;
    $dict['time'] = $this->time;
    $dict['url'] = $this->url;
    $dict['rootdir'] = $this->rootdir;
    
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
  
  function getRootdir(){
    return $this->rootdir;
  }
  
  function setRootdir($rootdir){
    $this->rootdir = $rootdir;
  }

  const HASHCAT_RELEASE_ID = "hashcatReleaseId";
  const VERSION = "version";
  const TIME = "time";
  const URL = "url";
  const ROOTDIR = "rootdir";
}
