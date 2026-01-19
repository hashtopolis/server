# Docker

All the following commands need to be executed in the folder, where the docker-compose.yml-file is located.

## Start the containers and run foreground:

This is to see the logging output for debugging purposes.

```
docker compose up
```

## Start the containers and run in background:

```
docker compose up --detach
```

## Update your containers to the latest version:

```
docker compose down
docker compose pull
docker compose up
```

## Stop and remove the containers:

```
docker compose down
```

## List running containers:

```
docker compose ps
```
 Here you see the different containers (frontend, backend and db), that are need to run Hashtopolis. In addition you see, when the containers were created and since when they are running.

## Access the database:

```
docker compose exec db mysql -u root -p
```

You will be prompted for the database-password (default is 'hashtopolis'). You can directly have a look at the data in the database there and alter it. This is not the supported way. Do this at your own risk!

## Show the logs of a container:

```
docker compose logs db
docker compose logs hashtopolis-frontend
docker compose logs hashtopolis-backend
```
You can have a look at the logs of the different containers, if something is going wrong. This may be needed for support purposes in the Discord-channel.