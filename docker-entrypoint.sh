#!/usr/bin/env bash

if test -f /var/www/html/inc/conf.php; then
  if chown www-data:www-data /var/www/html/inc/conf.php ; then
    echo "conf.php was chown'ed properly"
  else
    echo "There were some errors while chown'ing conf.php"
    exit 1
  fi
fi

if chown -R www-data:www-data /var/www/html/import/ ; then
  echo "import folder was chown'ed properly"
else
  echo "There were some errors while chown'ing the import folder"
  exit 1
fi

if chown -R www-data:www-data /var/www/html/files/ ; then
  echo "files folder was chown'ed properly"
else
  echo "There were some errors while chown'ing files folder"
  exit 1
fi

docker-php-entrypoint apache2-foreground
