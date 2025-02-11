# SSL/TLS Setup
On this page the setup proces will be described howto setup SSL for Hashtopolis. Before you continue it is highly recommanded to read [Docker](docker.md).

## Generate x509 Certificate
First create a folder were we are going to store all of our hashtopolis persistent files.

```bash

mkdir hashtopolis/
cd hashtopolis/

```

Next generate a self signed certificate

```bash

openssl req -x509 -newkey rsa:2048 -keyout nginx.key -out nginx.crt -days 365 -nodes

```

## Setting up docker-compose and env.example

Please see the [Install](../installation_guidelines/basic_install.md) page on how to download those settings file. 

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

2. Create a nginx.conf

Make sure that the server_name reflects your real server name. If you have changed the container names inside your docker-compose file, make sure to reflect those changes inside the nginx.conf file below.

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
        listen 443 ssl;
        server_name localhost;

        ssl_certificate /etc/nginx/ssl/nginx.crt;
        ssl_certificate_key /etc/nginx/ssl/nginx.key;

        ssl_protocols TLSv1.2 TLSv1.3;
        ssl_prefer_server_ciphers on;
        ssl_ciphers HIGH:!aNULL:!MD5;

        location / {
            proxy_pass http://hashtopolis-frontend;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }

        location /api/v2 {
            proxy_pass http://hashtopolis-backend:80/api/v2;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }
    
        location /old {
            proxy_pass http://hashtopolis-backend/;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }
    }
}
```

3. Edit the `HASHTOPOLIS_BACKEND_URL` in `.env` to `https://localhost/api/v2` to reflect the changes done above. 

4. Start the containers
```

docker compose up

```
5. Visit hashtopolis on http://localhost/ the old ui is available via http://localhost/old