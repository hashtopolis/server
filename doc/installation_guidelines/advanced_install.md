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