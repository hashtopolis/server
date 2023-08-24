#!/usr/bin/env bash
paths=(inc/utils/locks)

for path in ${paths[@]}; do
  if chown www-data:www-data /var/www/html/${path} ; then
    echo "${path} was chown'ed properly"
  else
    echo "There was an error while chown'ing ${path}"
  fi
done

echo "Testing database."
MYSQL="mysql -u${HASHTOPOLIS_DB_USER} -p${HASHTOPOLIS_DB_PASS} -h ${HASHTOPOLIS_DB_HOST}"
$MYSQL -e "SELECT 1"
ERROR=$?

while [ $ERROR -ne 0 ];
do
  echo "Failed connecting to the database. Sleeping 5s."
  sleep 5
  $MYSQL -e "SELECT 1"
  ERROR=$?
done

# required to trigger the initialization
echo "Start initialization process..."
php -f /var/www/html/inc/load.php
echo "Initialization complete!"

docker-php-entrypoint apache2-foreground
