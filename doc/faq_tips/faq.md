# Questions and Answers

## Installation & Setup

  

❓ How do I install Hashtopolis?

**Answer**: To install Hashtopolis, you typically clone the GitHub repository, configure the backend using Apache/PHP/MySQL, and set up the frontend via a web browser. For step-by-step instructions, refer to the official documentation. Make sure your system meets the minimum requirements (Linux server, PHP 7.4+, MySQL/MariaDB, Apache/Nginx).

  

---

  

❓ Can I run Hashtopolis on a server already running something else (e.g. Homebridge)?

**Answer**: Yes, as long as the server has enough resources.

  

---

  

❓ How do I make the agent start automatically on Ubuntu?

**Answer**: To auto-start the agent on boot, create a `systemd` service file in `/etc/systemd/system/hashtopolis-agent.service` that runs the agent script with Python. Enable it using `systemctl enable hashtopolis-agent` and start it with `systemctl start hashtopolis-agent`. Ensure your agent configuration (`config.json`) is correctly set before enabling.

  

---



❓ How can I mount folders (import, files, binaries) to a local directory instead of using a Docker volume?

**Answer**: By default (when using the standard `docker-compose` setup), Hashtopolis stores folders like `import`, `files`, and `binaries` in a Docker volume. You can list this volume using `docker volume ls` and access it inside the container at `/usr/local/share/hashtopolis`.

To use host directories instead of Docker volumes, you can change the mount paths in your `docker-compose.yml` file like this:

```
version: '3.7'
services:
  hashtopolis-backend:
    container_name: hashtopolis-backend
    image: hashtopolis/backend:latest
    restart: always
    volumes:
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

Before recreating the containers, make sure to copy data out of the Docker volume:

```
docker cp hashtopolis-backend:/usr/local/share/hashtopolis <local-directory>
```

Then shut down and bring the containers back up:

```
docker compose down
docker compose up
```

Finally, copy your data back into the corresponding folders.




---



❓ How can I debug MySQL queries?

**Answer**: If you're encountering unusual issues and want to understand what queries Hashtopolis is executing on the database, you can enable query logging in MySQL.

Steps to do this inside a Docker container:

```
docker exec -it <db container name> /bin/bash
mysql -p
# Default password is: hashtopolis
SET GLOBAL general_log = 'ON';
SET GLOBAL sql_log_off = 'ON';
exit
cd /var/lib/mysql
tail -f *.log
```

This enables the general query log, which logs all incoming SQL statements. You can inspect these logs to trace how the application interacts with the database.




---



❓ Why does Hashtopolis fail and how can I debug errors?

**Answer**: Troubleshooting Hashtopolis can sometimes be challenging. A common error you might encounter is:

```
Error during speed benchmark, return code: 255 Output:
```

This usually means something is misconfigured in the Hashcat setup. To debug:

1. **Stop the Hashtopolis agent** (if running in a background process or screen).
    
2. **Restart the agent manually with the** `**--debug**` **flag**:
    
    ```
    python3 agent.py --debug
    ```
    
3. Look in the debug output for a line starting with `CALL:` — this shows the exact Hashcat command being executed.
    
4. **Navigate to the relevant cracker binary directory**:
    
    ```
    cd crackers/1/
    ```
    
    _(Note: Check the actual cracker ID used by your task in the Hashtopolis web UI under the Crackers section.)_
    
5. **Copy the** `**CALL:**` **command and remove**:
    
    - `--machine-readable`
        
    - `--quiet`
        
    - `-p "<tab>"` _(as tab characters can cause issues when copying)_
        
6. **Run the simplified Hashcat command manually** and check the terminal output. Example:
    
    ```
    ./hashcat.bin --progress-only --restore-disable --potfile-disable --session=hashtopolis -a3 ../../hashlists/2 ?l?l?l?l?l?l?a --hash-type=0 -o ../../hashlists/2.out
    ```
    

This should help reveal any specific errors or misconfigurations in the command line.




---



❓ Can I fake an agent for debugging the server API?

**Answer**: Yes, you can simulate an agent to test how the Hashtopolis server API behaves. This is especially useful for replicating hard-to-reproduce production issues.

1. **Ensure the agent is registered** at the server and has a valid token.
    
2. Use the following Python code to simulate agent-server interactions:
    

```
#!/usr/bin/python3
import requests

url = 'http://hashtopolis-server-dev/api/server.php'
token = 'token'  # Replace with your actual agent token
headers = {}

data_get_task = {
    'action': 'getTask',
    'token': token
}

response = requests.post(url, headers=headers, json=data_get_task)
task_id = response.json().get('taskId')

data_get_chunk = {
    'action': 'getChunk',
    'token': token,
    'taskId': task_id
}

response = requests.post(url, headers=headers, json=data_get_chunk)
status = response.json().get('status')

if status == 'keyspace_required':
    data_send_keyspace = {
        'action': 'sendKeyspace',
        'token': token,
        'taskId': task_id,
        'keyspace': 5000000
    }
    print(requests.post(url, headers=headers, json=data_send_keyspace).json())
elif status == 'benchmark':
    data_send_benchmark = {
        'action': 'sendBenchmark',
        'token': token,
        'taskId': task_id,
        'type': 'speed',
        'result': '674:674.74'
    }
    print(requests.post(url, headers=headers, json=data_send_benchmark).json())
