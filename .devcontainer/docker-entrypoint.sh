#!/bin/sh

echo "Testing database."
MYSQL="mysql -uhashtopolis -phashtopolis -hdb"
$MYSQL -e "SELECT 1"
ERROR=$?

while [ $ERROR -ne 0 ];
do
  echo "Failed connecting to the database. Sleeping 5s."
  sleep 5
  $MYSQL -e "SELECT 1"
  ERROR=$?
done

echo "Database is up and running. Testing if databases is configured."
$MYSQL -Dhashtopolis -e 'select * from User LIMIT 1;'
if [ $? -eq 1 ];
then
  echo "Importing SQL script."
  $MYSQL -Dhashtopolis < /var/www/html/src/install/hashtopolis.sql
fi

echo "Database configured. Testing if config is there."
if [ ! -f /var/www/html/src/inc/conf.php ];
then
  echo "Creating config template."
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
fi


echo "Config is there. Testing if root user is there."
username=$(mysql -uhashtopolis -phashtopolis -hdb -Dhashtopolis -BNe 'select username from User WHERE userId=1;')
if [ "$username" != "root" ];
then
  echo "Creating user and access token."
  cd /var/www/html/src && /usr/local/bin/php ../.devcontainer/adduser.php
fi

echo "Done setting up. Starting apache."
docker-php-entrypoint apache2-foreground
