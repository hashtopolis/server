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
$MYSQL -e "SELECT 1" > /dev/null 2>&1
ERROR=$?

while [ $ERROR -ne 0 ];
do
  echo "Database not ready or unable to connect. Retrying in 5s."
  sleep 5
  $MYSQL -e "SELECT 1" > /dev/null 2>&1
  ERROR=$?
done

echo "Database ready."

directories=(
  "${HASHTOPOLIS_FILES_PATH}"
  "${HASHTOPOLIS_CONFIG_PATH}"
  "${HASHTOPOLIS_LOG_PATH}"
  "${HASHTOPOLIS_IMPORT_PATH}"
  "${HASHTOPOLIS_BINARIES_PATH}"
  "${HASHTOPOLIS_TUS_PATH}"
  "${HASHTOPOLIS_TEMP_UPLOADS_PATH}"
  "${HASHTOPOLIS_TEMP_META_PATH}"
)

echo "Setting up folders"
for dir in "${directories[@]}"; do
  mkdir -p "$dir"
  chown www-data:www-data "$dir"
done

# required to trigger the initialization
echo "Start initialization process..."
php -f ${HASHTOPOLIS_DOCUMENT_ROOT}/inc/load.php
echo "Initialization complete!"



echo "                               @@@@@@@@@@@@@@@@@@"
echo "               @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@"
echo "                 @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@"
echo "                   @@@@@@@#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@"
echo "                    @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@"
echo "                      @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@"
echo "                      @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@"
echo "                       @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@"
echo "                       @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@"
echo "                        @@@@+-#@@@@@@@@@@@@@@@@@@#-+@@@@"
echo "                         @@@@+  -*%@@@@@@@@@@%*-  +@@@@"
echo "                          @@@@%-   .-@@@@@@-.   -%@@@@"
echo "                           @@@@@@#*++@@@@@@++*#@@@@@@"
echo "                            @@@@@@@@@@@@@@@@@@@@@@@@"
echo "                              @@@@@@@@@@@@@@@@@@@@"
echo "           @@                     @@@@@@@@@@@@                     @@"
echo "          @@                      @@@@@@@@@@@@                      @@"
echo "    @@   @@@                      @@@@@@@@@@@@                      @@@   @@"
echo "   @@    @@@                     @@@@@@@@@@@@@@                     @@@    @@"
echo "   @@    @@@@                   @@@@@@@@@@@@@@@@                   @@@@    @@"
echo "   @@    @@@@                 @@@@@@@@@@@@@@@@@@@@                 @@@@    @@"
echo "  @@@    @@@@@             @@@@@@@@@@@@@@@@@@@@@@@@               @@@@@    @@@"
echo " @@@@     @@@@@@@@     @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@     @@@@@@@@     @@@@"
echo " @@@@      @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@      @@@@"
echo "  @@@        @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@       @@@@"
echo "  @@@@          @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@         @@@@@"
echo "   @@@@@@@            @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@           @@@@@@@@"
echo "   @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@"
echo "    @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@"
echo "       @@@@@@@@@@@@@@@@@@@@@@@                    @@@@@@@@@@@@@@@@@@@@@@@"
echo "          @@@@@@@@@@@@@@                                @@@@@@@@@@@@@@"
echo ""
echo ""
echo "                        Hashtopolis is now ready to use!"
echo "                                      *\0/*"
echo ""
echo ""


docker-php-entrypoint apache2-foreground
