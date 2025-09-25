# Basic installation
## Server installation

This guide explains how to install Hashtopolis using Docker.

### Prerequisites:

> [!NOTE]
> The instructions provided in this section have only been tested on Ubuntu 22.04 and Windows 11 with WSL2.

To install Hashtopolis server, ensure that the following prerequisites are met:

1. Docker: Follow the instructions available on the Docker website:

   - [Install Docker on Ubuntu](https://docs.docker.com/engine/install/ubuntu/)
   - [Install Docker on Windows](https://docs.docker.com/desktop/setup/install/windows-install/#:~:text=needing%20administrator%20privileges.-,Install%20interactively,Program%20Files%5CDocker%5CDocker%20.)

2. Docker Compose v2: Follow the instructions available on the Docker website:

   - Install Docker Compose on Linux

### Setup Hashtopolis Server
The official Docker images can be found on [Docker Hub](https://hub.docker.com/u/hashtopolis). To run Hashtopolis, you need two main images:

- *hashtopolis/frontend*: provides the web user interface
- *hashtopolis/backend*: handles background processing and communicates with the database (managed by a separate MySQL container)

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

5. Access the Hashtopolis UI through: ```http://127.0.0.1:8080``` using the credentials set in the *.env* file, default are user=admin and password=hashtopolis.


## Agent installation

### Prerequisites

To install the agent, ensure that the following prerequisites are met:

1. Python: Python 3 must be installed on the agent system. If Python 3 is not installed, refer to the official Python installation guide. You can verify the installation by running the following command in your terminal:

```
python3 --version
```

2. Python Packages: The Hashtopolis agents depends on the following Python packages:

   - requests
   - psutil

It is recommended to use a virtual environment for installing the required packages to avoid conflicts with system-wide packages. You can create and activate a virtual environment with the following commands:

```
python3 -m venv hashtopolis_env
source hashtopolis_env/bin/activate
```

Then, install the packages:
```
pip install requests psutil
```

### Download the Hashtopolis agent

1. Connect to the Hashtopolis server: ```http://<server-ip-address>:8080``` and log in. Navigate to the page *Agents > Show Agents* and click on the button *'+ New Agent'*. 
2. On that page you can click on "..." and choose to download the agent binary or copy the URL of the agent binary and download the agent using wget/curl:

```
curl -o hashtopolis.zip "http://<server-ip-address>:8080/agents.php?download=1"
```

or

```
wget --content-disposition "http://<server-ip-address>:8080/agents.php?download=1"
```

### Start and register a new agent

1. Activate your python virtual environment if not done before:   
```
source hashtopolis_env/bin/activate
```   
2. Start the agent:   
```
python hashtopolis.zip
```

3. When prompted, provide the URL to the server API as provided in the *+ New Agents* wizard of Hashtopolis (```http://<server-ip-address>:8080/api/server.php```).   
```
Starting client 's3-python-0.7.2.4'...
Please enter the url to the API of your Hashtopolis installation:
http://localhost:8080/api/server.php
```   
4. In the *+ New Agents* wizard of Hashtopolis, create a new Voucher and copy it.
5. Register the agent by providing the newly created token.   
```
No token found! Please enter a voucher to register your agent:
peKxylVY
Successfully registered!
Collecting agent data...
Login successful!
```

Your agent is now ready to receive new tasks. If you wish to fine-tune the configuration of your agent, please consult the [Agent Settings section](../user_manual/settings_and_configuration.md#agent-settings) or the specific parameters within the [agent overview](../user_manual/agents.md#agent-overview) page. Otherwise, to start using Hashtopolis, consult the [Basic workflow section](../user_manual/basic_workflow.md).
