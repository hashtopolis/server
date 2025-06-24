# Settings and Configuration

> [!NOTE]
> This page presents the settings following the structure of the updated front-end. All the settings from the old front-end are described but potentially structured in a different order.

## Agent Settings

### Activity / Registration

- **Inactivity Timeout Delay**:  Duration (in seconds) after which an agent is considered inactive if no communication is received.

- **Inactivity Timeout for Issued Chunks**:  Time allowed for an agent to process and report back on an assigned chunk before it is considered failed or timed out.

- **Task Reporting Frequency**:  Interval (in seconds) at which agents report their task progress and status back to the server.

- **Retention Period for Utilization and Temperature Data**:  Duration for which the agent’s utilization and GPU temperature statistics are stored before being purged.

- **Agent IP Information Privacy**:  Controls whether the IP addresses of agents are stored or displayed for privacy reasons.

- **Register Multiple Agents Using Voucher(s)**:  Allows bulk registration of multiple agents using one or more voucher codes for automated onboarding.

### Graphical Feedback

- **Maximum Data Points for Agent (GPU) Graphs**:  Maximum number of data points shown on utilization and temperature graphs per agent GPU.

- **Straight Lines or Bezier Curves for Agent Data Graphs**:  Choose the graph style to render agent metrics: straight line segments or smooth bezier curves.

- **Orange Status Threshold for Agent Temperature**:  Temperature (in °C) at which the agent GPU temperature graph changes to orange as a warning.

- **Red Status Threshold for Agent Temperature**:  Temperature (in °C) at which the agent GPU temperature graph changes to red indicating critical heat.

- **Orange Status Threshold for Agent Utilization**:  Utilization percentage at which the graph turns orange signaling moderate load.

- **Red Status Threshold for Agent Utilization**:  Utilization percentage triggering red status indicating high or potentially problematic load.

## Task/Chunks Settings

### Benchmark / Chunk

- **Expected Chunk Duration**:  Target duration (in seconds or minutes) for processing a single chunk to balance task granularity.

- **Authorized Expansion Percentage for Final Chunk in a Task**:  Permissible oversize percentage for the last chunk of a task to avoid creating a very small chunk.

- **Default Speed Benchmark Process**:  Indicates if agents should automatically run a speed benchmark to calibrate their chunk processing speed.

- **Disable Chunk Trimming and Revert to Full Chunk Processing**:  Option to disable trimming of chunks based on benchmarks, causing agents to always process full-sized chunks.

### Command Line & Misc

- **Hashlist Placeholder in Command Line**:  Placeholder string in task command lines that gets replaced by the actual hashlist file path during execution.

- **Forbidden Characters in Attack Command Input**:  List of characters not allowed in the attack command line to prevent injection or command errors.

- **Automatic Assignment of Tasks with Priority 0 (Needed, Check File)**:  Controls whether tasks with priority 0 are automatically assigned to agents or require manual action.

- **Display Cracks per Minute for Active Tasks**:  Enables showing real-time statistics of password cracks per minute for currently running tasks.

### Rule Splitting (Obsolete soon)

- **Rule Splitting for Tasks: Always Create Small Tasks**:  Forces tasks to be split into smaller subtasks based on rule files, regardless of size.

- **Rule Splitting with Benchmark Constraint: Allow Subtasks with a Single Rule**:  Controls whether subtasks can be created with only one rule when splitting is constrained by benchmarking results.

- **Disable Automatic Task Splitting for Large Rule Files**:  Turns off automatic splitting of tasks when rule files are large, reverting to processing the entire rule file at once.

## Hashes/Cracks/Hashlist Settings

### Import/Display of Hashlist

- **Maximum Lines in Hashlist**:  Limits the maximum number of hash entries allowed in a single hashlist upload or import.

- **Hashes size Page in Hash View**:  Number of hashes displayed per page in the hashlist viewing interface.

- **Hashes per Page in Hash View**:  Defines pagination size for the hash view listing.

- **Separator Character for Hash and Plain (or Salt)**:  Character used to separate hash values from plaintext or salts in import files.

- **Check for Previous Cracks in Other Hashlists at Hashlist Creation**:  Enables automatic checking if hashes were previously cracked in other hashlists when creating a new one.

### Database Parameters

- **SQL Query Batch Size for Hashlist Transmission to Agents**:  Number of hash entries sent per batch to agents during hashlist transfer to optimize performance.

- **Maximum Length of Plain Text**:  Maximum allowed length for cracked plaintext strings stored in the database.

- **Maximum length of a Hash**:  Maximum allowed length of a hash string accepted during import or task creation.

## Notification Settings

- **Notification Sender Email**:  Email address used as the sender for outgoing notifications.

- **Sender's Display Name**:  The name displayed as the sender in notification emails.

- **Telegram Bot Token for Notifications**:  API token for a Telegram bot used to send notifications via Telegram messaging.

