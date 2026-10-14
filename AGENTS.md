# Agent Guidelines

## Testing With The Devcontainer

Use the devcontainer Compose setup so tests run with the expected PHP extensions,
dependencies, directories, and database. Run these commands from the repository
root:

```bash
docker compose -p devcontainer -f .devcontainer/docker-compose.mysql.yml build
docker compose -p devcontainer -f .devcontainer/docker-compose.mysql.yml run --rm --entrypoint composer hashtopolis-server-dev install --working-dir=/var/www/html
docker compose -p devcontainer -f .devcontainer/docker-compose.mysql.yml up -d
```

If migrations or an incompatible test schema cause failures, recreate the test
database first, then start the stack again:

```bash
docker compose -p devcontainer -f .devcontainer/docker-compose.mysql.yml down --volumes --remove-orphans
```

Run PHP tests and static analysis inside `hashtopolis-server-dev`:

```bash
docker exec -e HASHTOPOLIS_BACKEND_URL=http://localhost hashtopolis-server-dev vendor/bin/phpunit
docker exec hashtopolis-server-dev vendor/bin/phpstan analyse --no-progress
```

Pass a test file to PHPUnit for focused runs. API tests must run from
`/var/www/html/ci/apiv2` so their default configuration is discovered:

```bash
docker exec -w /var/www/html/ci/apiv2 hashtopolis-server-dev python -m pytest test_file.py -q
```

The PostgreSQL devcontainer Compose file can be used in the same way when that
database backend is relevant.
