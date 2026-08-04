#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 1 ]]; then
  printf 'Usage: %s <mysql|postgres>\n' "$0" >&2
  exit 2
fi

db_system=$1
app_container="hashtopolis-server-dev"
db_container="hashtopolis-db-dev"
database="hashtopolis"
user="hashtopolis"
password="hashtopolis"
config_source="ci/testdata/config.json"
config_target="/usr/local/share/hashtopolis/config/config.json"

if [[ ! -f "$config_source" ]]; then
  printf 'Missing testdata config: %s\n' "$config_source" >&2
  exit 1
fi

printf 'Installing real-world test config into %s:%s\n' "$app_container" "$config_target"
docker exec -u root "$app_container" mkdir -p "$(dirname "$config_target")"
docker cp "$config_source" "$app_container:$config_target"
docker exec -u root "$app_container" chown www-data:www-data "$config_target"

case "$db_system" in
  mysql)
    dump="ci/testdata/db_dump.mysql"
    if [[ ! -f "$dump" ]]; then
      printf 'Missing MySQL test dump: %s\n' "$dump" >&2
      exit 1
    fi
    printf 'Importing real-world MySQL test dump: %s\n' "$dump"
    docker exec -i "$db_container" mysql -u"$user" -p"$password" "$database" < "$dump"
    ;;
  postgres)
    dump="ci/testdata/db_dump.psql"
    if [[ ! -f "$dump" ]]; then
      printf 'Missing PostgreSQL test dump: %s\n' "$dump" >&2
      exit 1
    fi
    printf 'Resetting PostgreSQL schema before real-world dump import\n'
    docker exec "$db_container" psql -v ON_ERROR_STOP=1 -U "$user" -d "$database" \
      -c 'DROP SCHEMA public CASCADE; CREATE SCHEMA public;'
    printf 'Importing real-world PostgreSQL test dump: %s\n' "$dump"
    docker exec -i "$db_container" psql -v ON_ERROR_STOP=1 -U "$user" -d "$database" < "$dump"
    ;;
  *)
    printf 'Unsupported db system: %s\n' "$db_system" >&2
    exit 2
    ;;
esac

printf 'Real-world test dataset import completed.\n'
