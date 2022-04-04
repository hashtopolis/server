#!/bin/bash

cd /var//html/hashtopolis
git checkout $1
chown -R www-data /var/www/html/hashtopolis
chmod -R 0777 /var/www/html/hashtopolis/src/inc/