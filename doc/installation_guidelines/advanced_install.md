# Advanced installation

## Installation in an offline environment
If you want to run Hashtopolis on a network without internet access, you need a separate machine with internet to either pull the images from Docker Hub or build them yourself.

Here are the commands to pull the images from Docker hub. To build the images from source, follow the instructions in the section related to building images.
```
docker pull hashtopolis/backend:latest
docker pull hashtopolis/frontend:latest
docker pull mysql:8.0
```

The images can then be saved as .tar archives:
```
docker save hashtopolis/backend:latest --output hashtopolis-backend.tar
docker save hashtopolis/frontend:latest --output hashtopolis-frontend.tar
docker save mysql:8.0 --output mysql.tar
```

Next, transfer both file to your Hashtopolis server and import them using the following commands:
```
docker load --input hashtopolis-backend.tar
docker load --input hashtopolis-frontend.tar
docker load --input mysql.tar
```

Download docker-compose.yml and env.example and transfer them to your Hashtopolis server as well:

```
wget https://raw.githubusercontent.com/hashtopolis/server/master/docker-compose.yml
wget https://raw.githubusercontent.com/hashtopolis/server/master/env.example -O .env
```

Continue with the normal docker installation described in the [basic installation section](basic_install.md#setup-hashtopolis-server).

> [!CAUTION]
> Hashtopolis is pre-configured with a hashcat cracker. However, the binary package is not loaded within the docker image. A URL is provided so that the agent can download the binary when required. Obviously this does not work in an offline environment. Please check the [binaries cracker section](../user_manual/crackers_binary.md#adding-a-new-version) for details about how to handle such situation. 

## Local webserver for cracker binaries

If you want to use a custom binary or a different version of hashcat (for example with custom modules), you need to supply an URL for a ZIP-file containing that binary, that the agents can reach. You may want to store all your binaries within a local webserver, especially if your environment is offline/air-gapped.


If your Hashtopolis-instance is running, stop it, before you make any changes:
``` shell
docker compose down
```

### docker-compose.yml

In your docker-compose.yml-file you have to add an additional container:
``` docker-compose.yml
  file-download:
    container_name: file-download
    image: nginx
    restart: always
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
      - ./data:/var/www/html
    ports:
      - 8081:80

```
Adapt the configuration as needed. In this example you have to put your binary-ZIP-files in the `./data`-folder, where your docker-compose.yml is located and the webserver listens on port 8081.

!!! note "Note:" 
    If your environment is offline, keep in mind that you need to export and import the nginx image first following a similar process than for the hashtopolis images as described [previously](./advanced_install.md#installation-in-an-offline-environment). 

### nginx.conf

For the webserver, which serves the binaries, you need a custom nginx.conf located in the folder, where your docker-compose.yml is located. 

``` nginx.conf
events {
    worker_connections 1024;
}

http {
	server {
    		listen       80;
    		listen  [::]:80;
    		server_name  localhost;

    		location / {
        		root   /var/www/html;
        		index  index.html index.htm;
    		}
	}
}
```

Adapt this config to your liking and to your setup. You can configure the path to the nginx.conf in the docker-compose.yml.

If you are using a nginx-server for the SSL/TLS Setup, it is recommended  to use a separate nginx.conf for the SSL/TLS Setup and for the local webserver. Change your config-names accordingly and the paths in the docker-compose.yml.

### Usage

The local webserver starts with your regular hashtopolis-instance:

```docker compose up --detach ```

Put the ZIP-file for your custom binary in the `./data`-folder.

When registering a [new binary in the UI](../user_manual/crackers_binary.md#binaries) enter your `Download URL`:
`http://<hashtopolis-ip>:8081/<filename>.zip`

Your agents should now be able to download the new binary, if you create a task using that binary.


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

2. *(Optional)* Check the output of ```file docker-entrypoint.sh```. If it mentions *'with CRLF line terminators'*, your git checkout is converting line-ending on checkout. This is causing issues for files within the docker container. This is common behavior for example within Windows (WSL) instances. To fix this:
```
git config core.eol lf
git config core.autocrlf input
git rm -rf --cached .
git reset --hard HEAD
```

Check that ```file docker-entrypoint.sh``` correctly outputs: *'docker-entrypoint.sh: Bourne-Again shell script, ASCII text executable'*.

3. Copy the env.example file and modify the values as needed.
```
cp env.example .env
nano .env
```

4. Build the server docker image
```
docker build . -t hashtopolis/backend:latest --target hashtopolis-server-prod
```

## Using Local Folders outside of the Docker Volumes

By default (when you use the default docker-compose) the Hashtopolis folder (import, files and binaries) are in a Docker volume.

You can list this volume via docker volume ls. You can also access the volume directly in the backend, because it is mounted at: ```/usr/local/share/hashtopolis``` inside the container.

However, if you prefer not to use Docker volumes and instead use folders on the host OS, you can update the mount points in the *docker-compose.yml* file:
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

Make sure to back up all data from the Docker volume. You can do this using:
```
docker cp hashtopolis-backend:/usr/local/share/hashtopolis <directory>
```

Next, recreate the containers:
```
docker compose down
docker compose up
```

Finally, copy the data back into the appropriate folders after recreating the containers.

## Set up a fresh and clean instance

When there is the need for a complete reset/clean setup (e.g. for testing), you can do following steps to completely remove all data.

> [!CAUTION]
> The following steps will delete all data in your hashtopolis instance (including the database, users, tasks, agents, etc.)!

These steps assume that you have set up your hashtopolis instance using a `docker-compose.yml` file.

First stop all running all containers and clean them up:

```
cd <directory-containing-docker-compose.yml>
docker compose down
```

In case you have mounted directories for files and other data instead of using a docker volume, clean these directories by removing all files inside (wordlists, rules, etc.).

Delete the docker volumes for the database and hashtopolis data (if you don't have the folders mounted otherwise).
Use `docker volume ls` to determine which volumes exist (typically they are prefixed with the name of the folder containing the `docker-compose.yml`).

For each of the relevant volume, delete it by using `docker volume rm <volume-name>`.

Afterwards, you can start up the dockers again which should then be in a complete clean state and a freshly set up instance:

```
docker compose up -d
```
