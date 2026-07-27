# Questions and Answers

## Installation & Setup

<span style="font-size:1.2em; font-weight:bold;">❓ How do I install Hashtopolis? </span>

The easiest way to install Hashtopolis is with the Docker images that can be retrieved from [Docker Hub](https://hub.docker.com/u/hashtopolis).
Follow the instructions from the [documentation](../installation_guidelines/basic_install.md).

---

<span style="font-size:1.2em; font-weight:bold;">❓ Can I run Hashtopolis on a server already running something else (e.g. Homebridge)?</span>

Yes, as long as the server has enough resources.

---

<span style="font-size:1.2em; font-weight:bold;">❓ How do I make the agent start automatically on Ubuntu?</span>

To auto-start the agent on boot, create a `systemd` service file in `/etc/systemd/system/hashtopolis-agent.service` that runs the agent script with Python, for example:

```
[Unit]
Description=Hashtopolis Agent
After=network.target
StartLimitIntervalSec=0

[Service]
Type=simple
Restart=always
RestartSec=10
User=root
ExecStart=/usr/bin/python3 /root/agents/hashtopolis.zip
StandardInput=tty-force
WorkingDirectory=/root/agents/

[Install]
WantedBy=multi-user.target
```

Make sure you adjust the running user and paths to your needs.
Reload the configs with `systemctl daemon-reload`.
Enable it using `systemctl enable hashtopolis-agent` and start it with `systemctl start hashtopolis-agent`.
Ensure your agent configuration (`config.json`) is correctly set before enabling.

---

<span style="font-size:1.2em; font-weight:bold;">❓ How can I mount folders (import, files, binaries) to a local directory instead of using a Docker volume?</span>

By default (when using the standard `docker compose` setup), Hashtopolis stores folders like `import`, `files`, and `binaries` in a Docker volume.
You can list this volume using `docker volume ls` and access it inside the container at `/usr/local/share/hashtopolis`.

To use host directories instead of Docker volumes, you can change the mount paths in your `docker-compose.yml` file like this (in this example the folders would then be mounted into `/opt/hashtopolis/` on the host:

```
version: '3.7'
services:
  hashtopolis-backend:
    container_name: hashtopolis-backend
    image: hashtopolis/backend:latest
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
    depends_on:
      - hashtopolis-backend
    ports:
      - 4200:80
volumes:
  db:
```

Before recreating the containers, make sure to copy data out of the Docker volume (if you have already used Hashtopolis with the volume):
```
docker cp hashtopolis-backend:/usr/local/share/hashtopolis /opt/hashtopolis
```

Then shut down and bring the containers back up:
```
docker compose down
docker compose up -d
```

---

<span style="font-size:1.2em; font-weight:bold;">❓ How can I debug MySQL queries?</span>

If you're encountering unusual issues and want to understand what queries Hashtopolis is executing on the database, you can enable query logging in MySQL.

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

This enables the general query log, which logs all incoming SQL statements. 
You can inspect these logs to trace how the application interacts with the database.

> [!Caution]
> Activating logging can increase the load on your MySQL server and when being active over a longer time, it can fill up your log file to large sizes quickly!

---

<span style="font-size:1.2em; font-weight:bold;">❓ Why does Hashtopolis fail and how can I debug errors?</span>

Troubleshooting Hashtopolis can sometimes be challenging. A common error you might encounter is:

```
Error during speed benchmark, return code: 255 Output:
```

This usually means something is misconfigured in the Hashcat setup. To debug:

1. **Stop the Hashtopolis agent** (if running in a background process or screen).

2. **Restart the agent manually with the** `--debug` **flag**:

```
python3 agent.py --debug
```

3. Look in the debug output for a line starting with `CALL:` — this shows the exact Hashcat command being executed.

4. **Navigate to the relevant cracker binary directory**:

```
cd crackers/1/
```

> [!Note]
> Check the actual cracker ID used by your task in the Hashtopolis web UI under the Crackers section.)

5. **Copy the** `CALL:` **command and remove**:
    - `--machine-readable`
    - `--quiet`
    - `-p "<tab>"` _(as tab characters can cause issues when copying)_

6. **Run the simplified Hashcat command manually** and check the terminal output. Example:

```
./hashcat.bin --progress-only --restore-disable --potfile-disable --session=hashtopolis -a3 ../../hashlists/2 ?l?l?l?l?l?l?a --hash-type=0 -o ../../hashlists/2.out
```

This should help reveal any specific errors or misconfigurations in the command line.

---

<span style="font-size:1.2em; font-weight:bold;">❓ Can I fake an agent for debugging the server API?</span>

Yes, you can simulate an agent to test how the Hashtopolis server API behaves. This is especially useful for replicating hard-to-reproduce production issues.

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

<span style="font-size:1.2em; font-weight:bold;">❓ Is internet access required to run Hashtopolis?</span>

No.

> [!Note]
> By default Hashtopolis tries to retrieve hashcat from their official download URL on hashcat.net.
> If you run Hashtopolis in an offline environment, you need to adjust the download URL in the cracker settings.

---

<span style="font-size:1.2em; font-weight:bold;">❓ Can I run Hashtopolis on ARM (e.g., Raspberry Pi)?</span>

It is not officially supported and there are no pre-built docker images available. ARM builds must be custom made.

---
<span style="font-size:1.2em; font-weight:bold;">❓ Can I use a custom layout and how can I embed it?</span>

**Custom Themes**

Drop `.css` files in this folder to register custom runtime themes.

No additional JSON or TypeScript changes are required for standard theme creation.

For a complete authoring guide, see **Custom Theme Authoring Guide**.

This folder (https://github.com/hashtopolis/web-ui/tree/master/custom-themes) ships two example themes — `rainbow.css` and `fallout.css`.

**Enabling custom themes (opt-in)**

No custom theme is loaded by default. The built-in `light` and `dark` themes are always available; everything in this folder is opt-in.

`HASHTOPOLIS_CUSTOM_THEMES_DIR` is the on/off switch: it names the folder the container scans for `.css` files on startup. When it is unset, no custom themes are loaded and the app uses the built-in `light`/`dark` themes only.

To enable themes with Docker Compose, set the variable to the container path the entrypoint should scan and mount your `.css` folder to that same path by uncommenting both lines in `docker-compose.yml` (or `.devcontainer/docker-compose.yml`):

```yaml
    environment:
      - HASHTOPOLIS_CUSTOM_THEMES_DIR=/custom-themes
    volumes:
      - ./custom-themes:/custom-themes:ro
```

**Baking themes into the image (no runtime mount)**

When building your own production image, pass the `CUSTOM_THEMES_DIR` build arg to copy a folder of `.css` files into the image (the default bakes nothing):

```shell
docker build --target hashtopolis-web-ui-prod \
  --build-arg CUSTOM_THEMES_DIR=custom-themes -t hashtopolis/web-ui .
```

Baked themes are still opt-in at runtime: set `HASHTOPOLIS_CUSTOM_THEMES_DIR=/custom-themes` to enable them.

**Naming**

- File names are converted to a theme id using lowercase kebab-case.
- Example: `Ocean Glow.css` becomes `ocean-glow`.

**Runtime behavior**

- When `HASHTOPOLIS_CUSTOM_THEMES_DIR` is set, `docker-entrypoint.sh` scans that folder on container startup (otherwise no custom themes are loaded).
- Valid CSS files are copied to `/assets/custom-themes/`.
- A manifest is generated at `/assets/themes/custom-themes.json`.
- The frontend reads the manifest and adds the themes to the selectors.
- Theme menu icons are currently auto-set to `style` for custom themes.
- A theme whose CSS declares `color-scheme: dark` is flagged dark, so the app uses its dark-mode logo, icon colors, and chart palettes.

**Reserved IDs**

These ids are built-in and cannot be overridden by custom files:

- `light`
- `dark`

**Need help generating a theme?**

Use the prompt template at the end of **Custom Theme Authoring Guide** with your favorite LLM.

**Custom Theme Authoring Guide**

This project supports runtime custom themes from CSS files.

For normal usage, custom theme creation is intentionally low-complexity: add one CSS file and recreate the container.

**1) Add a CSS file**

Place a `.css` file in the `custom-themes` folder.
Examples:

- `custom-themes/ocean.css`
- `custom-themes/cyber-night.css`

The file name is converted to the theme id using lowercase kebab-case.

- `Ocean Night.css` -> `ocean-night`

**2) Define your theme scope**


