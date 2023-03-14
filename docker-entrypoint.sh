#!/usr/bin/env bash
paths=(install inc/Encryption.class.php inc/load.php files templates inc files lang . inc/utils/locks)

for path in ${paths[@]}; do
  if chown www-data:www-data /var/www/html/${path} ; then
    echo "${path} was chown'ed properly"
  else
    echo "There was an error while chown'ing ${path}"
    exit 1
  fi
done

if [ ! -d /var/www/vendor ];
then
  composer install --working-dir=/var/www/
fi
composer update --working-dir=/var/www/

docker-php-entrypoint apache2-foreground
