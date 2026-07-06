
# Updating Hashtopolis

## Upgrading a Docker installation

This procedure applies to any upgrade between Docker-based releases (version 0.14.0 and up), for example from a 0.14.x or v1.0.0-rainbow release to the latest release. Database schema migrations are applied automatically by the backend container on startup, so the procedure is the same regardless of which version you are coming from.

> [!CAUTION]
> Upgrades keep the database engine you already use. Switching between MySQL and PostgreSQL as part of an upgrade is not supported. When downloading the docker-compose and env files below, always pick the variant matching your existing database.

1. Back up your database before upgrading:

For MySQL:
```
docker exec hashtopolis-db mysqldump -u root -p<root-password> hashtopolis > hashtopolis-backup.sql
```

For PostgreSQL:
```
docker exec db pg_dump -U <postgres-user> hashtopolis > hashtopolis-backup.sql
```

> [!NOTE]
> The database container name may differ on older installations. Use ```docker ps``` to find it.

2. Stop the containers:
```
docker compose down
```

3. The docker-compose file may have changed between releases (for example, the *hashtopolis/frontend* container and the separate MySQL/PostgreSQL variants were introduced with v1.0.0). Download the latest docker-compose file matching your database and replace your existing one:

For MySQL:
```
wget https://raw.githubusercontent.com/hashtopolis/server/master/docker-compose.mysql.yml -O docker-compose.yml
```

**or**

For PostgreSQL:
```
wget https://raw.githubusercontent.com/hashtopolis/server/master/docker-compose.postgres.yml -O docker-compose.yml
```

If you made local changes to your previous docker-compose file (custom volumes, ports, additional containers), re-apply them to the new file. Keep your existing *.env* file and Docker volumes: they contain your configuration and data.

4. Pull the new images and restart the containers:
```
docker compose pull
docker compose up --detach
```

The backend container applies any pending database migrations on startup and the upgrade is complete.

## Upgrading offline systems

1. On a system with internet access, pull the new images and save them as tar-files:

```
docker pull hashtopolis/backend:latest
docker pull hashtopolis/frontend:latest
docker save hashtopolis/backend:latest --output hashtopolis-backend.tar
docker save hashtopolis/frontend:latest --output hashtopolis-frontend.tar
```

If the docker-compose file changed since your installed version, also download the latest variant matching your database (see the previous section) and transfer it along with the tar-files.

2. Next, transfer the files to your Hashtopolis server and import them using the following commands:

```
docker compose down
docker load --input hashtopolis-backend.tar
docker load --input hashtopolis-frontend.tar
docker compose up --detach
```

## Migrating a non-Docker installation to Docker (pre-0.14.0)

Versions before 0.14.0 were installed directly on a webserver. There are multiple ways to migrate such a setup to Docker. You can start fresh, but if you want to keep your data, several migration options are available.

> [!NOTE]
> Pre-Docker Hashtopolis installations use MySQL, so this migration must use the MySQL variants of the docker-compose and env files. Switching to PostgreSQL is not possible as part of this migration — if you want PostgreSQL, perform a fresh installation instead.

### Importing the existing database in docker
You can reuse your old database server or also migrate the database within a docker container.

1. [Install docker](https://docs.docker.com/engine/install/ubuntu/) to your system
2. Create a database backup using
```
mysqldump <database-name> > hashtopolis-backup.sql
```

3. Make copies of the following folders, located in the Hashtopolis directory next to index.php:

   - files
   - import
   - log

4. Download the docker compose file: 
```
wget https://raw.githubusercontent.com/hashtopolis/server/master/docker-compose.mysql.yml -O docker-compose.yml
```

5. Edit the docker compose file
```
[...]
  hashtopolis-server:
[...]
    volumes:
      - <path to where you want to store your hashtopolis files>:/usr/local/share/hashtopolis:Z
[...]
```

6. Download the env file 
```
wget https://raw.githubusercontent.com/hashtopolis/server/master/env.mysql.example -O .env
```

7. Edit the .env file and adjust the settings according to your desired configuration:

   - HASHTOPOLIS_ADMIN_USER is only used during initial setup. Once the database is imported, it will be overwritten with your previous data.
8. Create the folder which to referred to in the docker-compose, in our example we will use /usr/local/share/hashtopolis
``` 
sudo mkdir -p /usr/local/share/hashtopolis
``` 

9. Copy the files, import, and log folders to the location referenced in the docker-compose file.
``` 
sudo cp -r files/ import/ log/ /usr/local/share/hashtopolis
```

10. In the same folder create a config folder: 
```
mkdir /usr/local/share/hashtopolis/config
```

11. Start the docker container docker compose up
12. Stop the backend container to avoid agents interfering with the migration:
```
docker compose stop hashtopolis-backend
```

13. To migrate the data, first copy the database backup towards the db container: 
```
docker cp hashtopolis-backup.sql db:.
```

14. Login on the container: 
```
docker exec -it db /bin/bash
```

15. Import the data: 
```
mysql -D hashtopolis -p < hashtopolis-backup.sql
```

16. Exit the container
17. Copy the PEPPER value from *inc/conf.php* and paste it into *config/config.json*. For example, from */var/www/hashtopolis/inc/conf.php*:
```
[...]
$PEPPER = [..., ..., ..., ...];
[...]
```
Becomes */usr/local/share/hashtopolis/config/config.json*:
```
{
  "PEPPER": [..., ..., ..., ...],
}
```

18. Restart the containers 

```
docker compose down && docker compose up
```

### Preserving the existing database 

Repeat the above steps, but you do not need to export or import the database. Just ensure the .env file points to your database server and that it is reachable from the container.
