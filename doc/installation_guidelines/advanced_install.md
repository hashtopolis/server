# Advanced installation

## Installation in an airgapped/offline/oil-gapped system (**make a note about the binary**)
If you are running Hashtopolis in an offline network or an air-gapped network, you will need to use a machine with internet access to either pull the images directly from the docker hub or build it yourself.

Here are the commands to pull the images from Docker hub. To build the images from source, follow the instructions in the section related to building images.
```
docker pull hashtopolis/backend:latest
docker pull hashtopolis/frontend:latest
```

The images can then be saved as .tar archives:
```
docker save hashtopolis/backend:latest --output hashtopolis-backend.tar
docker save hashtopolis/frontend:latest --output hashtopolis-frontend.tar
```

Next, transfer both file to your Hashtopolis server and import them using the following commands
```
docker load --input hashtopolis-backend.tar
docker load --input hashtopolis-frontend.tar
```

Continue with the normal docker installation described in the [basic installation section](/installation_guidelines/basic_install/#setup-hashtopolis-server).

## Build Hashtopolis images yourself
The Docker images can be built from source following these steps.

### Build frontend image
1. Clone the Hashtopolis web-ui repository and cd into it.
```
git clone https://github.com/hashtopolis/web-ui.git
cd web-ui
```

2. Build the web-ui repo and tag it
```
docker build -t hashtopolis/frontend:latest --target hashtopolis-web-ui-prod .
```

### Build backend image
1. Move one directory back, clone the Hashtopolis server repository and cd into it:
```
cd ..
git clone https://github.com/hashtopolis/server.git
cd server
```

2. *(Optional)* Check the output of ```file docker-entrypoint.sh```. If it mentions *'with CRLF line terminators'*, your git checkout is converting line-ending on checkout. This is causing issues for files within the docker container. This is common behaviour for example within Windows (WSL) instances. To fix this:
```
git config core.eol lf
git config core.autocrlf input
git rm -rf --cached .
git reset --hard HEAD
```

Check that ```file docker-entrypoint.sh``` correctly outputs: *'docker-entrypoint.sh: Bourne-Again shell script, ASCII text executable'*.

3. Copy the env.example and edit the values to your likings
```
cp env.example .env
nano .env
```

4. (Optional) If you want to test a preview of the version 2 of the UI, consult the New user interface technical preview section. (***Internal LINK***)

5. Build the server docker image
```
docker build . -t hashtopolis/backend:latest --target hashtopolis-server-prod
```

## Using Local Folders outside of the Docker Volumes

By default (when you use the default docker-compose) the Hashtopolis folder (import, files and binaries) are in a Docker volume.

You can list this volume via docker volume ls. You can also access the volume directly in the backend, because it is mounted at: ```/usr/local/share/hashtopolis``` inside the container.

However, if you do not want the use the volume but want to use folders of the host OS you can change the mount points in the docker compose file:
```
version: '3.7'
services:
  hashtopolis-backend:
    container_name: hashtopolis-backend
    image: hashtopolis/backend:latest
    restart: always
    volumes:
      # Where /opt/hashtopolis/<folder> are folders on you host OS.
      - /opt/hashtopolis/config:/usr/local/share/hashtopolis/config:Z
      - /opt/hashtopolis/log:/usr/local/share/hashtopolis/log:Z
      - /opt/hashtopolis/import:/usr/local/share/hashtopolis/import:Z
      - /opt/hashtopolis/binaries:/usr/local/share/hashtopolis/binaries:Z
      - /opt/hashtopolis/files:/usr/local/share/hashtopolis/files:Z
    environment:
      HASHTOPOLIS_DB_USER: $MYSQL_USER
      HASHTOPOLIS_DB_PASS: $MYSQL_PASSWORD
      HASHTOPOLIS_DB_HOST: $HASHTOPOLIS_DB_HOST
      HASHTOPOLIS_DB_DATABASE: $MYSQL_DATABASE
      HASHTOPOLIS_ADMIN_USER: $HASHTOPOLIS_ADMIN_USER
      HASHTOPOLIS_ADMIN_PASSWORD: $HASHTOPOLIS_ADMIN_PASSWORD
      HASHTOPOLIS_APIV2_ENABLE: $HASHTOPOLIS_APIV2_ENABLE
    depends_on:
      - db
    ports:
      - 8080:80
  db:
    container_name: db
    image: mysql:8.0
    restart: always
    volumes:
      - db:/var/lib/mysql
    environment:
      MYSQL_ROOT_PASSWORD: $MYSQL_ROOT_PASS
      MYSQL_DATABASE: $MYSQL_DATABASE
      MYSQL_USER: $MYSQL_USER
      MYSQL_PASSWORD: $MYSQL_PASSWORD
  hashtopolis-frontend:
    container_name: hashtopolis-frontend
    image: hashtopolis/frontend:latest
    environment:
      HASHTOPOLIS_BACKEND_URL: $HASHTOPOLIS_BACKEND_URL
    restart: always
    depends_on: 
      - hashtopolis-backend
    ports:
      - 4200:80
volumes:
  db:
  hashtopolis:
```

Make sure to copy everything out of the docker volume, you can do that using:
```
docker cp hashtopolis-backend:/usr/local/share/hashtopolis <directory>
```

Next, recreate the containers:
```
docker compose down
docker compose up
```

Remember to copy the contents back into the folders.

## Upgrading to 0.14.0 (from non-Docker to Docker)
There are multiple ways to migrate the data from your non-docker setup to docker. You can of course completely start fresh; but if you want to migrate your data there are multiple ways to do this.

### Existing database (**formerly called New database**)
You can reuse your old database server or also migrate the database to a docker container.

1. Install docker to your system (https://docs.docker.com/engine/install/ubuntu/)
2. Create a database backup mysqldump <database-name> > hashtopolis-backup.sql
3. Make copies of the following folders, can be found in the hashtopolis folder along side the index.php:
   - files
   - import
   - log
4. Download the docker compose file: wget https://raw.githubusercontent.com/hashtopolis/server/master/docker-compose.yml
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

7. Edit the .env file and change the settings to your likings nano .env
   - Optional: if you want to test the new API and new UI, set the HASHTOPOLIS_APIV2_ENABLE to 1 inside the .env file. NOTE: The APIv2 and UIv2 are a technical preview. Currently when enable everyone through the new API will be fully admin!
   - The HASHTOPOLIS_ADMIN_USER is only used during setup time and once you import the database backup will be replaced with your old data.
8. Create the folder which to referred to in the docker-compose, in our example we will use /usr/local/share/hashtopolis
``` 
sudo mkdir -p /usr/local/share/hashtopolis
``` 

9. Copy the files, import, and log to the new location you refered to in the docker-compose file.
``` 
sudo cp -r files/ import/ log/ /usr/local/share/hashtopolis
```

10. In the same folder create a config folder: 
```
mkdir /usr/local/share/hashtopolis/config
```

11. Start the docker container docker compose up
12. Stop the backend container so that agents don't mess up the database mid migration docker 
```
stop hashtopolis-backend
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
mysql -Dhashtopolis -p < hashtopolis-backup.sql
```

16. Exit the container
17. Copy the content of the PEPPER from the *inc/conf.php* file and place them into *config/config*.json`
Example */var/www/hashtopolis/inc/conf.php*:
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

### New database (**formerly called Existing database**)

Repeat all the steps above, but you don't need to export/import the database. Only make sure that you point the settings inside the .env file to your database server and that the database server is reachable from your container.

## Upgrading from docker to docker (version 0.14.0 and up)
1. Stop your docker compose docker compose down
2. docker compose pull
3. docker compose up

## Upgrading from docker to docker (version 0.14.0 and up) - Offline System(s)

***To be done***


## New user interface: technical preview

> [!NOTE]
> The APIv2 and UIv2 are a technical preview. Currently, when enabled, everyone through the new API will be fully admin!

To enable 'version 2' of the API:

1. Stop your containers

2. set the HASHTOPOLIS_APIV2_ENABLE to 1 inside the .env file.

3. Relaunch the containers
```
docker compose up --detach
```

4. Access the technical preview via: ```http://127.0.0.1:4200``` using the credentials user=admin and password=hashtopolis, unless modified in the .env file.
