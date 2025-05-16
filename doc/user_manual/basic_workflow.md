# Basic Workflow for Your First Cracking Task

Before diving into Hashtopolis, it’s important to understand some key terms used throughout this manual and in the application itself:

- **Agent**: An instance of the Hashtopolis client performing the actual password cracking using its associated hardware resources (e.g. GPUs and/or CPUs).
- **Hashlist**: A list of hashes stored in the database. Hashlists can be TEXT, HCCAPX, or BINARY, with most being TEXT format.
- **Task**: A specific password cracking job, defined by a command line specifying all the parameters, files to use, hashlist to target and binary to use.
- **Supertask**: A container grouping multiple subtasks together for easier management and monitoring. It is not a standalone cracking task.
- **Subtask**: A smaller task within a supertask, which behaves like a normal task but whose priority matters only inside the supertask.
- **Keyspace**: Size of the process that needs to be executed. This value is used to divide the total amount of work to then distribute it to the agents. 
> [!NOTE]  
> The keyspace returned by hashcat is typically not the same size as the actual search space as one may expect. For more details, see the [Hashcat Wiki on keyspace](https://hashcat.net/wiki/doku.php?id=frequently_asked_questions#what_is_a_keyspace).
- **Chunk**: A portion of the keyspace assigned to an agent for cracking. If an agent fails or a chunk times out, it will be reassigned.
- **Access Management**: Controls user permissions and access to various features and functions for fine-grained security.
- **Groups**: Logical separations that allow different sets of users, hashlists, tasks, and agents to operate independently without interference.

---

## Overview of the Basic Workflow

This section guides you through the essential steps to launch your first cracking task:

> [!NOTE]  
> This guide assumes you have a working Hashtopolis installation with at least one registered and active agent. For installation instructions, please see the [Installation Guide](/installation_guidelines/basic_install/).


1. **Upload Hashlists**  
   Import the hashes you want to crack into Hashtopolis. Supported formats include plain text and specialized hash capture files.

2. **Upload Required Files**  
   Upload any additional files your attack requires, such as wordlists or rule files.

3. **Create a Task**  
   Define your cracking task by setting the attack command line and assigning the hashlist and other files.

4. **Monitor the Task**  
   Track progress through the UI, view agent statuses, and check for any cracked passwords.

We provide below more details on each of these steps. A more comprehensive explanation can be found in the User Manual section.

### 1. Upload Hashlists

Start by importing the hashes you want to crack:

- Go to the **"Hashlists"** page in the sidebar.
- Click on the button **"+ New Hashlist"**.
- Choose a name for your hashlist
- Select the appropriate **hash type** (e.g., MD5, SHA1, NTLM).
- Paste your hashes into the text field or upload a file.
- Optionally assign the hashlist to a **group** to manage access permissions.
- Click **"Submit"** to create the hashlist.

> [!NOTE]
> Hash formats like plain text, HCCAPX (for WPA/WPA2), or binary dumps are supported. Make sure your input matches the expected format for the selected hash type.

---

### 2. Upload Required Files

To perform most attacks, you’ll need additional resources like wordlists or rule files:

- Go to the corresponding type of files in the **"Files"** section, for example **"Wordlists"**.
- Click **"+ New Wordlists"** and select the file to upload (or provide the link to the file).
- Optionally assign it to a **group**.
- Click **"Create"** to store the file on the server.

Uploaded files can later be linked to tasks.

---

### 3. Create a Task

Now you’re ready to define your first cracking job:

- Navigate to **"Tasks"** and click the button **"+ New Task"**.
- Provide a task name and select the hashlist created in step 1.
- Enter the **Attack command** for your deisered process. Note that the placeholder **#HL#** is placed by default in the command. It represents the hashlist you have selected and you therefore don't need to type it manually. The binary to use also does not need to be typed as it is added automatically by the tool. If you want to use files, they must have been uploaded to the server first. They appear in the right folding menu. By clicking on the box next to the file, they are automatically added to the command line, including the '-r' flag for rules files. 
> [!NOTE]
> A simple mask attack of 4 digits would therefore be the following command line:
> ```#HL# -a3 ?d?d?d?d```
- Choose a **priority** superior to 0 if you want the task to start. 
- Click on the button **"Create"**.

The task will automatically be assigned to available agents if it has the highest priority value.

---

### 4. Monitor the Task

Once the task is active, you can track its status and results:

- Go to the **"Tasks"** page to see overall task progress, speed, and number of passwords retrieved.
- Click on the task to view detailed progress and many other information.
- Cracked passwords will appear in the task view.

> [!TIP]
> You can pause, resume, or delete tasks at any time. Make sure agents remain online and responsive to ensure smooth cracking progress.


---

## Do’s and Don’ts

- **Do** test your command line locally with Hashcat if your task is failing for unknown reason.
- **Don’t** use multiple wordlists with attack mode 0, Hashtopolis currently supports only a single wordlist per task.
- **Don’t** use the `--increment` flag in your command line, as it is not supported.
- **Be cautious** with the `--slow-candidates` option, it may cause performance issues or unexpected behavior.
- **Don’t** create extremely large tasks as **small task**, as it is against the principle of parallelisation of hashtopolis.
- **Do** monitor your agents’ performance to adjust chunk sizes or task priorities as needed.

