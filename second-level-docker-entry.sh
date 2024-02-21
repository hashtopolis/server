#!/usr/bin/env bash

# set up SSMTP config
if [[ $HASHTOPOLIS_SSMTP_ENABLE == 1 ]]; then
  echo "Setting up SSMTP config..."
  echo -e "\
root=${HASHTOPOLIS_SSMTP_ROOT}\n\
mailhub=${HASHTOPOLIS_SSMTP_MAILHUB}\n\
hostname=${HASHTOPOLIS_SSMTP_HOSTNAME}\n\
UseTLS=${HASHTOPOLIS_SSMTP_USE_TLS}\n\
UseSTARTTLS=${HASHTOPOLIS_SSMTP_USE_STARTTLS}\n\
AuthUser=${HASHTOPOLIS_SSMTP_AUTH_USER}\n\
AuthPass=${HASHTOPOLIS_SSMTP_AUTH_PASS}\n\
FromLineOverride=NO\n\
#Debug=YES\n" > /etc/ssmtp/ssmtp.conf
fi