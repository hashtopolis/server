#### Examples

Common sequences of commands used in development setups

Initialize token:
```
TOKEN=$(curl -X POST --user admin:hashtopolis http://localhost:8080/api/v2/auth/token | jq -r .token)
```

Fetch object:
```
curl --compressed --header "Authorization: Bearer $TOKEN" -g 'http://localhost:8080/api/v2/ui/hashtypes?page[size]=5'
```

Access database (MySQL):
```
mysql -u $HASHTOPOLIS_DB_USER -p$HASHTOPOLIS_DB_PASS -h $HASHTOPOLIS_DB_HOST -D $HASHTOPOLIS_DB_DATABASE
```

Access database (PostgreSQL):
```
psql -U${HASHTOPOLIS_DB_USER} -h${HASHTOPOLIS_DB_HOST}
```

Enable query logging:
```
docker exec $(docker ps -aqf "ancestor=mysql:8.0") mysql -u root -phashtopolis -e "SET global log_output = 'FILE'; SET global general_log_file='/tmp/mysql_all.log'; SET global general_log = 1;"
docker exec $(docker ps -aqf "ancestor=mysql:8.0") tail -f /tmp/mysql_all.log
```

Shortcut for testing within development setup:
```
cd ~/src/hashtopolis/server/ci/apiv2
pytest --exitfirst --last-failed
```

Run a specific test from the terminal
```
cd /var/www/html/ci/apiv2 && python3 -m pytest test_task.py::TaskTest::test_toggle_archive_task_supertask_type -v -s
```
