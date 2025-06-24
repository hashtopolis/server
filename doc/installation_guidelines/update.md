
# Updating Hashtopolis 

## Upgrading to 0.14.0 (from non-Docker to Docker)

There are multiple ways to migrate data from a non-Docker setup to Docker. You can start fresh, but if you want to keep your data, several migration options are available.

### Existing database
You can reuse your old database server or also migrate the database to a docker container.

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
wget https://raw.githubusercontent.com/hashtopolis/server/master/docker-compose.yml
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
wget https://raw.githubusercontent.com/hashtopolis/server/master/env.example -O .env
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

18. Restart the compose docker compose down && docker compose up

### New database 

Repeat the above steps, but you do not need to export or import the database. Just ensure the .env file points to your database server and that it is reachable from the container.

## Upgrading from docker to docker (version 0.14.0 and up)
1. Stop your docker compose docker compose down
2. docker compose pull
3. docker compose up

## Upgrading from docker to docker (version 0.14.0 and up) - Offline System(s)

***To be done***


<!-- ## New user interface: technical preview

> [!NOTE]
> The APIv2 and UIv2 are a technical preview. Currently, when enabled, everyone through the new API will be fully admin!

To enable 'version 2' of the API:

1. Stop your containers

2. set the HASHTOPOLIS_APIV2_ENABLE to 1 inside the .env file.

3. Relaunch the containers
```
docker compose up --detach
```

4. Access the technical preview via: ```http://127.0.0.1:4200``` using the default credentials user=admin and password=hashtopolis, unless modified in the .env file. -->
