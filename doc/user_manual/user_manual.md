# Basic Workflow

This page describes the basic workflow required to launch your first cracking task.
It provides a high-level overview of the key steps needed to get started:

- Uploading hash lists;
- Uploading files;
- Creating a task;
- Monitoring the task and the results.
Each of these steps is covered in more detail in the advanced section **link**, but for now, this guide will walk you through the essentials to get your first task up and running. 

> [!NOTE]
> It is assumed that you have already access to a fully functional hashtopolis installation with at least one agent up and running. If it is not the case, please refer to the installation section **link**.

## Hashlists
Hashtopolis utilizes hashlists to store password hashes you want to crack. These lists can be in plain text, HCCAPX, or binary format. Some hashes might include additional information like salts, depending on the format.
This section details the creation of a hashlist within the Hashtopolis interface. Note that at least one hashlist is required for creating tasks.
Refer to the Hashcat documentation for detailed information on supported hash types and their expected formats. You can also use the example hashes provided there as a test to create your first hashlist.

### Create a hashlist
In the Hashtopolis web interface, navigate to *Lists > New Hashlist*. You will get the following window:

<figure markdown="span">
    ![screenshot_hashlist](https://upload.wikimedia.org/wikipedia/commons/8/80/Comingsoon.png?20120228065200){ width="300" }
</figure>

Here is how to fill in the different fields: 

1. **Name**: Provide a descriptive name for your hashlist.
2. **Hash Type**: Select the appropriate hash type from the dropdown menu. Suggestions will appear as you enter text.
3. **Hashlist Format**: Choose the format for your hashlist:
    - Text File: Paste or upload a plain text file containing one hash per line.
    - HCCAPX/PMKID: Upload a HCCAPX file containing password hashes.
    - Binary File: Upload a binary file containing password hashes.
4. **Salted Hashes**: Tick the box related to salted hashes if appropriate and provide the correct separator for your hashlist. The flag is enabled/disabled according to the settings defined in the Hashtype section (**see hashtypes REF**). If the provided salt(s) is in hex, the following flag needs to be enabled otherwise the salt will be interpreted as an ascii value (**is it ASCII or UTF8???**). 
5. **Hash source**: Select one of the following hash source types. 
6. **Providing the hash**: The last field of the form will automatically adapt depending on the chosen source type. You’ll be asked to provide additional details:
    - **Paste**: Copy and paste the hashes directly into the "Input" field.
    - **Upload**: Select a file containing the hashes from your computer.
    - **URL Download**: Provide a URL to download the hashlist.
    - **Import**: This option can be used as a workaround in case of upload errors with the first version of the user interface. To import a file, first copy it to the import folder as described in the section Import a new file.
7. **Access Group**: Modify the access group associated with the hashlist if needed.
8. **Create Hashlist**: Click "Create Hashlist" to finalize the process. This will open a new page displaying the details of your newly created hashlist.

## Files: Rules, Wordlist and other
When creating a password recovery task in Hashtopolis, you may need to upload additional files to the server, depending on the type of attack you want to perform. These files fall into three main categories:

1. **Rules**
    Rules files contain sets of instructions for dynamically modifying entries in a wordlist during an attack. By applying rules, you can generate variations of passwords without the need for additional wordlist files. For example, rules can:

    - Append numbers or special characters.
    - Replace or capitalize specific characters.
    - Reverse words or combine entries.

    Rules are commonly used alongside wordlist attacks to increase the range of password candidates efficiently.

2. **Wordlist**
    Wordlists, also known as dictionaries, are used in dictionary attacks. Each line in a wordlist is treated as a potential password candidate. Examples include: collections of commonly used passwords, specialized dictionaries tailored to a specific target or context.

3. **Others:** 
    This category includes any additional files required for specific attack types or configurations. Examples include … These files vary depending on the nature of the task and the tools being used.
Files can be uploaded to the Hashtopolis server from the Files page. To begin, select the appropriate file category by clicking on one of the tabs: Rules, Wordlists, or Other. The following figure illustrates the selection of the Rules category.

<figure markdown="span">
    ![screenshot_files](https://upload.wikimedia.org/wikipedia/commons/8/80/Comingsoon.png?20120228065200){ width="300" }
</figure>

Once a category is selected, files can be added to the server using one of the following methods:

- **Upload from your computer** – Directly upload files stored on your local machine.
- **Import from an import directory** – Use files that have been preloaded into the server’s import directory.
- **Download from a URL** – Provide a URL to fetch files from an external source.
Detailed instructions for each upload method are provided in the following subsections.

### Upload a new file from the computer

<figure markdown="span">
    ![screenshot_new_file](https://upload.wikimedia.org/wikipedia/commons/8/80/Comingsoon.png?20120228065200){ width="300" }
</figure>

1. **Add file**: Click this button to enable file upload.. After clicking, a new field labeled Choose file will appear. Each time you click on Add File, an additional Choose file field will be added, allowing you to upload multiple files simultaneously..
2. **Associated Access Group**: Define the access group that will have permissions to access the file(s) you are uploading. 
3. **Choose file**: Click this button to open your computer’s file explorer. Select the file you wish to upload.
4. **Upload files**: Once you have selected all the files you wanted to upload, click the Upload files button.

### Import a new file
When dealing with large files, such as wordlists, rules, or hashlists, you may encounter issues uploading them via the v1 of the Hashtopolis User Interface.. Common errors include exceeding the maximum upload size or experiencing a connection timeout. To bypass these limitations, you can use the import functionality of Hashtopolis.

- **Copy the file to the import folder**: Place the file in the designated import directory on the Hashtopolis server. If you are using the default Docker Compose setup, you can achieve this with the following command:
```
docker cp <dict> hashtopolis-backend:/usr/local/share/hashtopolis/import/
```

- **Import the file**: 

<figure markdown="span">
    ![screenshot_import_file](https://upload.wikimedia.org/wikipedia/commons/8/80/Comingsoon.png?20120228065200){ width="300" }
</figure>

1. **Associated Access Group**: Define the access group that will have permissions to access the file(s) you are uploading. 
2. **Select the files to import** by ticking the box in front of them. Alternatively, use Select All below.
3. **Import files**.

### Download new file from URL

<figure markdown="span">
    ![screenshot_download_file](https://upload.wikimedia.org/wikipedia/commons/8/80/Comingsoon.png?20120228065200){ width="300" }
</figure>

1. **Associated Access Group**: Define the access group that will have permissions to access the file(s) you are uploading. 
2. **URL**: Provide the URL to download from..
3. **Download file**.

### Manage Files
Navigating to the Files page of the Hashtopolis User Interface, you can manage the files uploaded to the server.

<figure markdown="span">
    ![screenshot_manage_file](https://upload.wikimedia.org/wikipedia/commons/8/80/Comingsoon.png?20120228065200){ width="300" }
</figure>

1. **Select Category**.
2. **Secret**: Files that are marked as secret will only be sent to trusted agents.
Line count: Reprocess the file and update the line count with the number of lines contained in the file.
3. **Edit**: Edit the parameters of the file (name, file type and associated group).
4. **Delete**: Removes the file from Hashtopolis.

## Tasks

To create a new task, you have to navigate to *Tasks > New Task*. You will get the following window in which you can create a new task. Some of the fields are mandotory, some others are filled with default values. 

1. **Name**: provide a name for the task you want to create. This is how the task will be referenced with during the monitoring phase (see **link**) therefore it should be relatively explicit to facilitate its monitoring.

2. **Hashlist**: select the hashlist you want to target in this specific task. Tasks are ordered by their IDs. Supertasks (**see ref to advanced usage**) are at the bottom of the list ordered by their respective IDs.

3. **Command Line**: provide in this field the attack command that will be executed by the agent on the targeted hashlist using the selected binary (see below). Note that *#HL#* is filled in by default in the command line. It is a placeholder for the hashlist and will be replaced automatically at execution time by the agent with the correct path to the hashlist file. Therefore you should not remove it nor include the filename for the hashlist. If for example you want to perform a mask attack of 6 digits, the command line would look like ```#HL# -a3 ?d?d?d?d?d?d```.
In case you want to perform a dictionary attack with rules, you have to select the corresponding files in the right table. If it is a wordlist, select it within the right column corresponding to T/Task. The Preprocessor part is explained in the advanced section. If it is a rule file, select first the rule tab (see **ref to the picture**) and then select the desired rule file. Note that upon selection of a rule file, the name of the file is included in the command line and automatically include the required '-r' flag.

4. **Priority**: Assign a priority number to the task. The expected value has to be an integer. Agents will be assigned to tasks in decreasing order of priority. A task with a priority 0 will not be processed even if agents are available. Default value is 0.

5. **Maximum number of agents**: Specify the maximum agents that can be assigned to the task. If this amount is reached, future available agents will be assigned to the next task available with a lower priority even if the all the chunks of the task have been distributed. The default value of 0 means that there is no maximum and therefore, all available agents are assigned to this tasks until all the chunks have been distributed. This functionality is helpful to only use a portion of the cluster for a specific task, and therefore allowing to split the workers on different tasks.

6. **Task Notes** - *optional*: This field allows the user to indicate some details about the tasks, the command line or any other details the user can find relevant. 

7. **Color** - *optional*: Can assign a color in a Hex color code format #RRGGBB. Default value is white #FFFFFF. This can be useful in the monitoring part to visually recognise a task or a set of tasks.

8. **Chunk size**: This parameter defines the duration that each agent should take to process a chunk for this task (**chunk should be define at some point in the general context of hashtopolis**). The default value is defined in the Settings (**ref to settings page XXX**).

9. **Status timer**: Defines the frequency with which each agent report its progress for this task to the server. The default value is defined in the Settings (**ref to settings page XXX**).

10. **Benchmark Type**: Select which benchmarking type should be used for this task. In most of the cases, it is recommended to use the default *Speed Test*. Only in few cases, such as tasks with big salted lists, the *Runtime* may be used.

11. **Task is CPU only**: If this flag is enabled, only the agents that are declared as CPU only can be assigned to this task. More details can be found in **ref to advanced agents**. The flag is disabled by default. 

12. **Task is small**: If this flag is enabled, a single agent can be assigned to this task. This is relevant for small tasks or to assign the full keyspace in a single chunk to an agent. Note that this is **NOT** equivalent to define the *Maximum number of agents* to 1. Indeed, in this latter case, the task will still be divided in chunks according to the *chunk size* parameter. The flag is disabled by default.

13. **Binary type to run the task**: This pair of parameters specify the binary type as well as the version of the binary to use for this specific task. It will by default use the latest uploaded version of the first binary type defined in the *Binaries* section (**see binaries for more details**).  


Do and Don't
- multiple wordlists do not work
- increment do not work
- --slow-candidates may not be a good idea
- others?

## Monitoring


# Future Work
- Project structure
- LDAP
- Permission Scheme
- (Ref to the sprints)
