# What is Hashtopolis?

<!-- > [!CAUTION]
> This is the new documentation of Hashtopolis. It is work in progress, so use with care!
>
> You can find the old documentation still inside this folder, please check the [Hashtopolis Communication Protocol (V2)](protocol.pdf) docs. The user api documentation can be found here: [Hashtopolis User API (V1)](user-api/user-api.pdf). -->

**Hashtopolis** is an open-source platform designed to distribute and manage password cracking tasks across multiple machines. 
Password cracking is a *pleasantly parallel* problem, meaning it can be divided into many independent subtasks that run simultaneously without needing to communicate with each other. Each agent can work on a different portion of the attack without waiting for others. This makes cracking highly scalable: the more resources you have, the faster the overall process will run. Hashtopolis takes full advantage of this by coordinating multiple agents to work in parallel, maximizing resource utilization and significantly reducing cracking time.

## Objectives and Purpose

Hashtopolis is built to:

- **Centralize password cracking** management through a user-friendly web interface available in both dark and light themes.
- **Efficiently distribute** workloads to multiple agents, locally or over a network, taking into account heterogeneous hardware configurations.
- **Support various cracking tools**, primarily designed for **Hashcat**, as well as custom attack strategies.
- Allow **easy monitoring**, **task automation**, and result collection in large-scale environments.
- **Centralized management** of files (e.g. wordlists, rules,...) as well as binaries update and distribution.
- Support **multi-user environment** with configurable permission levels.


## How It Works – In a Nutshell

Hashtopolis operates on a **client-server architecture**:

- The **server** hosts the web interface and database, serving as the central hub where users upload hashes and files, configure cracking tasks, and monitor overall progress. It distributes all necessary files, hashlists, and binaries to agents, centralizes their cracking progress, and collects the recovered passwords. The server runs on PHP and uses MySQL as its database backend.

- The **agents** are lightweight Python clients installed on various computing resources. They communicate with the server by requesting work, execute cracking tasks using Hashcat, and report results back to the server.

A detailed [Basic Workflow](./user_manual/basic_workflow.md) section is available for new users providing step-by-step guidance on how to operate Hashtopolis. Here, we provide a concise overview of what happens once a hash or hashlist is uploaded and a cracking task is created:

1. Agents that currently have no assigned work send requests to the server via API calls asking for new tasks.

2. The server assigns these agents to the highest-priority task for which they have the necessary permissions.

3. Before cracking begins, the server initiates a keyspace calculation for the assigned task. This calculation is performed by one or more agents assigned to the task. A few points to note:
    - Ideally, only one agent would perform this calculation since the result is always the same. However, having multiple agents perform it prevents idle time if a single agent fails.
    - The concept of **keyspace** in Hashcat differs from the traditional definition. See the [Hashcat Wiki](https://hashcat.net/wiki/doku.php?id=frequently_asked_questions#what_is_a_keyspace) for more information.
    Briefly, Hashcat’s `--keyspace` option is designed to optimize workload distribution rather than represent the exact total keyspace. If you know the idea of "base" and "amplifier," Hashcat’s keyspace command outputs the size of the base, whereas the traditional keyspace is base × amplifier.

4. After the keyspace is known, each agent runs a benchmark for the task to determine how quickly it can process its assigned workload.

5. Using the benchmark results and the task’s keyspace, the server calculates the size of the **chunk** — a portion of the keyspace assigned to an agent. This chunk size aligns with the task’s configured "*chunk size*" parameter, which specifies how long an agent should work uninterrupted on a chunk.

6. Once an agent completes its assigned chunk — either by cracking all possible passwords in that portion or exhausting the keyspace — it requests new work from the server. If the task still has remaining work and remains the highest-priority task, the server assigns the next chunk to that agent.

## Contribution Guidelines
We are open to all kinds of contributions. If it's a bug fix or a new feature, feel free to create a pull request. Please consider the following points:

- Include one feature or one bugfix in one pull request;
- Try to follow the existing code style (especially for PHP). IntelliJ/PHPStorm users can import the style-XML provided.

The pull request will then be reviewed by at least one member and merged after approval. Don’t be discouraged if your pull request isn’t accepted immediately, most requested changes are minor.


## What to expect from the manual?

This manual aims to describe all the functionalities and settings existing in Hashtopolis. In particular, you can find the following sections:

- [**Basic Workflow**](./user_manual/basic_workflow.md): Tailored for new users unfamiliar with Hashtopolis. It describes the most important features to know in order to have your first tasks running.
- [**Installation Guidelines**](./installation_guidelines/basic_install.md): Covers basic installation steps to deploy a Hashtopolis instance. It also contains advanced installation procedures for air-gapped environments, HTTPS configuration, as well as many other advanced features.
- [**User Manual**](./user_manual/agents.md): goes deeper than the basic workflow into each aspect of Hashtopolis. This aims to cover all the existing features and settings. 
- [**FAQ and Tips**](./faq_tips/faq.md): gathers most of the questions that were asked on different channels (discord, wiki, etc.).
- [**API Reference**](./api.md): contains all the details related to the API in case you need to automate some processes or want to develop your own front end. 