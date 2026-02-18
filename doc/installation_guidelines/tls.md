# SSL/TLS Setup

This page describes how to set up SSL for Hashtopolis.

## Generate x509 Certificate
First, create a folder where all persistent Hashtopolis files will be stored.

```bash

mkdir hashtopolis/
cd hashtopolis/

```

Next generate a self-signed certificate

```bash

openssl req -x509 -newkey rsa:2048 -keyout nginx.key -out nginx.crt -days 365 -nodes

```

## Setting up docker-compose and env.example

Refer to the [Basic installation](../installation_guidelines/basic_install.md) page on how to download those settings file. 

1. Edit docker-compose.yaml

Add the following new container to the `service:` section in the docker-compose.yaml.

```json
  nginx:
    container_name: nginx
    image: nginx:latest
    restart: always
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf:ro
      - ./nginx.crt:/etc/nginx/ssl/nginx.crt:ro
      - ./nginx.key:/etc/nginx/ssl/nginx.key:ro
    ports:
      - 443:443
      - 80:80
```

2. Create a *nginx.conf* file
Ensure that server_name matches your actual server name. If you changed container names in docker-compose.yaml, update them in the *nginx.conf* file accordingly.

```
events {
    worker_connections 1024;
}

http {

    server {
        listen 80;
        server_name localhost;
        return 301 https://$host$request_uri;
    }

    server {
        client_max_body_size 2G;
        listen 443 ssl http2;
        server_name localhost;

        ssl_certificate /etc/nginx/ssl/nginx.crt;
        ssl_certificate_key /etc/nginx/ssl/nginx.key;

        ssl_protocols TLSv1.2 TLSv1.3;
        ssl_prefer_server_ciphers on;
        ssl_ciphers HIGH:!aNULL:!MD5;

        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        location ~ (.*\.php) {
            proxy_pass http://hashtopolis-backend/$request_uri;
        }

        location /api {
            proxy_pass http://hashtopolis-backend/api;
        }

        location /static {
            proxy_pass http://hashtopolis-backend/static;
        }

        location / {
            proxy_pass http://hashtopolis-frontend;
        }
        location /legacy {
            proxy_pass http://hashtopolis-backend/index.php;
        }
    }
}
```

3. Update the value of `HASHTOPOLIS_BACKEND_URL` in the `.env` file to reflect the changes done above.

4. Start the containers
```

docker compose up

```
5. Visit hashtopolis at https://localhost/
