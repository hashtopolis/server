#!/usr/bin/env bash

if test -f /var/www/html/inc/conf.php; then
  if chown www-data:www-data /var/www/html/inc/conf.php ; then
    echo "conf.php was chown'ed properly"
  else
    echo "There were some errors while chown'ing conf.php"
  fi
fi

if chown -R www-data:www-data /var/www/html/import/ ; then
  echo "import folder was chown'ed properly"
else
  echo "There were some errors while chown'ing the import folder"
fi

if chown -R www-data:www-data /var/www/html/files/ ; then
  echo "files folder was chown'ed properly"
else
  echo "There were some errors while chown'ing files folder"
fi

docker-php-entrypoint apache2-foreground
