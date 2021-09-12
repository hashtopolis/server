#!/usr/bin/env bash

if chown -R www-data:www-data /var/www/html/ ; then
  echo "All files were chown'ed properly"
else
  echo "There were some errors while chown'ing files"
fi
docker-php-entrypoint apache2-foreground
