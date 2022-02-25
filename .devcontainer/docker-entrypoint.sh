#!/bin/sh
/usr/bin/mysql -uhashtopolis -phashtopolis -hdb -o < /var/www/html/src/install/hashtopolis.sql

cd /var/www/html/src && /usr/local/bin/php ../.devcontainer/adduser.php

docker-php-entrypoint apache2-foreground
