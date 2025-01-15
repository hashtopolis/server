# Basic Workflow

Basic workflow highlighting the main point. The goal is that with such workflow a new user is able to run a task on a new hashlist with files or with masks. 

- New Hashlist
- New Files, wordlist/rules/others
- New Task
- Monitoring

## Hashlists
Hashtopolis utilizes hashlists to store password hashes you want to crack. These lists can be in plain text, HCCAPX, or binary format. Some hashes might include additional information like salts, depending on the format.
This section details the creation of a hashlist within the Hashtopolis interface. Note that at least one hashlist is required for creating tasks.
Refer to the Hashcat documentation for detailed information on supported hash types and their expected formats. You can also use the example hashes provided there as a test to create your first hashlist.

### Create a hashlist
In the Hashtopolis web interface, navigate to *Lists > New Hashlist*. You will get the following window:

![screenshot_hashlist](https://upload.wikimedia.org/wikipedia/commons/8/80/Comingsoon.png?20120228065200)

Here is how to fill in the different fields: 

1. **Name**: Provide a descriptive name for your hashlist.
2. **Hash Type**: Select the appropriate hash type from the dropdown menu. Suggestions will appear as you enter text.
3. **Hashlist Format**: Choose the format for your hashlist:
    - Text File: Paste or upload a plain text file containing one hash per line.
    - HCCAPX/PMKID: Upload a HCCAPX file containing password hashes.
    - Binary File: Upload a binary file containing password hashes.
4. **Salted Hashes**: Tick the box related to salted hashes if appropriate and provide the correct separator for your hashlist.
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

Once a category is selected, files can be added to the server using one of the following methods:

- **Upload from your computer** – Directly upload files stored on your local machine.
- **Import from an import directory** – Use files that have been preloaded into the server’s import directory.
- **Download from a URL** – Provide a URL to fetch files from an external source.
Detailed instructions for each upload method are provided in the following subsections.

### Upload a new file from the computer

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

1. **Associated Access Group**: Define the access group that will have permissions to access the file(s) you are uploading. 
2. **Select the files to import** by ticking the box in front of them. Alternatively, use Select All below.
3. **Import files**.

### Download new file from URL

1. **Associated Access Group**: Define the access group that will have permissions to access the file(s) you are uploading. 
2. **URL**: Provide the URL to download from..
3. **Download file**.

### Manage Files
Navigating to the Files page of the Hashtopolis User Interface, you can manage the files uploaded to the server.

1. **Select Category**.
2. **Secret**: Files that are marked as secret will only be sent to trusted agents.
Line count: Reprocess the file and update the line count with the number of lines contained in the file.
3. **Edit**: Edit the parameters of the file (name, file type and associated group).
4. **Delete**: Removes the file from Hashtopolis.

## Tasks

## Monitoring

# Advanced options/Features

## Advanced Hashlist

- Super Hashlist

- New Hashmode

## Advanced tasks

- Advanced option in task creation
- Preconfigured tasks (including from existing task)
- Super Task
- Import Super task

## New Binary

# Settings and Configuration

# Access Management 

Under construction

# Future Work
- Project structure
- LDAP
- Permission Scheme
- (Ref to the sprints)
