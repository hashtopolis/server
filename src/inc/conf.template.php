<?php

//START CONFIG
$CONN['user'] = '__DBUSER__';
$CONN['pass'] = '__DBPASS__';
$CONN['server'] = '__DBSERVER__';
$CONN['db'] = '__DBDB__';
$CONN['port'] = '__DBPORT__';

$PEPPER = [
  "__PEPPER1__",
  "__PEPPER2__",
  "__PEPPER3__",
  "__CSRF__"
];

$INSTALL = false; //set this to true if you config the mysql and setup manually

$MASK_API_KEYS = false; // set this to true to restrict access to API keys to their owners
//END CONFIG
