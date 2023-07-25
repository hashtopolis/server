#!/usr/bin/env bash 
#
# Helper script to maintentance of docker dev containers
#

#
# Parse options
declare {FLAG_BATCH,FLAG_COMMIT,FLAG_FORCE}=0
OPTS=$(getopt -o '' -a --longoptions 'force,batch,commit' -n "$0" -- "$@")
if [[ $? -ne 0 ]] ; then echo "Failed parsing options." >&2 ; exit 128 ; fi
eval set -- "$OPTS"

while true; do
  case "$1" in
    --force)
        FLAG_FORCE=1
        shift 1
        ;;
    --batch)
        FLAG_BATCH=1
        shift 1
        ;;
    --commit)
        FLAG_COMMIT=1
        shift 1
        ;;
    --)
        shift
        break
        ;;
    *)
        echo ""
        echo "Error in given Parameters. Undefined: "
        echo $*
        echo ""
        echo "Usage: $0 [--batch] [--commit] [--force]"
        exit 1
  esac
done

#
# Issue warning message
DELAY=10
echo """
# !!!!!!!!! PLEASE READ CAREFULLY !!!!!!!!
# This script can destroy any information about the app, by default
# if will show what would be deleted (dry-run).
#
# Flags:
#    --force    Forcefully remove the database and volumes as well
#    --batch    Non-interactive mode (e.g. no delays)
#    --commit   Commit changes (e.g. apply dry-run changes)
#
# Press CTRL+C within $DELAY seconds to cancel
# !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
"""
if [ $FLAG_BATCH -eq 0 ]; then
  sleep $DELAY
fi


#
# Perform actual deleting of container
CONTAINER_NAME="hashtopolis-server-dev"
DB_NAME="hashtopolis-db-dev"
IMAGE_NAME="server_devcontainer-hashtopolis-server-dev"
VOLUME_NAME="server_devcontainer_hashtopolis-server-dev"
VOLUME_DB_NAME="server_devcontainer_hashtopolis-db-dev"
NETWORK_NAME="hashtopolis_dev"

if [ $FLAG_COMMIT -eq 0 ]; then
  DOCKER_CMD='echo ## [DRY-RUN] docker'
else
  DOCKER_CMD='docker'
fi

echo "# 1 - Stop and remove running app container(s) ($CONTAINER_NAME)"
docker ps -q --no-trunc --filter "name=$CONTAINER_NAME" | xargs -r $DOCKER_CMD stop
docker ps -a -q --filter "name=$CONTAINER_NAME" | xargs -r $DOCKER_CMD rm

if [ $FLAG_FORCE -eq 1 ]; then
  echo "# 2 - Stop and remove running database app container(s) ($DB_NAME)"
  docker ps -q --no-trunc  --filter "name=$DB_NAME" | xargs -r $DOCKER_CMD stop
  docker ps -a -q --filter "name=$DB_NAME" | xargs -r $DOCKER_CMD rm

  echo "# 3 - Remove app volume ($VOLUME_NAME)"
  docker volume ls -q --filter "name=$VOLUME_NAME" | xargs -r $DOCKER_CMD volume rm --force
  echo "# 4 - Remove database volume ($VOLUME_DB_NAME)"
  docker volume ls -q --filter "name=$VOLUME_DB_NAME" | xargs -r $DOCKER_CMD volume rm --force
fi 

echo "# 5 - Remove app image ($IMAGE_NAME)"
docker image ls -q "$IMAGE_NAME" --no-trunc | xargs -r $DOCKER_CMD image rmi 
echo "# 6 - Remove Network ($NETWORK_NAME)"
docker network ls -q --no-trunc --filter "name=$NETWORK_NAME" | xargs -r $DOCKER_CMD network rm --force
echo "# 7 - Prune container, image and volume"
$DOCKER_CMD container prune -f
$DOCKER_CMD image prune -f
$DOCKER_CMD volume prune -f

if [ $FLAG_COMMIT -eq 0 ]; then
  echo ""
  echo "#"
  echo "# Use '--commit' to apply [DRY-RUN] changes"
  echo "#"
fi