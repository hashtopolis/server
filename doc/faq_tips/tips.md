# Tips

Here are some cool tips for the users

## Debugging MySQL queries

Running into some funky issues? Want to see what hashtopolis is really submitting to the database?

You can enable query logging in mysql.

Login into the database:
```
docker exec -it <db container name> /bin/bash
mysql -p
# default is: hashtopolis
SET GLOBAL general_log = 'ON';
SET GLOBAL sql_log_off = 'ON';
exit
cd /var/lib/mysql
tail -f *.log
```