elif status == 'OK':
    chunk_id = response.json().get('chunkId')
    data_send_progress = {
        'action': 'sendProgress',
        'token': token,
        'chunkId': chunk_id,
        'keyspaceProgress': 1,
        'relativeProgress': 1,
        'speed': 1000,
        'state': 3,
        'cracks': [['hash', 'plain', 'salt', '5']],
        "gpuTemp": ["0"],
        "gpuUtil": ["0"]
    }
    print(requests.post(url, headers=headers, json=data_send_progress).json())
```

This lets you debug API interactions manually without needing a live cracking job or agent setup.




---




❓ Is internet access required to run Hashtopolis?

**Answer**: No.

  

---

  

❓ Can I run Hashtopolis on ARM (e.g., Raspberry Pi)?

**Answer**: Not officially supported. ARM builds must be custom-built.

  

---

  

## Server Configuration & Issues

  

❓ Why does Apache show only a directory or a 500 error?

**Answer**: A 500 error or directory index display usually indicates PHP is either not installed, disabled, or misconfigured. Ensure that `libapache2-mod-php` is installed and enabled. Also, verify that your `php.ini` and `.htaccess` files don't contain invalid directives. Check Apache error logs at `/var/log/apache2/error.log` for more specific issues.

  

---

  

❓ How to fix a failed first login in Docker?

**Answer**: Check if the backend logs show “initialization successful”. Docker environment variables must be set correctly.

  

---

  

❓ How to upgrade Hashtopolis without data loss?

**Answer**: Back up the database, pull the latest version from Git, and apply the update through the upgrade feature.

  

---

  

❓ PHP Fatal error:  Allowed memory size of 268435456 bytes exhausted - What can I do now?

  
This guide shows how to raise PHP's memory limit in a Dockerized setup using a `custom.ini` file mounted into the PHP container. It also includes example `docker-compose.yml` snippets for common images.

1) **Create `custom.ini` next to your `docker-compose.yml`**

Add your PHP overrides (uppercase `M` is conventional, PHP is case-insensitive here):

  
```ini

; custom.ini

memory_limit = 256M

upload_max_filesize = 256M

max_execution_time = 60

```

- Adjust `memory_limit` to your needs (e.g., `512M`, `1G`).

- The other two values are optional and not directly related to the memory issue.

 2) **Mount `custom.ini` into the PHP container**

  Add this to your **backend** service’s `volumes:` list in `docker-compose.yml`:


```yaml

- ./custom.ini:/usr/local/etc/php/conf.d/custom.ini

```

  
> The path `/usr/local/etc/php/conf.d/` is correct for official PHP images (`php:*`).

  

 3) **Recreate or restart the container**

Make sure the container reloads the INI:

  

```bash

# Start or recreate after changes

docker compose up -d

  

# or, if the stack is already running and only the ini changed:

docker compose restart backend

```


**Done!** Your PHP container will now use the memory settings from `custom.ini`.




---



## Hashcat Questions

  

❓ Is `--increment` supported?

**Answer**: No, not directly. Workaround: create individual masks or use “Import Supertask” for manual mask input.

  

---

  

❓ Can Hashtopolis use custom Hashcat builds?

**Answer**: Yes, upload them through the admin interface as separate binaries.

  

---

  

❓ What if a client only uses one of multiple GPUs?

**Answer**: This is likely due to small chunk size or a single hash. Larger workloads will utilize more GPUs.

  

---

  

❓ How do you deal with huge wordlists (e.g. 20–50 GB)?

**Answer**: Split files, SCP them to the server or serve them via Python’s HTTP server.

  

---

  

## Tasks & Distribution

  

❓ How are tasks split across clients?

**Answer**: Based on keyspace ranges (e.g. Client A: AAAA–BBBB, Client B: CCCC–DDDD).

  

---

  

❓ Can I assign specific agents to specific users or tasks?

**Answer**: Admins can manage this in task settings or manually configure allowed agents.

  

---

  

❓ How are tasks prioritized?

**Answer**: Tasks are prioritized numerically.

  

---

  

## Interface & Features

  
❓ Does Hashtopolis support notifications (e.g. Telegram, Discord)?

**Answer**: Yes, Discord and Telegram bot notifications are supported but require manual setup.

  

---

  

## File Management

  

❓ Can large wordlists be remotely deleted from agents?

**Answer**: Requires manual script or reconfiguration.

  

---

  

## Troubleshooting & Performance

  

❓ Why is only 4 GB of VRAM used on a 10 GB RTX 3080?

**Answer**: Hashcat uses only as much memory as needed. More memory ≠ more speed.

  

---

  

❓ What are “zaps” in status logs?

**Answer**: Notification that another client already cracked a hash, allowing the client to skip it.

  

---

  

## Security & Access Control

  

❓ Is there a way to trust all agents by default?

**Answer**: No, but there’s an open feature request for it: [GitHub Issue #721](https://github.com/hashtopolis/server/issues/721)

  

---

  

❓ Can an API token be shared across multiple agents?

**Answer**: Yes, using the same token is fine for basic usage.

  

---