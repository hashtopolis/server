#!/bin/bash

COUNT=0
while [ $COUNT -lt 16 ]
do
  curl localhost:8080/api/server.php -X POST -H 'Content-Type: application/json' -d '{ "action": "testConnection" }' | grep SUCCESS
  if [ $? -eq 0 ]; then
    exit 0
  fi
  COUNT=$[$COUNT+1]
  sleep 4
done
exit 1