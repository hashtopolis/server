#!/bin/bash

git rev-parse --short HEAD > HEAD

docker build -t hashtopolis-server .

rm HEAD
