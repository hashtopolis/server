#!/usr/bin/env bash
paths=(inc/utils/locks)

for path in ${paths[@]}; do
  if [ -w ${HASHTOPOLIS_DOCUMENT_ROOT}/${path} ] ; then
    echo "${path} writeable"
  else
    echo "${path} is not writeable, please fix."
    exit 1
  fi
done

echo "Testing database."
MYSQL="mysql -u${HASHTOPOLIS_DB_USER} -p${HASHTOPOLIS_DB_PASS} -h ${HASHTOPOLIS_DB_HOST}"
$MYSQL -e "SELECT 1" > /dev/null
ERROR=$?

while [ $ERROR -ne 0 ];
do
  echo "Database not ready or unable to connect. Retrying in 5s."
  sleep 5
  $MYSQL -e "SELECT 1" > /dev/null
  ERROR=$?
done

echo "Setting up folders"
if [ ! -d ${HASHTOPOLIS_FILES_PATH} ];then
	mkdir -p ${HASHTOPOLIS_FILES_PATH} && chown www-data:www-data ${HASHTOPOLIS_FILES_PATH}
fi
if [ ! -d ${HASHTOPOLIS_CONFIG_PATH} ];then
	mkdir -p ${HASHTOPOLIS_CONFIG_PATH} && chown www-data:www-data ${HASHTOPOLIS_CONFIG_PATH}
fi
if [ ! -d ${HASHTOPOLIS_LOG_PATH} ];then
	mkdir -p ${HASHTOPOLIS_LOG_PATH} && chown www-data:www-data ${HASHTOPOLIS_LOG_PATH}
fi
if [ ! -d ${HASHTOPOLIS_IMPORT_PATH} ];then
	mkdir -p ${HASHTOPOLIS_IMPORT_PATH} && chown www-data:www-data ${HASHTOPOLIS_IMPORT_PATH}
fi

# required to trigger the initialization
echo "Start initialization process..."
php -f ${HASHTOPOLIS_DOCUMENT_ROOT}/inc/load.php
echo "Initialization complete!"

docker-php-entrypoint apache2-foreground