- **Enable Notification Proxy**:  Enables the use of a proxy server for sending notifications.

### Proxy Settings

- **Notification Server URL**:  URL of the proxy server used to route notification traffic.

- **Notification Proxy Port**:  Port number on which the notification proxy server listens.

- **Notification Proxy Type**:  Type of proxy protocol (e.g., HTTP, SOCKS5) used for notifications.

## General Settings

- **Enable Hashcat Brain**:  Enables integration with Hashcat Brain for collaborative password cracking and workload distribution.

- **Host for Hashcat Brain (Accessible by Agents)**:  Hostname or IP address where the Hashcat Brain server is accessible to agents.

- **Port for Hashcat Brain**:  Network port used by the Hashcat Brain server.

- **Password for Accessing Hashcat Brain Server**:  Password required by agents to connect to the Hashcat Brain server.

- **Ignore Error Messages Containing the Following String from Crackers**:  Filter to suppress error log entries containing specified text strings from cracking tools.

- **Number of Retained Log Entries**:  Maximum number of log entries retained in the database or log files before pruning.

- **Time Format Configuration**:  Format string or setting defining how timestamps are displayed in the UI and logs.

- **Maximum User Session Duration (in hours)**:  Maximum duration before a user session expires and requires re-authentication.

- **Base Hostname/Port/Protocol Override**:  Allows overriding the automatically detected base URL, port, or protocol for the web interface.

- **Admin Email Address for Webpage Footer Display**:  Email address shown in the website footer as the contact for the administrator.

- **Server Level Logging to File**:  Enables detailed server-side logging output to log files for troubleshooting or audits.

## Hashtypes

In Hashcat (and by extension, Hashtopolis), a hashtype refers to the specific algorithm or format used to generate a hash from a password. Hashtopolis is preconfigured with the hashtypes supported by the latest version of hashcat. For flexibility reason, there is the possibility to include additional hashtypes in case you use a modified version of hashcat with additional modules. 

Click on *+ New Hashtype* to add a new hashtype. Fill the corresponding fields described below:

- **Hashtype**: The numeric ID corresponding to the Hashcat hashmode (used with the -m option), e.g. -m 0 for MD5.
- **Description**: A human-readable name for the hashtype, typically matching the hashmode name.
- **Salted**: Salted indicates whether the hash algorithm uses a separate salt value (e.g., vBulletin). It does not apply to algorithms where the salt is embedded within the hash itself (e.g., bcrypt). This setting allows Hashtopolis to automatically check the Salted box when importing a hashlist that uses such an algorithm.
- **Slow Hash**: Check this box if the hashmode is a computationally intensive (slow) hash function. 

> [!TIP]
> Considering that the hashmode is configured in your hashcat binary, you can obtain all these information directly from hashcat:
> ```
> hashcat.bin --exam -m 3200
> 
> hashcat (v6.2.6-850-gfafb277e0+) starting in hash-info mode
> 
> Hash Info:
> ==========
> 
> Hash mode #3200
>   Name................: bcrypt $2*$, Blowfish (Unix)
>   Category............: Operating System
>  Slow.Hash...........: Yes
>   Password.Len.Min....: 0
>   Password.Len.Max....: 72
>   Salt.Type...........: Embedded
>   Salt.Len.Min........: 0
>   Salt.Len.Max........: 256
>   Kernel.Type(s)......: pure
>   Example.Hash.Format.: plain
>   Example.Hash........: $2a$05$MBCzKhG1KhezLh.0LRa0Kuw12nLJtpHy6DIaU.JAnqJUDYspHC.Ou
>   Example.Pass........: hashcat
>   Benchmark.Mask......: ?b?b?b?b?b?b?b
>   Autodetect.Enabled..: Yes
>   Self.Test.Enabled...: Yes
>   Potfile.Enabled.....: Yes
>   Custom.Plugin.......: No
>   Plaintext.Encoding..: ASCII, HEX
> 
> ```

You cannot create a new hashtype using an existing hashtype value. In case you want to modify an existing hashtype, you must delete it and recreate it. This is unfortunately not possible if there are existing hashlists associated to this hashtype.

## Health Checks

Health checks offer an excellent opportunity to ensure that the cracker binary set up is working correctly. For this purpose, a test command is executed on the agent and it is checked whether everything is working properly. The result and a possible error code are then displayed in the frontend

A new health check can be created by clicking on **New Health Check**. All you have to do is select the binary and the test can be started. The result is displayed transparently on the overview page.

Additional information is displayed by clicking on the ID. Here you will then find the detailed test result and the possible error code, which can be used for debugging.


## Log

Important events can be viewed in the log area. For example, failed logins are documented or document uploads are tracked:

<figure markdown="span">
    ![screenshot_logs_example](/assets/images/logs_example.png){ width="600" }
</figure>