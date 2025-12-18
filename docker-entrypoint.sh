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
if [[ -z "${HASHTOPOLIS_DB_TYPE+x}" ]]; then
    HASHTOPOLIS_DB_TYPE="mysql"
fi

echo "Testing database..."
if [[ "$HASHTOPOLIS_DB_TYPE" == "mysql" ]]; then
    echo "Using MySQL..."
    DB_CMD="mysql -u${HASHTOPOLIS_DB_USER} -p${HASHTOPOLIS_DB_PASS} -h ${HASHTOPOLIS_DB_HOST} --skip-ssl"
    DB_TYPE="mysql"
    if [[ -n "${HASHTOPOLIS_DB_PORT}" ]]; then
        DB_CMD="${DB_CMD} -P${HASHTOPOLIS_DB_PORT}"
    fi
elif [[ "$HASHTOPOLIS_DB_TYPE" == "postgres" ]]; then
    echo "Using postgres..."
    DB_CMD="psql -U${HASHTOPOLIS_DB_USER} -h ${HASHTOPOLIS_DB_HOST} ${HASHTOPOLIS_DB_DATABASE}"
    DB_TYPE="postgres"
    if [[ -n "${HASHTOPOLIS_DB_PORT}" ]]; then
        DB_CMD="${DB_CMD} -p${HASHTOPOLIS_DB_PORT}"
    fi
else
    echo "INVALID DATABASE TYPE PROVIDED: $HASHTOPOLIS_DB_TYPE"
    exit 1
fi

while :; do
    if [[ $DB_TYPE == "mysql" ]]; then
        $DB_CMD -e "SELECT 1" > /dev/null 2>&1
        ERROR=$?
    elif [[ $DB_TYPE == "postgres" ]]; then
        PGPASSWORD="${HASHTOPOLIS_DB_PASS}" $DB_CMD -c "SELECT 1" > /dev/null 2>&1
        ERROR=$?
    fi
    if [ $ERROR -eq 0 ]; then
        break
    fi
    echo "Database not ready or unable to connect. Retrying in 5s."
    sleep 5
done
echo "Database ready!"

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
if [ ! -d ${HASHTOPOLIS_BINARIES_PATH} ];then
  mkdir -p ${HASHTOPOLIS_BINARIES_PATH} && chown www-data:www-data ${HASHTOPOLIS_BINARIES_PATH}
fi

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
