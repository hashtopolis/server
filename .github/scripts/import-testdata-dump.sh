#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 1 ]]; then
  printf 'Usage: %s <mysql|postgres>\n' "$0" >&2
  exit 2
fi

db_system=$1
db_container="hashtopolis-db-dev"
database="hashtopolis"
user="hashtopolis"
password="hashtopolis"
config_source="ci/testdata/config.json"
config_target="/usr/local/share/hashtopolis/config/config.json"
compose_file=".github/docker-compose.${db_system}.yml"

if [[ ! -f "$config_source" ]]; then
  printf 'Missing testdata config: %s\n' "$config_source" >&2
  exit 1
fi

if [[ ! -f "$compose_file" ]]; then
  printf 'Missing compose file: %s\n' "$compose_file" >&2
  exit 1
fi

printf 'Installing real-world test config into application data volume: %s\n' "$config_target"
docker compose -f "$compose_file" run --rm --no-deps --entrypoint bash -T -u root hashtopolis-server-dev \
  -c "mkdir -p '$(dirname "$config_target")' && cp '/var/www/html/$config_source' '$config_target' && chown www-data:www-data '$config_target'"

wait_for_mysql() {
  for _ in {1..90}; do
    if docker exec "$db_container" mysql -u"$user" -p"$password" "$database" -e 'SELECT 1;' >/dev/null 2>&1; then
      return 0
    fi
    sleep 1
  done
  printf 'MySQL database did not become ready in time.\n' >&2
  return 1
}

wait_for_postgres() {
  for _ in {1..90}; do
    if docker exec "$db_container" pg_isready -U "$user" -d "$database" >/dev/null 2>&1; then
      return 0
    fi
    sleep 1
  done
  printf 'PostgreSQL database did not become ready in time.\n' >&2
  return 1
}

case "$db_system" in
  mysql)
    dump="ci/testdata/db_dump.mysql"
    if [[ ! -f "$dump" ]]; then
      printf 'Missing MySQL test dump: %s\n' "$dump" >&2
      exit 1
    fi
    wait_for_mysql
    printf 'Importing real-world MySQL test dump: %s\n' "$dump"
    docker exec -i "$db_container" mysql -u"$user" -p"$password" "$database" < "$dump"
    ;;
  postgres)
    dump="ci/testdata/db_dump.psql"
    if [[ ! -f "$dump" ]]; then
      printf 'Missing PostgreSQL test dump: %s\n' "$dump" >&2
      exit 1
    fi
    wait_for_postgres
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
