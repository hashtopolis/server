#!/usr/bin/env bash

# https://elrey.casa/bash/scripting/harden
set -${-//[sc]/}eu${DEBUG:+xv}o pipefail

function main(){
  if chown -R www-data:www-data /var/www/html/ ; then
    echo "All files were chown'ed properly"
  else
    echo "There were some errors while chown'ing files"
  fi
  docker-php-entrypoint apache2-foreground
}

# https://elrey.casa/bash/scripting/main
if [[ "${0}" = "${BASH_SOURCE[0]:-bash}" ]] ; then
  main "${@}"
fi