Use the generated id as a body class in the format `.<theme-id>-theme`.

Example for `ocean.css`:

```css
.ocean-theme {
  --background: #07131f;
  --foreground: #d9f2ff;
  --primary: #4db5ff;
  --border: rgba(77, 181, 255, 0.35);
  color-scheme: dark;
}
```

At minimum, define the color tokens your components rely on.

**3) Recreate container**

Custom themes are opt-in. Set `HASHTOPOLIS_CUSTOM_THEMES_DIR` to the folder the container should scan and mount your `.css` folder to that path (see `custom-themes/README.md`). When the container starts, `docker-entrypoint.sh` scans that folder, copies CSS files into served assets, and generates a manifest.

Apply changes with:

```shell
docker compose up -d --build --force-recreate
```

If your stack is already running and mounted correctly, a restart may be enough:

```shell
docker compose restart
```

**4) Verify in browser**

Open:

- `/assets/themes/custom-themes.json`
- `/assets/custom-themes/<theme-id>.css`

If both are available, the theme appears in theme selectors.

**Notes**

- Custom theme ids must match `^[a-z0-9-]+$`.
- Built-in ids are reserved and ignored in custom manifest: `light`, `dark`.
- Use absolute URLs in CSS for assets, for example `/assets/img/...`.
- Custom menu icons are currently generated as `style` by default.
- Declare `color-scheme: dark` for a dark theme so the app picks dark-mode logos, icon colors, and charts.
- Do not add JavaScript or TypeScript to create a theme unless you are extending platform behavior.

