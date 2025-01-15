# Installation Guidelines (Work in Progress)
## Basic installation
### Server installation
This guide details installing Hashtopolis using Docker, the recommended method since version 0.14.0. Docker offers a faster, more consistent setup process.
#### Prerequisites:

> [!NOTE]
> The instructions provided in this section have only been tested on Ubuntu 22.04 and Windows 11 with WSL2.

To install Hashtopolis server, ensure that the following prerequisites are met:
1. Docker: Follow the instructions available on the Docker website:
   - Install Docker on Ubuntu
   - Install Docker on Windows
2. Docker Compose v2: Follow the instructions available on the Docker website:
   - Install Docker Compose on Linux

#### Setup Hashtopolis Server
The official Docker images can be found on Docker Hub at: https://hub.docker.com/u/hashtopolis. Two Docker images are needed to run Hashtopolis: hashtopolis/frontend (setting up the web user interface), and hashtopolis/backend (taking care of the Hashtopolis database).

A docker-compose file allowing to configure the docker containers for Hashtopolis is available in this repository. Here are the steps to follow to run Hashtopolis using that docker-compose file:

1. Create a folder and change into the folder
``` 
mkdir hashtopolis
cd hashtopolis
```
2. Download docker-compose.yml and env.example    
```
wget https://raw.githubusercontent.com/hashtopolis/server/master/docker-compose.yml 
wget https://raw.githubusercontent.com/hashtopolis/server/master/env.example -O .env
```   
3. Edit the .env file and change the settings to your likings.   
```
nano .env
```   
4. Start the containers:   
```
docker compose up --detach
```   
5. Access the Hashtopolis UI through: http://127.0.0.1:8080 using the credentials (user=admin, password=hashtopolis)
6. If you want to play around with a preview of the version 2 of the UI, consult the New user interface: technical preview section.

#### New user interface: technical preview

> [!NOTE]
> The APIv2 and UIv2 are a technical preview. Currently, when enabled, everyone through the new API will be fully admin!

To enable 'version 2' of the API:

1. Stop your containers

2. set the HASHTOPOLIS_APIV2_ENABLE to 1 inside the .env file.

3. Relaunch the containers
   ```
   docker compose up --detach
   ```

4. Access the technical preview via: http://127.0.0.1:4200 using the credentials user=admin and password=hashtopolis, unless modified in the .env file.

### Agent installation
#### Prerequisites
To install the agent, ensure that the following prerequisites are met:
1. Python: Python 3 must be installed on the agent system. You can verify the installation by running the following command in your terminal:
   ```
   python3 --version
   ```
   If Python 3 is not installed, refer to the official Python installation guide.
2. Python Packages: The Hashtopolis agents depends on the following Python packages:
   - requests
   - psutil

[***To be checked***]
It is recommended to use a virtual environment for installing the required packages to avoid conflicts with system-wide packages. You can create and activate a virtual environment with the following commands:
```
python3 -m venv hastopolis_env
source hashtopolis_env/bin/activate
```

Then, install the packages:
```
pip install requests psutil
```

#### Download the Hashtopolis agent
1. Connect to the Hashtopolis server: http://<server-ip-address>:8080 and log in. Navigate to the Agents tab > New Agent. 
2. From that page, you can either download the agent by clicking on the Download button, or copy and paste the provided url to download the agent using wget/curl:
   ```
   curl -o hastopolis.zip "http://<server-ip-address>:8080/agents.php?download=1"
   ```

#### Start and register a new agent

1. Activate your python virtual environment if not done before:   
   ```
   source hashtopolis_env/bin/activate
   ```   
2. Start the agent:   
   ```
   python hashtopolis.zip
   ```

3. When prompted, provide the URL to the server API as provided in the Agents page of Hashtopolis (http://<server-ip-address>:8080/api/server.php).   
   ```
   Starting client 's3-python-0.7.2.4'...
   Please enter the url to the API of your Hashtopolis installation:
   http://localhost:8080/api/server.php
   ```   
4. On the server Agents page of Hashtopolis, create a new Voucher and copy it.
5. Register the agent by providing the newly created token.   
   ```
   No token found! Please enter a voucher to register your agent:
   peKxylVY
   Successfully registered!
   Collecting agent data...
   Login successful!
   ```

Your agent is now ready to receive new tasks. If you wish to finetune the configuration of your agent, please consult the section related to the agent configuration file or the command line arguments in the Advanced installation section. Otherwise, to start using Hashtopolis, consult the Basic workflow section.

## Advanced installation
- Installation in an airgapped/offline/oil-gapped system (make a note about the binary)
- Installation with local folders
- Installation of TLS X.509 certificate
- Agent configuration file and command line arguments
- (Boot from PXE) and run HtP as a service (voucher, local disk,...)
- Misc.
- Upgrade of the install
