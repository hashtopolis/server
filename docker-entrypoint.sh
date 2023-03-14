#!/usr/bin/env bash
paths=(inc/utils/locks)

for path in ${paths[@]}; do
  if chown www-data:www-data /var/www/html/${path} ; then
    echo "${path} was chown'ed properly"
  else
    echo "There was an error while chown'ing ${path}"
    exit 1
  fi
done

docker-php-entrypoint apache2-foreground