**LLM Prompt Template**

Copy and adapt this prompt when asking an LLM to generate a new theme CSS file.

```
Generate a single CSS file for a Hashtopolis custom theme.

Requirements:
- Output CSS only, no markdown.
- Use this class selector exactly: .<theme-id>-theme
- Define all variables below with visually coherent values:
  --background
  --muted
  --well
  --card
  --card-hover
  --input
  --sidebar
  --foreground
  --muted-foreground
  --subtle-foreground
  --heading-foreground
  --primary
  --primary-hover
  --primary-muted
  --primary-foreground
  --secondary
  --border
  --border-strong
  --border-faint
  --surface-faint
  --surface-soft
  --surface-soft-hover
  --cell-hover
  --success
  --warning
  --destructive
  --info
  --link
  --link-hover
  --success-bg
  --destructive-bg
  --info-bg
  --header
  --shell-frame-image
  --brand-backdrop
  --shadow-sm
  --shadow-md
  --shadow-lg
  --gradient-accent
- Include color-scheme: dark; or color-scheme: light; to match the palette.
- Keep contrast accessible for body text and headings.
- Avoid changing any selector outside .<theme-id>-theme.

Theme request:
- Theme id: <theme-id>
- Mood/style: <describe visual direction>
- Preferred primary hue: <color>
- Dark or light: <dark|light>
```

**Example Output**

This is the kind of CSS structure the prompt should generate.

```css
.ocean-night-theme {
  --background: #06131f;
  --muted: #0d1d2c;
  --well: #10263a;
  --card: rgba(255, 255, 255, 0.05);
  --card-hover: rgba(255, 255, 255, 0.09);
  --input: rgba(255, 255, 255, 0.06);
  --sidebar: #081520;

  --foreground: #e6f4ff;
  --muted-foreground: rgba(230, 244, 255, 0.75);
  --subtle-foreground: rgba(230, 244, 255, 0.55);
  --heading-foreground: #ffffff;

  --primary: #4db5ff;
  --primary-hover: #7ac8ff;
  --primary-muted: rgba(77, 181, 255, 0.2);
  --primary-foreground: #04111b;
  --secondary: #7df9ff;

  --border: rgba(77, 181, 255, 0.25);
  --border-strong: rgba(77, 181, 255, 0.45);
  --border-faint: rgba(77, 181, 255, 0.15);

  --surface-faint: rgba(255, 255, 255, 0.03);
  --surface-soft: rgba(255, 255, 255, 0.06);
  --surface-soft-hover: rgba(255, 255, 255, 0.1);

  --cell-hover: rgba(77, 181, 255, 0.16);

  --success: #76e39b;
  --warning: #ffd26a;
  --destructive: #ff7f96;
  --info: #7df9ff;
  --link: #7df9ff;
  --link-hover: #b7fdff;

  --success-bg: color-mix(in oklch, var(--success) 18%, var(--well));
  --destructive-bg: color-mix(in oklch, var(--destructive) 18%, var(--well));
  --info-bg: color-mix(in oklch, var(--info) 18%, var(--well));

  --header: rgba(6, 19, 31, 0.72);
  --shell-frame-image: linear-gradient(135deg, rgba(77, 181, 255, 0.18), rgba(125, 249, 255, 0.08));
  --brand-backdrop: #4db5ff;

  --shadow-sm: none;
  --shadow-md: 0 3px 12px rgba(3, 11, 20, 0.42);
  --shadow-lg: 0 10px 30px rgba(3, 11, 20, 0.58);

  --gradient-accent: linear-gradient(130deg, #4db5ff 0%, #7df9ff 100%);

  color-scheme: dark;
}
```
---

## Server Configuration & Issues

<span style="font-size:1.2em; font-weight:bold;">❓ Why does Apache show only a directory or a 500 error?</span>

A 500 error or directory index usually indicates PHP is either not installed, disabled, or misconfigured.
Ensure that `libapache2-mod-php` is installed and enabled.
Also, verify that your `php.ini` and `.htaccess` files don't contain invalid directives.
When encountering 500 Internal Server Errors, check Apache error logs at `/var/log/apache2/error.log` for information about the error.

---

<span style="font-size:1.2em; font-weight:bold;">❓ How to fix a failed first login in Docker?</span>

