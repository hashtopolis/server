#!/bin/sh

if [ ! -f /var/www/html/src/inc/conf.php ];
then
  echo "Testing database."
  MYSQL="mysql -uhashtopolis -phashtopolis -hdb"
	$MYSQL -e "SELECT 1"
	ERROR=$?

  while [ $ERROR -ne 0 ];
  do
    echo "Failed connecting to the database. Sleeping 5s"
    sleep 5
    $MYSQL -e "SELECT 1"
	  ERROR=$?
  done

  $MYSQL -Dhashtopolis --verbose < /var/www/html/src/install/hashtopolis.sql

  if [ $? -eq 0 ];
  then
      echo "<?php

//START CONFIG
\$CONN['user'] = 'hashtopolis';
\$CONN['pass'] = 'hashtopolis';
\$CONN['server'] = 'db';
\$CONN['db'] = 'hashtopolis';
\$CONN['port'] = '3306';

\$PEPPER = [
  \"__PEPPER1__\",
  \"__PEPPER2__\",
  \"__PEPPER3__\",
  \"__CSRF__\"
];

\$INSTALL = true; //set this to true if you config the mysql and setup manually
" > /var/www/html/src/inc/conf.php
    cd /var/www/html/src && /usr/local/bin/php ../.devcontainer/adduser.php
  else
    echo "Error setting up database."
  fi



fi

docker-php-entrypoint apache2-foreground
