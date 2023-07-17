#!/usr/bin/env bash
#
# PLEASE READ CAREFULLY
# This script will destroy any information about the app
# if you want to keep the database comment 2 and 3 for the volume (ie. import folder)
#
CONTAINER_NAME="hashtopolis-backend"
DB_NAME="db"
IMAGE_NAME="dev_hashtopolis-backend"
VOLUME_NAME="dev_db"
VOLUME_DB_NAME="dev_hashtopolis"
NETWORK_NAME="dev_default"

# 1 - Stop and remove running app container(s)
docker ps -q --no-trunc --filter "name=$CONTAINER_NAME" | xargs -r docker stop
docker ps -a -q --filter "name=$CONTAINER_NAME" | xargs -r docker rm
# 2 - Stop and remove running app container(s)
docker ps -q --no-trunc  --filter "name=$DB_NAME" | xargs -r docker stop
docker ps -a -q --filter "name=$DB_NAME" | xargs -r docker rm
# 3 - Remove Volumne, comment this line if you dont want to the volumen
docker volume ls --filter "name=$VOLUME_NAME" | xargs -r docker volume rm --force
docker volume ls --filter "name=$VOLUME_DB_NAME" | xargs -r docker volume rm --force
# 4 - Remove image
docker image rmi "$IMAGE_NAME"
# 5 - Remove Network
docker network ls -q --no-trunc --filter "name=$NETWORK_NAME" | xargs -r docker network rm --force
# 6 - Prune container and volume
docker container prune -f && docker volume prune -f