Check if the backend logs show `initialization successful`. 
Docker environment variables must be set correctly (e.g. by using the example given in `env.example`.

---

<span style="font-size:1.2em; font-weight:bold;">❓ How to upgrade Hashtopolis without data loss?</span>

If you run Hashtopolis in a dockerized setup with docker-compose, all the data which should be persistent is stored in volumes or mounted into the containers.
In this case you simply can pull the newest images with `docker compose pull` and then recreate them with `docker compose up -d`.

In case you run a setup directly on a server, back up the database, pull the latest version from Git. 
When accessing Hashtopolis the first time afterwards, the required updates are executed automatically.

---

<span style="font-size:1.2em; font-weight:bold;">❓ PHP Fatal error:  Allowed memory size of 268435456 bytes exhausted - What can I do now?</span>

If there is enough RAM available, it is possible to raise PHP's memory limit in a dockerized setup using a `custom.ini` file mounted into the Hashtopolis container.

1. **Create a file `custom.ini` next to your `docker-compose.yml`**

    Adjust your desired memory limit (`M` for Megabytes, or `G` for Gigabytes).
    The other three values are optional to adjust, but need to remain in there, as otherwise they are overwritten with the new `custom.ini` not containing them.


```ini
; custom.ini
memory_limit = 256M
upload_max_filesize = 256M
max_execution_time = 60
display_errors = 0
```


2. **Mount `custom.ini` into the PHP container**

    Add this to your hashtopolis-backend service’s `volumes:` list in `docker-compose.yml`:

```yaml
...
  volumes:
    - ./custom.ini:/usr/local/etc/php/conf.d/custom.ini
...
```

3. **Recreate the container**

    Trigger a recreation and updating the volumes setting:

```bash
docker compose up -d
```

---

## Hashcat Questions

<span style="font-size:1.2em; font-weight:bold;">❓ Is `--increment` supported?</span>

No, Hashcat internally handles `--increment` the same way as if it would be multiple tasks executed after each other. 
Due to this it does not allow to manage it as a single task as Hashtopolis would need it.

To work around this: create individual brute force tasks with each of the masks of the different lengths (either manually or use `Import Supertask`).

---

<span style="font-size:1.2em; font-weight:bold;">❓ Can Hashtopolis use custom Hashcat builds?</span>

Yes. In order to add a new one, add it as a new version and provide a download link where it is served to the agents as HTTP download.

---

<span style="font-size:1.2em; font-weight:bold;">❓ What if a client only uses one of multiple GPUs?</span>

This is likely due to small chunk size or a suboptimal task. Larger workloads will utilize more GPUs.

---

<span style="font-size:1.2em; font-weight:bold;">❓ How do you deal with huge wordlists (e.g. 20–50 GB)?</span>

Copy the files into the import folder of the server and then import them or add them via a HTTP download link.

---

## Tasks & Distribution

<span style="font-size:1.2em; font-weight:bold;">❓ How are tasks split across clients?</span>

Based on keyspace ranges (e.g. Client A: AAAA–BBBB, Client B: CCCC–DDDD for a bruteforce task).

---

<span style="font-size:1.2em; font-weight:bold;">❓ Can I assign specific agents to specific users or tasks?</span>

Admins can manage this by creating groups of users and agents to manage which users can access which tasks and which agents can work on them.

---

<span style="font-size:1.2em; font-weight:bold;">❓ How are tasks prioritized?</span>

Tasks are prioritized numerically.

---

## Interface & Features

<span style="font-size:1.2em; font-weight:bold;">❓ Does Hashtopolis support notifications (e.g. Telegram, Discord)?</span>

Yes, Discord and Telegram bot notifications are supported but require manual setup.

---

## File Management

<span style="font-size:1.2em; font-weight:bold;">❓ Can large wordlists be remotely deleted from agents?</span>

Requires manual deletion on the agent side if they are not deleted on the server side.

---

## Troubleshooting & Performance

<span style="font-size:1.2em; font-weight:bold;">❓ Why is only 4 GB of VRAM used on a 10 GB RTX 3080?</span>

Hashcat uses only as much memory as needed. More memory ≠ more speed.

---

<span style="font-size:1.2em; font-weight:bold;">❓ What are `zaps` in status logs?</span>

Zaps are notification to agents that another agent already cracked a hash, allowing the agent to remove it from its own left list.

---

## Security & Access Control

<span style="font-size:1.2em; font-weight:bold;">❓ Is there a way to trust all agents by default?</span>

No, but there’s an open feature request for it: [GitHub Issue #721](https://github.com/hashtopolis/server/issues/721)

---

<span style="font-size:1.2em; font-weight:bold;">❓ Can a User API token be shared across multiple users?</span>

Yes, using the same token is fine for basic usage.

---
