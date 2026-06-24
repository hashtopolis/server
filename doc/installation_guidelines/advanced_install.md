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

## Backup and Restore

What the best way to backup and restore your hashtopolis instance depends heavily on the way the instance is set up and what configurations are made.
Therefore, there is no guide available for backing up / restoring which works for everyone, but some considerations which need to be taken into account:

- Depending on the amount of data (files, database size, etc.) in the hashtopolis instance, a complete backup can become quite large. If it is needed to just be able to restore information about executed tasks, progress etc. (e.g. in case of a fatal failure of the system) it is enough to just back up the database, but of course this would not allow a easy restore to a previous state.
- If you plan to do a backup in a way to be able to completely restore it to the previous state (files, logs, database, users, etc.), you need to be careful to include all required items into your backup and when restoring make sure that nothing gets left out during that process, otherwise you may end up with a semi-broken or non-functional hashtopolis instance.
- In case you have set up your hashtopolis instance only using volumes (one for the database, one for all the hashtopolis data), backup up the complete content of these volumes is enough to have all data backed up.
- Restoring only parts (some tasks, only users, other database parts) from a backup is very tricky and should only be done by experts and very easily goes wrong when primary keys are not sequential and not updated for auto increment in the database.

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

In case you have mounted directories for files and other data instead of using a docker volume, clean these directories by removing all files within (wordlists, rules, etc.).

Delete the docker volumes for the database and hashtopolis data (if you don't have the folders mounted otherwise).
Use `docker volume ls` to determine which volumes exist (typically they are prefixed with the name of the folder containing the `docker-compose.yml`).

For each of the relevant volume, delete it by using `docker volume rm <volume-name>`.

Afterwards, you can start up the containers again which should then be in a complete clean state and a freshly set up instance:

```
docker compose up -d
```

## Hashtopolis Mail Setup (Sendmail or Postfix)

This guide gives the fastest ways to make mail sending work in Hashtopolis to be used for sending notifications

### Important Hashtopolis-specific requirement

Current backend logic considers mail "configured" only if this file exists:

- /etc/ssmtp/ssmtp.conf

Even when using sendmail or postfix, you should still provide this file (it can be minimal) so Hashtopolis enables email sending.

### Recommended easiest path: Postfix relay + existing backend image

This is the easiest because the backend image already supports an ssmtp config mount.

#### 1. Prepare a postfix relay (host or separate container)

Configure postfix as a null client / relay host. Typical key setting in postfix main.cf:

- relayhost = [smtp.your-provider.tld]:587

Also configure SASL and TLS as needed by your provider.

#### 2. Create ssmtp.conf in your project root

Use the provided template as base:

- copy ssmtp.conf.example to ssmtp.conf

Example values:

- root=admin@your-domain.tld
- mailhub=host.docker.internal:25
- rewriteDomain=your-domain.tld
- UseTLS=No
- UseSTARTTLS=No
- FromLineOverride=yes

If your postfix relay requires auth/TLS on another port, set mailhub and TLS/auth values accordingly.

#### 3. Mount ssmtp.conf into Hashtopolis backend container

In docker-compose.mysql.yml or docker-compose.postgres.yml, enable this volume:

```yaml
services:
  hashtopolis-backend:
    volumes:
      - hashtopolis:/usr/local/share/hashtopolis:Z
      - ./ssmtp.conf:/etc/ssmtp/ssmtp.conf
```

#### 4. Configure sender identity in Hashtopolis

In Hashtopolis Config - Notifications / Sender Settings, set:

- emailSender
- emailSenderName

These values are used in From headers.

#### 5. Restart and test

- Restart backend container.
- Create a 'New Notification' in your user notification page (see [Notifications page](../user_manual/user-settings.md#notifications) for more details) for example for a 'newTask'. 
- Trigger the notification accordingly, e.g. by creating a new task.
- If it fails, inspect backend logs and postfix logs.

---

### Alternative path: direct sendmail/postfix inside backend container

Use this only if you specifically need local MTA delivery from the backend container.

#### 1. Build a custom backend image

Install postfix or sendmail package and ensure /usr/sbin/sendmail exists.

#### 2. Keep Hashtopolis mail gate satisfied

Create the file below inside the container (even if unused by your MTA logic):

- /etc/ssmtp/ssmtp.conf

A minimal placeholder is enough:

```conf
root=postmaster@localhost
mailhub=localhost
FromLineOverride=yes
```

#### 3. Ensure PHP uses sendmail interface

Set php sendmail_path appropriately (commonly default is already fine):

- sendmail_path = "/usr/sbin/sendmail -t -i"

#### 4. Test sendmail manually, then from Hashtopolis

Manual test example:

```bash
printf "Subject: test\n\nhello" | /usr/sbin/sendmail you@example.com
```

Then test Hashtopolis-triggered mail events.

---

### Troubleshooting checklist

- /etc/ssmtp/ssmtp.conf exists inside backend container.
- emailSender and emailSenderName are set in Hashtopolis config.
- DNS/network from backend to relay is reachable.
- Relay accepts sender domain and authentication method.
- PHP can execute /usr/sbin/sendmail successfully.

---

### Gmail quick test example (no own SMTP server)

Use this section to test mail locally with a Gmail account.

#### Prerequisites

- Enable 2-Step Verification on your Google account.
- Create a Google App Password (do not use your normal Gmail password).

#### Example ssmtp.conf for Gmail

Create or update ssmtp.conf in your project root with values like:

```conf
root=youraddress@gmail.com
mailhub=smtp.gmail.com:587
rewriteDomain=gmail.com
UseTLS=Yes
UseSTARTTLS=Yes
AuthUser=youraddress@gmail.com
AuthPass=your_16_char_google_app_password
AuthMethod=LOGIN
FromLineOverride=yes
```

#### Mount the file in Docker Compose

Make sure the backend service has this volume enabled:

```yaml
services:
  hashtopolis-backend:
    volumes:
      - hashtopolis:/usr/local/share/hashtopolis:Z
      - ./ssmtp.conf:/etc/ssmtp/ssmtp.conf
```

#### Hashtopolis sender settings

In Hashtopolis server config:

- emailSender = youraddress@gmail.com
- emailSenderName = Hashtopolis Local Test

#### Local test flow

1. Restart the backend container.
2. Trigger a password reset email for a user with your Gmail address.
3. Check your inbox (and spam folder).

#### If Gmail test fails

- Verify AuthPass is an App Password, not your account password.
- Verify port 587 outbound is allowed from your Docker environment.
- Check backend container logs for mail/sendmail errors.
- Confirm /etc/ssmtp/ssmtp.conf exists inside the backend container.
